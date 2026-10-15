<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\DiningTableRepository;
use Tests\BaseTest;

final class DiningTableRepositoryTest extends BaseTest
{
    public function testAllReturnsDemoTablesWhenOffline(): void
    {
        // Arrange
        $repository = new DiningTableRepository();

        // Act
        $tables = $repository->all();

        // Assert
        $this->assertCount(4, $tables);
        $this->assertArrayHasKey('area', $tables[0]);
        $this->assertArrayHasKey('seats', $tables[0]);
        $this->assertArrayHasKey('status', $tables[0]);
    }

    public function testOptionsCoverTheMigrationEnums(): void
    {
        // Arrange / Act
        $areas = DiningTableRepository::areaOptions();
        $status = DiningTableRepository::statusOptions();

        // Assert
        $this->assertSame(['salao', 'varanda', 'balcao'], array_keys($areas));
        $this->assertSame(['free', 'occupied', 'reserved'], array_keys($status));
    }

    public function testWritesAreRejectedWhenDatabaseIsOffline(): void
    {
        // Arrange
        $repository = new DiningTableRepository();

        // Act / Assert
        $this->assertFalse($repository->persists());
        $this->assertFalse($repository->create(['name' => 'Mesa 20', 'area' => 'salao', 'seats' => 4, 'status' => 'free']));
        $this->assertFalse($repository->update(1, ['status' => 'occupied']));
        $this->assertFalse($repository->delete(1));
    }

    public function testFindReturnsDemoTableById(): void
    {
        // Arrange
        $repository = new DiningTableRepository();

        // Act
        $table = $repository->find(3);

        // Assert
        $this->assertNotNull($table);
        $this->assertSame('Mesa 07', $table['name']);
        $this->assertNull($repository->find(99));
    }
}
