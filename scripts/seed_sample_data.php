<?php

declare(strict_types=1);

/*
 * Seed OPCIONAL de dados operacionais de exemplo (cardápio, comandas, itens,
 * mesas, clientes, estoque, fornecedores, estações e formas de pagamento),
 * para a aplicação já abrir com conteúdo em uma instalação nova. Não é
 * requisito do sistema: as telas funcionam vazias.
 *
 * Reaproveita os repositórios, então grava pelo mesmo caminho que a
 * interface usa — nada de SQL paralelo aqui.
 *
 * Cada tabela é semeada de forma independente e só se estiver vazia, então
 * o script é seguro de repetir e completa uma instalação que já tinha parte
 * dos dados (ex.: rodou o seed antes de as tabelas novas existirem).
 *
 * Uso: php scripts/seed_sample_data.php
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Repositories\AbstractCrudRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\DiningTableRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\ProductionStationRepository;
use App\Repositories\ProductRepository;
use App\Repositories\StockMovementRepository;
use App\Repositories\SupplierRepository;

if (! Database::instance()->connected()) {
    echo "MySQL indisponível. Configure o banco e rode as migrações antes do seed.\n";
    exit(1);
}

/**
 * Insere as linhas só se a tabela estiver vazia.
 *
 * @param array<int, array<string, mixed>> $rows
 */
$seed = static function (string $label, AbstractCrudRepository $repository, array $rows): void {
    if ($repository->all() !== []) {
        echo $label . ": já tem registros, nada inserido.\n";

        return;
    }

    $inserted = 0;
    foreach ($rows as $row) {
        $inserted += $repository->create($row) ? 1 : 0;
    }

    echo $label . ': ' . $inserted . " registros inseridos.\n";
};

$products = new ProductRepository();
$orders = new OrderRepository();

$seed('Cardápio', $products, [
    ['name' => 'Batata frita', 'category' => 'Comida', 'price' => 28.00, 'stock_qty' => 33],
    ['name' => 'Bruschetta', 'category' => 'Comida', 'price' => 24.00, 'stock_qty' => 18],
    ['name' => 'Hambúrguer artesanal', 'category' => 'Comida', 'price' => 39.00, 'stock_qty' => 25],
    ['name' => 'Porção de calabresa', 'category' => 'Comida', 'price' => 32.00, 'stock_qty' => 12],
    ['name' => 'Chopp 300ml', 'category' => 'Bebida', 'price' => 14.00, 'stock_qty' => 61],
    ['name' => 'Caipirinha', 'category' => 'Bebida', 'price' => 22.00, 'stock_qty' => 14],
    ['name' => 'Refrigerante lata', 'category' => 'Bebida', 'price' => 8.00, 'stock_qty' => 48],
    ['name' => 'Água mineral', 'category' => 'Bebida', 'price' => 5.00, 'stock_qty' => 7],
]);

// Sem total_value: o total de cada comanda sai da soma dos itens lançados abaixo.
$seed('Comandas', $orders, [
    ['table_name' => 'Mesa 03', 'status' => 'open', 'notes' => ''],
    ['table_name' => 'Mesa 07', 'status' => 'preparing', 'notes' => 'Sem cebola'],
    ['table_name' => 'Mesa 12', 'status' => 'preparing', 'notes' => ''],
    ['table_name' => 'Balcão 01', 'status' => 'ready', 'notes' => 'Retirada no balcão'],
    ['table_name' => 'Mesa 05', 'status' => 'closed', 'notes' => ''],
]);

// Os ids reais só existem depois do insert, então os catálogos são relidos.
$productsByName = [];
foreach ($products->all() as $product) {
    $productsByName[(string) $product['name']] = $product;
}

$ordersByTable = [];
foreach ($orders->all() as $order) {
    $ordersByTable[(string) $order['table_name']] ??= (int) $order['id'];
}

$movements = [];
foreach ([
    ['Batata frita', 'in', 40, 'Reposição do fornecedor'],
    ['Caipirinha', 'out', 6, 'Consumo do balcão'],
    ['Água mineral', 'out', 9, 'Consumo do salão'],
    ['Chopp 300ml', 'in', 30, 'Barril novo'],
    ['Porção de calabresa', 'adjustment', 2, 'Correção de inventário'],
] as [$productName, $type, $quantity, $reason]) {
    if (isset($productsByName[$productName])) {
        $movements[] = [
            'product_id' => (int) $productsByName[$productName]['id'],
            'movement_type' => $type,
            'quantity' => $quantity,
            'reason' => $reason,
        ];
    }
}

$seed('Estoque', new StockMovementRepository(), $movements);

