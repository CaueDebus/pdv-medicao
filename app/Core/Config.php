<?php

declare(strict_types=1);

namespace App\Core;

final class Config
{
    private static ?self $instance = null;

    /** @var array<string, mixed> */
    private array $items = [];

    private function __construct()
    {
        $this->items = [
            'app' => require base_path('config/app.php'),
            'database' => require base_path('config/database.php'),
        ];
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get(string $path, mixed $default = null): mixed
    {
        $segments = explode('.', $path);
        $value = $this->items;

        foreach ($segments as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public function all(): array
    {
        return $this->items;
    }
}
