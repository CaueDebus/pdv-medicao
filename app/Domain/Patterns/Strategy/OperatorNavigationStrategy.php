<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Strategy;

final class OperatorNavigationStrategy implements NavigationVisibilityStrategy
{
    public function decorate(array $items, string $activePage): array
    {
        return array_map(static function (array $item) use ($activePage): array {
            $item['active'] = ($item['key'] ?? '') === $activePage;
            $item['locked'] = in_array($item['key'] ?? '', ['relatorios', 'configuracoes'], true);
            $item['reason'] = $item['locked'] ? 'restrito ao gerente/admin' : null;

            return $item;
        }, $items);
    }
}
