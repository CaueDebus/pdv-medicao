<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Controllers\CrudController;
use App\Controllers\PageController;
use App\Core\Session;
use Tests\BaseTest;

/**
 * Feature: as dez telas de CRUD (cardápio, comandas, itens, mesas, clientes,
 * estoque, fornecedores, estações, pagamentos e módulos) são servidas pelo
 * MESMO controller, pela mesma listagem e pelo mesmo formulário, variando
 * apenas o CrudResource entregue pela CrudFactory.
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
        foreach (['cardapio', 'comandas', 'itens', 'mesas', 'clientes', 'estoque', 'fornecedores', 'estacoes', 'pagamentos', 'modulos'] as $page) {
            $html = $controller->render($page, $this->user());
            $this->assertStringContainsString('class="data-table"', $html, $page);
            $this->assertStringContainsString('action=create', $html, $page);
        }
    }

    public function testOrderItemsListResolvesOrderAndProductLabels(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('itens', $this->user());

        // Assert — a listagem mostra mesa e produto, não os ids crus
        $this->assertStringContainsString('<title>Itens da comanda · ComandaFlex</title>', $html);
        $this->assertStringContainsString('Mesa 07 · #31', $html);
        $this->assertStringContainsString('Chopp 300ml', $html);
        $this->assertStringContainsString('R$ 120,00', $html, 'indicador "Valor lançado" soma os subtotais');
    }

    public function testOrderItemFormOffersOrdersAndProducts(): void
    {
        // Arrange
        $controller = new CrudController();

        // Act
        $html = $controller->form('itens', $this->user('operator'));

        // Assert
        $this->assertStringContainsString('name="order_id"', $html);
        $this->assertStringContainsString('name="product_id"', $html);
        $this->assertStringContainsString('Mesa 03 · #32', $html);
        $this->assertStringNotContainsString('name="subtotal"', $html);
    }

    public function testOrderFormShowsTheTotalAsReadOnly(): void
    {
        // Arrange
        $controller = new CrudController();

        // Act
        $html = $controller->form('comandas', $this->user(), 31);

        // Assert — o total aparece, mas não é campo enviado
        $this->assertStringContainsString('value="R$ 118,00"', $html);
        $this->assertStringContainsString('readonly', $html);
        $this->assertStringNotContainsString('name="total_value"', $html);
        $this->assertStringContainsString('Soma dos itens lançados', $html);
    }

    public function testOrdersListStillShowsTheComputedTotal(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('comandas', $this->user());

        // Assert
        $this->assertStringContainsString('<th>Total</th>', $html);
        $this->assertStringContainsString('R$ 92,00', $html);
    }

    public function testSavingAnOrderNoLongerAsksForTheTotal(): void
    {
        // Arrange
        $controller = new CrudController();
        $token = Session::instance()->csrfToken();

        // Act
        $redirect = $controller->save('comandas', $this->user('operator'), [
            '_token' => $token,
            'table_name' => 'Mesa 20',
            'status' => 'open',
            'notes' => '',
        ]);
        $flash = Session::instance()->pullFlash();

        // Assert — passa da validação sem total e só para no modo demo
        $this->assertSame('?page=comandas', $redirect);
        $this->assertSame('warn', $flash['tone']);
        $this->assertStringContainsString('Modo demonstração', $flash['message']);
    }

    public function testDiningTablesFormRendersAreaAndSituationSelects(): void
    {
        // Arrange
        $controller = new CrudController();

        // Act
        $html = $controller->form('mesas', $this->user(), 3);

        // Assert
        $this->assertStringContainsString('value="Mesa 07"', $html);
        $this->assertStringContainsString('Varanda', $html);
        $this->assertStringContainsString('Reservada', $html);
    }

    public function testSavingACustomerInDemoModeWarnsThatNothingWasPersisted(): void
    {
        // Arrange
        $controller = new CrudController();
        $token = Session::instance()->csrfToken();

        // Act
        $redirect = $controller->save('clientes', $this->user('operator'), [
            '_token' => $token,
            'name' => 'Davi Rocha',
            'phone' => '(11) 95555-7080',
            'email' => '',
            'notes' => '',
        ]);
        $flash = Session::instance()->pullFlash();

        // Assert — o operador cadastra cliente; só o modo demo impede gravar
        $this->assertSame('?page=clientes', $redirect);
        $this->assertSame('warn', $flash['tone']);
        $this->assertStringContainsString('Modo demonstração', $flash['message']);
    }

    public function testSavingACustomerWithoutPhoneReturnsToTheForm(): void
    {
        // Arrange
        $controller = new CrudController();
        $token = Session::instance()->csrfToken();

        // Act
        $redirect = $controller->save('clientes', $this->user(), [
            '_token' => $token,
            'name' => 'Davi Rocha',
            'phone' => '',
        ]);
        $flash = Session::instance()->pullFlash();

        // Assert
        $this->assertSame('?page=clientes&action=create', $redirect);
        $this->assertStringContainsString('telefone', $flash['message']);
    }

    public function testManagementCatalogsFollowTheRoleOfEachProfile(): void
    {
        // Arrange
        $controller = new PageController();

        // Act / Assert — operador consulta; gerente e admin alteram
        foreach (['fornecedores', 'estacoes', 'pagamentos'] as $page) {
            $operator = $controller->render($page, $this->user('operator'));
            $this->assertStringContainsString('somente leitura', $operator, $page . ' operador');
            $this->assertStringNotContainsString('action=create', $operator, $page . ' operador');
            $this->assertStringNotContainsString('action=toggle', $operator, $page . ' operador');

            foreach (['manager', 'admin'] as $role) {
                $html = $controller->render($page, $this->user($role));
                $this->assertStringContainsString('action=create', $html, $page . ' ' . $role);
                $this->assertStringContainsString('action=toggle', $html, $page . ' ' . $role);
                $this->assertStringContainsString('Remover', $html, $page . ' ' . $role);
            }
        }
    }

    public function testOperatorCannotTogglePaymentMethodsEvenPostingDirectly(): void
    {
        // Arrange
        $controller = new CrudController();
        $token = Session::instance()->csrfToken();

        // Act
        $controller->toggle('pagamentos', $this->user('operator'), ['_token' => $token, 'field' => 'active'], 4);
        $flash = Session::instance()->pullFlash();

        // Assert
        $this->assertSame('danger', $flash['tone']);
        $this->assertStringContainsString('não pode alterar', $flash['message']);
    }

    public function testManagerToggleOfASupplierReachesPersistenceLayer(): void
    {
        // Arrange
        $controller = new CrudController();
        $token = Session::instance()->csrfToken();

        // Act
        $controller->toggle('fornecedores', $this->user('manager'), ['_token' => $token, 'field' => 'active'], 3);
        $flash = Session::instance()->pullFlash();

        // Assert — passa das permissões e só para no modo demo
        $this->assertSame('warn', $flash['tone']);
        $this->assertStringContainsString('Modo demonstração', $flash['message']);
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

    public function testModulesListRendersAnInlineToggleForAdmin(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('modulos', $this->user('admin'));

        // Assert
        $this->assertStringContainsString('action=toggle', $html);
        $this->assertStringContainsString('name="field" value="enabled"', $html);
        $this->assertStringContainsString('module-chip-button', $html);
    }

    public function testOperatorSeesModulesAsReadOnly(): void
    {
        // Arrange
        $controller = new PageController();

        // Act
        $html = $controller->render('modulos', $this->user('operator'));

        // Assert
        $this->assertStringContainsString('somente leitura', $html);
        $this->assertStringNotContainsString('action=toggle', $html);
        $this->assertStringNotContainsString('action=create', $html);
    }

    public function testOperatorCannotToggleModulesEvenPostingDirectly(): void
    {
        // Arrange
        $controller = new CrudController();
        $token = Session::instance()->csrfToken();

        // Act
        $controller->toggle('modulos', $this->user('operator'), ['_token' => $token, 'field' => 'enabled'], 3);
        $flash = Session::instance()->pullFlash();

        // Assert
        $this->assertSame('danger', $flash['tone']);
        $this->assertStringContainsString('não pode alterar', $flash['message']);
    }

    public function testToggleRejectsAFieldThatIsNotAToggle(): void
    {
        // Arrange
        $controller = new CrudController();
        $token = Session::instance()->csrfToken();

        // Act — "name" existe no recurso, mas é texto, não liga/desliga
        $redirect = $controller->toggle('modulos', $this->user('admin'), ['_token' => $token, 'field' => 'name'], 3);
        $flash = Session::instance()->pullFlash();

        // Assert
        $this->assertSame('?page=modulos', $redirect);
        $this->assertNull($flash, 'campo inválido não gera gravação nem mensagem de sucesso');
    }

    public function testToggleRejectsAFieldOutsideTheResource(): void
    {
        // Arrange
        $controller = new CrudController();
        $token = Session::instance()->csrfToken();

        // Act
        $controller->toggle('modulos', $this->user('admin'), ['_token' => $token, 'field' => 'id'], 3);
        $flash = Session::instance()->pullFlash();

        // Assert
        $this->assertNull($flash);
    }

    public function testToggleWithoutCsrfIsRejected(): void
    {
        // Arrange
        $controller = new CrudController();

        // Act
        $controller->toggle('modulos', $this->user('admin'), ['field' => 'enabled'], 3);
        $flash = Session::instance()->pullFlash();

        // Assert
        $this->assertSame('danger', $flash['tone']);
        $this->assertStringContainsString('Sessão expirada', $flash['message']);
    }

    public function testAdminToggleReachesPersistenceLayer(): void
    {
        // Arrange
        $controller = new CrudController();
        $token = Session::instance()->csrfToken();

        // Act
        $controller->toggle('modulos', $this->user('admin'), ['_token' => $token, 'field' => 'enabled'], 3);
        $flash = Session::instance()->pullFlash();

        // Assert — passa das permissões e só para no modo demo
        $this->assertSame('warn', $flash['tone']);
        $this->assertStringContainsString('Modo demonstração', $flash['message']);
    }

    public function testOperatorCannotOpenTheModulesForm(): void
    {
        // Arrange
        $controller = new CrudController();

        // Act
        $html = $controller->form('modulos', $this->user('operator'));

        // Assert — cai na listagem, sem formulário
        $this->assertStringNotContainsString('action=store', $html);
        $this->assertStringContainsString('somente leitura', $html);
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
