<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Patterns\Template\DashboardTemplate;
use App\Domain\Patterns\Template\MenuTemplate;
use App\Domain\Patterns\Template\ModulesTemplate;
use App\Domain\Patterns\Template\OrdersTemplate;
use App\Domain\Patterns\Template\ProductionTemplate;
use App\Domain\Patterns\Template\ReportsTemplate;
use App\Domain\Patterns\Template\SettingsTemplate;
use App\Domain\Patterns\Template\StockTemplate;
use Tests\BaseTest;

/**
 * Template Method: build() sempre devolve a mesma estrutura
 * (base + metrics + highlights + sections), variando só o conteúdo.
 *
 * Cada tela tem seu próprio template e calcula os indicadores a partir
 * do contexto recebido, em vez de repetir números fixos.
 */
final class ScreenTemplatesTest extends BaseTest
{
    /** @return array<string, mixed> */
    private function context(): array
    {
        return [
            'products' => [
                ['name' => 'Batata frita', 'category' => 'Comida', 'price' => 30.00, 'stock_qty' => 33],
                ['name' => 'Caipirinha', 'category' => 'Bebida', 'price' => 20.00, 'stock_qty' => 14],
            ],
            'low_stock' => [
                ['name' => 'Caipirinha', 'category' => 'Bebida', 'price' => 20.00, 'stock_qty' => 14],
            ],
            'orders' => [
                ['table_name' => 'Mesa 07', 'status' => 'preparing', 'total_value' => 118.00],
                ['table_name' => 'Mesa 03', 'status' => 'open', 'total_value' => 64.00],
            ],
            'queue' => [
                ['stage' => 'Recebido', 'table' => 'Mesa 07', 'item' => '1× Bruschetta'],
                ['stage' => 'Pronto', 'table' => 'Mesa 02', 'item' => '1× Entrada'],
            ],
            'movements' => [
                ['product_name' => 'Caipirinha', 'movement_type' => 'out', 'quantity' => 6],
            ],
            'modules' => [
                ['name' => 'Módulo Estoque', 'code' => 'stock', 'enabled' => true],
                ['name' => 'Integração hotel', 'code' => 'hotel_integration', 'enabled' => false],
            ],
            'features' => ['stock' => true, 'hotel_integration' => false],
        ];
    }

    public function testEveryTemplateBuildsTheSameStructure(): void
    {
        // Arrange
        $templates = [
            new DashboardTemplate(),
            new MenuTemplate(),
            new OrdersTemplate(),
            new StockTemplate(),
            new ProductionTemplate(),
            new ModulesTemplate(),
            new ReportsTemplate(),
            new SettingsTemplate(),
        ];

        // Act / Assert
        foreach ($templates as $template) {
            $screen = $template->build($this->context());
            $this->assertNotEmpty($screen['title']);
            $this->assertNotEmpty($screen['metrics']);
            $this->assertNotEmpty($screen['highlights']);
            $this->assertNotEmpty($screen['sections']);
        }
    }

    public function testEachScreenHasItsOwnTitleAndIndicators(): void
    {
        // Arrange
        $context = $this->context();

        // Act
        $menu = (new MenuTemplate())->build($context);
        $orders = (new OrdersTemplate())->build($context);
        $stock = (new StockTemplate())->build($context);

        // Assert — títulos distintos
        $this->assertSame('Cardápio', $menu['title']);
        $this->assertSame('Comandas', $orders['title']);
        $this->assertSame('Estoque', $stock['title']);

        // Assert — indicadores distintos entre as telas
        $this->assertStringNotContainsString(
            (string) $menu['metrics'][0]['label'],
            (string) $orders['metrics'][0]['label'],
        );
    }

    public function testMenuTemplateCountsProductsByCategory(): void
    {
        // Arrange / Act
        $screen = (new MenuTemplate())->build($this->context());
        $labels = array_column($screen['metrics'], 'value', 'label');

        // Assert
        $this->assertSame('2', $labels['Itens no cardápio']);
        $this->assertSame('1', $labels['Comidas']);
        $this->assertSame('1', $labels['Bebidas']);
        $this->assertSame('R$ 25,00', $labels['Preço médio']);
    }

    public function testOrdersTemplateCountsByStatusAndSumsOpenValue(): void
    {
        // Arrange / Act
        $screen = (new OrdersTemplate())->build($this->context());
        $labels = array_column($screen['metrics'], 'value', 'label');

        // Assert
        $this->assertSame('1', $labels['Comandas abertas']);
        $this->assertSame('1', $labels['Em preparo']);
        $this->assertSame('R$ 182,00', $labels['Valor em aberto']);
    }

    public function testStockTemplateFlagsCriticalItems(): void
    {
        // Arrange / Act
        $screen = (new StockTemplate())->build($this->context());
        $critical = array_values(array_filter(
            $screen['metrics'],
            static fn (array $metric): bool => $metric['label'] === 'Itens críticos',
        ));

        // Assert
        $this->assertSame('1', $critical[0]['value']);
        $this->assertSame('danger', $critical[0]['tone']);
        $this->assertSame('Caipirinha', $screen['highlights'][0]['title']);
    }

    public function testProductionTemplateCountsQueueByStage(): void
    {
        // Arrange / Act
        $screen = (new ProductionTemplate())->build($this->context());
        $labels = array_column($screen['metrics'], 'value', 'label');

        // Assert
        $this->assertSame('2', $labels['Na fila']);
        $this->assertSame('1', $labels['Recebidos']);
        $this->assertSame('1', $labels['Prontos']);
        $this->assertSame('0', $labels['Em preparo']);
    }

    public function testModulesTemplateSeparatesEnabledFromDisabled(): void
    {
        // Arrange / Act
        $screen = (new ModulesTemplate())->build($this->context());
        $labels = array_column($screen['metrics'], 'value', 'label');

        // Assert
        $this->assertSame('1', $labels['Módulos ligados']);
        $this->assertSame('1', $labels['Desligados']);
        $this->assertSame('Integração hotel', $screen['highlights'][0]['title']);
    }

    public function testTemplatesDegradeGracefullyWithoutData(): void
    {
        // Arrange / Act
        $stock = (new StockTemplate())->build();
        $orders = (new OrdersTemplate())->build();

        // Assert
        $this->assertSame('Estoque saudável', $stock['highlights'][0]['title']);
        $this->assertSame('Nenhuma comanda aberta', $orders['highlights'][0]['title']);
    }

    public function testDashboardTemplateCarriesContextThrough(): void
    {
        // Arrange
        $template = new DashboardTemplate();

        // Act
        $screen = $template->build(['dashboard' => ['open_orders' => 7]]);

        // Assert
        $this->assertSame(7, $screen['context']['dashboard']['open_orders']);
    }

    public function testReportsTemplateExposesManagerMetrics(): void
    {
        // Arrange / Act
        $screen = (new ReportsTemplate())->build();

        // Assert
        $this->assertSame('Relatórios', $screen['title']);
        $this->assertCount(3, $screen['metrics']);
    }
}
