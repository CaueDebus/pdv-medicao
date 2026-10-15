<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\OrderRepository;
use Tests\BaseTest;

final class OrderRepositoryTest extends BaseTest
{
    public function testOpenOrdersReturnsDemoOrdersWhenOffline(): void
    {
        // Arrange
        $repository = new OrderRepository();

        // Act
        $orders = $repository->openOrders();

        // Assert
        $this->assertCount(3, $orders);
        $this->assertArrayHasKey('table_name', $orders[0]);
        $this->assertArrayHasKey('status', $orders[0]);
        $this->assertArrayHasKey('total_value', $orders[0]);
    }

    public function testProductionQueueReturnsStages(): void
    {
        // Arrange
        $repository = new OrderRepository();

        // Act
        $queue = $repository->productionQueue();

        // Assert
        $this->assertCount(3, $queue);
        $this->assertSame('Recebido', $queue[0]['stage']);
        $this->assertArrayHasKey('item', $queue[0]);
    }

    public function testOptionsLabelEachOrderByTableAndNumber(): void
    {
        // Arrange
        $repository = new OrderRepository();

        // Act — o gancho optionLabel() troca o rótulo padrão (name)
        $options = $repository->options();

        // Assert
        $this->assertCount(3, $options);
        $this->assertSame('Mesa 07 · #31', $options['31']);
    }

    public function testRecalculatingTheTotalNeedsTheDatabase(): void
    {
        // Arrange
        $repository = new OrderRepository();

        // Act / Assert — no modo demo nada é recalculado nem gravado
        $this->assertFalse($repository->recalculateTotal(31));
    }
}
