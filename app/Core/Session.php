<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    private static ?self $instance = null;

    private function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function user(): ?array
    {
        $user = $_SESSION['user'] ?? null;

        return is_array($user) ? $user : null;
    }

    public function putUser(array $user): void
    {
        $_SESSION['user'] = $user;
    }

    public function forgetUser(): void
    {
        unset($_SESSION['user']);
    }

    public function isAuthenticated(): bool
    {
        return $this->user() !== null;
    }
}
