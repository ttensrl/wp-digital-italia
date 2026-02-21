(function($) {
    'use strict';

    function initGeocoding() {
        const indirizzoField = $('#_dci_dipartimento_indirizzo');
        const latField = $('#_dci_dipartimento_latitudine');
        const lngField = $('#_dci_dipartimento_longitudine');

        if (!indirizzoField.length) {
            return;
        }

        if (!$('#dci-geocode-btn').length) {
            indirizzoField.closest('.cmb-row').find('.cmb2-metabox-description').after(
                '<button type="button" id="dci-geocode-btn" class="button button-secondary" style="margin-top:10px;">Ottieni coordinate</button>' +
                '<span id="dci-geocode-status" style="margin-left:10px;"></span>'
            );
        }

        $('#dci-geocode-btn').off('click').on('click', function(e) {
            e.preventDefault();
            const indirizzo = indirizzoField.val().trim();

            if (!indirizzo) {
                $('#dci-geocode-status').text('Inserisci un indirizzo').css('color', 'red');
                return;
            }

            $('#dci-geocode-status').text('Ricerca in corso...').css('color', 'inherit');
            $(this).prop('disabled', true);

            $.ajax({
                url: 'https://nominatim.openstreetmap.org/search',
                type: 'GET',
                dataType: 'json',
                data: {
                    format: 'json',
                    q: indirizzo,
                    limit: 1,
                    addressdetails: 1
                },
                success: function(response) {
                    if (response && response.length > 0) {
                        var result = response[0];
                        latField.val(result.lat);
                        lngField.val(result.lon);
                        $('#dci-geocode-status').text('Coordinate trovate!').css('color', 'green');
                    } else {
                        $('#dci-geocode-status').text('Nessun risultato trovato').css('color', 'orange');
                    }
                },
                error: function() {
                    $('#dci-geocode-status').text('Errore nella ricerca').css('color', 'red');
                },
                complete: function() {
                    $('#dci-geocode-btn').prop('disabled', false);
                }
            });
        });
    }

    $(document).ready(function() {
        initGeocoding();
    });

    $(document).on('cmb2_add_row', function() {
        setTimeout(initGeocoding, 100);
    });

})(jQuery);
