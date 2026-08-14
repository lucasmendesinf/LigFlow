class NvoipWebphoneService {
    constructor({ remoteAudio, onState, onLog }) {
        this.remoteAudio = remoteAudio;
        this.onState = onState;
        this.onLog = onLog;
        this.ua = null;
        this.session = null;
        this.status = 'DISCONNECTED';
        this.muted = false;
        this.registeredOnce = false;
    }

    emit(status, patch = {}) {
        this.status = status;
        this.onState?.({ status, ...patch });
    }

    log(message, detail = '') {
        this.onLog?.(message + (detail ? `: ${detail}` : ''));
    }

    async requestMicrophone() {
        if (!navigator.mediaDevices?.getUserMedia) {
            throw new Error('Navegador sem suporte a captura de audio.');
        }
        if (!window.isSecureContext && !['localhost', '127.0.0.1'].includes(location.hostname)) {
            throw new Error('WebRTC exige HTTPS. Em desenvolvimento, use localhost.');
        }
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
        stream.getTracks().forEach((track) => track.stop());
        return true;
    }

    connect(config) {
        if (!window.JsSIP) {
            this.emit('REGISTRATION_FAILED');
            this.log('JsSIP nao carregou');
            return;
        }
        if (this.ua) {
            this.disconnect();
        }
        this.registeredOnce = false;
        const missing = [
            ['WSS URL', config.wssUrl],
            ['Dominio SIP', config.domain],
            ['Usuario SIP', config.sipUsername],
            ['Senha SIP', config.sipPassword],
        ].filter(([, value]) => !value).map(([label]) => label);
        if (missing.length) {
            this.emit('REGISTRATION_FAILED');
            this.log('Configuracao SIP incompleta', missing.join(', '));
            return;
        }

        this.emit('CONNECTING', { ws: 'Conectando' });
        this.log('Conexao iniciada');
        const socket = new JsSIP.WebSocketInterface(config.wssUrl);
        this.ua = new JsSIP.UA({
            sockets: [socket],
            uri: `sip:${config.sipUsername}@${config.domain}`,
            authorization_user: config.sipUsername,
            password: config.sipPassword,
            register: true,
            session_timers: false,
        });

        this.bindUaEvents(config);
        this.ua.start();
    }

    bindUaEvents(config) {
        this.ua.on('connecting', () => {
            this.emit('CONNECTING', { ws: 'Conectando' });
            this.log('WebSocket conectando');
        });
        this.ua.on('connected', () => {
            this.emit('CONNECTED', { ws: 'Conectado', register: 'Registrando' });
            this.log('WSS conectado');
        });
        this.ua.on('disconnected', (event) => {
            this.emit('DISCONNECTED', { ws: 'Desconectado', register: 'Sem registro' });
            const detail = [event?.code, event?.reason].filter(Boolean).join(' - ');
            this.log('WSS desconectado', detail);
            if (!this.registeredOnce && this.ua) {
                const failedUa = this.ua;
                this.ua = null;
                setTimeout(() => failedUa.stop(), 0);
                this.log('Reconexao automatica interrompida porque o primeiro registro nao foi concluido');
            }
        });
        this.ua.on('registered', () => {
            this.registeredOnce = true;
            this.emit('REGISTERED', { ws: 'Conectado', register: 'Registrado' });
            this.log('Registro concluido');
        });
        this.ua.on('unregistered', () => {
            this.emit('DISCONNECTED', { register: 'Sem registro' });
            this.log('Registro encerrado');
        });
        this.ua.on('registrationFailed', (event) => {
            this.emit('REGISTRATION_FAILED', { register: 'Falha no registro' });
            this.log('Falha de registro', this.sipFailureDetail(event));
        });
        this.ua.on('newRTCSession', (event) => {
            if (event.originator !== 'remote') {
                this.log('Sessao local ignorada');
                return;
            }
            if (this.session) {
                event.session.terminate();
                this.log('Chamada recusada por ja existir sessao ativa');
                return;
            }
            this.session = event.session;
            this.session._ligflowDirection = 'incoming';
            this.session._ligflowRingingConfirmed = true;
            this.emit('INCOMING', { call: 'Chamada recebida', direction: 'incoming', ringingConfirmed: true });
            this.log('Chamada recebida');
            this.bindSessionEvents(this.session);
            if (config.autoAnswer) {
                this.log('Autoatendimento habilitado');
                this.answer();
            }
        });
    }

    bindSessionEvents(session) {
        session.on('progress', (event) => {
            const progress = this.sipResponseDetail(event);
            const direction = session._ligflowDirection || 'outgoing';
            if (progress.code >= 300) {
                session._ligflowLastSipFailure = progress;
            }
            if (progress.code === 180) {
                session._ligflowRingingConfirmed = true;
                this.emit('RINGING', {
                    call: 'Chamando',
                    direction,
                    ringingConfirmed: true,
                    sipCode: progress.code,
                    sipReason: progress.reason,
                });
                this.log('Toque confirmado pela operadora', progress.summary);
                return;
            }
            if (progress.code === 100 || progress.code === 0) {
                this.emit('CALLING', {
                    call: 'Preparando chamada',
                    direction,
                    ringingConfirmed: false,
                    sipCode: progress.code,
                    sipReason: progress.reason,
                });
                this.log('Chamada sendo preparada', progress.summary);
                return;
            }
            this.emit('EARLY_MEDIA', {
                call: 'Retorno sem toque confirmado',
                direction,
                ringingConfirmed: false,
                sipCode: progress.code,
                sipReason: progress.reason,
                cause: progress.summary || `SIP ${progress.code}`,
            });
            this.log('Retorno SIP sem toque confirmado', progress.summary);
        });
        session.on('accepted', (event) => {
            const detail = this.sipResponseDetail(event);
            this.emit('IN_CALL', {
                call: 'Aceita',
                direction: session._ligflowDirection || 'outgoing',
                ringingConfirmed: Boolean(session._ligflowRingingConfirmed),
                sipCode: detail.code,
                sipReason: detail.reason,
                cause: detail.summary,
            });
            this.log('Chamada atendida');
        });
        session.on('confirmed', () => {
            this.emit('IN_CALL', {
                call: 'Em chamada',
                audio: 'Conectado',
                direction: session._ligflowDirection || 'outgoing',
                ringingConfirmed: Boolean(session._ligflowRingingConfirmed),
            });
            this.log('Midia conectada');
        });
        session.on('ended', (event) => {
            const eventDetail = this.sipResponseDetail(event);
            const detail = eventDetail.code >= 300
                ? eventDetail
                : (session._ligflowLastSipFailure || eventDetail);
            const cause = detail.summary || this.sipCause(event);
            this.emit('ENDED', {
                call: 'Encerrada',
                cause,
                direction: session._ligflowDirection || 'outgoing',
                ringingConfirmed: Boolean(session._ligflowRingingConfirmed),
                sipCode: detail.code,
                sipReason: detail.reason,
            });
            this.log('Chamada encerrada', cause);
            this.session = null;
        });
        session.on('failed', (event) => {
            const eventDetail = this.sipResponseDetail(event);
            const detail = eventDetail.code >= 300
                ? eventDetail
                : (session._ligflowLastSipFailure || eventDetail);
            const cause = detail.summary || this.sipCause(event);
            this.emit('CALL_FAILED', {
                call: 'Falhou',
                cause,
                direction: session._ligflowDirection || 'outgoing',
                ringingConfirmed: Boolean(session._ligflowRingingConfirmed),
                sipCode: detail.code,
                sipReason: detail.reason,
            });
            this.log('Chamada falhou', cause);
            this.session = null;
        });
        session.on('muted', () => this.log('Microfone silenciado'));
        session.on('unmuted', () => this.log('Microfone reativado'));
        session.on('peerconnection', () => {
            session.connection.addEventListener('track', (event) => {
                const stream = event.streams?.[0];
                if (stream && this.remoteAudio) {
                    this.remoteAudio.srcObject = stream;
                    this.remoteAudio.play().catch(() => this.log('Audio remoto aguardando interacao'));
                    this.emit(this.status, { audio: 'Audio remoto conectado', remoteStream: stream });
                }
            });
        });
    }

    async answer() {
        if (!this.session) {
            this.log('Nenhuma chamada para atender');
            return;
        }
        try {
            this.emit('ANSWERING', { call: 'Atendendo' });
            await this.requestMicrophone();
            this.session.answer({ mediaConstraints: { audio: true, video: false } });
        } catch (error) {
            this.emit('CALL_FAILED', { audio: 'Falha no microfone' });
            this.log('Falha ao atender', error.message);
        }
    }

    normalizeSipDialNumber(destination) {
        const digits = String(destination || '').replace(/\D+/g, '');
        if (digits.startsWith('55') && digits.length > 11) {
            return digits.slice(2);
        }
        return digits;
    }

    async call(destination, domain) {
        const targetNumber = this.normalizeSipDialNumber(destination);
        if (!this.ua || !this.ua.isRegistered()) {
            this.log('Webphone precisa estar registrado antes de ligar');
            return false;
        }
        if (!targetNumber) {
            this.log('Informe um numero para teste direto');
            return false;
        }
        if (this.session) {
            this.log('Ja existe uma chamada ativa');
            return false;
        }
        try {
            await this.requestMicrophone();
            const target = `sip:${targetNumber}@${domain}`;
            this.session = this.ua.call(target, {
                mediaConstraints: { audio: true, video: false },
                rtcOfferConstraints: { offerToReceiveAudio: 1, offerToReceiveVideo: 0 },
            });
            this.session._ligflowDirection = 'outgoing';
            this.session._ligflowRingingConfirmed = false;
            this.bindSessionEvents(this.session);
            this.emit('CALLING', { call: `Ligando para ${targetNumber}`, direction: 'outgoing', ringingConfirmed: false });
            this.log('Chamada SIP iniciada', targetNumber);
            return true;
        } catch (error) {
            this.emit('CALL_FAILED', { audio: 'Falha no microfone' });
            this.log('Falha ao iniciar chamada SIP', error.message);
            return false;
        }
    }

    reject() {
        if (!this.session) return;
        this.session.terminate({ status_code: 486, reason_phrase: 'Busy Here' });
        this.emit('ENDED', { call: 'Rejeitada' });
    }

    hangup() {
        if (!this.session) {
            this.emit('ENDED', { call: 'Encerrada' });
            this.log('Nenhuma chamada SIP ativa para desligar');
            return;
        }
        this.emit('ENDING', { call: 'Encerrando' });
        try {
            this.session.terminate();
        } catch (error) {
            this.log('Falha ao desligar chamada SIP', error.message);
            this.session = null;
            this.emit('ENDED', { call: 'Encerrada' });
        }
    }

    mute() {
        if (!this.session) return;
        this.muted = !this.muted;
        if (this.muted) {
            this.session.mute({ audio: true });
        } else {
            this.session.unmute({ audio: true });
        }
    }

    disconnect() {
        if (this.session) {
            this.session.terminate();
            this.session = null;
        }
        if (this.ua) {
            this.ua.stop();
            this.ua = null;
        }
        this.registeredOnce = false;
        this.emit('DISCONNECTED', { ws: 'Desconectado', register: 'Sem registro', call: 'Nenhuma' });
        this.log('Webphone desconectado');
    }

    sipResponseDetail(event) {
        const response = event?.response || event?.message;
        const code = Number(response?.status_code || response?.statusCode || 0);
        const reason = String(response?.reason_phrase || response?.reasonPhrase || '').trim();
        const cause = String(event?.cause || '').trim();
        const summary = [code ? `SIP ${code}` : '', reason, cause]
            .filter((value, index, values) => value && values.indexOf(value) === index)
            .join(' - ');
        return { code, reason, cause, summary };
    }

    sipCause(event) {
        return this.sipResponseDetail(event).summary;
    }

    sipFailureDetail(event) {
        const response = event?.response || event?.message;
        const code = response?.status_code || response?.statusCode || '';
        const reason = response?.reason_phrase || response?.reasonPhrase || '';
        const cause = event?.cause || '';
        return [code, reason, cause].filter(Boolean).join(' - ');
    }
}

