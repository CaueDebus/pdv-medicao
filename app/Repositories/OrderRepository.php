<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Comandas: CRUD sobre a tabela `orders`, mais as leituras operacionais
 * já usadas pelo dashboard e pela fila de produção.
 */
final class OrderRepository extends AbstractCrudRepository
{
    protected function table(): string
    {
        return 'orders';
    }

    protected function columns(): array
    {
        return ['table_name', 'status', 'total_value', 'notes'];
    }

    protected function orderBy(): string
    {
        return 'updated_at DESC';
    }

    protected function demoRows(): array
    {
        return [
            ['id' => 31, 'table_name' => 'Mesa 07', 'status' => 'preparing', 'total_value' => 118.00, 'notes' => 'Sem cebola', 'updated_at' => 'há 4 min'],
            ['id' => 32, 'table_name' => 'Mesa 03', 'status' => 'open', 'total_value' => 64.00, 'notes' => '', 'updated_at' => 'há 8 min'],
            ['id' => 33, 'table_name' => 'Mesa 12', 'status' => 'preparing', 'total_value' => 92.00, 'notes' => '', 'updated_at' => 'há 13 min'],
        ];
    }

    public function openOrders(): array
    {
        $database = Database::instance();

        if ($database->connected()) {
            $items = $database->fetchAll('SELECT id, table_name, status, total_value, updated_at FROM orders WHERE status IN ("open", "preparing") ORDER BY updated_at DESC');

            if ($items !== []) {
                return $items;
            }
        }

        return $this->demoRows();
    }

    public function productionQueue(): array
    {
        return [
            ['stage' => 'Recebido', 'table' => 'Mesa 07', 'item' => '1× Bruschetta'],
            ['stage' => 'Em preparo', 'table' => 'Mesa 03', 'item' => '2× Batata frita'],
            ['stage' => 'Pronto', 'table' => 'Mesa 02', 'item' => '1× Entrada'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            'open' => 'Aberta',
            'preparing' => 'Em preparo',
            'ready' => 'Pronta',
            'closed' => 'Fechada',
            'cancelled' => 'Cancelada',
        ];
    }
}
