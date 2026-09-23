<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\StockMovementRepository;
use Tests\BaseTest;

final class StockMovementRepositoryTest extends BaseTest
{
    public function testAllReturnsDemoMovementsWhenOffline(): void
    {
        // Arrange
        $repository = new StockMovementRepository();

        // Act
        $movements = $repository->all();

        // Assert
        $this->assertCount(3, $movements);
        $this->assertArrayHasKey('product_name', $movements[0]);
        $this->assertArrayHasKey('movement_type', $movements[0]);
        $this->assertArrayHasKey('quantity', $movements[0]);
    }

    public function testTypeOptionsCoverTheMigrationEnum(): void
    {
        // Arrange / Act
        $options = StockMovementRepository::typeOptions();

        // Assert
        $this->assertCount(3, $options);
        $this->assertArrayHasKey('in', $options);
        $this->assertArrayHasKey('out', $options);
        $this->assertArrayHasKey('adjustment', $options);
    }

    public function testWritesAreRejectedWhenDatabaseIsOffline(): void
    {
        // Arrange
        $repository = new StockMovementRepository();

        // Act / Assert
        $this->assertFalse($repository->persists());
        $this->assertFalse($repository->create(['product_id' => 1, 'movement_type' => 'in', 'quantity' => 5, 'reason' => 'teste']));
        $this->assertFalse($repository->update(1, ['quantity' => 9]));
        $this->assertFalse($repository->delete(1));
    }

    public function testFindReturnsDemoMovementById(): void
    {
        // Arrange
        $repository = new StockMovementRepository();

        // Act
        $movement = $repository->find(2);

        // Assert
        $this->assertNotNull($movement);
        $this->assertSame('Batata frita', $movement['product_name']);
    }
}
