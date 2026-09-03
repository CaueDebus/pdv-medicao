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

Credenciais padrão:

- e-mail: `admin@comandaflex.local`
- senha: `Admin@123`

Se o MySQL ainda não estiver disponível, a tela de login aceita essas mesmas credenciais em modo de desenvolvimento para permitir navegação imediata.

## Funcionalidades implementadas

- Navegação entre as telas principais: dashboard, cardápio, comandas, estoque, produção, módulos, relatórios e configurações.
- Layout único compartilhado entre todas as páginas.
- Singletons para configuração e banco.
- Autenticação por sessão com login e logout.
- Perfis `operator`, `manager` e `admin` com visibilidade de navegação por role.
- Template Method para montagem de telas.
- Factory para selecionar o template da tela.
- Repository para leitura de dados e fallback demo.
- Strategy para visibilidade da navegação por perfil.
- Migrações MySQL iniciais para usuários, produtos, pedidos, itens, movimentos de estoque e módulos.
- Documentação de reúso de software.

## O que ainda não foi implementado

- CRUD completo persistindo no MySQL para todas as telas.
- Painel administrativo com edição de permissões.
- Integração com impressão, NFC-e ou APIs externas.
- Testes automatizados.
- Pipeline de deploy.

## Próximos passos sugeridos

1. Criar CRUD real para cardápio, comandas e estoque.
2. Trocar os dados demo por CRUD real usando as tabelas migradas.
3. Adicionar testes de integração para rotas e repositórios.
4. Separar a camada de domínio em entidades e casos de uso conforme o sistema crescer.
