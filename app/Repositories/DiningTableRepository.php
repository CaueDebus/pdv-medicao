<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Mesas: CRUD sobre a tabela `dining_tables` (mapa do salão e ocupação).
 */
final class DiningTableRepository extends AbstractCrudRepository
{
    protected function table(): string
    {
        return 'dining_tables';
    }

    protected function columns(): array
    {
        return ['name', 'area', 'seats', 'status'];
    }

    protected function orderBy(): string
    {
        return 'name ASC';
    }

    protected function demoRows(): array
    {
        return [
            ['id' => 1, 'name' => 'Mesa 01', 'area' => 'salao', 'seats' => 4, 'status' => 'free'],
            ['id' => 2, 'name' => 'Mesa 03', 'area' => 'salao', 'seats' => 4, 'status' => 'occupied'],
            ['id' => 3, 'name' => 'Mesa 07', 'area' => 'varanda', 'seats' => 6, 'status' => 'occupied'],
            ['id' => 4, 'name' => 'Mesa 12', 'area' => 'varanda', 'seats' => 2, 'status' => 'reserved'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function areaOptions(): array
    {
        return [
            'salao' => 'Salão',
            'varanda' => 'Varanda',
            'balcao' => 'Balcão',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            'free' => 'Livre',
            'occupied' => 'Ocupada',
            'reserved' => 'Reservada',
        ];
    }
}
