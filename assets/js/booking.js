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
const serviceSelect = document.getElementById('booking-service');
const monthSelect   = document.getElementById('booking-month');

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
    // Se esiste il select principale usato nel template, usalo
    if (typeof serviceSelect !== 'undefined' && serviceSelect && serviceSelect.value) {
        return String(serviceSelect.value);
    }

    // Fallback: cerca altri select conosciuti
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
    
    // Applicazione della visibilità: preferiamo usare la classe bootstrap d-none
    if (calEl && calendar !== null) {
        if (calendar) {
            calEl.classList.remove('d-none');
            calEl.setAttribute('aria-hidden', 'false');
        } else {
            calEl.classList.add('d-none');
            calEl.setAttribute('aria-hidden', 'true');
        }
    }

    if (slotsEl && slots !== null) {
        if (slots) {
            slotsEl.classList.remove('d-none');
            slotsEl.setAttribute('aria-hidden', 'false');
        } else {
            slotsEl.classList.add('d-none');
            slotsEl.setAttribute('aria-hidden', 'true');
        }
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

    // Richiesta mesi: includiamo esplicitamente l'action
    ajaxFetch({ action: 'get_available_appointments', service_id: serviceId })
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
             // Non mostrare ancora il calendario: verrà mostrato quando si seleziona il mese
             setCalendarVisibility({ calendar: false, slots: false });
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
            const calWrapper = document.getElementById('calendar-wrapper');
            const calTitle = document.getElementById('calendar-title');
            calendarGrid.innerHTML = '';

            if (days.length === 0) {
                // Mostriamo il wrapper (per il messaggio), ma teniamo nascosto il titolo
                if (calWrapper) {
                    calWrapper.classList.remove('d-none');
                    calWrapper.setAttribute('aria-hidden', 'false');
                }
                if (calTitle) calTitle.classList.add('d-none');
                calendarGrid.innerHTML = '<p>Nessun giorno disponibile per questo mese.</p>';
                // aggiorna stato bottone avanti
                updateNextButtonState();
                return;
            }

            // Ci sono giorni disponibili: mostriamo titolo e wrapper
            if (calWrapper) {
                calWrapper.classList.remove('d-none');
                calWrapper.setAttribute('aria-hidden', 'false');
            }
            if (calTitle) calTitle.classList.remove('d-none');

            days.forEach(dayObj => {
                const btn = document.createElement('button');
                btn.className        = 'btn btn-outline-primary mr-2 mb-2';
                btn.textContent      = dayObj.label;
                btn.dataset.date     = dayObj.day;
                btn.dataset.fullDate = dayObj.date;

                btn.addEventListener('click', function () {
                    // Nascondiamo gli slot (se visibili) prima di ricaricarli
                    setCalendarVisibility({ slots: false });
                    calendarGrid.querySelectorAll('button').forEach(b => {
                        b.classList.replace('btn-primary', 'btn-outline-primary');
                    });
                    this.classList.replace('btn-outline-primary', 'btn-primary');
                    showAvailableSlots(serviceId, selectedMonth, dayObj.day);
                });

                calendarGrid.appendChild(btn);
            });
            // dopo aver popolato i giorni aggiorniamo lo stato del bottone avanti
            updateNextButtonState();
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

    // Mostriamo il loader e il wrapper con classi (non manipoliamo style direttamente)
    setCalendarVisibility({ slots: true });
    slotsGrid.innerHTML        = '<p>Caricamento orari disponibili...</p>';

     ajaxFetch({ action: 'get_available_appointments', service_id: serviceId, month, day })
         .then(data => {
             if (!data.success || !data.data?.slots) throw new Error('Formato dati non valido');

             const slots = data.data.slots;
             slotsGrid.innerHTML = '';

             if (slots.length === 0) {
                 slotsGrid.innerHTML = '<p>Nessuno slot disponibile per questo giorno.</p>';
                 // assicurati che il wrapper degli slot sia visibile per il messaggio
                 if (slotsWrapper) {
                     slotsWrapper.classList.remove('d-none');
                     slotsWrapper.setAttribute('aria-hidden', 'false');
                 }
                 return;
             }

             // Mostriamo la griglia degli slot
             if (slotsWrapper) {
                 slotsWrapper.classList.remove('d-none');
                 slotsWrapper.setAttribute('aria-hidden', 'false');
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
                         w.removeAttribute('data-selected');
                         const i = w.querySelector('input.slot-input');
                         if (i) i.checked = false;
                     });
                     wrapper.classList.add('selected');
                     wrapper.setAttribute('aria-checked', 'true');
                     input.checked = true;
                     // marcatore esplicito per debug/controllo
                     wrapper.setAttribute('data-selected', 'true');
                     // aggiorna stato bottone avanti
                     updateNextButtonState();
                 });

                 // gestione tastiera: Enter/Space attiva
                 wrapper.addEventListener('keydown', function (e) {
                     if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
                         e.preventDefault();
                         wrapper.click();
                     }
                 });
              });
            // aggiornamento stato bottone avanti dopo popolamento
            updateNextButtonState();
         })
         .catch(err => {
             console.error('[BOOKING] Errore slot:', err);
             slotsGrid.innerHTML = '<p>Errore nel caricamento degli orari disponibili</p>';
         });
}

