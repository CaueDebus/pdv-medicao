<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\MigrationRunner;

$command = strtolower((string) ($argv[1] ?? 'up'));
$steps = (int) ($argv[2] ?? 1);

$runner = new MigrationRunner(Database::instance());

try {
    switch ($command) {
        case 'up':
        case 'migrate':
            $applied = $runner->up();
            echo $applied === []
                ? "Nenhuma migration pendente.\n"
                : 'Aplicadas: ' . implode(', ', array_map(static fn (array $migration): string => $migration['name'], $applied)) . "\n";
            break;

        case 'down':
        case 'rollback':
            $rolledBack = $runner->rollback($steps);
            echo $rolledBack === []
                ? "Nada para reverter.\n"
                : 'Rollback executado: ' . implode(', ', $rolledBack) . "\n";
            break;

        case 'status':
            $rows = $runner->status();
            foreach ($rows as $row) {
                echo sprintf(
                    "%s | %s | batch=%s | applied_at=%s\n",
                    $row['applied'] ? 'APLICADA ' : 'PENDENTE',
                    $row['name'],
                    $row['batch'] ?? '-',
                    $row['applied_at'] ?? '-'
                );
            }
            break;

        default:
            echo "Uso: php scripts/migrate.php [up|down|status] [steps]\n";
            exit(1);
    }
} catch (Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . "\n");
    exit(1);
}
