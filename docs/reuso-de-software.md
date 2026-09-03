# Reúso de Software

Este documento registra o que foi reaproveitado e quais decisões de design foram adotadas na base inicial do ComandaFlex.

## Flags

- `reuso-ui-design-system`: sim, a linguagem visual do protótipo foi reaproveitada como referência de cor, contraste, bordas e foco em leitura rápida.
- `reuso-layout-base`: sim, as telas usam um único layout com navegação lateral compartilhada.
- `reuso-dados-fallback`: sim, os repositórios retornam dados demo quando o MySQL ainda não estiver disponível.
- `reuso-template-screens`: sim, as telas compartilham templates de montagem para reduzir repetição.
- `reuso-acesso-por-perfil`: parcial, a navegação já diferencia operador e admin, mas autenticação real ainda não existe.

## Padrões de projeto adotados

### Singletons

- `App\Core\Config`: centraliza acesso às configurações da aplicação.
- `App\Core\Database`: concentra a conexão PDO com MySQL e evita múltiplas conexões ao longo da requisição.

### Template Method

- `App\Domain\Patterns\Template\AbstractScreenTemplate`: base para montar cada tela com estrutura consistente.
- `DashboardTemplate`, `OperationsTemplate`, `ReportsTemplate`: exemplos concretos do template method para telas distintas.

### Factory

- `App\Domain\Patterns\Factory\ScreenFactory`: decide qual template de tela usar conforme a rota.

### Repository

- `DashboardRepository`, `ProductRepository`, `OrderRepository`, `ModuleRepository`: isolam a leitura de dados e permitem trocar o fallback por MySQL sem mexer na camada de tela.

### Strategy

- `NavigationVisibilityStrategy`: define como a navegação é decorada para cada perfil.
- `OperatorNavigationStrategy` e `AdminNavigationStrategy`: alteram bloqueios e estados da navegação sem mudar o layout.

## Razões de reúso

- O layout único reduz manutenção e garante navegação consistente entre todas as telas.
- O uso de templates de tela diminui duplicação na montagem de páginas.
- Os repositórios com fallback permitem abrir a aplicação mesmo antes das migrações e do banco estarem prontos.

## O que foi evitado

- Não foi criado um card ou tela específica para cada módulo do cliente.
- Não foi criado banco de dados em memória separado do MySQL.
- Não foi criada navegação diferente por tela; o menu é central e reaproveitado.
