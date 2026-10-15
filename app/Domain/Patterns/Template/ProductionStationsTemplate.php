<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Template;

/**
 * Tela de estações de produção: postos da cozinha e do bar e sua capacidade.
 */
final class ProductionStationsTemplate extends AbstractScreenTemplate
{
    protected function baseScreen(array $context = []): array
    {
        return [
            'title' => 'Estações',
            'subtitle' => 'Postos da cozinha e do bar',
            'lead' => 'Estações que preparam os pedidos. Faz parte do módulo Produção: desligado o módulo, a tela sai do produto.',
            'route' => 'estacoes',
            'context' => $context,
        ];
    }

    protected function metrics(array $context = []): array
    {
        $stations = $this->crudRows($context);
        $operating = array_values(array_filter($stations, static fn (array $station): bool => (bool) ($station['active'] ?? false)));

        return [
            $this->metric('Em operação', count($operating), 'success'),
            $this->metric('Cozinha', $this->countWhere($stations, 'kind', ['kitchen']), 'info'),
            $this->metric('Bar', $this->countWhere($stations, 'kind', ['bar']), 'info'),
            $this->metric('Pedidos simultâneos', (int) $this->sumOf($operating, 'capacity'), 'warn'),
        ];
    }

    protected function highlights(array $context = []): array
    {
        $stations = $this->crudRows($context);

        if ($stations === []) {
            return [$this->highlight('Nenhuma estação cadastrada', 'Cadastre os postos da cozinha e do bar.')];
        }

        $stopped = array_values(array_filter($stations, static fn (array $station): bool => ! (bool) ($station['active'] ?? false)));

        if ($stopped === []) {
            return [$this->highlight('Todas operando', 'Todas as estações cadastradas estão em operação.')];
        }

        return array_map(
            fn (array $station): array => $this->highlight(
                (string) ($station['name'] ?? ''),
                'Fora de operação nesta instalação.',
            ),
            array_slice($stopped, 0, 3),
        );
    }

    protected function sections(array $context = []): array
    {
        return [
            ['title' => 'Estações da cozinha', 'type' => 'kitchen'],
            ['title' => 'Estações do bar', 'type' => 'bar'],
        ];
    }
}
