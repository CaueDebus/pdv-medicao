<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\Controllers\AuthController;
use App\Core\Session;
use Tests\BaseTest;

final class AuthControllerTest extends BaseTest
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testLoginRequiresEmailAndPassword(): void
    {
        // Arrange
        $auth = new AuthController();

        // Act
        $error = $auth->login(['email' => '', 'password' => '']);

        // Assert
        $this->assertSame('Informe e-mail e senha.', $error);
        $this->assertFalse(Session::instance()->isAuthenticated());
    }

    public function testLoginAcceptsDemoAdminWhenDatabaseIsOffline(): void
    {
        // Arrange
        $auth = new AuthController();

        // Act
        $error = $auth->login(['email' => 'admin@comandaflex.local', 'password' => 'Admin@123']);

        // Assert
        $this->assertNull($error);
        $user = Session::instance()->user();
        $this->assertSame('admin', $user['role']);
        $this->assertSame('admin@comandaflex.local', $user['email']);
    }

    public function testLoginNormalizesEmailCasingAndWhitespace(): void
    {
        // Arrange
        $auth = new AuthController();

        // Act
        $error = $auth->login(['email' => '  ADMIN@ComandaFlex.local  ', 'password' => 'Admin@123']);

        // Assert
        $this->assertNull($error);
        $this->assertTrue(Session::instance()->isAuthenticated());
    }

    public function testLoginRejectsWrongDemoCredentials(): void
    {
        // Arrange
        $auth = new AuthController();

        // Act
        $error = $auth->login(['email' => 'admin@comandaflex.local', 'password' => 'senha-errada']);

        // Assert
        $this->assertStringContainsString('Banco indisponível', (string) $error);
        $this->assertFalse(Session::instance()->isAuthenticated());
    }

    public function testLogoutClearsSession(): void
    {
        // Arrange
        $auth = new AuthController();
        $auth->login(['email' => 'admin@comandaflex.local', 'password' => 'Admin@123']);

        // Act
        $auth->logout();

        // Assert
        $this->assertFalse(Session::instance()->isAuthenticated());
    }

    public function testShowLoginRendersFormWithError(): void
    {
        // Arrange
        $auth = new AuthController();

        // Act
        $html = $auth->showLogin('Credenciais inválidas.');

        // Assert
        $this->assertStringContainsString('<form', $html);
        $this->assertStringContainsString('Credenciais inválidas.', $html);
    }
}
