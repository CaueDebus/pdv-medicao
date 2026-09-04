<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Patterns\Strategy\AdminNavigationStrategy;
use App\Domain\Patterns\Strategy\OperatorNavigationStrategy;
use Tests\BaseTest;

/**
 * Strategy: cada perfil decora a MESMA lista de navegação de um jeito.
 */
final class NavigationStrategyTest extends BaseTest
{
    /** @return array<int, array<string, mixed>> */
    private function items(): array
    {
        return [
            ['key' => 'dashboard', 'label' => 'Dashboard'],
            ['key' => 'estoque', 'label' => 'Estoque'],
            ['key' => 'relatorios', 'label' => 'Relatórios'],
            ['key' => 'configuracoes', 'label' => 'Configurações'],
        ];
    }

    public function testAdminStrategyMarksActivePageAndNeverLocks(): void
    {
        // Arrange
        $strategy = new AdminNavigationStrategy();

        // Act
        $decorated = $strategy->decorate($this->items(), 'estoque');

        // Assert
        $this->assertTrue($decorated[1]['active']);
        $this->assertFalse($decorated[0]['active']);
        foreach ($decorated as $item) {
            $this->assertFalse($item['locked'], 'admin não tem item bloqueado');
        }
    }

    public function testOperatorStrategyLocksManagementSections(): void
    {
        // Arrange
        $strategy = new OperatorNavigationStrategy();

        // Act
        $decorated = $strategy->decorate($this->items(), 'dashboard');
        $locked = [];
        foreach ($decorated as $item) {
            if ($item['locked'] === true) {
                $locked[] = $item['key'];
            }
        }

        // Assert
        $this->assertContains('relatorios', $locked);
        $this->assertContains('configuracoes', $locked);
        $this->assertCount(2, $locked);
    }

    public function testOperatorStrategyKeepsOperationalSectionsOpen(): void
    {
        // Arrange
        $strategy = new OperatorNavigationStrategy();

        // Act
        $decorated = $strategy->decorate($this->items(), 'dashboard');

        // Assert
        $this->assertFalse($decorated[0]['locked']); // dashboard
        $this->assertFalse($decorated[1]['locked']); // estoque
        $this->assertNull($decorated[1]['reason']);
    }

    public function testOperatorStrategyMarksActivePage(): void
    {
        // Arrange
        $strategy = new OperatorNavigationStrategy();

        // Act
        $decorated = $strategy->decorate($this->items(), 'relatorios');

        // Assert
        $this->assertTrue($decorated[2]['active']);
        $this->assertSame('restrito ao gerente/admin', $decorated[2]['reason']);
    }
}
