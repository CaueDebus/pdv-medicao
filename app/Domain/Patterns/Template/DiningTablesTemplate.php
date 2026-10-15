<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Template;

use App\Repositories\DiningTableRepository;

/**
 * Tela de mesas: ocupação do salão e lugares disponíveis.
 */
final class DiningTablesTemplate extends AbstractScreenTemplate
{
    protected function baseScreen(array $context = []): array
    {
        return [
            'title' => 'Mesas',
            'subtitle' => 'Salão, lugares e ocupação',
            'lead' => 'Mapa das mesas da casa, com a situação de cada uma para o atendimento.',
            'route' => 'mesas',
            'context' => $context,
        ];
    }

    protected function metrics(array $context = []): array
    {
        $tables = $this->crudRows($context);

        return [
            $this->metric('Mesas livres', $this->countWhere($tables, 'status', ['free']), 'success'),
            $this->metric('Ocupadas', $this->countWhere($tables, 'status', ['occupied']), 'warn'),
            $this->metric('Reservadas', $this->countWhere($tables, 'status', ['reserved']), 'info'),
            $this->metric('Lugares', (int) $this->sumOf($tables, 'seats'), 'info'),
        ];
    }

    protected function highlights(array $context = []): array
    {
        $tables = $this->crudRows($context);

        if ($tables === []) {
            return [$this->highlight('Nenhuma mesa cadastrada', 'Cadastre as mesas do salão para acompanhar a ocupação.')];
        }

        $busy = array_values(array_filter(
            $tables,
            static fn (array $table): bool => in_array((string) ($table['status'] ?? ''), ['occupied', 'reserved'], true),
        ));

        if ($busy === []) {
            return [$this->highlight('Salão livre', 'Nenhuma mesa ocupada ou reservada neste momento.')];
        }

        $status = DiningTableRepository::statusOptions();
        $areas = DiningTableRepository::areaOptions();

        return array_map(
            fn (array $table): array => $this->highlight(
                (string) ($table['name'] ?? ''),
                ($status[(string) ($table['status'] ?? '')] ?? '') . ' · ' . (int) ($table['seats'] ?? 0) . ' lugares · '
                    . ($areas[(string) ($table['area'] ?? '')] ?? '') . '.',
            ),
            array_slice($busy, 0, 3),
        );
    }

    protected function sections(array $context = []): array
    {
        return [
            ['title' => 'Mapa do salão', 'type' => 'floor'],
            ['title' => 'Reservas do dia', 'type' => 'reservations'],
        ];
    }
}
