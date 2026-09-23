<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Repositories\Contracts\CrudRepository;

/**
 * Template Method aplicado à persistência: o esqueleto das quatro operações
 * (listar, buscar, gravar, excluir) fica aqui e é final; cada repositório
 * concreto só informa tabela, colunas, ordenação e dados de demonstração.
 *
 * Isso evita repetir o mesmo SQL em cada CRUD e mantém o fallback demo
 * consistente em todas as telas quando o MySQL não está disponível.
 */
abstract class AbstractCrudRepository implements CrudRepository
{
    abstract protected function table(): string;

    /**
     * Colunas graváveis. Funciona como lista branca: nada que venha do
     * formulário entra no SQL sem estar declarado aqui.
     *
     * @return array<int, string>
     */
    abstract protected function columns(): array;

    /**
     * Linhas usadas quando o banco está offline (modo demo, sem persistência).
     *
     * @return array<int, array<string, mixed>>
     */
    abstract protected function demoRows(): array;

    protected function orderBy(): string
    {
        return 'id DESC';
    }

    final public function persists(): bool
    {
        return Database::instance()->connected();
    }

    final public function all(): array
    {
        if ($this->persists()) {
            $rows = Database::instance()->fetchAll($this->selectSql() . ' ORDER BY ' . $this->orderBy());

            if ($rows !== []) {
                return $rows;
            }
        }

        return $this->demoRows();
    }

    final public function find(int $id): ?array
    {
        if ($this->persists()) {
            return Database::instance()->fetchOne($this->selectSql() . ' WHERE ' . $this->idColumn() . ' = :id', ['id' => $id]);
        }

        foreach ($this->demoRows() as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return $row;
            }
        }

        return null;
    }

    final public function create(array $data): bool
    {
        $data = $this->onlyKnownColumns($data);

        if ($data === [] || ! $this->persists()) {
            return false;
        }

        $columns = array_keys($data);
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table(),
            implode(', ', $columns),
            implode(', ', array_map(static fn (string $column): string => ':' . $column, $columns)),
        );

        return Database::instance()->execute($sql, $data);
    }

    final public function update(int $id, array $data): bool
    {
        $data = $this->onlyKnownColumns($data);

        if ($data === [] || ! $this->persists()) {
            return false;
        }

        $assignments = array_map(static fn (string $column): string => $column . ' = :' . $column, array_keys($data));
        $sql = sprintf('UPDATE %s SET %s WHERE id = :id', $this->table(), implode(', ', $assignments));

        return Database::instance()->execute($sql, $data + ['id' => $id]);
    }

    final public function delete(int $id): bool
    {
        if (! $this->persists()) {
            return false;
        }

        return Database::instance()->execute('DELETE FROM ' . $this->table() . ' WHERE id = :id', ['id' => $id]);
    }

    protected function selectSql(): string
    {
        return 'SELECT id, ' . implode(', ', $this->columns()) . ' FROM ' . $this->table();
    }

    /**
     * Coluna de identidade usada na leitura. Repositórios que fazem JOIN
     * qualificam o nome para não ficar ambíguo.
     */
    protected function idColumn(): string
    {
        return 'id';
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function onlyKnownColumns(array $data): array
    {
        return array_intersect_key($data, array_flip($this->columns()));
    }
}
