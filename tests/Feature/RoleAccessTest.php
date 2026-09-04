<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Controllers\PageController;
use Tests\BaseTest;

/**
 * Feature: o PageController limita as telas por perfil e cai na tela
 * padrão quando o perfil não pode ver a rota pedida.
 */
final class RoleAccessTest extends BaseTest
{
    /** @return array<string, mixed> */
    private function user(string $role): array
    {
        return ['id' => 1, 'name' => 'Fulano', 'email' => 'fulano@comandaflex.local', 'role' => $role];
    }

    public function testOperatorCannotReachReportsAndFallsBackToDashboard(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('relatorios', $this->user('operator'));

        // Assert
        $this->assertStringContainsString('<title>Dashboard · ComandaFlex</title>', $html);
    }

    public function testManagerCannotReachSettingsAndFallsBackToDashboard(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('configuracoes', $this->user('manager'));

        // Assert
        $this->assertStringContainsString('<title>Dashboard · ComandaFlex</title>', $html);
    }

    public function testManagerCanReachReports(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('relatorios', $this->user('manager'));

        // Assert
        $this->assertStringContainsString('<title>Relatórios · ComandaFlex</title>', $html);
    }

    public function testAdminCanReachSettings(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('configuracoes', $this->user('admin'));

        // Assert
        $this->assertStringContainsString('<title>Configurações · ComandaFlex</title>', $html);
    }

    public function testOperatorNavigationShowsLockedManagementSections(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('dashboard', $this->user('operator'));

        // Assert
        $this->assertStringContainsString('is-locked', $html);
        $this->assertStringContainsString('restrito ao gerente/admin', $html);
    }

    public function testAdminNavigationHasNoLockedSections(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('dashboard', $this->user('admin'));

        // Assert
        $this->assertStringNotContainsString('is-locked', $html);
    }
}
