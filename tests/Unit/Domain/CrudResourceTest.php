<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Patterns\Factory\CrudFactory;
use Tests\BaseTest;

/**
 * Validação compartilhada: as mesmas regras valem para qualquer CRUD,
 * porque vêm da descrição dos campos e não de código por tela.
 */
final class CrudResourceTest extends BaseTest
{
    public function testRejectsEmptyRequiredField(): void
    {
        // Arrange
        $resource = (new CrudFactory())->create('cardapio');

        // Act
        $result = $resource->validate(['name' => '', 'category' => 'Comida', 'price' => '10', 'stock_qty' => '5']);

        // Assert
        $this->assertNotEmpty($result['errors']);
    }

    public function testAcceptsValidInputAndCastsTypes(): void
    {
        // Arrange
        $resource = (new CrudFactory())->create('cardapio');

        // Act
        $result = $resource->validate(['name' => 'Pastel', 'category' => 'Comida', 'price' => '12,50', 'stock_qty' => '20']);

        // Assert
        $this->assertEmpty($result['errors']);
        $this->assertSame('Pastel', $result['data']['name']);
        $this->assertSame(12.5, $result['data']['price']);
        $this->assertSame(20, $result['data']['stock_qty']);
    }

    public function testRejectsValueOutsideTheSelectOptions(): void
    {
        // Arrange
        $resource = (new CrudFactory())->create('comandas');

        // Act
        $result = $resource->validate(['table_name' => 'Mesa 1', 'status' => 'inventado', 'total_value' => '10', 'notes' => '']);

        // Assert
        $this->assertNotEmpty($result['errors']);
    }

    public function testRejectsNonNumericNumberField(): void
    {
        // Arrange
        $resource = (new CrudFactory())->create('cardapio');

        // Act
        $result = $resource->validate(['name' => 'Pastel', 'category' => 'Comida', 'price' => '10', 'stock_qty' => 'muitos']);

        // Assert
        $this->assertNotEmpty($result['errors']);
    }

    public function testOptionalFieldMayStayEmpty(): void
    {
        // Arrange
        $resource = (new CrudFactory())->create('comandas');

        // Act
        $result = $resource->validate(['table_name' => 'Mesa 1', 'status' => 'open', 'total_value' => '10', 'notes' => '']);

        // Assert
        $this->assertEmpty($result['errors']);
        $this->assertSame('', $result['data']['notes']);
    }

    public function testLabelsAgreeWithTheGenderOfTheResource(): void
    {
        // Arrange
        $factory = new CrudFactory();

        // Act
        $product = $factory->create('cardapio');
        $order = $factory->create('comandas');

        // Assert
        $this->assertSame('Novo produto', $product->newLabel());
        $this->assertSame('Nova comanda', $order->newLabel());
        $this->assertSame('Produto cadastrado com sucesso.', $product->savedMessage(true));
        $this->assertSame('Comanda atualizada com sucesso.', $order->savedMessage(false));
    }

    public function testToggleFieldBecomesIntegerFlag(): void
    {
        // Arrange
        $resource = (new CrudFactory())->create('modulos');

        // Act
        $on = $resource->validate(['name' => 'X', 'code' => 'x', 'description' => '', 'enabled' => '1']);
        $off = $resource->validate(['name' => 'X', 'code' => 'x', 'description' => '']);

        // Assert
        $this->assertSame(1, $on['data']['enabled']);
        $this->assertSame(0, $off['data']['enabled']);
    }
}
