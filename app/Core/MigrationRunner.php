<?php

declare(strict_types=1);

namespace App\Core;

use DirectoryIterator;
use RuntimeException;

final class MigrationRunner
{
    private const REPOSITORY_TABLE = 'schema_migrations';

    public function __construct(private readonly Database $database)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function up(): array
    {
        $this->ensureRepository();

        $pending = array_values(array_filter($this->allMigrations(), fn (array $migration): bool => ! $migration['applied']));
        if ($pending === []) {
            return [];
        }

        $batch = $this->nextBatch();
        $applied = [];

        foreach ($pending as $migration) {
            $sql = file_get_contents($migration['path']);
            if ($sql === false) {
                throw new RuntimeException('Não foi possível ler a migration: ' . $migration['name']);
            }

            $this->executeSql($sql);
            $this->recordApplied($migration['name'], $batch, $migration['checksum']);
            $applied[] = $migration;
        }

        return $applied;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function rollback(int $steps = 1): array
    {
        $this->ensureRepository();

        $steps = max(1, $steps);
        $batches = $this->database->fetchAll(
            'SELECT batch FROM ' . self::REPOSITORY_TABLE . ' GROUP BY batch ORDER BY batch DESC LIMIT ' . $steps
        );

        $rolledBack = [];

        foreach ($batches as $batchRow) {
            $batch = (int) ($batchRow['batch'] ?? 0);
            $migrations = $this->database->fetchAll(
                'SELECT migration FROM ' . self::REPOSITORY_TABLE . ' WHERE batch = :batch ORDER BY id DESC',
                ['batch' => $batch]
            );

            foreach ($migrations as $row) {
                $migrationName = (string) ($row['migration'] ?? '');
                $downPath = $this->downPathFor($migrationName);

                if (! is_file($downPath)) {
                    throw new RuntimeException('Migration de rollback não encontrada: ' . basename($downPath));
                }

                $sql = file_get_contents($downPath);
                if ($sql === false) {
                    throw new RuntimeException('Não foi possível ler a migration de rollback: ' . basename($downPath));
                }

                $this->executeSql($sql);
                $this->database->connection()?->prepare('DELETE FROM ' . self::REPOSITORY_TABLE . ' WHERE migration = :migration')->execute([
                    'migration' => $migrationName,
                ]);
                $rolledBack[] = $migrationName;
            }
        }

        return $rolledBack;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function status(): array
    {
        $this->ensureRepository();

        return $this->allMigrations();
    }

    private function ensureRepository(): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS schema_migrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(190) NOT NULL UNIQUE,
    batch INT NOT NULL,
    checksum VARCHAR(64) NOT NULL,
    applied_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $this->executeSql($sql);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function allMigrations(): array
    {
        $appliedRows = $this->database->fetchAll('SELECT migration, batch, checksum, applied_at FROM ' . self::REPOSITORY_TABLE . ' ORDER BY id ASC');
        $applied = [];

        foreach ($appliedRows as $row) {
            $applied[(string) $row['migration']] = $row;
        }

        $migrations = [];
        foreach ($this->migrationFiles() as $file) {
            $name = basename($file);
            $migrations[] = [
                'name' => $name,
                'path' => $file,
                'checksum' => hash_file('sha256', $file) ?: '',
                'applied' => isset($applied[$name]),
                'batch' => $applied[$name]['batch'] ?? null,
                'applied_at' => $applied[$name]['applied_at'] ?? null,
            ];
        }

        return $migrations;
    }

    /**
     * @return array<int, string>
     */
    private function migrationFiles(): array
    {
        $directory = base_path('migrations');
        $files = [];

        foreach (new DirectoryIterator($directory) as $file) {
            if ($file->isDot() || ! $file->isFile()) {
                continue;
            }

            $filename = $file->getFilename();
            if (! str_ends_with($filename, '.sql') || str_ends_with($filename, '.down.sql')) {
                continue;
            }

            $files[] = $file->getPathname();
        }

        sort($files, SORT_STRING);

        return $files;
    }

    private function nextBatch(): int
    {
        $row = $this->database->fetchOne('SELECT COALESCE(MAX(batch), 0) AS max_batch FROM ' . self::REPOSITORY_TABLE);

        return ((int) ($row['max_batch'] ?? 0)) + 1;
    }

    private function recordApplied(string $migration, int $batch, string $checksum): void
    {
        $statement = $this->database->connection()?->prepare(
            'INSERT INTO ' . self::REPOSITORY_TABLE . ' (migration, batch, checksum) VALUES (:migration, :batch, :checksum)'
        );

        if (! $statement) {
            throw new RuntimeException('Conexão MySQL indisponível para registrar migration.');
        }

        $statement->execute([
            'migration' => $migration,
            'batch' => $batch,
            'checksum' => $checksum,
        ]);
    }

    private function downPathFor(string $migrationName): string
    {
        return base_path('migrations/' . str_replace('.sql', '.down.sql', $migrationName));
    }

    private function executeSql(string $sql): void
    {
        $pdo = $this->database->connection();
        if (! $pdo) {
            throw new RuntimeException('Banco de dados indisponível.');
        }

        $sql = trim($sql);
        if ($sql === '') {
            return;
        }

        $pdo->exec($sql);
    }
}
