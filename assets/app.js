const savedTheme = localStorage.getItem('ligflow-theme') || 'dark';
document.body.classList.toggle('light-theme', savedTheme === 'light');

const ligflowAudio = (() => {
    let context = null;
    const ensureContext = () => {
        if (!context) {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextClass) return null;
            context = new AudioContextClass();
        }
        if (context.state === 'suspended') {
            context.resume().catch(() => {});
        }
        return context;
    };

    const playTone = (tones = [], { gain = 0.06, type = 'sine', gap = 0.02 } = {}) => {
        const ctx = ensureContext();
        if (!ctx || !tones.length) return;
        let startAt = ctx.currentTime + 0.01;
        tones.forEach((tone) => {
            const osc = ctx.createOscillator();
            const amp = ctx.createGain();
            osc.type = type;
            osc.frequency.value = tone.freq;
            amp.gain.setValueAtTime(0.0001, startAt);
            amp.gain.exponentialRampToValueAtTime(Math.max(0.0002, gain), startAt + 0.01);
            amp.gain.exponentialRampToValueAtTime(0.0001, startAt + tone.duration);
            osc.connect(amp).connect(ctx.destination);
            osc.start(startAt);
            osc.stop(startAt + tone.duration + 0.02);
            startAt += tone.duration + gap;
        });
    };

    return {
        dial: () => playTone([{ freq: 880, duration: 0.035 }], { gain: 0.035, type: 'square', gap: 0 }),
        hangup: () => playTone([
            { freq: 420, duration: 0.09 },
            { freq: 220, duration: 0.12 },
        ], { gain: 0.05, type: 'triangle', gap: 0.03 }),
        prime: () => ensureContext(),
    };
})();

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const nextTheme = document.body.classList.toggle('light-theme') ? 'light' : 'dark';
        localStorage.setItem('ligflow-theme', nextTheme);
    });
});

document.querySelectorAll('[data-radar-select-all]').forEach((toggle) => {
    toggle.addEventListener('change', () => {
        const table = toggle.closest('table');
        if (!table) return;
        table.querySelectorAll('input[name="place_ids[]"]').forEach((checkbox) => {
            checkbox.checked = toggle.checked;
        });
    });
});

const radarLoadingOverlay = document.querySelector('[data-radar-loading-overlay]');
document.querySelectorAll('[data-radar-loading-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (form.dataset.loading === '1') return;
        event.preventDefault();
        form.dataset.loading = '1';
        radarLoadingOverlay?.classList.remove('is-hidden');
        document.body.classList.add('radar-is-loading');
        requestAnimationFrame(() => requestAnimationFrame(() => form.submit()));
    });
});
window.addEventListener('pageshow', () => {
    document.querySelectorAll('[data-radar-loading-form]').forEach((form) => delete form.dataset.loading);
    radarLoadingOverlay?.classList.add('is-hidden');
    document.body.classList.remove('radar-is-loading');
});

document.querySelectorAll('[data-user-menu]').forEach((menu) => {
    const trigger = menu.querySelector('[data-user-menu-toggle]');
    const panel = menu.querySelector('[data-user-menu-panel]');
    if (!trigger || !panel) return;

    const close = () => {
        menu.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
    };

    trigger.addEventListener('click', (event) => {
        event.stopPropagation();
        const isOpen = menu.classList.toggle('is-open');
        trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    panel.addEventListener('click', (event) => {
        event.stopPropagation();
    });

    document.addEventListener('click', close);
const paymentWatch = document.querySelector('[data-payment-watch]');
if (paymentWatch) {
    const paymentId = paymentWatch.dataset.paymentWatch;
    const statusTarget = paymentWatch.querySelector('[data-payment-status]');
    const timer = window.setInterval(async () => {
        try {
            const response = await fetch(`?page=payment_status&id=${encodeURIComponent(paymentId)}`, { headers: { Accept: 'application/json' } });
            const data = await response.json();
            if (!data.ok) return;
            if (statusTarget) statusTarget.textContent = data.payment.status;
            if (data.payment.status === 'APPROVED') {
                window.clearInterval(timer);
                document.querySelectorAll('.payment-pix-modal, .payment-result-pix').forEach((element) => element.remove());
                window.location.href = '?page=dashboard&payment=approved';
            }
            if (['REJECTED','CANCELLED','EXPIRED','REFUNDED','CHARGED_BACK','ERROR'].includes(data.payment.status)) window.clearInterval(timer);
        } catch (error) {}
    }, 5000);
}

document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
    });
});