/* ============================================================
   EVENT LISTENERS & INIT
   ============================================================ */

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
        // Aggiorna la validazione dopo la selezione del servizio
        setupRealTimeValidation();
        updateNextButtonState();
    });

    monthSelect.addEventListener('change', function () {
        // Nascondi calendario e slot fino a quando non riceviamo i giorni per il mese selezionato
        setCalendarVisibility({ calendar: false, slots: false });
        if (this.value) showAvailableDays();
        // Aggiorna la validazione dopo la selezione del mese
        setupRealTimeValidation();
        updateNextButtonState();
     });
 }

// Preseleziona il servizio da query string
const preselectedService = new URLSearchParams(window.location.search).get('servizio_id');
if (preselectedService && serviceSelect) {
    serviceSelect.value = preselectedService;
    serviceSelect.dispatchEvent(new Event('change', { bubbles: true }));
}

goToStep(1);

// Inizializzazione di base - la logica principale è in booking.js
document.addEventListener('DOMContentLoaded', function () {
    // Disabilita inizialmente il select dei mesi fino a quando un servizio non viene selezionato
    if (monthSelect) {
        monthSelect.disabled = true;
    }

    // Preseleziona il servizio se presente nei dati passati da WordPress o nell'URL
    const preselectedService = window.bookingData?.preselectedService || new URLSearchParams(window.location.search).get('servizio_id');
    
    if (preselectedService && serviceSelect) {
        serviceSelect.value = preselectedService;
        serviceSelect.dispatchEvent(new Event('change', { bubbles: true }));
    }
});

// Aggiungi listener sui campi del richiedente per aggiornare lo stato del bottone avanti
['applicant-name', 'applicant-surname', 'applicant-email', 'applicant-phone', 'applicant-message'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', updateNextButtonState);
});

// Inizializza stato bottone avanti
updateNextButtonState();

// No-op placeholder per evitare errori se chiamata da listener; può essere implementata se serve validazione live
function setupRealTimeValidation() {
    // attualmente non serve implementare la validazione in tempo reale;
    // questa funzione evita ReferenceError nelle callback che la invocano
}

// Imposta messaggio di feedback inline (aria-live)
function setFeedback(message) {
    const fb = document.getElementById('booking-feedback');
    if (!fb) return;
    if (message) {
        fb.textContent = message;
        fb.classList.remove('visually-hidden');
    } else {
        fb.textContent = '';
        fb.classList.add('visually-hidden');
    }
}

// Verifica se lo step corrente è valido senza mostrare messaggi
function isCurrentStepValid() {
    if (currentStep === 1) {
        const service = document.getElementById('booking-service');
        if (!service || !service.value) return false;
        // check slot selection
        let selectedSlot = document.querySelector('.slot-time-btn.selected') || document.querySelector('.slot-time-btn[data-selected="true"]');
        if (!selectedSlot) {
            const checkedInput = document.querySelector('input.slot-input:checked');
            if (checkedInput) selectedSlot = checkedInput.closest('.slot-time-btn');
        }
        return !!selectedSlot;
    }

    if (currentStep === 2) {
        const name = document.getElementById('applicant-name');
        const surname = document.getElementById('applicant-surname');
        const email = document.getElementById('applicant-email');
        if (!name || !name.value.trim()) return false;
        if (!surname || !surname.value.trim()) return false;
        if (!email || !email.checkValidity()) return false;
        return true;
    }

    return true;
}

// Abilita o disabilita il bottone Avanti in base alla validazione corrente
function updateNextButtonState() {
    const next = btnNext;
    if (!next) return;
    const valid = isCurrentStepValid();
    next.disabled = !valid;
    if (valid) setFeedback('');
}

// Validazione attivata al click: usa isCurrentStepValid() e setFeedback per messaggi
function validateCurrentStep() {
    setFeedback('');
    if (currentStep === 1) {
        const service = document.getElementById('booking-service');
        if (!service || !service.value) {
            setFeedback('Seleziona un servizio prima di procedere.');
            return false;
        }
        // slot
        let selectedSlot = document.querySelector('.slot-time-btn.selected') || document.querySelector('.slot-time-btn[data-selected="true"]');
        if (!selectedSlot) {
            const checkedInput = document.querySelector('input.slot-input:checked');
            if (checkedInput) selectedSlot = checkedInput.closest('.slot-time-btn');
        }
        if (!selectedSlot) {
            setFeedback('Seleziona un orario prima di procedere.');
            return false;
        }
        return true;
    }

    if (currentStep === 2) {
        const name = document.getElementById('applicant-name');
        const surname = document.getElementById('applicant-surname');
        const email = document.getElementById('applicant-email');
        if (!name || !name.value.trim()) {
            setFeedback('Inserisci il nome del richiedente.');
            name?.focus();
            return false;
        }
        if (!surname || !surname.value.trim()) {
            setFeedback('Inserisci il cognome del richiedente.');
            surname?.focus();
            return false;
        }
        if (!email || !email.checkValidity()) {
            setFeedback('Inserisci un indirizzo email valido.');
            email?.focus();
            return false;
        }
        return true;
    }

    // default: ok
    return true;
}

