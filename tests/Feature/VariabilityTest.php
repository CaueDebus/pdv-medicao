<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Controllers\PageController;
use App\Core\Config;
use App\Domain\Variability\FeatureToggle;
use App\Repositories\DashboardRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Core\View;
use App\Domain\Patterns\Factory\ScreenFactory;
use Tests\BaseTest;

/**
 * Feature (LPS): o catálogo de módulos define o produto entregue.
 *
 * Desligar um módulo tira a tela da navegação e do roteamento, sem remover
 * código e sem criar uma variante paralela do sistema.
 */
final class VariabilityTest extends BaseTest
{
    /** @return array<string, mixed> */
    private function admin(): array
    {
        return ['id' => 1, 'name' => 'Administrador', 'email' => 'admin@comandaflex.local', 'role' => 'admin'];
    }

    /**
     * @param array<string, bool> $overrides code => habilitado
     */
    private function controllerWith(array $overrides): PageController
    {
        $modules = [];

        foreach ((new ModuleRepository())->all() as $module) {
            $code = (string) $module['code'];
            $module['enabled'] = $overrides[$code] ?? $module['enabled'];
            $modules[] = $module;
        }

        return new PageController(
            new View(),
            new ScreenFactory(),
            new DashboardRepository(),
            new ProductRepository(),
            new OrderRepository(),
            new ModuleRepository(),
            // O mapa real de config/app.php: o teste exercita a configuração entregue.
            new FeatureToggle($modules, Config::instance()->get('app.features', [])),
        );
    }

    public function testStockScreenIsAvailableWhileTheModuleIsOn(): void
    {
        // Arrange
        $controller = $this->controllerWith(['stock' => true]);

        // Act
        $html = $controller->render('estoque', $this->admin());

        // Assert
        $this->assertStringContainsString('<title>Estoque · ComandaFlex</title>', $html);
        $this->assertStringContainsString('Estoque', $html);
    }

    public function testDisablingTheStockModuleRemovesTheScreen(): void
    {
        // Arrange
        $controller = $this->controllerWith(['stock' => false]);

        // Act
        $html = $controller->render('estoque', $this->admin());

        // Assert — a rota cai para a tela padrão
        $this->assertStringContainsString('<title>Dashboard · ComandaFlex</title>', $html);
    }

    public function testDisabledModuleAlsoDisappearsFromNavigation(): void
    {
        // Arrange
        $controller = $this->controllerWith(['stock' => false]);

        // Act
        $html = $controller->render('dashboard', $this->admin());

        // Assert
        $this->assertStringNotContainsString('?page=estoque', $html);
        $this->assertStringContainsString('?page=cardapio', $html, 'os demais módulos seguem entregues');
    }

    public function testSuppliersScreenIsDeliveredWithTheStockModule(): void
    {
        // Arrange
        $controller = $this->controllerWith(['stock' => true]);

        // Act
        $html = $controller->render('fornecedores', $this->admin());

        // Assert
        $this->assertStringContainsString('<title>Fornecedores · ComandaFlex</title>', $html);
    }

    public function testDisablingStockRemovesStockAndSuppliersTogether(): void
    {
        // Arrange
        $controller = $this->controllerWith(['stock' => false]);

        // Act
        $suppliers = $controller->render('fornecedores', $this->admin());
        $dashboard = $controller->render('dashboard', $this->admin());

        // Assert — um módulo, duas telas
        $this->assertStringContainsString('<title>Dashboard · ComandaFlex</title>', $suppliers);
        $this->assertStringNotContainsString('?page=fornecedores', $dashboard);
        $this->assertStringNotContainsString('?page=estoque', $dashboard);
    }

    public function testStationsScreenIsDeliveredWithTheProductionModule(): void
    {
        // Arrange
        $controller = $this->controllerWith(['production' => true]);

        // Act
        $html = $controller->render('estacoes', $this->admin());

        // Assert
        $this->assertStringContainsString('<title>Estações · ComandaFlex</title>', $html);
    }

    public function testDisablingProductionRemovesStationsToo(): void
    {
        // Arrange
        $controller = $this->controllerWith(['production' => false]);

        // Act
        $stations = $controller->render('estacoes', $this->admin());
        $dashboard = $controller->render('dashboard', $this->admin());

        // Assert
        $this->assertStringContainsString('<title>Dashboard · ComandaFlex</title>', $stations);
        $this->assertStringNotContainsString('?page=estacoes', $dashboard);
        $this->assertStringContainsString('?page=mesas', $dashboard, 'telas do núcleo seguem entregues');
    }

    public function testTurningOffReportsAffectsManagerToo(): void
    {
        // Arrange
        $controller = $this->controllerWith(['reports' => false]);

        // Act
        $html = $controller->render('relatorios', ['id' => 2, 'name' => 'Gerente', 'role' => 'manager']);

        // Assert
        $this->assertStringContainsString('<title>Dashboard · ComandaFlex</title>', $html);
    }

    public function testCoreScreensIgnoreTheModuleCatalog(): void
    {
        // Arrange
        $controller = $this->controllerWith(['stock' => false, 'production' => false, 'reports' => false]);

        // Act / Assert
        foreach (['comandas' => 'Comandas', 'itens' => 'Itens da comanda', 'mesas' => 'Mesas', 'clientes' => 'Clientes', 'pagamentos' => 'Pagamentos'] as $page => $title) {
            $html = $controller->render($page, $this->admin());
            $this->assertStringContainsString('<title>' . $title . ' · ComandaFlex</title>', $html);
        }
    }

    public function testSettingsScreenListsTheActiveFeatureMap(): void
    {
        // Arrange
        $controller = $this->controllerWith(['stock' => false]);

        // Act
        $html = $controller->render('configuracoes', $this->admin());

        // Assert
        $this->assertStringContainsString('Módulos ligados nesta instalação', $html);
        $this->assertStringContainsString('hotel_integration', $html);
    }
}
