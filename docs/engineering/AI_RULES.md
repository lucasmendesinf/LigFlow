# LigFlow AI Rules

Estas regras valem para Codex e qualquer IA que altere o repositório.

## Antes de implementar

1. Leia `docs/engineering/ARCHITECTURE.md`.
2. Leia `docs/engineering/TELEPHONY.md` quando a tarefa envolver telefonia.
3. Confirme a branch atual.
4. Não trabalhe diretamente na `main`.
5. Liste arquivos afetados e riscos antes de modificar.

## Regras obrigatórias

- Não remover funcionalidades existentes.
- Não substituir fluxos homologados.
- Não refatorar áreas fora do pedido.
- Não duplicar serviços ou regras.
- Reutilizar código existente.
- Preservar compatibilidade de APIs.
- Fazer mudanças pequenas e commits lógicos.
- Criar ou atualizar testes diretamente relacionados.

## Módulos protegidos

Sem autorização explícita, não alterar estruturalmente:

- ASTERISK;
- NVOIP_DIRECT;
- SIP/WebRTC;
- JsSIP;
- ARI;
- worker;
- WSS;
- Nginx;
- `sip_config`;
- cobrança;
- campanhas;
- autenticação.

## Regra de parada

Caso a tarefa exija alterar comportamento homologado, interrompa antes da mudança e informe:

- motivo;
- arquivos afetados;
- risco;
- alternativa mínima;
- rollback.

## Entrega padrão

Informar somente:

- branch e commits;
- arquivos alterados;
- migrations e rollback;
- testes executados;
- pendências;
- confirmação de que os módulos protegidos não sofreram regressão.
