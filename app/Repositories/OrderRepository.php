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
            return $database->fetchAll('SELECT id, table_name, status, total_value, updated_at FROM orders WHERE status IN ("open", "preparing") ORDER BY updated_at DESC');
        }

        return $this->demoRows();
    }

    /**
     * Fila de produção derivada das comandas reais: o status da comanda é o
     * estágio na cozinha/bar.
     */
    public function productionQueue(): array
    {
        $database = Database::instance();

        if (! $database->connected()) {
            return [
                ['stage' => 'Recebido', 'table' => 'Mesa 07', 'item' => '1× Bruschetta'],
                ['stage' => 'Em preparo', 'table' => 'Mesa 03', 'item' => '2× Batata frita'],
                ['stage' => 'Pronto', 'table' => 'Mesa 02', 'item' => '1× Entrada'],
            ];
        }

        $rows = $database->fetchAll(
            'SELECT o.table_name, o.status, COUNT(i.id) AS item_count'
            . ' FROM orders o LEFT JOIN order_items i ON i.order_id = o.id'
            . ' WHERE o.status IN ("open", "preparing", "ready")'
            . ' GROUP BY o.id, o.table_name, o.status, o.updated_at'
            . ' ORDER BY o.updated_at ASC'
        );

        return array_map(static function (array $row): array {
            $count = (int) ($row['item_count'] ?? 0);

            return [
                'stage' => self::stageFor((string) ($row['status'] ?? '')),
                'table' => (string) ($row['table_name'] ?? ''),
                'item' => $count === 0
                    ? 'Comanda sem itens lançados'
                    : $count . ($count === 1 ? ' item lançado' : ' itens lançados'),
            ];
        }, $rows);
    }

    private static function stageFor(string $status): string
    {
        return match ($status) {
            'preparing' => 'Em preparo',
            'ready' => 'Pronto',
            default => 'Recebido',
        };
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
