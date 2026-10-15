<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Itens da comanda: CRUD sobre a tabela `order_items`.
 *
 * A listagem faz JOIN com `orders` e `products` para a tela mostrar mesa e
 * produto em vez de ids crus. O subtotal não vem do formulário: é derivado
 * no gancho beforeSave(), então nunca fica diferente de quantidade × preço.
 * Depois de cada gravação, o gancho afterWrite() recalcula o total das
 * comandas afetadas, que passa a ser sempre a soma dos itens.
 */
final class OrderItemRepository extends AbstractCrudRepository
{
    protected function table(): string
    {
        return 'order_items';
    }

    protected function columns(): array
    {
        return ['order_id', 'product_id', 'qty', 'unit_price', 'subtotal'];
    }

    protected function selectSql(): string
    {
        return 'SELECT oi.id, oi.order_id, oi.product_id, oi.qty, oi.unit_price, oi.subtotal, o.table_name, p.name AS product_name'
            . ' FROM order_items oi'
            . ' INNER JOIN orders o ON o.id = oi.order_id'
            . ' INNER JOIN products p ON p.id = oi.product_id';
    }

    protected function idColumn(): string
    {
        return 'oi.id';
    }

    protected function orderBy(): string
    {
        return 'oi.created_at DESC';
    }

    protected function beforeSave(array $data): array
    {
        if (isset($data['qty'], $data['unit_price'])) {
            $data['subtotal'] = round((int) $data['qty'] * (float) $data['unit_price'], 2);
        }

        return $data;
    }

    protected function afterWrite(?array $before, array $data): void
    {
        $orders = new OrderRepository();

        foreach ($this->affectedOrders($before, $data) as $orderId) {
            $orders->recalculateTotal($orderId);
        }
    }

    /**
     * Comandas cujo total muda com a gravação: a de antes e a de depois,
     * já que na edição um item pode passar de uma comanda para outra.
     *
     * @param array<string, mixed>|null $before
     * @param array<string, mixed> $data
     * @return array<int, int>
     */
    private function affectedOrders(?array $before, array $data): array
    {
        $ids = [(int) ($before['order_id'] ?? 0), (int) ($data['order_id'] ?? 0)];

        return array_values(array_unique(array_filter($ids)));
    }

    protected function demoRows(): array
    {
        return [
            ['id' => 1, 'order_id' => 31, 'table_name' => 'Mesa 07', 'product_id' => 1, 'product_name' => 'Batata frita', 'qty' => 2, 'unit_price' => 28.00, 'subtotal' => 56.00],
            ['id' => 2, 'order_id' => 31, 'table_name' => 'Mesa 07', 'product_id' => 2, 'product_name' => 'Caipirinha', 'qty' => 1, 'unit_price' => 22.00, 'subtotal' => 22.00],
            ['id' => 3, 'order_id' => 32, 'table_name' => 'Mesa 03', 'product_id' => 3, 'product_name' => 'Chopp 300ml', 'qty' => 3, 'unit_price' => 14.00, 'subtotal' => 42.00],
        ];
    }
}
