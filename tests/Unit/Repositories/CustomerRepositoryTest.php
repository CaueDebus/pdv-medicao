<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\CustomerRepository;
use Tests\BaseTest;

final class CustomerRepositoryTest extends BaseTest
{
    public function testAllReturnsDemoCustomersWhenOffline(): void
    {
        // Arrange
        $repository = new CustomerRepository();

        // Act
        $customers = $repository->all();

        // Assert
        $this->assertCount(3, $customers);
        $this->assertArrayHasKey('phone', $customers[0]);
        $this->assertArrayHasKey('email', $customers[0]);
        $this->assertArrayHasKey('notes', $customers[0]);
    }

    public function testWritesAreRejectedWhenDatabaseIsOffline(): void
    {
        // Arrange
        $repository = new CustomerRepository();

        // Act / Assert
        $this->assertFalse($repository->persists());
        $this->assertFalse($repository->create(['name' => 'Davi', 'phone' => '(11) 90000-0000', 'email' => '', 'notes' => '']));
        $this->assertFalse($repository->update(1, ['phone' => '(11) 91111-1111']));
        $this->assertFalse($repository->delete(1));
    }

    public function testFindReturnsDemoCustomerById(): void
    {
        // Arrange
        $repository = new CustomerRepository();

        // Act
        $customer = $repository->find(3);

        // Assert
        $this->assertNotNull($customer);
        $this->assertSame('Carla Mendes', $customer['name']);
    }
}
