from pathlib import Path
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import cm
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, KeepTogether
from reportlab.pdfbase.pdfmetrics import stringWidth


OUTPUT = Path(r"C:\xampp\htdocs\LigFlow\output\pdf\analise-arquitetura-provisionamento-ramais-asterisk.pdf")


def bullet(text):
    return Paragraph(f"- {text}", styles['body'])


styles = getSampleStyleSheet()
styles.add(ParagraphStyle(
    name='TitleSimple', parent=styles['Title'], fontName='Helvetica-Bold',
    fontSize=16, leading=20, spaceAfter=12,
))
styles.add(ParagraphStyle(
    name='HeadingSimple', parent=styles['Heading2'], fontName='Helvetica-Bold',
    fontSize=12, leading=15, spaceBefore=10, spaceAfter=5,
))
styles.add(ParagraphStyle(
    name='body', parent=styles['BodyText'], fontName='Helvetica',
    fontSize=10.5, leading=14, spaceAfter=4,
))


sections = [
    ('1. Recomendacao principal', [
        'Usar um agente de provisionamento restrito na VPS, acessado por API autenticada pelo LigFlow. E a melhor opcao para o estado atual: mantem o Asterisk isolado, evita SSH no PHP e preserva NVOIP_DIRECT, ARI e a discagem existente.',
    ]),
    ('2. Arquitetura proposta', [
        'LigFlow cria uma reserva de ramal e um job idempotente no banco. Um agente local da VPS recebe apenas operacoes permitidas (create, update, disable), valida os dados, gera arquivos dedicados do LigFlow em pjsip.d, faz backup, executa somente reload PJSIP permitido e confirma o endpoint.',
        'ARI continua apenas para operacao, eventos e diagnostico. AMI nao e necessario para provisionar ramais.',
    ]),
    ('3. Fluxo de criacao', [
        'Admin cria usuario e escolhe ramal.',
        'LigFlow valida e reserva o ramal em transacao (BEGIN IMMEDIATE).',
        'Gera senha SIP forte no backend e armazena criptografada.',
        'Cria job de provisionamento com chave de idempotencia.',
        'Agente cria endpoint, auth e aor PJSIP no include dedicado.',
        'Agente valida, recarrega PJSIP e confirma o endpoint.',
        'LigFlow marca o vinculo como ativo/provisionado.',
        'O sip_config do usuario passa a usar seu proprio ramal e senha.',
    ]),
    ('4. Falha e rollback', [
        'Se o agente falhar, o usuario permanece criado, mas o vinculo fica FAILED ou pendente e nao e usado pelo WebRTC nem pela resolucao ARI. O job pode ser repetido sem duplicar endpoint.',
        'Se o Asterisk aplicar e a confirmacao no LigFlow falhar, um reconciliador consulta o resultado pelo idempotency_key antes de qualquer compensacao. Nunca apagar automaticamente um ramal sem confirmar o estado remoto.',
    ]),
    ('5. Banco', [
        'Reutilizar a tabela asterisk_user_extensions e ampliar, em vez de criar outra tabela de vinculo.',
        'Campos recomendados: sip_password_encrypted; lifecycle_status (RESERVED, ACTIVE, FAILED, RELEASING, RELEASED); provisioned_at; last_provision_error; provisioning_version ou config_fingerprint; released_at.',
        'Criar uma tabela pequena de asterisk_provisioning_jobs para outbox, tentativas, idempotencia e auditoria do resultado remoto.',
        'O atual asterisk_server_id = 1 pode continuar nesta fase, mas deve deixar de ser espalhado ou hardcoded. Hoje ha uma unica configuracao global em asterisk_settings; quando houver mais servidores, ela deve evoluir para uma entidade de servidores.',
    ]),
    ('6. Impacto no codigo atual', [
        'O ponto de vinculo ja existe em sync_user_asterisk_extension(), e a resolucao por eventos ARI ja ocorre no processamento de eventos Asterisk.',
        'A mudanca futura deve restringir a resolucao automatica a ramais ACTIVE e PROVISIONED. O endpoint WebRTC atual e a senha global em asterisk_settings deixam de ser a fonte para usuarios provisionados, sem alterar o fluxo NVOIP_DIRECT.',
    ]),
    ('7. Riscos', [
        'Arquivo PJSIP manual e arquivo gerenciado nao podem definir o mesmo ramal.',
        'Senha SIP precisa aparecer somente ao navegador autenticado do proprio usuario, nunca em listagens, logs ou auditoria.',
        'SQLite exige reserva transacional para evitar dois usuarios obterem o mesmo ramal.',
        'Realtime Asterisk e mais escalavel, mas hoje acrescentaria ODBC, banco compartilhado e risco operacional desnecessario.',
        'Alterar pjsip.conf diretamente pelo LigFlow ou por SSH e o caminho menos seguro e nao e recomendado.',
    ]),
    ('8. Etapas pequenas', [
        'Definir ciclo de vida do vinculo e criar outbox de provisionamento.',
        'Criar agente da VPS com API restrita, HMAC ou mTLS, allowlist de IP e operacoes idempotentes.',
        'Homologar criacao, atualizacao e desativacao de um ramal por include dedicado.',
        'Integrar o job do LigFlow ao agente e exibir estado e retry administrativo.',
        'Ajustar sip_config para credencial individual provisionada.',
        'Adicionar reconciliacao, auditoria e liberacao segura do ramal.',
        'So entao avaliar Asterisk Realtime para escala maior.',
    ]),
    ('Confirmacao', [
        'Nenhuma alteracao foi feita durante a analise.',
    ]),
]


def add_page_number(canvas, doc):
    canvas.saveState()
    canvas.setFont('Helvetica', 8)
    canvas.drawRightString(A4[0] - 1.8 * cm, 1.2 * cm, f'Pagina {doc.page}')
    canvas.restoreState()


def main():
    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    document = SimpleDocTemplate(
        str(OUTPUT), pagesize=A4,
        rightMargin=1.8 * cm, leftMargin=1.8 * cm,
        topMargin=1.7 * cm, bottomMargin=1.8 * cm,
        title='Analise - Arquitetura de Provisionamento de Ramais Asterisk',
        author='LigFlow',
    )
    story = [
        Paragraph('Analise - Arquitetura de Provisionamento de Ramais Asterisk', styles['TitleSimple']),
        Spacer(1, 4),
    ]
    for heading, items in sections:
        content = [Paragraph(heading, styles['HeadingSimple'])]
        content.extend(bullet(item) for item in items)
        story.append(KeepTogether(content))
    document.build(story, onFirstPage=add_page_number, onLaterPages=add_page_number)


if __name__ == '__main__':
    main()
