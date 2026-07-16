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

Ainda falta integrar todos os corpos e o lado Design ao
`HumanDesignCalculator`. O mapa visual completo também ainda não está
implementado.

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

1. Integrar todos os corpos celestes ao cálculo principal.
2. Integrar o lado Design ao cálculo principal.
3. Corrigir e validar centros por canais completos.
4. Implementar o mapa visual completo.
