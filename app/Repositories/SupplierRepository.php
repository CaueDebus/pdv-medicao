<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Fornecedores: CRUD sobre a tabela `suppliers`.
 *
 * A tela pertence ao módulo `stock` (mapa app.features): desligar o
 * Estoque também tira Fornecedores do produto.
 */
final class SupplierRepository extends AbstractCrudRepository
{
    protected function table(): string
    {
        return 'suppliers';
    }

    protected function columns(): array
    {
        return ['name', 'document', 'phone', 'active'];
    }

    protected function orderBy(): string
    {
        return 'name ASC';
    }

    protected function demoRows(): array
    {
        return [
            ['id' => 1, 'name' => 'Distribuidora Bom Gole', 'document' => '12.345.678/0001-90', 'phone' => '(11) 3333-1000', 'active' => true],
            ['id' => 2, 'name' => 'Hortifruti Central', 'document' => '', 'phone' => '(11) 3333-2000', 'active' => true],
            ['id' => 3, 'name' => 'Frigorífico Serra', 'document' => '98.765.432/0001-10', 'phone' => '(11) 3333-3000', 'active' => false],
        ];
    }
}
