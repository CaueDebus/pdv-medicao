# Reúso de Software

Este documento registra o que foi reaproveitado e quais decisões de design foram adotadas na base inicial do ComandaFlex.

O objetivo acadêmico aqui é demonstrar que o sistema não foi montado como telas isoladas: a cada feature, deve existir uma justificativa de reaproveitamento, desacoplamento e manutenção futura.

## Flags

- `reuso-ui-design-system`: sim, a linguagem visual do protótipo foi reaproveitada como referência de cor, contraste, bordas e foco em leitura rápida.
- `reuso-layout-base`: sim, as telas usam um único layout com navegação lateral compartilhada.
- `reuso-dados-fallback`: sim, os repositórios retornam dados demo quando o MySQL ainda não estiver disponível.
- `reuso-template-screens`: sim, as telas compartilham templates de montagem para reduzir repetição.
- `reuso-acesso-por-perfil`: sim, a navegação e o roteamento diferenciam operador, gerente e admin, cada um com sua estratégia.
- `reuso-crud-generico`: sim, as quatro telas de CRUD compartilham um único controller, uma única listagem e um único formulário; o que muda é a descrição do recurso.
- `reuso-variabilidade-por-modulo`: sim, quais telas o produto entrega é dado da tabela `modules`, não código duplicado por cliente.
- `reuso-migrations-sql-puro`: sim, o schema é versionado com SQL puro para facilitar leitura, revisão e rollback.
- `reuso-seed-admin`: sim, o usuário inicial é criado por seed para acelerar a entrada no sistema.
- `reuso-testes-basetest`: sim, todos os testes estendem `Tests\BaseTest`, reaproveitando asserções e ciclo de vida em vez de cada teste montar seu próprio scaffold.
- `reuso-documentacao-viva`: sim, toda mudança relevante deve refletir em documentação do projeto.

## Importância Dos Padrões

### Singleton

- Evita múltiplas configurações conflitantes e múltiplas conexões no mesmo fluxo.
- É útil quando há um recurso global por requisição, como banco e configuração.

### Template Method

- Ajuda a manter telas homogêneas sem copiar estrutura.
- Facilita criar novas páginas reaproveitando o mesmo esqueleto.

### Factory

- Isola a regra de escolha de tela.
- É importante quando a aplicação cresce e a criação de objetos deixa de ser trivial.

### Repository

- Protege o restante da aplicação de detalhes de SQL e persistência.
- Torna mais fácil trocar fallback por dados reais sem reescrever as telas.

### Strategy

- Permite perfis diferentes sem duplicar menus ou regras de exibição.
- É útil para regras que variam por contexto, como operador, gerente e admin.

## Reúso Para Futuras Features

- `reuso-futuras-telas`: novas páginas devem herdar o layout e os templates existentes sempre que possível.
- `reuso-futuro-crud`: novos CRUDs devem seguir o padrão repository + controller + view já iniciado.
- `reuso-futuras-permissoes`: qualquer feature com regra de acesso deve reaproveitar o mecanismo de sessão e perfis.
- `reuso-futuras-migrations`: novas tabelas devem vir com migration, down migration e atualização do runner quando necessário.
- `reuso-futuro-seed`: se a feature precisar de dados de apoio, deve ter seed documentado e reversível quando fizer sentido.
- `reuso-futuro-dashboard`: métricas novas devem reaproveitar blocos de resumo e não criar um sistema paralelo de indicadores.
- `reuso-futuros-testes`: toda feature nova entra com teste que estende `Tests\BaseTest`; asserção que falte é adicionada na própria base, sem utilitário de teste paralelo.

## Padrões de projeto adotados

### Singletons

- `App\Core\Config`: centraliza acesso às configurações da aplicação.
- `App\Core\Database`: concentra a conexão PDO com MySQL e evita múltiplas conexões ao longo da requisição.

### Template Method

- `App\Domain\Patterns\Template\AbstractScreenTemplate`: base para montar cada tela com estrutura consistente.
- `DashboardTemplate`, `OperationsTemplate`, `ReportsTemplate`: exemplos concretos do template method para telas distintas.
- `App\Repositories\AbstractCrudRepository`: segunda aplicação do padrão, agora na persistência. O esqueleto de listar, buscar, gravar e excluir é `final`; cada repositório concreto só informa tabela, colunas, ordenação e dados de demonstração.

### Factory

- `App\Domain\Patterns\Factory\ScreenFactory`: decide qual template de tela usar conforme a rota.
- `App\Domain\Patterns\Factory\CrudFactory`: decide qual recurso CRUD (repositório + rótulos + campos) atende a rota. É o que permite um controller único servir cardápio, comandas, estoque e módulos.

