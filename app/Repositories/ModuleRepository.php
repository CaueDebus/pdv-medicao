<?php

declare(strict_types=1);

namespace App\Repositories;

final class ModuleRepository
{
    public function all(): array
    {
        return [
            ['name' => 'Módulo Comida', 'enabled' => true, 'description' => 'Fluxo de preparo e mesas'],
            ['name' => 'Módulo Bebida', 'enabled' => true, 'description' => 'Balcão e bar'],
            ['name' => 'Integração hotel', 'enabled' => false, 'description' => 'Conector externo pendente'],
        ];
    }
}
