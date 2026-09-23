<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Cardápio: CRUD de produtos sobre a tabela `products`.
 */
final class ProductRepository extends AbstractCrudRepository
{
    protected function table(): string
    {
        return 'products';
    }

    protected function columns(): array
    {
        return ['name', 'category', 'price', 'stock_qty'];
    }

    protected function orderBy(): string
    {
        return 'name ASC';
    }

    protected function demoRows(): array
    {
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

    /**
     * Pares id => nome, usados pelos selects de outras telas (ex.: estoque).
     *
     * @return array<string, string>
     */
    public function options(): array
    {
        $options = [];

        foreach ($this->all() as $product) {
            $options[(string) ($product['id'] ?? '')] = (string) ($product['name'] ?? '');
        }

        return $options;
    }
}
