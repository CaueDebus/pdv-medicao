<?php

declare(strict_types=1);

namespace Tests;

use ReflectionClass;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Descobre e executa as classes de teste que estendem BaseTest.
 *
 * Convenção de descoberta: o caminho relativo do arquivo dentro de `tests/`
 * vira o nome totalmente qualificado da classe.
 *   tests/Unit/Core/RouterTest.php  ->  Tests\Unit\Core\RouterTest
 */
final class TestRunner
{
    public function __construct(private readonly string $testsDir)
    {
    }

    /**
     * @param string|null $filter Caminho (arquivo ou pasta) para restringir a execução.
     */
    public function run(?string $filter = null): int
    {
        $files = $this->discover($filter);

        if ($files === []) {
            fwrite(STDERR, "Nenhum teste encontrado.\n");

            return 1;
        }

        $passed = 0;
        $failed = 0;
        $assertions = 0;
        $failures = [];

        foreach ($files as $file) {
            require_once $file;

            $class = $this->classForFile($file);
            if (! class_exists($class)) {
                $failed++;
                $failures[] = sprintf('%s não define a classe esperada %s', $this->relative($file), $class);
                echo "  ??  " . $this->relative($file) . "\n";

                continue;
            }

            $reflection = new ReflectionClass($class);
            if ($reflection->isAbstract() || ! $reflection->isSubclassOf(BaseTest::class)) {
                continue;
            }

            /** @var BaseTest $test */
            $test = $reflection->newInstance();
            $result = $test->run();

            $passed += $result['passed'];
            $failed += $result['failed'];
            $assertions += $result['assertions'];
            $failures = array_merge($failures, $result['failures']);

            printf(
                "  %s  %s (%d ok%s)\n",
                $result['failed'] === 0 ? 'PASS' : 'FAIL',
                $this->shortName($class),
                $result['passed'],
                $result['failed'] > 0 ? sprintf(', %d falhando', $result['failed']) : '',
            );
        }

        if ($failures !== []) {
            echo "\nFalhas:\n";
            foreach ($failures as $failure) {
                echo '  - ' . $failure . "\n";
            }
        }

        printf(
            "\n%s | %d passaram, %d falharam | %d asserções\n",
            $failed === 0 ? 'RESULTADO: OK' : 'RESULTADO: FALHOU',
            $passed,
            $failed,
            $assertions,
        );

        return $failed === 0 ? 0 : 1;
    }

    /**
     * @return list<string>
     */
    private function discover(?string $filter): array
    {
        $target = $this->testsDir;

        if ($filter !== null && $filter !== '') {
            $candidate = $this->resolveFilter($filter);

            if ($candidate !== null && is_file($candidate)) {
                return [$candidate];
            }

            if ($candidate !== null && is_dir($candidate)) {
                $target = $candidate;
            } else {
                fwrite(STDERR, sprintf("Filtro não encontrado: %s\n", $filter));

                return [];
            }
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($target, RecursiveDirectoryIterator::SKIP_DOTS));

        /** @var SplFileInfo $item */
        foreach ($iterator as $item) {
            if ($item->isFile() && str_ends_with($item->getFilename(), 'Test.php')) {
                $files[] = $item->getPathname();
            }
        }

        sort($files, SORT_STRING);

        return $files;
    }

    private function resolveFilter(string $filter): ?string
    {
        $paths = [
            $filter,
            $this->testsDir . DIRECTORY_SEPARATOR . ltrim($filter, '/\\'),
        ];

        foreach ($paths as $path) {
            $real = realpath($path);
            if ($real !== false) {
                return $real;
            }
        }

        return null;
    }

    private function classForFile(string $file): string
    {
        $relative = $this->relative($file);
        $relative = substr($relative, 0, -strlen('.php'));
        $relative = str_replace(['/', '\\'], '\\', $relative);

        return 'Tests\\' . $relative;
    }

    private function relative(string $file): string
    {
        $root = realpath($this->testsDir) ?: $this->testsDir;
        $file = realpath($file) ?: $file;

        return ltrim(str_replace($root, '', $file), '/\\');
    }

    private function shortName(string $class): string
    {
        $parts = explode('\\', $class);

        return end($parts);
    }
}