document.querySelectorAll('[data-payment-method-tab]').forEach((tab) => {
    tab.addEventListener('click', () => {
        if (tab.disabled) return;
        const method = tab.dataset.paymentMethodTab;
        document.querySelectorAll('[data-payment-method-tab]').forEach((item) => {
            const active = item === tab;
            item.classList.toggle('active', active);
            item.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        document.querySelectorAll('[data-payment-method-panel]').forEach((panel) => {
            panel.hidden = panel.dataset.paymentMethodPanel !== method;
        });
    });
});

const pixPaymentModal = document.querySelector('[data-payment-pix-modal]');
if (pixPaymentModal) {
    const closePixPaymentModal = () => pixPaymentModal.remove();
    pixPaymentModal.querySelectorAll('[data-payment-pix-close]').forEach((button) => button.addEventListener('click', closePixPaymentModal));
    pixPaymentModal.addEventListener('click', (event) => {
        if (event.target === pixPaymentModal) closePixPaymentModal();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && document.body.contains(pixPaymentModal)) closePixPaymentModal();
    });
}

document.querySelectorAll('[data-copy-pix]').forEach((button) => {
    button.addEventListener('click', async () => {
        const scope = button.closest('.payment-pix-dialog, .payment-result-pix');
        const code = scope?.querySelector('textarea')?.value || '';
        if (!code) return;
        try {
            await navigator.clipboard.writeText(code);
            const original = button.textContent;
            button.textContent = 'Codigo copiado';
            window.setTimeout(() => { button.textContent = original; }, 1800);
        } catch (error) {
            scope.querySelector('textarea')?.select();
        }
    });
});

document.querySelectorAll('[data-check-payment]').forEach((button) => {
    button.addEventListener('click', async () => {
        const paymentId = button.dataset.paymentId;
        if (!paymentId || button.disabled) return;
        const original = button.textContent;
        button.disabled = true;
        button.textContent = 'Verificando...';
        try {
            const response = await fetch(`?page=payment_status&id=${encodeURIComponent(paymentId)}`, { headers: { Accept: 'application/json' } });
            const body = await response.text();
            const data = body.trim() ? JSON.parse(body) : null;
            if (!response.ok || !data?.ok) throw new Error('Nao foi possivel verificar o pagamento.');
            document.querySelectorAll('[data-payment-status]').forEach((target) => { target.textContent = data.payment.status; });
            if (data.payment.status === 'APPROVED') {
                button.textContent = 'Pagamento aprovado';
                document.querySelectorAll('.payment-pix-modal, .payment-result-pix').forEach((element) => element.remove());
                window.setTimeout(() => { window.location.href = '?page=dashboard&payment=approved'; }, 600);
                return;
            }
            button.textContent = ['PENDING', 'IN_PROCESS', 'CREATED'].includes(data.payment.status) ? 'Pagamento pendente' : data.payment.status;
            window.setTimeout(() => { button.textContent = original; button.disabled = false; }, 1800);
        } catch (error) {
            button.textContent = 'Falha ao verificar';
            window.setTimeout(() => { button.textContent = original; button.disabled = false; }, 1800);
        }
    });
});

document.querySelectorAll('[data-plan-select]').forEach((select) => {
    const form = select.closest('form');
    if (!form) return;

    const applyPlan = (updateFields = true) => {
        const option = select.selectedOptions[0];
        if (!option) return;
        const values = {
            minutes: option.dataset.minutes,
            maxUsers: option.dataset.maxUsers,
            maxConsultants: option.dataset.maxConsultants,
            maxLists: option.dataset.maxLists,
            maxContacts: option.dataset.maxContacts,
            priceMinute: option.dataset.priceMinute,
        };
        if (updateFields) {
            Object.entries(values).forEach(([key, value]) => {
                if (value === undefined) return;
                form.querySelectorAll(`[data-plan-field="${key}"]`).forEach((input) => {
                    input.value = value;
                });
            });
        }
        const monthly = form.querySelector('[data-plan-display="monthly_price"]');
        const period = form.querySelector('[data-plan-display="period"]');
        const payment = form.querySelector('[data-plan-display="payment"]');
        if (monthly) monthly.value = option.dataset.monthlyPrice ? `R$ ${Number(option.dataset.monthlyPrice).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` : '';
        if (period) period.value = option.dataset.period || '';
        if (payment) payment.value = option.dataset.payment || '';
    };

    select.addEventListener('change', () => applyPlan(true));
    applyPlan(!select.hasAttribute('data-plan-preserve-current'));
});

function formatBrazilPhone(value) {
    const digits = value.replace(/\D+/g, '').slice(0, 13);
    const withoutCountry = digits.startsWith('55') && digits.length > 11 ? digits.slice(2) : digits;
    if (withoutCountry.length <= 2) return withoutCountry;
    if (withoutCountry.length <= 6) return `(${withoutCountry.slice(0, 2)}) ${withoutCountry.slice(2)}`;
    if (withoutCountry.length <= 10) {
        return `(${withoutCountry.slice(0, 2)}) ${withoutCountry.slice(2, 6)}-${withoutCountry.slice(6)}`;
    }
    return `(${withoutCountry.slice(0, 2)}) ${withoutCountry.slice(2, 7)}-${withoutCountry.slice(7, 11)}`;
}

function withoutBrazilCountryCode(value) {
    const digits = String(value || '').replace(/\D+/g, '');
    return digits.startsWith('55') && digits.length > 11 ? digits.slice(2) : digits;
}

document.querySelectorAll('[data-phone-mask]').forEach((input) => {
    input.value = formatBrazilPhone(input.value);
    input.addEventListener('input', () => {
        input.value = formatBrazilPhone(input.value);
    });
});

document.querySelectorAll('[data-reset-list-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        const typed = window.prompt('Para resetar esta lista, digite RESETAR. O historico de chamadas sera mantido.');
        if (typed !== 'RESETAR') {
            event.preventDefault();
            return;
        }
        const confirmation = form.querySelector('input[name="reset_confirmation"]');
        if (confirmation) confirmation.value = typed;
    });
});

