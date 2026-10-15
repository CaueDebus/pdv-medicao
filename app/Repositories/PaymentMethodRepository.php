<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Formas de pagamento: CRUD sobre a tabela `payment_methods`.
 */
final class PaymentMethodRepository extends AbstractCrudRepository
{
    protected function table(): string
    {
        return 'payment_methods';
    }

    protected function columns(): array
    {
        return ['name', 'kind', 'active'];
    }

    protected function orderBy(): string
    {
        return 'name ASC';
    }

    protected function demoRows(): array
    {
        return [
            ['id' => 1, 'name' => 'Dinheiro', 'kind' => 'cash', 'active' => true],
            ['id' => 2, 'name' => 'Pix', 'kind' => 'pix', 'active' => true],
            ['id' => 3, 'name' => 'Crédito à vista', 'kind' => 'credit', 'active' => true],
            ['id' => 4, 'name' => 'Vale-refeição', 'kind' => 'voucher', 'active' => false],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function kindOptions(): array
    {
        return [
            'cash' => 'Dinheiro',
            'credit' => 'Cartão de crédito',
            'debit' => 'Cartão de débito',
            'pix' => 'Pix',
            'voucher' => 'Vale-refeição',
        ];
    }
}
