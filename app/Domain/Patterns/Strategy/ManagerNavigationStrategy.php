<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Strategy;

/**
 * Terceira estratégia de navegação: o gerente enxerga a operação inteira e
 * os relatórios, mas não administra o ambiente.
 */
final class ManagerNavigationStrategy implements NavigationVisibilityStrategy
{
    public function decorate(array $items, string $activePage): array
    {
        return array_map(static function (array $item) use ($activePage): array {
            $item['active'] = ($item['key'] ?? '') === $activePage;
            $item['locked'] = ($item['key'] ?? '') === 'configuracoes';
            $item['reason'] = $item['locked'] ? 'restrito ao admin' : null;

            return $item;
        }, $items);
    }
}