function tickTimers() {
    document.querySelectorAll('.timer[data-start]').forEach((timer) => {
        let startedAt = Number(timer.dataset.startMs || 0);
        if (!startedAt) {
            const liveModal = timer.closest('[data-call-modal]');
            if (timer.matches('[data-live-call-timer]') && liveModal && !liveModal.classList.contains('is-hidden')) {
                startedAt = Date.now();
                timer.dataset.startMs = String(startedAt);
            } else {
                const raw = timer.getAttribute('data-start') || '';
                const normalized = /(?:Z|[+-]\d{2}:?\d{2})$/i.test(raw) ? raw : `${raw.replace(' ', 'T')}Z`;
                startedAt = new Date(normalized).getTime();
            }
        }
        const elapsed = Number.isFinite(startedAt) ? Math.max(0, Math.floor((Date.now() - startedAt) / 1000)) : 0;
        const minutes = String(Math.floor(elapsed / 60)).padStart(2, '0');
        const seconds = String(elapsed % 60).padStart(2, '0');
        timer.textContent = `${minutes}:${seconds}`;
    });
}

tickTimers();
setInterval(tickTimers, 1000);

document.querySelectorAll('[data-dialpad]').forEach((dialpad) => {
    const form = dialpad.closest('form');
    const input = form ? form.querySelector('.dial-display') : null;
    if (!input) return;
    dialpad.querySelectorAll('[data-digit]').forEach((button) => {
        button.addEventListener('click', () => {
            input.value += button.getAttribute('data-digit') || '';
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.focus();
            ligflowAudio.dial();
        });
    });
});

document.querySelectorAll('[data-clear-phone]').forEach((button) => {
    button.addEventListener('click', () => {
        const phone = document.querySelector('.dial-display');
        if (phone) {
            phone.value = '';
            phone.dispatchEvent(new Event('input', { bubbles: true }));
            phone.focus();
        }
    });
});

document.querySelectorAll('[data-webphone-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const phone = document.querySelector('[data-webphone]');
        if (phone) phone.classList.toggle('is-hidden');
    });
});

document.querySelectorAll('[data-webphone-close]').forEach((button) => {
    button.addEventListener('click', () => {
        const phone = document.querySelector('[data-webphone]');
        if (phone) phone.classList.add('is-hidden');
    });
});

document.querySelectorAll('[data-phone-tab]').forEach((button) => {
    button.addEventListener('click', () => {
        const tab = button.getAttribute('data-phone-tab');
        document.querySelectorAll('[data-phone-tab]').forEach((item) => item.classList.toggle('active', item === button));
        document.querySelectorAll('[data-tab-panel]').forEach((panel) => {
            panel.classList.toggle('active', panel.getAttribute('data-tab-panel') === tab);
        });
        if (tab === 'teclado') {
            const input = document.querySelector('.dial-display');
            if (input) input.focus();
        }
    });
});

document.querySelectorAll('[data-phone-subtab]').forEach((button) => {
    button.addEventListener('click', () => {
        const tab = button.getAttribute('data-phone-subtab');
        const panel = button.closest('[data-tab-panel]');
        if (!panel) return;
        panel.querySelectorAll('[data-phone-subtab]').forEach((item) => item.classList.toggle('active', item === button));
        panel.querySelectorAll('[data-subtab-panel]').forEach((item) => {
            item.classList.toggle('active', item.getAttribute('data-subtab-panel') === tab);
        });
    });
});

document.querySelectorAll('[data-fill-phone]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.querySelector('.dial-display');
        if (!input) return;
        input.value = withoutBrazilCountryCode(button.getAttribute('data-fill-phone') || '');
        input.dispatchEvent(new Event('input', { bubbles: true }));
        document.querySelector('[data-webphone]')?.classList.remove('is-hidden');
        document.querySelector('[data-phone-tab="teclado"]')?.click();
    });
});

