<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Template;

/**
 * Tela de configurações: parâmetros do ambiente e composição entregue.
 */
final class SettingsTemplate extends AbstractScreenTemplate
{
    protected function baseScreen(array $context = []): array
    {
        return [
            'title' => 'Configurações',
            'subtitle' => 'Preferências do ambiente',
            'lead' => 'Ponto de entrada para tema, permissões e parâmetros do sistema.',
            'route' => 'configuracoes',
            'context' => $context,
        ];
    }

    protected function metrics(array $context = []): array
    {
        $features = $context['features'] ?? [];
        $features = is_array($features) ? $features : [];
        $enabled = count(array_filter($features));

        return [
            $this->metric('Perfis disponíveis', 3, 'info'),
            $this->metric('Módulos ligados', $enabled, 'success'),
            $this->metric('Pontos de variação', count($features), 'warn'),
        ];
    }

    protected function highlights(array $context = []): array
    {
        return [
            $this->highlight('Tema da marca', 'Cor, tipografia e espaçamento vêm do design system compartilhado.'),
            $this->highlight('Perfis', 'Operador, gerente e admin têm navegação e permissões próprias.'),
        ];
    }

    protected function sections(array $context = []): array
    {
        return [
            ['title' => 'Preferências do ambiente', 'type' => 'preferences'],
            ['title' => 'Permissões por perfil', 'type' => 'permissions'],
        ];
    }
}
