<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\View;
use RuntimeException;
use Tests\BaseTest;

final class ViewTest extends BaseTest
{
    public function testRenderReturnsHtmlForExistingView(): void
    {
        // Arrange
        $view = new View();

        // Act
        $html = $view->render('auth/login.php', ['error' => null, 'sessionUser' => null]);

        // Assert
        $this->assertStringContainsString('<form', $html);
        $this->assertStringContainsString('Entrar no sistema', $html);
    }

    public function testRenderInjectsDataIntoView(): void
    {
        // Arrange
        $view = new View();

        // Act
        $html = $view->render('auth/login.php', ['error' => 'Credenciais inválidas.', 'sessionUser' => null]);

        // Assert
        $this->assertStringContainsString('Credenciais inválidas.', $html);
    }

    public function testRenderThrowsForMissingView(): void
    {
        // Arrange
        $view = new View();

        // Act / Assert
        $this->assertThrows(
            RuntimeException::class,
            static fn () => $view->render('pages/nao-existe.php'),
            'View inexistente deve lançar RuntimeException',
        );
    }
}
