<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\ModuleRepository;
use Tests\BaseTest;

final class ModuleRepositoryTest extends BaseTest
{
    public function testAllReturnsModuleCatalogWithEnabledFlag(): void
    {
        // Arrange
        $repository = new ModuleRepository();

        // Act
        $modules = $repository->all();

        // Assert
        $this->assertCount(6, $modules);
        $this->assertArrayHasKey('name', $modules[0]);
        $this->assertArrayHasKey('code', $modules[0]);
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
        $this->assertSame('hotel_integration', $integration[0]['code']);
    }

    public function testEveryModuleHasACodeForTheFeatureMap(): void
    {
        // Arrange
        $repository = new ModuleRepository();

        // Act
        $modules = $repository->all();

        // Assert
        foreach ($modules as $module) {
            $this->assertNotEmpty($module['code'] ?? '', 'módulo sem código não consegue habilitar tela');
        }
    }

    public function testWritesAreRejectedWhenDatabaseIsOffline(): void
    {
        // Arrange
        $repository = new ModuleRepository();

        // Act / Assert
        $this->assertFalse($repository->persists());
        $this->assertFalse($repository->create(['name' => 'Novo', 'code' => 'novo', 'description' => '', 'enabled' => 1]));
        $this->assertFalse($repository->update(1, ['enabled' => 0]));
        $this->assertFalse($repository->delete(1));
    }

    public function testFindReturnsDemoRowById(): void
    {
        // Arrange
        $repository = new ModuleRepository();

        // Act
        $module = $repository->find(3);

        // Assert
        $this->assertNotNull($module);
        $this->assertSame('stock', $module['code']);
    }
}
