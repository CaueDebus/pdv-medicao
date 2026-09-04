<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\DashboardRepository;
use Tests\BaseTest;

final class DashboardRepositoryTest extends BaseTest
{
    public function testSummaryReturnsDemoOpenOrdersWhenOffline(): void
    {
        // Arrange
        $repository = new DashboardRepository();

        // Act
        $summary = $repository->summary();

        // Assert
        $this->assertArrayHasKey('open_orders', $summary);
        $this->assertSame(18, $summary['open_orders']);
    }
}
