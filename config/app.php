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
        'itens' => ['label' => 'Itens da comanda', 'description' => 'Produtos lançados por comanda'],
        'mesas' => ['label' => 'Mesas', 'description' => 'Salão, lugares e ocupação'],
        'clientes' => ['label' => 'Clientes', 'description' => 'Contatos e preferências'],
        'estoque' => ['label' => 'Estoque', 'description' => 'Disponibilidade e alertas'],
        'fornecedores' => ['label' => 'Fornecedores', 'description' => 'Contatos de reposição'],
        'producao' => ['label' => 'Produção', 'description' => 'Fila da cozinha e bar'],
        'estacoes' => ['label' => 'Estações', 'description' => 'Postos da cozinha e do bar'],
        'pagamentos' => ['label' => 'Pagamentos', 'description' => 'Formas aceitas no caixa'],
        'modulos' => ['label' => 'Módulos', 'description' => 'Recursos do produto'],
        'relatorios' => ['label' => 'Relatórios', 'description' => 'Indicadores e totais'],
        'configuracoes' => ['label' => 'Configurações', 'description' => 'Tema e preferências'],
    ],

    /*
     * Mapa de variabilidade (LPS): liga cada tela ao código do módulo que a
     * habilita, na tabela `modules`. Tela sem entrada aqui é núcleo do produto
     * e está sempre presente. Ver App\Domain\Variability\FeatureToggle.
     *
     * Um módulo pode habilitar mais de uma tela: `stock` entrega Estoque e
     * Fornecedores; `production` entrega Produção e Estações.
     */
    'features' => [
        'estoque' => 'stock',
        'fornecedores' => 'stock',
        'producao' => 'production',
        'estacoes' => 'production',
        'relatorios' => 'reports',
    ],
];
