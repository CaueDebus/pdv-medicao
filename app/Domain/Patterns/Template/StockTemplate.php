<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Template;

/**
 * Tela de estoque: disponibilidade e pressão de reposição.
 */
final class StockTemplate extends AbstractScreenTemplate
{
    protected function baseScreen(array $context = []): array
    {
        return [
            'title' => 'Estoque',
            'subtitle' => 'Disponibilidade e alertas operacionais',
            'lead' => 'Leitura rápida do que ainda pode ser vendido e do que precisa reposição.',
            'route' => 'estoque',
            'context' => $context,
        ];
    }

    protected function metrics(array $context = []): array
    {
        $products = $this->rows($context, 'products');
        $lowStock = $this->rows($context, 'low_stock');
        $movements = $this->rows($context, 'movements');

        return [
            $this->metric('Itens críticos', count($lowStock), $lowStock === [] ? 'success' : 'danger'),
            $this->metric('Itens monitorados', count($products), 'info'),
            $this->metric('Unidades em estoque', (int) $this->sumOf($products, 'stock_qty'), 'success'),
            $this->metric('Movimentações', count($movements), 'warn'),
        ];
    }

    protected function highlights(array $context = []): array
    {
        $lowStock = $this->rows($context, 'low_stock');

        if ($lowStock === []) {
            return [$this->highlight('Estoque saudável', 'Nenhum insumo está abaixo do mínimo configurado.')];
        }

        return array_map(
            fn (array $product): array => $this->highlight(
                (string) ($product['name'] ?? ''),
                'Restam ' . (int) ($product['stock_qty'] ?? 0) . ' unidades — abaixo do mínimo de 20.',
            ),
            array_slice($lowStock, 0, 3),
        );
    }

    protected function sections(array $context = []): array
    {
        return [
            ['title' => 'Movimentações de entrada e saída', 'type' => 'movements'],
            ['title' => 'Alertas de reposição', 'type' => 'alerts'],
        ];
    }
}
