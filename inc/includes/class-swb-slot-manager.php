<?php
/**
 * Gestisce gli slot di appuntamento
 */

if (!defined('ABSPATH')) {
    exit;
}

class SWB_Slot_Manager {

    private $table_name;
    private $bookings_table;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'booking_slots';
        $this->bookings_table = $wpdb->prefix . 'booking_reservations';
    }

    public function init() {
        // Hooks
    }

    /**
     * Crea uno slot
     */
    public function create_slot($service_id, $uo_id, $date, $start_time, $end_time, $max_bookings = 1) {
        global $wpdb;

        $result = $wpdb->insert(
            $this->table_name,
            array(
                'service_id' => $service_id,
                'uo_id' => $uo_id,
                'slot_date' => $date,
                'slot_start_time' => $start_time,
                'slot_end_time' => $end_time,
                'max_bookings' => $max_bookings,
                'current_bookings' => 0,
                'is_active' => 1
            ),
            array('%d', '%d', '%s', '%s', '%s', '%d', '%d', '%d')
        );

        if ($result === false) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Calcola il conteggio reale delle prenotazioni per uno o più slot
     * basandosi sullo status degli appuntamenti (solo 'publish')
     */
    public function calculate_actual_bookings($slots) {
        if (empty($slots)) {
            return $slots;
        }

        global $wpdb;

        // Se è un singolo slot, trasformalo in array
        $single_slot = false;
        if (!is_array($slots)) {
            $slots = array($slots);
            $single_slot = true;
        }

        // Ottieni gli ID degli slot
        $slot_ids = array_map(function($slot) { return $slot->id; }, $slots);
        $slot_ids_placeholder = implode(',', array_fill(0, count($slot_ids), '%d'));

        // Query per contare le prenotazioni attive (solo appuntamenti pubblicati)
        $query = "SELECT br.slot_id, COUNT(*) as active_bookings
                 FROM {$this->bookings_table} br
                 INNER JOIN {$wpdb->posts} p ON br.appuntamento_id = p.ID
                 WHERE br.slot_id IN ($slot_ids_placeholder)
                 AND p.post_type = 'appuntamento'
                 AND p.post_status = 'publish'
                 GROUP BY br.slot_id";

        $results = $wpdb->get_results($wpdb->prepare($query, ...$slot_ids), OBJECT_K);

        // Aggiorna il conteggio per ogni slot
        foreach ($slots as $slot) {
            $slot->current_bookings = isset($results[$slot->id]) ? (int)$results[$slot->id]->active_bookings : 0;
        }

        return $single_slot ? $slots[0] : $slots;
    }

    /**
     * Ottiene gli slot disponibili per un mese
     */
    public function get_slots_by_month($service_id, $uo_id, $year, $month) {
        global $wpdb;

        $start_date = sprintf('%04d-%02d-01', $year, $month);
        $end_date = date('Y-m-t', strtotime($start_date)); // Ultimo giorno del mese

        // Non mostrare slot di giorni passati
        $today = date('Y-m-d');

        // Se l'intero mese è nel passato, restituisci array vuoto
        if ($end_date < $today) {
            return array();
        }

        // Se siamo nel mese corrente, parti da oggi
        if ($start_date < $today) {
            $start_date = $today;
        }

        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name}
            WHERE service_id = %d
            AND uo_id = %d
            AND slot_date BETWEEN %s AND %s
            AND is_active = 1
            ORDER BY slot_date ASC, slot_start_time ASC",
            $service_id,
            $uo_id,
            $start_date,
            $end_date
        );

        $slots = $wpdb->get_results($sql);

        // Calcola il conteggio reale delle prenotazioni
        $slots = $this->calculate_actual_bookings($slots);

        // Filtra solo gli slot con posti disponibili
        return array_filter($slots, function($slot) {
            return $slot->current_bookings < $slot->max_bookings;
        });
    }

    /**
     * Ottiene tutti gli slot per un servizio (per admin)
     */
    public function get_all_slots_by_service($service_id, $uo_id = null, $future_only = true) {
        global $wpdb;

        $where = array();
        $where[] = $wpdb->prepare('service_id = %d', $service_id);

        if ($uo_id) {
            $where[] = $wpdb->prepare('uo_id = %d', $uo_id);
        }

        if ($future_only) {
            $where[] = $wpdb->prepare('slot_date >= %s', date('Y-m-d'));
        }

        $where_clause = implode(' AND ', $where);

        $sql = "SELECT * FROM {$this->table_name}
                WHERE {$where_clause}
                ORDER BY slot_date ASC, slot_start_time ASC";

        $slots = $wpdb->get_results($sql);

        // Calcola il conteggio reale delle prenotazioni
        return $this->calculate_actual_bookings($slots);
    }

    /**
     * Ottiene un singolo slot per ID
     */
    public function get_slot($slot_id) {
        global $wpdb;

        $slot = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $slot_id
        ));

        // Calcola il conteggio reale delle prenotazioni
        return $slot ? $this->calculate_actual_bookings($slot) : null;
    }

    /**
     * Prenota uno slot
     */
    public function book_slot($slot_id, $appuntamento_id, $user_email, $user_name, $user_cf = null) {
        global $wpdb;

        // Verifica che lo slot sia disponibile
        $slot = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d AND is_active = 1",
            $slot_id
        ));

        if (!$slot || $slot->current_bookings >= $slot->max_bookings) {
            return false;
        }

        // Inizia transazione
        $wpdb->query('START TRANSACTION');

        try {
            // Crea la prenotazione
            $result = $wpdb->insert(
                $this->bookings_table,
                array(
                    'slot_id' => $slot_id,
                    'appuntamento_id' => $appuntamento_id,
                    'user_email' => $user_email,
                    'user_name' => $user_name,
                    'user_cf' => $user_cf,
                    'status' => 'confirmed'
                ),
                array('%d', '%d', '%s', '%s', '%s', '%s')
            );

            if ($result === false) {
                throw new Exception('Errore creazione prenotazione');
            }

            // Incrementa il contatore
            $update = $wpdb->query($wpdb->prepare(
                "UPDATE {$this->table_name} 
                SET current_bookings = current_bookings + 1 
                WHERE id = %d AND current_bookings < max_bookings",
                $slot_id
            ));

            if ($update === false) {
                throw new Exception('Errore aggiornamento slot');
            }

            $wpdb->query('COMMIT');
            return $wpdb->insert_id;

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log('SWB: Errore prenotazione slot - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancella una prenotazione
     */
    public function cancel_booking($booking_id) {
        global $wpdb;

        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->bookings_table} WHERE id = %d",
            $booking_id
        ));

        if (!$booking) {
            return false;
        }

        $wpdb->query('START TRANSACTION');

        try {
            // Aggiorna status
            $wpdb->update(
                $this->bookings_table,
                array('status' => 'cancelled'),
                array('id' => $booking_id),
                array('%s'),
                array('%d')
            );

            // Decrementa contatore
            $wpdb->query($wpdb->prepare(
                "UPDATE {$this->table_name} 
                SET current_bookings = GREATEST(0, current_bookings - 1)
                WHERE id = %d",
                $booking->slot_id
            ));

            $wpdb->query('COMMIT');
            return true;

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return false;
        }
    }

    /**
     * Elimina uno slot
     */
    public function delete_slot($slot_id) {
        global $wpdb;

        error_log('SWB DEBUG: delete_slot chiamata con ID: ' . $slot_id);
        error_log('SWB DEBUG: Tabella: ' . $this->table_name);

        // Verifica che lo slot esista
        $slot = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $slot_id
        ));

        error_log('SWB DEBUG: Slot trovato: ' . print_r($slot, true));

        if (!$slot) {
            error_log('SWB DEBUG: Slot non trovato con ID: ' . $slot_id);
            return false;
        }

        // Soft delete
        $result = $wpdb->update(
            $this->table_name,
            array('is_active' => 0),
            array('id' => $slot_id),
            array('%d'),
            array('%d')
        );

        error_log('SWB DEBUG: Risultato wpdb->update: ' . var_export($result, true));
        error_log('SWB DEBUG: wpdb->last_error: ' . $wpdb->last_error);
        error_log('SWB DEBUG: wpdb->last_query: ' . $wpdb->last_query);

        // Se result è false c'è stato un errore SQL
        // Se result è 0, lo slot era già disattivato (comunque successo)
        // Se result è 1+, lo slot è stato disattivato ora (successo)
        if ($result === false) {
            error_log('SWB DEBUG: Errore SQL nella query UPDATE');
            return false;
        }

        error_log('SWB DEBUG: Slot eliminato/già eliminato con successo');
        return true;
    }

    /**
     * Genera slot automaticamente per un servizio
     * @throws Exception
     */
    public function generate_slots_for_service($service_id, $uo_id, $start_date, $end_date, $config = array()): int
    {
        $defaults = array(
            'morning_slots' => array(
                array('start' => '09:00', 'end' => '09:45'),
                array('start' => '09:45', 'end' => '10:30'),
                array('start' => '10:30', 'end' => '11:15'),
                array('start' => '11:15', 'end' => '12:00'),
            ),
            'afternoon_slots' => array(
                array('start' => '14:00', 'end' => '14:45'),
                array('start' => '14:45', 'end' => '15:30'),
                array('start' => '15:30', 'end' => '16:15'),
                array('start' => '16:15', 'end' => '17:00'),
            ),
            'morning_days' => array(1, 2, 3, 4, 5), // Lun-Ven
            'afternoon_days' => array(2, 4), // Mar-Gio
            'max_bookings_per_slot' => 1,
            'skip_holidays' => array(),
        );

        $config = array_merge($defaults, $config);

        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $count = 0;

        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_slots';

        // Prefetch: prendi tutti gli slot esistenti nel range per evitare query ripetute
        $existing_slots_results = $wpdb->get_results($wpdb->prepare(
            "SELECT slot_date, slot_start_time, slot_end_time FROM {$table_name} WHERE service_id = %d AND uo_id = %d AND slot_date BETWEEN %s AND %s AND is_active = 1",
            $service_id,
            $uo_id,
            $start->format('Y-m-d'),
            $end->format('Y-m-d')
        ), ARRAY_A);

        $existing_by_date = array();
        foreach ($existing_slots_results as $er) {
            $d = $er['slot_date'];
            if (!isset($existing_by_date[$d])) $existing_by_date[$d] = array();
            // Manteniamo gli orari in formato H:i:s per comparazioni coerenti
            $existing_by_date[$d][] = array('start' => $er['slot_start_time'], 'end' => $er['slot_end_time']);
        }

         while ($start <= $end) {
             $day_of_week = (int)$start->format('N'); // 1=Lun, 7=Dom
             $date_str = $start->format('Y-m-d');

             // Salta se weekend
             if ($day_of_week == 6 || $day_of_week == 7) {
                 $start->modify('+1 day');
                 continue;
             }

             // Salta se la data è configurata come giorno chiuso (globale)
             if (class_exists('SWB_Closures_Manager') && SWB_Closures_Manager::is_closed_date($date_str)) {
                 $start->modify('+1 day');
                 continue;
             }

             // Salta se festivo
             if (in_array($date_str, $config['skip_holidays'])) {
                 $start->modify('+1 day');
                 continue;
             }

             // Aggiungi slot mattina
             if (in_array($day_of_week, $config['morning_days'])) {
                 foreach ($config['morning_slots'] as $slot) {
                    // Controllo sovrapposizione in-memory usando la mappa prefetchata
                    $s_start = $slot['start'];
                    $s_end = $slot['end'];
                    $s_start_db = strpos($s_start, ':') === false ? $s_start . ':00' : (strlen($s_start) === 5 ? $s_start . ':00' : $s_start);
                    $s_end_db = strpos($s_end, ':') === false ? $s_end . ':00' : (strlen($s_end) === 5 ? $s_end . ':00' : $s_end);

                    $overlap = false;
                    if (isset($existing_by_date[$date_str])) {
                        foreach ($existing_by_date[$date_str] as $ex) {
                            $ex_start = $ex['start'];
                            $ex_end = $ex['end'];
                            // Condizione di non-overlap: ex_end <= new_start OR ex_start >= new_end
                            if (!($ex_end <= $s_start_db || $ex_start >= $s_end_db)) {
                                $overlap = true;
                                break;
                            }
                        }
                    }

                    if ($overlap) {
                        // Salta la creazione di questo slot per evitare sovrapposizione
                        continue;
                    }
                     $this->create_slot(
                         $service_id,
                         $uo_id,
                         $date_str,
                         $slot['start'],
                         $slot['end'],
                         $config['max_bookings_per_slot']
                     );
                    // Aggiungi anche allo storico in-memory per evitare sovrapposizioni con gli slot appena creati
                    if (!isset($existing_by_date[$date_str])) $existing_by_date[$date_str] = array();
                    $existing_by_date[$date_str][] = array('start' => $s_start_db, 'end' => $s_end_db);
                     $count++;
                 }
             }

             // Aggiungi slot pomeriggio
             if (in_array($day_of_week, $config['afternoon_days'])) {
                 foreach ($config['afternoon_slots'] as $slot) {
                    // Controllo sovrapposizione in-memory
                    $s_start = $slot['start'];
                    $s_end = $slot['end'];
                    $s_start_db = strpos($s_start, ':') === false ? $s_start . ':00' : (strlen($s_start) === 5 ? $s_start . ':00' : $s_start);
                    $s_end_db = strpos($s_end, ':') === false ? $s_end . ':00' : (strlen($s_end) === 5 ? $s_end . ':00' : $s_end);

                    $overlap = false;
                    if (isset($existing_by_date[$date_str])) {
                        foreach ($existing_by_date[$date_str] as $ex) {
                            $ex_start = $ex['start'];
                            $ex_end = $ex['end'];
                            if (!($ex_end <= $s_start_db || $ex_start >= $s_end_db)) {
                                $overlap = true;
                                break;
                            }
                        }
                    }

                    if ($overlap) continue;
                     $this->create_slot(
                         $service_id,
                         $uo_id,
                         $date_str,
                         $slot['start'],
                         $slot['end'],
                         $config['max_bookings_per_slot']
                     );
                    if (!isset($existing_by_date[$date_str])) $existing_by_date[$date_str] = array();
                    $existing_by_date[$date_str][] = array('start' => $s_start_db, 'end' => $s_end_db);
                     $count++;
                 }
             }

             $start->modify('+1 day');
         }

         return $count;
     }
 }

