<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Clientes: CRUD sobre a tabela `customers`.
 */
final class CustomerRepository extends AbstractCrudRepository
{
    protected function table(): string
    {
        return 'customers';
    }

    protected function columns(): array
    {
        return ['name', 'phone', 'email', 'notes'];
    }

    protected function orderBy(): string
    {
        return 'name ASC';
    }

    protected function demoRows(): array
    {
        return [
            ['id' => 1, 'name' => 'Ana Souza', 'phone' => '(11) 98888-1020', 'email' => 'ana.souza@example.com', 'notes' => 'Prefere mesa na varanda'],
            ['id' => 2, 'name' => 'Bruno Lima', 'phone' => '(11) 97777-3040', 'email' => '', 'notes' => ''],
            ['id' => 3, 'name' => 'Carla Mendes', 'phone' => '(11) 96666-5060', 'email' => 'carla.mendes@example.com', 'notes' => 'Alergia a amendoim'],
        ];
    }
}
