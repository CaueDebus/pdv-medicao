<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Estoque: CRUD de movimentações sobre a tabela `stock_movements`.
 *
 * A listagem faz JOIN com `products` para mostrar o nome do insumo em vez
 * do id cru — por isso a coluna de identidade é qualificada.
 */
final class StockMovementRepository extends AbstractCrudRepository
{
    protected function table(): string
    {
        return 'stock_movements';
    }

    protected function columns(): array
    {
        return ['product_id', 'movement_type', 'quantity', 'reason'];
    }

    protected function selectSql(): string
    {
        return 'SELECT sm.id, sm.product_id, sm.movement_type, sm.quantity, sm.reason, p.name AS product_name'
            . ' FROM stock_movements sm INNER JOIN products p ON p.id = sm.product_id';
    }

    protected function idColumn(): string
    {
        return 'sm.id';
    }

    protected function orderBy(): string
    {
        return 'sm.created_at DESC';
    }

    protected function demoRows(): array
    {
        return [
            ['id' => 1, 'product_id' => 2, 'product_name' => 'Caipirinha', 'movement_type' => 'out', 'quantity' => 6, 'reason' => 'Consumo do balcão'],
            ['id' => 2, 'product_id' => 1, 'product_name' => 'Batata frita', 'movement_type' => 'in', 'quantity' => 40, 'reason' => 'Reposição do fornecedor'],
            ['id' => 3, 'product_id' => 2, 'product_name' => 'Caipirinha', 'movement_type' => 'adjustment', 'quantity' => 2, 'reason' => 'Correção de inventário'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return [
            'in' => 'Entrada',
            'out' => 'Saída',
            'adjustment' => 'Ajuste',
        ];
    }
}
