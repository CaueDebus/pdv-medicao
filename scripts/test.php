<?php

declare(strict_types=1);

/**
 * Runner de testes do ComandaFlex.
 *
 *   php scripts/test.php                 # roda tudo (tests/Unit + tests/Feature)
 *   php scripts/test.php Unit            # só a pasta tests/Unit
 *   php scripts/test.php Feature/LoginFlowTest.php
 *
 * Sem dependências externas de propósito (ver AGENTS.md e docs/testes.md).
 */

require dirname(__DIR__) . '/tests/bootstrap.php';

use Tests\TestRunner;

$filter = $argv[1] ?? null;

$runner = new TestRunner(dirname(__DIR__) . '/tests');

exit($runner->run($filter));