// Itens das comandas de exemplo. O repositório calcula o subtotal (gancho
// beforeSave) e recalcula o total da comanda a cada item (gancho afterWrite):
// Mesa 03 = R$ 64, Mesa 07 = R$ 118, Mesa 12 = R$ 92, Balcão 01 = R$ 36, Mesa 05 = R$ 147.
$items = [];
foreach ([
    ['Mesa 07', 'Hambúrguer artesanal', 2], ['Mesa 07', 'Bruschetta', 1], ['Mesa 07', 'Refrigerante lata', 2],
    ['Mesa 03', 'Chopp 300ml', 2], ['Mesa 03', 'Batata frita', 1], ['Mesa 03', 'Refrigerante lata', 1],
    ['Mesa 12', 'Porção de calabresa', 1], ['Mesa 12', 'Batata frita', 1], ['Mesa 12', 'Caipirinha', 1], ['Mesa 12', 'Água mineral', 2],
    ['Balcão 01', 'Chopp 300ml', 1], ['Balcão 01', 'Caipirinha', 1],
    ['Mesa 05', 'Hambúrguer artesanal', 3], ['Mesa 05', 'Chopp 300ml', 1], ['Mesa 05', 'Refrigerante lata', 2],
] as [$table, $productName, $qty]) {
    if (isset($ordersByTable[$table], $productsByName[$productName])) {
        $items[] = [
            'order_id' => $ordersByTable[$table],
            'product_id' => (int) $productsByName[$productName]['id'],
            'qty' => $qty,
            'unit_price' => (float) $productsByName[$productName]['price'],
        ];
    }
}

$seed('Itens da comanda', new OrderItemRepository(), $items);

$seed('Mesas', new DiningTableRepository(), [
    ['name' => 'Mesa 01', 'area' => 'salao', 'seats' => 4, 'status' => 'free'],
    ['name' => 'Mesa 03', 'area' => 'salao', 'seats' => 4, 'status' => 'occupied'],
    ['name' => 'Mesa 05', 'area' => 'salao', 'seats' => 4, 'status' => 'free'],
    ['name' => 'Mesa 07', 'area' => 'varanda', 'seats' => 6, 'status' => 'occupied'],
    ['name' => 'Mesa 10', 'area' => 'varanda', 'seats' => 4, 'status' => 'reserved'],
    ['name' => 'Mesa 12', 'area' => 'varanda', 'seats' => 2, 'status' => 'occupied'],
    ['name' => 'Balcão 01', 'area' => 'balcao', 'seats' => 1, 'status' => 'occupied'],
]);

$seed('Clientes', new CustomerRepository(), [
    ['name' => 'Ana Souza', 'phone' => '(11) 98888-1020', 'email' => 'ana.souza@example.com', 'notes' => 'Prefere mesa na varanda'],
    ['name' => 'Bruno Lima', 'phone' => '(11) 97777-3040', 'email' => '', 'notes' => ''],
    ['name' => 'Carla Mendes', 'phone' => '(11) 96666-5060', 'email' => 'carla.mendes@example.com', 'notes' => 'Alergia a amendoim'],
    ['name' => 'Diego Alves', 'phone' => '(11) 95555-7080', 'email' => 'diego.alves@example.com', 'notes' => ''],
]);

$seed('Fornecedores', new SupplierRepository(), [
    ['name' => 'Distribuidora Bom Gole', 'document' => '12.345.678/0001-90', 'phone' => '(11) 3333-1000', 'active' => 1],
    ['name' => 'Hortifruti Central', 'document' => '', 'phone' => '(11) 3333-2000', 'active' => 1],
    ['name' => 'Frigorífico Serra', 'document' => '98.765.432/0001-10', 'phone' => '(11) 3333-3000', 'active' => 0],
]);

$seed('Estações', new ProductionStationRepository(), [
    ['name' => 'Cozinha quente', 'kind' => 'kitchen', 'capacity' => 6, 'active' => 1],
    ['name' => 'Cozinha fria', 'kind' => 'kitchen', 'capacity' => 4, 'active' => 1],
    ['name' => 'Bar principal', 'kind' => 'bar', 'capacity' => 8, 'active' => 1],
    ['name' => 'Bar da varanda', 'kind' => 'bar', 'capacity' => 4, 'active' => 0],
]);

$seed('Formas de pagamento', new PaymentMethodRepository(), [
    ['name' => 'Dinheiro', 'kind' => 'cash', 'active' => 1],
    ['name' => 'Pix', 'kind' => 'pix', 'active' => 1],
    ['name' => 'Crédito à vista', 'kind' => 'credit', 'active' => 1],
    ['name' => 'Débito', 'kind' => 'debit', 'active' => 1],
    ['name' => 'Vale-refeição', 'kind' => 'voucher', 'active' => 0],
]);

echo "Seed de exemplo concluído.\n";
exit(0);
