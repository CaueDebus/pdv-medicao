<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Template;

abstract class AbstractScreenTemplate
{
    final public function build(array $context = []): array
    {
        $screen = $this->baseScreen($context);
        $screen['metrics'] = $this->metrics($context);
        $screen['highlights'] = $this->highlights($context);
        $screen['sections'] = $this->sections($context);

        return $screen;
    }

    abstract protected function baseScreen(array $context = []): array;

    /** @return array<int, array<string, mixed>> */
    abstract protected function metrics(array $context = []): array;

    /** @return array<int, array<string, mixed>> */
    abstract protected function highlights(array $context = []): array;

    /** @return array<int, array<string, mixed>> */
    abstract protected function sections(array $context = []): array;

    // -----------------------------------------------------------------
    // Auxiliares compartilhados por todas as telas, para que cada
    // template concreto descreva o que mostrar e não como montar.
    // -----------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    final protected function metric(string $label, string|int|float $value, string $tone = 'info'): array
    {
        return ['label' => $label, 'value' => (string) $value, 'tone' => $tone];
    }

    /**
     * @return array<string, mixed>
     */
    final protected function highlight(string $title, string $text): array
    {
        return ['title' => $title, 'text' => $text];
    }

    /**
     * Linhas de uma chave do contexto, já garantidas como lista.
     *
     * @param array<string, mixed> $context
     * @return array<int, array<string, mixed>>
     */
    final protected function rows(array $context, string $key): array
    {
        $rows = $context[$key] ?? [];

        return is_array($rows) ? array_values(array_filter($rows, 'is_array')) : [];
    }

    /**
     * Conta as linhas cujo campo tem um dos valores informados.
     *
     * @param array<int, array<string, mixed>> $rows
     * @param array<int, string> $values
     */
    final protected function countWhere(array $rows, string $field, array $values): int
    {
        return count(array_filter(
            $rows,
            static fn (array $row): bool => in_array((string) ($row[$field] ?? ''), $values, true),
        ));
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    final protected function sumOf(array $rows, string $field): float
    {
        return array_sum(array_map(static fn (array $row): float => (float) ($row[$field] ?? 0), $rows));
    }

    final protected function money(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }
}
