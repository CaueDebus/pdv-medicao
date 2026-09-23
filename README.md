# ComandaFlex

Base inicial full stack em PHP para o ComandaFlex, organizada para crescer em módulos sem perder consistência visual nem navegação.

## Estrutura

- `public/`: ponto de entrada da aplicação e assets.
- `app/`: núcleo PHP com core, controllers, patterns e repositórios.
- `resources/views/`: layout e renderização das telas.
- `config/`: configuração da aplicação e do banco.
- `migrations/`: migrações MySQL em SQL puro.
- `docs/`: documentação funcional e de reúso.
- `docs/`: documentação funcional, de reúso e de migrações.

## Como rodar

1. Aponte o servidor local do PHP para `public/`.
2. Ajuste as variáveis `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD` se for usar MySQL.
3. Execute as migrações com o comando único.
4. Rode o seed inicial do admin.

O passo a passo completo ficou em [docs/como-rodar.md](docs/como-rodar.md).
Detalhes do runner e do rollback estão em [docs/sistema-de-migracoes.md](docs/sistema-de-migracoes.md).

Comando único de migração:

```bash
php scripts/migrate.php up
```

Rollback do último batch:

```bash
php scripts/migrate.php down
```

Status das migrations:

```bash
php scripts/migrate.php status
```

Exemplo com servidor embutido do PHP:

```bash
php -S localhost:8000 -t public
```

Seed do admin:

```bash
php scripts/seed_admin.php
```

Testes automatizados (unidade + feature, sem dependências externas):

```bash
php scripts/test.php
```

Detalhes e como escrever testes para novas features em [docs/testes.md](docs/testes.md).

Credenciais padrão:

- e-mail: `admin@comandaflex.local`
- senha: `Admin@123`

Se o MySQL ainda não estiver disponível, a tela de login aceita essas mesmas credenciais em modo de desenvolvimento para permitir navegação imediata.

## Funcionalidades implementadas

- Navegação entre as telas principais: dashboard, cardápio, comandas, estoque, produção, módulos, relatórios e configurações.
- Layout único compartilhado entre todas as páginas.
- Autenticação por sessão com login e logout.
- Perfis `operator`, `manager` e `admin` com visibilidade de navegação e roteamento por role.
- **CRUD completo persistindo no MySQL** em quatro telas: cardápio (`products`), comandas (`orders`), estoque (`stock_movements`) e módulos (`modules`).
- Proteção CSRF nas escritas e exclusão restrita por perfil.
- **Variabilidade em tempo de configuração (LPS):** a tabela `modules` define quais telas o produto entrega nesta instalação.
- Migrações MySQL para usuários, produtos, pedidos, itens, movimentos de estoque e módulos, mais o seed do catálogo de módulos.
- Testes de unidade e de feature com runner próprio (`php scripts/test.php`) e classe base `Tests\BaseTest`.
- Documentação de reúso de software.

### Padrões de projeto aplicados

| Padrão | Exemplos codificados |
| --- | --- |
| Singleton | `Config`, `Database`, `Session` |
| Template Method | `AbstractScreenTemplate` (+ `DashboardTemplate`, `OperationsTemplate`, `ReportsTemplate`) e `AbstractCrudRepository` |
| Factory | `ScreenFactory` (escolhe o template da tela) e `CrudFactory` (escolhe o recurso CRUD da rota) |
| Strategy | `OperatorNavigationStrategy`, `ManagerNavigationStrategy`, `AdminNavigationStrategy` |
| Repository | `Dashboard`, `Product`, `Order`, `StockMovement`, `Module`, `User` |

Detalhes e justificativas em [docs/reuso-de-software.md](docs/reuso-de-software.md).

## O que ainda não foi implementado

- Itens da comanda (`order_items`) ainda não têm tela própria.
- Pagamento e fechamento financeiro da comanda.
- Painel administrativo com edição de permissões.
- Integração com impressão, NFC-e ou APIs externas.
- Testes de integração com MySQL real (a suíte roda em modo demo, com o banco offline).
- Pipeline de deploy.

## Próximos passos sugeridos

1. Criar a tela de itens da comanda reaproveitando a `CrudFactory`.
2. Implementar o fluxo de pagamento e o fechamento da comanda.
3. Adicionar testes de integração com MySQL real.
4. Separar a camada de domínio em entidades e casos de uso conforme o sistema crescer.
