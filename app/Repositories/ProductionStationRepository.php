<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Estações de produção: CRUD sobre a tabela `production_stations`.
 *
 * A tela pertence ao módulo `production` (mapa app.features): desligar a
 * Produção também tira as Estações do produto.
 */
final class ProductionStationRepository extends AbstractCrudRepository
{
    protected function table(): string
    {
        return 'production_stations';
    }

    protected function columns(): array
    {
        return ['name', 'kind', 'capacity', 'active'];
    }

    protected function orderBy(): string
    {
        return 'name ASC';
    }

    protected function demoRows(): array
    {
        return [
            ['id' => 1, 'name' => 'Bar da varanda', 'kind' => 'bar', 'capacity' => 4, 'active' => false],
            ['id' => 2, 'name' => 'Bar principal', 'kind' => 'bar', 'capacity' => 8, 'active' => true],
            ['id' => 3, 'name' => 'Cozinha fria', 'kind' => 'kitchen', 'capacity' => 4, 'active' => true],
            ['id' => 4, 'name' => 'Cozinha quente', 'kind' => 'kitchen', 'capacity' => 6, 'active' => true],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function kindOptions(): array
    {
        return [
            'kitchen' => 'Cozinha',
            'bar' => 'Bar',
        ];
    }
}