document.querySelectorAll('[data-start-reserved-sip]').forEach((button) => {
    button.addEventListener('click', async () => {
        const number = withoutBrazilCountryCode(button.getAttribute('data-start-reserved-sip') || '');
        const input = document.querySelector('.dial-display');
        const webphone = document.querySelector('[data-webphone]');
        if (input) {
            input.value = number;
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (webphone) webphone.classList.remove('is-hidden');
        document.querySelector('[data-phone-tab="teclado"]')?.click();
        if (window.ligflowStartWebphoneCall) {
            button.disabled = true;
            try {
                await window.ligflowStartWebphoneCall(number);
            } finally {
                button.disabled = false;
            }
            return;
        }
        document.querySelector('[data-floating-call-button]')?.click();
    });
});

document.querySelectorAll('[data-floating-stop-call]').forEach((button) => {
    button.addEventListener('click', async (event) => {
        event.preventDefault();
        const form = button.closest('form');
        button.disabled = true;
        button.textContent = 'Parando...';
        if (window.ligflowStopWebphoneCall) {
            await window.ligflowStopWebphoneCall();
        }
        setTimeout(() => {
            if (form) {
                let statusInput = form.querySelector('input[name="status"][data-stop-status]');
                if (!statusInput) {
                    statusInput = document.createElement('input');
                    statusInput.type = 'hidden';
                    statusInput.name = 'status';
                    statusInput.dataset.stopStatus = '1';
                    form.appendChild(statusInput);
                }
                statusInput.value = 'Pausa';
                form.submit();
            }
        }, 250);
    });
});

document.querySelectorAll('[data-pause-operation]').forEach((button) => {
    button.addEventListener('click', async (event) => {
        const form = button.closest('form');
        if (!form || form.dataset.pausing === '1') return;
        event.preventDefault();
        form.dataset.pausing = '1';
        button.disabled = true;
        button.textContent = 'Pausando...';
        if (window.ligflowStopWebphoneCall) {
            await window.ligflowStopWebphoneCall();
        }
        let statusInput = form.querySelector('input[name="status"][data-stop-status]');
        if (!statusInput) {
            statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.dataset.stopStatus = '1';
            form.appendChild(statusInput);
        }
        statusInput.value = 'Pausa';
        setTimeout(() => form.submit(), 250);
    });
});

document.querySelectorAll('[data-phone-search-input]').forEach((input) => {
    input.addEventListener('input', () => {
        const term = input.value.trim().toLowerCase();
        document.querySelectorAll('[data-phone-search]').forEach((item) => {
            const haystack = (item.getAttribute('data-phone-search') || item.textContent || '').toLowerCase();
            item.classList.toggle('is-filtered', term !== '' && !haystack.includes(term));
        });
    });
});

document.querySelectorAll('[data-delayed-finish]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        if (form.dataset.submitting === '1') return;
        event.preventDefault();
        form.dataset.submitting = '1';
        const button = form.querySelector('[data-delayed-finish-button]');
        const modal = form.closest('[data-call-modal]');
        const info = modal?.querySelector('[data-modal-countdown-text]');
        if (button) {
            button.disabled = true;
            button.textContent = 'Finalizando...';
        }
        if (info) info.textContent = 'Encerrando a ligacao agora.';

        // O BYE SIP precisa sair antes da navegacao que salva o atendimento.
        try {
            if (window.ligflowStopWebphoneCall) {
                await window.ligflowStopWebphoneCall({ playSound: true });
            } else if (window.ligflowWebphoneService) {
                window.ligflowWebphoneService.hangup();
            }
        } finally {
            modal?.classList.add('is-hidden');
            form.submit();
        }
    });
});

document.querySelectorAll('[data-call-modal-close]').forEach((button) => {
    button.addEventListener('click', () => {
        const modal = button.closest('[data-call-modal]');
        const form = modal?.querySelector('form[data-delayed-finish]');
        modal?.classList.add('is-hidden');
        if (form && form.querySelector('[name="call_id"]')?.value) {
            form.submit();
        }
    });
});

document.querySelectorAll('[data-open-call-history]').forEach((button) => {
    button.addEventListener('click', () => {
        const id = button.getAttribute('data-open-call-history');
        document.querySelector(`[data-call-history-modal="${id}"]`)?.classList.remove('is-hidden');
    });
});

document.querySelectorAll('[data-call-history-close]').forEach((button) => {
    button.addEventListener('click', () => {
        button.closest('[data-call-history-modal]')?.classList.add('is-hidden');
    });
});

document.querySelectorAll('[data-open-callback]').forEach((button) => {
    button.addEventListener('click', () => {
        const id = button.getAttribute('data-open-callback');
        document.querySelector(`[data-callback-modal="${id}"]`)?.classList.remove('is-hidden');
    });
});

document.querySelectorAll('[data-callback-modal-close]').forEach((button) => {
    button.addEventListener('click', () => button.closest('[data-callback-modal]')?.classList.add('is-hidden'));
});

