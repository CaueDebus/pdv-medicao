<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Template;

/**
 * Tela de comandas: atendimento em curso e valor em aberto.
 */
final class OrdersTemplate extends AbstractScreenTemplate
{
    protected function baseScreen(array $context = []): array
    {
        return [
            'title' => 'Comandas',
            'subtitle' => 'Abertura, acompanhamento e fechamento',
            'lead' => 'Atendimento em curso no balcão e nas mesas, com o valor ainda em aberto.',
            'route' => 'comandas',
            'context' => $context,
        ];
    }

    protected function metrics(array $context = []): array
    {
        $orders = $this->rows($context, 'orders');

        return [
            $this->metric('Comandas abertas', $this->countWhere($orders, 'status', ['open']), 'info'),
            $this->metric('Em preparo', $this->countWhere($orders, 'status', ['preparing']), 'warn'),
            $this->metric('Prontas', $this->countWhere($orders, 'status', ['ready']), 'success'),
            $this->metric('Valor em aberto', $this->money($this->sumOf($orders, 'total_value')), 'success'),
        ];
    }

    protected function highlights(array $context = []): array
    {
        $orders = $this->rows($context, 'orders');

        if ($orders === []) {
            return [$this->highlight('Nenhuma comanda aberta', 'O salão está sem atendimento em curso neste momento.')];
        }

        usort($orders, static fn (array $a, array $b): int => (float) ($b['total_value'] ?? 0) <=> (float) ($a['total_value'] ?? 0));

        return array_map(
            fn (array $order): array => $this->highlight(
                (string) ($order['table_name'] ?? ''),
                $this->money((float) ($order['total_value'] ?? 0)) . ' em aberto — status ' . (string) ($order['status'] ?? '') . '.',
            ),
            array_slice($orders, 0, 3),
        );
    }

    protected function sections(array $context = []): array
    {
        return [
            ['title' => 'Comandas em andamento', 'type' => 'orders'],
            ['title' => 'Fechamento e pagamento', 'type' => 'checkout'],
        ];
    }
}
