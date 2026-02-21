/**
 * Simple WP Booking - Calendar JavaScript
 * Gestisce tutte le interazioni del calendario slot
 */
(function($) {
    'use strict';

    // Variabili globali inizializzate da PHP tramite wp_localize_script
    let swbServiceId = window.swbCalendar.serviceId;
    let swbUoId = window.swbCalendar.uoId;
    let swbCurrentMonth = window.swbCalendar.currentMonth;

    /**
     * Inizializzazione
     */
    $(document).ready(function() {
        initModalEvents();
        initKeyboardShortcuts();
    });

    /**
     * Inizializza eventi delle modali
     */
    function initModalEvents() {
        // Chiudi modali cliccando fuori
        $('.swb-modal').on('click', function(e) {
            if (e.target === this) {
                closeAllModals();
            }
        });
    }

    /**
     * Inizializza scorciatoie da tastiera
     */
    function initKeyboardShortcuts() {
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAllModals();
            }
        });

        // INVIO nel campo conferma elimina tutto
        $('#swbDeleteAllConfirm').on('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                swbConfirmDeleteAll();
            }
        });
    }

    /**
     * Chiudi tutte le modali
     */
    function closeAllModals() {
        $('.swb-modal').removeClass('active');
    }

    // ========================================
    // MODALE AGGIUNGI SLOT SINGOLO
    // ========================================

    window.swbAddSlot = function(date) {
        const dateObj = new Date(date + 'T00:00:00');
        const dateFormatted = dateObj.toLocaleDateString('it-IT', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        $('#swb_add_slot_date').val(date);
        $('#swb_add_slot_date_display').val(dateFormatted);

        // Reset campi
        $('#swb_add_start_time').val('09:00');
        $('#swb_add_slot_duration').val('45');
        $('#swb_add_max_bookings').val('1');

        $('#swbAddSlotModal').addClass('active');
    };

    window.swbCloseAddSlotModal = function() {
        $('#swbAddSlotModal').removeClass('active');
    };

    window.swbSubmitAddSlot = function() {
        const date = $('#swb_add_slot_date').val();
        const time = $('#swb_add_start_time').val();
        const duration = $('#swb_add_slot_duration').val();
        const maxBookings = $('#swb_add_max_bookings').val();

        if (!time) {
            alert('❌ Inserisci un orario valido');
            return;
        }

        swbCloseAddSlotModal();

        $.post(ajaxurl, {
            action: 'swb_quick_add_slot',
            service_id: swbServiceId,
            uo_id: swbUoId,
            date: date,
            time: time,
            duration: duration,
            max_bookings: maxBookings,
            nonce: swbAdmin.nonce
        }, function(response) {
            if (response.success) {
                alert('✅ Slot creato con successo!');
                location.reload();
            } else {
                alert('❌ Errore: ' + response.data);
            }
        });
    };

    // ========================================
    // MODALE MODIFICA SLOT
    // ========================================

    window.swbEditSlot = function(slotId, date, time, filterUrl) {
        $.post(ajaxurl, {
            action: 'swb_get_slot_details',
            slot_id: slotId,
            nonce: swbAdmin.nonce
        }, function(response) {
            if (response.success) {
                const slot = response.data;

                const dateObj = new Date(slot.slot_date + 'T00:00:00');
                const dateFormatted = dateObj.toLocaleDateString('it-IT', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                const startTime = new Date('2000-01-01 ' + slot.slot_start_time);
                const endTime = new Date('2000-01-01 ' + slot.slot_end_time);
                const duration = Math.round((endTime - startTime) / 60000);

                $('#swb_edit_slot_id').val(slot.id);
                $('#swb_edit_slot_date_display').val(dateFormatted);
                $('#swb_edit_start_time').val(slot.slot_start_time.substring(0, 5));
                $('#swb_edit_slot_duration').val(duration);
                $('#swb_edit_max_bookings').val(slot.max_bookings);
                $('#swb_current_bookings').text(slot.current_bookings);

                if (slot.current_bookings > 0) {
                    $('#swb_edit_bookings_info').show();
                } else {
                    $('#swb_edit_bookings_info').hide();
                }

                // Popola il link per visualizzare le prenotazioni; usa filterUrl passato dal markup
                // se presente, altrimenti prova a leggere response.data.filter_url (backend opzionale).
                const resolvedFilterUrl = filterUrl || (slot.filter_url ? slot.filter_url : '');
                if (resolvedFilterUrl) {
                    $('#swb_view_slot_bookings_link').attr('href', resolvedFilterUrl).show();
                } else {
                    $('#swb_view_slot_bookings_link').attr('href', '#').hide();
                }

                $('#swbEditSlotModal').addClass('active');
            } else {
                alert('❌ Errore caricamento dati slot: ' + response.data);
            }
        });
    };

    window.swbCloseEditSlotModal = function() {
        $('#swbEditSlotModal').removeClass('active');
    };

    window.swbSubmitEditSlot = function() {
        const slotId = $('#swb_edit_slot_id').val();
        const time = $('#swb_edit_start_time').val();
        const duration = $('#swb_edit_slot_duration').val();
        const maxBookings = $('#swb_edit_max_bookings').val();

        if (!time) {
            alert('❌ Inserisci un orario valido');
            return;
        }

        swbCloseEditSlotModal();

        $.post(ajaxurl, {
            action: 'swb_update_slot',
            slot_id: slotId,
            time: time,
            duration: duration,
            max_bookings: maxBookings,
            nonce: swbAdmin.nonce
        }, function(response) {
            if (response.success) {
                alert('✅ Slot modificato con successo!');
                location.reload();
            } else {
                alert('❌ Errore: ' + response.data);
            }
        });
    };

    // ========================================
    // MODALE GENERA SLOT
    // ========================================

    window.swbOpenModal = function(title, type) {
        $('#swbModalTitle').text(title);
        $('#swb_generate_type').val(type);

        const weekStartGroup = $('#swb_week_start_group');
        const customHoursSection = $('#swb_custom_hours_section');

        let dateStr = null;
        if (type === 'week') {
            weekStartGroup.show();
            customHoursSection.show();

            const today = new Date();
            const dayOfWeek = today.getDay();
            const targetDate = new Date(today);

            if (dayOfWeek === 0) {
                targetDate.setDate(today.getDate() + 1);
            } else if (dayOfWeek === 6) {
                targetDate.setDate(today.getDate() + 2);
            }

            dateStr = targetDate.toISOString().split('T')[0];
        } else {
            weekStartGroup.hide();
            // Per il tipo 'month' abilitiamo comunque gli orari personalizzati come nella settimana
            if (type === 'month') {
                customHoursSection.show();
            } else {
                customHoursSection.hide();
            }
        }

        // Reset form
        $('#swbGenerateForm')[0].reset();

        if (type === 'week' && dateStr) {
            $('#swb_week_start_input').val(dateStr);
        }

        $('#swb_start_time').val('09:00');
        $('#swb_end_time').val('17:00');
        $('#swb_slot_duration').val('45');
        $('#swb_max_bookings').val('1');
        $('#swb_break_start').val('12:00');
        $('#swb_break_end').val('14:00');
        $('#swb_break_fields').removeClass('active');
        $('#swb_enable_break').prop('checked', false);
        $('#swb_custom_hours_fields').removeClass('active');
        $('#swb_enable_custom_hours').prop('checked', false);

        $('#swbGenerateModal').addClass('active');
    };

    window.swbCloseModal = function() {
        $('#swbGenerateModal').removeClass('active');
    };

    window.swbToggleBreak = function() {
        const checked = $('#swb_enable_break').is(':checked');
        $('#swb_break_fields').toggleClass('active', checked);
    };

    window.swbToggleCustomHours = function() {
        const checked = $('#swb_enable_custom_hours').is(':checked');
        $('#swb_custom_hours_fields').toggleClass('active', checked);
    };

    window.swbSubmitGenerate = function() {
        const type = $('#swb_generate_type').val();
        const startTime = $('#swb_start_time').val();
        const endTime = $('#swb_end_time').val();
        const duration = $('#swb_slot_duration').val();
        const maxBookings = $('#swb_max_bookings').val();

        let breakStart = null;
        let breakEnd = null;
        if ($('#swb_enable_break').is(':checked')) {
            breakStart = $('#swb_break_start').val();
            breakEnd = $('#swb_break_end').val();
        }

        if (!startTime || !endTime || !duration || !maxBookings) {
            alert('⚠️ Compila tutti i campi obbligatori!');
            return;
        }

        let startDate, endDate;

        if (type === 'week') {
            const weekStart = $('#swb_week_start_input').val();
            if (!weekStart) {
                alert('⚠️ Seleziona la data di inizio settimana!');
                return;
            }
            startDate = weekStart;
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekEnd.getDate() + 6);
            endDate = weekEnd.toISOString().split('T')[0];
        } else {
            const parts = swbCurrentMonth.split('-');
            const year = parseInt(parts[0]);
            const month = parseInt(parts[1]);
            startDate = year + '-' + String(month).padStart(2, '0') + '-01';
            const lastDay = new Date(year, month, 0).getDate();
            endDate = year + '-' + String(month).padStart(2, '0') + '-' + lastDay;
        }

        const ajaxData = {
            action: 'swb_generate_range',
            service_id: swbServiceId,
            uo_id: swbUoId,
            start_date: startDate,
            end_date: endDate,
            start_time: startTime,
            end_time: endTime,
            slot_duration: duration,
            max_bookings: maxBookings,
            nonce: swbAdmin.nonce
        };

        // Intervallo tra slot (minuti)
        const slotInterval = $('#swb_slot_interval').val();
        ajaxData.slot_interval = slotInterval ? parseInt(slotInterval, 10) : 0;

        if (breakStart && breakEnd) {
            ajaxData.break_start = breakStart;
            ajaxData.break_end = breakEnd;
        }

        // Invia custom_hours anche per il tipo 'month' oltre che per 'week'
        if ((type === 'week' || type === 'month') && $('#swb_enable_custom_hours').is(':checked')) {
            const customHours = {};
            const days = ['mon', 'tue', 'wed', 'thu', 'fri'];

            days.forEach(function(day) {
                const start = $('#swb_' + day + '_start').val();
                const end = $('#swb_' + day + '_end').val();

                if (start && end) {
                    customHours[day] = { start: start, end: end };
                }
            });

            if (Object.keys(customHours).length > 0) {
                ajaxData.custom_hours = JSON.stringify(customHours);
            }
        }

        swbCloseModal();

        $.post(ajaxurl, ajaxData, function(response) {
            if (response.success) {
                alert('✅ Generati ' + response.data.count + ' slot!');
                location.reload();
            } else {
                alert('❌ Errore: ' + response.data);
            }
        }).fail(function() {
            alert('❌ Errore di connessione!');
        });
    };

    window.swbGenerateWeek = function() {
        swbOpenModal('Genera Settimana', 'week');
    };

    window.swbGenerateMonth = function() {
        swbOpenModal('Genera Mese Intero', 'month');
    };

    // ========================================
    // ELIMINAZIONE MASSIVA
    // ========================================

    window.swbBulkDelete = function() {
        $('#swbDeleteMonthModal').addClass('active');
    };

    window.swbCloseDeleteMonthModal = function() {
        $('#swbDeleteMonthModal').removeClass('active');
    };

    window.swbConfirmDeleteMonth = function() {
        swbCloseDeleteMonthModal();

        $.post(ajaxurl, {
            action: 'swb_bulk_delete_month',
            service_id: swbServiceId,
            uo_id: swbUoId,
            month: swbCurrentMonth,
            nonce: swbAdmin.nonce
        }, function(response) {
            if (response.success) {
                alert('✅ Eliminati ' + response.data.count + ' slot!');
                location.reload();
            } else {
                alert('❌ Errore: ' + response.data);
            }
        });
    };

    window.swbDeleteDaySlots = function(date) {
        const dateObj = new Date(date + 'T00:00:00');
        const dateFormatted = dateObj.toLocaleDateString('it-IT', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        window.swbDeleteDayDate = date;
        $('#swbDeleteDayDate').text(dateFormatted);
        $('#swbDeleteDayModal').addClass('active');
    };

    window.swbCloseDeleteDayModal = function() {
        $('#swbDeleteDayModal').removeClass('active');
    };

    window.swbConfirmDeleteDay = function() {
        swbCloseDeleteDayModal();

        $.post(ajaxurl, {
            action: 'swb_delete_day_slots',
            service_id: swbServiceId,
            uo_id: swbUoId,
            date: window.swbDeleteDayDate,
            nonce: swbAdmin.nonce
        }, function(response) {
            if (response.success) {
                let msg = '✅ Eliminati ' + response.data.count + ' slot';
                if (response.data.bookings > 0) {
                    msg += ' con ' + response.data.bookings + ' prenotazioni';
                }
                alert(msg + '!');
                location.reload();
            } else {
                alert('❌ Errore: ' + response.data);
            }
        });
    };

    window.swbDeleteAllSlots = function() {
        $('#swbDeleteAllConfirm').val('');
        $('#swbDeleteAllModal').addClass('active');

        setTimeout(function() {
            $('#swbDeleteAllConfirm').focus();
        }, 300);
    };

    window.swbCloseDeleteAllModal = function() {
        $('#swbDeleteAllModal').removeClass('active');
        $('#swbDeleteAllConfirm').val('');
    };

    window.swbConfirmDeleteAll = function() {
        const conferma = $('#swbDeleteAllConfirm').val();

        if (conferma !== 'ELIMINA TUTTO') {
            alert('❌ Operazione annullata. Devi digitare esattamente: ELIMINA TUTTO');
            return;
        }

        swbCloseDeleteAllModal();

        $.post(ajaxurl, {
            action: 'swb_delete_all_service_slots',
            service_id: swbServiceId,
            uo_id: swbUoId,
            nonce: swbAdmin.nonce
        }, function(response) {
            if (response.success) {
                alert('✅ Eliminati ' + response.data.count + ' slot e ' + response.data.bookings + ' prenotazioni!');
                location.reload();
            } else {
                alert('❌ Errore: ' + response.data);
            }
        });
    };

    // ========================================
    // GESTIONE CLICK SLOT
    // ========================================

    // Prima: la UI usava un doppio click per aprire la lista prenotazioni; lo rimuoviamo.
    // Ora un singolo click apre sempre la modale di modifica; nella modale verrà mostrato
    // un link "Visualizza prenotazioni" quando disponibile.
    window.swbHandleSlotClick = function(element, slotId, date, time) {
        const filterUrl = element.getAttribute('data-filter-url') || '';
        swbEditSlot(slotId, date, time, filterUrl);
    };

})(jQuery);
