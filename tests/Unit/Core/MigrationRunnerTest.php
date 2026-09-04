<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\Database;
use App\Core\MigrationRunner;
use RuntimeException;
use Tests\BaseTest;

/**
 * Sem MySQL, o runner deve falhar de forma explícita em vez de fingir sucesso.
 */
final class MigrationRunnerTest extends BaseTest
{
    public function testUpThrowsWhenDatabaseUnavailable(): void
    {
        // Arrange
        $runner = new MigrationRunner(Database::instance());

        // Act / Assert
        $this->assertThrows(
            RuntimeException::class,
            static fn () => $runner->up(),
            'up() sem conexão deve lançar RuntimeException',
        );
    }

    public function testStatusThrowsWhenDatabaseUnavailable(): void
    {
        // Arrange
        $runner = new MigrationRunner(Database::instance());

        // Act / Assert
        $this->assertThrows(
            RuntimeException::class,
            static fn () => $runner->status(),
        );
    }

    public function testRollbackThrowsWhenDatabaseUnavailable(): void
    {
        // Arrange
        $runner = new MigrationRunner(Database::instance());

        // Act / Assert
        $this->assertThrows(
            RuntimeException::class,
            static fn () => $runner->rollback(1),
        );
    }
}
