# Decisões técnicas

Formato ADR simplificado. “Aceita” descreve a implementação atual, não uma aprovação jurídica ou compromisso imutável.

## ADR-001 — Swiss Ephemeris como provedor externo

**Status:** Aceita no núcleo atual.

**Contexto:** O cálculo precisa de longitudes astronômicas reais.

**Decisão:** Executar o `swetest` instalado fora do repositório e ler seus arquivos de efemérides externos.

**Consequências:** O ambiente precisa manter binário, dados, permissões e compatibilidade de saída; o core não é autossuficiente.

**Pendências:** Procedimento de instalação reproduzível, fixação de versão e revisão de licença.

## ADR-002 — Provedor abstrato por interface

**Status:** Aceita.

**Contexto:** Astronomia real, demonstração e ausência de configuração têm comportamentos distintos.

**Decisão:** Dependência em `EphemerisProviderInterface`, com implementações Swiss, demo e strict.

**Consequências:** O cálculo pode receber provedores alternativos e testar fluxos sem acoplar o orquestrador ao processo externo.

**Pendências:** Estabilizar essa interface como parte da futura API pública.

## ADR-003 — Modo demo marcado como não confiável

**Status:** Aceita.

**Contexto:** A interface precisa funcionar sem fingir posições astronômicas.

**Decisão:** Gerar valores determinísticos e incluir `reliable: false` e aviso no resultado.

**Consequências:** O modo serve a demonstrações técnicas, nunca a mapas reais.

**Pendências:** Tornar o aviso igualmente inequívoco em futuras interfaces.

## ADR-004 — Data Design por arco solar de 88°

**Status:** Aceita.

**Contexto:** Uma quantidade fixa de dias não preserva o arco astronômico.

**Decisão:** Localizar o instante anterior cujo Sol esteja 88° atrás do Sol natal por intervalo e bisseção.

**Consequências:** Há várias chamadas ao provedor e limites explícitos de tolerância e convergência.

**Pendências:** Ampliar regressões em outras datas e passagens pela fronteira angular.

## ADR-005 — Mandala Rave iniciada em 302°

**Status:** Aceita no núcleo atual.

**Contexto:** A sequência de gates exige um offset verificável.

**Decisão:** Usar 302° como limite inicial do portão 41 e avançar com a longitude zodiacal.

**Consequências:** Gates e subdivisões são determinísticos e têm fronteiras início-inclusivas.

**Pendências:** Validar contra mais mapas independentes.

## ADR-006 — Cálculo determinístico separado de interpretação por IA

**Status:** Aceita como limite arquitetural.

**Contexto:** Resultados de cálculo precisam ser reproduzíveis e auditáveis.

**Decisão:** Manter astronomia, mapeamento e classificação no código determinístico; nenhuma interpretação por IA integra o pipeline atual.

**Consequências:** A saída estruturada pode ser consumida por outras camadas sem alterar o núcleo.

**Pendências:** Documentar contratos antes de qualquer integração interpretativa futura.

## ADR-007 — Cruz de Encarnação permanece unresolved sem tabela validada

**Status:** Aceita.

**Contexto:** Os quatro gates são calculáveis, mas nome, quarter e angle exigem fonte confiável ainda ausente.

**Decisão:** Expor os gates, campos nulos e status `unresolved`.

**Consequências:** A API não inventa uma resolução; consumidores precisam tratar o status.

**Pendências:** Selecionar e validar uma fonte compatível antes de implementar a resolução.

## ADR-008 — Licença do projeto permanece pendente de revisão

**Status:** Pendente.

**Contexto:** O código próprio ainda não tem licença definida e integra uma ferramenta externa com termos próprios.

**Decisão:** Não escolher licença automaticamente nem distribuir `swetest` ou `.se1` nesta sprint.

**Consequências:** Ausência de licença explícita limita permissões de reutilização e impede tratar o projeto como pronto para release pública estável.

**Pendências:** O mantenedor deve revisar compatibilidade, modelo de integração/distribuição e então registrar a licença escolhida. Nenhuma conclusão jurídica definitiva é afirmada.
