<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Controllers\PageController;
use App\Core\Config;
use Tests\BaseTest;

/**
 * Feature: PageController monta a tela completa (layout + navegação + screen)
 * para um usuário autenticado, reaproveitando Router, Factory, Templates e Strategy.
 */
final class PageRenderingTest extends BaseTest
{
    /** @return array<string, mixed> */
    private function admin(): array
    {
        return ['id' => 1, 'name' => 'Administrador', 'email' => 'admin@comandaflex.local', 'role' => 'admin'];
    }

    public function testRendersDashboardForAdmin(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('dashboard', $this->admin());

        // Assert
        $this->assertStringContainsString('<title>Dashboard · ComandaFlex</title>', $html);
        $this->assertStringContainsString('Resumo rápido', $html);
    }

    public function testRendersEveryConfiguredRouteForAdmin(): void
    {
        // Arrange
        $controller = new PageController();
        $routes = Config::instance()->get('app.routes', []);

        // Act / Assert
        foreach ($routes as $key => $meta) {
            $html = $controller->render((string) $key, $this->admin());
            $this->assertStringContainsString('<title>' . $meta['label'] . ' · ComandaFlex</title>', $html);
            $this->assertStringContainsString('class="app-shell"', $html);
        }
    }

    public function testRenderIncludesSharedNavigation(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('dashboard', $this->admin());

        // Assert
        $this->assertStringContainsString('Cardápio', $html);
        $this->assertStringContainsString('Relatórios', $html);
        $this->assertStringContainsString('nav-link', $html);
    }

    public function testUnknownPageFallsBackToDefaultPage(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('rota-inexistente', $this->admin());

        // Assert
        $this->assertStringContainsString('<title>Dashboard · ComandaFlex</title>', $html);
    }
}
