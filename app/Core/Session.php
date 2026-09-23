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

    /**
     * Mensagem de uma requisição só: sobrevive ao redirect pós-POST e some na leitura.
     */
    public function flash(string $tone, string $message): void
    {
        $_SESSION['flash'] = ['tone' => $tone, 'message' => $message];
    }

    /**
     * @return array{tone: string, message: string}|null
     */
    public function pullFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        return is_array($flash) ? $flash : null;
    }

    public function csrfToken(): string
    {
        $token = $_SESSION['csrf_token'] ?? null;

        if (! is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(16));
            $_SESSION['csrf_token'] = $token;
        }

        return $token;
    }

    public function validCsrf(?string $token): bool
    {
        return is_string($token) && hash_equals($this->csrfToken(), $token);
    }
}
