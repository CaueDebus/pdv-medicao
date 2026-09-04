<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\UserRepository;
use Tests\BaseTest;

/**
 * Sem MySQL, o UserRepository não inventa dados: retorna null / false.
 * A autenticação de desenvolvimento é responsabilidade do AuthController.
 */
final class UserRepositoryTest extends BaseTest
{
    public function testFindByEmailReturnsNullWhenOffline(): void
    {
        // Arrange
        $repository = new UserRepository();

        // Act
        $user = $repository->findByEmail('admin@comandaflex.local');

        // Assert
        $this->assertNull($user);
    }

    public function testFindByIdReturnsNullWhenOffline(): void
    {
        // Arrange
        $repository = new UserRepository();

        // Act
        $user = $repository->findById(1);

        // Assert
        $this->assertNull($user);
    }

    public function testCreateAdminIfMissingReturnsFalseWhenOffline(): void
    {
        // Arrange
        $repository = new UserRepository();

        // Act
        $created = $repository->createAdminIfMissing('Administrador', 'admin@comandaflex.local', 'Admin@123');

        // Assert
        $this->assertFalse($created);
    }
}
