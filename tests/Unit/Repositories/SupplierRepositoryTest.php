<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\SupplierRepository;
use Tests\BaseTest;

final class SupplierRepositoryTest extends BaseTest
{
    public function testAllReturnsDemoSuppliersWhenOffline(): void
    {
        // Arrange
        $repository = new SupplierRepository();

        // Act
        $suppliers = $repository->all();

        // Assert
        $this->assertCount(3, $suppliers);
        $this->assertArrayHasKey('document', $suppliers[0]);
        $this->assertArrayHasKey('active', $suppliers[0]);
    }

    public function testDemoCatalogHasAnInactiveSupplier(): void
    {
        // Arrange
        $repository = new SupplierRepository();

        // Act
        $inactive = array_filter($repository->all(), static fn (array $supplier): bool => ! $supplier['active']);

        // Assert
        $this->assertCount(1, $inactive);
    }

    public function testWritesAreRejectedWhenDatabaseIsOffline(): void
    {
        // Arrange
        $repository = new SupplierRepository();

        // Act / Assert
        $this->assertFalse($repository->persists());
        $this->assertFalse($repository->create(['name' => 'Laticínios Sul', 'document' => '', 'phone' => '(11) 3333-4000', 'active' => 1]));
        $this->assertFalse($repository->update(1, ['active' => 0]));
        $this->assertFalse($repository->delete(1));
    }
}
