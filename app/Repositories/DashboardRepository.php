<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class DashboardRepository
{
    public function summary(): array
    {
        $database = Database::instance();

        if ($database->connected()) {
            $rows = $database->fetchOne('SELECT COUNT(*) AS total FROM orders WHERE status IN ("open", "preparing")');

            return [
                'open_orders' => (int) ($rows['total'] ?? 0),
            ];
        }

        return ['open_orders' => 18];
    }
}