document.querySelectorAll('[data-quick-block-call]').forEach((button) => {
    button.addEventListener('click', async () => {
        const callId = Number(button.dataset.quickBlockCall || 0);
        if (!callId || button.disabled) return;
        button.disabled = true;
        button.textContent = 'Bloqueando...';
        try {
            const response = await fetch('?page=quick_block_call', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ call_id: callId }),
            });
            const body = await response.text();
            const result = body.trim() ? JSON.parse(body) : null;
            if (!response.ok || !result?.ok) throw new Error(result?.error || 'Falha ao bloquear numero.');
            button.textContent = 'Bloqueado';
            button.classList.add('is-blocked');
        } catch (error) {
            button.disabled = false;
            button.textContent = 'Bloquear';
            window.alert(error.message);
        }
    });
});

document.querySelectorAll('[data-open-recording]').forEach((button) => {
    button.addEventListener('click', () => {
        const id = button.getAttribute('data-open-recording');
        document.querySelector(`[data-recording-modal="${id}"]`)?.classList.remove('is-hidden');
    });
});

document.querySelectorAll('[data-recording-close]').forEach((button) => {
    button.addEventListener('click', () => {
        button.closest('[data-recording-modal]')?.classList.add('is-hidden');
    });
});

const statusFilterForm = document.querySelector('[data-lead-status-filter-form]');
if (statusFilterForm) {
    const allToggle = statusFilterForm.querySelector('[data-status-filter-all]');
    const optionInputs = () => Array.from(statusFilterForm.querySelectorAll('[data-status-filter-option]'));
    const syncAllToggle = () => {
        if (!allToggle) return;
        const items = optionInputs();
        const checkedCount = items.filter((item) => item.checked).length;
        allToggle.checked = items.length > 0 && checkedCount === items.length;
        allToggle.indeterminate = checkedCount > 0 && checkedCount < items.length;
    };

    statusFilterForm.addEventListener('change', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLInputElement)) return;
        if (target.matches('[data-status-filter-all]')) {
            optionInputs().forEach((item) => {
                item.checked = target.checked;
            });
            syncAllToggle();
            return;
        }
        if (target.matches('[data-status-filter-option]')) {
            syncAllToggle();
        }
    });

    syncAllToggle();
}

const remessaForm = document.querySelector('[data-create-remessa-form]');
if (remessaForm) {
    const selectionInputs = () => Array.from(document.querySelectorAll('[data-remessa-selection]'));
    const toggleInputs = () => Array.from(document.querySelectorAll('[data-remessa-toggle-all]'));
    const syncToggleState = () => {
        const items = selectionInputs();
        const checkedCount = items.filter((item) => item.checked).length;
        toggleInputs().forEach((toggle) => {
            toggle.checked = items.length > 0 && checkedCount === items.length;
            toggle.indeterminate = checkedCount > 0 && checkedCount < items.length;
        });
    };

    document.addEventListener('change', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLInputElement)) return;
        if (target.matches('[data-remessa-toggle-all]')) {
            const checked = target.checked;
            selectionInputs().forEach((item) => {
                item.checked = checked;
            });
            syncToggleState();
            return;
        }
        if (target.matches('[data-remessa-selection]')) {
            syncToggleState();
        }
    });

    remessaForm.addEventListener('submit', (event) => {
        const selected = selectionInputs().filter((item) => item.checked);
        if (!selected.length) {
            event.preventDefault();
            alert('Selecione ao menos uma lead para criar a nova remessa.');
        }
    });

    syncToggleState();
}

