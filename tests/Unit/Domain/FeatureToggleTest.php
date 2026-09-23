<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Variability\FeatureToggle;
use Tests\BaseTest;

/**
 * Variabilidade (LPS): o catálogo de módulos decide quais telas o produto
 * entrega nesta instalação.
 */
final class FeatureToggleTest extends BaseTest
{
    /** @return array<int, array<string, mixed>> */
    private function modules(bool $stockEnabled = true): array
    {
        return [
            ['name' => 'Módulo Estoque', 'code' => 'stock', 'enabled' => $stockEnabled],
            ['name' => 'Módulo Relatórios', 'code' => 'reports', 'enabled' => true],
            ['name' => 'Integração hotel', 'code' => 'hotel_integration', 'enabled' => false],
        ];
    }

    /** @return array<string, string> */
    private function pageMap(): array
    {
        return ['estoque' => 'stock', 'relatorios' => 'reports'];
    }

    public function testEnabledReadsTheModuleCatalog(): void
    {
        // Arrange
        $features = new FeatureToggle($this->modules(), $this->pageMap());

        // Act / Assert
        $this->assertTrue($features->enabled('stock'));
        $this->assertFalse($features->enabled('hotel_integration'));
    }

    public function testModuleOutsideTheCatalogIsTreatedAsCore(): void
    {
        // Arrange
        $features = new FeatureToggle($this->modules(), $this->pageMap());

        // Act / Assert
        $this->assertTrue($features->enabled('nao_catalogado'));
        $this->assertTrue($features->pageEnabled('dashboard'), 'tela de núcleo não depende de módulo');
    }

    public function testDisabledModuleDisablesItsPage(): void
    {
        // Arrange
        $features = new FeatureToggle($this->modules(stockEnabled: false), $this->pageMap());

        // Act / Assert
        $this->assertFalse($features->pageEnabled('estoque'));
        $this->assertTrue($features->pageEnabled('relatorios'));
    }

    public function testFilterPagesRemovesOnlyTheDisabledOnes(): void
    {
        // Arrange
        $features = new FeatureToggle($this->modules(stockEnabled: false), $this->pageMap());

        // Act
        $pages = $features->filterPages(['dashboard', 'estoque', 'relatorios']);

        // Assert
        $this->assertCount(2, $pages);
        $this->assertContains('dashboard', $pages);
        $this->assertContains('relatorios', $pages);
    }

    public function testMysqlIntegerFlagCountsAsEnabled(): void
    {
        // Arrange
        $features = new FeatureToggle([
            ['code' => 'stock', 'enabled' => 1],
            ['code' => 'reports', 'enabled' => 0],
        ], $this->pageMap());

        // Act / Assert
        $this->assertTrue($features->enabled('stock'));
        $this->assertFalse($features->enabled('reports'));
    }
}
