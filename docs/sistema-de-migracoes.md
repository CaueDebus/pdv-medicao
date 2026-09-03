# Sistema de Migrações

Este documento descreve como o sistema de migrações do ComandaFlex funciona hoje: como os arquivos são descobertos, como são aplicados, como fica o controle no banco e como fazer rollback.

## Objetivo

O objetivo do runner é permitir que a estrutura do banco seja versionada junto com o código, usando arquivos SQL simples e reversíveis.

## Visão geral

O processo atual tem três partes:

1. Arquivos `.sql` em [migrations/](../migrations/).
2. Um runner PHP em [app/Core/MigrationRunner.php](../app/Core/MigrationRunner.php).
3. Um script de linha de comando em [scripts/migrate.php](../scripts/migrate.php).

O runner cria e mantém a tabela de controle `schema_migrations` no próprio MySQL.

## Estrutura esperada de arquivos

Cada migration principal deve seguir o padrão:

- `001_create_users.sql`
- `002_create_products.sql`
- `003_create_orders.sql`

Para rollback, cada migration precisa ter um arquivo correspondente:

- `001_create_users.down.sql`
- `002_create_products.down.sql`
- `003_create_orders.down.sql`

O runner identifica somente arquivos `.sql` que não terminam com `.down.sql` para o comando de subida.

## Como o `up` funciona

Quando você executa:

```bash
php scripts/migrate.php up
```

o fluxo é o seguinte:

1. O runner garante que a tabela `schema_migrations` exista.
2. Ele varre a pasta `migrations/` e ordena os arquivos por nome.
3. Ele consulta o banco para descobrir quais migrations já foram aplicadas.
4. Ele executa apenas as pendentes.
5. Depois de executar cada arquivo, registra a migration como aplicada.

## Controle de estado no banco

O sistema usa a tabela `schema_migrations` com as colunas abaixo:

- `id`: identificador interno do registro.
- `migration`: nome do arquivo aplicado.
- `batch`: lote lógico da execução.
- `checksum`: hash SHA-256 do arquivo no momento da aplicação.
- `applied_at`: data e hora da aplicação.

## O que é um batch

Batch é o agrupamento de migrations aplicadas na mesma execução do comando `up`.

Exemplo:

- Primeiro `up` aplica 6 migrations e grava todas com `batch = 1`.
- Segundo `up`, se houver novas migrations, grava o próximo grupo com `batch = 2`.

Isso permite reverter um conjunto inteiro de alterações de forma organizada.

## Como o `status` funciona

Quando você executa:

```bash
php scripts/migrate.php status
```

o runner lista todos os arquivos conhecidos e marca:

- `APLICADA` quando o arquivo já está registrado na `schema_migrations`
- `PENDENTE` quando o arquivo ainda não foi executado

O `status` também exibe o batch e a data de aplicação quando existir.

## Como o rollback funciona

Quando você executa:

```bash
php scripts/migrate.php down
```

o runner reverte o último batch inteiro.

Fluxo:

1. Ele identifica o batch mais recente na tabela `schema_migrations`.
2. Busca todas as migrations daquele batch.
3. Para cada migration, procura o arquivo `.down.sql` correspondente.
4. Executa o SQL de reversão.
5. Remove o registro da migration da tabela de controle.

Se você quiser reverter mais de um batch, pode usar:

```bash
php scripts/migrate.php down 2
```

## Exemplo prático

Se o banco ainda não foi criado, o fluxo normal é:

```bash
php scripts/migrate.php up
php scripts/migrate.php status
php scripts/seed_admin.php
```

Se precisar desfazer a última alteração estrutural:

```bash
php scripts/migrate.php down
```

## Regras de convenção

- O nome do arquivo deve começar com prefixo numérico para manter a ordem.
- O arquivo `.sql` é a migration de subida.
- O arquivo `.down.sql` é a migration de rollback.
- A ordem do rollback segue o batch mais recente.

## Limitações atuais

- As migrations são SQL puro, então o runner não interpreta operações de schema nem gera rollback automaticamente.
- Cada migration precisa ter seu `.down.sql` manualmente escrito.
- O runner usa execução direta de SQL e não faz parsing de múltiplos statements avançados além do que o MySQL aceita via `exec`.
- O processo atual não faz validação semântica do SQL; ele só executa e registra.

## Boas práticas adotadas

- Manter migrations pequenas e focadas.
- Evitar misturar criação de tabelas com carga de dados em massa.
- Sempre criar o `.down.sql` junto com a migration principal.
- Conferir `status` antes e depois das alterações.

## Relação com a aplicação

O sistema de autenticação depende da tabela `users`.

Por isso, antes de usar login real, é necessário rodar as migrations e depois o seed do admin.

Se o MySQL não estiver disponível, a aplicação entra no modo de desenvolvimento com login de fallback, mas isso não substitui o banco real.
