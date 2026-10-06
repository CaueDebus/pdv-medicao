<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Domain\Crud\CrudResource;
use App\Domain\Patterns\Factory\CrudFactory;

/**
 * Controller único de CRUD do ComandaFlex.
 *
 * Não existe controller por entidade: a CrudFactory entrega o CrudResource
 * da rota e este controller executa sempre o mesmo fluxo (formulário,
 * validação, gravação, redirect com mensagem). Cardápio, comandas, estoque
 * e módulos reaproveitam este mesmo código e as mesmas views.
 */
final class CrudController
{
    public function __construct(
        private readonly PageController $pageController = new PageController(),
        private readonly CrudFactory $crudFactory = new CrudFactory(),
    ) {
    }

    /**
     * Formulário de criação ou edição.
     */
    public function form(string $page, ?array $user, ?int $id = null): string
    {
        $resource = $this->resourceFor($page, $user);

        if (! $resource instanceof CrudResource || ! $resource->canWrite($this->roleOf($user))) {
            return $this->pageController->render($page, $user);
        }

        $values = $id !== null ? ($resource->repository->find($id) ?? []) : [];

        return $this->renderForm($resource, $page, $user, $values, $id);
    }

    /**
     * Grava criação ou edição e devolve a rota para redirecionar.
     *
     * @param array<string, mixed> $input
     */
    public function save(string $page, ?array $user, array $input, ?int $id = null): string
    {
        $session = Session::instance();
        $resource = $this->resourceFor($page, $user);

        if (! $resource instanceof CrudResource) {
            return '?page=' . urlencode($page);
        }

        if (! $session->validCsrf(isset($input['_token']) ? (string) $input['_token'] : null)) {
            $session->flash('danger', 'Sessão expirada. Tente novamente.');

            return '?page=' . urlencode($page);
        }

        if (! $resource->canWrite($this->roleOf($user))) {
            $session->flash('danger', 'Seu perfil não pode alterar ' . $resource->singular . '.');

            return '?page=' . urlencode($page);
        }

        $validation = $resource->validate($input);

        if ($validation['errors'] !== []) {
            $session->flash('danger', implode(' ', $validation['errors']));

            return '?page=' . urlencode($page) . '&action=' . ($id === null ? 'create' : 'edit') . ($id === null ? '' : '&id=' . $id);
        }

        $saved = $id === null
            ? $resource->repository->create($validation['data'])
            : $resource->repository->update($id, $validation['data']);

        if (! $saved) {
            $session->flash('warn', $resource->repository->persists()
                ? 'Não foi possível gravar: revise os dados e tente de novo.'
                : 'Modo demonstração: o MySQL não está disponível, então nada foi gravado.');

            return '?page=' . urlencode($page);
        }

        $session->flash('ok', $resource->savedMessage($id === null));

        return '?page=' . urlencode($page);
    }

    /**
     * Alterna um campo booleano direto da listagem, em um clique.
     *
     * É o que faz o painel de módulos funcionar como painel de variabilidade:
     * ligar e desligar um módulo não deveria exigir abrir o formulário.
     *
     * @param array<string, mixed> $input
     */
    public function toggle(string $page, ?array $user, array $input, int $id): string
    {
        $session = Session::instance();
        $resource = $this->resourceFor($page, $user);
        $redirect = '?page=' . urlencode($page);

        if (! $resource instanceof CrudResource) {
            return $redirect;
        }

        if (! $session->validCsrf(isset($input['_token']) ? (string) $input['_token'] : null)) {
            $session->flash('danger', 'Sessão expirada. Tente novamente.');

            return $redirect;
        }

        if (! $resource->canWrite($this->roleOf($user))) {
            $session->flash('danger', 'Seu perfil não pode alterar ' . $resource->singular . '.');

            return $redirect;
        }

        // O nome do campo vem do formulário, então só vale se estiver
        // declarado no recurso e for mesmo um campo de liga/desliga.
        $field = $resource->field(isset($input['field']) ? (string) $input['field'] : '');

        if ($field === null || $field->type !== 'toggle') {
            return $redirect;
        }

        $record = $resource->repository->find($id);

        if ($record === null) {
            $session->flash('warn', 'Registro não encontrado.');

            return $redirect;
        }

        $enabled = ! (bool) ($record[$field->name] ?? false);

        if (! $resource->repository->update($id, [$field->name => $enabled ? 1 : 0])) {
            $session->flash('warn', $resource->repository->persists()
                ? 'Não foi possível alterar o registro.'
                : 'Modo demonstração: o MySQL não está disponível, então nada foi gravado.');

            return $redirect;
        }

        $session->flash('ok', $resource->toggledMessage($enabled));

        return $redirect;
    }

    public function destroy(string $page, ?array $user, array $input, int $id): string
    {
        $session = Session::instance();
        $resource = $this->resourceFor($page, $user);
        $role = $this->roleOf($user);

        if (! $resource instanceof CrudResource) {
            return '?page=' . urlencode($page);
        }

        if (! $session->validCsrf(isset($input['_token']) ? (string) $input['_token'] : null)) {
            $session->flash('danger', 'Sessão expirada. Tente novamente.');

            return '?page=' . urlencode($page);
        }

        if (! $resource->canDestroy($role)) {
            $session->flash('danger', 'Seu perfil não pode excluir ' . $resource->singular . '.');

            return '?page=' . urlencode($page);
        }

        if (! $resource->repository->delete($id)) {
            $session->flash('warn', $resource->repository->persists()
                ? 'Não foi possível excluir o registro.'
                : 'Modo demonstração: o MySQL não está disponível, então nada foi excluído.');

            return '?page=' . urlencode($page);
        }

        $session->flash('ok', $resource->deletedMessage());

        return '?page=' . urlencode($page);
    }

    /**
     * @param array<string, mixed> $values
     */
    private function renderForm(CrudResource $resource, string $page, ?array $user, array $values, ?int $id): string
    {
        return $this->pageController->renderWithScreen($page, $user, [
            'view' => 'crud/form.php',
            'title' => $id === null ? $resource->newLabel() : $resource->editLabel(),
            'lead' => $id === null
                ? 'Cadastro novo usando o formulário compartilhado entre todos os CRUDs do sistema.'
                : 'Edição do registro selecionado, com os mesmos campos e as mesmas validações do cadastro.',
            'crud' => [
                'resource' => $resource,
                'values' => $values,
                'id' => $id,
                'token' => Session::instance()->csrfToken(),
                'page' => $page,
            ],
        ]);
    }

    private function resourceFor(string $page, ?array $user): ?CrudResource
    {
        if (! $this->pageController->canAccess($page, $this->roleOf($user))) {
            return null;
        }

        return $this->crudFactory->create($page);
    }

    private function roleOf(?array $user): string
    {
        return is_array($user) ? (string) ($user['role'] ?? 'operator') : 'operator';
    }
}
