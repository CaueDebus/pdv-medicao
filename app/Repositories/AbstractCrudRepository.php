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

    /**
     * Com o banco disponível, o resultado real é sempre devolvido — inclusive
     * vazio. Tabela sem registro é informação legítima ("nada cadastrado"),
     * não motivo para exibir dado de demonstração no lugar.
     */
    final public function all(): array
    {
        if ($this->persists()) {
            return Database::instance()->fetchAll($this->selectSql() . ' ORDER BY ' . $this->orderBy());
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

    /**
     * Pares id => rótulo, usados pelos selects de outras telas (ex.: o
     * produto de uma movimentação ou a comanda de um item).
     *
     * @return array<string, string>
     */
    final public function options(): array
    {
        $options = [];

        foreach ($this->all() as $row) {
            $options[(string) ($row['id'] ?? '')] = $this->optionLabel($row);
        }

        return $options;
    }

    final public function create(array $data): bool
    {
        $data = $this->onlyKnownColumns($this->beforeSave($data));

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

        return $this->written(Database::instance()->execute($sql, $data), null, $data);
    }

    final public function update(int $id, array $data): bool
    {
        $data = $this->onlyKnownColumns($this->beforeSave($data));

        if ($data === [] || ! $this->persists()) {
            return false;
        }

        $before = $this->find($id);
        $assignments = array_map(static fn (string $column): string => $column . ' = :' . $column, array_keys($data));
        $sql = sprintf('UPDATE %s SET %s WHERE id = :id', $this->table(), implode(', ', $assignments));

        return $this->written(Database::instance()->execute($sql, $data + ['id' => $id]), $before, $data);
    }

    final public function delete(int $id): bool
    {
        if (! $this->persists()) {
            return false;
        }

        $before = $this->find($id);

        return $this->written(Database::instance()->execute('DELETE FROM ' . $this->table() . ' WHERE id = :id', ['id' => $id]), $before, []);
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
     * Gancho rodado antes de gravar, para derivar colunas que não vêm do
     * formulário (ex.: subtotal = quantidade × preço). O resultado ainda
     * passa pela lista branca de colunas. Padrão: grava como veio.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function beforeSave(array $data): array
    {
        return $data;
    }

    /**
     * Gancho rodado só depois de uma gravação bem-sucedida, para o
     * repositório manter dados derivados em outra tabela (ex.: o total da
     * comanda quando um item muda). Recebe o registro como era antes (null
     * no create) e os dados gravados (vazio no delete). Padrão: nada.
     *
     * @param array<string, mixed>|null $before
     * @param array<string, mixed> $data
     */
    protected function afterWrite(?array $before, array $data): void
    {
    }

    /**
     * Gancho do rótulo de cada registro nos selects de outras telas.
     *
     * @param array<string, mixed> $row
     */
    protected function optionLabel(array $row): string
    {
        return (string) ($row['name'] ?? $row['id'] ?? '');
    }

    /**
     * Repassa o resultado da escrita e só aciona afterWrite() quando ela deu certo.
     *
     * @param array<string, mixed>|null $before
     * @param array<string, mixed> $data
     */
    private function written(bool $ok, ?array $before, array $data): bool
    {
        if ($ok) {
            $this->afterWrite($before, $data);
        }

        return $ok;
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
