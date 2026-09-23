<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Factory;

use App\Domain\Crud\CrudField;
use App\Domain\Crud\CrudResource;
use App\Domain\Variability\FeatureToggle;
use App\Repositories\ModuleRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\StockMovementRepository;

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
            'estoque' => $this->stockMovements(),
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
                CrudField::money('total_value', 'Total'),
                CrudField::text('notes', 'Observações', false),
            ],
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
