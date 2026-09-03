<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public function render(string $view, array $data = []): string
    {
        $file = base_path('resources/views/' . ltrim($view, '/'));

        if (! is_file($file)) {
            throw new \RuntimeException('View não encontrada: ' . $view);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $file;

        return (string) ob_get_clean();
    }
}
