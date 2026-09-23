<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Crud\CrudResource;
use App\Domain\Patterns\Factory\CrudFactory;
use App\Domain\Variability\FeatureToggle;
use Tests\BaseTest;

/**
 * Factory (2ª aplicação): a rota decide qual recurso CRUD é montado.
 */
final class CrudFactoryTest extends BaseTest
{
    public function testCreatesAResourceForEveryCrudRoute(): void
    {
        // Arrange
        $factory = new CrudFactory();

        // Act / Assert
        foreach (['cardapio', 'comandas', 'estoque', 'modulos'] as $page) {
            $resource = $factory->create($page);
            $this->assertInstanceOf(CrudResource::class, $resource);
            $this->assertSame($page, $resource->key);
            $this->assertNotEmpty($resource->fields);
        }
    }

    public function testReturnsNullForScreensWithoutCrud(): void
    {
        // Arrange
        $factory = new CrudFactory();

        // Act / Assert
        $this->assertNull($factory->create('dashboard'));
        $this->assertNull($factory->create('producao'));
        $this->assertFalse($factory->supports('relatorios'));
        $this->assertTrue($factory->supports('cardapio'));
    }

    public function testProductCategoriesFollowTheEnabledSalesModules(): void
    {
        // Arrange
        $features = new FeatureToggle([
            ['code' => 'menu_food', 'enabled' => true],
            ['code' => 'menu_drink', 'enabled' => false],
        ]);
        $factory = new CrudFactory($features);

        // Act
        $category = $factory->create('cardapio')?->field('category');

        // Assert
        $this->assertNotNull($category);
        $this->assertArrayHasKey('Comida', $category->options);
        $this->assertCount(1, $category->options, 'módulo bebida desligado não oferta a categoria');
    }

    public function testProductCategoriesIncludeBothModulesWhenEnabled(): void
    {
        // Arrange
        $features = new FeatureToggle([
            ['code' => 'menu_food', 'enabled' => true],
            ['code' => 'menu_drink', 'enabled' => true],
        ]);
        $factory = new CrudFactory($features);

        // Act
        $category = $factory->create('cardapio')?->field('category');

        // Assert
        $this->assertCount(2, $category->options);
        $this->assertArrayHasKey('Bebida', $category->options);
    }

    public function testOnlyAdminDestroysModules(): void
    {
        // Arrange
        $factory = new CrudFactory();

        // Act
        $modules = $factory->create('modulos');

        // Assert
        $this->assertTrue($modules->canDestroy('admin'));
        $this->assertFalse($modules->canDestroy('manager'));
        $this->assertFalse($modules->canDestroy('operator'));
    }

    public function testOperatorCannotDestroyProducts(): void
    {
        // Arrange
        $factory = new CrudFactory();

        // Act
        $products = $factory->create('cardapio');

        // Assert
        $this->assertTrue($products->canDestroy('manager'));
        $this->assertTrue($products->canDestroy('admin'));
        $this->assertFalse($products->canDestroy('operator'));
    }
}
