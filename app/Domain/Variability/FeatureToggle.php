<?php

declare(strict_types=1);

namespace App\Domain\Variability;

use App\Core\Config;
use App\Repositories\ModuleRepository;

/**
 * Ponto único de decisão de variabilidade do ComandaFlex (abordagem LPS).
 *
 * A tabela `modules` é o modelo de features do produto: cada linha é um ponto
 * de variação com um `code` e um `enabled`. O mapa `app.features` (config)
 * liga cada tela ao código do módulo que a habilita.
 *
 * Com isso, "qual produto esta instalação entrega" vira dado de configuração:
 * desligar o módulo Estoque remove a tela de estoque da navegação e do
 * roteamento, sem remover código e sem criar uma variante do sistema.
 */
final class FeatureToggle
{
    /** @var array<string, bool> code => habilitado */
    private array $states = [];

    /**
     * @param array<int, array<string, mixed>> $modules
     * @param array<string, string> $pageMap tela => código do módulo
     */
    public function __construct(array $modules = [], private readonly array $pageMap = [])
    {
        foreach ($modules as $module) {
            $code = (string) ($module['code'] ?? '');

            if ($code === '') {
                continue;
            }

            $this->states[$code] = $this->truthy($module['enabled'] ?? false);
        }
    }

    public static function fromModules(?ModuleRepository $repository = null): self
    {
        $repository ??= new ModuleRepository();

        return new self($repository->all(), Config::instance()->get('app.features', []));
    }

    /**
     * Um módulo não catalogado é tratado como ligado: o núcleo do produto
     * (dashboard, comandas, módulos) não depende de ponto de variação.
     */
    public function enabled(string $code): bool
    {
        return $this->states[$code] ?? true;
    }

    public function pageEnabled(string $page): bool
    {
        $code = $this->pageMap[$page] ?? null;

        return $code === null || $this->enabled($code);
    }

    /**
     * @param array<int, string> $pages
     * @return array<int, string>
     */
    public function filterPages(array $pages): array
    {
        return array_values(array_filter($pages, fn (string $page): bool => $this->pageEnabled($page)));
    }

    /**
     * @return array<string, bool>
     */
    public function states(): array
    {
        return $this->states;
    }

    private function truthy(mixed $value): bool
    {
        return in_array($value, [true, 1, '1'], true);
    }
}
