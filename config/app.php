<?php

return [
    'name' => 'ComandaFlex',
    'version' => '0.1.0',
    'locale' => 'pt-BR',
    'default_page' => 'dashboard',
    'roles' => ['operator', 'manager', 'admin'],
    'routes' => [
        'dashboard' => ['label' => 'Dashboard', 'description' => 'Resumo operacional'],
        'cardapio' => ['label' => 'Cardápio', 'description' => 'Produtos e preços'],
        'comandas' => ['label' => 'Comandas', 'description' => 'Abertura e fechamento'],
        'estoque' => ['label' => 'Estoque', 'description' => 'Disponibilidade e alertas'],
        'producao' => ['label' => 'Produção', 'description' => 'Fila da cozinha e bar'],
        'modulos' => ['label' => 'Módulos', 'description' => 'Recursos do produto'],
        'relatorios' => ['label' => 'Relatórios', 'description' => 'Indicadores e totais'],
        'configuracoes' => ['label' => 'Configurações', 'description' => 'Tema e preferências'],
    ],
];
