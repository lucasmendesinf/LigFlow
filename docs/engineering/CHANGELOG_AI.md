# LigFlow Architecture Changelog

## 14/08/2026

- Adicionado estado visual agregado no Discador enquanto um lote Asterisk aguarda a primeira chamada atendida.
- Exibidos contadores de chamadas solicitadas, originadas, ativas, chamando, atendidas e finalizadas sem revelar os contatos concorrentes.
- Mantido o fluxo serial inalterado e limitada a interface normal de atendimento à chamada `WINNER`.

## 05/08/2026

- Homologado WebRTC próprio com Asterisk.
- Publicado WSS em `wss://telefonia.calutec.com.br/ws`.
- Removida a dependência operacional de túnel SSH.
- Homologado endpoint WebRTC `1001`.
- Homologado contexto `from-ligflow-webrtc`.
- Homologado tronco PJSIP `directcall`.
- Consolidada configuração WebRTC pelo painel.
- Definida separação oficial entre ASTERISK e NVOIP_DIRECT.
- Criado o LigFlow Engineering Handbook.
