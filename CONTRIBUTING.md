# Como contribuir

## Preparar o ambiente

Use PHP 8.1 ou superior, copie `.env.example` para `.env` e, para testes reais, instale externamente `swetest` e os `.se1` nos caminhos esperados. Rode `./bin/run-tests` antes de propor alterações.

## Branch e commits

Crie uma branch curta a partir da base atual, por exemplo `fix/fronteira-mandala` ou `docs/instalacao-swiss`. Commits pequenos e no imperativo são recomendados; prefixos como `fix:`, `feat:`, `test:` e `docs:` podem ser usados, mas não são obrigatórios.

## Testes e mapas de referência

Toda mudança de cálculo precisa de teste adequado. Para adicionar um mapa:

1. obtenha consentimento explícito ou use dados publicados para essa finalidade;
2. registre data, hora, timezone, conversão UTC, origem da referência e precisão disponível;
3. anonimize nome e outros dados quando a identidade não for necessária;
4. adicione expectativas de Personality e Design sem copiar dados irrelevantes;
5. execute a suíte completa em ambiente Swiss conhecido.

Não modifique resultados esperados apenas para fazer os testes passarem. Investigue a divergência e documente a fonte e a decisão matemática. Fontes externas também precisam de avaliação de licença; evite incorporar tabelas ou textos sem permissão.

Crie a fixture em `tests/reference/` como PHP que retorna um array. Para um caso ativo, informe `status => active`, `id`, `label`, `birth` (`date`, `time`, `timezone`, `latitude`, `longitude`), `expected`, `source` (`provider`, `reference`, `checked_at`) e `privacy` (`consent`, `anonymized`). O campo `expected` pode conter somente os resultados realmente conferidos entre `type`, `authority`, `definition`, `profile`, `active_channels`, `defined_centers`, `personality` e `design`; omitir um campo é preferível a inventá-lo.

Se ainda faltarem nascimento, resultado independente, fonte ou consentimento, mantenha um template com `status => pending`, campos desconhecidos vazios e `pending_reason`. Casos pending documentam trabalho futuro e não são executados. Depois de obter e conferir a referência, preencha todos os campos obrigatórios, altere o status para `active` e rode `./bin/run-tests`. Não use a saída atual do motor como fonte das próprias expectativas.

## Issues e pull requests

Antes de abrir uma issue, procure relatos existentes e forneça reprodução mínima, versões e mensagens de erro. Não publique credenciais nem dados de nascimento privados. Para mudanças, descreva problema, decisão, impacto, validação e limitações. Abra um pull request de escopo único e vincule a issue quando existir.

## Checklist do PR

- [ ] Alteração tem escopo único
- [ ] Testes existentes passam
- [ ] Novos testes foram adicionados
- [ ] Não há dados sensíveis
- [ ] Documentação foi atualizada
- [ ] Nenhum resultado foi forçado
- [ ] Compatibilidade de licença foi considerada

## Segurança e privacidade

Dados de nascimento podem identificar pessoas. Não inclua mapas privados sem consentimento e não colete ou armazene esses dados além do necessário. Vulnerabilidades devem ser comunicadas conforme [SECURITY.md](SECURITY.md), evitando exposição pública prematura.
