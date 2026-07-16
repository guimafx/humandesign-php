# Validação

## Mapa de referência principal

- Nome: Guilherme Borges Viana
- Nascimento local: 27/03/1982 11:05
- Timezone: `America/Sao_Paulo`
- UTC: 27/03/1982 14:05
- Design calculado: aproximadamente 30/12/1981 08:47 UTC

Esses são os únicos dados pessoais reproduzidos aqui e já são usados publicamente como referência no repositório.

## Ativações esperadas

| Corpo | Personality | Design |
| --- | ---: | ---: |
| Sol | 17.3 | 58.6 |
| Terra | 18.3 | 52.6 |
| Lua | 27.4 | 49.4 |
| Nodo Norte verdadeiro | 53.5 | 62.2 |
| Nodo Sul | 54.5 | 61.2 |
| Mercúrio | 22.6 | 54.6 |
| Vênus | 49.2 | 19.2 |
| Marte | 48.3 | 18.3 |
| Júpiter | 44.2 | 28.5 |
| Saturno | 57.6 | 32.1 |
| Urano | 34.5 | 34.3 |
| Netuno | 11.5 | 11.3 |
| Plutão | 32.6 | 50.1 |

## O que foi validado

O início da Mandala em 302° foi comparado às 26 ativações de Personality e Design. A data Design não é uma subtração fixa: o teste encontra pelo arco solar de 88° um instante dentro de 120 segundos da referência visual de 08:47 UTC e também verifica o arco resultante com tolerância de `0,00002°`.

O resultado calculado pode ficar alguns segundos distante do horário visual arredondado; o teste aceita até dois minutos porque a referência disponível está expressa em minutos. Para gates e lines, os scripts comparam os valores esperados de todos os corpos.

Há testes de fronteira imediatamente antes, exatamente em e depois dos limites de gate e line, além de casos em 0°, próximo de 360°, em 360°, negativos e acima de 360°.

## Limitações

Um único mapa completo é insuficiente para afirmar cobertura geral. Os testes unitários exercitam classificadores com casos sintéticos, mas não substituem mapas completos independentes para diferentes datas, fusos, tipos, autoridades e definições. Novas referências devem ter origem documentada, consentimento para dados pessoais e resultados que não sejam alterados apenas para acomodar a implementação.