document.querySelectorAll('[data-sip-diagnostic]').forEach((root) => {
    const statusEl = document.querySelector('[data-sip-status]');
    const wsEl = document.querySelector('[data-sip-ws]');
    const registerEl = document.querySelector('[data-sip-register]');
    const callEl = document.querySelector('[data-sip-call]');
    const audioEl = document.querySelector('[data-sip-audio]');
    const logEl = document.querySelector('[data-sip-log]');
    const remoteAudio = document.getElementById('nvoip-remote-audio');

    const addLog = (message) => {
        const item = document.createElement('li');
        item.textContent = `${new Date().toLocaleTimeString('pt-BR')} - ${message}`;
        logEl.prepend(item);
    };

    const service = new NvoipWebphoneService({
        remoteAudio,
        onState: (state) => {
            if (statusEl) statusEl.textContent = state.status;
            if (state.ws && wsEl) wsEl.textContent = state.ws;
            if (state.register && registerEl) registerEl.textContent = state.register;
            if (state.call && callEl) callEl.textContent = state.call;
            if (state.audio && audioEl) audioEl.textContent = state.audio;
        },
        onLog: addLog,
    });

    const readManualConfig = () => ({
        wssUrl: root.querySelector('[data-sip-wss]')?.value.trim(),
        domain: root.querySelector('[data-sip-domain]')?.value.trim(),
        sipUsername: root.querySelector('[data-sip-username]')?.value.trim(),
        sipPassword: root.querySelector('[data-sip-password]')?.value,
        autoAnswer: root.querySelector('[data-sip-auto-answer]')?.checked,
    });

    const loadConfig = async () => {
        if (!root.querySelector('[data-sip-use-saved]')?.checked) {
            return readManualConfig();
        }
        const response = await fetch('?page=sip_config', { credentials: 'same-origin' });
        const json = await response.json();
        if (!json.ok) throw new Error(json.error || 'Falha ao obter configuracao SIP');
        root.setAttribute('data-outbound-via-ari', json.provider === 'ASTERISK' ? '1' : '0');
        const manual = readManualConfig();
        root.querySelector('[data-sip-wss]').value = json.wssUrl || manual.wssUrl || '';
        root.querySelector('[data-sip-domain]').value = json.domain || manual.domain || '';
        root.querySelector('[data-sip-username]').value = json.sipUsername || manual.sipUsername || '';
        return {
            wssUrl: json.wssUrl || manual.wssUrl,
            domain: json.domain || manual.domain,
            sipUsername: json.sipUsername || manual.sipUsername,
            sipPassword: manual.sipPassword || json.sipPassword,
            autoAnswer: manual.autoAnswer ?? json.autoAnswer,
            provider: json.provider || '',
        };
    };

    root.querySelector('[data-sip-connect]')?.addEventListener('click', async () => {
        try {
            service.connect(await loadConfig());
        } catch (error) {
            addLog(error.message);
        }
    });
    root.querySelector('[data-sip-disconnect]')?.addEventListener('click', () => service.disconnect());
    root.querySelector('[data-sip-answer]')?.addEventListener('click', () => service.answer());
    root.querySelector('[data-sip-place-call]')?.addEventListener('click', async () => {
        const config = await loadConfig();
        if (config.provider === 'ASTERISK') {
            const outboundForm = document.createElement('form');
            outboundForm.method = 'post';
            [['action', 'manual_call'], ['campaign_id', '0'], ['manual_phone', root.querySelector('[data-sip-destination]')?.value || '']].forEach(([name, value]) => {
                const field = document.createElement('input');
                field.type = 'hidden';
                field.name = name;
                field.value = value;
                outboundForm.appendChild(field);
            });
            document.body.appendChild(outboundForm);
            outboundForm.submit();
            return;
        }
        service.call(root.querySelector('[data-sip-destination]')?.value, config.domain);
    });
    root.querySelector('[data-sip-reject]')?.addEventListener('click', () => service.reject());
    root.querySelector('[data-sip-hangup]')?.addEventListener('click', () => service.hangup());
    root.querySelector('[data-sip-mute]')?.addEventListener('click', () => service.mute());
    root.querySelector('[data-sip-mic]')?.addEventListener('click', async () => {
        try {
            await service.requestMicrophone();
            if (audioEl) audioEl.textContent = 'Microfone autorizado';
            addLog('Microfone autorizado');
        } catch (error) {
            if (audioEl) audioEl.textContent = 'Falha no microfone';
            addLog(error.message);
        }
    });
});

