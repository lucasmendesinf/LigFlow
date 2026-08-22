# Lig Flow - Discador Inteligente

MVP local em PHP + SQLite para consultores que importam leads, ligam pela Nvoip, gravam chamadas e acompanham custo/minutos.

## Como acessar

1. Inicie o Apache pelo XAMPP.
2. Abra `http://localhost/voipCalutec/`.
3. Entre com um dos usuarios de demonstracao:

| Perfil | E-mail | Senha |
| --- | --- | --- |
| Administrador da plataforma | `admin@consorciocall.local` | `admin123` |
| Cliente admin | `gestor@consorciocall.local` | `admin123` |
| Supervisor | `supervisor@consorciocall.local` | `admin123` |
| Consultor | `consultor@consorciocall.local` | `admin123` |

O banco SQLite e criado automaticamente em `data/callflow.sqlite`.

## Recursos implementados

- Multicliente com `company_id` nas tabelas operacionais.
- Login e permissoes por perfil: administrador da plataforma, cliente admin e usuario operacional.
- Cadastro simplificado de cliente, criando automaticamente conta, acesso principal, consultor principal, equipe padrao, assinatura e discador principal.
- Criacao de listas e importacao CSV com normalizacao E.164, duplicidade, invalidos e bloqueio.
- Criacao interna de listas de discagem para preservar relatórios sem expor complexidade de call center.
- Tela do consultor com reserva de lead, botao ligar, cronometro, resultado, observacoes e retorno.
- Integracao Nvoip configuravel por `.env`, com modo demonstracao enquanto as credenciais nao estao preenchidas.
- Lista de bloqueio com validacao antes da discagem.
- Painel de operacao com consultores, chamadas e indicadores.
- Dashboard com chamadas, minutos, gasto e status da Nvoip.
- Relatorios por hora, dia, mes e ano.
- Tela de gravacoes.
- Tela de custos por consultor e por carteira.
- Auditoria de acoes criticas.
- Webhook generico em `?page=nvoip_webhook` para receber status, duracao, custo e gravacao.

## Permissoes e fluxo de cliente

O fluxo principal para novos clientes fica no menu **Clientes** e deve ser usado pelo administrador da plataforma:

1. Criar o cliente como `Consultor individual` ou `Escritorio com equipe`.
2. Informar o acesso principal.
3. Definir plano e limites.
4. Selecionar telefonia gerenciada, quando aplicavel.
5. O sistema cria automaticamente equipe padrao, perfil de consultor, assinatura, lista principal e discador principal.

Perfis atuais:

- `admin_plataforma` ou legado `admin_geral`: acessa todos os clientes, integrações, consumo, auditoria e configurações administrativas.
- `cliente_admin` ou legado `admin_empresa`: acessa apenas a própria conta, listas, contatos, discador, chamadas, gravações, relatórios, consumo e acessos.
- `usuario_operacional`: acesso operacional para discador, listas permitidas, chamadas e gravações/relatórios.

Clientes não acessam o menu **Integrações** e não visualizam credenciais, URL de API, tokens, Napikey, SIP ou webhook.

## Configuracao de telefonia

Somente o administrador da plataforma acessa **Integracoes**. A telefonia é gerenciada pela plataforma; o cliente enxerga apenas status, consumo de minutos e dados comerciais autorizados.

Pelo painel administrativo, acesse **Integracoes** e selecione uma integracao existente ou clique em **Adicionar nova**. Para Nvoip, use o identificador interno `nvoip`.

Os campos sao opcionais porque outros provedores podem exigir dados diferentes. Preencha apenas o que fizer sentido para a integracao:

- modo: demonstracao ou API real;
- URL da API de chamada;
- metodo de autenticacao: Napikey na URL ou OAuth;
- Napikey;
- NumberSIP, quando usar OAuth;
- Usuario/Ramal SIP que deve originar a chamada;
- User Token;
- Payload JSON opcional, caso a colecao Postman da sua conta use nomes de campos especificos;
- numero de origem;
- custo por minuto;
- gravacao ativa/inativa;
- URL e segredo do webhook.
- campos adicionais JSON para dados especificos de outros provedores.

