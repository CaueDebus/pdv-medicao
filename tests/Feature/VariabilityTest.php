<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Controllers\PageController;
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
            new FeatureToggle($modules, ['estoque' => 'stock', 'producao' => 'production', 'relatorios' => 'reports']),
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

        // Act
        $html = $controller->render('comandas', $this->admin());

        // Assert
        $this->assertStringContainsString('<title>Comandas · ComandaFlex</title>', $html);
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
