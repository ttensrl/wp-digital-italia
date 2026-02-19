/* ============================================================
   BOOKING - Step navigation
   ============================================================ */

const STEPS = [
    { label: 'Luogo' },
    { label: 'Data e orario' },
    { label: 'Riepilogo' },
];

const TOTAL = STEPS.length;
let currentStep = 1;

/* --- DOM refs --- */
const btnNext    = document.querySelector('.steppers-btn-next');
const btnPrev    = document.querySelector('.steppers-btn-prev');
const serviceSelect = document.getElementById('defaultSelect');
const monthSelect   = document.getElementById('appointment-month');

/* ============================================================
   STEP NAVIGATION
   ============================================================ */

function updateHeader(step) {
    document.querySelectorAll('.steppers-header li').forEach((li, i) => {
        const s = i + 1;
        li.classList.toggle('confirmed', s < step);
        li.classList.toggle('active',    s === step);
    });
    document.querySelector('.steppers-index').textContent = `${step}/${TOTAL}`;
}

function updateContent(step) {
    document.querySelectorAll('.steppers-content [data-steps]').forEach(panel => {
        const active = Number(panel.dataset.steps) === step;
        panel.classList.toggle('active', active);
        panel.classList.toggle('d-none', !active);
    });
}

function updateNav(step) {
    btnPrev.disabled = step === 1;
    const isLast = step === TOTAL;
    btnNext.classList.toggle('d-none', isLast);
    document.querySelector('.steppers-btn-confirm').classList.toggle('d-none', !isLast);
}

function goToStep(step) {
    if (step < 1 || step > TOTAL) return;
    currentStep = step;
    updateHeader(currentStep);
    updateContent(currentStep);
    updateNav(currentStep);
    console.log(`[BOOKING] Step ${currentStep}/${TOTAL}: "${STEPS[currentStep - 1].label}"`);
}

/* ============================================================
   UTILITIES
   ============================================================ */

/**
 * Recupera l'ID del servizio selezionato cercando tra le select note.
 */
function getServiceId() {
    const candidateIds = ['defaultSelect', 'motivo-appuntamento', 'motivo_appuntamento', 'service-select', 'servizio'];
    const candidateNames = 'select[name="motivo-appuntamento"], select[name="servizio_id"], select[name="service_id"]';

    const sources = [
        ...candidateIds.map(id => document.getElementById(id)),
        document.querySelector(candidateNames),
        ...[...document.querySelectorAll('select')].filter(s => !s.id.includes('appointment')),
    ];

    for (const el of sources) {
        if (!el?.value) continue;
        const n = parseInt(el.value, 10);
        if (!isNaN(n) && n > 0) return String(n);
    }

    return '';
}

/**
 * Recupera l'URL base AJAX da window.url[0].
 */
function getAjaxUrl() {
    return window.url?.[0] ?? '';
}

/**
 * Esegue una chiamata AJAX GET aggiungendo i parametri forniti all'URL base.
 * @param {Record<string, string>} params
 * @returns {Promise<any>}
 */
function ajaxFetch(params) {
    const ajaxUrl = getAjaxUrl();
    if (!ajaxUrl) return Promise.reject(new Error('URL AJAX non definito'));

    const url = new URL(ajaxUrl);
    Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));

    return fetch(url.toString(), { headers: { 'Content-Type': 'application/json' } })
        .then(res => {
            if (!res.ok) return res.text().then(t => { throw new Error(t); });
            return res.json();
        });
}

/**
 * Mostra/nasconde elementi del calendario.
 * @param {Object} options - Opzioni per la visibilità
 * @param {boolean|null} options.calendar - Mostra/nasconde il calendario (true=mostra, false=nasconde, null=nessuna modifica)
 * @param {boolean|null} options.slots - Mostra/nasconde gli slot (true=mostra, false=nasconde, null=nessuna modifica)
 */
function setCalendarVisibility({ calendar = null, slots = null } = {}) {
    const calEl   = document.getElementById('calendar-wrapper');
    const slotsEl = document.getElementById('slots-wrapper');
    
    // Validazione dei parametri
    if (calendar !== null && typeof calendar !== 'boolean') {
        console.error('[BOOKING] setCalendarVisibility: calendar deve essere un booleano o null', calendar);
        calendar = null;
    }
    
    if (slots !== null && typeof slots !== 'boolean') {
        console.error('[BOOKING] setCalendarVisibility: slots deve essere un booleano o null', slots);
        slots = null;
    }
    
    // Applicazione della visibilità
    if (calEl && calendar !== null) {
        calEl.style.display = calendar ? 'block' : 'none';
    }
    
    if (slotsEl && slots !== null) {
        slotsEl.style.display = slots ? 'block' : 'none';
    }
}

/* ============================================================
   BOOKING LOGIC
   ============================================================ */

function populateMonthSelect() {
    if (!monthSelect) return;

    const serviceId = getServiceId();
    if (!serviceId) {
        console.error('[BOOKING] Nessun servizio selezionato');
        return;
    }

    monthSelect.innerHTML = '<option value="">Caricamento mesi disponibili...</option>';

    ajaxFetch({ service_id: serviceId })
        .then(data => {
            if (!data.success || !Array.isArray(data.data?.months)) {
                throw new Error('Formato dati non valido');
            }

            const months = data.data.months;

            if (months.length === 0) {
                monthSelect.innerHTML = '<option value="">Nessun mese disponibile</option>';
                monthSelect.disabled = true;
                setCalendarVisibility({ calendar: false, slots: false });
                return;
            }

            monthSelect.innerHTML = '<option value="">Seleziona un mese</option>';
            months.forEach(({ year, value, label }) => {
                const opt = document.createElement('option');
                opt.value       = `${year}-${String(value).padStart(2, '0')}`;
                opt.textContent = label;
                monthSelect.appendChild(opt);
            });

            monthSelect.disabled = false;
            setCalendarVisibility({ calendar: true });
        })
        .catch(err => {
            console.error('[BOOKING] Errore mesi:', err);
            monthSelect.innerHTML = '<option value="">Errore nel caricamento dei mesi</option>';
            monthSelect.disabled = true;
        });
}