const listScroll = document.querySelector('[data-list-infinite-scroll]');
if (listScroll) {
    const rowsTarget = listScroll.querySelector('[data-list-contact-rows]');
    const loading = listScroll.querySelector('[data-list-loading-more]');
    let loadingBatch = false;
    let hasMore = listScroll.dataset.hasMore === '1';
    let nextOffset = Number(listScroll.dataset.nextOffset || 10);
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[char]));
    const fieldCell = (formId, name, value, type = 'text') => `<td><input form="${formId}" name="${name}" type="${type}" value="${escapeHtml(value)}"></td>`;
    const contactRow = (contact) => {
        const id = Number(contact.id);
        const formId = `contact-form-${id}`;
        const wasCalled = Number(contact.attempts || 0) > 0 || Boolean(contact.last_call_at);
        let html = `<tr><td class="select-col"><input type="checkbox" form="remessa-create-form" name="selected_contacts[]" value="${id}" data-remessa-selection></td>`;
        html += `<td><input form="${formId}" type="hidden" name="action" value="update_contact"><input form="${formId}" type="hidden" name="contact_id" value="${id}"><input form="${formId}" type="hidden" name="list_id" value="${Number(contact.list_id)}"><input form="${formId}" type="hidden" name="company_id" value="${Number(listScroll.dataset.companyId)}"><input form="${formId}" name="name" value="${escapeHtml(contact.name)}"></td>`;
        html += `<td><input form="${formId}" name="phone" value="${escapeHtml(contact.phone_raw || contact.phone_e164)}"><small>${escapeHtml(contact.phone_e164)}</small></td>`;
        if (listScroll.dataset.showEmail === '1') html += fieldCell(formId, 'email', contact.email, 'email');
        if (listScroll.dataset.showCity === '1') html += fieldCell(formId, 'city', contact.city);
        if (listScroll.dataset.showState === '1') html += fieldCell(formId, 'state', contact.state);
        if (listScroll.dataset.showProduct === '1') html += fieldCell(formId, 'product', contact.product);
        if (listScroll.dataset.showOrigin === '1') html += fieldCell(formId, 'origin', contact.origin);
        if (listScroll.dataset.showNotes === '1') html += fieldCell(formId, 'notes', contact.notes);
        html += `<td><span class="status-badge ${wasCalled ? 'called' : ''}">${wasCalled ? 'Sim' : 'Nao'}</span></td><td>${escapeHtml(contact.status)}</td>`;
        html += `<td class="actions"><form id="${formId}" method="post"></form><button form="${formId}" class="button small" type="submit">Salvar</button><form method="post" onsubmit="return confirm('Remover este numero da lista?');"><input type="hidden" name="action" value="delete_contact"><input type="hidden" name="contact_id" value="${id}"><input type="hidden" name="list_id" value="${Number(contact.list_id)}"><input type="hidden" name="company_id" value="${Number(listScroll.dataset.companyId)}"><button class="button secondary small" type="submit">Excluir</button></form></td></tr>`;
        return html;
    };
    const loadMore = async () => {
        if (!hasMore || loadingBatch || !rowsTarget) return;
        loadingBatch = true;
        loading?.classList.remove('is-hidden');
        const params = new URLSearchParams({ page: 'list_contacts_batch', list_id: listScroll.dataset.listId, offset: String(nextOffset) });
        new URLSearchParams(window.location.search).getAll('lead_statuses[]').forEach((status) => params.append('lead_statuses[]', status));
        try {
            const response = await fetch(`?${params.toString()}`, { headers: { Accept: 'application/json' } });
            const body = await response.text();
            if (!body.trim()) throw new Error('O servidor retornou um lote vazio. Tente rolar novamente.');
            let data;
            try {
                data = JSON.parse(body);
            } catch (parseError) {
                throw new Error('O servidor retornou um lote de contatos invalido.');
            }
            if (!response.ok || !data.ok) throw new Error(data.error || 'Falha ao carregar contatos.');
            rowsTarget.insertAdjacentHTML('beforeend', data.contacts.map(contactRow).join(''));
            nextOffset = Number(data.next_offset || nextOffset + 10);
            hasMore = Boolean(data.has_more);
        } catch (error) {
            if (loading) loading.textContent = error.message || 'Nao foi possivel carregar mais contatos.';
            hasMore = false;
        } finally {
            loadingBatch = false;
            if (!hasMore) loading?.classList.add('is-hidden');
        }
    };
    listScroll.addEventListener('scroll', () => {
        if (listScroll.scrollTop + listScroll.clientHeight >= listScroll.scrollHeight - 80) loadMore();
    });
    if (hasMore && listScroll.scrollHeight <= listScroll.clientHeight + 20) loadMore();
}

