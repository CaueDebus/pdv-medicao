<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;
use App\Domain\Patterns\Factory\CrudFactory;
use App\Domain\Patterns\Factory\ScreenFactory;
use App\Domain\Patterns\Strategy\AdminNavigationStrategy;
use App\Domain\Patterns\Strategy\ManagerNavigationStrategy;
use App\Domain\Patterns\Strategy\NavigationVisibilityStrategy;
use App\Domain\Patterns\Strategy\OperatorNavigationStrategy;
use App\Domain\Variability\FeatureToggle;
use App\Repositories\DashboardRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\StockMovementRepository;

final class PageController
{
    private readonly FeatureToggle $features;

    private readonly CrudFactory $crudFactory;

    public function __construct(
        private readonly View $view = new View(),
        private readonly ScreenFactory $screenFactory = new ScreenFactory(),
        private readonly DashboardRepository $dashboardRepository = new DashboardRepository(),
        private readonly ProductRepository $productRepository = new ProductRepository(),
        private readonly OrderRepository $orderRepository = new OrderRepository(),
        private readonly ModuleRepository $moduleRepository = new ModuleRepository(),
        ?FeatureToggle $features = null,
        private readonly StockMovementRepository $stockMovementRepository = new StockMovementRepository(),
    ) {
        $this->features = $features ?? new FeatureToggle(
            $this->moduleRepository->all(),
            Config::instance()->get('app.features', []),
        );
        $this->crudFactory = new CrudFactory($this->features);
    }

    public function render(string $page, ?array $user = null): string
    {
        return $this->renderWithScreen($page, $user, []);
    }

    /**
     * Monta a moldura da aplicação (layout, navegação, permissões e
     * variabilidade) e injeta a tela recebida. É o ponto único de montagem:
     * o CrudController reaproveita isto para exibir seus formulários.
     *
     * @param array<string, mixed> $overrides
     */
    public function renderWithScreen(string $page, ?array $user, array $overrides): string
    {
        $config = Config::instance();
        $router = new Router($config->get('app.routes', []));
        $resolvedPage = $router->resolve($page, $config->get('app.default_page', 'dashboard'));
        $currentUser = $user ?? Session::instance()->user();
        $role = is_array($currentUser) ? (string) ($currentUser['role'] ?? 'operator') : 'operator';

        if (! $this->canAccess($resolvedPage, $role)) {
            $resolvedPage = $config->get('app.default_page', 'dashboard');
            $overrides = [];
        }

        $screen = $this->screenFactory->create($resolvedPage, $this->contextFor($resolvedPage, $role));
        $screen = array_merge($screen, $overrides);

        $navigation = $this->navigationFor($role);
        $items = $this->buildNavigation($config->get('app.routes', []), $role);

        return $this->view->render('layouts/app.php', [
            'app' => $config->get('app', []),
            'screen' => $screen,
            'user' => $currentUser,
            'navigation' => $navigation->decorate($items, $resolvedPage),
        ]);
    }

    /**
     * Uma tela só é acessível se o perfil pode vê-la e se o módulo que a
     * habilita está ligado nesta instalação.
     */
    public function canAccess(string $page, string $role): bool
    {
        return in_array($page, $this->allowedPagesForRole($role), true) && $this->features->pageEnabled($page);
    }

    public function features(): FeatureToggle
    {
        return $this->features;
    }

    /**
     * @return array<string, mixed>
     */
    private function contextFor(string $page, string $role): array
    {
        $context = [
            'dashboard' => $this->dashboardRepository->summary(),
            'products' => $this->productRepository->all(),
            'low_stock' => $this->productRepository->lowStock(),
            'orders' => $this->orderRepository->all(),
            'open_orders' => $this->orderRepository->openOrders(),
            'queue' => $this->orderRepository->productionQueue(),
            'movements' => $this->stockMovementRepository->all(),
            'modules' => $this->moduleRepository->all(),
            'features' => $this->features->states(),
            'flash' => Session::instance()->pullFlash(),
        ];

        $resource = $this->crudFactory->create($page);

        if ($resource !== null) {
            $context['crud'] = [
                'resource' => $resource,
                'rows' => $resource->repository->all(),
                'persists' => $resource->repository->persists(),
                'can_destroy' => $resource->canDestroy($role),
                'token' => Session::instance()->csrfToken(),
                'page' => $page,
            ];
        }

        return $context;
    }

    private function navigationFor(string $role): NavigationVisibilityStrategy
    {
        return match ($role) {
            'admin' => new AdminNavigationStrategy(),
            'manager' => new ManagerNavigationStrategy(),
            default => new OperatorNavigationStrategy(),
        };
    }

    /**
     * @return array<int, string>
     */
    private function allowedPagesForRole(string $role): array
    {
        $pages = match ($role) {
            'admin' => ['dashboard', 'cardapio', 'comandas', 'estoque', 'producao', 'modulos', 'relatorios', 'configuracoes'],
            'manager' => ['dashboard', 'cardapio', 'comandas', 'estoque', 'producao', 'modulos', 'relatorios'],
            default => ['dashboard', 'cardapio', 'comandas', 'estoque', 'producao', 'modulos'],
        };

        return $this->features->filterPages($pages);
    }

    /**
     * @param array<string, array<string, string>> $routes
     * @return array<int, array<string, mixed>>
     */
    private function buildNavigation(array $routes, string $role): array
    {
        $items = [];
        $allowed = $this->allowedPagesForRole($role);

        foreach ($routes as $key => $meta) {
            if (! $this->features->pageEnabled((string) $key)) {
                continue;
            }

            if (! in_array((string) $key, $allowed, true) && ! in_array((string) $key, ['relatorios', 'configuracoes'], true)) {
                continue;
            }

            $items[] = [
                'key' => $key,
                'label' => $meta['label'] ?? $key,
                'description' => $meta['description'] ?? '',
                'url' => '?page=' . urlencode($key),
            ];
        }

        return $items;
    }
}
