<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\Session;
use Tests\BaseTest;

final class SessionTest extends BaseTest
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testPutUserThenUserReturnsSameData(): void
    {
        // Arrange
        $session = Session::instance();
        $user = ['id' => 9, 'name' => 'Ana', 'email' => 'ana@comandaflex.local', 'role' => 'manager'];

        // Act
        $session->putUser($user);

        // Assert
        $this->assertSame($user, $session->user());
    }

    public function testIsAuthenticatedReflectsUserPresence(): void
    {
        // Arrange
        $session = Session::instance();

        // Act / Assert
        $this->assertFalse($session->isAuthenticated());

        $session->putUser(['id' => 1, 'role' => 'admin']);
        $this->assertTrue($session->isAuthenticated());
    }

    public function testForgetUserClearsSession(): void
    {
        // Arrange
        $session = Session::instance();
        $session->putUser(['id' => 1, 'role' => 'admin']);

        // Act
        $session->forgetUser();

        // Assert
        $this->assertNull($session->user());
        $this->assertFalse($session->isAuthenticated());
    }
}
