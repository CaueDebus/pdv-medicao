<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Template;

abstract class AbstractScreenTemplate
{
    final public function build(array $context = []): array
    {
        $screen = $this->baseScreen($context);
        $screen['metrics'] = $this->metrics($context);
        $screen['highlights'] = $this->highlights($context);
        $screen['sections'] = $this->sections($context);

        return $screen;
    }

    abstract protected function baseScreen(array $context = []): array;

    /** @return array<int, array<string, mixed>> */
    abstract protected function metrics(array $context = []): array;

    /** @return array<int, array<string, mixed>> */
    abstract protected function highlights(array $context = []): array;

    /** @return array<int, array<string, mixed>> */
    abstract protected function sections(array $context = []): array;
}
