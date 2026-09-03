<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Strategy;

interface NavigationVisibilityStrategy
{
    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    public function decorate(array $items, string $activePage): array;
}
