# Política de segurança

## Relato de vulnerabilidades

Use um GitHub Security Advisory privado neste repositório, caso o recurso esteja disponível. Se não estiver, abra uma issue apenas para solicitar um canal privado, sem revelar detalhes exploráveis. Não há e-mail de segurança oficial definido.

Nunca publique credenciais, tokens, conteúdo de `.env`, dados pessoais ou uma prova de conceito perigosa em issues públicas. Inclua versões afetadas, impacto e passos mínimos de reprodução no canal privado.

## Considerações operacionais

O driver Swiss inicia um processo com `proc_open`. Administradores devem restringir quem altera a configuração, verificar origem e integridade do executável e conceder somente as permissões necessárias. `SWETEST_BIN` e `SWISSEPH_EPHE_PATH` devem ser caminhos controlados pelo operador; nunca aceite esses caminhos diretamente de parâmetros enviados por usuários HTTP.

Dependências externas, inclusive binário e arquivos de efemérides, precisam de atualização e verificação independentes. Não armazene dados de nascimento sem necessidade, consentimento, retenção definida e proteção compatível com seu contexto.
