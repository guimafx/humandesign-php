# HumanDesign PHP

[![Tests](https://github.com/guimafx/humandesign-php/actions/workflows/tests.yml/badge.svg)](https://github.com/guimafx/humandesign-php/actions/workflows/tests.yml)

Engine open source de cálculo de Human Design em PHP, usando Swiss Ephemeris como provedor astronômico externo.

## Visão geral

O projeto recebe dados de nascimento, converte o instante local para UTC, obtém longitudes eclípticas e monta uma representação estruturada do mapa. A implementação atual inclui uma aplicação web MVC pequena e uma API de classes ainda não estabilizada.

## Estado atual

O núcleo corresponde ao escopo `v0.1.0-alpha`: é utilizável para desenvolvimento e validação, mas ainda não constitui uma release pública estável. O mapa completo principal foi comparado a uma referência visual; são necessários mais mapas independentes de regressão.

## Funcionalidades implementadas

- `SwissEphemerisProvider` baseado no executável `swetest`, com longitudes astronômicas reais;
- conversão de data e hora local para UTC;
- data Design calculada pelo arco solar de 88°;
- Personality e Design com 13 corpos cada e 26 ativações no total;
- Terra derivada do Sol + 180° e Nodo Sul do Nodo Norte verdadeiro + 180°;
- Mandala Rave iniciada pelo portão 41 em 302°;
- gate, line, color, tone e base;
- gates ativos, canais completos e centros definidos;
- tipo, autoridade, definição, perfil, strategy, signature e not-self theme;
- estrutura ordenada dos quatro gates da Cruz de Encarnação.

## Funcionalidades pendentes

- resolver nome, ângulo e quarto da Cruz de Encarnação;
- variables, cognition, determination, environment, perspective e motivation;
- orientações left/right;
- BodyGraph SVG;
- pacote Composer e API pública estável;
- mais mapas independentes de regressão.

Consulte o [roadmap](ROADMAP.md) para o planejamento por versão.

## Requisitos

- PHP 8.1 ou superior, com CLI para servidor e testes;
- funções padrão de JSON e `proc_open` disponíveis;
- Swiss Ephemeris (`swetest` e arquivos `.se1`) para resultados astronômicos reais;
- Apache 2 com `mod_rewrite` ou o servidor embutido do PHP para a interface web.

Não há dependências PHP externas nem Composer nesta versão. O Swiss Ephemeris é uma dependência externa e não é distribuído pelo repositório.

## Instalação

```bash
git clone https://github.com/guimafx/humandesign-php.git
cd humandesign-php
cp .env.example .env
```

Não existe etapa de `composer install`: o projeto usa o autoloader próprio em `app/Core/Autoloader.php`.

## Instalação do Swiss Ephemeris

Instale o `swetest` e os arquivos de efemérides conforme as instruções oficiais do fornecedor. Os caminhos esperados por padrão são:

```text
/usr/local/bin/swetest
/usr/local/share/swisseph/ephe
```

O repositório não baixa nem distribui o binário ou arquivos `.se1`. Veja [docs/SWISS_EPHEMERIS.md](docs/SWISS_EPHEMERIS.md) para configuração e diagnóstico.

## Configuração do `.env`

```env
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8080
APP_TIMEZONE=America/Sao_Paulo
EPHEMERIS_DRIVER=swiss
SWETEST_BIN=/usr/local/bin/swetest
SWISSEPH_EPHE_PATH=/usr/local/share/swisseph/ephe
```

Drivers disponíveis: `swiss` usa dados reais; `demo` gera valores determinísticos não astronômicos; `strict` impede o cálculo quando não há provedor real configurado. Não use resultados do modo demo como mapas reais.

## Execução local com servidor PHP

```bash
php -S localhost:8080 server.php
```

Acesse `http://localhost:8080`. O endpoint `GET /health` informa o estado básico da aplicação.

## Configuração conceitual com Apache

Aponte o `DocumentRoot` para a pasta `public/`, habilite `mod_rewrite` e permita que o `.htaccess` seja aplicado (`AllowOverride All`). Configure as variáveis no ambiente do processo Apache ou em `.env`; o usuário do servidor precisa executar `swetest` e ler o diretório de efemérides.

## Exemplo de entrada

```php
$birth = BirthData::fromArray([
    'name' => 'Exemplo',
    'date' => '1982-03-27',
    'time' => '11:05',
    'timezone' => 'America/Sao_Paulo',
    'latitude' => '',
    'longitude' => '',
]);
```

## Exemplo resumido de saída

```json
{
  "metadata": {"ephemeris": "swiss-ephemeris-swetest", "reliable": true},
  "design_date": "1981-12-30T08:47:32+00:00",
  "personality": {"SUN": {"gate": 17, "line": 3}},
  "design": {"SUN": {"gate": 58, "line": 6}},
  "type": {"id": "generator", "name": "Generator"},
  "profile": {"value": "3/6"}
}
```

O resultado real contém as 26 ativações e as demais classificações. Veja [examples/](examples/README.md).

## Executando os testes

```bash
./bin/run-tests
```

O script executa, em ordem alfabética, todos os arquivos PHP diretamente em `tests/` e encerra na primeira falha. Isso inclui `tests/reference_charts.php`, que carrega em ordem estável as fixtures ativas de `tests/reference/`. Os testes de integração exigem `swetest` e os `.se1` nos caminhos padrão; não há dependência de PHPUnit.

### Mapas de referência

Cada fixture PHP retorna um array com `id`, `label`, `birth`, `expected`, `source` e `privacy`; fixtures ativas usam também `status => active`. `birth` registra data, hora, timezone e coordenadas opcionais. `source` identifica provedor, referência verificável e data da conferência. `privacy` registra consentimento e anonimização.

Somente campos presentes em `expected` são comparados. São aceitos `type`, `authority`, `definition`, `profile`, `active_channels`, `defined_centers`, `personality` e `design`. Templates `status => pending` documentam lacunas, podem manter nascimento, fonte e expectativas vazios e não entram na regressão. Para ativá-los, é obrigatório preencher dados reais, uma fonte independente confiável e consentimento, então remover o motivo de pendência. Resultados esperados nunca devem ser ajustados para agradar ao código.

## Arquitetura

O autoloader próprio mapeia o namespace `App\` para `app/`. A entrada HTTP cria o provedor de efemérides e registra o `HumanDesignCalculator`, que ainda é o orquestrador principal. Domínio, provedores, mapeamento e classificadores são classes separadas, enquanto controllers e views compõem a interface MVC.

Detalhes e diagrama: [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md). Decisões matemáticas: [docs/MATHEMATICS.md](docs/MATHEMATICS.md).

## Precisão e validação

O modo `swiss` produz longitudes reais, mas a precisão do mapa completo ainda precisa ser ampliada com referências independentes. O offset da Mandala e as 26 ativações foram validados contra um mapa conhecido, e há testes de fronteiras angulares e do arco solar. Isso não significa que todas as regras de Human Design estejam implementadas ou validadas.

Leia [docs/VALIDATION.md](docs/VALIDATION.md) antes de interpretar resultados.

## Roadmap

As próximas etapas incluem ampliar regressões e classificações, resolver a Cruz com fonte validada, implementar variáveis e renderer SVG, estabilizar a API e preparar a `v1.0.0`. Consulte [ROADMAP.md](ROADMAP.md).

## Como contribuir

Issues e pull requests são bem-vindos. Prepare o ambiente, execute toda a suíte e documente a fonte de qualquer regra ou referência. Não altere resultados esperados apenas para fazer testes passarem. Leia [CONTRIBUTING.md](CONTRIBUTING.md) e o [Código de Conduta](CODE_OF_CONDUCT.md).

## Licenciamento

A licença do código próprio deste projeto ainda precisa ser definida pelo mantenedor. A integração e qualquer distribuição envolvendo Swiss Ephemeris precisam de revisão específica de compatibilidade de licenças. Este repositório não distribui automaticamente o binário `swetest` nem arquivos `.se1`.

Nenhuma conclusão jurídica definitiva é apresentada aqui. A decisão de licença permanece uma pendência obrigatória antes de uma release pública estável. Veja [ADR-008](docs/DECISIONS.md#adr-008--licença-do-projeto-permanece-pendente-de-revisão).

## Aviso de escopo

Este software é experimental, não é apresentado como cientificamente comprovado e não implementa todas as regras de Human Design. Ele não substitui aconselhamento médico, jurídico ou psicológico. Verifique entradas, ambiente astronômico e resultados antes de qualquer uso relevante.
