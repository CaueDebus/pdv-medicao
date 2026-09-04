# Testes

O ComandaFlex tem uma bateria de testes de **unidade** e de **feature** que roda
sem dependências externas. Não há PHPUnit nem Composer install: o runner é
próprio, para manter a base alinhada ao `AGENTS.md` ("não introduzir bibliotecas
novas sem necessidade clara").

## Como rodar

```bash
php scripts/test.php                        # tudo
php scripts/test.php Unit                   # só tests/Unit
php scripts/test.php Feature                 # só tests/Feature
php scripts/test.php Unit/Core/RouterTest.php
```

O comando devolve **exit code 0** quando tudo passa e **1** quando há falha, então
serve direto para pipeline/CI.

## Estrutura

```
tests/
  BaseTest.php          # classe base de TODO teste (asserções + ciclo de vida)
  AssertionFailed.php   # exceção interna de asserção
  TestRunner.php        # descoberta + execução
  bootstrap.php         # ambiente determinístico (banco sempre offline = modo demo)
  Unit/                 # 1 classe/arquivo sob teste, sem tocar em outras camadas
    Core/  Repositories/  Domain/  Controllers/
  Feature/             # fluxo ponta a ponta (ex.: login, render de página por perfil)
scripts/test.php        # entrada: php scripts/test.php
```

- **Unidade**: exercita uma classe isolada (um repositório, um template, uma
  strategy, o Router...).
- **Feature**: exercita um caminho de uso real combinando várias camadas
  (`AuthController` + `Session`, `PageController` + Router + Factory + Template +
  Strategy + View).

## Ambiente dos testes

`tests/bootstrap.php` força `DB_HOST/DB_PORT` para uma porta fechada, então
`Database::instance()->connected()` é sempre `false` e os repositórios devolvem os
dados demo. Isso mantém os testes determinísticos e sem depender de MySQL.
Também inicia a sessão de CLI antes de qualquer saída e zera `$_SESSION`.

## Como escrever um teste

1. Crie o arquivo em `tests/Unit/...` ou `tests/Feature/...`.
2. O nome do arquivo e da classe terminam em `Test` (ex.: `PromocaoRepositoryTest`).
3. O namespace segue a pasta: `tests/Unit/Repositories/PromocaoRepositoryTest.php`
   → `namespace Tests\Unit\Repositories;`.
4. A classe estende `Tests\BaseTest`.
5. Cada caso é um método público `testAlgumComportamento()` no padrão
   **Arrange / Act / Assert**.
6. Use `setUp()` / `tearDown()` para o preparo/limpeza comum (ex.: `$_SESSION = []`).

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\PromocaoRepository;
use Tests\BaseTest;

final class PromocaoRepositoryTest extends BaseTest
{
    public function testListaPromocoesDemoQuandoOffline(): void
    {
        // Arrange
        $repository = new PromocaoRepository();

        // Act
        $promocoes = $repository->ativas();

        // Assert
        $this->assertCount(2, $promocoes);
        $this->assertArrayHasKey('titulo', $promocoes[0]);
    }
}
```

## Asserções disponíveis na BaseTest

`assertTrue` · `assertFalse` · `assertSame` (===) · `assertEquals` (==) ·
`assertNull` · `assertNotNull` · `assertEmpty` · `assertNotEmpty` ·
`assertCount` · `assertArrayHasKey` · `assertContains` (in_array estrito) ·
`assertStringContainsString` · `assertStringNotContainsString` ·
`assertInstanceOf` · `assertGreaterThan` · `assertThrows(Classe::class, fn)` ·
`fail(mensagem)`.

Todas aceitam uma mensagem final opcional que aparece no relatório em caso de falha.

Se faltar uma asserção, adicione na `BaseTest` (é a única fonte) seguindo o mesmo
padrão: incrementar o contador e lançar `AssertionFailed` na expectativa quebrada.

## Checklist ao adicionar uma feature

Toda nova feature **entra junto com os testes**, na mesma mudança:

1. **Unidade** para cada classe nova de domínio/infra (repositório, template,
   strategy, serviço). Sem MySQL, valide o caminho de fallback demo.
2. **Feature** para o caminho de uso: rota nova → um teste em `tests/Feature/`
   que renderiza via `PageController` e confere o HTML/estado resultante.
3. Se a feature mexe em **perfil/permissão**, cubra os três perfis
   (`operator`, `manager`, `admin`) como em `tests/Feature/RoleAccessTest.php`.
4. Se a feature tem **regra condicional** (fallback, bloqueio, filtro), teste o
   caso que passa e o que não passa.
5. `php scripts/test.php` verde antes de considerar a feature pronta.

## Regra do projeto

**Toda nova feature entra com teste.** As regras estão em `AGENTS.md` →
"Regras Para Futuras Features" e "Testes Obrigatórios".
