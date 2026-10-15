<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\ProductionStationRepository;
use Tests\BaseTest;

final class ProductionStationRepositoryTest extends BaseTest
{
    public function testAllReturnsDemoStationsWhenOffline(): void
    {
        // Arrange
        $repository = new ProductionStationRepository();

        // Act
        $stations = $repository->all();

        // Assert
        $this->assertCount(4, $stations);
        $this->assertArrayHasKey('kind', $stations[0]);
        $this->assertArrayHasKey('capacity', $stations[0]);
        $this->assertArrayHasKey('active', $stations[0]);
    }

    public function testKindOptionsCoverTheMigrationEnum(): void
    {
        // Arrange / Act
        $options = ProductionStationRepository::kindOptions();

        // Assert
        $this->assertSame(['kitchen', 'bar'], array_keys($options));
    }

    public function testWritesAreRejectedWhenDatabaseIsOffline(): void
    {
        // Arrange
        $repository = new ProductionStationRepository();

        // Act / Assert
        $this->assertFalse($repository->persists());
        $this->assertFalse($repository->create(['name' => 'Confeitaria', 'kind' => 'kitchen', 'capacity' => 2, 'active' => 1]));
        $this->assertFalse($repository->update(1, ['active' => 1]));
        $this->assertFalse($repository->delete(1));
    }
}
