<?php

declare(strict_types=1);

namespace Tests;

use Throwable;

/**
 * Classe base de TODOS os testes do ComandaFlex (unidade e feature).
 *
 * Toda classe de teste deve:
 *  - estender esta classe;
 *  - viver em `tests/Unit/...` ou `tests/Feature/...`;
 *  - ter o sufixo `Test` no nome do arquivo e da classe;
 *  - expor cada caso como um método público `testAlgumComportamento()`
 *    escrito no padrão Arrange / Act / Assert.
 *
 * Não há framework externo: a execução é feita por `php scripts/test.php`
 * (ver `docs/testes.md`). Isso mantém a base sem novas dependências,
 * alinhado ao AGENTS.md.
 */
abstract class BaseTest
{
    private int $assertionCount = 0;

    /**
     * Executa todos os métodos `test*` desta classe.
     *
     * @return array{class: class-string, passed: int, failed: int, assertions: int, failures: list<string>}
     */
    final public function run(): array
    {
        $passed = 0;
        $failed = 0;
        $failures = [];

        foreach ($this->collectTestMethods() as $method) {
            try {
                $this->setUp();
                $this->{$method}();
                $passed++;
            } catch (Throwable $error) {
                $failed++;
                $failures[] = sprintf('%s::%s — %s', static::class, $method, $error->getMessage());
            } finally {
                try {
                    $this->tearDown();
                } catch (Throwable) {
                    // tearDown não deve derrubar o relatório dos demais testes.
                }
            }
        }

        return [
            'class' => static::class,
            'passed' => $passed,
            'failed' => $failed,
            'assertions' => $this->assertionCount,
            'failures' => $failures,
        ];
    }

    /**
     * Gancho opcional rodado antes de cada método `test*`.
     * Use para o "Arrange" comum (limpar sessão, montar fixtures, etc.).
     */
    protected function setUp(): void
    {
    }

    /**
     * Gancho opcional rodado depois de cada método `test*`, mesmo em falha.
     */
    protected function tearDown(): void
    {
    }

    // -----------------------------------------------------------------
    // Asserções — mínimas e suficientes para o padrão Arrange/Act/Assert.
    // -----------------------------------------------------------------

    protected function assertTrue(mixed $condition, string $message = ''): void
    {
        $this->track();

        if ($condition !== true) {
            $this->reject($message, 'Esperava true, veio ' . $this->export($condition));
        }
    }

    protected function assertFalse(mixed $condition, string $message = ''): void
    {
        $this->track();

        if ($condition !== false) {
            $this->reject($message, 'Esperava false, veio ' . $this->export($condition));
        }
    }

    protected function assertSame(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->track();

        if ($expected !== $actual) {
            $this->reject($message, sprintf('Esperava (===) %s, veio %s', $this->export($expected), $this->export($actual)));
        }
    }

    protected function assertEquals(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->track();

        if ($expected != $actual) {
            $this->reject($message, sprintf('Esperava (==) %s, veio %s', $this->export($expected), $this->export($actual)));
        }
    }

    protected function assertNull(mixed $actual, string $message = ''): void
    {
        $this->track();

        if ($actual !== null) {
            $this->reject($message, 'Esperava null, veio ' . $this->export($actual));
        }
    }

    protected function assertNotNull(mixed $actual, string $message = ''): void
    {
        $this->track();

        if ($actual === null) {
            $this->reject($message, 'Esperava valor não nulo, veio null');
        }
    }

    protected function assertEmpty(mixed $actual, string $message = ''): void
    {
        $this->track();

        if (! empty($actual)) {
            $this->reject($message, 'Esperava vazio, veio ' . $this->export($actual));
        }
    }

    protected function assertNotEmpty(mixed $actual, string $message = ''): void
    {
        $this->track();

        if (empty($actual)) {
            $this->reject($message, 'Esperava algo não vazio, veio ' . $this->export($actual));
        }
    }

