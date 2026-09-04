# AGENTS.md

## Contexto Do Projeto

Este workspace é um trabalho de faculdade com foco em reúso de software, arquitetura modular e documentação clara.

O sistema ComandaFlex deve evoluir com o menor acoplamento possível entre camadas, favorecendo reaproveitamento de componentes, padrões de projeto e rastreabilidade das decisões.

## Regras Gerais

- Priorize reutilização antes de criar componentes novos.
- Preserve a estrutura modular já existente.
- Não apague documentação existente; complemente quando houver novo comportamento.
- Sempre que um padrão de projeto for introduzido, documente o motivo e o ganho esperado.
- Prefira mudanças pequenas e localizadas.
- Mantenha nomes coerentes com o domínio do sistema: comanda, cardápio, estoque, produção, módulos e relatórios.
- Se houver comportamento de demo, deixe explícito que é fallback e não persistência real.
- Não introduza bibliotecas novas sem necessidade clara.

## Diretrizes De Arquitetura

- Camada de apresentação: views e layout compartilhado.
- Camada de aplicação: controllers e orquestração do fluxo.
- Camada de domínio: regras do negócio e padrões de comportamento.
- Camada de infraestrutura: banco, seed, migrações e acesso a dados.

## Padrões De Projeto E Importância

### Singleton

- Uso atual: configuração e banco.
- Importância: centraliza acesso a recursos compartilhados e evita instâncias redundantes dentro da mesma requisição.

### Template Method

- Uso atual: montagem de telas por tipo de contexto.
- Importância: garante uma estrutura fixa e reduz duplicação entre telas parecidas.

### Factory

- Uso atual: escolha do template de tela.
- Importância: desacopla a decisão de criação da classe que consome o objeto.

### Repository

- Uso atual: leitura de dados de produtos, pedidos, módulos e dashboard.
- Importância: separa consulta de dados da regra de apresentação e facilita troca de fallback por MySQL real.

### Strategy

- Uso atual: visibilidade da navegação por perfil.
- Importância: permite variar comportamento sem espalhar condicionais pela aplicação.

## Testes

- A base tem testes de unidade e de feature em `tests/`, executados por `php scripts/test.php` (sem PHPUnit/Composer, para não introduzir biblioteca nova).
- Classe base única: `Tests\BaseTest` em [tests/BaseTest.php](tests/BaseTest.php). Toda classe de teste estende essa classe e usa as asserções dela.
- `tests/Unit/` isola uma classe por vez; `tests/Feature/` cobre um fluxo ponta a ponta.
- `tests/bootstrap.php` força o banco offline, então os testes exercitam o modo demo/fallback de forma determinística.
- Cada caso segue Arrange / Act / Assert e fica em um método público `testAlgumComportamento()`.
- Detalhes e exemplo de esqueleto: [docs/testes.md](docs/testes.md).

## Testes Obrigatórios

- Nenhuma feature é considerada pronta sem testes na mesma mudança.
- Classe nova de domínio ou infraestrutura (repositório, template, strategy, serviço) entra com teste de unidade cobrindo o caminho feliz e o fallback demo.
- Rota ou tela nova entra com teste de feature que renderiza via `PageController` e verifica o resultado.
- Feature com regra condicional (fallback, bloqueio, filtro) testa o caso que passa e o que não passa.
- Feature que mexe em perfil/permissão testa os perfis `operator`, `manager` e `admin`.
- Se faltar uma asserção, adicione na `Tests\BaseTest` seguindo o padrão existente; não crie um utilitário de teste paralelo.
- `php scripts/test.php` deve terminar verde (exit code 0) antes de fechar a feature.

## Protótipo E Design System

- O protótipo visual oficial é [ComandaFlex_Design_System.html](ComandaFlex_Design_System.html). Ele é a fonte de verdade para cor, tipografia, espaçamento, forma, botões, campos, badges e status, cards, listas, navegação, alertas e toasts, disponibilidade de estoque, fila de produção e chip de módulo.
- Toda nova feature ou tela deve seguir o protótipo: reutilize os tokens, classes e padrões de componente já definidos nele antes de criar estilo ou marcação nova.
- Se a tela da nova feature não existir no protótipo, derive-a dos componentes base já documentados em vez de inventar um padrão visual novo.
- Divergir do protótipo só é permitido quando houver necessidade funcional clara. Nesse caso, documente o motivo, atualize o protótipo e alinhe a [documentação de reúso](docs/reuso-de-software.md).
- Não introduza novos padrões visuais fora do protótipo sem antes registrar a decisão.

## Regras Para Futuras Features

- Toda nova feature deve indicar se reutiliza layout, repositório, template, estratégia ou seed existentes.
- Toda nova feature deve ser validada contra o protótipo [ComandaFlex_Design_System.html](ComandaFlex_Design_System.html) antes de ser considerada pronta.
- Toda nova feature deve entrar com testes de unidade e/ou feature na mesma mudança (ver "Testes Obrigatórios").
- Se uma tela nova repetir padrão visual ou estrutural, reutilize o componente base antes de criar outro.
- Se a feature exigir tabela nova, registre migração e rollback correspondente.
- Se a feature depender de dados iniciais, adicione seed documentado.
- Se a feature alterar perfil/permissão, atualize o fluxo de autenticação e a documentação de reúso.
- Se a feature for apenas variação de algo existente, crie extensão do padrão atual em vez de duplicar código.

## Documentação Obrigatória

- Atualize [docs/reuso-de-software.md](docs/reuso-de-software.md) quando houver novo reúso, padrão ou decisão de arquitetura.
- Atualize [docs/testes.md](docs/testes.md) quando o runner, a `Tests\BaseTest` ou a forma de organizar os testes mudar.
- Atualize [ComandaFlex_Design_System.html](ComandaFlex_Design_System.html) quando um novo componente visual ou uma nova variação de estado for introduzido.
- Atualize [docs/como-rodar.md](docs/como-rodar.md) quando mudar comandos ou credenciais.
- Atualize [docs/sistema-de-migracoes.md](docs/sistema-de-migracoes.md) quando o runner ou a estratégia de rollback mudar.
