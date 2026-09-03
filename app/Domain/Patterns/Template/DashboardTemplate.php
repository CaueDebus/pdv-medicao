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
        return [
            ['label' => 'Comandas abertas', 'value' => '18', 'tone' => 'info'],
            ['label' => 'Pedidos em preparo', 'value' => '7', 'tone' => 'warn'],
            ['label' => 'Itens críticos', 'value' => '3', 'tone' => 'danger'],
            ['label' => 'Receita do dia', 'value' => 'R$ 4.280', 'tone' => 'success'],
        ];
    }

    protected function highlights(array $context = []): array
    {
        return [
            ['title' => 'Mesa 07', 'text' => 'Aguardando fechamento da comanda.'],
            ['title' => 'Limão', 'text' => 'Estoque abaixo do mínimo configurado.'],
            ['title' => 'Cozinha', 'text' => 'Fila com prioridade para pedidos antigos.'],
        ];
    }

    protected function sections(array $context = []): array
    {
        return [
            ['title' => 'Atalhos do dia', 'type' => 'shortcuts'],
            ['title' => 'Movimentações recentes', 'type' => 'timeline'],
        ];
    }
}