Tambem e possivel copiar `.env.example` para `.env` e usar variaveis de ambiente como fallback.

Configure na Nvoip o webhook para:

```text
http://seu-dominio/voipCalutec/?page=nvoip_webhook&secret=SUA_CHAVE
```

O formato exato do payload da Nvoip pode variar conforme o endpoint contratado. O webhook aceita campos comuns como `id`, `call_id`, `uuid`, `status`, `duration_seconds`, `billable_seconds`, `cost`, `value` e `recording_url`.

Para autenticar, o sistema suporta dois caminhos:

- `Napikey`: envia a chave no parametro `napikey` da URL.
- `OAuth`: usa `NumberSIP + User Token` para obter um `access_token` em `https://api.nvoip.com.br/v2/oauth/token`.

Se a API retornar `User sip not found`, confira se o usuario/ramal SIP informado existe na conta Nvoip e esta habilitado para ligacoes via API.

## Retencao das gravacoes Asterisk

As gravacoes novas feitas pelo bridge do Asterisk sao persistidas em `call_recordings` e removidas somente pela Stored Recordings API do ARI. O worker nunca apaga arquivos diretamente em `/var/spool/asterisk` e nao interfere nas URLs historicas de gravacao da Nvoip.

Politica padrao:

- gravacao com menos de 5 segundos: estado `DISCARD_PENDING`, com carencia de 24 horas;
- gravacao com 5 segundos ou mais: estado `READY`, com retencao de 90 dias;
- depois do prazo e de uma exclusao ARI confirmada: estado `DISCARDED` e preenchimento de `discarded_at`;
- falha temporaria: o arquivo permanece recuperavel e `last_cleanup_error` permite nova tentativa;
- resposta 404 da Stored Recordings API: o registro e reconciliado como `DISCARDED`, sem apagar o historico da chamada.

Os valores ficam centralizados nas variaveis:

```text
ASTERISK_RECORDING_SHORT_THRESHOLD_SECONDS=5
ASTERISK_RECORDING_DISCARD_GRACE_HOURS=24
ASTERISK_RECORDING_RETENTION_DAYS=90
ASTERISK_RECORDING_DISK_THRESHOLD_PERCENT=80
ASTERISK_RECORDING_RETENTION_BATCH_SIZE=25
ASTERISK_RECORDING_STORAGE_USAGE_PERCENT=
```

O uso do armazenamento nao e consultado por caminho local, porque o PHP pode estar em outro servidor. Uma monitoracao confiavel da VPS pode preencher `ASTERISK_RECORDING_STORAGE_USAGE_PERCENT`; a partir de 80%, o worker aumenta apenas o lote de gravacoes que ja estao elegiveis. Gravacoes recentes nunca sao removidas arbitrariamente.

Execute o worker em pequenos lotes por cron, por exemplo a cada hora:

```cron
17 * * * * cd /caminho/do/LigFlow && /usr/bin/php asterisk_recording_retention_worker.php 25 >> data/asterisk_recording_retention.log 2>&1
```

O worker usa lock exclusivo, e idempotente e mantem intactos os estados `RECORDING`, `FAILED` e `DISCARDED`. Durante a carencia, `DISCARD_PENDING` continua disponivel para reproducao e download autorizados.

## CSV de exemplo

```csv
nome,telefone,email,cidade,estado,produto,valor_carta,origem
Joao da Silva,41999999999,joao@email.com,Curitiba,PR,Carta imovel,500000,Landing Page
Maria Oliveira,11988888888,maria@email.com,Sao Paulo,SP,Carta auto,120000,Indicacao
Carlos Pereira,48977777777,carlos@email.com,Florianopolis,SC,Carta investimento,300000,Instagram
```

## Proximos passos sugeridos

- Confirmar com a Nvoip o endpoint final de click-to-call/originacao e mapear o payload oficial.
- Adicionar CSRF forte e politica de senha para producao.
- Criar tela dedicada de planos e faturamento.
- Evoluir permissoes configuraveis por usuario operacional.
- Migrar SQLite para MySQL/PostgreSQL em ambiente multiusuario.
- Adicionar WebSocket/SSE para atualizacao em tempo real.
- Criar exports CSV/XLSX/PDF.
