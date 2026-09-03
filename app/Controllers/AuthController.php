<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\Database;
use App\Core\Session;
use App\Repositories\UserRepository;

final class AuthController
{
    public function __construct(
        private readonly View $view = new View(),
        private readonly UserRepository $users = new UserRepository(),
    ) {
    }

    public function showLogin(?string $error = null): string
    {
        return $this->view->render('auth/login.php', [
            'error' => $error,
            'sessionUser' => Session::instance()->user(),
        ]);
    }

    public function login(array $input): ?string
    {
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $password = (string) ($input['password'] ?? '');

        if ($email === '' || $password === '') {
            return 'Informe e-mail e senha.';
        }

        if (! Database::instance()->connected()) {
            if ($email === 'admin@comandaflex.local' && $password === 'Admin@123') {
                Session::instance()->putUser([
                    'id' => 1,
                    'name' => 'Administrador',
                    'email' => $email,
                    'role' => 'admin',
                ]);

                return null;
            }

            return 'Banco indisponível. Use o admin padrão em modo de desenvolvimento.';
        }

        $user = $this->users->findByEmail($email);
        if (! $user || (int) ($user['active'] ?? 0) !== 1) {
            return 'Usuário inválido ou desativado.';
        }

        if (! password_verify($password, (string) ($user['password_hash'] ?? ''))) {
            return 'Credenciais inválidas.';
        }

        Session::instance()->putUser([
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ]);

        return null;
    }

    public function logout(): void
    {
        Session::instance()->forgetUser();
    }
}
