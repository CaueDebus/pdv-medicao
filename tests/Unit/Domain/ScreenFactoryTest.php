<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Patterns\Factory\ScreenFactory;
use Tests\BaseTest;

final class ScreenFactoryTest extends BaseTest
{
    public function testCreateDashboardUsesDashboardTemplate(): void
    {
        // Arrange
        $factory = new ScreenFactory();

        // Act
        $screen = $factory->create('dashboard', ['products' => [1, 2, 3]]);

        // Assert
        $this->assertSame('dashboard', $screen['page']);
        $this->assertSame('Dashboard', $screen['title']);
        $this->assertCount(4, $screen['metrics']);
        $this->assertArrayHasKey('highlights', $screen);
        $this->assertArrayHasKey('sections', $screen);
    }

    public function testCreateOperationsPageOverridesTitleAndSubtitle(): void
    {
        // Arrange
        $factory = new ScreenFactory();

        // Act
        $screen = $factory->create('cardapio');

        // Assert
        $this->assertSame('cardapio', $screen['page']);
        $this->assertSame('Cardápio', $screen['title']);
        $this->assertSame('Catálogo e preços do menu', $screen['subtitle']);
    }

    public function testCreateReportsPageUsesReportsTemplate(): void
    {
        // Arrange
        $factory = new ScreenFactory();

        // Act
        $screen = $factory->create('relatorios');

        // Assert
        $this->assertSame('Relatórios', $screen['title']);
        $this->assertCount(3, $screen['metrics']);
    }

    public function testCreateUnknownPageFallsBackToDashboardTemplate(): void
    {
        // Arrange
        $factory = new ScreenFactory();

        // Act
        $screen = $factory->create('pagina-desconhecida');

        // Assert
        $this->assertSame('pagina-desconhecida', $screen['page']);
        $this->assertCount(4, $screen['metrics'], 'fallback usa o DashboardTemplate');
    }
}
