<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Template;

/**
 * Tela de formas de pagamento: o que o caixa aceita nesta instalação.
 */
final class PaymentMethodsTemplate extends AbstractScreenTemplate
{
    protected function baseScreen(array $context = []): array
    {
        return [
            'title' => 'Pagamentos',
            'subtitle' => 'Formas aceitas no caixa',
            'lead' => 'Formas de pagamento cadastradas e quais delas o caixa aceita hoje.',
            'route' => 'pagamentos',
            'context' => $context,
        ];
    }

    protected function metrics(array $context = []): array
    {
        $methods = $this->crudRows($context);
        $active = $this->countWhere($methods, 'active', ['1']);

        return [
            $this->metric('Formas cadastradas', count($methods), 'info'),
            $this->metric('Aceitas no caixa', $active, $active > 0 ? 'success' : 'danger'),
            $this->metric('Desativadas', count($methods) - $active, 'warn'),
        ];
    }

    protected function highlights(array $context = []): array
    {
        $methods = $this->crudRows($context);

        if ($methods === []) {
            return [$this->highlight('Nenhuma forma cadastrada', 'Cadastre as formas de pagamento que o caixa aceita.')];
        }

        $accepted = array_map(
            static fn (array $method): string => (string) ($method['name'] ?? ''),
            array_filter($methods, static fn (array $method): bool => (bool) ($method['active'] ?? false)),
        );

        if ($accepted === []) {
            return [$this->highlight('Nenhuma forma ativa', 'O caixa está sem forma de pagamento aceita.')];
        }

        $highlights = [$this->highlight('Aceitas no caixa', implode(', ', $accepted) . '.')];

        foreach ($methods as $method) {
            if (! (bool) ($method['active'] ?? false)) {
                $highlights[] = $this->highlight((string) ($method['name'] ?? ''), 'Desativada: não é aceita no caixa.');
            }
        }

        return array_slice($highlights, 0, 3);
    }

    protected function sections(array $context = []): array
    {
        return [
            ['title' => 'Formas de pagamento', 'type' => 'payments'],
            ['title' => 'Fechamento da comanda', 'type' => 'checkout'],
        ];
    }
}
