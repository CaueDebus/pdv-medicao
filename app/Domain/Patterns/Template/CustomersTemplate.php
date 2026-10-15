<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Template;

/**
 * Tela de clientes: contatos e restrições que o atendimento precisa saber.
 */
final class CustomersTemplate extends AbstractScreenTemplate
{
    protected function baseScreen(array $context = []): array
    {
        return [
            'title' => 'Clientes',
            'subtitle' => 'Contatos e preferências',
            'lead' => 'Cadastro de clientes da casa, com contato e as observações que o salão precisa conhecer.',
            'route' => 'clientes',
            'context' => $context,
        ];
    }

    protected function metrics(array $context = []): array
    {
        $customers = $this->crudRows($context);

        return [
            $this->metric('Clientes cadastrados', count($customers), 'info'),
            $this->metric('Com e-mail', $this->countFilled($customers, 'email'), 'success'),
            $this->metric('Com observação', $this->countFilled($customers, 'notes'), 'warn'),
        ];
    }

    protected function highlights(array $context = []): array
    {
        $customers = $this->crudRows($context);

        if ($customers === []) {
            return [$this->highlight('Nenhum cliente cadastrado', 'Cadastre os clientes frequentes para guardar contato e preferências.')];
        }

        $withNotes = array_values(array_filter(
            $customers,
            static fn (array $customer): bool => trim((string) ($customer['notes'] ?? '')) !== '',
        ));

        if ($withNotes === []) {
            return [$this->highlight('Sem observações', 'Nenhum cliente tem preferência ou restrição registrada.')];
        }

        return array_map(
            fn (array $customer): array => $this->highlight(
                (string) ($customer['name'] ?? ''),
                (string) ($customer['notes'] ?? '') . '.',
            ),
            array_slice($withNotes, 0, 3),
        );
    }

    protected function sections(array $context = []): array
    {
        return [
            ['title' => 'Cadastro de clientes', 'type' => 'customers'],
            ['title' => 'Preferências e restrições', 'type' => 'notes'],
        ];
    }
}
