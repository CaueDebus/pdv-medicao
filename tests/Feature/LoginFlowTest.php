<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Controllers\AuthController;
use App\Core\Session;
use Tests\BaseTest;

/**
 * Feature: ciclo de vida da autenticação por sessão
 * (o mesmo fluxo que public/index.php orquestra).
 */
final class LoginFlowTest extends BaseTest
{
    private AuthController $auth;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->auth = new AuthController();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testVisitorStartsUnauthenticated(): void
    {
        // Arrange / Act
        $session = Session::instance();

        // Assert
        $this->assertFalse($session->isAuthenticated());
        $this->assertNull($session->user());
    }

    public function testSuccessfulLoginOpensAuthenticatedSession(): void
    {
        // Arrange
        $credentials = ['email' => 'admin@comandaflex.local', 'password' => 'Admin@123'];

        // Act
        $error = $this->auth->login($credentials);

        // Assert
        $this->assertNull($error);
        $this->assertTrue(Session::instance()->isAuthenticated());
    }

    public function testFailedLoginKeepsSessionClosed(): void
    {
        // Arrange
        $credentials = ['email' => 'intruso@comandaflex.local', 'password' => 'x'];

        // Act
        $error = $this->auth->login($credentials);

        // Assert
        $this->assertNotNull($error);
        $this->assertFalse(Session::instance()->isAuthenticated());
    }

    public function testLogoutAfterLoginEndsSession(): void
    {
        // Arrange
        $this->auth->login(['email' => 'admin@comandaflex.local', 'password' => 'Admin@123']);

        // Act
        $this->auth->logout();

        // Assert
        $this->assertFalse(Session::instance()->isAuthenticated());
        $this->assertNull(Session::instance()->user());
    }
}
