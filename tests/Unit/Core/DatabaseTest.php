<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\Database;
use Tests\BaseTest;

/**
 * O bootstrap dos testes aponta o MySQL para uma porta fechada, então
 * estes casos validam o comportamento de "banco indisponível" — que é o
 * modo demo documentado no README.
 */
final class DatabaseTest extends BaseTest
{
    public function testInstanceIsSingleton(): void
    {
        // Arrange / Act
        $first = Database::instance();
        $second = Database::instance();

        // Assert
        $this->assertSame($first, $second);
    }

    public function testConnectedIsFalseInTestEnvironment(): void
    {
        // Arrange / Act
        $connected = Database::instance()->connected();

        // Assert
        $this->assertFalse($connected, 'Testes rodam em modo demo, sem MySQL');
    }

    public function testFetchAllReturnsEmptyArrayWhenDisconnected(): void
    {
        // Arrange
        $database = Database::instance();

        // Act
        $rows = $database->fetchAll('SELECT 1');

        // Assert
        $this->assertSame([], $rows);
    }

    public function testFetchOneReturnsNullWhenDisconnected(): void
    {
        // Arrange
        $database = Database::instance();

        // Act
        $row = $database->fetchOne('SELECT 1');

        // Assert
        $this->assertNull($row);
    }
}
