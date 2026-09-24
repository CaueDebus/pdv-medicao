<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Template;

final class DashboardTemplate extends AbstractScreenTemplate
{
    protected function baseScreen(array $context = []): array
    {
        return [
            'title' => 'Dashboard',
            'subtitle' => 'Resumo operacional do ComandaFlex',
            'lead' => 'Tela principal para acompanhar vendas, produção, estoque e atalhos do fluxo de atendimento.',
            'route' => 'dashboard',
            'context' => $context,
        ];
    }

    protected function metrics(array $context = []): array
    {
        $orders = $this->rows($context, 'orders');
        $openCount = (int) ($context['dashboard']['open_orders'] ?? 0);
        $critical = count($this->rows($context, 'low_stock'));
        $revenue = $this->sumOf(
            array_filter($orders, static fn (array $order): bool => (string) ($order['status'] ?? '') === 'closed'),
            'total_value',
        );

        return [
            $this->metric('Comandas em andamento', $openCount, 'info'),
            $this->metric('Pedidos em preparo', $this->countWhere($orders, 'status', ['preparing']), 'warn'),
            $this->metric('Itens críticos', $critical, $critical === 0 ? 'success' : 'danger'),
            $this->metric('Receita fechada', $this->money($revenue), 'success'),
        ];
    }

    protected function highlights(array $context = []): array
    {
        $highlights = [];

        foreach (array_slice($this->rows($context, 'open_orders'), 0, 2) as $order) {
            $highlights[] = $this->highlight(
                (string) ($order['table_name'] ?? ''),
                'Comanda em ' . (string) ($order['status'] ?? '') . ' — ' . $this->money((float) ($order['total_value'] ?? 0)) . '.',
            );
        }

        foreach (array_slice($this->rows($context, 'low_stock'), 0, 1) as $product) {
            $highlights[] = $this->highlight(
                (string) ($product['name'] ?? ''),
                'Estoque abaixo do mínimo: restam ' . (int) ($product['stock_qty'] ?? 0) . ' unidades.',
            );
        }

        return $highlights === []
            ? [$this->highlight('Operação tranquila', 'Nenhuma comanda em aberto e nenhum item crítico de estoque.')]
            : $highlights;
    }

    protected function sections(array $context = []): array
    {
        return [
            ['title' => 'Atalhos do dia', 'type' => 'shortcuts'],
            ['title' => 'Movimentações recentes', 'type' => 'timeline'],
        ];
    }
}
