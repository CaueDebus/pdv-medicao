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

## Regras Para Futuras Features

- Toda nova feature deve indicar se reutiliza layout, repositório, template, estratégia ou seed existentes.
- Se uma tela nova repetir padrão visual ou estrutural, reutilize o componente base antes de criar outro.
- Se a feature exigir tabela nova, registre migração e rollback correspondente.
- Se a feature depender de dados iniciais, adicione seed documentado.
- Se a feature alterar perfil/permissão, atualize o fluxo de autenticação e a documentação de reúso.
- Se a feature for apenas variação de algo existente, crie extensão do padrão atual em vez de duplicar código.

## Documentação Obrigatória

- Atualize [docs/reuso-de-software.md](docs/reuso-de-software.md) quando houver novo reúso, padrão ou decisão de arquitetura.
- Atualize [docs/como-rodar.md](docs/como-rodar.md) quando mudar comandos ou credenciais.
- Atualize [docs/sistema-de-migracoes.md](docs/sistema-de-migracoes.md) quando o runner ou a estratégia de rollback mudar.
