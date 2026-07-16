# Swiss Ephemeris

## Dependência externa

O cálculo real depende do executável `swetest` e do diretório de arquivos `.se1`. Eles não fazem parte deste repositório, não são baixados automaticamente e precisam ser instalados e licenciados separadamente.

Padrões usados pelo código e pelos testes:

```text
/usr/local/bin/swetest
/usr/local/share/swisseph/ephe
```

## Procedimento usado no ambiente de desenvolvimento

O ambiente de desenvolvimento foi preparado com o seguinte procedimento técnico:

```bash
git clone --depth 1 https://github.com/aloistr/swisseph.git /opt/swisseph
cd /opt/swisseph
make swetest
sudo install -m 0755 swetest /usr/local/bin/swetest
sudo mkdir -p /usr/local/share/swisseph/ephe
sudo cp -a ephe/. /usr/local/share/swisseph/ephe/
```

Uma tag ou commit deve ser fixado antes de uma release estável. O workflow de CI já fixa uma versão para tornar suas execuções repetíveis, mas a compatibilidade de licença continua pendente de revisão. O repositório não distribui o binário `swetest` nem os arquivos `.se1`; eles são obtidos da dependência externa durante a preparação do ambiente.

## Variáveis de ambiente

```env
EPHEMERIS_DRIVER=swiss
SWETEST_BIN=/usr/local/bin/swetest
SWISSEPH_EPHE_PATH=/usr/local/share/swisseph/ephe
```

- `swiss`: executa `swetest` e marca o resultado como confiável quanto ao provedor astronômico.
- `demo`: gera valores determinísticos não astronômicos e marca o resultado como não confiável.
- `strict`: bloqueia o cálculo; é o fallback para um driver ausente ou desconhecido.

## Teste manual

O provedor chama o programa com data UTC, seletor do corpo, `-eswe` e saída CSV. Um teste equivalente para o Sol é:

```bash
/usr/local/bin/swetest -edir/usr/local/share/swisseph/ephe -b27.3.1982 -utc14:05:00 -p0 -eswe -fPl -g, -head
```

Confirme também os caminhos:

```bash
command -v swetest
test -x /usr/local/bin/swetest
test -r /usr/local/share/swisseph/ephe
```

Em servidor Apache, teste sob o mesmo usuário do processo web:

```bash
sudo -u www-data /usr/local/bin/swetest -edir/usr/local/share/swisseph/ephe -b27.3.1982 -utc14:05:00 -p0 -eswe -fPl -g, -head
```

## Troubleshooting

- **Binário ausente:** confira `SWETEST_BIN`, `command -v swetest` e a instalação externa.
- **Falta de permissão:** o binário deve ser executável e o diretório de efemérides legível pelo usuário do PHP.
- **Diretório `.se1` ausente ou vazio:** confirme `SWISSEPH_EPHE_PATH` e os arquivos exigidos para as datas testadas.
- **`proc_open` desabilitado:** remova-o de `disable_functions` apenas após avaliação de segurança; sem a função, o driver não opera.
- **Apache usa outro ambiente:** variáveis do shell interativo não são necessariamente herdadas. Configure-as no serviço/virtual host ou no `.env` e teste como o usuário do servidor.
- **Saída inválida:** execute o comando manualmente, confirme a versão do `swetest` e verifique se o formato continua compatível com `-fPl -g, -head`.

## Licenciamento

A compatibilidade entre a futura licença do código próprio, a integração e qualquer forma de distribuição do Swiss Ephemeris ainda precisa de revisão. Este texto não oferece conclusão jurídica. A decisão deve estar resolvida antes de uma release pública estável, sem incorporar automaticamente `swetest` ou arquivos `.se1` ao repositório.
