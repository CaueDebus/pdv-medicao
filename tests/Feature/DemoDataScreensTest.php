<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Controllers\PageController;
use Tests\BaseTest;

/**
 * Feature: em modo demo, cada tela transacional injeta os dados do
 * repositório correspondente no bloco "Dados carregados".
 */
final class DemoDataScreensTest extends BaseTest
{
    /** @return array<string, mixed> */
    private function admin(): array
    {
        return ['id' => 1, 'name' => 'Administrador', 'email' => 'admin@comandaflex.local', 'role' => 'admin'];
    }

    public function testCardapioListsDemoProducts(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('cardapio', $this->admin());

        // Assert
        $this->assertStringContainsString('Batata frita', $html);
        $this->assertStringContainsString('Chopp 300ml', $html);
    }

    public function testComandasListsDemoOrders(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('comandas', $this->admin());

        // Assert
        $this->assertStringContainsString('Mesa 07', $html);
        $this->assertStringContainsString('preparing', $html);
    }

    public function testEstoqueShowsOnlyLowStockItems(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('estoque', $this->admin());

        // Assert
        $this->assertStringContainsString('Caipirinha', $html);
        $this->assertStringNotContainsString('Chopp 300ml', $html);
    }

    public function testModulosListsDemoModules(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('modulos', $this->admin());

        // Assert
        $this->assertStringContainsString('Módulo Comida', $html);
        $this->assertStringContainsString('Integração hotel', $html);
    }

    public function testDashboardShowsOpenOrdersCounter(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('dashboard', $this->admin());

        // Assert
        $this->assertStringContainsString('Comandas abertas', $html);
        $this->assertStringContainsString('18', $html);
    }
}
