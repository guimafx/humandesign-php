# Decisões matemáticas

Este texto registra somente operações presentes no código e cobertas direta ou indiretamente pelos testes.

## Normalização angular

Longitudes são reduzidas ao intervalo `[0, 360)`: calcula-se o resto por 360 e, se negativo, soma-se 360. Assim, 360° equivale a 0° e valores negativos ou acima de uma volta continuam válidos.

Terra e Nodo Sul são pontos opostos:

```text
Terra = normalizar(Sol + 180°)
Nodo Sul = normalizar(Nodo Norte verdadeiro + 180°)
```

O seletor `t` do `swetest` é usado para o Nodo Norte verdadeiro.

## Data Design

O alvo solar é:

```text
alvo = normalizar(longitude do Sol natal - 88°)
```

O cálculo começa com uma estimativa anterior ao nascimento, procura em passos de dois dias um intervalo cujo erro troca de sinal e usa bisseção nesse intervalo. A diferença angular assinada é reduzida para `(-180°, 180°]`, evitando que a passagem por 0° seja confundida com uma volta inteira.

A busca para quando o intervalo temporal chega a 1 segundo ou quando o erro angular é no máximo `0,000001°`. Existem limites de 100 iterações para localização e para bisseção; ausência de intervalo ou convergência gera erro.

## Mandala e subdivisões

A sequência de 64 gates começa no limite inicial do portão 41, em 302°, e avança com a longitude. Primeiro é calculado `normalizar(longitude - 302°)`.

```text
tamanho do gate  = 360° / 64
tamanho da line  = tamanho do gate / 6
tamanho da color = tamanho da line / 6
tamanho do tone  = tamanho da color / 6
tamanho da base  = tamanho do tone / 5
```

Cada índice usa `floor` e recebe 1 para virar a numeração pública, exceto o índice do gate, usado para consultar a ordem Rave. Gates, lines, colors e tones têm seis subdivisões; bases têm cinco.

## Fronteiras

Os intervalos são fechados no início e abertos no final: uma longitude exatamente sobre uma nova fronteira pertence à subdivisão que começa ali. Os testes verificam valores imediatamente antes, exatamente em e imediatamente depois de fronteiras de gate e line.

Em torno de 0°/360°, a normalização é aplicada antes do mapeamento. Portanto 0° e 360° produzem a mesma posição, e longitudes como -1° ou 662° são trazidas à volta canônica antes da classificação.
