# HumanDesign PHP MVC

Microframework MVC em PHP para organizar e executar uma calculadora de Human Design.

## Requisitos

- PHP 8.1 ou superior
- Apache 2 com `mod_rewrite`, ou servidor interno do PHP
- Extensão JSON
- Para cálculos reais: provedor de efemérides implementado

## Instalação no Windows

Extraia o conteúdo deste ZIP dentro de:

```text
C:\Users\User\Documents\GitHub\humandesign-php
```

O pacote não contém nem substitui a pasta `plugins-amigos`.

### Servidor interno do PHP

No PowerShell:

```powershell
cd C:\Users\User\Documents\GitHub\humandesign-php
copy .env.example .env
php -S localhost:8080 server.php
```

Acesse:

```text
http://localhost:8080
```

### Apache

Aponte o `DocumentRoot` para:

```text
C:\Users\User\Documents\GitHub\humandesign-php\public
```

Ative `mod_rewrite` e permita `AllowOverride All`.

## Importante

O projeto inicia em `strict mode`: sem um provedor astronômico real ele não inventa posições planetárias.

Para testar apenas a interface:

```env
APP_ENV=development
EPHEMERIS_DRIVER=demo
```

O modo `demo` retorna dados marcados explicitamente como não confiáveis.

## Swiss Ephemeris

O driver `swiss` depende do executável `swetest` e dos arquivos de efemérides
do Swiss Ephemeris. Configure seus caminhos com `SWETEST_BIN` e
`SWISSEPH_EPHE_PATH` e ative-o com:

```env
EPHEMERIS_DRIVER=swiss
```

Esse modo produz longitudes astronômicas reais. O mapeamento dessas longitudes
na Mandala Rave foi validado contra um mapa conhecido do myBodyGraph: Guilherme
Borges Viana, nascimento em 27/03/1982 às 11:05 em `America/Sao_Paulo`
(`1982-03-27 14:05:00 UTC`) e Design de referência em
`1981-12-30 08:47:00 UTC`. A validação cobre Sol, Terra, Lua, nodos verdadeiros,
Mercúrio, Vênus, Marte, Júpiter, Saturno, Urano, Netuno e Plutão, em Personality
e Design.

A sequência Rave avança com a longitude zodiacal a partir do limite inicial do
portão 41 em 302°. O modo `demo` permanece disponível apenas para testes da
interface, enquanto o modo `strict` bloqueia cálculos sem um provedor real.

O `HumanDesignCalculator` calcula os 13 corpos de Personality e os 13 corpos
de Design. A data Design é encontrada astronomicamente no instante anterior
em que o Sol está exatamente 88° atrás do Sol natal, com localização de
intervalo e bisseção; ela não é obtida subtraindo uma quantidade fixa de dias.
A referência validada é `1981-12-30 08:47:00 UTC` para o nascimento acima.

As 26 ativações alimentam a união ordenada `active_gates`, e canais completos
e centros definidos são recalculados a partir dessa união dos dois lados.

A classificação usa o grafo dos canais completos entre centros. Tipo verifica
conectividade funcional (por qualquer caminho de canais definidos) entre motores
e Garganta; definição é o número de componentes conectados desse mesmo grafo.
Autoridade segue a hierarquia de centros definidos: Plexo Solar, Sacral, Baço,
Ego, G, Mental e Lunar. Um mapa sem centros recebe `No Definition`, pois Single,
Split, Triple Split e Quadruple Split pressupõem ao menos um centro definido.

## Estrutura

```text
app/
  Controllers/
  Core/
  Domain/
  Services/
config/
public/
resources/views/
routes/
storage/logs/
tests/
```

## Próximos passos

1. Implementar tipo, autoridade e definição.
2. Implementar perfil e cruz de encarnação.
3. Implementar o visual completo do BodyGraph.
