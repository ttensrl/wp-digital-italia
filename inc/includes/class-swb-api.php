<?php
/**
 * Gestisce le API per gli slot
 */

if (!defined('ABSPATH')) {
    exit;
}

class SWB_API {

    private $slot_manager;

    public function __construct() {
        $this->slot_manager = new SWB_Slot_Manager();
    }

    public function init() {
        // AJAX endpoints
        add_action('wp_ajax_swb_get_slots', array($this, 'ajax_get_slots'));
        add_action('wp_ajax_nopriv_swb_get_slots', array($this, 'ajax_get_slots'));

        add_action('wp_ajax_swb_get_service_uo', array($this, 'ajax_get_service_uo'));

        add_action('wp_ajax_swb_delete_slot', array($this, 'ajax_delete_slot'));
        add_action('wp_ajax_swb_get_slot_details', array($this, 'ajax_get_slot_details'));
        add_action('wp_ajax_swb_update_slot', array($this, 'ajax_update_slot'));

        add_action('wp_ajax_swb_quick_add_slot', array($this, 'ajax_quick_add_slot'));
        add_action('wp_ajax_swb_generate_range', array($this, 'ajax_generate_range'));
        add_action('wp_ajax_swb_bulk_delete_month', array($this, 'ajax_bulk_delete_month'));
        add_action('wp_ajax_swb_delete_day_slots', array($this, 'ajax_delete_day_slots'));
        add_action('wp_ajax_swb_delete_all_service_slots', array($this, 'ajax_delete_all_service_slots'));

        // Nuovi endpoint per giorni di chiusura
        add_action('wp_ajax_swb_add_closed_day', array($this, 'ajax_add_closed_day'));
        add_action('wp_ajax_swb_remove_closed_day', array($this, 'ajax_remove_closed_day'));
        add_action('wp_ajax_swb_list_closed_days', array($this, 'ajax_list_closed_days'));
        add_action('wp_ajax_swb_remove_closed_by_date', array($this, 'ajax_remove_closed_by_date'));

        // Endpoint per il frontend di prenotazione
        add_action('wp_ajax_get_available_appointments', array($this, 'ajax_get_available_appointments'));
        add_action('wp_ajax_nopriv_get_available_appointments', array($this, 'ajax_get_available_appointments'));

        // NOTA: Hook save_appuntamento gestito dal tema child (booking-handler.php)
        // per evitare conflitti con la logica del tema principale
    }

    /**
     * AJAX: Ottiene slot disponibili per mese
     */
    public function ajax_get_slots() {
        $month = isset($_GET['month']) ? sanitize_text_field($_GET['month']) : '';
        $service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;
        $uo_id = isset($_GET['uo_id']) ? intval($_GET['uo_id']) : 0;

        if (empty($month) || !$service_id || !$uo_id) {
            wp_send_json_error('Parametri mancanti');
            return;
        }

        // Parsing YYYY-MM
        $parts = explode('-', $month);
        if (count($parts) !== 2) {
            wp_send_json_error('Formato mese non valido');
            return;
        }

        $year = intval($parts[0]);
        $month_num = intval($parts[1]);

        // Ottieni slot dal database
        $slots = $this->slot_manager->get_slots_by_month($service_id, $uo_id, $year, $month_num);

        // Formatta per il frontend
        $formatted_slots = array();
        foreach ($slots as $slot) {
            $formatted_slots[] = array(
                'id' => $slot->id,
                'startDate' => $slot->slot_date . 'T' . substr($slot->slot_start_time, 0, 5),
                'endDate' => $slot->slot_date . 'T' . substr($slot->slot_end_time, 0, 5),
                'available' => ($slot->max_bookings - $slot->current_bookings)
            );
        }

        wp_send_json_success($formatted_slots);
    }

    /**
     * AJAX: Ottiene slot disponibili per il frontend di prenotazione
     * Azione: get_available_appointments
     */
    public function ajax_get_available_appointments() {
        $service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;
        $month = isset($_GET['month']) ? sanitize_text_field($_GET['month']) : '';
        $day = isset($_GET['day']) ? intval($_GET['day']) : 0;

        if (!$service_id) {
            wp_send_json_error('Service ID mancante');
            return;
        }

        // Se non sono specificati mese e giorno, restituisci i mesi disponibili
        if (empty($month) && empty($day)) {
            $this->get_available_months($service_id);
            return;
        }

        // Se è specificato solo il mese, restituisci i giorni disponibili
        if (!empty($month) && empty($day)) {
            $this->get_available_days($service_id, $month);
            return;
        }

        // Se sono specificati mese e giorno, restituisci gli slot orari disponibili
        if (!empty($month) && !empty($day)) {
            $this->get_available_slots($service_id, $month, $day);
            return;
        }

        wp_send_json_error('Parametri non validi');
    }