document.querySelectorAll('[data-sip-floating]').forEach((root) => {
    const panel = root.querySelector('[data-webphone]');
    const form = root.querySelector('[data-floating-webphone-form]');
    const input = root.querySelector('[data-phone-search-input]');
    const registerEl = root.querySelector('[data-floating-register]');
    const statusEl = root.querySelector('[data-floating-status]');
    const destinationEl = root.querySelector('[data-floating-destination]');
    const callButton = root.querySelector('[data-floating-call-button]');
    const dot = root.querySelector('[data-floating-sip-dot]') || root.querySelector('.status-dot');
    const monitor = root.querySelector('[data-floating-monitor]') || root.querySelector('.phone-monitor');
    const callState = root.querySelector('[data-floating-call-state]') || monitor?.querySelector('strong');
    const callDetail = root.querySelector('[data-floating-call-detail]') || monitor?.querySelector('small');
    const remoteAudio = root.querySelector('[data-floating-remote-audio]');
    const stopCallButtons = Array.from(document.querySelectorAll('[data-floating-stop-call]'));
    let config = null;
    let connecting = false;
    let configSyncing = false;
    let autoCallStarted = false;
    let currentSipCallId = null;
    let currentSipStartedAt = null;
    let currentSipAnswered = false;
    let currentSipRingingConfirmed = false;
    let currentSipDirection = 'outgoing';
    let currentSipCallMeta = null;
    let stopRequestedByUser = false;
    let nextAutoCallTimer = null;
    let answerConfirmationTimer = null;
    let ringConfirmationTimer = null;
    let unansweredRingTimer = null;
    let earlyMediaTimer = null;
    let terminalFailureFinalizeTimer = null;
    let forcedTerminalFailure = null;
    let ringbackAudioContext = null;
    let ringbackAudioTimer = null;
    let ringbackToneSamples = 0;
    let ringbackSilenceSamples = 0;
    let ringbackToneQualified = false;

    const setText = (element, text) => {
        if (element && text !== undefined) element.textContent = text;
    };

    const toggleStopCallButtons = (visible) => {
        stopCallButtons.forEach((button) => {
            button.hidden = !visible;
        });
    };

    const refreshCallButtonReady = () => {
        if (!callButton || callButton.classList.contains('hangup')) return;
        callButton.classList.toggle('ready', !isAutoDialing() && (input?.value || '').replace(/\D+/g, '') !== '');
    };

    const isAutoDialing = () => root.getAttribute('data-auto-dialing') === '1';
    const blockManualCallDuringAutoDialing = () => {
        if (!isAutoDialing()) return false;
        setText(callDetail, 'Ligacao manual bloqueada enquanto o atendimento automatico estiver ativo.');
        refreshCallButtonReady();
        return true;
    };

    const isVoicemailCause = (cause = '') => /voicemail|mailbox|caixa\s*postal|answering\s*machine|voice\s*mail|machine\s*detected|amd\s*machine/i.test(String(cause));

    const isTerminalFailureCause = (cause = '') => {
        const value = String(cause || '');
        return isVoicemailCause(value)
            || /\bSIP\s*[3-6]\d{2}\b|\b(?:400|402|403|404|405|406|407|408|410|415|416|420|421|423|480|481|482|483|484|485|486|487|488|491|493|500|501|502|503|504|505|513|580|600|603|604|606)\b/i.test(value)
            || /busy(?:\s*here)?|declin|reject|refus|unavailable|temporarily\s*unavailable|no[\s_-]*answer|timeout|timed\s*out|call[\s_-]*failed|failure|forbidden|not\s*found|not\s*reachable|out\s*of\s*coverage|subscriber\s*absent|network\s*(?:error|failure|unavailable)|congestion|invalid\s*(?:number|destination)|number\s*(?:invalid|unallocated)|early[\s_-]*media|session\s*progress\s*sem\s*toque|ringing[\s_-]*not[\s_-]*confirmed|fora\s*d[ae]\s*[aá]rea|sem\s*cobertura|desligad[oa]|n[aã]o\s*dispon[ií]vel|n[uú]mero\s*inv[aá]lido|n[uú]mero\s*inexistente|ocupad[oa]|recusad[oa]|sem\s*sucesso|chamada\s*n[aã]o\s*completada/i.test(value);
    };

    const isImmediateSkipCause = (cause = '', sipCode = 0) => {
        return Number(sipCode) === 480
            || /\bSIP\s*480\b|temporarily\s*unavailable|no_answer_early_media_timeout/i.test(String(cause || ''));
    };

    const clearAnswerConfirmationTimer = () => {
        if (answerConfirmationTimer) {
            clearTimeout(answerConfirmationTimer);
            answerConfirmationTimer = null;
        }
    };

    const stopRingbackAudioDetector = () => {
        if (ringbackAudioTimer) {
            clearInterval(ringbackAudioTimer);
            ringbackAudioTimer = null;
        }
        ringbackToneSamples = 0;
        ringbackSilenceSamples = 0;
        ringbackToneQualified = false;
        if (ringbackAudioContext) {
            ringbackAudioContext.close().catch(() => {});
            ringbackAudioContext = null;
        }
    };

    const confirmDetectedRingback = () => {
        if (currentSipRingingConfirmed || currentSipAnswered || !currentSipCallId) return;
        currentSipRingingConfirmed = true;
        if (ringConfirmationTimer) {
            clearTimeout(ringConfirmationTimer);
            ringConfirmationTimer = null;
        }
        if (earlyMediaTimer) {
            clearTimeout(earlyMediaTimer);
            earlyMediaTimer = null;
        }
        stopRingbackAudioDetector();
        setText(callState, 'Chamando');
        setText(callDetail, 'Som de chamada confirmado. Aguardando atendimento.');
        reportSipProgress({ ringingConfirmed: true, cause: 'ringback_tone_detected', direction: currentSipDirection });
    };

    const startRingbackAudioDetector = (stream) => {
        if (!stream || !isAutoDialing() || currentSipDirection === 'incoming' || currentSipRingingConfirmed || currentSipAnswered) return;
        stopRingbackAudioDetector();
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (!AudioContextClass) return;
        try {
            ringbackAudioContext = new AudioContextClass();
            const source = ringbackAudioContext.createMediaStreamSource(stream);
            const analyser = ringbackAudioContext.createAnalyser();
            analyser.fftSize = 2048;
            analyser.smoothingTimeConstant = 0.65;
            source.connect(analyser);
            const spectrum = new Uint8Array(analyser.frequencyBinCount);
            ringbackAudioTimer = setInterval(() => {
                if (!currentSipCallId || currentSipAnswered || currentSipRingingConfirmed) {
                    stopRingbackAudioDetector();
                    return;
                }
                analyser.getByteFrequencyData(spectrum);
                const binHz = ringbackAudioContext.sampleRate / analyser.fftSize;
                const from = Math.max(1, Math.floor(350 / binHz));
                const to = Math.min(spectrum.length - 1, Math.ceil(520 / binHz));
                let toneEnergy = 0;
                for (let index = from; index <= to; index += 1) toneEnergy += spectrum[index];
                const toneAverage = toneEnergy / Math.max(1, to - from + 1);
                let referenceEnergy = 0;
                let referenceBins = 0;
                const referenceTo = Math.min(spectrum.length - 1, Math.ceil(1800 / binHz));
                for (let index = Math.max(1, Math.floor(180 / binHz)); index <= referenceTo; index += 1) {
                    if (index >= from && index <= to) continue;
                    referenceEnergy += spectrum[index];
                    referenceBins += 1;
                }
                const referenceAverage = referenceEnergy / Math.max(1, referenceBins);
                const toneDetected = toneAverage >= 38 && toneAverage >= referenceAverage * 1.7;
                if (toneDetected) {
                    ringbackToneSamples += 1;
                    ringbackSilenceSamples = 0;
                    if (ringbackToneSamples >= 6) ringbackToneQualified = true;
                    return;
                }
                if (ringbackToneQualified) {
                    ringbackSilenceSamples += 1;
                    if (ringbackSilenceSamples >= 4) confirmDetectedRingback();
                    return;
                }
                ringbackToneSamples = Math.max(0, ringbackToneSamples - 1);
            }, 100);
        } catch (error) {
            stopRingbackAudioDetector();
            setText(callDetail, `Nao foi possivel analisar o som de chamada: ${error.message}`);
        }
    };

    const clearSignalingTimers = () => {
        stopRingbackAudioDetector();
        if (ringConfirmationTimer) {
            clearTimeout(ringConfirmationTimer);
            ringConfirmationTimer = null;
        }
        if (unansweredRingTimer) {
            clearTimeout(unansweredRingTimer);
            unansweredRingTimer = null;
        }
        if (earlyMediaTimer) {
            clearTimeout(earlyMediaTimer);
            earlyMediaTimer = null;
        }
        if (terminalFailureFinalizeTimer) {
            clearTimeout(terminalFailureFinalizeTimer);
            terminalFailureFinalizeTimer = null;
        }
    };

    const confirmAnsweredCall = async () => {
        if (!currentSipCallId || currentSipAnswered || stopRequestedByUser) return;
        currentSipAnswered = true;
        try {
            const result = await postSipCallEvent('answered', {
                call_id: currentSipCallId,
                ringing_confirmed: currentSipRingingConfirmed,
            });
            if (result.call) showLeadModal(result.call);
        } catch (error) {
            setText(callDetail, error.message);
        }
    };

    const scheduleAnsweredConfirmation = () => {
        if (answerConfirmationTimer || currentSipAnswered || stopRequestedByUser || !currentSipCallId) return;
        answerConfirmationTimer = setTimeout(async () => {
            answerConfirmationTimer = null;
            if (!service.session || currentSipAnswered || stopRequestedByUser) return;
            await confirmAnsweredCall();
        }, 900);
    };

    const sqlNow = () => {
        const now = new Date();
        const pad = (value) => String(value).padStart(2, '0');
        return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
    };

    const setModalText = (selector, value, fallback = '-') => {
        const element = document.querySelector(selector);
        if (element) element.textContent = value || fallback;
    };

    const escapeHtml = (value) => String(value || '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[char]));

    const updateCurrentLeadPanel = (callMeta = {}) => {
        const panel = document.querySelector('.contact-card');
        if (!panel) return;
        panel.innerHTML = `
            <h2>Lead atual</h2>
            <div class="contact-name">${escapeHtml(callMeta.name || 'Ligacao manual')}</div>
            <div class="contact-phone">${escapeHtml(callMeta.destination_number || callMeta.phone || '')}</div>
            <dl>
                <dt>Origem</dt><dd>${escapeHtml(callMeta.origin || 'Manual')}</dd>
                <dt>Cidade</dt><dd>${escapeHtml(callMeta.city_state || '')}</dd>
                <dt>Produto</dt><dd>${escapeHtml(callMeta.product || '')}</dd>
                <dt>Tentativas</dt><dd>${escapeHtml(String(callMeta.attempts ?? 0))}</dd>
            </dl>
        `;
    };

    const showLeadModal = (callMeta = {}) => {
        const modal = document.querySelector('[data-call-modal]');
        if (!modal) return;
        currentSipCallMeta = { ...currentSipCallMeta, ...callMeta };
        const data = currentSipCallMeta || {};
        setModalText('[data-live-lead-name]', data.name || 'Ligacao manual', 'Ligacao manual');
        const leadPhone = String(data.phone || data.destination_number || '');
        const whatsappLink = modal.querySelector('[data-live-whatsapp-link]');
        if (whatsappLink) {
            let phoneDigits = leadPhone.replace(/\D/g, '');
            if (phoneDigits.length === 10 || phoneDigits.length === 11) phoneDigits = `55${phoneDigits}`;
            whatsappLink.textContent = leadPhone || '-';
            whatsappLink.hidden = phoneDigits === '';
            whatsappLink.href = phoneDigits
                ? `https://wa.me/${phoneDigits}?text=${encodeURIComponent(modal.dataset.whatsappMessage || '')}`
                : '#';
        }
        setModalText('[data-live-lead-origin]', data.origin);
        setModalText('[data-live-lead-city]', data.city_state);
        setModalText('[data-live-lead-product]', data.product);
        setModalText('[data-live-lead-attempts]', String(data.attempts ?? 0), '0');
        setModalText('[data-live-call-status]', data.status || 'answered', 'answered');
        setModalText('[data-live-call-external]', data.external_call_id || 'Chamada ativa', 'Chamada ativa');
        setModalText('[data-live-call-destination]', data.destination_number || data.phone);
        const callId = document.querySelector('[data-live-call-id]');
        if (callId) callId.value = data.id || currentSipCallId || '';
        const blockButton = modal.querySelector('[data-live-block-button]');
        if (blockButton) {
            const activeCallId = data.id || currentSipCallId || '';
            blockButton.dataset.quickBlockCall = activeCallId;
            blockButton.hidden = activeCallId === '';
            blockButton.disabled = false;
            blockButton.textContent = 'Bloquear';
        }
        const timer = document.querySelector('[data-live-call-timer]');
        if (timer) {
            timer.dataset.startMs = String(Date.now());
            timer.dataset.start = sqlNow();
            timer.textContent = '00:00';
        }
        modal.classList.remove('is-hidden');
        document.querySelector('[data-modal-countdown-text]')?.replaceChildren(document.createTextNode('Ao finalizar, a janela conta 10 segundos e segue para o proximo numero.'));
    };

    const submitLeadModal = (delaySeconds = 10) => {
        const modal = document.querySelector('[data-call-modal]');
        const form = modal?.querySelector('form[data-delayed-finish]');
        if (!form || form.dataset.submitting === '1') return;
        form.dataset.submitting = '1';
        const button = form.querySelector('[data-delayed-finish-button]');
        const info = modal.querySelector('[data-modal-countdown-text]');
        let remaining = delaySeconds;
        const update = () => {
            if (button) {
                button.disabled = true;
                button.textContent = `Finalizando em ${remaining}s...`;
            }
            if (info) info.textContent = `Avancando automaticamente em ${remaining}s.`;
        };
        update();
        const timer = setInterval(() => {
            remaining -= 1;
            update();
            if (remaining <= 0) {
                clearInterval(timer);
                modal.classList.add('is-hidden');
                form.submit();
            }
        }, 1000);
    };

    const postSipCallEvent = async (event, data = {}) => {
        let lastError = null;
        for (let attempt = 1; attempt <= 2; attempt += 1) {
            try {
                const response = await fetch('?page=sip_call_event', {
                    method: 'POST',
                    credentials: 'same-origin',
                    keepalive: true,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ event, ...data }),
                });
                const body = await response.text();
                if (!body.trim()) throw new Error('Resposta vazia do servidor');
                const json = JSON.parse(body);
                if (!response.ok || !json.ok) throw new Error(json.error || 'Falha ao registrar evento SIP');
                return json;
            } catch (error) {
                lastError = error;
                if (attempt < 2) await new Promise((resolve) => setTimeout(resolve, 250));
            }
        }
        throw new Error(lastError?.message || 'Falha ao registrar evento SIP');
    };

    const refreshPhoneHistory = async () => {
        try {
            const response = await fetch('?page=phone_history', { credentials: 'same-origin' });
            const json = await response.json();
            if (!response.ok || !json.ok) return;
            const emptyMessages = {
                todas: 'Nenhuma ligacao recente.',
                recebidas: 'Nenhuma ligacao recebida registrada.',
                realizadas: 'Nenhuma ligacao realizada.',
                perdidas: 'Nenhuma ligacao perdida.',
            };
            Object.entries(emptyMessages).forEach(([tab, emptyMessage]) => {
                const target = root.querySelector(`[data-subtab-panel="${tab}"]`);
                if (!target) return;
                const items = Array.isArray(json[tab]) ? json[tab] : [];
                if (!items.length) {
                    target.innerHTML = `<p class="phone-empty">${escapeHtml(emptyMessage)}</p>`;
                    return;
                }
                target.innerHTML = items.map((call, index) => {
                    const meta = [call.location, call.time, call.duration].filter(Boolean).join(' - ');
                    const badgeClass = ['green', 'orange', 'blue'][index % 3];
                    return `<button type="button" class="phone-history-item" data-history-phone="${escapeHtml(call.phone)}" data-phone-search="${escapeHtml(`${call.contact || ''} ${call.phone || ''} ${meta}`.toLowerCase())}">
                        <span class="phone-history-badge ${badgeClass}">${Math.max(1, 5 - index)}</span>
                        <span class="phone-history-main"><strong>${escapeHtml(call.phone)}</strong><small>${escapeHtml(meta || call.result || call.status)}</small></span>
                        <span class="phone-history-actions">&#9742; &#8942;</span>
                    </button>`;
                }).join('');
                target.querySelectorAll('[data-history-phone]').forEach((button) => {
                    button.addEventListener('click', () => {
                        if (input) {
                            input.value = service.normalizeSipDialNumber(button.dataset.historyPhone || '');
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        root.querySelector('[data-phone-tab="teclado"]')?.click();
                    });
                });
            });
        } catch (_) {
            // Mantem o historico ja renderizado quando a atualizacao nao estiver disponivel.
        }
    };

    const reportSipProgress = (state = {}) => {
        if (!currentSipCallId) return;
        postSipCallEvent('progress', {
            call_id: currentSipCallId,
            status_code: Number(state.sipCode || 0),
            reason: state.sipReason || '',
            cause: state.cause || '',
            ringing_confirmed: Boolean(state.ringingConfirmed || currentSipRingingConfirmed),
            direction: state.direction || currentSipDirection,
        }).catch((error) => setText(callDetail, error.message));
    };

    const forceTerminalCallFailure = (cause, state = {}) => {
        if (!currentSipCallId || stopRequestedByUser || forcedTerminalFailure) return;
        forcedTerminalFailure = {
            cause: String(cause || 'ringing_not_confirmed'),
            sipCode: Number(state.sipCode || 0),
            sipReason: state.sipReason || '',
        };
        const terminalFailureSnapshot = { ...forcedTerminalFailure };
        currentSipAnswered = false;
        clearAnswerConfirmationTimer();
        clearSignalingTimers();
        setText(callState, 'Ligacao descartada');
        setText(callDetail, 'A operadora nao confirmou que o telefone esta tocando. Encerrando e avancando para a proxima lead.');
        if (service.session || currentSipCallId) {
            service.hangup();
        } else {
            service.emit('CALL_FAILED', {
                call: 'Falhou',
                cause: forcedTerminalFailure.cause,
                sipCode: forcedTerminalFailure.sipCode,
                sipReason: forcedTerminalFailure.sipReason,
                direction: currentSipDirection,
                ringingConfirmed: false,
            });
        }
        const finalizeDelay = isImmediateSkipCause(
            terminalFailureSnapshot.cause,
            terminalFailureSnapshot.sipCode,
        ) ? 50 : (terminalFailureSnapshot.cause === 'no_answer_early_media_timeout' ? 500 : 700);
        terminalFailureFinalizeTimer = setTimeout(async () => {
                terminalFailureFinalizeTimer = null;
                if (!currentSipCallId || stopRequestedByUser || forcedTerminalFailure?.cause !== terminalFailureSnapshot.cause) return;
                await finishSipCallRecord('failed', {
                    cause: terminalFailureSnapshot.cause,
                    terminal_failure: true,
                    immediate_advance: true,
                    urgent_advance: true,
                    sip_code: terminalFailureSnapshot.sipCode,
                    sip_reason: terminalFailureSnapshot.sipReason,
                });
                forcedTerminalFailure = null;
                resetFloatingCallState('Chamada sem retorno valido. Avancando para a proxima lead.');
            }, finalizeDelay);
    };

    const remainingOriginationTimeoutMs = () => {
        const configuredSeconds = Number(config?.callbackTimeoutSeconds || 30);
        const timeoutSeconds = Math.max(10, Number.isFinite(configuredSeconds) ? configuredSeconds : 30);
        const elapsedMs = currentSipStartedAt ? Math.max(0, Date.now() - currentSipStartedAt) : 0;
        return Math.max(0, (timeoutSeconds * 1000) - elapsedMs);
    };

    const startRingConfirmationTimer = () => {
        if (!isAutoDialing() || currentSipDirection === 'incoming') return;
        if (ringConfirmationTimer) clearTimeout(ringConfirmationTimer);
        ringConfirmationTimer = setTimeout(() => {
            ringConfirmationTimer = null;
            if (currentSipCallId && !currentSipRingingConfirmed && !currentSipAnswered) {
                forceTerminalCallFailure('origination_timeout_without_ringing');
            }
        }, remainingOriginationTimeoutMs());
    };

    const startUnansweredRingTimer = () => {
        if (!isAutoDialing() || currentSipDirection === 'incoming') return;
        if (unansweredRingTimer) clearTimeout(unansweredRingTimer);
        unansweredRingTimer = setTimeout(() => {
            unansweredRingTimer = null;
            if (currentSipCallId && currentSipRingingConfirmed && !currentSipAnswered) {
                forceTerminalCallFailure('no_answer_ring_timeout');
            }
        }, remainingOriginationTimeoutMs());
    };

    const finishSipCallRecord = async (eventName, extra = {}) => {
        if (!currentSipCallId) return;
        clearAnswerConfirmationTimer();
        clearSignalingTimers();
        const durationSeconds = currentSipStartedAt ? Math.max(1, Math.floor((Date.now() - currentSipStartedAt) / 1000)) : 0;
        const callId = currentSipCallId;
        const wasAnswered = currentSipAnswered;
        const ringingConfirmed = currentSipRingingConfirmed;
        const stoppedByUser = stopRequestedByUser || Boolean(extra.stopped_by_user);
        const immediateAdvance = Boolean(extra.immediate_advance);
        const urgentAdvance = Boolean(extra.urgent_advance);
        currentSipCallId = null;
        currentSipStartedAt = null;
        currentSipAnswered = false;
        currentSipRingingConfirmed = false;
        currentSipDirection = 'outgoing';
        stopRequestedByUser = false;
        try {
            const result = await postSipCallEvent(eventName, {
                call_id: callId,
                duration_seconds: durationSeconds,
                answered: wasAnswered,
                stopped_by_user: stoppedByUser,
                auto_dialing: isAutoDialing(),
                cause: extra.cause || '',
                terminal_failure: Boolean(extra.terminal_failure),
                ringing_confirmed: ringingConfirmed,
                sip_code: Number(extra.sip_code || 0),
                sip_reason: extra.sip_reason || '',
            });
            refreshPhoneHistory();
            if (result.next_phone && !stoppedByUser) {
                setText(callDetail, immediateAdvance ? 'Falha imediata detectada. Avancando para o proximo numero.' : 'Proximo numero da lista reservado. Iniciando em instantes.');
                if (nextAutoCallTimer) clearTimeout(nextAutoCallTimer);
                nextAutoCallTimer = setTimeout(() => {
                    nextAutoCallTimer = null;
                    if (!stopRequestedByUser) {
                        placeCall(result.next_phone);
                    }
                }, urgentAdvance ? 150 : (immediateAdvance ? 1200 : 1800));
            }
            if (result.queue_empty) {
                setText(callDetail, 'Lista finalizada. Nao ha novos numeros para ligar.');
                setTimeout(() => window.location.reload(), 2200);
            }
        } catch (error) {
            setText(callDetail, error.message);
        }
    };

    const service = new NvoipWebphoneService({
        remoteAudio,
        onState: (state) => {
            setText(statusEl, state.call || state.status);
            if (state.register) setText(registerEl, state.register);
            if (state.call) setText(callState, state.call);
            if (state.direction) currentSipDirection = state.direction;
            if (state.ringingConfirmed) currentSipRingingConfirmed = true;
            if (state.remoteStream) startRingbackAudioDetector(state.remoteStream);
            if (state.status === 'REGISTERED') {
                setText(registerEl, 'Registrado');
                dot?.classList.add('online');
            }
            if (['DISCONNECTED', 'REGISTRATION_FAILED'].includes(state.status)) {
                dot?.classList.remove('online');
            }
            if (['IN_CALL', 'CALLING', 'RINGING', 'EARLY_MEDIA', 'INCOMING', 'ANSWERING', 'ENDING'].includes(state.status)) {
                callButton?.classList.remove('ready');
                callButton?.classList.add('hangup');
                callButton?.setAttribute('aria-label', 'Encerrar chamada');
                monitor?.classList.add('online');
                toggleStopCallButtons(true);
            }
            if (state.status === 'CALLING' && state.sipCode) {
                reportSipProgress(state);
            }
            if (state.status === 'RINGING') {
                currentSipRingingConfirmed = true;
                if (ringConfirmationTimer) {
                    clearTimeout(ringConfirmationTimer);
                    ringConfirmationTimer = null;
                }
                startUnansweredRingTimer();
                reportSipProgress({ ...state, ringingConfirmed: true });
            }
            if (state.status === 'EARLY_MEDIA') {
                reportSipProgress(state);
                if (isAutoDialing() && currentSipDirection !== 'incoming' && !currentSipAnswered) {
                    const terminalSipResponse = Number(state.sipCode || 0) >= 300 && isTerminalFailureCause(state.cause);
                    if (isImmediateSkipCause(state.cause, state.sipCode) || terminalSipResponse) {
                        forceTerminalCallFailure(state.cause || `SIP ${state.sipCode}`, state);
                    }
                }
            }
            if (state.status === 'IN_CALL' && currentSipCallId && !currentSipAnswered) {
                if (earlyMediaTimer) {
                    clearTimeout(earlyMediaTimer);
                    earlyMediaTimer = null;
                }
                clearSignalingTimers();
                scheduleAnsweredConfirmation();
            }
            if (['ENDED', 'CALL_FAILED', 'DISCONNECTED'].includes(state.status)) {
                clearAnswerConfirmationTimer();
                if (earlyMediaTimer) {
                    clearTimeout(earlyMediaTimer);
                    earlyMediaTimer = null;
                }
                clearSignalingTimers();
                const forcedFailure = forcedTerminalFailure;
                const cause = forcedFailure?.cause || state.cause || '';
                const voicemailDetected = isVoicemailCause(cause);
                const terminalFailure = Boolean(forcedFailure)
                    || voicemailDetected
                    || isTerminalFailureCause(cause)
                    || (!currentSipAnswered && state.status === 'CALL_FAILED');
                const hadAnsweredCall = currentSipAnswered && !terminalFailure;
                const urgentAdvance = isImmediateSkipCause(
                    cause,
                    forcedFailure?.sipCode || state.sipCode || 0,
                );
                if (urgentAdvance && service.session) {
                    service.hangup();
                }
                callButton?.classList.remove('hangup');
                callButton?.setAttribute('aria-label', 'Ligar manualmente');
                monitor?.classList.remove('online');
                toggleStopCallButtons(false);
                refreshCallButtonReady();
                finishSipCallRecord(state.status === 'CALL_FAILED' ? 'failed' : 'ended', {
                    cause,
                    immediate_advance: !hadAnsweredCall,
                    urgent_advance: urgentAdvance,
                    terminal_failure: terminalFailure || !hadAnsweredCall,
                    sip_code: forcedFailure?.sipCode || state.sipCode || 0,
                    sip_reason: forcedFailure?.sipReason || state.sipReason || '',
                });
                forcedTerminalFailure = null;
                if (hadAnsweredCall) {
                    showLeadModal({ status: 'pos_atendimento' });
                    submitLeadModal(10);
                }
            }
        },
        onLog: (message) => {
            setText(callDetail, message);
        },
    });
    window.ligflowWebphoneService = service;

    const resetFloatingCallState = (message = 'Chamada encerrada') => {
        clearSignalingTimers();
        callButton?.classList.remove('hangup');
        callButton?.setAttribute('aria-label', 'Ligar manualmente');
        monitor?.classList.remove('online');
        toggleStopCallButtons(false);
        setText(callState, 'Sem chamada ativa');
        setText(statusEl, 'Encerrada');
        setText(callDetail, message);
        refreshCallButtonReady();
    };

    window.ligflowStopWebphoneCall = async (options = {}) => {
        stopRequestedByUser = true;
        forcedTerminalFailure = null;
        clearAnswerConfirmationTimer();
        clearSignalingTimers();
        if (nextAutoCallTimer) {
            clearTimeout(nextAutoCallTimer);
            nextAutoCallTimer = null;
        }
        if (options.playSound && window.ligflowPlayHangupSound) {
            window.ligflowPlayHangupSound();
        }
        if (service.session || currentSipCallId) {
            service.hangup();
        } else {
            service.emit('ENDED', { call: 'Encerrada' });
            service.log('Nenhuma chamada SIP ativa para desligar');
        }
        await finishSipCallRecord('ended', { stopped_by_user: true });
        resetFloatingCallState('Ligacao parada pelo usuario.');
    };

    window.ligflowSkipCurrentCampaignCall = async (options = {}) => {
        if (!isAutoDialing()) {
            await window.ligflowStopWebphoneCall(options);
            return;
        }
        stopRequestedByUser = false;
        forcedTerminalFailure = null;
        clearAnswerConfirmationTimer();
        clearSignalingTimers();
        if (nextAutoCallTimer) {
            clearTimeout(nextAutoCallTimer);
            nextAutoCallTimer = null;
        }
        if (options.playSound && window.ligflowPlayHangupSound) {
            window.ligflowPlayHangupSound();
        }
        if (service.session) {
            service.hangup();
        }
        await finishSipCallRecord('failed', {
            cause: 'skipped_by_consultant',
            terminal_failure: true,
            immediate_advance: true,
        });
        resetFloatingCallState('Chamada atual ignorada. Avancando para a proxima lead.');
    };

    const loadConfig = async () => {
        const response = await fetch('?page=sip_config', { credentials: 'same-origin', cache: 'no-store' });
        const json = await response.json();
        if (!json.ok) throw new Error(json.error || 'Falha ao obter configuracao SIP');
        root.setAttribute('data-outbound-via-ari', json.provider === 'ASTERISK' ? '1' : '0');
        config = json;
        return config;
    };

    const syncGlobalTelephonyConfig = async () => {
        if (service.session || currentSipCallId || connecting || configSyncing || isAutoDialing()) return;
        configSyncing = true;
        const previousVersion = config?.configVersion || '';
        try {
            const loaded = await loadConfig();
            const changed = previousVersion !== '' && previousVersion !== loaded.configVersion;
            if (changed || !service.ua?.isRegistered()) {
                service.connect(loaded);
                setText(registerEl, changed ? 'Atualizando' : 'Registrando');
            }
        } catch (error) {
            setText(callDetail, error.message);
        } finally {
            configSyncing = false;
        }
    };

    const ensureRegistered = async () => {
        if (service.ua?.isRegistered()) return config;
        if (connecting) {
            await waitUntilRegistered();
            return config;
        }
        connecting = true;
        try {
            const loaded = await loadConfig();
            service.connect(loaded);
            setText(registerEl, 'Registrando');
            return loaded;
        } catch (error) {
            setText(registerEl, 'Falha');
            setText(callDetail, error.message);
            return null;
        } finally {
            connecting = false;
        }
    };

    const waitUntilRegistered = async (timeoutMs = 7000) => {
        const startedAt = Date.now();
        while (Date.now() - startedAt < timeoutMs) {
            if (service.ua?.isRegistered()) return true;
            await new Promise((resolve) => setTimeout(resolve, 150));
        }
        return false;
    };

    window.setTimeout(syncGlobalTelephonyConfig, 300);
    window.setInterval(syncGlobalTelephonyConfig, 15000);
    window.addEventListener('focus', syncGlobalTelephonyConfig);

    root.querySelector('[data-webphone-toggle]')?.addEventListener('click', () => {
        if (!panel?.classList.contains('is-hidden')) {
            ensureRegistered();
        }
    });

    const placeCall = async (number) => {
        const loaded = await ensureRegistered();
        if (!loaded) return;
        if (!await waitUntilRegistered()) {
            setText(registerEl, 'Sem registro');
            setText(callDetail, 'Aguarde o webfone registrar antes de ligar.');
            return;
        }
        const cleanNumber = String(number || '').trim();
        if (!cleanNumber) {
            setText(callDetail, 'Informe um numero para ligar.');
            return;
        }
        try {
            clearAnswerConfirmationTimer();
            await service.requestMicrophone();
        } catch (error) {
            setText(callDetail, error.message);
            return;
        }
        try {
            const campaignId = Number(form?.querySelector('[name="campaign_id"]')?.value || 0);
            const registeredCall = await postSipCallEvent('start', {
                phone: cleanNumber,
                campaign_id: campaignId,
                auto_dialing: isAutoDialing(),
            });
            currentSipCallId = registeredCall.callId || null;
            currentSipStartedAt = Date.now();
            currentSipAnswered = false;
            currentSipRingingConfirmed = false;
            currentSipDirection = 'outgoing';
            forcedTerminalFailure = null;
            currentSipCallMeta = registeredCall.call || null;
            if (currentSipCallMeta) updateCurrentLeadPanel(currentSipCallMeta);
        } catch (error) {
            setText(callDetail, error.message);
            if (isAutoDialing() && /lead automatica|reservada nesta campanha/i.test(String(error.message || ''))) {
                setTimeout(() => window.location.reload(), 1200);
            }
            if (!isAutoDialing() && /lista de bloqueio|bloquead/i.test(String(error.message || ''))) {
                window.alert('Contato bloqueado. Este numero esta na lista de bloqueio e nao pode receber chamadas.');
            }
            return;
        }
        const displayNumber = service.normalizeSipDialNumber(currentSipCallMeta?.destination_number || cleanNumber);
        if (input) {
            input.value = displayNumber;
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
        setText(destinationEl, cleanNumber);
        callButton?.classList.add('hangup');
        callButton?.classList.remove('ready');
        callButton?.setAttribute('aria-label', 'Encerrar chamada');
        monitor?.classList.add('online');
        toggleStopCallButtons(true);
        setText(callState, `Ligando para ${cleanNumber}`);
        const started = await service.call(cleanNumber, loaded.domain);
        if (!started) {
            await finishSipCallRecord('failed', { cause: 'call-not-started', terminal_failure: true, immediate_advance: true });
            resetFloatingCallState('Falha ao iniciar chamada.');
            return;
        }
        startRingConfirmationTimer();
    };

    window.ligflowStartWebphoneCall = async (number) => {
        if (blockManualCallDuringAutoDialing()) return false;
        const cleanNumber = service.normalizeSipDialNumber(number || '');
        if (input) {
            input.value = cleanNumber;
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
        panel?.classList.remove('is-hidden');
        root.querySelector('[data-phone-tab="teclado"]')?.click();
        form?.requestSubmit();
        return true;
    };

    input?.addEventListener('input', refreshCallButtonReady);
    refreshCallButtonReady();

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (service.session || currentSipCallId) {
            if (isAutoDialing()) {
                await window.ligflowSkipCurrentCampaignCall();
            } else {
                await window.ligflowStopWebphoneCall();
            }
            return;
        }
        if (blockManualCallDuringAutoDialing()) return;
        try {
            const loaded = await loadConfig();
            if (loaded.provider === 'ASTERISK') {
                HTMLFormElement.prototype.submit.call(form);
                return;
            }
        } catch (error) {
            setText(callDetail, error.message);
            return;
        }
        await placeCall(input?.value || '');
    });

    root.querySelector('[data-phone-tab="recentes"]')?.addEventListener('click', refreshPhoneHistory);

    callButton?.addEventListener('click', async (event) => {
        if (service.session || currentSipCallId) {
            event.preventDefault();
            if (isAutoDialing()) {
                await window.ligflowSkipCurrentCampaignCall({ playSound: true });
            } else {
                await window.ligflowStopWebphoneCall({ playSound: true });
            }
        }
    });

    const autoCallPhone = root.getAttribute('data-auto-call-phone');
    const recoverAutoCallId = Number(root.getAttribute('data-recover-auto-call-id') || 0);
    if (autoCallPhone && !autoCallStarted) {
        autoCallStarted = true;
        const cleanAutoCallPhone = service.normalizeSipDialNumber(autoCallPhone);
        if (input) {
            input.value = cleanAutoCallPhone;
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
        panel?.classList.remove('is-hidden');
        setText(callDetail, 'Iniciando chamada automatica pelo webfone.');
        setTimeout(() => placeCall(cleanAutoCallPhone), 500);
    } else if (recoverAutoCallId && isAutoDialing() && !autoCallStarted) {
        autoCallStarted = true;
        currentSipCallId = recoverAutoCallId;
        currentSipStartedAt = Date.now();
        currentSipAnswered = false;
        currentSipRingingConfirmed = false;
        setText(callDetail, 'Recuperando a fila automatica apos perda da sessao da chamada.');
        setTimeout(async () => {
            await finishSipCallRecord('failed', {
                cause: 'browser_session_lost',
                terminal_failure: true,
                immediate_advance: true,
                urgent_advance: true,
            });
            resetFloatingCallState('Fila recuperada. Avancando para a proxima lead.');
        }, 300);
    }
});
