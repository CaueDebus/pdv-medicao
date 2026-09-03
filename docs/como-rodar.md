# Como Rodar o ComandaFlex

Este guia descreve como subir o projeto localmente, aplicar as migrações e acessar o sistema com o usuário inicial.

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

2. Aplique as migrações na ordem dos arquivos em `migrations/`.

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

## Comandos úteis

```bash
php -l app/Controllers/AuthController.php
php -l app/Controllers/PageController.php
php -S localhost:8000 -t public
```

## Observações

- O login usa sessão PHP.
- Perfis disponíveis: `operator`, `manager` e `admin`.
- O menu e as páginas respeitam o perfil autenticado.