    /**
     * Ottiene i mesi disponibili per un servizio
     */
    private function get_available_months($service_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_slots';

        // Prova a determinare l'UO del servizio (se configurata)
        $canale_fisico = dci_get_meta("canale_fisico_uffici", '_dci_servizio_', $service_id);
        if (!empty($canale_fisico) && is_array($canale_fisico)) {
            $uo_id = intval($canale_fisico[0]);
        } else {
            $uo_id = intval(dci_get_meta("unita_responsabile", '_dci_servizio_', $service_id));
        }

        $today = date('Y-m-d');

        // Costruisci la query per ottenere YEAR e MONTH distinti che hanno slot disponibili
        if ($uo_id) {
            $sql = $wpdb->prepare(
                "SELECT DISTINCT YEAR(slot_date) as year, MONTH(slot_date) as month
                FROM {$table_name}
                WHERE service_id = %d
                AND uo_id = %d
                AND is_active = 1
                AND current_bookings < max_bookings
                AND slot_date >= %s
                ORDER BY year ASC, month ASC",
                $service_id,
                $uo_id,
                $today
            );
        } else {
            $sql = $wpdb->prepare(
                "SELECT DISTINCT YEAR(slot_date) as year, MONTH(slot_date) as month
                FROM {$table_name}
                WHERE service_id = %d
                AND is_active = 1
                AND current_bookings < max_bookings
                AND slot_date >= %s
                ORDER BY year ASC, month ASC",
                $service_id,
                $today
            );
        }

        $rows = $wpdb->get_results($sql);

        $months = array();
        if (!empty($rows)) {
            foreach ($rows as $r) {
                $y = intval($r->year);
                $m = intval($r->month);
                $months[] = array(
                    'value' => $m,
                    'year' => $y,
                    'label' => date_i18n('F Y', mktime(0, 0, 0, $m, 1, $y))
                );
            }

            wp_send_json_success(array('months' => $months));
            return;
        }

        // Se non troviamo mesi nel DB, restituiamo un array vuoto (il frontend deciderà cosa fare)
        wp_send_json_success(array('months' => array()));
    }