// Popola il riepilogo nello step 3
function fillReview() {
    // Servizio
    const serviceSel = document.getElementById('booking-service');
    document.getElementById('review-service').textContent = serviceSel?.selectedOptions?.[0]?.textContent || '';

    // Data (full date dal bottone selezionato)
    const selectedDayBtn = document.querySelector('#calendar-grid button.btn.btn-primary');
    document.getElementById('review-date').textContent = selectedDayBtn?.dataset?.fulldate || selectedDayBtn?.dataset?.fullDate || selectedDayBtn?.dataset?.date || '';

    // Orario
    const selectedSlot = document.querySelector('.slot-time-btn.selected');
    let timeText = '';
    if (selectedSlot) {
        const t = selectedSlot.querySelector('.slot-time')?.textContent || '';
        const d = selectedSlot.querySelector('.slot-duration')?.textContent || '';
        timeText = t + (d ? ' — ' + d : '');
    }
    document.getElementById('review-time').textContent = timeText;

    // Applicant fields
    document.getElementById('review-name').textContent = document.getElementById('applicant-name')?.value || '';
    document.getElementById('review-surname').textContent = document.getElementById('applicant-surname')?.value || '';
    document.getElementById('review-email').textContent = document.getElementById('applicant-email')?.value || '';
    document.getElementById('review-phone').textContent = document.getElementById('applicant-phone')?.value || '';
    document.getElementById('review-message').textContent = document.getElementById('applicant-message')?.value || '';
}

// Override del comportamento del pulsante Avanti per includere validazioni
btnNext.addEventListener('click', function () {
    // Usa la validazione centralizzata per lo step corrente
    const ok = validateCurrentStep();
    if (!ok) return;
    goToStep(currentStep + 1);
});

// Assicuriamoci di popolare il riepilogo ogni volta che entriamo nello step 3
const originalGoToStep = goToStep;
goToStep = function (step) {
    // Esegui la logica originale
    originalGoToStep(step);

    // Se chiedono lo step 3, popoliamo il riepilogo
    if (step === 3) {
        fillReview();
    }

    // Pulisci i messaggi di feedback e aggiorna lo stato del bottone Avanti
    setFeedback('');
    updateNextButtonState();
};

/* ============================================================
   SUBMIT BOOKING
   ============================================================ */

function submitBooking() {
    const serviceId = getServiceId();
    const slotEl = document.querySelector('.slot-time-btn.selected') || document.querySelector('.slot-time-btn[data-selected="true"]');
    const slotId = slotEl ? slotEl.querySelector('input.slot-input')?.value : null;

    const nome = document.getElementById('applicant-name')?.value?.trim() || '';
    const cognome = document.getElementById('applicant-surname')?.value?.trim() || '';
    const email = document.getElementById('applicant-email')?.value?.trim() || '';
    const telefono = document.getElementById('applicant-phone')?.value?.trim() || '';
    const messaggio = document.getElementById('applicant-message')?.value?.trim() || '';

    if (!serviceId || !slotId || !nome || !cognome || !email) {
        setFeedback('Dati mancanti. Verifica tutti i campi obbligatori.');
        return;
    }

    const btnConfirm = document.querySelector('.steppers-btn-confirm');
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

    const ajaxUrl = getAjaxUrl();

    fetch(ajaxUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('[BOOKING] Prenotazione completata:', data.data);
            showBookingSuccess(data.data);
        } else {
            console.error('[BOOKING] Errore prenotazione:', data.data);
            setFeedback(data.data || 'Errore durante la prenotazione. Riprova.');
            if (btnConfirm) {
                btnConfirm.disabled = false;
                btnConfirm.innerHTML = '<svg class="icon icon-white me-1" aria-hidden="true"><use href="#it-check"></use></svg>Invia prenotazione';
            }
        }
    })
    .catch(error => {
        console.error('[BOOKING] Errore rete:', error);
        setFeedback('Errore di connessione. Riprova.');
        if (btnConfirm) {
            btnConfirm.disabled = false;
            btnConfirm.innerHTML = '<svg class="icon icon-white me-1" aria-hidden="true"><use href="#it-check"></use></svg>Invia prenotazione';
        }
    });
}

function showBookingSuccess(data) {
    const stepper = document.getElementById('booking-stepper');
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

const btnConfirm = document.querySelector('.steppers-btn-confirm');
if (btnConfirm) {
    btnConfirm.addEventListener('click', function(e) {
        e.preventDefault();
        submitBooking();
    });
}
