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

        // Act / Assert — dez telas de CRUD, duas por integrante da equipe
        $pages = ['cardapio', 'comandas', 'itens', 'mesas', 'clientes', 'estoque', 'fornecedores', 'estacoes', 'pagamentos', 'modulos'];
        $this->assertCount(10, $pages);

        foreach ($pages as $page) {
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

    public function testManagementCatalogsAreWrittenOnlyByManagerAndAdmin(): void
    {
        // Arrange
        $factory = new CrudFactory();

        // Act / Assert
        foreach (['fornecedores', 'estacoes', 'pagamentos'] as $page) {
            $resource = $factory->create($page);
            $this->assertFalse($resource->canWrite('operator'), $page . ': operador só consulta');
            $this->assertTrue($resource->canWrite('manager'), $page . ': gerente altera');
            $this->assertTrue($resource->canWrite('admin'), $page . ': admin altera');
        }
    }

    public function testOperationalCatalogsAreWrittenByEveryRole(): void
    {
        // Arrange
        $factory = new CrudFactory();

        // Act / Assert
        foreach (['itens', 'mesas', 'clientes'] as $page) {
            $resource = $factory->create($page);
            $this->assertTrue($resource->canWrite('operator'), $page);
            $this->assertTrue($resource->canWrite('manager'), $page);
            $this->assertTrue($resource->canWrite('admin'), $page);
            $this->assertFalse($resource->canDestroy('operator'), $page . ': operador não exclui');
        }
    }

    public function testOrderItemSelectsOfferExistingOrdersAndProducts(): void
    {
        // Arrange
        $factory = new CrudFactory();

        // Act
        $items = $factory->create('itens');
        $order = $items->field('order_id');
        $product = $items->field('product_id');

        // Assert — as opções vêm dos repositórios de comandas e produtos
        $this->assertSame('Mesa 07 · #31', $order->options['31']);
        $this->assertSame('Batata frita', $product->options['1']);
        $this->assertNull($items->field('subtotal'), 'subtotal não é campo do formulário');
    }

    public function testOrderItemValidationRejectsAnUnknownOrder(): void
    {
        // Arrange
        $items = (new CrudFactory())->create('itens');

        // Act
        $result = $items->validate(['order_id' => '999', 'product_id' => '1', 'qty' => '2', 'unit_price' => '28,00']);

        // Assert
        $this->assertNotEmpty($result['errors']);
    }
}
