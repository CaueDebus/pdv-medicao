<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\AbstractCrudRepository;
use Tests\BaseTest;

/**
 * Template Method na persistência: os ganchos da base têm comportamento
 * padrão seguro e afterWrite() só roda quando a escrita deu certo.
 */
final class AbstractCrudRepositoryTest extends BaseTest
{
    /**
     * Repositório mínimo que só declara o obrigatório e anota o gancho.
     */
    private function repository(): AbstractCrudRepository
    {
        return new class extends AbstractCrudRepository {
            /** @var array<int, array{before: array<string, mixed>|null, data: array<string, mixed>}> */
            public array $writes = [];

            protected function table(): string
            {
                return 'exemplo';
            }

            protected function columns(): array
            {
                return ['name'];
            }

            protected function demoRows(): array
            {
                return [['id' => 7, 'name' => 'Registro demo']];
            }

            protected function afterWrite(?array $before, array $data): void
            {
                $this->writes[] = ['before' => $before, 'data' => $data];
            }
        };
    }

    public function testAfterWriteDoesNotRunWhenTheWriteFails(): void
    {
        // Arrange — banco offline: nenhuma escrita acontece
        $repository = $this->repository();

        // Act
        $created = $repository->create(['name' => 'Novo']);
        $updated = $repository->update(7, ['name' => 'Editado']);
        $deleted = $repository->delete(7);

        // Assert
        $this->assertFalse($created || $updated || $deleted);
        $this->assertSame([], $repository->writes, 'sem gravação, nada derivado é recalculado');
    }

    public function testOptionsUseTheNameColumnByDefault(): void
    {
        // Arrange
        $repository = $this->repository();

        // Act
        $options = $repository->options();

        // Assert
        $this->assertSame(['7' => 'Registro demo'], $options);
    }
}
