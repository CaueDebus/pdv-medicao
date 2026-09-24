<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Template;

/**
 * Tela de cardápio: composição do menu e alerta de reposição.
 */
final class MenuTemplate extends AbstractScreenTemplate
{
    protected function baseScreen(array $context = []): array
    {
        return [
            'title' => 'Cardápio',
            'subtitle' => 'Catálogo e preços do menu',
            'lead' => 'Composição do menu ofertado nesta instalação, com preços e disponibilidade.',
            'route' => 'cardapio',
            'context' => $context,
        ];
    }

    protected function metrics(array $context = []): array
    {
        $products = $this->rows($context, 'products');
        $total = count($products);
        $average = $total > 0 ? $this->sumOf($products, 'price') / $total : 0.0;

        return [
            $this->metric('Itens no cardápio', $total, 'success'),
            $this->metric('Comidas', $this->countWhere($products, 'category', ['Comida']), 'info'),
            $this->metric('Bebidas', $this->countWhere($products, 'category', ['Bebida']), 'info'),
            $this->metric('Preço médio', $this->money($average), 'warn'),
        ];
    }

    protected function highlights(array $context = []): array
    {
        $lowStock = $this->rows($context, 'low_stock');

        if ($lowStock === []) {
            return [$this->highlight('Cardápio completo', 'Nenhum item do menu está abaixo do estoque mínimo.')];
        }

        return array_map(
            fn (array $product): array => $this->highlight(
                (string) ($product['name'] ?? ''),
                'Restam ' . (int) ($product['stock_qty'] ?? 0) . ' unidades — avaliar reposição antes de manter no menu.',
            ),
            array_slice($lowStock, 0, 3),
        );
    }

    protected function sections(array $context = []): array
    {
        return [
            ['title' => 'Catálogo de produtos', 'type' => 'catalog'],
            ['title' => 'Categorias ofertadas', 'type' => 'categories'],
        ];
    }
}
