<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\Config;
use Tests\BaseTest;

final class ConfigTest extends BaseTest
{
    public function testInstanceIsSingleton(): void
    {
        // Arrange / Act
        $first = Config::instance();
        $second = Config::instance();

        // Assert
        $this->assertSame($first, $second, 'Config deve devolver sempre a mesma instância');
    }

    public function testGetResolvesNestedPath(): void
    {
        // Arrange
        $config = Config::instance();

        // Act
        $name = $config->get('app.name');

        // Assert
        $this->assertSame('ComandaFlex', $name);
    }

    public function testGetReturnsRoutesArray(): void
    {
        // Arrange
        $config = Config::instance();

        // Act
        $routes = $config->get('app.routes', []);

        // Assert
        $this->assertArrayHasKey('dashboard', $routes);
        $this->assertArrayHasKey('configuracoes', $routes);
    }

    public function testGetReturnsDefaultForUnknownPath(): void
    {
        // Arrange
        $config = Config::instance();

        // Act
        $value = $config->get('app.inexistente.profundo', 'padrao');

        // Assert
        $this->assertSame('padrao', $value);
    }
}
