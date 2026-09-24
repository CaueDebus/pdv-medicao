<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Template;

/**
 * Tela de módulos: composição do produto nesta instalação.
 *
 * É a leitura do modelo de features da Linha de Produto de Software:
 * mostra o que está entregue e o que está desligado.
 */
final class ModulesTemplate extends AbstractScreenTemplate
{
    protected function baseScreen(array $context = []): array
    {
        return [
            'title' => 'Módulos',
            'subtitle' => 'Recursos ativos do sistema',
            'lead' => 'Pontos de variação do produto: o que está ligado aqui define quais telas o sistema entrega.',
            'route' => 'modulos',
            'context' => $context,
        ];
    }

    protected function metrics(array $context = []): array
    {
        $modules = $this->rows($context, 'modules');
        $enabled = count(array_filter($modules, static fn (array $module): bool => (bool) ($module['enabled'] ?? false)));
        $total = count($modules);

        return [
            $this->metric('Módulos ligados', $enabled, 'success'),
            $this->metric('Desligados', $total - $enabled, $total - $enabled > 0 ? 'warn' : 'info'),
            $this->metric('Catalogados', $total, 'info'),
        ];
    }

    protected function highlights(array $context = []): array
    {
        $modules = $this->rows($context, 'modules');
        $disabled = array_values(array_filter($modules, static fn (array $module): bool => ! (bool) ($module['enabled'] ?? false)));

        if ($disabled === []) {
            return [$this->highlight('Produto completo', 'Todos os módulos catalogados estão ativos nesta instalação.')];
        }

        return array_map(
            fn (array $module): array => $this->highlight(
                (string) ($module['name'] ?? ''),
                'Desligado nesta instalação — as telas deste módulo não são entregues.',
            ),
            array_slice($disabled, 0, 3),
        );
    }

    protected function sections(array $context = []): array
    {
        return [
            ['title' => 'Catálogo de módulos', 'type' => 'catalog'],
            ['title' => 'Integrações externas', 'type' => 'integrations'],
        ];
    }
}
