<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\OrderItemRepository;
use ReflectionMethod;
use Tests\BaseTest;

final class OrderItemRepositoryTest extends BaseTest
{
    public function testAllReturnsDemoItemsWithOrderAndProductWhenOffline(): void
    {
        // Arrange
        $repository = new OrderItemRepository();

        // Act
        $items = $repository->all();

        // Assert
        $this->assertCount(3, $items);
        $this->assertArrayHasKey('table_name', $items[0]);
        $this->assertArrayHasKey('product_name', $items[0]);
        $this->assertArrayHasKey('subtotal', $items[0]);
    }

    public function testFindReturnsDemoItemById(): void
    {
        // Arrange
        $repository = new OrderItemRepository();

        // Act
        $item = $repository->find(3);

        // Assert
        $this->assertNotNull($item);
        $this->assertSame('Chopp 300ml', $item['product_name']);
    }

    public function testWritesAreRejectedWhenDatabaseIsOffline(): void
    {
        // Arrange
        $repository = new OrderItemRepository();

        // Act / Assert
        $this->assertFalse($repository->persists());
        $this->assertFalse($repository->create(['order_id' => 31, 'product_id' => 1, 'qty' => 2, 'unit_price' => 28.0]));
        $this->assertFalse($repository->update(1, ['qty' => 3, 'unit_price' => 28.0]));
        $this->assertFalse($repository->delete(1));
    }

    public function testSubtotalIsDerivedFromQuantityAndUnitPrice(): void
    {
        // Arrange
        $beforeSave = new ReflectionMethod(OrderItemRepository::class, 'beforeSave');

        // Act
        $data = $beforeSave->invoke(new OrderItemRepository(), [
            'order_id' => 31,
            'product_id' => 1,
            'qty' => 3,
            'unit_price' => 14.5,
            'subtotal' => 999.0,
        ]);

        // Assert — o valor enviado é ignorado; vale quantidade × preço
        $this->assertSame(43.5, $data['subtotal']);
    }

    public function testSubtotalIsLeftAloneWhenTheUpdateIsPartial(): void
    {
        // Arrange
        $beforeSave = new ReflectionMethod(OrderItemRepository::class, 'beforeSave');

        // Act
        $data = $beforeSave->invoke(new OrderItemRepository(), ['qty' => 4]);

        // Assert
        $this->assertFalse(array_key_exists('subtotal', $data));
    }

    public function testEveryWriteRecalculatesTheOrdersItTouches(): void
    {
        // Arrange
        $affected = new ReflectionMethod(OrderItemRepository::class, 'affectedOrders');
        $repository = new OrderItemRepository();

        // Act
        $created = $affected->invoke($repository, null, ['order_id' => 31]);
        $edited = $affected->invoke($repository, ['order_id' => 31], ['order_id' => 31, 'qty' => 4]);
        $moved = $affected->invoke($repository, ['order_id' => 31], ['order_id' => 32]);
        $deleted = $affected->invoke($repository, ['order_id' => 32], []);

        // Assert
        $this->assertSame([31], $created);
        $this->assertSame([31], $edited, 'mesma comanda é recalculada uma vez só');
        $this->assertSame([31, 32], $moved, 'item trocado de comanda recalcula as duas');
        $this->assertSame([32], $deleted);
    }
}
