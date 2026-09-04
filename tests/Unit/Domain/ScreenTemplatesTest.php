<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Patterns\Template\DashboardTemplate;
use App\Domain\Patterns\Template\OperationsTemplate;
use App\Domain\Patterns\Template\ReportsTemplate;
use Tests\BaseTest;

/**
 * Template Method: build() sempre devolve a mesma estrutura
 * (base + metrics + highlights + sections), variando só o conteúdo.
 */
final class ScreenTemplatesTest extends BaseTest
{
    public function testDashboardTemplateBuildsFullStructure(): void
    {
        // Arrange
        $template = new DashboardTemplate();

        // Act
        $screen = $template->build(['products' => []]);

        // Assert
        $this->assertSame('Dashboard', $screen['title']);
        $this->assertCount(4, $screen['metrics']);
        $this->assertCount(3, $screen['highlights']);
        $this->assertCount(2, $screen['sections']);
    }

    public function testDashboardTemplateCarriesContextThrough(): void
    {
        // Arrange
        $template = new DashboardTemplate();
        $context = ['dashboard' => ['open_orders' => 7]];

        // Act
        $screen = $template->build($context);

        // Assert
        $this->assertSame(7, $screen['context']['dashboard']['open_orders']);
    }

    public function testOperationsTemplateExposesOrdersAndProductionSections(): void
    {
        // Arrange
        $template = new OperationsTemplate();

        // Act
        $screen = $template->build();
        $types = array_column($screen['sections'], 'type');

        // Assert
        $this->assertCount(3, $screen['metrics']);
        $this->assertContains('orders', $types);
        $this->assertContains('production', $types);
    }

    public function testReportsTemplateExposesManagerMetrics(): void
    {
        // Arrange
        $template = new ReportsTemplate();

        // Act
        $screen = $template->build();

        // Assert
        $this->assertSame('Relatórios', $screen['title']);
        $this->assertCount(3, $screen['metrics']);
        $this->assertCount(2, $screen['highlights']);
    }
}
