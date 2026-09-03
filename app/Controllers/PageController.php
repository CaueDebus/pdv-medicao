<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;
use App\Domain\Patterns\Factory\ScreenFactory;
use App\Domain\Patterns\Strategy\AdminNavigationStrategy;
use App\Domain\Patterns\Strategy\NavigationVisibilityStrategy;
use App\Domain\Patterns\Strategy\OperatorNavigationStrategy;
use App\Repositories\DashboardRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;

final class PageController
{
    public function __construct(
        private readonly View $view = new View(),
        private readonly ScreenFactory $screenFactory = new ScreenFactory(),
        private readonly DashboardRepository $dashboardRepository = new DashboardRepository(),
        private readonly ProductRepository $productRepository = new ProductRepository(),
        private readonly OrderRepository $orderRepository = new OrderRepository(),
        private readonly ModuleRepository $moduleRepository = new ModuleRepository(),
    ) {
    }

    public function render(string $page, ?array $user = null): string
    {
        $config = Config::instance();
        $router = new Router($config->get('app.routes', []));
        $resolvedPage = $router->resolve($page, $config->get('app.default_page', 'dashboard'));
        $currentUser = $user ?? Session::instance()->user();
        $role = is_array($currentUser) ? (string) ($currentUser['role'] ?? 'operator') : 'operator';
        $navigation = $this->navigationFor($role);

        $allowedPages = $this->allowedPagesForRole($role);
        if (! in_array($resolvedPage, $allowedPages, true)) {
            $resolvedPage = $config->get('app.default_page', 'dashboard');
        }

        $context = [
            'dashboard' => $this->dashboardRepository->summary(),
            'products' => $this->productRepository->all(),
            'low_stock' => $this->productRepository->lowStock(),
            'orders' => $this->orderRepository->openOrders(),
            'queue' => $this->orderRepository->productionQueue(),
            'modules' => $this->moduleRepository->all(),
        ];

        $screen = $this->screenFactory->create($resolvedPage, $context);

        return $this->view->render('layouts/app.php', [
            'app' => $config->get('app', []),
            'screen' => $screen,
            'user' => $currentUser,
            'navigation' => $navigation->decorate($this->buildNavigation($config->get('app.routes', [])), $resolvedPage),
        ]);
    }

    private function navigationFor(string $role): NavigationVisibilityStrategy
    {
        return $role === 'admin' ? new AdminNavigationStrategy() : new OperatorNavigationStrategy();
    }

    /**
     * @return array<int, string>
     */
    private function allowedPagesForRole(string $role): array
    {
        return match ($role) {
            'admin' => ['dashboard', 'cardapio', 'comandas', 'estoque', 'producao', 'modulos', 'relatorios', 'configuracoes'],
            'manager' => ['dashboard', 'cardapio', 'comandas', 'estoque', 'producao', 'modulos', 'relatorios'],
            default => ['dashboard', 'cardapio', 'comandas', 'estoque', 'producao', 'modulos'],
        };
    }

    /**
     * @param array<string, array<string, string>> $routes
     * @return array<int, array<string, mixed>>
     */
    private function buildNavigation(array $routes): array
    {
        $items = [];

        foreach ($routes as $key => $meta) {
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
