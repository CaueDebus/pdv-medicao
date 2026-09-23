INSERT INTO modules (name, code, description, enabled) VALUES
    ('Módulo Comida', 'menu_food', 'Fluxo de preparo e mesas', 1),
    ('Módulo Bebida', 'menu_drink', 'Balcão e bar', 1),
    ('Módulo Estoque', 'stock', 'Movimentações e alertas de reposição', 1),
    ('Módulo Produção', 'production', 'Fila de cozinha e bar', 1),
    ('Módulo Relatórios', 'reports', 'Indicadores gerenciais', 1),
    ('Integração hotel', 'hotel_integration', 'Conector externo pendente', 0)
ON DUPLICATE KEY UPDATE description = VALUES(description);
