<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<string, string>> */
    private array $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function resolve(string $requestedPage, string $defaultPage): string
    {
        return array_key_exists($requestedPage, $this->routes) ? $requestedPage : $defaultPage;
    }

    public function routeMeta(string $page): array
    {
        return $this->routes[$page] ?? [];
    }

    public function all(): array
    {
        return $this->routes;
    }
}
