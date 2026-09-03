<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class UserRepository
{
    public function findByEmail(string $email): ?array
    {
        $database = Database::instance();

        if (! $database->connected()) {
            return null;
        }

        $user = $database->fetchOne('SELECT id, name, email, password_hash, role, active FROM users WHERE email = :email LIMIT 1', [
            'email' => $email,
        ]);

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $database = Database::instance();

        if (! $database->connected()) {
            return null;
        }

        $user = $database->fetchOne('SELECT id, name, email, password_hash, role, active FROM users WHERE id = :id LIMIT 1', [
            'id' => $id,
        ]);

        return $user ?: null;
    }

    public function createAdminIfMissing(string $name, string $email, string $plainPassword): bool
    {
        $database = Database::instance();

        if (! $database->connected()) {
            return false;
        }

        $existing = $this->findByEmail($email);
        if ($existing) {
            return false;
        }

        $statement = $database->connection()->prepare('INSERT INTO users (name, email, password_hash, role, active) VALUES (:name, :email, :password_hash, :role, 1)');
        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($plainPassword, PASSWORD_DEFAULT),
            'role' => 'admin',
        ]);

        return true;
    }
}
