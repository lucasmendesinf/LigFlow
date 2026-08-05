# LigFlow Architecture

Versão: 1.0  
Status: fonte oficial da arquitetura

## Objetivo

O LigFlow é uma plataforma omnichannel com CRM, campanhas, cobrança e telefonia Asterisk nativa.

Toda implementação deve reutilizar os módulos existentes, preservar compatibilidade e evitar fluxos paralelos.

## Arquitetura geral

```text
LigFlow
├── Core
├── Usuários e empresas
├── CRM
├── Campanhas e discador
├── Cobrança
├── WhatsApp
├── Telefonia
└── IA
```

## Arquitetura oficial de telefonia

```text
Browser
→ JsSIP
→ ?page=sip_config
→ wss://telefonia.calutec.com.br/ws
→ Nginx TLS
→ Asterisk 127.0.0.1:8088/ws
→ ARI
→ Worker
→ LigFlow
```

## Ambientes

### Homologação

O ambiente de homologação é o LigFlow executado localmente no Windows/XAMPP.

Ele usa a mesma VPS Asterisk por WSS público, sem túnel SSH.

### Produção

O ambiente de produção é o LigFlow após deploy.

Homologação e produção usam o mesmo código, mas possuem banco e configuração próprios.

Configurações locais nunca devem ser copiadas automaticamente para produção.

## Fluxo ASTERISK

- O navegador mantém somente o ramal WebRTC do consultor.
- As chamadas PSTN são originadas pelo backend/Asterisk.
- O ARI controla canais e bridges.
- O worker processa e normaliza os eventos.
- O contexto homologado é `from-ligflow-webrtc`.
- O endpoint WebRTC homologado atual é `1001`.
- O endpoint PJSIP homologado do tronco é `directcall`.

## Fluxo NVOIP_DIRECT

O fluxo NVOIP_DIRECT permanece independente, sequencial e compatível com o comportamento já homologado.

Não misturar regras, estados ou originação do ASTERISK com NVOIP_DIRECT.

## Módulos homologados

Os módulos abaixo exigem autorização explícita para mudanças estruturais:

- autenticação e usuários;
- empresas e tenancy;
- campanhas;
- cobrança;
- NVOIP_DIRECT;
- ASTERISK;
- SIP/WebRTC;
- JsSIP;
- ARI e worker;
- `sip_config`;
- painel Asterisk;
- WSS público e proxy Nginx.

## Princípios

1. Preservar comportamento homologado.
2. Reutilizar serviços existentes.
3. Estender antes de substituir.
4. Evitar duplicação de fluxo.
5. Alterações pequenas e testáveis.
6. Uma responsabilidade por componente.
7. Segurança e isolamento por tenant.
8. Nunca expor segredos em logs, commits ou documentação.
