<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Template;

/**
 * Tela de itens da comanda: o que foi lançado e quanto isso soma.
 */
final class OrderItemsTemplate extends AbstractScreenTemplate
{
    protected function baseScreen(array $context = []): array
    {
        return [
            'title' => 'Itens da comanda',
            'subtitle' => 'Produtos lançados por comanda',
            'lead' => 'Cada linha é um produto lançado em uma comanda, com o subtotal calculado na gravação.',
            'route' => 'itens',
            'context' => $context,
        ];
    }

    protected function metrics(array $context = []): array
    {
        $items = $this->crudRows($context);
        $orders = array_unique(array_map(static fn (array $item): string => (string) ($item['order_id'] ?? ''), $items));

        return [
            $this->metric('Itens lançados', count($items), 'info'),
            $this->metric('Unidades', (int) $this->sumOf($items, 'qty'), 'info'),
            $this->metric('Comandas com itens', count($orders), 'warn'),
            $this->metric('Valor lançado', $this->money($this->sumOf($items, 'subtotal')), 'success'),
        ];
    }

    protected function highlights(array $context = []): array
    {
        $items = $this->crudRows($context);

        if ($items === []) {
            return [$this->highlight('Nenhum item lançado', 'As comandas ainda não têm produtos lançados.')];
        }

        usort($items, static fn (array $a, array $b): int => (float) ($b['subtotal'] ?? 0) <=> (float) ($a['subtotal'] ?? 0));

        return array_map(
            fn (array $item): array => $this->highlight(
                (string) ($item['product_name'] ?? ''),
                (int) ($item['qty'] ?? 0) . '× na ' . (string) ($item['table_name'] ?? '') . ' — ' . $this->money((float) ($item['subtotal'] ?? 0)) . '.',
            ),
            array_slice($items, 0, 3),
        );
    }

    protected function sections(array $context = []): array
    {
        return [
            ['title' => 'Itens por comanda', 'type' => 'items'],
            ['title' => 'Subtotal calculado na gravação', 'type' => 'pricing'],
        ];
    }
}
