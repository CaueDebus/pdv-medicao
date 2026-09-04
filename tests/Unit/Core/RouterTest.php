<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\Router;
use Tests\BaseTest;

final class RouterTest extends BaseTest
{
    /** @return array<string, array<string, string>> */
    private function routes(): array
    {
        return [
            'dashboard' => ['label' => 'Dashboard', 'description' => 'Resumo'],
            'cardapio' => ['label' => 'Cardápio', 'description' => 'Produtos'],
        ];
    }

    public function testResolveReturnsRequestedPageWhenKnown(): void
    {
        // Arrange
        $router = new Router($this->routes());

        // Act
        $page = $router->resolve('cardapio', 'dashboard');

        // Assert
        $this->assertSame('cardapio', $page);
    }

    public function testResolveFallsBackToDefaultWhenUnknown(): void
    {
        // Arrange
        $router = new Router($this->routes());

        // Act
        $page = $router->resolve('pagina-que-nao-existe', 'dashboard');

        // Assert
        $this->assertSame('dashboard', $page);
    }

    public function testRouteMetaReturnsMetadataForKnownRoute(): void
    {
        // Arrange
        $router = new Router($this->routes());

        // Act
        $meta = $router->routeMeta('cardapio');

        // Assert
        $this->assertSame('Cardápio', $meta['label']);
    }

    public function testRouteMetaReturnsEmptyArrayForUnknownRoute(): void
    {
        // Arrange
        $router = new Router($this->routes());

        // Act
        $meta = $router->routeMeta('inexistente');

        // Assert
        $this->assertSame([], $meta);
    }
}
