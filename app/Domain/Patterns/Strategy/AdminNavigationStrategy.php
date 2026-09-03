<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Strategy;

final class AdminNavigationStrategy implements NavigationVisibilityStrategy
{
    public function decorate(array $items, string $activePage): array
    {
        return array_map(static function (array $item) use ($activePage): array {
            $item['active'] = ($item['key'] ?? '') === $activePage;
            $item['locked'] = false;
            $item['reason'] = null;

            return $item;
        }, $items);
    }
}
