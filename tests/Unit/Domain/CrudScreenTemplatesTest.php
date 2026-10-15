<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Patterns\Template\CustomersTemplate;
use App\Domain\Patterns\Template\DiningTablesTemplate;
use App\Domain\Patterns\Template\OrderItemsTemplate;
use App\Domain\Patterns\Template\PaymentMethodsTemplate;
use App\Domain\Patterns\Template\ProductionStationsTemplate;
use App\Domain\Patterns\Template\SuppliersTemplate;
use Tests\BaseTest;

/**
 * Template Method nas telas de cadastro: cada uma calcula os indicadores
 * sobre as mesmas linhas que a listagem de CRUD exibe (contexto `crud.rows`).
 */
final class CrudScreenTemplatesTest extends BaseTest
{
    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<string, mixed>
     */
    private function crud(array $rows): array
    {
        return ['crud' => ['rows' => $rows]];
    }

    /**
     * @param array<string, mixed> $screen
     * @return array<string, string>
     */
    private function metricValues(array $screen): array
    {
        return array_column($screen['metrics'], 'value', 'label');
    }

    public function testEveryCrudTemplateBuildsTheSharedStructureEvenWithoutData(): void
    {
        // Arrange
        $templates = [
            new OrderItemsTemplate(),
            new DiningTablesTemplate(),
            new CustomersTemplate(),
            new SuppliersTemplate(),
            new PaymentMethodsTemplate(),
            new ProductionStationsTemplate(),
        ];

        // Act / Assert
        foreach ($templates as $template) {
            $screen = $template->build();
            $this->assertNotEmpty($screen['title']);
            $this->assertNotEmpty($screen['metrics']);
            $this->assertCount(1, $screen['highlights'], 'sem dados, mostra o estado vazio');
            $this->assertNotEmpty($screen['sections']);
        }
    }

    public function testOrderItemsTemplateSumsUnitsOrdersAndValue(): void
    {
        // Arrange
        $context = $this->crud([
            ['order_id' => 31, 'table_name' => 'Mesa 07', 'product_name' => 'Batata frita', 'qty' => 2, 'subtotal' => 56.00],
            ['order_id' => 31, 'table_name' => 'Mesa 07', 'product_name' => 'Caipirinha', 'qty' => 1, 'subtotal' => 22.00],
            ['order_id' => 32, 'table_name' => 'Mesa 03', 'product_name' => 'Chopp 300ml', 'qty' => 3, 'subtotal' => 42.00],
        ]);

        // Act
        $screen = (new OrderItemsTemplate())->build($context);
        $values = $this->metricValues($screen);

        // Assert
        $this->assertSame('3', $values['Itens lançados']);
        $this->assertSame('6', $values['Unidades']);
        $this->assertSame('2', $values['Comandas com itens']);
        $this->assertSame('R$ 120,00', $values['Valor lançado']);
        $this->assertSame('Batata frita', $screen['highlights'][0]['title'], 'destaque começa pelo maior subtotal');
    }

    public function testDiningTablesTemplateCountsBySituationAndSumsSeats(): void
    {
        // Arrange
        $context = $this->crud([
            ['name' => 'Mesa 01', 'area' => 'salao', 'seats' => 4, 'status' => 'free'],
            ['name' => 'Mesa 03', 'area' => 'salao', 'seats' => 4, 'status' => 'occupied'],
            ['name' => 'Mesa 12', 'area' => 'varanda', 'seats' => 2, 'status' => 'reserved'],
        ]);

        // Act
        $screen = (new DiningTablesTemplate())->build($context);
        $values = $this->metricValues($screen);

        // Assert
        $this->assertSame('1', $values['Mesas livres']);
        $this->assertSame('1', $values['Ocupadas']);
        $this->assertSame('1', $values['Reservadas']);
        $this->assertSame('10', $values['Lugares']);
        $this->assertCount(2, $screen['highlights'], 'destaca só as mesas ocupadas ou reservadas');
        $this->assertStringContainsString('Ocupada', $screen['highlights'][0]['text']);
    }

    public function testDiningTablesTemplateReportsAFreeFloor(): void
    {
        // Arrange
        $context = $this->crud([['name' => 'Mesa 01', 'area' => 'salao', 'seats' => 4, 'status' => 'free']]);

        // Act
        $screen = (new DiningTablesTemplate())->build($context);

        // Assert
        $this->assertSame('Salão livre', $screen['highlights'][0]['title']);
    }

