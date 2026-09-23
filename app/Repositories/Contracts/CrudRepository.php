<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

/**
 * Contrato mínimo que um repositório precisa cumprir para ser servido
 * pelo CrudController genérico.
 */
interface CrudRepository
{
    /** @return array<int, array<string, mixed>> */
    public function all(): array;

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array;

    /** @param array<string, mixed> $data */
    public function create(array $data): bool;

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    /**
     * false quando o MySQL não está disponível: a tela continua navegável
     * em modo demo, mas nada é gravado de verdade.
     */
    public function persists(): bool;
}
