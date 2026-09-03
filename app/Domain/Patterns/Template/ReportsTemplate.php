<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Template;

final class ReportsTemplate extends AbstractScreenTemplate
{
    protected function baseScreen(array $context = []): array
    {
        return [
            'title' => 'Relatórios',
            'subtitle' => 'Indicadores e acompanhamento gerencial',
            'lead' => 'Tela de leitura para gerência e administração, com foco em visão consolidada.',
            'route' => 'relatorios',
            'context' => $context,
        ];
    }

    protected function metrics(array $context = []): array
    {
        return [
            ['label' => 'Ticket médio', 'value' => 'R$ 64,20', 'tone' => 'success'],
            ['label' => 'Cancelamentos', 'value' => '2', 'tone' => 'danger'],
            ['label' => 'Itens vendidos', 'value' => '318', 'tone' => 'info'],
        ];
    }

    protected function highlights(array $context = []): array
    {
        return [
            ['title' => 'Hoje', 'text' => 'Performance acima da média dos últimos 7 dias.'],
            ['title' => 'Semana', 'text' => 'Produtos de bebida lideram o faturamento.'],
        ];
    }

    protected function sections(array $context = []): array
    {
        return [
            ['title' => 'Receita por categoria', 'type' => 'chart'],
            ['title' => 'Resumo operacional', 'type' => 'summary'],
        ];
    }
}
