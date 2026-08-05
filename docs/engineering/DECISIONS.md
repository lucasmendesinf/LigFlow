# LigFlow Architecture Decisions

## ADR-001 — WSS público

**Data:** 05/08/2026  
**Status:** aprovado

### Decisão

Eliminar a dependência de túnel SSH para WebRTC.

### Arquitetura

```text
Browser
→ wss://telefonia.calutec.com.br/ws
→ Nginx TLS
→ Asterisk 127.0.0.1:8088/ws
```

### Motivo

Permitir que homologação local e produção usem a mesma infraestrutura de telefonia.

---

## ADR-002 — Homologação local

**Data:** 05/08/2026  
**Status:** aprovado

### Decisão

O LigFlow local no XAMPP é o ambiente oficial de homologação.

### Consequência

Homologação e produção usam o mesmo código, mas bancos e configurações independentes.

---

## ADR-003 — Separação ASTERISK e NVOIP_DIRECT

**Data:** 05/08/2026  
**Status:** aprovado

### Decisão

Os dois modos possuem fluxos independentes.

### Consequência

A discagem simultânea será aplicada somente ao ASTERISK. NVOIP_DIRECT permanece sequencial.

---

## ADR-004 — Configuração WebRTC pelo painel

**Data:** 05/08/2026  
**Status:** aprovado

### Decisão

WSS, domínio, endpoint, contexto, tronco, timeout e senha WebRTC devem ser administrados pelo painel.

### Segurança

A senha WebRTC é distinta da senha ARI e deve permanecer criptografada.
