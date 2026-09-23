<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Módulos: CRUD sobre a tabela `modules`.
 *
 * Esta tabela é o catálogo de pontos de variação do produto (ver
 * App\Domain\Variability\FeatureToggle): ligar/desligar um módulo aqui
 * muda quais telas o ComandaFlex entrega, sem tocar em código.
 */
final class ModuleRepository extends AbstractCrudRepository
{
    protected function table(): string
    {
        return 'modules';
    }

    protected function columns(): array
    {
        return ['name', 'code', 'description', 'enabled'];
    }

    protected function orderBy(): string
    {
        return 'name ASC';
    }

    protected function demoRows(): array
    {
        return [
            ['id' => 1, 'name' => 'Módulo Comida', 'code' => 'menu_food', 'enabled' => true, 'description' => 'Fluxo de preparo e mesas'],
            ['id' => 2, 'name' => 'Módulo Bebida', 'code' => 'menu_drink', 'enabled' => true, 'description' => 'Balcão e bar'],
            ['id' => 3, 'name' => 'Módulo Estoque', 'code' => 'stock', 'enabled' => true, 'description' => 'Movimentações e alertas de reposição'],
            ['id' => 4, 'name' => 'Módulo Produção', 'code' => 'production', 'enabled' => true, 'description' => 'Fila de cozinha e bar'],
            ['id' => 5, 'name' => 'Módulo Relatórios', 'code' => 'reports', 'enabled' => true, 'description' => 'Indicadores gerenciais'],
            ['id' => 6, 'name' => 'Integração hotel', 'code' => 'hotel_integration', 'enabled' => false, 'description' => 'Conector externo pendente'],
        ];
    }
}
