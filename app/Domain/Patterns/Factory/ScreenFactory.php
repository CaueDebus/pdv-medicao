<?php

declare(strict_types=1);

namespace App\Domain\Patterns\Factory;

use App\Domain\Patterns\Template\DashboardTemplate;
use App\Domain\Patterns\Template\OperationsTemplate;
use App\Domain\Patterns\Template\ReportsTemplate;

final class ScreenFactory
{
    public function create(string $page, array $context = []): array
    {
        $template = match ($page) {
            'dashboard' => new DashboardTemplate(),
            'cardapio', 'comandas', 'estoque', 'producao', 'modulos' => new OperationsTemplate(),
            'relatorios', 'configuracoes' => new ReportsTemplate(),
            default => new DashboardTemplate(),
        };

        $screen = $template->build($context);
        $screen['page'] = $page;
        $screen['title'] = $this->titleFor($page, $screen['title'] ?? $page);
        $screen['subtitle'] = $this->subtitleFor($page, $screen['subtitle'] ?? '');
        $screen['lead'] = $this->leadFor($page, $screen['lead'] ?? '');

        return $screen;
    }

    private function titleFor(string $page, string $fallback): string
    {
        return match ($page) {
            'cardapio' => 'Cardápio',
            'comandas' => 'Comandas',
            'estoque' => 'Estoque',
            'producao' => 'Produção',
            'modulos' => 'Módulos',
            'relatorios' => 'Relatórios',
            'configuracoes' => 'Configurações',
            default => $fallback,
        };
    }

    private function subtitleFor(string $page, string $fallback): string
    {
        return match ($page) {
            'cardapio' => 'Catálogo e preços do menu',
            'comandas' => 'Abertura, acompanhamento e fechamento',
            'estoque' => 'Disponibilidade e alertas operacionais',
            'producao' => 'Fila de cozinha e bar',
            'modulos' => 'Recursos ativos do sistema',
            'relatorios' => 'Indicadores operacionais e gerenciais',
            'configuracoes' => 'Preferências do ambiente',
            default => $fallback,
        };
    }

    private function leadFor(string $page, string $fallback): string
    {
        return match ($page) {
            'cardapio' => 'Lista de produtos pronta para virar CRUD, com navegação central compartilhada.',
            'comandas' => 'Fluxo de atendimento pensado para balcão e mesa, sem quebrar o padrão do layout.',
            'estoque' => 'Leitura rápida do que ainda pode ser vendido e do que precisa reposição.',
            'producao' => 'Visão da fila de preparo com estados legíveis para cozinha e bar.',
            'modulos' => 'Mapa dos módulos do produto e das integrações em expansão.',
            'relatorios' => 'Resumo para tomada de decisão sem obrigar o usuário a sair da navegação principal.',
            'configuracoes' => 'Ponto de entrada para tema, permissões e parâmetros do sistema.',
            default => $fallback,
        };
    }
}
