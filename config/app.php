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

    /*
     * Mapa de variabilidade (LPS): liga cada tela ao código do módulo que a
     * habilita, na tabela `modules`. Tela sem entrada aqui é núcleo do produto
     * e está sempre presente. Ver App\Domain\Variability\FeatureToggle.
     */
    'features' => [
        'estoque' => 'stock',
        'producao' => 'production',
        'relatorios' => 'reports',
    ],
];
