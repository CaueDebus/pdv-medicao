<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?self $instance = null;

    private ?PDO $pdo = null;

    private function __construct()
    {
        $config = Config::instance()->get('database');

        if (! is_array($config)) {
            return;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'] ?? '127.0.0.1',
            $config['port'] ?? '3306',
            $config['database'] ?? 'comandaflex',
            $config['charset'] ?? 'utf8mb4'
        );

        try {
            $this->pdo = new PDO($dsn, (string) ($config['username'] ?? 'root'), (string) ($config['password'] ?? ''), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException) {
            $this->pdo = null;
        }
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function connection(): ?PDO
    {
        return $this->pdo;
    }

    public function connected(): bool
    {
        return $this->pdo instanceof PDO;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        if (! $this->pdo) {
            return [];
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        if (! $this->pdo) {
            return null;
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        $result = $statement->fetch();

        return is_array($result) ? $result : null;
    }
}
