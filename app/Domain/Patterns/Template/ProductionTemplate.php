<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Template;

/**
 * Tela de produção: fila de cozinha e bar por estágio.
 */
final class ProductionTemplate extends AbstractScreenTemplate
{
    protected function baseScreen(array $context = []): array
    {
        return [
            'title' => 'Produção',
            'subtitle' => 'Fila de cozinha e bar',
            'lead' => 'Visão da fila de preparo com estados legíveis para cozinha e bar.',
            'route' => 'producao',
            'context' => $context,
        ];
    }

    protected function metrics(array $context = []): array
    {
        $queue = $this->rows($context, 'queue');

        return [
            $this->metric('Na fila', count($queue), 'info'),
            $this->metric('Recebidos', $this->countWhere($queue, 'stage', ['Recebido']), 'warn'),
            $this->metric('Em preparo', $this->countWhere($queue, 'stage', ['Em preparo']), 'warn'),
            $this->metric('Prontos', $this->countWhere($queue, 'stage', ['Pronto']), 'success'),
        ];
    }

    protected function highlights(array $context = []): array
    {
        $queue = $this->rows($context, 'queue');

        if ($queue === []) {
            return [$this->highlight('Fila vazia', 'Nenhum item aguardando preparo na cozinha ou no bar.')];
        }

        return array_map(
            fn (array $item): array => $this->highlight(
                (string) ($item['table'] ?? ''),
                (string) ($item['item'] ?? '') . ' — ' . (string) ($item['stage'] ?? '') . '.',
            ),
            array_slice($queue, 0, 3),
        );
    }

    protected function sections(array $context = []): array
    {
        return [
            ['title' => 'Fila da cozinha', 'type' => 'kitchen'],
            ['title' => 'Fila do bar', 'type' => 'bar'],
        ];
    }
}
