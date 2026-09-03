<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Repositories\UserRepository;

$repository = new UserRepository();
$created = $repository->createAdminIfMissing('Administrador', 'admin@comandaflex.local', 'Admin@123');

if ($created) {
    echo "Admin seed criado com sucesso\n";
    echo "Email: admin@comandaflex.local\n";
    echo "Senha: Admin@123\n";
    exit(0);
}

echo "Admin seed já existe ou o banco não está disponível\n";
exit(0);