    protected function assertCount(int $expected, mixed $countable, string $message = ''): void
    {
        $this->track();

        if (! is_countable($countable)) {
            $this->reject($message, 'Valor não é contável: ' . $this->export($countable));
        }

        $actual = count($countable);
        if ($actual !== $expected) {
            $this->reject($message, sprintf('Esperava %d itens, veio %d', $expected, $actual));
        }
    }

    protected function assertArrayHasKey(string|int $key, array $array, string $message = ''): void
    {
        $this->track();

        if (! array_key_exists($key, $array)) {
            $this->reject($message, sprintf('Esperava a chave "%s" no array %s', (string) $key, $this->export(array_keys($array))));
        }
    }

    /**
     * @param array<int|string, mixed> $haystack
     */
    protected function assertContains(mixed $needle, array $haystack, string $message = ''): void
    {
        $this->track();

        if (! in_array($needle, $haystack, true)) {
            $this->reject($message, sprintf('Esperava encontrar %s em %s', $this->export($needle), $this->export($haystack)));
        }
    }

    protected function assertStringContainsString(string $needle, string $haystack, string $message = ''): void
    {
        $this->track();

        if (! str_contains($haystack, $needle)) {
            $this->reject($message, sprintf('Esperava a substring "%s" no texto gerado', $needle));
        }
    }

    protected function assertStringNotContainsString(string $needle, string $haystack, string $message = ''): void
    {
        $this->track();

        if (str_contains($haystack, $needle)) {
            $this->reject($message, sprintf('Não esperava a substring "%s" no texto gerado', $needle));
        }
    }

    protected function assertInstanceOf(string $class, mixed $object, string $message = ''): void
    {
        $this->track();

        if (! $object instanceof $class) {
            $this->reject($message, sprintf('Esperava instância de %s, veio %s', $class, get_debug_type($object)));
        }
    }

    protected function assertGreaterThan(int|float $limit, int|float $actual, string $message = ''): void
    {
        $this->track();

        if (! ($actual > $limit)) {
            $this->reject($message, sprintf('Esperava valor maior que %s, veio %s', (string) $limit, (string) $actual));
        }
    }

    /**
     * Falha explícita — útil quando o teste espera uma exceção que não veio.
     */
    protected function fail(string $message): void
    {
        $this->track();
        $this->reject($message, 'Falha forçada pelo teste');
    }

    /**
     * Açúcar para o cenário "esta chamada deve lançar tal exceção".
     *
     * @param class-string<Throwable> $expected
     */
    protected function assertThrows(string $expected, callable $callback, string $message = ''): void
    {
        $this->track();

        try {
            $callback();
        } catch (Throwable $thrown) {
            if (! $thrown instanceof $expected) {
                $this->reject($message, sprintf('Esperava %s, veio %s (%s)', $expected, get_debug_type($thrown), $thrown->getMessage()));
            }

            return;
        }

        $this->reject($message, sprintf('Esperava que %s fosse lançada, mas nada foi lançado', $expected));
    }

    // -----------------------------------------------------------------
    // Internos
    // -----------------------------------------------------------------

    private function track(): void
    {
        $this->assertionCount++;
    }

    private function reject(string $custom, string $fallback): void
    {
        throw new AssertionFailed($custom !== '' ? $custom : $fallback);
    }

    private function export(mixed $value): string
    {
        if (is_string($value)) {
            return '"' . (mb_strlen($value) > 80 ? mb_substr($value, 0, 77) . '...' : $value) . '"';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value) || $value === null) {
            return var_export($value, true);
        }

        if (is_array($value)) {
            return 'array(' . count($value) . ')';
        }

        return get_debug_type($value);
    }

    /**
     * Métodos `test*` públicos declarados na classe de teste concreta.
     * Ignora o que vem da própria BaseTest (ex.: run) e métodos estáticos.
     *
     * @return list<string>
     */
    private function collectTestMethods(): array
    {
        $methods = [];

        foreach ((new \ReflectionClass($this))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isStatic() || $method->getDeclaringClass()->getName() === self::class) {
                continue;
            }

            if (str_starts_with($method->getName(), 'test')) {
                $methods[] = $method->getName();
            }
        }

        return $methods;
    }
}
