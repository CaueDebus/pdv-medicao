<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Factory;

use App\Domain\Crud\CrudField;
use App\Domain\Crud\CrudResource;
use App\Domain\Variability\FeatureToggle;
use App\Repositories\CustomerRepository;
use App\Repositories\DiningTableRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\ProductionStationRepository;
use App\Repositories\ProductRepository;
use App\Repositories\StockMovementRepository;
use App\Repositories\SupplierRepository;

/**
 * Segunda aplicação do Factory no projeto (a primeira é a ScreenFactory).
 *
 * Enquanto a ScreenFactory decide qual template monta a tela, esta decide
 * qual recurso CRUD atende a rota: repositório, rótulos e campos. O
 * CrudController não conhece produto, comanda, estoque ou módulo — recebe
 * o CrudResource pronto e opera em cima dele.
 */
final class CrudFactory
{
    public function __construct(private readonly ?FeatureToggle $features = null)
    {
    }

    public function create(string $page): ?CrudResource
    {
        return match ($page) {
            'cardapio' => $this->products(),
            'comandas' => $this->orders(),
            'itens' => $this->orderItems(),
            'mesas' => $this->diningTables(),
            'clientes' => $this->customers(),
            'estoque' => $this->stockMovements(),
            'fornecedores' => $this->suppliers(),
            'estacoes' => $this->productionStations(),
            'pagamentos' => $this->paymentMethods(),
            'modulos' => $this->modules(),
            default => null,
        };
    }

    public function supports(string $page): bool
    {
        return $this->create($page) !== null;
    }

    private function products(): CrudResource
    {
        return new CrudResource(
            key: 'cardapio',
            label: 'Produtos do cardápio',
            singular: 'produto',
            repository: new ProductRepository(),
            fields: [
                CrudField::text('name', 'Nome do item'),
                CrudField::select('category', 'Categoria', $this->categoryOptions()),
                CrudField::money('price', 'Preço'),
                CrudField::number('stock_qty', 'Estoque'),
            ],
            feminine: false,
        );
    }

    private function orders(): CrudResource
    {
        return new CrudResource(
            key: 'comandas',
            label: 'Comandas',
            singular: 'comanda',
            repository: new OrderRepository(),
            fields: [
                CrudField::text('table_name', 'Mesa / cliente'),
                CrudField::select('status', 'Status', OrderRepository::statusOptions()),
                CrudField::computed('total_value', 'Total', 'money', 'Soma dos itens lançados na comanda; não é digitado.'),
                CrudField::text('notes', 'Observações', false),
            ],
        );
    }

    private function orderItems(): CrudResource
    {
        return new CrudResource(
            key: 'itens',
            label: 'Itens lançados nas comandas',
            singular: 'item',
            repository: new OrderItemRepository(),
            fields: [
                CrudField::select('order_id', 'Comanda', (new OrderRepository())->options()),
                CrudField::select('product_id', 'Produto', (new ProductRepository())->options()),
                CrudField::number('qty', 'Quantidade'),
                CrudField::money('unit_price', 'Preço unitário', 'O subtotal é calculado na gravação: quantidade × preço.'),
            ],
            feminine: false,
        );
    }

    private function diningTables(): CrudResource
    {
        return new CrudResource(
            key: 'mesas',
            label: 'Mesas do salão',
            singular: 'mesa',
            repository: new DiningTableRepository(),
            fields: [
                CrudField::text('name', 'Identificação', true, 'Ex.: Mesa 07 ou Balcão 01. Não pode repetir.'),
                CrudField::select('area', 'Área', DiningTableRepository::areaOptions()),
                CrudField::number('seats', 'Lugares'),
                CrudField::select('status', 'Situação', DiningTableRepository::statusOptions()),
            ],
        );
    }

