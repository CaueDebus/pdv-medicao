# Como Rodar o ComandaFlex

Este guia descreve como subir o projeto localmente, aplicar as migrações e acessar o sistema com o usuário inicial.

Para entender em detalhes o funcionamento interno do runner, veja [sistema-de-migracoes.md](sistema-de-migracoes.md).

## Pré-requisitos

- PHP 8.2 ou superior
- MySQL 8 ou MariaDB compatível
- `composer` instalado, caso você queira regenerar o autoload

## Estrutura esperada

- `public/` como document root
- `migrations/` com as tabelas SQL
- `scripts/seed_admin.php` para criar o usuário inicial

## Passo a passo

1. Configure as variáveis de ambiente do banco, se necessário:

```bash
set DB_HOST=127.0.0.1
set DB_PORT=3306
set DB_DATABASE=comandaflex
set DB_USERNAME=root
set DB_PASSWORD=sua_senha
```

2. Aplique as migrações com o comando único:

```bash
php scripts/migrate.php up
```

Se quiser conferir o controle:

```bash
php scripts/migrate.php status
```

Se precisar desfazer o último batch:

```bash
php scripts/migrate.php down
```

3. Crie o admin inicial:

```bash
php scripts/seed_admin.php
```

4. Inicie o servidor local:

```bash
php -S localhost:8000 -t public
```

5. Acesse:

```text
http://localhost:8000
```

## Credenciais padrão

- E-mail: `admin@comandaflex.local`
- Senha: `Admin@123`

## Modo de desenvolvimento sem MySQL

Se o banco ainda não estiver disponível, a tela de login aceita as mesmas credenciais acima para permitir navegação local imediata.

Nesse modo as telas de CRUD continuam navegáveis, mas exibem o aviso "Modo demonstração" e **não gravam nada**: a listagem mostra dados de exemplo do repositório. Para exercitar criação, edição e exclusão de verdade, rode as migrações com o MySQL no ar.

## Telas de CRUD

Quatro telas fazem CRUD completo sobre o MySQL, todas servidas pelo mesmo controller e pelas mesmas views:

| Tela | Tabela | Observação |
| --- | --- | --- |
| Cardápio | `products` | categorias dependem dos módulos de venda ligados |
| Comandas | `orders` | status vem do enum da migration |
| Estoque | `stock_movements` | lista com JOIN em `products` |
| Módulos | `modules` | é o painel de variabilidade do produto |

Exclusão é restrita: cardápio, comandas e estoque exigem perfil `manager` ou `admin`; módulos exigem `admin`.

## Ligar e desligar módulos (variabilidade)

A migration `007_seed_modules.sql` popula o catálogo de módulos. Para ver a variabilidade funcionando:

1. Entre como `admin` e abra a tela **Módulos**.
2. Edite o "Módulo Estoque" e desmarque "Ativo nesta instalação".
3. A tela de Estoque some da navegação e a rota passa a cair no dashboard.
4. Reative o módulo para trazer a tela de volta.

A tela de Configurações lista quais módulos estão ligados nesta instalação.

## Rodar os testes

Não precisa de MySQL nem de `composer install`:

```bash
php scripts/test.php            # tudo
php scripts/test.php Unit       # só unidade
php scripts/test.php Feature    # só feature
```

Exit code 0 = tudo verde. Ver [testes.md](testes.md).

## Comandos úteis

```bash
php -l app/Controllers/AuthController.php
php -l app/Controllers/PageController.php
php -S localhost:8000 -t public
php scripts/test.php
```

## Observações

- O login usa sessão PHP.
- Perfis disponíveis: `operator`, `manager` e `admin`.
- O menu e as páginas respeitam o perfil autenticado.