const callbackWebphone = document.querySelector('[data-sip-floating]');
if (callbackWebphone) {
    const storageKey = 'ligflow_callback_notifications_seen';
    const callbackPollBaseDelay = 30000;
    const callbackPollMaxDelay = 60000;
    const callbackEscapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[char]));
    let callbackNotificationPolling = false;
    let callbackPollDelay = callbackPollBaseDelay;
    let callbackPollTimer = null;
    let seenCallbackNotifications = [];
    try {
        const stored = JSON.parse(localStorage.getItem(storageKey) || '[]');
        seenCallbackNotifications = Array.isArray(stored) ? stored.map(String).filter(Boolean) : [];
    } catch (error) {
        seenCallbackNotifications = [];
    }

    const saveSeenCallbackNotifications = () => {
        seenCallbackNotifications = Array.from(new Set(seenCallbackNotifications)).slice(-200);
        localStorage.setItem(storageKey, JSON.stringify(seenCallbackNotifications));
    };
    const markCallbackNotificationSeen = (key) => {
        if (!seenCallbackNotifications.includes(key)) {
            seenCallbackNotifications.push(key);
            saveSeenCallbackNotifications();
        }
    };
    const callbackNotificationContainer = document.createElement('div');
    callbackNotificationContainer.className = 'callback-notification-stack';
    callbackNotificationContainer.setAttribute('aria-live', 'polite');
    document.body.appendChild(callbackNotificationContainer);

    const openCallbackInWebphone = (callback, notification) => {
        const input = callbackWebphone.querySelector('[data-phone-search-input]');
        const panel = callbackWebphone.querySelector('[data-webphone]');
        if (input) {
            input.value = withoutBrazilCountryCode(callback.phone || '');
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
        panel?.classList.remove('is-hidden');
        callbackWebphone.querySelector('[data-phone-tab="teclado"]')?.click();
        markCallbackNotificationSeen(`${callback.id}:${callback.scheduled_at || ''}`);
        notification.remove();
    };

    const showCallbackNotification = (callback) => {
        const id = Number(callback.id);
        const notificationKey = `${id}:${callback.scheduled_at || ''}`;
        const alreadyVisible = Array.from(callbackNotificationContainer.children).some((item) => item.dataset.callbackNotificationKey === notificationKey);
        if (!id || seenCallbackNotifications.includes(notificationKey) || alreadyVisible) return false;
        const notification = document.createElement('article');
        notification.className = 'callback-notification';
        notification.dataset.callbackNotificationKey = notificationKey;
        notification.innerHTML = `
            <button type="button" class="callback-notification-main">
                <span class="callback-notification-bell" aria-hidden="true">&#128276;&#65038;</span>
                <span><strong>Horario de retorno</strong><small>${callbackEscapeHtml(callback.contact || 'Contato')} - ${callbackEscapeHtml(callback.phone || '')}</small></span>
            </button>
            <button type="button" class="callback-notification-close" aria-label="Fechar notificacao">x</button>`;
        notification.querySelector('.callback-notification-main')?.addEventListener('click', () => openCallbackInWebphone(callback, notification));
        notification.querySelector('.callback-notification-close')?.addEventListener('click', () => {
            markCallbackNotificationSeen(notificationKey);
            notification.remove();
        });
        callbackNotificationContainer.appendChild(notification);
        return true;
    };

    const scheduleCallbackPoll = (delay = callbackPollDelay) => {
        window.clearTimeout(callbackPollTimer);
        callbackPollTimer = null;
        if (document.hidden || !navigator.onLine) return;
        callbackPollTimer = window.setTimeout(pollCallbackNotifications, delay);
    };

    const pollCallbackNotifications = async () => {
        if (callbackNotificationPolling || document.hidden || !navigator.onLine) return;
        callbackNotificationPolling = true;
        try {
            const response = await fetch('?page=callback_notifications', { headers: { Accept: 'application/json' } });
            const body = await response.text();
            const data = body.trim() ? JSON.parse(body) : null;
            if (!response.ok || !data?.ok) throw new Error('Falha ao consultar retornos.');
            const newNotifications = (data.callbacks || []).reduce((total, callback) => total + (showCallbackNotification(callback) ? 1 : 0), 0);
            callbackPollDelay = newNotifications > 0
                ? callbackPollBaseDelay
                : Math.min(callbackPollMaxDelay, callbackPollDelay * 2);
        } catch (error) {
            callbackPollDelay = Math.min(callbackPollMaxDelay, callbackPollDelay * 2);
        } finally {
            callbackNotificationPolling = false;
            scheduleCallbackPoll();
        }
    };

    pollCallbackNotifications();
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            window.clearTimeout(callbackPollTimer);
            callbackPollTimer = null;
            return;
        }
        callbackPollDelay = callbackPollBaseDelay;
        pollCallbackNotifications();
    });
    window.addEventListener('online', () => {
        callbackPollDelay = callbackPollBaseDelay;
        pollCallbackNotifications();
    });
    window.addEventListener('offline', () => {
        window.clearTimeout(callbackPollTimer);
        callbackPollTimer = null;
    });
}

