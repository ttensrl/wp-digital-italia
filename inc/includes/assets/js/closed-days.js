/**
 * Simple WP Booking - Closed Days JavaScript
 * Gestisce l'interfaccia per i giorni di chiusura globali
 */

(function($) {
    'use strict';

    // Variabili globali inizializzate da PHP
    let swbCurrentMonth = window.swbClosedDays ? window.swbClosedDays.currentMonth : '';

    /**
     * Inizializzazione
     */
    $(document).ready(function() {
        initClosedDays();
    });

    /**
     * Inizializza la pagina giorni chiusi
     */
    function initClosedDays() {
        if ($('#swbClosedDaysCalendar').length === 0) {
            return; // Non siamo nella pagina corretta
        }

        // Carica lista iniziale
        listClosedDays();

        // Eventi
        $('#swbAddClosedDayBtn').on('click', openAddClosedDayModal);
        $(document).on('click', '.swb-remove-closed', removeClosedDay);
        $(document).on('click', '.swb-add-day', addClosedDayFromCalendar);
        $(document).on('click', '.swb-remove-day', removeClosedDayFromCalendar);
    }

    /**
     * Carica e renderizza la lista dei giorni chiusi
     */
    function listClosedDays() {
        const $list = $('#swbClosedDaysList');
        if ($list.length === 0) {
            // Elemento rimoss o non presente: non tentare di aggiornare
            return;
        }

        $list.html('<p>Caricamento...</p>');

        $.get(swbAdmin.ajaxurl, {
            action: 'swb_list_closed_days',
            month: swbCurrentMonth,
            nonce: swbAdmin.nonce
        }, function(response) {
            if (response.success) {
                renderClosedDaysList(response.data);
            } else {
                $list.html(
                    '<div class="notice notice-error">' +
                    (response.data || 'Errore caricamento') +
                    '</div>'
                );
            }
        }).fail(function() {
            $list.html(
                '<div class="notice notice-error">Errore di connessione</div>'
            );
        });
    }

    /**
     * Renderizza la tabella con la lista dei giorni chiusi
     */
    function renderClosedDaysList(items) {
        const $list = $('#swbClosedDaysList');
        if ($list.length === 0) return; // niente da fare se il container non esiste

        if (!items || items.length === 0) {
            // Non mostrare messaggio quando la lista è vuota (come richiesto)
            $list.html('');
            return;
        }

        let html = '<table class="widefat">' +
            '<thead>' +
            '<tr>' +
            '<th>Data</th>' +
            '<th>Ricorrente</th>' +
            '<th>Motivo</th>' +
            '<th>Azioni</th>' +
            '</tr>' +
            '</thead>' +
            '<tbody>';

        items.forEach(function(item) {
            html += '<tr>' +
                '<td>' + item.closed_date + '</td>' +
                '<td>' + (item.is_recurring ? 'Sì (settimanale)' : 'No') + '</td>' +
                '<td>' + (item.reason ? item.reason : '') + '</td>' +
                '<td>' +
                '<button data-id="' + item.id + '" class="button swb-remove-closed">Rimuovi</button>' +
                '</td>' +
                '</tr>';
        });

        html += '</tbody></table>';
        $list.html(html);
    }

    /**
     * Apre la modale per aggiungere un giorno chiuso
     */
    function openAddClosedDayModal(e) {
        e.preventDefault();

        const modalHtml = '<div id="swbAddClosedModal" class="swb-modal-overlay">' +
            '<div class="swb-modal" style="max-width:500px">' +
            '<div class="swb-modal-header">' +
            '<h2>Aggiungi Giorno Chiuso</h2>' +
            '<button class="swb-modal-close">&times;</button>' +
            '</div>' +
            '<div class="swb-modal-body">' +
            '<p><label>Data: <input type="date" id="swb_closed_date"></label></p>' +
            '<p>' +
            '<label>' +
            '<input type="checkbox" id="swb_closed_recurring"> ' +
            'Ricorrente settimanale' +
            '</label>' +
            '</p>' +
            '<p id="swb_weekday_select" style="display:none;">' +
            '<label>Giorno della settimana: ' +
            '<select id="swb_weekday">' +
            '<option value="1">Lunedì</option>' +
            '<option value="2">Martedì</option>' +
            '<option value="3">Mercoledì</option>' +
            '<option value="4">Giovedì</option>' +
            '<option value="5">Venerdì</option>' +
            '<option value="6">Sabato</option>' +
            '<option value="7">Domenica</option>' +
            '</select>' +
            '</label>' +
            '</p>' +
            '<p>' +
            '<label>Motivo (opzionale): ' +
            '<input type="text" id="swb_closed_reason" style="width:100%">' +
            '</label>' +
            '</p>' +
            '</div>' +
            '<div class="swb-modal-footer">' +
            '<button class="button swb-cancel">Annulla</button>' +
            '<button class="button button-primary swb-save-closed">Aggiungi</button>' +
            '</div>' +
            '</div>' +
            '</div>';

        $('body').append(modalHtml);

        // Eventi modale
        $('.swb-modal-close, .swb-cancel').on('click', closeAddClosedDayModal);
        $('#swb_closed_recurring').on('change', toggleRecurringFields);
        $('.swb-save-closed').on('click', saveClosedDay);
    }

    /**
     * Chiude la modale di aggiunta
     */
    function closeAddClosedDayModal() {
        $('#swbAddClosedModal').remove();
    }

    /**
     * Mostra/nasconde i campi per ricorrenza settimanale
     */
    function toggleRecurringFields() {
        const isRecurring = $('#swb_closed_recurring').is(':checked');
        $('#swb_weekday_select').toggle(isRecurring);
    }

    /**
     * Salva un nuovo giorno chiuso
     */
    function saveClosedDay() {
        const closedDate = $('#swb_closed_date').val();
        const isRecurring = $('#swb_closed_recurring').is(':checked') ? 1 : 0;
        const weekday = $('#swb_weekday').val();
        const reason = $('#swb_closed_reason').val();

        // Validazione
        if (isRecurring && (!weekday || weekday < 1)) {
            alert('Seleziona un giorno della settimana');
            return;
        }

        if (!isRecurring && !closedDate) {
            alert('Seleziona una data');
            return;
        }

        $.post(swbAdmin.ajaxurl, {
            action: 'swb_add_closed_day',
            nonce: swbAdmin.nonce,
            closed_date: closedDate,
            is_recurring: isRecurring,
            weekday: weekday,
            reason: reason
        }, function(response) {
            if (response.success) {
                closeAddClosedDayModal();
                listClosedDays();
                // Ricarica pagina per aggiornare calendario
                location.reload();
            } else {
                alert(response.data || 'Errore durante il salvataggio');
            }
        }).fail(function() {
            alert('Errore di connessione');
        });
    }

    /**
     * Rimuove un giorno chiuso dalla lista
     */
    function removeClosedDay(e) {
        e.preventDefault();

        const id = $(this).data('id');
        if (!confirm('Sei sicuro di rimuovere questa chiusura?')) {
            return;
        }

        $.post(swbAdmin.ajaxurl, {
            action: 'swb_remove_closed_day',
            nonce: swbAdmin.nonce,
            id: id
        }, function(response) {
            if (response.success) {
                listClosedDays();
                // Ricarica pagina per aggiornare calendario
                location.reload();
            } else {
                alert(response.data || 'Errore durante la rimozione');
            }
        }).fail(function() {
            alert('Errore di connessione');
        });
    }

    /**
     * Aggiunge una chiusura cliccando sul + nel calendario
     */
    function addClosedDayFromCalendar(e) {
        e.preventDefault();

        const date = $(this).data('date');
        if (!confirm('Aggiungere una chiusura per ' + date + '?')) {
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true);

        $.post(swbAdmin.ajaxurl, {
            action: 'swb_add_closed_day',
            nonce: swbAdmin.nonce,
            closed_date: date,
            is_recurring: 0,
            reason: ''
        }, function(response) {
            btn.prop('disabled', false);

            if (response.success) {
                // Aggiorna cella: trasforma + in - e aggiungi badge
                const cell = $('[data-date="' + date + '"]');
                cell.addClass('closed');
                cell.find('.swb-close-toggle').remove();
                cell.append('<div class="swb-closed-badge">Chiuso</div>');
                cell.append(
                    '<button class="swb-close-toggle swb-remove-day" ' +
                    'data-date="' + date + '" ' +
                    'title="Rimuovi chiusura">' +
                    '<span class="dashicons dashicons-dismiss"></span>' +
                    '</button>'
                );
                listClosedDays();
            } else {
                alert(response.data || 'Errore aggiunta chiusura');
            }
        }).fail(function() {
            btn.prop('disabled', false);
            alert('Errore di connessione');
        });
    }

    /**
     * Rimuove una chiusura cliccando sul - nel calendario
     */
    function removeClosedDayFromCalendar(e) {
        e.preventDefault();

        const date = $(this).data('date');
        if (!confirm('Rimuovere tutte le chiusure per ' + date +
            ' (includerà anche ricorrenze settimanali con lo stesso giorno)?')) {
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true);

        $.post(swbAdmin.ajaxurl, {
            action: 'swb_remove_closed_by_date',
            nonce: swbAdmin.nonce,
            date: date
        }, function(response) {
            btn.prop('disabled', false);

            if (response.success) {
                // Aggiorna cella: trasforma - in + e rimuovi badge
                const cell = $('[data-date="' + date + '"]');
                cell.removeClass('closed');
                cell.find('.swb-closed-badge, .swb-closed-reason, .swb-close-toggle').remove();
                cell.append(
                    '<button class="swb-close-toggle swb-add-day" ' +
                    'data-date="' + date + '" ' +
                    'title="Aggiungi chiusura">' +
                    '<span class="dashicons dashicons-plus"></span>' +
                    '</button>'
                );
                listClosedDays();
            } else {
                alert(response.data || 'Errore rimozione chiusura');
            }
        }).fail(function() {
            btn.prop('disabled', false);
            alert('Errore di connessione');
        });
    }

})(jQuery);

