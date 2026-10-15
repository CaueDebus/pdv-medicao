<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\PaymentMethodRepository;
use Tests\BaseTest;

final class PaymentMethodRepositoryTest extends BaseTest
{
    public function testAllReturnsDemoMethodsWhenOffline(): void
    {
        // Arrange
        $repository = new PaymentMethodRepository();

        // Act
        $methods = $repository->all();

        // Assert
        $this->assertCount(4, $methods);
        $this->assertArrayHasKey('kind', $methods[0]);
        $this->assertArrayHasKey('active', $methods[0]);
    }

    public function testKindOptionsCoverTheMigrationEnum(): void
    {
        // Arrange / Act
        $options = PaymentMethodRepository::kindOptions();

        // Assert
        $this->assertSame(['cash', 'credit', 'debit', 'pix', 'voucher'], array_keys($options));
    }

    public function testWritesAreRejectedWhenDatabaseIsOffline(): void
    {
        // Arrange
        $repository = new PaymentMethodRepository();

        // Act / Assert
        $this->assertFalse($repository->persists());
        $this->assertFalse($repository->create(['name' => 'Débito', 'kind' => 'debit', 'active' => 1]));
        $this->assertFalse($repository->update(4, ['active' => 1]));
        $this->assertFalse($repository->delete(4));
    }
}
