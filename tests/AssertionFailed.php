<?php

declare(strict_types=1);

namespace Tests;

use RuntimeException;

/**
 * Lançada por qualquer asserção da BaseTest quando a expectativa falha.
 * O TestRunner captura essa exceção e transforma em falha legível do teste.
 */
final class AssertionFailed extends RuntimeException
{
}