    private function customers(): CrudResource
    {
        return new CrudResource(
            key: 'clientes',
            label: 'Clientes',
            singular: 'cliente',
            repository: new CustomerRepository(),
            fields: [
                CrudField::text('name', 'Nome'),
                CrudField::text('phone', 'Telefone'),
                CrudField::text('email', 'E-mail', false),
                CrudField::text('notes', 'Observações', false, 'Preferências ou restrições, como alergias.'),
            ],
            feminine: false,
        );
    }

    private function stockMovements(): CrudResource
    {
        return new CrudResource(
            key: 'estoque',
            label: 'Movimentações de estoque',
            singular: 'movimentação',
            repository: new StockMovementRepository(),
            fields: [
                CrudField::select('product_id', 'Produto', (new ProductRepository())->options()),
                CrudField::select('movement_type', 'Tipo', StockMovementRepository::typeOptions()),
                CrudField::number('quantity', 'Quantidade'),
                CrudField::text('reason', 'Motivo', false),
            ],
        );
    }

    /*
     * Fornecedores, estações e formas de pagamento são cadastros de
     * gestão: o operador consulta, mas quem altera é gerente ou admin.
     */

    private function suppliers(): CrudResource
    {
        return new CrudResource(
            key: 'fornecedores',
            label: 'Fornecedores',
            singular: 'fornecedor',
            repository: new SupplierRepository(),
            fields: [
                CrudField::text('name', 'Razão social'),
                CrudField::text('document', 'CNPJ', false),
                CrudField::text('phone', 'Telefone'),
                CrudField::toggle('active', 'Ativo', 'Desligue para manter o cadastro sem usar o fornecedor.'),
            ],
            feminine: false,
            writeRoles: ['manager', 'admin'],
        );
    }

    private function productionStations(): CrudResource
    {
        return new CrudResource(
            key: 'estacoes',
            label: 'Estações de produção',
            singular: 'estação',
            repository: new ProductionStationRepository(),
            fields: [
                CrudField::text('name', 'Nome da estação'),
                CrudField::select('kind', 'Setor', ProductionStationRepository::kindOptions()),
                CrudField::number('capacity', 'Pedidos simultâneos'),
                CrudField::toggle('active', 'Em operação'),
            ],
            writeRoles: ['manager', 'admin'],
        );
    }

    private function paymentMethods(): CrudResource
    {
        return new CrudResource(
            key: 'pagamentos',
            label: 'Formas de pagamento',
            singular: 'forma de pagamento',
            repository: new PaymentMethodRepository(),
            fields: [
                CrudField::text('name', 'Nome'),
                CrudField::select('kind', 'Tipo', PaymentMethodRepository::kindOptions()),
                CrudField::toggle('active', 'Aceita no caixa'),
            ],
            writeRoles: ['manager', 'admin'],
        );
    }

    private function modules(): CrudResource
    {
        return new CrudResource(
            key: 'modulos',
            label: 'Módulos do produto',
            singular: 'módulo',
            repository: new ModuleRepository(),
            fields: [
                CrudField::text('name', 'Nome do módulo'),
                CrudField::text('code', 'Código', true, 'Usado pelo mapa app.features para habilitar telas.'),
                CrudField::text('description', 'Descrição', false),
                CrudField::toggle('enabled', 'Habilitado', 'Desligar remove as telas deste módulo do produto.'),
            ],
            feminine: false,
            destroyRoles: ['admin'],
            // Alterar o catálogo de módulos muda o produto entregue:
            // é decisão de administração, não de operação.
            writeRoles: ['admin'],
        );
    }

    /**
     * Variabilidade fina: as categorias ofertadas no cardápio dependem de
     * quais módulos de venda estão ligados nesta instalação.
     *
     * @return array<string, string>
     */
    private function categoryOptions(): array
    {
        $features = $this->features ?? FeatureToggle::fromModules();
        $options = [];

        if ($features->enabled('menu_food')) {
            $options['Comida'] = 'Comida';
        }

        if ($features->enabled('menu_drink')) {
            $options['Bebida'] = 'Bebida';
        }

        return $options === [] ? ['Comida' => 'Comida'] : $options;
    }
}
