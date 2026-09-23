<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Controllers\CrudController;
use App\Controllers\PageController;
use App\Core\Session;
use Tests\BaseTest;

/**
 * Feature: as quatro telas de CRUD (cardápio, comandas, estoque e módulos)
 * são servidas pelo MESMO controller, pela mesma listagem e pelo mesmo
 * formulário, variando apenas o CrudResource entregue pela CrudFactory.
 */
final class CrudScreensTest extends BaseTest
{
    /** @return array<string, mixed> */
    private function user(string $role = 'admin'): array
    {
        return ['id' => 1, 'name' => 'Administrador', 'email' => 'admin@comandaflex.local', 'role' => $role];
    }

    protected function setUp(): void
    {
        Session::instance()->pullFlash();
    }

    public function testEveryCrudScreenRendersTheSharedTable(): void
    {
        // Arrange
        $controller = new PageController();

        // Act / Assert
        foreach (['cardapio', 'comandas', 'estoque', 'modulos'] as $page) {
            $html = $controller->render($page, $this->user());
            $this->assertStringContainsString('class="data-table"', $html);
            $this->assertStringContainsString('action=create', $html);
        }
    }

    public function testCardapioTableListsProductsWithActions(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('cardapio', $this->user());

        // Assert
        $this->assertStringContainsString('Batata frita', $html);
        $this->assertStringContainsString('action=edit&amp;id=1', $html);
        $this->assertStringContainsString('Remover', $html);
    }

    public function testCreateFormRendersFieldsOfTheResource(): void
    {
        // Arrange
        $controller = new CrudController();

        // Act
        $html = $controller->form('cardapio', $this->user());

        // Assert
        $this->assertStringContainsString('name="name"', $html);
        $this->assertStringContainsString('name="category"', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringContainsString('action=store', $html);
    }

    public function testEditFormComesPrefilledWithTheRecord(): void
    {
        // Arrange
        $controller = new CrudController();

        // Act
        $html = $controller->form('cardapio', $this->user(), 1);

        // Assert
        $this->assertStringContainsString('value="Batata frita"', $html);
        $this->assertStringContainsString('action=update&amp;id=1', $html);
    }

    public function testOperatorDoesNotSeeDeleteButton(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('cardapio', $this->user('operator'));

        // Assert
        $this->assertStringNotContainsString('Remover', $html);
    }

    public function testSaveWithoutCsrfTokenIsRejected(): void
    {
        // Arrange
        $controller = new CrudController();

        // Act
        $redirect = $controller->save('cardapio', $this->user(), [
            'name' => 'Pastel',
            'category' => 'Comida',
            'price' => '12',
            'stock_qty' => '10',
        ]);
        $flash = Session::instance()->pullFlash();

        // Assert
        $this->assertSame('?page=cardapio', $redirect);
        $this->assertSame('danger', $flash['tone']);
    }

    public function testSaveWithInvalidDataReturnsToTheForm(): void
    {
        // Arrange
        $controller = new CrudController();
        $token = Session::instance()->csrfToken();

        // Act
        $redirect = $controller->save('cardapio', $this->user(), [
            '_token' => $token,
            'name' => '',
            'category' => 'Comida',
            'price' => '12',
            'stock_qty' => '10',
        ]);
        $flash = Session::instance()->pullFlash();

        // Assert
        $this->assertSame('?page=cardapio&action=create', $redirect);
        $this->assertSame('danger', $flash['tone']);
    }

    public function testSaveInDemoModeWarnsThatNothingWasPersisted(): void
    {
        // Arrange
        $controller = new CrudController();
        $token = Session::instance()->csrfToken();

        // Act
        $redirect = $controller->save('cardapio', $this->user(), [
            '_token' => $token,
            'name' => 'Pastel',
            'category' => 'Comida',
            'price' => '12',
            'stock_qty' => '10',
        ]);
        $flash = Session::instance()->pullFlash();

        // Assert
        $this->assertSame('?page=cardapio', $redirect);
        $this->assertSame('warn', $flash['tone']);
        $this->assertStringContainsString('Modo demonstração', $flash['message']);
    }

    public function testOperatorCannotDeleteEvenPostingDirectly(): void
    {
        // Arrange
        $controller = new CrudController();
        $token = Session::instance()->csrfToken();

        // Act
        $controller->destroy('cardapio', $this->user('operator'), ['_token' => $token], 1);
        $flash = Session::instance()->pullFlash();

        // Assert
        $this->assertSame('danger', $flash['tone']);
        $this->assertStringContainsString('não pode excluir', $flash['message']);
    }

    public function testManagerCanReachDeleteFlow(): void
    {
        // Arrange
        $controller = new CrudController();
        $token = Session::instance()->csrfToken();

        // Act
        $controller->destroy('cardapio', $this->user('manager'), ['_token' => $token], 1);
        $flash = Session::instance()->pullFlash();

        // Assert — passa da permissão e só para no modo demo
        $this->assertSame('warn', $flash['tone']);
        $this->assertStringContainsString('Modo demonstração', $flash['message']);
    }

    public function testDemoModeBannerIsVisibleOnCrudScreens(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('comandas', $this->user());

        // Assert
        $this->assertStringContainsString('Modo demonstração', $html);
    }
}
