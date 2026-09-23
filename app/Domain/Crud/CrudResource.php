<?php

declare(strict_types=1);

namespace App\Domain\Crud;

use App\Repositories\Contracts\CrudRepository;

/**
 * Um recurso CRUD do produto: liga uma tela a um repositório e à lista de campos.
 *
 * Adicionar um CRUD novo ao ComandaFlex é adicionar uma instância disto na
 * CrudFactory — nenhuma controller ou view nova é necessária.
 */
final class CrudResource
{
    /**
     * @param array<int, CrudField> $fields
     * @param array<int, string> $destroyRoles perfis autorizados a excluir
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly string $singular,
        public readonly CrudRepository $repository,
        public readonly array $fields,
        public readonly bool $feminine = true,
        public readonly array $destroyRoles = ['manager', 'admin'],
    ) {
    }

    public function newLabel(): string
    {
        return ($this->feminine ? 'Nova ' : 'Novo ') . $this->singular;
    }

    public function editLabel(): string
    {
        return 'Editar ' . $this->singular;
    }

    public function savedMessage(bool $created): string
    {
        $verb = $created
            ? ($this->feminine ? 'cadastrada' : 'cadastrado')
            : ($this->feminine ? 'atualizada' : 'atualizado');

        return ucfirst($this->singular) . ' ' . $verb . ' com sucesso.';
    }

    public function deletedMessage(): string
    {
        return ucfirst($this->singular) . ($this->feminine ? ' excluída.' : ' excluído.');
    }

    /**
     * @return array<int, string>
     */
    public function fieldNames(): array
    {
        return array_map(static fn (CrudField $field): string => $field->name, $this->fields);
    }

    public function field(string $name): ?CrudField
    {
        foreach ($this->fields as $field) {
            if ($field->name === $name) {
                return $field;
            }
        }

        return null;
    }

    public function canDestroy(string $role): bool
    {
        return in_array($role, $this->destroyRoles, true);
    }

    /**
     * Valida e converte a entrada do formulário.
     *
     * @param array<string, mixed> $input
     * @return array{data: array<string, mixed>, errors: array<int, string>}
     */
    public function validate(array $input): array
    {
        $data = [];
        $errors = [];

        foreach ($this->fields as $field) {
            $raw = $input[$field->name] ?? null;

            if ($field->type === 'toggle') {
                $data[$field->name] = $field->cast($raw !== null && $raw !== '' && $raw !== '0');
                continue;
            }

            if ($raw === null || trim((string) $raw) === '') {
                if ($field->required) {
                    $errors[] = 'Informe ' . mb_strtolower($field->label) . '.';
                    continue;
                }

                $data[$field->name] = '';
                continue;
            }

            if ($field->options !== [] && ! array_key_exists((string) $raw, $field->options)) {
                $errors[] = mb_strtolower($field->label) . ' inválido.';
                continue;
            }

            if (in_array($field->type, ['number', 'money'], true) && ! is_numeric(str_replace(',', '.', (string) $raw))) {
                $errors[] = mb_strtolower($field->label) . ' precisa ser um número.';
                continue;
            }

            $data[$field->name] = $field->cast($raw);
        }

        return ['data' => $data, 'errors' => $errors];
    }
}
