<?php

declare(strict_types=1);

namespace App\Domain\Crud;

/**
 * Descrição de um campo de formulário/listagem de um recurso CRUD.
 *
 * O conjunto de campos é a única coisa que muda de um CRUD para outro, então
 * ele é dado (e não código novo): o CrudController e as views de CRUD são
 * genéricos e leem esta descrição.
 */
final class CrudField
{
    /**
     * @param array<string, string> $options pares valor => rótulo para o tipo select
     */
    public function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly string $type = 'text',
        public readonly array $options = [],
        public readonly bool $required = true,
        public readonly string $help = '',
    ) {
    }

    /**
     * @param array<string, string> $options
     */
    public static function select(string $name, string $label, array $options, string $help = ''): self
    {
        return new self($name, $label, 'select', $options, true, $help);
    }

    public static function number(string $name, string $label, string $help = ''): self
    {
        return new self($name, $label, 'number', [], true, $help);
    }

    public static function money(string $name, string $label, string $help = ''): self
    {
        return new self($name, $label, 'money', [], true, $help);
    }

    public static function text(string $name, string $label, bool $required = true, string $help = ''): self
    {
        return new self($name, $label, 'text', [], $required, $help);
    }

    public static function toggle(string $name, string $label, string $help = ''): self
    {
        return new self($name, $label, 'toggle', [], false, $help);
    }

    /**
     * Rótulo legível de um valor já gravado (resolve o select para o texto).
     */
    public function display(mixed $value): string
    {
        if ($this->type === 'toggle') {
            return $value ? 'Ativo' : 'Inativo';
        }

        if ($this->type === 'money') {
            return 'R$ ' . number_format((float) $value, 2, ',', '.');
        }

        if ($this->options !== []) {
            return $this->options[(string) $value] ?? (string) $value;
        }

        return (string) $value;
    }

    /**
     * Converte o valor cru do formulário para o tipo que vai ao banco.
     */
    public function cast(mixed $value): mixed
    {
        return match ($this->type) {
            'number' => (int) $value,
            'money' => (float) str_replace(',', '.', (string) $value),
            'toggle' => $value ? 1 : 0,
            default => trim((string) $value),
        };
    }
}
