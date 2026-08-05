# LigFlow Telephony

## Arquitetura oficial

```text
Browser
→ JsSIP
→ WSS público
→ Nginx
→ Asterisk
→ ARI
→ Worker
→ LigFlow
```

## Endereços homologados

```text
WSS: wss://telefonia.calutec.com.br/ws
Domínio SIP: telefonia.calutec.com.br
Contexto WebRTC: from-ligflow-webrtc
Endpoint WebRTC atual: 1001
Tronco PJSIP atual: directcall
```

Os valores operacionais devem vir do painel. Não fixar valores no código.

## Responsabilidades

### Browser e JsSIP

- manter o ramal WebRTC do consultor;
- registrar no Asterisk;
- receber estados de chamada;
- não originar múltiplas chamadas PSTN simultâneas.

### Asterisk

- originar chamadas PSTN;
- controlar canais;
- executar dialplan;
- criar e controlar bridges;
- encerrar canais perdedores.

### ARI e worker

- correlacionar eventos;
- normalizar estados;
- atualizar chamadas e tentativas;
- garantir idempotência;
- controlar continuação dos lotes Asterisk.

## ASTERISK x NVOIP_DIRECT

### ASTERISK

- backend origina;
- worker controla eventos;
- lotes e concorrência pertencem ao backend;
- navegador mantém apenas o ramal do consultor.

### NVOIP_DIRECT

- mantém o fluxo atual;
- permanece sequencial;
- usa a sessão JsSIP e o comportamento já homologado;
- não deve ser afetado por lotes Asterisk.

## Discagem simultânea

A implementação deve respeitar:

- simultaneidade de 1 a 10;
- padrão 1;
- um lote ativo por consultor;
- reserva transacional;
- uma vencedora atômica;
- bridge apenas da vencedora;
- hangup real das perdedoras;
- `LATE_ANSWERED` sem segunda modal;
- cobrança idempotente;
- isolamento por tenant e consultor.

## Segurança

- nunca usar senha ARI como senha WebRTC;
- segredos devem ser criptografados;
- ARI permanece privado;
- Asterisk HTTP permanece em localhost;
- apenas `/ws` é publicado pelo Nginx;
- não executar SSH arbitrário pelo PHP.
