<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\ProductRepository;
use Tests\BaseTest;

final class ProductRepositoryTest extends BaseTest
{
    public function testAllReturnsDemoCatalogWhenOffline(): void
    {
        // Arrange
        $repository = new ProductRepository();

        // Act
        $products = $repository->all();

        // Assert
        $this->assertCount(3, $products);
        $this->assertArrayHasKey('name', $products[0]);
        $this->assertArrayHasKey('category', $products[0]);
        $this->assertArrayHasKey('price', $products[0]);
        $this->assertArrayHasKey('stock_qty', $products[0]);
    }

    public function testLowStockReturnsOnlyItemsBelowThreshold(): void
    {
        // Arrange
        $repository = new ProductRepository();

        // Act
        $lowStock = $repository->lowStock();

        // Assert
        $this->assertCount(1, $lowStock);
        $this->assertSame('Caipirinha', $lowStock[0]['name']);
        $this->assertTrue($lowStock[0]['stock_qty'] < 20);
    }
}