### Repository

- `DashboardRepository`, `ProductRepository`, `OrderRepository`, `StockMovementRepository`, `ModuleRepository`, `UserRepository`: isolam persistência e permitem trocar o fallback por MySQL sem mexer na camada de tela.
- `App\Repositories\Contracts\CrudRepository`: contrato mínimo (`all`, `find`, `create`, `update`, `delete`, `persists`) que o controller genérico consome.

### Strategy

- `NavigationVisibilityStrategy`: define como a navegação é decorada para cada perfil.
- `OperatorNavigationStrategy`, `ManagerNavigationStrategy` e `AdminNavigationStrategy`: alteram bloqueios e estados da navegação sem mudar o layout. O operador não vê relatórios nem configurações; o gerente vê relatórios mas não configurações; o admin vê tudo.

## CRUD genérico

As telas de cardápio, comandas, estoque e módulos são CRUD completo sobre MySQL e **não** têm controller nem view próprios:

- `App\Domain\Crud\CrudField` descreve um campo (rótulo, tipo, opções, obrigatoriedade) e sabe formatar e converter o valor.
- `App\Domain\Crud\CrudResource` junta repositório + campos + regra de exclusão por perfil, e concentra a validação.
- `App\Controllers\CrudController` executa sempre o mesmo fluxo: formulário, validação, gravação, redirect com mensagem.
- `resources/views/crud/table.php` e `resources/views/crud/form.php` percorrem os campos declarados; nenhuma das duas conhece produto, comanda, movimentação ou módulo.

Consequência prática: **adicionar um CRUD novo é adicionar um método na `CrudFactory`** — sem controller novo, sem view nova, sem SQL novo.

Cuidados de segurança adotados: as colunas graváveis são lista branca declarada no repositório (nada que venha do formulário entra no SQL sem estar declarado), todos os valores vão por prepared statement, a saída é escapada com `htmlspecialchars` e toda escrita exige token CSRF de sessão.

## Variabilidade (Linha de Produto de Software)

O ponto de variação do ComandaFlex é a tabela `modules`: cada linha é uma feature do produto, com `code` e `enabled`.

- `config/app.php` → chave `features`: mapa `tela => código do módulo`.
- `App\Domain\Variability\FeatureToggle`: ponto único de decisão. Responde se um módulo está ligado e se uma tela está habilitada.
- `PageController` consulta o toggle para montar navegação e autorizar rota. Tela sem entrada no mapa é núcleo do produto e está sempre presente.

Dois níveis de variabilidade são exercitados:

1. **Grossa (tela):** desligar o módulo `stock` remove a tela de Estoque da navegação e do roteamento — sem remover código e sem criar uma variante paralela do sistema.
2. **Fina (campo):** as categorias ofertadas no cadastro de produto dependem dos módulos de venda ligados. Com `menu_drink` desligado, "Bebida" deixa de ser uma opção do formulário.

A tela de Módulos é o próprio painel de configuração do produto: é um CRUD como os outros, e o que ele edita é a composição da instalação.

### Base de testes (Template Method + reúso de asserções)

- `Tests\BaseTest`: concentra as asserções e o ciclo `setUp`/`run`/`tearDown`. Cada classe de teste só descreve os casos, no padrão Arrange/Act/Assert.
- `Tests\TestRunner`: descobre os arquivos `*Test.php` e executa, sem depender de PHPUnit/Composer.
- `tests/bootstrap.php`: fixa o ambiente (banco offline = modo demo) para os testes serem determinísticos.
- Ver [docs/testes.md](testes.md).

## Razões de reúso

- O layout único reduz manutenção e garante navegação consistente entre todas as telas.
- O uso de templates de tela diminui duplicação na montagem de páginas.
- Os repositórios com fallback permitem abrir a aplicação mesmo antes das migrações e do banco estarem prontos.
- A documentação centralizada reduz perda de contexto quando novas features forem adicionadas.
- A estratégia de migrações em SQL puro facilita revisão acadêmica e demonstração do processo.

## O que foi evitado

- Não foi criado um card ou tela específica para cada módulo do cliente.
- Não foi criado banco de dados em memória separado do MySQL.
- Não foi criada navegação diferente por tela; o menu é central e reaproveitado.
- Não foi criado CRUD duplicado para cada perfil ou módulo.
- Não foi criada lógica de autenticação separada por tela.
- Não foi criado um controller por entidade: um `CrudController` atende todas as entidades.
- Não foi criada uma cópia do sistema por cliente: a variação é dado na tabela `modules`, lida por um único `FeatureToggle`.
