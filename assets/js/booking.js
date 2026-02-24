const Booking = (function () {
    const STEPS = [
        { label: 'Luogo' },
        { label: 'Data e orario' },
        { label: 'Riepilogo' },
    ];

    let currentStep = 1;

    const els = {
        btnNext: '.steppers-btn-next',
        btnPrev: '.steppers-btn-prev',
        btnConfirm: '.steppers-btn-confirm',
        service: '#booking-service',
        month: '#booking-month',
        calendarWrapper: '#calendar-wrapper',
        calendarGrid: '#calendar-grid',
        calendarTitle: '#calendar-title',
        slotsWrapper: '#slots-wrapper',
        slotsGrid: '#slots-grid',
        feedback: '#booking-feedback',
    };

    function $(selector) {
        return document.querySelector(selector);
    }

    function $$(selector) {
        return document.querySelectorAll(selector);
    }

    function getEl(name) {
        const el = $(els[name]);
        return el && el instanceof HTMLElement ? el : null;
    }

    function getSelectedSlot() {
        return $('.slot-time-btn.selected') 
            || $('.slot-time-btn[data-selected="true"]')
            || (() => {
                const checked = $('input.slot-input:checked');
                return checked?.closest('.slot-time-btn') || null;
            })();
    }

    function showFeedback(message) {
        const fb = getEl('feedback');
        if (!fb) return;
        fb.textContent = message || '';
        fb.classList.toggle('visually-hidden', !message);
    }

    function setVisibility(name, visible) {
        const el = getEl(name);
        if (!el) return;
        el.classList.toggle('d-none', !visible);
        el.setAttribute('aria-hidden', String(!visible));
    }

    function getServiceId() {
        const service = getEl('service');
        if (service?.value) return String(service.value);

        const candidates = [
            'defaultSelect', 'motivo-appuntamento', 'service-select', 'servizio'
        ];
        
        for (const id of candidates) {
            const el = document.getElementById(id);
            if (el?.value) {
                const n = parseInt(el.value, 10);
                if (!isNaN(n) && n > 0) return String(n);
            }
        }

        const fallback = $('select[name="servizio_id"], select[name="service_id"]');
        if (fallback?.value) return String(fallback.value);

        return '';
    }

    function getAjaxUrl() {
        return window.url?.[0] ?? '';
    }

    function ajaxFetch(params) {
        const ajaxUrl = getAjaxUrl();
        if (!ajaxUrl) return Promise.reject(new Error('URL AJAX non definito'));

        const url = new URL(ajaxUrl);
        Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));

        return fetch(url.toString())
            .then(res => res.ok ? res.json() : res.text().then(t => { throw new Error(t); }));
    }

    function getFormData() {
        return {
            nome: $('#applicant-name')?.value?.trim() || '',
            cognome: $('#applicant-surname')?.value?.trim() || '',
            email: $('#applicant-email')?.value?.trim() || '',
            telefono: $('#applicant-phone')?.value?.trim() || '',
            messaggio: $('#applicant-message')?.value?.trim() || '',
        };
    }

    function validateStep1() {
        const service = getEl('service');
        if (!service?.value) {
            showFeedback('Seleziona un servizio prima di procedere.');
            return false;
        }
        if (!getSelectedSlot()) {
            showFeedback('Seleziona un orario prima di procedere.');
            return false;
        }
        return true;
    }

    function validateStep2() {
        const { nome, cognome, email } = getFormData();
        
        if (!nome) {
            showFeedback('Inserisci il nome del richiedente.');
            return false;
        }
        if (!cognome) {
            showFeedback('Inserisci il cognome del richiedente.');
            return false;
        }
        if (!email || !($('#applicant-email')?.checkValidity())) {
            showFeedback('Inserisci un indirizzo email valido.');
            return false;
        }
        return true;
    }

    function isStepValid(step) {
        if (step === 1) return validateStep1();
        if (step === 2) return validateStep2();
        return true;
    }

    function updateStepper() {
        const steppersHeader = $$('.steppers-header li');
        steppersHeader.forEach((li, i) => {
            const s = i + 1;
            li.classList.toggle('confirmed', s < currentStep);
            li.classList.toggle('active', s === currentStep);
        });

        const steppersIndex = $('.steppers-index');
        if (steppersIndex) {
            steppersIndex.textContent = `${currentStep}/${STEPS.length}`;
        }

        $$('.steppers-content [data-steps]').forEach(panel => {
            const active = Number(panel.dataset.steps) === currentStep;
            panel.classList.toggle('active', active);
            panel.classList.toggle('d-none', !active);
        });

        const btnPrev = getEl('btnPrev');
        if (btnPrev) btnPrev.disabled = currentStep === 1;

        const isLast = currentStep === STEPS.length;
        const btnNext = getEl('btnNext');
        if (btnNext) {
            btnNext.classList.toggle('d-none', isLast);
        }
        
        const btnConfirm = getEl('btnConfirm');
        if (btnConfirm) {
            btnConfirm.classList.toggle('d-none', !isLast);
        }

        if (currentStep === 3) fillReview();
        
        updateNextButtonState();
    }

    function goToStep(step) {
        if (step < 1 || step > STEPS.length) return;
        currentStep = step;
        showFeedback('');
        updateStepper();
    }

    function updateNextButtonState() {
        const btnNext = getEl('btnNext');
        if (!btnNext) return;
        
        const valid = isStepValid(currentStep);
        btnNext.disabled = !valid;
        if (valid) showFeedback('');
    }

    function fillReview() {
        const service = getEl('service');
        $('#review-service').textContent = service?.selectedOptions?.[0]?.textContent || '';

        const selectedDayBtn = $('#calendar-grid button.btn.btn-primary');
        $('#review-date').textContent = selectedDayBtn?.dataset?.fullDate || selectedDayBtn?.dataset?.date || '';

        const selectedSlot = getSelectedSlot();
        if (selectedSlot) {
            const time = selectedSlot.querySelector('.slot-time')?.textContent || '';
            const duration = selectedSlot.querySelector('.slot-duration')?.textContent || '';
            $('#review-time').textContent = duration ? `${time} — ${duration}` : time;
        } else {
            $('#review-time').textContent = '';
        }

        const { nome, cognome, email, telefono, messaggio } = getFormData();
        $('#review-name').textContent = nome;
        $('#review-surname').textContent = cognome;
        $('#review-email').textContent = email;
        $('#review-phone').textContent = telefono;
        $('#review-message').textContent = messaggio;
    }

    function loadMonths() {
        const monthSelect = getEl('month');
        const service = getEl('service');
        
        if (!monthSelect || !service?.value) return;

        monthSelect.innerHTML = '<option value="">Caricamento mesi disponibili...</option>';

        ajaxFetch({ action: 'get_available_appointments', service_id: service.value })
            .then(data => {
                if (!data.success || !Array.isArray(data.data?.months)) {
                    throw new Error('Formato dati non valido');
                }

                const months = data.data.months;
                
                if (months.length === 0) {
                    monthSelect.innerHTML = '<option value="">Nessun mese disponibile</option>';
                    monthSelect.disabled = true;
                    setVisibility('calendarWrapper', false);
                    setVisibility('slotsWrapper', false);
                    return;
                }

                monthSelect.innerHTML = '<option value="">Seleziona un mese</option>';
                months.forEach(({ year, value, label }) => {
                    const opt = document.createElement('option');
                    opt.value = `${year}-${String(value).padStart(2, '0')}`;
                    opt.textContent = label;
                    monthSelect.appendChild(opt);
                });

                monthSelect.disabled = false;
                setVisibility('calendarWrapper', false);
                setVisibility('slotsWrapper', false);
            })
            .catch(err => {
                console.error('[BOOKING] Errore mesi:', err);
                monthSelect.innerHTML = '<option value="">Errore nel caricamento dei mesi</option>';
                monthSelect.disabled = true;
            });
    }

    function loadDays() {
        const monthSelect = getEl('month');
        const calendarGrid = getEl('calendarGrid');
        
        if (!calendarGrid || !monthSelect?.value) return;

        const serviceId = getServiceId();
        if (!serviceId) return;

        calendarGrid.innerHTML = '<p>Caricamento giorni disponibili...</p>';
        setVisibility('calendarWrapper', true);

        ajaxFetch({ action: 'get_available_appointments', service_id: serviceId, month: monthSelect.value })
            .then(data => {
                if (!data.success || !data.data?.days) throw new Error('Formato dati non valido');

                const days = data.data.days;
                const calTitle = getEl('calendarTitle');
                calendarGrid.innerHTML = '';

                if (days.length === 0) {
                    if (calTitle) calTitle.classList.add('d-none');
                    calendarGrid.innerHTML = '<p>Nessun giorno disponibile per questo mese.</p>';
                    updateNextButtonState();
                    return;
                }

                if (calTitle) calTitle.classList.remove('d-none');

                days.forEach(day => {
                    const btn = document.createElement('button');
                    btn.className = 'btn btn-outline-primary mr-2 mb-2';
                    btn.textContent = day.label;
                    btn.dataset.date = day.day;
                    btn.dataset.fullDate = day.date;

                    btn.addEventListener('click', function () {
                        setVisibility('slotsWrapper', false);
                        calendarGrid.querySelectorAll('button').forEach(b => {
                            b.classList.replace('btn-primary', 'btn-outline-primary');
                        });
                        this.classList.replace('btn-outline-primary', 'btn-primary');
                        loadSlots(day.day);
                    });

                    calendarGrid.appendChild(btn);
                });

                updateNextButtonState();
            })
            .catch(err => {
                console.error('[BOOKING] Errore giorni:', err);
                calendarGrid.innerHTML = '<p>Errore nel caricamento dei giorni disponibili</p>';
            });
    }

    function loadSlots(day) {
        const slotsGrid = getEl('slotsGrid');
        const serviceId = getServiceId();
        const month = getEl('month')?.value;

        if (!slotsGrid || !serviceId || !month || !day) return;

        setVisibility('slotsWrapper', true);
        slotsGrid.innerHTML = '<p>Caricamento orari disponibili...</p>';

        ajaxFetch({ action: 'get_available_appointments', service_id: serviceId, month, day })
            .then(data => {
                if (!data.success || !data.data?.slots) throw new Error('Formato dati non valido');

                const slots = data.data.slots;
                slotsGrid.innerHTML = '';

                if (slots.length === 0) {
                    slotsGrid.innerHTML = '<p>Nessuno slot disponibile per questo giorno.</p>';
                    return;
                }

                slots.forEach(slot => {
                    const slotId = `slot-${slot.start_time.replace(':', '')}-${slot.id}`;
                    const wrapper = document.createElement('div');
                    wrapper.className = 'slot-time-btn';
                    wrapper.setAttribute('role', 'radio');
                    wrapper.setAttribute('aria-checked', 'false');
                    wrapper.setAttribute('tabindex', '0');

                    wrapper.innerHTML = `
                        <input class="slot-input" type="radio" name="appointment-slot"
                               id="${slotId}" value="${slot.id}" data-slot-id="${slot.id}" aria-hidden="true" />
                        <div class="slot-time">${slot.start_time}</div>
                        <div class="slot-duration">${slot.end_time} (${slot.available} disponibili)</div>
                    `;

                    slotsGrid.appendChild(wrapper);

                    const input = wrapper.querySelector('input');

                    wrapper.addEventListener('click', function () {
                        slotsGrid.querySelectorAll('.slot-time-btn').forEach(w => {
                            w.classList.remove('selected');
                            w.setAttribute('aria-checked', 'false');
                            w.removeAttribute('data-selected');
                            w.querySelector('input')?.checked && (w.querySelector('input').checked = false);
                        });
                        wrapper.classList.add('selected');
                        wrapper.setAttribute('aria-checked', 'true');
                        wrapper.setAttribute('data-selected', 'true');
                        input.checked = true;
                        updateNextButtonState();
                    });

                    wrapper.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
                            e.preventDefault();
                            wrapper.click();
                        }
                    });
                });

                updateNextButtonState();
            })
            .catch(err => {
                console.error('[BOOKING] Errore slot:', err);
                slotsGrid.innerHTML = '<p>Errore nel caricamento degli orari disponibili</p>';
            });
    }

    function submitBooking() {
        const serviceId = getServiceId();
        const slotEl = getSelectedSlot();
        const slotId = slotEl?.querySelector('input')?.value;
        const { nome, cognome, email, telefono, messaggio } = getFormData();

        if (!serviceId || !slotId || !nome || !cognome || !email) {
            showFeedback('Dati mancanti. Verifica tutti i campi obbligatori.');
            return;
        }

        const btnConfirm = getEl('btnConfirm');
        if (btnConfirm) {
            btnConfirm.disabled = true;
            btnConfirm.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Invio in corso...';
        }

        const formData = new FormData();
        formData.append('action', 'submit_booking');
        formData.append('slot_id', slotId);
        formData.append('service_id', serviceId);
        formData.append('nome', nome);
        formData.append('cognome', cognome);
        formData.append('email', email);
        formData.append('telefono', telefono);
        formData.append('messaggio', messaggio);

        fetch(getAjaxUrl(), { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data.data);
                } else {
                    showFeedback(data.data || 'Errore durante la prenotazione.');
                    resetConfirmButton(btnConfirm);
                }
            })
            .catch(err => {
                console.error('[BOOKING] Errore rete:', err);
                showFeedback('Errore di connessione. Riprova.');
                resetConfirmButton(btnConfirm);
            });
    }

    function resetConfirmButton(btn) {
        if (!btn) return;
        btn.disabled = false;
        btn.innerHTML = '<svg class="icon icon-white me-1" aria-hidden="true"><use href="#it-check"></use></svg>Invia prenotazione';
    }

    function showSuccess() {
        const stepper = $('#booking-stepper');
        if (!stepper) return;

        stepper.innerHTML = `
            <div class="text-center py-5">
                <svg class="icon icon-success icon-xl mb-4" aria-hidden="true" style="width: 64px; height: 64px;">
                    <use href="#it-check-circle"></use>
                </svg>
                <h1 class="h3 text-success mb-3">Prenotazione confermata!</h1>
                <p class="text-muted mb-4">La tua prenotazione è stata registrata con successo.<br>Riceverai una email di conferma all'indirizzo indicato.</p>
                <a href="/" class="btn btn-primary">Torna alla home</a>
            </div>
        `;
    }

    function isBookingPage() {
        return getEl('service') !== null;
    }

    function init() {
        if (!isBookingPage()) return;

        const btnPrev = getEl('btnPrev');
        const btnNext = getEl('btnNext');
        const btnConfirm = getEl('btnConfirm');
        const service = getEl('service');
        const month = getEl('month');

        if (btnPrev) {
            btnPrev.addEventListener('click', () => goToStep(currentStep - 1));
        }

        if (btnNext) {
            btnNext.addEventListener('click', () => {
                if (isStepValid(currentStep)) {
                    goToStep(currentStep + 1);
                }
            });
        }

        if (btnConfirm) {
            btnConfirm.addEventListener('click', e => {
                e.preventDefault();
                if (isStepValid(currentStep)) {
                    submitBooking();
                }
            });
        }

        if (service && month) {
            service.addEventListener('change', function () {
                month.disabled = true;
                month.innerHTML = '<option value="">Seleziona prima il servizio</option>';
                setVisibility('calendarWrapper', false);
                setVisibility('slotsWrapper', false);

                if (this.value) {
                    month.disabled = false;
                    loadMonths();
                }
                updateNextButtonState();
            });

            month.addEventListener('change', function () {
                setVisibility('calendarWrapper', false);
                setVisibility('slotsWrapper', false);
                if (this.value) loadDays();
                updateNextButtonState();
            });
        }

        const applicantFields = ['applicant-name', 'applicant-surname', 'applicant-email', 'applicant-phone', 'applicant-message'];
        applicantFields.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', updateNextButtonState);
        });

        const preselectedService = window.bookingData?.preselectedService || new URLSearchParams(window.location.search).get('servizio_id');
        if (preselectedService && service) {
            service.value = preselectedService;
            service.dispatchEvent(new Event('change', { bubbles: true }));
        } else if (month) {
            month.disabled = true;
        }

        goToStep(1);
    }

    return { init };

})();

document.addEventListener('DOMContentLoaded', Booking.init);
