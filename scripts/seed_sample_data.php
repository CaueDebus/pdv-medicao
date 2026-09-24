<?php

declare(strict_types=1);

/*
 * Seed OPCIONAL de dados operacionais de exemplo (cardápio, comandas e
 * movimentações), para a aplicação já abrir com conteúdo em uma instalação
 * nova. Não é requisito do sistema: as telas funcionam vazias.
 *
 * Reaproveita os repositórios, então grava pelo mesmo caminho que a
 * interface usa — nada de SQL paralelo aqui.
 *
 * Uso: php scripts/seed_sample_data.php
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\StockMovementRepository;

if (! Database::instance()->connected()) {
    echo "MySQL indisponível. Configure o banco e rode as migrações antes do seed.\n";
    exit(1);
}

$products = new ProductRepository();
$orders = new OrderRepository();
$movements = new StockMovementRepository();

if ($products->all() !== []) {
    echo "O cardápio já tem registros. Nada foi inserido para evitar duplicidade.\n";
    exit(0);
}

$catalog = [
    ['name' => 'Batata frita', 'category' => 'Comida', 'price' => 28.00, 'stock_qty' => 33],
    ['name' => 'Bruschetta', 'category' => 'Comida', 'price' => 24.00, 'stock_qty' => 18],
    ['name' => 'Hambúrguer artesanal', 'category' => 'Comida', 'price' => 39.00, 'stock_qty' => 25],
    ['name' => 'Porção de calabresa', 'category' => 'Comida', 'price' => 32.00, 'stock_qty' => 12],
    ['name' => 'Chopp 300ml', 'category' => 'Bebida', 'price' => 14.00, 'stock_qty' => 61],
    ['name' => 'Caipirinha', 'category' => 'Bebida', 'price' => 22.00, 'stock_qty' => 14],
    ['name' => 'Refrigerante lata', 'category' => 'Bebida', 'price' => 8.00, 'stock_qty' => 48],
    ['name' => 'Água mineral', 'category' => 'Bebida', 'price' => 5.00, 'stock_qty' => 7],
];

foreach ($catalog as $product) {
    $products->create($product);
}

echo 'Cardápio: ' . count($catalog) . " produtos inseridos.\n";

$comandas = [
    ['table_name' => 'Mesa 03', 'status' => 'open', 'total_value' => 64.00, 'notes' => ''],
    ['table_name' => 'Mesa 07', 'status' => 'preparing', 'total_value' => 118.00, 'notes' => 'Sem cebola'],
    ['table_name' => 'Mesa 12', 'status' => 'preparing', 'total_value' => 92.00, 'notes' => ''],
    ['table_name' => 'Balcão 01', 'status' => 'ready', 'total_value' => 36.00, 'notes' => 'Retirada no balcão'],
    ['table_name' => 'Mesa 05', 'status' => 'closed', 'total_value' => 147.00, 'notes' => ''],
];

foreach ($comandas as $order) {
    $orders->create($order);
}

echo 'Comandas: ' . count($comandas) . " registros inseridos.\n";

// Os ids reais só existem depois do insert, então o catálogo é relido.
$idsByName = [];
foreach ($products->all() as $product) {
    $idsByName[(string) $product['name']] = (int) $product['id'];
}

$stockEntries = [
    ['Batata frita', 'in', 40, 'Reposição do fornecedor'],
    ['Caipirinha', 'out', 6, 'Consumo do balcão'],
    ['Água mineral', 'out', 9, 'Consumo do salão'],
    ['Chopp 300ml', 'in', 30, 'Barril novo'],
    ['Porção de calabresa', 'adjustment', 2, 'Correção de inventário'],
];

$inserted = 0;
foreach ($stockEntries as [$productName, $type, $quantity, $reason]) {
    if (! isset($idsByName[$productName])) {
        continue;
    }

    $movements->create([
        'product_id' => $idsByName[$productName],
        'movement_type' => $type,
        'quantity' => $quantity,
        'reason' => $reason,
    ]);
    $inserted++;
}

echo 'Estoque: ' . $inserted . " movimentações inseridas.\n";
echo "Seed de exemplo concluído.\n";
exit(0);
