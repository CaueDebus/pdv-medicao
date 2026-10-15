<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Template;

/**
 * Tela de fornecedores: quem abastece o estoque e quem está fora de uso.
 */
final class SuppliersTemplate extends AbstractScreenTemplate
{
    protected function baseScreen(array $context = []): array
    {
        return [
            'title' => 'Fornecedores',
            'subtitle' => 'Contatos de reposição',
            'lead' => 'Fornecedores que abastecem o estoque. Faz parte do módulo Estoque: desligado o módulo, a tela sai do produto.',
            'route' => 'fornecedores',
            'context' => $context,
        ];
    }

    protected function metrics(array $context = []): array
    {
        $suppliers = $this->crudRows($context);
        $active = $this->countWhere($suppliers, 'active', ['1']);
        $inactive = count($suppliers) - $active;

        return [
            $this->metric('Fornecedores', count($suppliers), 'info'),
            $this->metric('Ativos', $active, 'success'),
            $this->metric('Inativos', $inactive, $inactive > 0 ? 'warn' : 'info'),
            $this->metric('Com CNPJ', $this->countFilled($suppliers, 'document'), 'info'),
        ];
    }

    protected function highlights(array $context = []): array
    {
        $suppliers = $this->crudRows($context);

        if ($suppliers === []) {
            return [$this->highlight('Nenhum fornecedor cadastrado', 'Cadastre quem abastece o estoque para ter o contato à mão na reposição.')];
        }

        $inactive = array_values(array_filter(
            $suppliers,
            static fn (array $supplier): bool => ! (bool) ($supplier['active'] ?? false),
        ));

        if ($inactive === []) {
            return [$this->highlight('Todos ativos', 'Todos os fornecedores cadastrados estão em uso.')];
        }

        return array_map(
            fn (array $supplier): array => $this->highlight(
                (string) ($supplier['name'] ?? ''),
                'Inativo: o cadastro fica guardado, mas o fornecedor está fora de uso.',
            ),
            array_slice($inactive, 0, 3),
        );
    }

    protected function sections(array $context = []): array
    {
        return [
            ['title' => 'Cadastro de fornecedores', 'type' => 'suppliers'],
            ['title' => 'Contatos para reposição', 'type' => 'replenishment'],
        ];
    }
}
