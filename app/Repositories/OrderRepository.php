<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class OrderRepository
{
    public function openOrders(): array
    {
        $database = Database::instance();

        if ($database->connected()) {
            $items = $database->fetchAll('SELECT id, table_name, status, total_value, updated_at FROM orders WHERE status IN ("open", "preparing") ORDER BY updated_at DESC');

            if ($items !== []) {
                return $items;
            }
        }

        return [
            ['id' => 31, 'table_name' => 'Mesa 07', 'status' => 'preparing', 'total_value' => 118.00, 'updated_at' => 'há 4 min'],
            ['id' => 32, 'table_name' => 'Mesa 03', 'status' => 'open', 'total_value' => 64.00, 'updated_at' => 'há 8 min'],
            ['id' => 33, 'table_name' => 'Mesa 12', 'status' => 'preparing', 'total_value' => 92.00, 'updated_at' => 'há 13 min'],
        ];
    }

    public function productionQueue(): array
    {
        return [
            ['stage' => 'Recebido', 'table' => 'Mesa 07', 'item' => '1× Bruschetta'],
            ['stage' => 'Em preparo', 'table' => 'Mesa 03', 'item' => '2× Batata frita'],
            ['stage' => 'Pronto', 'table' => 'Mesa 02', 'item' => '1× Entrada'],
        ];
    }
}
