/* global swbAdmin */
jQuery(document).ready(function($) {
    'use strict';

    // Handler per eliminazione slot nella pagina gestione slot
    $(document).on('click', '.swb-delete-slot', function(e) {
        e.preventDefault();

        var slotId = $(this).data('slot-id');
        var $button = $(this);

        if (!slotId) {
            window.alert('❌ Errore: ID slot mancante');
            return;
        }

        if (!window.confirm('Eliminare questo slot?')) {
            return;
        }

        // Disabilita pulsante durante richiesta
        $button.prop('disabled', true).text('Eliminazione...');

        var ajaxData = {
            action: 'swb_delete_slot',
            /* eslint-disable camelcase */
            slot_id: slotId,
            /* eslint-enable camelcase */
            nonce: swbAdmin.nonce
        };

        console.log('SWB DEBUG: Invio richiesta AJAX con dati:', ajaxData);
        console.log('SWB DEBUG: URL:', swbAdmin.ajaxurl);

        $.ajax({
            url: swbAdmin.ajaxurl,
            type: 'POST',
            data: ajaxData,
            success: function(response) {
                console.log('SWB DEBUG: Risposta ricevuta:', response);
                if (response.success) {
                    window.alert('✅ Slot eliminato!');
                    window.location.reload();
                } else {
                    window.alert('❌ Errore: ' + (response.data || 'Errore sconosciuto'));
                    $button.prop('disabled', false).text('Elimina');
                }
            },
            error: function(xhr, status, error) {
                console.error('SWB DEBUG: Errore AJAX');
                console.error('SWB DEBUG: Status:', status);
                console.error('SWB DEBUG: Error:', error);
                console.error('SWB DEBUG: Response:', xhr.responseText);
                window.alert('❌ Errore di rete: ' + error);
                $button.prop('disabled', false).text('Elimina');
            }
        });
    });
});