    /**
     * Ottiene i giorni disponibili per un servizio/mese
     */
    private function get_available_days($service_id, $month) {
        // Parsing YYYY-MM
        $parts = explode('-', $month);
        if (count($parts) !== 2) {
            wp_send_json_error('Formato mese non valido');
            return;
        }

        $year = intval($parts[0]);
        $month_num = intval($parts[1]);

        // Ottieni i giorni con slot disponibili dal database
        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_slots';

        $days = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT DAY(slot_date) as day, slot_date 
            FROM $table_name 
            WHERE service_id = %d 
            AND YEAR(slot_date) = %d 
            AND MONTH(slot_date) = %d 
            AND is_active = 1 
            AND current_bookings < max_bookings 
            ORDER BY slot_date ASC",
            $service_id,
            $year,
            $month_num
        ));

        if (empty($days)) {
            wp_send_json_success(array('days' => array()));
            return;
        }

        $formatted_days = array();
        foreach ($days as $day_obj) {
            $formatted_days[] = array(
                'day' => intval($day_obj->day),
                'date' => $day_obj->slot_date,
                'label' => intval($day_obj->day) . ' ' . date_i18n('M', mktime(0, 0, 0, $month_num, 1, $year))
            );
        }

        wp_send_json_success(array('days' => $formatted_days));
    }

    /**
     * Ottiene gli slot orari disponibili per un servizio/mese/giorno
     */
    private function get_available_slots($service_id, $month, $day) {
        // Parsing YYYY-MM
        $parts = explode('-', $month);
        if (count($parts) !== 2) {
            wp_send_json_error('Formato mese non valido');
            return;
        }

        $year = intval($parts[0]);
        $month_num = intval($parts[1]);
        $date_str = sprintf('%04d-%02d-%02d', $year, $month_num, $day);

        // Ottieni gli slot disponibili dal database
        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_slots';

        $slots = $wpdb->get_results($wpdb->prepare(
            "SELECT id, slot_start_time, slot_end_time, max_bookings, current_bookings 
            FROM $table_name 
            WHERE service_id = %d 
            AND slot_date = %s 
            AND is_active = 1 
            AND current_bookings < max_bookings 
            ORDER BY slot_start_time ASC",
            $service_id,
            $date_str
        ));

        if (empty($slots)) {
            wp_send_json_success(array('slots' => array()));
            return;
        }

        $formatted_slots = array();
        foreach ($slots as $slot) {
            $formatted_slots[] = array(
                'id' => $slot->id,
                'start_time' => substr($slot->slot_start_time, 0, 5),
                'end_time' => substr($slot->slot_end_time, 0, 5),
                'available' => intval($slot->max_bookings) - intval($slot->current_bookings),
                'max_bookings' => intval($slot->max_bookings)
            );
        }

        wp_send_json_success(array('slots' => $formatted_slots));
    }

    /**
     * AJAX: Ottiene UO di un servizio
     */
    public function ajax_get_service_uo() {
        $service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;

        if (!$service_id) {
            wp_send_json_error('Service ID mancante');
            return;
        }

        // Ottieni UO
        $canale_fisico = dci_get_meta("canale_fisico_uffici", '_dci_servizio_', $service_id);
        if (!empty($canale_fisico) && is_array($canale_fisico)) {
            $uo_id = intval($canale_fisico[0]);
        } else {
            $uo_id = intval(dci_get_meta("unita_responsabile", '_dci_servizio_', $service_id));
        }

        if (!$uo_id) {
            wp_send_json_error('Nessuna UO trovata');
            return;
        }

        wp_send_json_success(array(
            'id' => $uo_id,
            'title' => get_the_title($uo_id)
        ));
    }

    /**
     * AJAX: Elimina slot
     */
    public function ajax_delete_slot() {
        error_log('SWB DEBUG: ajax_delete_slot chiamata');
        error_log('SWB DEBUG: POST data: ' . print_r($_POST, true));

        check_ajax_referer('swb-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            error_log('SWB DEBUG: Permessi insufficienti');
            wp_send_json_error('Permessi insufficienti');
            return;
        }

        $slot_id = isset($_POST['slot_id']) ? intval($_POST['slot_id']) : 0;
        error_log('SWB DEBUG: Slot ID ricevuto: ' . $slot_id);

        if (!$slot_id) {
            error_log('SWB DEBUG: Slot ID mancante o zero');
            wp_send_json_error('Slot ID mancante');
            return;
        }

        $result = $this->slot_manager->delete_slot($slot_id);
        error_log('SWB DEBUG: Risultato delete_slot: ' . var_export($result, true));

        if ($result) {
            error_log('SWB DEBUG: Slot eliminato con successo, ID: ' . $slot_id);
            wp_send_json_success('Slot eliminato');
        } else {
            error_log('SWB DEBUG: Errore eliminazione slot, ID: ' . $slot_id);
            wp_send_json_error('Errore eliminazione slot');
        }
    }

    /**
     * AJAX: Ottiene dettagli di uno slot
     */
    public function ajax_get_slot_details() {
        check_ajax_referer('swb-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
            return;
        }

        $slot_id = isset($_POST['slot_id']) ? intval($_POST['slot_id']) : 0;

        if (!$slot_id) {
            wp_send_json_error('Slot ID mancante');
            return;
        }

        $slot = $this->slot_manager->get_slot($slot_id);

        if ($slot) {
            // Calcola filter_url per aprire la lista appuntamenti filtrata (utile se il markup non lo contiene)
            $filter_url = '';
            if (!empty($slot->current_bookings) && $slot->current_bookings > 0) {
                $start = substr($slot->slot_start_time, 0, 5);
                $filter_url = add_query_arg(array(
                    'post_type' => 'appuntamento',
                    'swb_filter_slot' => $slot->id,
                    'swb_filter_date' => $slot->slot_date,
                    'swb_filter_time' => $start
                ), admin_url('edit.php'));
            }

            // Converti l'oggetto slot in array e aggiungi filter_url
            $slot_array = (array) $slot;
            $slot_array['filter_url'] = $filter_url;

            wp_send_json_success($slot_array);
        } else {
            wp_send_json_error('Slot non trovato');
        }
    }

    /**
     * AJAX: Aggiorna uno slot esistente
     */
    public function ajax_update_slot() {
        check_ajax_referer('swb-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
            return;
        }

        $slot_id = isset($_POST['slot_id']) ? intval($_POST['slot_id']) : 0;
        $time = isset($_POST['time']) ? sanitize_text_field($_POST['time']) : '';
        $duration = isset($_POST['duration']) ? intval($_POST['duration']) : 0;
        $max_bookings = isset($_POST['max_bookings']) ? intval($_POST['max_bookings']) : 0;

        if (!$slot_id || !$time || !$duration || !$max_bookings) {
            wp_send_json_error('Parametri mancanti');
            return;
        }

        // Ottieni lo slot esistente per avere la data, service_id e uo_id
        $slot = $this->slot_manager->get_slot($slot_id);
        if (!$slot) {
            wp_send_json_error('Slot non trovato');
            return;
        }

        // Calcola ora fine in base alla durata
        $start = new DateTime($slot->slot_date . ' ' . $time);
        $end = clone $start;
        $end->modify('+' . $duration . ' minutes');

        // Verifica se esiste già uno slot che si sovrappone con questo orario (escluso lo slot corrente)
        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_slots';

        $overlapping_slot = $wpdb->get_row($wpdb->prepare(
            "SELECT id, slot_start_time, slot_end_time FROM $table_name 
            WHERE service_id = %d 
            AND uo_id = %d 
            AND slot_date = %s 
            AND is_active = 1
            AND id != %d
            AND (
                (slot_start_time < %s AND slot_end_time > %s)
                OR (slot_start_time < %s AND slot_end_time > %s)
                OR (slot_start_time >= %s AND slot_end_time <= %s)
            )",
            $slot->service_id,
            $slot->uo_id,
            $slot->slot_date,
            $slot_id,  // Escludi lo slot corrente dal controllo
            // Il nuovo slot inizia prima che finisca quello esistente
            $end->format('H:i:s'),
            $start->format('H:i:s'),
            // Il nuovo slot finisce dopo che inizi quello esistente
            $end->format('H:i:s'),
            $start->format('H:i:s'),
            // Uno slot esistente è completamente contenuto nel nuovo
            $start->format('H:i:s'),
            $end->format('H:i:s')
        ));

        if ($overlapping_slot) {
            $existing_start = substr($overlapping_slot->slot_start_time, 0, 5);
            $existing_end = substr($overlapping_slot->slot_end_time, 0, 5);
            wp_send_json_error(sprintf(
                'Esiste già uno slot attivo che si sovrappone con questo orario (%s - %s). Scegli un orario diverso o modifica quello esistente.',
                $existing_start,
                $existing_end
            ));
            return;
        }

        // Aggiorna lo slot nel database
        $result = $wpdb->update(
            $table_name,
            array(
                'slot_start_time' => $start->format('H:i:s'),
                'slot_end_time' => $end->format('H:i:s'),
                'max_bookings' => $max_bookings
            ),
            array('id' => $slot_id),
            array('%s', '%s', '%d'),
            array('%d')
        );

        if ($result !== false) {
            wp_send_json_success('Slot aggiornato');
        } else {
            wp_send_json_error('Errore aggiornamento slot');
        }
    }

    /**
     * Gestisce la prenotazione quando viene creato un appuntamento
     * Hook con priorità 1 per eseguire PRIMA del salvataggio standard
     */
    public function handle_booking() {
        error_log('SWB: handle_booking chiamato');
        error_log('SWB: POST data: ' . print_r($_POST, true));

        // Verifica se l'appuntamento ha uno slot associato
        if (!isset($_POST['slot_id']) || empty($_POST['slot_id'])) {
            error_log('SWB: Nessuno slot_id trovato, skippo gestione booking');
            // Nessuno slot, lascia che il tema gestisca normalmente
            return;
        }

        error_log('SWB: Slot ID trovato: ' . $_POST['slot_id']);

        $slot_id = intval($_POST['slot_id']);
        $user_email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $user_name = isset($_POST['surname']) && isset($_POST['name'])
            ? sanitize_text_field($_POST['surname'] . ' ' . $_POST['name'])
            : '';
        $user_cf = isset($_POST['cf']) ? sanitize_text_field($_POST['cf']) : null;

        error_log('SWB: User email: ' . $user_email);
        error_log('SWB: User name: ' . $user_name);

        // Prima crea l'appuntamento (lascia che il tema lo faccia)
        // poi prenota lo slot

        // Salviamo i dati per usarli dopo
        $transient_data = array(
            'slot_id' => $slot_id,
            'user_email' => $user_email,
            'user_name' => $user_name,
            'user_cf' => $user_cf
        );

        $transient_key = 'swb_pending_booking_' . $slot_id;

        error_log('SWB: Salvo transient: ' . $transient_key);
        error_log('SWB: Dati transient: ' . print_r($transient_data, true));

        set_transient($transient_key, $transient_data, 300); // 5 minuti

        error_log('SWB: Transient salvato, verifico...');
        $check_transient = get_transient($transient_key);
        error_log('SWB: Transient verificato: ' . print_r($check_transient, true));

        error_log('SWB: Hook wp_insert_post già registrato in init(), lascio eseguire il tema');
    }

    /**
     * Completa la prenotazione dopo che l'appuntamento è stato creato
     */
    public function complete_booking($post_id, $post) {
        error_log('SWB: complete_booking chiamato - Post ID: ' . $post_id . ', Post Type: ' . $post->post_type);

        if ($post->post_type !== 'appuntamento') {
            error_log('SWB: Post type non è appuntamento, skippo');
            return;
        }

        error_log('SWB: È un appuntamento, cerco transient con dati prenotazione');

        // Cerca transient con dati prenotazione
        global $wpdb;
        $transients = $wpdb->get_results(
            "SELECT option_name, option_value FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_swb_pending_booking_%'"
        );

        error_log('SWB: Trovati ' . count($transients) . ' transient pendenti');

        foreach ($transients as $transient) {
            error_log('SWB: Elaboro transient: ' . $transient->option_name);

            $data = maybe_unserialize($transient->option_value);
            if (!$data) {
                error_log('SWB: Dati transient non validi');
                continue;
            }

            error_log('SWB: Dati transient: ' . print_r($data, true));

            // Prenota lo slot
            error_log('SWB: Prenoto slot ' . $data['slot_id'] . ' per appuntamento ' . $post_id);

            $booking_id = $this->slot_manager->book_slot(
                $data['slot_id'],
                $post_id,
                $data['user_email'],
                $data['user_name'],
                $data['user_cf']
            );

            error_log('SWB: Risultato book_slot: ' . var_export($booking_id, true));

            if ($booking_id) {
                // Salva riferimento nello appuntamento
                update_post_meta($post_id, '_swb_booking_id', $booking_id);
                update_post_meta($post_id, '_swb_slot_id', $data['slot_id']);

                error_log('SWB: Prenotazione completata - Booking ID: ' . $booking_id . ', Appuntamento ID: ' . $post_id);

                // Invia email di conferma
                error_log('SWB: Invio email di conferma...');
                $email_sent = $this->send_confirmation_email($post_id, $data);
                error_log('SWB: Email inviata: ' . ($email_sent ? 'SI' : 'NO'));
            } else {
                error_log('SWB: ERRORE: book_slot ha fallito!');
            }

            // Elimina transient
            $key = str_replace('_transient_', '', $transient->option_name);
            delete_transient($key);
            error_log('SWB: Transient eliminato: ' . $key);

            break; // Gestisci solo il primo
        }

        error_log('SWB: complete_booking terminato');
    }

    /**
     * Invia email di conferma all'utente
     */
    private function send_confirmation_email($appuntamento_id, $booking_data) {
        error_log('SWB EMAIL: send_confirmation_email chiamato');
        error_log('SWB EMAIL: Appuntamento ID: ' . $appuntamento_id);
        error_log('SWB EMAIL: Booking data: ' . print_r($booking_data, true));

        $user_email = $booking_data['user_email'];
        $user_name = $booking_data['user_name'];
        $slot_id = $booking_data['slot_id'];

        if (empty($user_email) || !is_email($user_email)) {
            error_log('SWB EMAIL: Email non valida per invio conferma: ' . $user_email);
            return false;
        }

        error_log('SWB EMAIL: Email valida: ' . $user_email);

        // Ottieni dettagli slot
        $slot = $this->slot_manager->get_slot($slot_id);
        if (!$slot) {
            error_log('SWB EMAIL: Slot non trovato per email conferma: ' . $slot_id);
            return false;
        }

        error_log('SWB EMAIL: Slot trovato: ' . print_r($slot, true));

        // Formatta data e ora
        $data_appuntamento = date_i18n('l d F Y', strtotime($slot->slot_date));
        $ora_inizio = substr($slot->slot_start_time, 0, 5);
        $ora_fine = substr($slot->slot_end_time, 0, 5);

        // Ottieni nome servizio e UO
        $servizio_nome = get_the_title($slot->service_id);
        $uo_nome = get_the_title($slot->uo_id);

        error_log('SWB EMAIL: Data: ' . $data_appuntamento);
        error_log('SWB EMAIL: Orario: ' . $ora_inizio . ' - ' . $ora_fine);
        error_log('SWB EMAIL: Servizio: ' . $servizio_nome);
        error_log('SWB EMAIL: UO: ' . $uo_nome);

        // Nome del sito
        $site_name = get_bloginfo('name');

        // Costruisci email
        $subject = sprintf('Conferma appuntamento - %s', $site_name);

        $message = sprintf(
            "Gentile %s,\n\n" .
            "La sua prenotazione è stata confermata con successo.\n\n" .
            "DETTAGLI APPUNTAMENTO:\n" .
            "------------------------\n" .
            "Data: %s\n" .
            "Orario: dalle %s alle %s\n" .
            "Servizio: %s\n" .
            "Ufficio: %s\n\n" .
            "------------------------\n\n" .
            "La preghiamo di presentarsi con un documento di identità valido.\n\n" .
            "Per qualsiasi informazione può contattare l'ufficio di riferimento.\n\n" .
            "Cordiali saluti,\n" .
            "%s",
            $user_name,
            $data_appuntamento,
            $ora_inizio,
            $ora_fine,
            $servizio_nome,
            $uo_nome,
            $site_name
        );

        error_log('SWB EMAIL: Subject: ' . $subject);
        error_log('SWB EMAIL: Message length: ' . strlen($message));

        // Imposta headers per email
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $site_name . ' <' . get_option('admin_email') . '>'
        );

        error_log('SWB EMAIL: Headers: ' . print_r($headers, true));
        error_log('SWB EMAIL: Invio email a: ' . $user_email);

        // Invia email
        $sent = wp_mail($user_email, $subject, $message, $headers);

        if ($sent) {
            error_log('SWB EMAIL: ✓ Email di conferma inviata con successo a ' . $user_email);
        } else {
            error_log('SWB EMAIL: ✗ ERRORE invio email di conferma a ' . $user_email);

            // Debug wp_mail
            global $phpmailer;
            if (isset($phpmailer)) {
                error_log('SWB EMAIL: PHPMailer error: ' . $phpmailer->ErrorInfo);
            }
        }

        return $sent;
    }

    /**
     * AJAX: Aggiunge uno slot rapidamente
     */
    public function ajax_quick_add_slot() {
        check_ajax_referer('swb-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
            return;
        }

        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        $uo_id = isset($_POST['uo_id']) ? intval($_POST['uo_id']) : 0;
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
        $time = isset($_POST['time']) ? sanitize_text_field($_POST['time']) : '';
        $duration = isset($_POST['duration']) ? intval($_POST['duration']) : 45;
        $max_bookings = isset($_POST['max_bookings']) ? intval($_POST['max_bookings']) : 1;

        if (!$service_id || !$uo_id || !$date || !$time) {
            wp_send_json_error('Parametri mancanti');
            return;
        }

        // Calcola ora fine in base alla durata
        $start = new DateTime($date . ' ' . $time);
        $end = clone $start;
        $end->modify('+' . $duration . ' minutes');

        // Verifica se esiste già uno slot che si sovrappone con questo orario
        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_slots';

        // Controlla sovrapposizione: nuovo slot inizia prima che quello esistente finisca
        // E nuovo slot finisce dopo che quello esistente inizia
        $overlapping_slot = $wpdb->get_row($wpdb->prepare(
            "SELECT id, slot_start_time, slot_end_time FROM $table_name 
            WHERE service_id = %d 
            AND uo_id = %d 
            AND slot_date = %s 
            AND is_active = 1
            AND (
                (slot_start_time < %s AND slot_end_time > %s)
                OR (slot_start_time < %s AND slot_end_time > %s)
                OR (slot_start_time >= %s AND slot_end_time <= %s)
            )",
            $service_id,
            $uo_id,
            $date,
            // Il nuovo slot inizia prima che finisca quello esistente
            $end->format('H:i:s'),
            $start->format('H:i:s'),
            // Il nuovo slot finisce dopo che inizi quello esistente
            $end->format('H:i:s'),
            $start->format('H:i:s'),
            // Uno slot esistente è completamente contenuto nel nuovo
            $start->format('H:i:s'),
            $end->format('H:i:s')
        ));

        if ($overlapping_slot) {
            $existing_start = substr($overlapping_slot->slot_start_time, 0, 5);
            $existing_end = substr($overlapping_slot->slot_end_time, 0, 5);
            wp_send_json_error(sprintf(
                'Esiste già uno slot attivo che si sovrappone con questo orario (%s - %s). Scegli un orario diverso o modifica quello esistente.',
                $existing_start,
                $existing_end
            ));
            return;
        }

        $slot_id = $this->slot_manager->create_slot(
            $service_id,
            $uo_id,
            $date,
            $start->format('H:i:s'),
            $end->format('H:i:s'),
            $max_bookings
        );

        if ($slot_id) {
            wp_send_json_success(array('slot_id' => $slot_id));
        } else {
            wp_send_json_error('Errore creazione slot');
        }
    }

    /**
     * AJAX: Genera slot per un range di date
     */
    public function ajax_generate_range() {
        check_ajax_referer('swb-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
            return;
        }

        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        $uo_id = isset($_POST['uo_id']) ? intval($_POST['uo_id']) : 0;
        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
        $start_time = isset($_POST['start_time']) ? sanitize_text_field($_POST['start_time']) : '09:00';
        $end_time = isset($_POST['end_time']) ? sanitize_text_field($_POST['end_time']) : '12:00';
        $slot_duration = isset($_POST['slot_duration']) ? intval($_POST['slot_duration']) : 45;
        $max_bookings = isset($_POST['max_bookings']) ? intval($_POST['max_bookings']) : 1;
        $slot_interval = isset($_POST['slot_interval']) ? intval($_POST['slot_interval']) : 0;

        // Gestione pausa pranzo
        $break_start = isset($_POST['break_start']) ? sanitize_text_field($_POST['break_start']) : null;
        $break_end = isset($_POST['break_end']) ? sanitize_text_field($_POST['break_end']) : null;

        // Gestione orari personalizzati per giorno
        $custom_hours = array();
        if (isset($_POST['custom_hours'])) {
            $custom_hours_json = stripslashes($_POST['custom_hours']);
            $custom_hours_decoded = json_decode($custom_hours_json, true);
            if (is_array($custom_hours_decoded)) {
                $custom_hours = $custom_hours_decoded;
            }
        }

        if (!$service_id || !$uo_id || !$start_date || !$end_date) {
            wp_send_json_error('Parametri mancanti');
            return;
        }

        // Verifica che le date non siano nel passato
        $today = new DateTime();
        $today->setTime(0, 0, 0);

        $start_date_obj = new DateTime($start_date);
        $end_date_obj = new DateTime($end_date);

        if ($end_date_obj < $today) {
            wp_send_json_error('Non è possibile generare slot per date passate. Seleziona date a partire da oggi.');
            return;
        }

        // Se la data di inizio è nel passato ma quella di fine no, aggiusta la data di inizio a oggi
        if ($start_date_obj < $today) {
            $start_date = $today->format('Y-m-d');
            $start_date_obj = clone $today;
        }

        $count = 0;

        // Se ci sono orari personalizzati, genera giorno per giorno
        if (!empty($custom_hours)) {
            $day_map = array(1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri');

            $current_date = clone $start_date_obj;
            while ($current_date <= $end_date_obj) {
                $day_of_week = (int)$current_date->format('N'); // 1=Lun, 5=Ven, 6=Sab, 7=Dom

                // Salta weekend
                if ($day_of_week >= 6) {
                    $current_date->modify('+1 day');
                    continue;
                }

                $day_key = $day_map[$day_of_week];

                // Usa orari personalizzati se disponibili per questo giorno, altrimenti usa i default
                $day_start_time = isset($custom_hours[$day_key]['start']) ? $custom_hours[$day_key]['start'] : $start_time;
                $day_end_time = isset($custom_hours[$day_key]['end']) ? $custom_hours[$day_key]['end'] : $end_time;

                // Genera slot per questo giorno
                $time_slots = $this->generate_time_slots($day_start_time, $day_end_time, $slot_duration, $break_start, $break_end, $slot_interval);

                if (!empty($time_slots)) {
                    $config = array(
                        'morning_slots' => $time_slots,
                        'afternoon_slots' => array(),
                        'morning_days' => array($day_of_week),
                        'afternoon_days' => array(),
                        'max_bookings_per_slot' => $max_bookings
                    );

                    $day_count = $this->slot_manager->generate_slots_for_service(
                        $service_id,
                        $uo_id,
                        $current_date->format('Y-m-d'),
                        $current_date->format('Y-m-d'),
                        $config
                    );

                    $count += $day_count;
                }

                $current_date->modify('+1 day');
            }
        } else {
            // Logica standard: usa gli stessi orari per tutti i giorni
            $time_slots = $this->generate_time_slots($start_time, $end_time, $slot_duration, $break_start, $break_end, $slot_interval);

            $config = array(
                'morning_slots' => $time_slots,
                'afternoon_slots' => array(),
                'morning_days' => array(1, 2, 3, 4, 5), // Lun-Ven
                'afternoon_days' => array(),
                'max_bookings_per_slot' => $max_bookings
            );

            $count = $this->slot_manager->generate_slots_for_service(
                $service_id,
                $uo_id,
                $start_date,
                $end_date,
                $config
            );
        }

        wp_send_json_success(array('count' => $count));
    }

    /**
     * Genera intervalli di tempo in base a start, end e durata, escludendo la pausa
     *
     * Aggiunto parametro $interval_minutes: spazio (in minuti) tra la fine di uno slot e l'inizio del successivo
     */
    private function generate_time_slots($start_time, $end_time, $duration_minutes, $break_start = null, $break_end = null, $interval_minutes = 0) {
        $slots = array();
        $current = new DateTime($start_time);
        $end = new DateTime($end_time);

        $break_start_obj = $break_start ? new DateTime($break_start) : null;
        $break_end_obj = $break_end ? new DateTime($break_end) : null;

        while ($current < $end) {
            $slot_start = clone $current;
            $slot_end = clone $current;
            $slot_end->modify("+{$duration_minutes} minutes");

            // Non aggiungere lo slot se supera l'orario di fine
            if ($slot_end > $end) {
                break;
            }

            // Verifica se lo slot cade nella pausa pranzo
            $skip_slot = false;
            if ($break_start_obj && $break_end_obj) {
                if (($slot_start >= $break_start_obj && $slot_start < $break_end_obj) ||
                    ($slot_end > $break_start_obj && $slot_end <= $break_end_obj) ||
                    ($slot_start < $break_start_obj && $slot_end > $break_end_obj)) {
                    $skip_slot = true;
                }
            }

            if (!$skip_slot) {
                $slots[] = array(
                    'start' => $slot_start->format('H:i'),
                    'end' => $slot_end->format('H:i')
                );
            }

            // Avanza al termine dello slot e applica l'intervallo
            $current = clone $slot_end;
            if ($interval_minutes > 0) {
                $current->modify("+{$interval_minutes} minutes");
            }
        }

        return $slots;
    }

    /**
     * AJAX: Elimina tutti gli slot di un mese
     */
    public function ajax_bulk_delete_month() {
        check_ajax_referer('swb-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
            return;
        }

        global $wpdb;

        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        $uo_id = isset($_POST['uo_id']) ? intval($_POST['uo_id']) : 0;
        $month = isset($_POST['month']) ? sanitize_text_field($_POST['month']) : '';

        if (!$service_id || !$uo_id || !$month) {
            wp_send_json_error('Parametri mancanti');
            return;
        }

        list($year, $month_num) = explode('-', $month);
        $start_date = sprintf('%04d-%02d-01', $year, $month_num);
        $end_date = date('Y-m-t', strtotime($start_date));

        $table_name = $wpdb->prefix . 'booking_slots';

        $count = $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET is_active = 0 
            WHERE service_id = %d 
            AND uo_id = %d 
            AND slot_date BETWEEN %s AND %s",
            $service_id,
            $uo_id,
            $start_date,
            $end_date
        ));

        wp_send_json_success(array('count' => $count));
    }

    /**
     * AJAX: Elimina tutti gli slot di un giorno specifico
     */
    public function ajax_delete_day_slots() {
        check_ajax_referer('swb-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
            return;
        }

        global $wpdb;

        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        $uo_id = isset($_POST['uo_id']) ? intval($_POST['uo_id']) : 0;
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';

        if (!$service_id || !$uo_id || !$date) {
            wp_send_json_error('Parametri mancanti');
            return;
        }

        $table_name = $wpdb->prefix . 'booking_slots';

        // Conta le prenotazioni che verranno eliminate
        $bookings_count = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(current_bookings) FROM $table_name 
            WHERE service_id = %d 
            AND uo_id = %d 
            AND slot_date = %s
            AND is_active = 1",
            $service_id,
            $uo_id,
            $date
        ));

        // Elimina tutti gli slot del giorno
        $count = $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET is_active = 0 
            WHERE service_id = %d 
            AND uo_id = %d 
            AND slot_date = %s",
            $service_id,
            $uo_id,
            $date
        ));

        wp_send_json_success(array(
            'count' => $count,
            'bookings' => intval($bookings_count)
        ));
    }

    /**
     * AJAX: Elimina TUTTI gli slot del servizio (senza limiti temporali)
     */
    public function ajax_delete_all_service_slots() {
        check_ajax_referer('swb-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
            return;
        }

        global $wpdb;

        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        $uo_id = isset($_POST['uo_id']) ? intval($_POST['uo_id']) : 0;

        if (!$service_id || !$uo_id) {
            wp_send_json_error('Parametri mancanti');
            return;
        }

        $table_name = $wpdb->prefix . 'booking_slots';

        // Prima conta le prenotazioni che verranno eliminate
        $bookings_count = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(current_bookings) FROM $table_name 
            WHERE service_id = %d 
            AND uo_id = %d 
            AND is_active = 1",
            $service_id,
            $uo_id
        ));

        // Elimina tutti gli slot del servizio (impostando is_active = 0)
        $count = $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET is_active = 0 
            WHERE service_id = %d 
            AND uo_id = %d",
            $service_id,
            $uo_id
        ));

        wp_send_json_success(array(
            'count' => $count,
            'bookings' => intval($bookings_count)
        ));
    }

    /**
     * AJAX: Aggiunge un giorno di chiusura (singolo o ricorrente)
     */
    public function ajax_add_closed_day() {
        check_ajax_referer('swb-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
            return;
        }

        $closed_date = isset($_POST['closed_date']) ? sanitize_text_field($_POST['closed_date']) : null;
        $is_recurring = isset($_POST['is_recurring']) ? intval($_POST['is_recurring']) : 0;
        $weekday = isset($_POST['weekday']) ? intval($_POST['weekday']) : null;
        $reason = isset($_POST['reason']) ? sanitize_text_field($_POST['reason']) : null;

        $id = SWB_Closures_Manager::add_closed_day($closed_date, $is_recurring, $weekday, $reason);

        if ($id) {
            wp_send_json_success(array('id' => $id));
        } else {
            wp_send_json_error('Errore creazione giorno chiuso o duplicato');
        }
    }

    /**
     * AJAX: Rimuove un giorno chiuso per ID
     */
    public function ajax_remove_closed_day() {
        check_ajax_referer('swb-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
            return;
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if (!$id) {
            wp_send_json_error('ID mancante');
            return;
        }

        $ok = SWB_Closures_Manager::remove_closed_day($id);
        if ($ok) {
            wp_send_json_success('Rimosso');
        } else {
            wp_send_json_error('Errore rimozione');
        }
    }

    /**
     * AJAX: Lista giorni chiusi per month
     */
    public function ajax_list_closed_days() {
        check_ajax_referer('swb-admin-nonce', 'nonce');

        $month = isset($_GET['month']) ? sanitize_text_field($_GET['month']) : null;

        $list = SWB_Closures_Manager::list_closed_days($month);
        wp_send_json_success($list);
    }

    /**
     * AJAX: Rimuove chiusure per una data specifica (include anche ricorrenze settimanali con lo stesso weekday)
     */
    public function ajax_remove_closed_by_date() {
        check_ajax_referer('swb-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
            return;
        }

        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
        if (empty($date)) {
            wp_send_json_error('Data mancante');
            return;
        }

        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            wp_send_json_error('Formato data non valido');
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'booking_closed_days';

        $weekday = intval($d->format('N')); // 1..7

        // Rimuove sia le righe con closed_date = date sia le ricorrenze settimanali con weekday
        $deleted1 = $wpdb->query($wpdb->prepare("DELETE FROM $table WHERE closed_date = %s", $date));
        $deleted2 = $wpdb->query($wpdb->prepare("DELETE FROM $table WHERE is_recurring = 1 AND weekday = %d", $weekday));

        $total = 0;
        if ($deleted1 !== false) $total += intval($deleted1);
        if ($deleted2 !== false) $total += intval($deleted2);

        wp_send_json_success(array('deleted' => $total));
    }
}