document.addEventListener('keydown', (event) => {
    const webphone = document.querySelector('[data-webphone]');
    const input = document.querySelector('.dial-display');
    if (!webphone || !input || webphone.classList.contains('is-hidden')) return;

    const active = document.activeElement;
    const typingElsewhere = active && active !== input && ['INPUT', 'TEXTAREA', 'SELECT'].includes(active.tagName);
    if (typingElsewhere) return;

    if (/^[0-9*#+]$/.test(event.key)) {
        event.preventDefault();
        input.value += event.key;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.focus();
        return;
    }

    if (event.key === 'Backspace') {
        event.preventDefault();
        input.value = input.value.slice(0, -1);
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.focus();
        return;
    }

    if (event.key === 'Escape') {
        webphone.classList.add('is-hidden');
        return;
    }

    if (event.key === 'Enter' && (input.value.trim() !== '' || document.querySelector('.call-fab.hangup'))) {
        event.preventDefault();
        input.closest('form').requestSubmit();
    }
});

(() => {
    const root = document.querySelector('[data-asterisk-diagnostics]');
    if (!root) return;

    const batchesTarget = document.querySelector('[data-asterisk-batches]');
    const alertsTarget = document.querySelector('[data-asterisk-alert-list]');
    const refreshStatus = document.querySelector('[data-asterisk-refresh-status]');
    const totalTarget = document.querySelector('[data-asterisk-total]');
    let timer = null;
    let busy = false;
    let delay = 5000;
    const maxDelay = 30000;
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[char]));
    const displayDate = (value) => {
        if (!value) return '-';
        const parsed = new Date(`${String(value).replace(' ', 'T')}Z`);
        return Number.isNaN(parsed.getTime()) ? '-' : new Intl.DateTimeFormat('pt-BR', { timeZone: 'America/Sao_Paulo', dateStyle: 'short', timeStyle: 'medium' }).format(parsed);
    };
    const duration = (value) => {
        const seconds = Math.max(0, Number(value || 0));
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const remaining = seconds % 60;
        return [hours, minutes, remaining].map((item) => String(item).padStart(2, '0')).join(':');
    };
    const schedule = () => {
        window.clearTimeout(timer);
        if (!document.hidden && navigator.onLine) timer = window.setTimeout(refresh, delay);
    };
    const render = (data) => {
        const health = data.health || {};
        const set = (selector, value) => { const element = document.querySelector(selector); if (element) element.textContent = value; };
        set('[data-health-ari]', health.ari?.state || '-');
        set('[data-health-worker]', health.worker?.state || '-');
        set('[data-health-worker-at]', displayDate(health.worker?.last_event_at));
        set('[data-health-webrtc]', health.webrtc?.state || '-');
        set('[data-health-endpoint]', health.webrtc?.endpoint || '-');
        if (totalTarget) totalTarget.textContent = `${data.pagination?.total || 0} lote(s)`;
        if (alertsTarget) {
            const alerts = data.alerts || [];
            alertsTarget.innerHTML = alerts.length
                ? alerts.map((alert) => `<p class="alert ${alert.level === 'error' ? 'error' : 'warning'}">${alert.batch_id ? `Batch #${Number(alert.batch_id)}: ` : ''}${escapeHtml(alert.message)}</p>`).join('')
                : '<p class="hint">Nenhum alerta para os lotes exibidos.</p>';
        }
        if (batchesTarget) {
            const batches = data.batches || [];
            batchesTarget.innerHTML = batches.length ? batches.map((batch) => {
                const started = batch.created_at ? Math.floor((Date.now() - new Date(`${String(batch.created_at).replace(' ', 'T')}Z`).getTime()) / 1000) : 0;
                return `<tr><td><a href="?page=asterisk_diagnostics&batch_id=${Number(batch.id)}">#${Number(batch.id)}</a></td><td>${escapeHtml(batch.tenant_name || '-')}</td><td>${escapeHtml(batch.campaign_name || '-')}</td><td>${escapeHtml(batch.agent_name || '-')}</td><td>${escapeHtml(batch.status || '-')}</td><td>${Number(batch.requested_parallelism || 0)} / ${Number(batch.effective_parallelism || 0)}</td><td>${escapeHtml(`${batch.telephony_mode || '-'} / ${batch.telephony_trunk || '-'}`)}</td><td>${displayDate(batch.created_at)}</td><td>${duration(started)}</td><td>${Number(batch.originated_count || 0)} / ${Number(batch.active_count || 0)} / ${Number(batch.finalized_count || 0)}</td><td>${Number(batch.winner_count || 0)} / ${Number(batch.loser_count || 0)} / ${Number(batch.late_answered_count || 0)}</td><td>${displayDate(batch.next_started_at)}</td></tr>`;
            }).join('') : '<tr><td colspan="12" class="empty">Nenhum lote encontrado.</td></tr>';
        }
    };
    const refresh = async () => {
        if (busy || document.hidden || !navigator.onLine) return;
        busy = true;
        try {
            const params = new URLSearchParams(window.location.search);
            params.set('page', 'asterisk_diagnostics_data');
            params.delete('call_page');
            const response = await fetch(`?${params.toString()}`, { headers: { Accept: 'application/json' } });
            const body = await response.text();
            const data = body.trim() ? JSON.parse(body) : null;
            if (!response.ok || !data?.ok) throw new Error(data?.error || 'Falha ao atualizar diagnostico.');
            render(data);
            delay = 5000;
            if (refreshStatus) refreshStatus.textContent = `Atualizado ${new Date().toLocaleTimeString('pt-BR')}`;
        } catch (error) {
            delay = Math.min(maxDelay, delay * 2);
            if (refreshStatus) refreshStatus.textContent = 'Atualizacao indisponivel';
        } finally {
            busy = false;
            schedule();
        }
    };
    schedule();
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) return window.clearTimeout(timer);
        delay = 5000;
        refresh();
    });
    window.addEventListener('online', () => { delay = 5000; refresh(); });
    window.addEventListener('offline', () => window.clearTimeout(timer));
})();