    public function testCustomersTemplateCountsContactsAndHighlightsNotes(): void
    {
        // Arrange
        $context = $this->crud([
            ['name' => 'Ana Souza', 'phone' => '1', 'email' => 'ana@example.com', 'notes' => 'Prefere varanda'],
            ['name' => 'Bruno Lima', 'phone' => '2', 'email' => '', 'notes' => ''],
        ]);

        // Act
        $screen = (new CustomersTemplate())->build($context);
        $values = $this->metricValues($screen);

        // Assert
        $this->assertSame('2', $values['Clientes cadastrados']);
        $this->assertSame('1', $values['Com e-mail']);
        $this->assertSame('1', $values['Com observação']);
        $this->assertSame('Ana Souza', $screen['highlights'][0]['title']);
    }

    public function testSuppliersTemplateSeparatesActiveFromInactive(): void
    {
        // Arrange — `active` chega como bool no demo e como int do MySQL
        $context = $this->crud([
            ['name' => 'Bom Gole', 'document' => '12.345.678/0001-90', 'active' => true],
            ['name' => 'Hortifruti', 'document' => '', 'active' => 1],
            ['name' => 'Frigorífico Serra', 'document' => '', 'active' => 0],
        ]);

        // Act
        $screen = (new SuppliersTemplate())->build($context);
        $values = $this->metricValues($screen);

        // Assert
        $this->assertSame('3', $values['Fornecedores']);
        $this->assertSame('2', $values['Ativos']);
        $this->assertSame('1', $values['Inativos']);
        $this->assertSame('1', $values['Com CNPJ']);
        $this->assertSame('Frigorífico Serra', $screen['highlights'][0]['title']);
    }

    public function testPaymentMethodsTemplateListsWhatTheCashierAccepts(): void
    {
        // Arrange
        $context = $this->crud([
            ['name' => 'Dinheiro', 'kind' => 'cash', 'active' => 1],
            ['name' => 'Pix', 'kind' => 'pix', 'active' => 1],
            ['name' => 'Vale-refeição', 'kind' => 'voucher', 'active' => 0],
        ]);

        // Act
        $screen = (new PaymentMethodsTemplate())->build($context);
        $values = $this->metricValues($screen);

        // Assert
        $this->assertSame('2', $values['Aceitas no caixa']);
        $this->assertSame('1', $values['Desativadas']);
        $this->assertSame('Dinheiro, Pix.', $screen['highlights'][0]['text']);
        $this->assertSame('Vale-refeição', $screen['highlights'][1]['title']);
    }

    public function testPaymentMethodsTemplateWarnsWhenNothingIsAccepted(): void
    {
        // Arrange
        $context = $this->crud([['name' => 'Dinheiro', 'kind' => 'cash', 'active' => 0]]);

        // Act
        $screen = (new PaymentMethodsTemplate())->build($context);
        $accepted = array_values(array_filter($screen['metrics'], static fn (array $metric): bool => $metric['label'] === 'Aceitas no caixa'));

        // Assert
        $this->assertSame('danger', $accepted[0]['tone']);
        $this->assertSame('Nenhuma forma ativa', $screen['highlights'][0]['title']);
    }

    public function testProductionStationsTemplateCountsOnlyOperatingCapacity(): void
    {
        // Arrange
        $context = $this->crud([
            ['name' => 'Cozinha quente', 'kind' => 'kitchen', 'capacity' => 6, 'active' => 1],
            ['name' => 'Bar principal', 'kind' => 'bar', 'capacity' => 8, 'active' => 1],
            ['name' => 'Bar da varanda', 'kind' => 'bar', 'capacity' => 4, 'active' => 0],
        ]);

        // Act
        $screen = (new ProductionStationsTemplate())->build($context);
        $values = $this->metricValues($screen);

        // Assert
        $this->assertSame('2', $values['Em operação']);
        $this->assertSame('1', $values['Cozinha']);
        $this->assertSame('2', $values['Bar']);
        $this->assertSame('14', $values['Pedidos simultâneos'], 'estação parada não soma capacidade');
        $this->assertSame('Bar da varanda', $screen['highlights'][0]['title']);
    }
}
