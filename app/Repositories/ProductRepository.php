<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class ProductRepository
{
    public function all(): array
    {
        $database = Database::instance();

        if ($database->connected()) {
            $items = $database->fetchAll('SELECT id, name, category, price, stock_qty FROM products ORDER BY name ASC');

            if ($items !== []) {
                return $items;
            }
        }

        return [
            ['id' => 1, 'name' => 'Batata frita', 'category' => 'Comida', 'price' => 28.00, 'stock_qty' => 33],
            ['id' => 2, 'name' => 'Caipirinha', 'category' => 'Bebida', 'price' => 22.00, 'stock_qty' => 14],
            ['id' => 3, 'name' => 'Chopp 300ml', 'category' => 'Bebida', 'price' => 14.00, 'stock_qty' => 61],
        ];
    }

    public function lowStock(): array
    {
        return array_values(array_filter($this->all(), static fn (array $product): bool => ($product['stock_qty'] ?? 0) < 20));
    }
}
