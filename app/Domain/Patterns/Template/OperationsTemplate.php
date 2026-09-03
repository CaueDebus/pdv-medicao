<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Template;

final class OperationsTemplate extends AbstractScreenTemplate
{
    protected function baseScreen(array $context = []): array
    {
        return [
            'title' => 'Operações',
            'subtitle' => 'Cardápio, comandas e produção',
            'lead' => 'Agrupa o núcleo transacional do sistema com foco em velocidade e leitura imediata.',
            'route' => 'comandas',
            'context' => $context,
        ];
    }

    protected function metrics(array $context = []): array
    {
        return [
            ['label' => 'Itens no cardápio', 'value' => '42', 'tone' => 'success'],
            ['label' => 'Pedidos ativos', 'value' => '12', 'tone' => 'info'],
            ['label' => 'Tempo médio', 'value' => '11m', 'tone' => 'warn'],
        ];
    }

    protected function highlights(array $context = []): array
    {
        return [
            ['title' => 'Mesa 03', 'text' => '2 pratos, 1 bebida e observação ativa.'],
            ['title' => 'Mesa 12', 'text' => 'Pedido enviado e aguardando conferência.'],
        ];
    }

    protected function sections(array $context = []): array
    {
        return [
            ['title' => 'Comandas em andamento', 'type' => 'orders'],
            ['title' => 'Fila de produção', 'type' => 'production'],
        ];
    }
}
