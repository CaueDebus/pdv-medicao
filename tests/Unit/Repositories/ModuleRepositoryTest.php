<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\ModuleRepository;
use Tests\BaseTest;

final class ModuleRepositoryTest extends BaseTest
{
    public function testAllReturnsModulesWithEnabledFlag(): void
    {
        // Arrange
        $repository = new ModuleRepository();

        // Act
        $modules = $repository->all();

        // Assert
        $this->assertCount(3, $modules);
        $this->assertArrayHasKey('name', $modules[0]);
        $this->assertArrayHasKey('enabled', $modules[0]);
        $this->assertArrayHasKey('description', $modules[0]);
    }

    public function testIntegrationModuleIsDisabledByDefault(): void
    {
        // Arrange
        $repository = new ModuleRepository();

        // Act
        $modules = $repository->all();
        $integration = array_values(array_filter(
            $modules,
            static fn (array $module): bool => $module['name'] === 'Integração hotel',
        ));

        // Assert
        $this->assertCount(1, $integration);
        $this->assertFalse($integration[0]['enabled']);
    }
}