function showAvailableDays() {
    const calendarGrid = document.getElementById('calendar-grid');
    if (!calendarGrid || !monthSelect) return;

    const serviceId    = getServiceId();
    const selectedMonth = monthSelect.value;

    if (!serviceId || !selectedMonth) {
        console.error('[BOOKING] Servizio o mese non selezionato');
        return;
    }

    calendarGrid.innerHTML = '<p>Caricamento giorni disponibili...</p>';

    ajaxFetch({ action: 'get_available_appointments', service_id: serviceId, month: selectedMonth })
        .then(data => {
            if (!data.success || !data.data?.days) throw new Error('Formato dati non valido');

            const days = data.data.days;
            calendarGrid.innerHTML = '';

            if (days.length === 0) {
                calendarGrid.innerHTML = '<p>Nessun giorno disponibile per questo mese.</p>';
                return;
            }

            days.forEach(dayObj => {
                const btn = document.createElement('button');
                btn.className        = 'btn btn-outline-primary mr-2 mb-2';
                btn.textContent      = dayObj.label;
                btn.dataset.date     = dayObj.day;
                btn.dataset.fullDate = dayObj.date;

                btn.addEventListener('click', function () {
                    setCalendarVisibility({ slots: false });
                    calendarGrid.querySelectorAll('button').forEach(b => {
                        b.classList.replace('btn-primary', 'btn-outline-primary');
                    });
                    this.classList.replace('btn-outline-primary', 'btn-primary');
                    showAvailableSlots(serviceId, selectedMonth, dayObj.day);
                });

                calendarGrid.appendChild(btn);
            });
        })
        .catch(err => {
            console.error('[BOOKING] Errore giorni:', err);
            calendarGrid.innerHTML = '<p>Errore nel caricamento dei giorni disponibili</p>';
        });
}

function showAvailableSlots(serviceId, month, day) {
    const slotsWrapper = document.getElementById('slots-wrapper');
    const slotsGrid    = document.getElementById('slots-grid');

    serviceId = serviceId || getServiceId();
    if (!slotsWrapper || !slotsGrid || !serviceId || !month || !day) return;

    slotsWrapper.style.display = 'block';
    slotsGrid.innerHTML        = '<p>Caricamento orari disponibili...</p>';

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
                const slotId  = `slot-${slot.start_time.replace(':', '')}-${slot.id}`;
                const wrapper = document.createElement('div');
                wrapper.className = 'slot-time-btn';
                wrapper.setAttribute('role', 'radio');
                wrapper.setAttribute('aria-checked', 'false');
                wrapper.setAttribute('tabindex', '0');

                // inner HTML: hidden input + visual elements
                wrapper.innerHTML = `
                    <input class="slot-input" type="radio" name="appointment-slot"
                           id="${slotId}" value="${slot.id}" data-slot-id="${slot.id}" aria-hidden="true" />
                    <div class="slot-time">${slot.start_time}</div>
                    <div class="slot-duration">${slot.end_time} (${slot.available} disponibili)</div>
                `;

                slotsGrid.appendChild(wrapper);

                const input = wrapper.querySelector('input.slot-input');

                // click su wrapper seleziona
                wrapper.addEventListener('click', function () {
                    // reset di tutte
                    slotsGrid.querySelectorAll('.slot-time-btn').forEach(w => {
                        w.classList.remove('selected');
                        w.setAttribute('aria-checked', 'false');
                        const i = w.querySelector('input.slot-input');
                        if (i) i.checked = false;
                    });
                    wrapper.classList.add('selected');
                    wrapper.setAttribute('aria-checked', 'true');
                    input.checked = true;
                });

                // gestione tastiera: Enter/Space attiva
                wrapper.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
                        e.preventDefault();
                        wrapper.click();
                    }
                });
             });
        })
        .catch(err => {
            console.error('[BOOKING] Errore slot:', err);
            slotsGrid.innerHTML = '<p>Errore nel caricamento degli orari disponibili</p>';
        });
}

/* ============================================================
   EVENT LISTENERS & INIT
   ============================================================ */

btnNext.addEventListener('click', () => goToStep(currentStep + 1));
btnPrev.addEventListener('click', () => goToStep(currentStep - 1));

if (serviceSelect && monthSelect) {
    serviceSelect.addEventListener('change', function () {
        // Reset
        monthSelect.disabled = true;
        monthSelect.innerHTML = '<option value="">Seleziona prima il servizio</option>';
        setCalendarVisibility({ calendar: false, slots: false });

        if (this.value) {
            monthSelect.disabled = false;
            populateMonthSelect();
        }
    });

    monthSelect.addEventListener('change', function () {
        setCalendarVisibility({ slots: false });
        if (this.value) showAvailableDays();
    });
}

// Preseleziona il servizio da query string
const preselectedService = new URLSearchParams(window.location.search).get('servizio_id');
if (preselectedService && serviceSelect) {
    serviceSelect.value = preselectedService;
    serviceSelect.dispatchEvent(new Event('change', { bubbles: true }));
}

goToStep(1);