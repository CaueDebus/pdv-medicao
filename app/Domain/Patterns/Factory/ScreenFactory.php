<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Factory;

use App\Domain\Patterns\Template\AbstractScreenTemplate;
use App\Domain\Patterns\Template\DashboardTemplate;
use App\Domain\Patterns\Template\MenuTemplate;
use App\Domain\Patterns\Template\ModulesTemplate;
use App\Domain\Patterns\Template\OrdersTemplate;
use App\Domain\Patterns\Template\ProductionTemplate;
use App\Domain\Patterns\Template\ReportsTemplate;
use App\Domain\Patterns\Template\SettingsTemplate;
use App\Domain\Patterns\Template\StockTemplate;

/**
 * Decide qual Template Method monta a tela pedida.
 *
 * Cada rota tem seu próprio template, e o texto e os indicadores de cada
 * tela vivem no template correspondente — a factory só escolhe, não
 * descreve. Assim não existe uma segunda fonte de verdade para o conteúdo.
 */
final class ScreenFactory
{
    public function create(string $page, array $context = []): array
    {
        $screen = $this->templateFor($page)->build($context);
        $screen['page'] = $page;

        return $screen;
    }

    private function templateFor(string $page): AbstractScreenTemplate
    {
        return match ($page) {
            'cardapio' => new MenuTemplate(),
            'comandas' => new OrdersTemplate(),
            'estoque' => new StockTemplate(),
            'producao' => new ProductionTemplate(),
            'modulos' => new ModulesTemplate(),
            'relatorios' => new ReportsTemplate(),
            'configuracoes' => new SettingsTemplate(),
            default => new DashboardTemplate(),
        };
    }
}
