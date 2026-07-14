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

1. Implementar `SwissEphemerisProvider`.
2. Validar o offset da Mandala Rave.
3. Adicionar todos os corpos celestes.
4. Corrigir e validar centros por canais completos.
5. Criar testes contra mapas conhecidos.
