# Exemplos de linha de comando

Os exemplos carregam o autoloader atual e não dependem do servidor HTTP.

## Cálculo com Swiss Ephemeris

`basic.php` usa os caminhos padrão do binário e das efemérides:

```bash
php examples/basic.php
```

Ele falhará de forma explícita se `swetest` ou o diretório externo não estiverem disponíveis.

## Demonstração sem astronomia

```bash
php examples/demo.php
```

`demo.php` escreve um aviso em stderr e inclui metadados de resultado não confiável. Use-o somente para inspecionar o formato JSON e o fluxo técnico.
