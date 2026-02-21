<?php
/**
 * Class SWB_Calendar_View
 *
 * Gestisce il rendering della vista calendario
 *
 * @package SimpleWPBooking
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SWB_Calendar_View {

    private $slot_manager;

    public function __construct($slot_manager) {
        $this->slot_manager = $slot_manager;
    }

    /**
     * Renderizza la vista calendario completa
     *
     * @param int $service_id ID del servizio
     * @param string $month_str Mese in formato Y-m
     * @return array Dati per il JavaScript (service_id, uo_id, month)
     */
    public function render($service_id, $month_str): array {
        global $wpdb;

        $servizio = get_post($service_id);

        // Non più necessario recuperare UO
        $uo_id = 0;

        // Parse mese
        list($year, $month) = explode('-', $month_str);
        $year = intval($year);
        $month = intval($month);

        $first_day = new DateTime("$year-$month-01");
        $last_day = new DateTime($first_day->format('Y-m-t'));
        $today = new DateTime();

        // Ottieni slot del mese (solo attivi) con conteggi reali basati su status post
        $table_name = $wpdb->prefix . 'booking_slots';
        $slots_raw = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name 
            WHERE service_id = %d 
            AND uo_id = 0 
            AND slot_date BETWEEN %s AND %s
            AND is_active = 1
            ORDER BY slot_start_time",
            $service_id,
            $first_day->format('Y-m-d'),
            $last_day->format('Y-m-d')
        ));

        // Calcola conteggi reali basati sullo status degli appuntamenti (solo 'publish')
        $slots_data = $this->slot_manager->calculate_actual_bookings($slots_raw);

        // Organizza slot per data
        $slots_by_date = array();
        foreach ($slots_data as $slot) {
            $date_key = $slot->slot_date;
            if (!isset($slots_by_date[$date_key])) {
                $slots_by_date[$date_key] = array();
            }
            $slots_by_date[$date_key][] = $slot;
        }

        $total_slots = count($slots_data);
        $booked_slots = array_sum(array_map(function($s) { return $s->current_bookings; }, $slots_data));

        // Verifica se il mese è passato
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        $is_past_month = $last_day < $today;

        // Render Header Info
        $this->render_header_info($servizio, $month_str, $total_slots, $booked_slots, $is_past_month);

        // Render Action Buttons
        $this->render_action_buttons($is_past_month);

        // Render Calendar Grid
        $this->render_calendar_grid($first_day, $last_day, $today, $slots_by_date, $is_past_month);

        // Render Month Navigation
        $this->render_month_navigation($service_id, $month_str);

        // Restituisce i dati per il JavaScript
        return [
            'service_id' => $service_id,
            'uo_id' => $uo_id,
            'month' => $month_str
        ];
    }

    /**
     * Renderizza le informazioni header del calendario
     */
    private function render_header_info($servizio, $month_str, $total_slots, $booked_slots, $is_past_month): void {
        ?>
        <div class="swb-calendar-info" style="background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px;">
            <h2 style="margin-top: 0;"><?php echo esc_html($servizio->post_title); ?></h2>
            <p><strong>Mese:</strong> <?php echo date_i18n('F Y', strtotime($month_str . '-01')); ?></p>
            <p><strong>Slot totali:</strong> <?php echo $total_slots; ?> | <strong>Prenotazioni:</strong> <?php echo $booked_slots; ?></p>
            <?php if ($is_past_month): ?>
                <p style="color: #d63638; font-weight: bold;">ATTENZIONE: Questo è un mese passato - la generazione di nuovi slot è disabilitata</p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Renderizza i pulsanti di azione
     */
    private function render_action_buttons($is_past_month): void {
        ?>
        <div class="swb-calendar-actions">
            <?php if (!$is_past_month): ?>
            <button type="button" class="button button-primary" onclick="swbGenerateWeek()">
                Genera Settimana
            </button>
            <button type="button" class="button button-primary" onclick="swbGenerateMonth()">
                Genera Mese Intero
            </button>
            <?php endif; ?>
            <button type="button" class="button" onclick="swbBulkDelete()">
                Elimina Tutti del Mese
            </button>
            <button type="button" class="button button-link-delete" onclick="swbDeleteAllSlots()" style="color: #d63638;">
                Elimina TUTTI gli Slot del Servizio
            </button>
        </div>
        <?php
    }

    /**
     * Renderizza la griglia del calendario
     */
    private function render_calendar_grid($first_day, $last_day, $today, $slots_by_date, $is_past_month): void {
        ?>
        <div class="swb-calendar-grid">
            <!-- Header giorni settimana -->
            <div class="swb-calendar-header">Lunedì</div>
            <div class="swb-calendar-header">Martedì</div>
            <div class="swb-calendar-header">Mercoledì</div>
            <div class="swb-calendar-header">Giovedì</div>
            <div class="swb-calendar-header">Venerdì</div>
            <div class="swb-calendar-header">Sabato</div>
            <div class="swb-calendar-header">Domenica</div>

            <?php
            // Calcola primo giorno del mese (1=Lun, 7=Dom)
            $first_weekday = (int)$first_day->format('N');

            // Aggiungi giorni vuoti all'inizio
            for ($i = 1; $i < $first_weekday; $i++) {
                echo '<div class="swb-calendar-day other-month"></div>';
            }

            // Giorni del mese
            $current_date = clone $first_day;
            while ($current_date <= $last_day) {
                $this->render_calendar_day($current_date, $today, $slots_by_date, $is_past_month);
                $current_date->modify('+1 day');
            }

            // Giorni vuoti alla fine
            $last_weekday = (int)$last_day->format('N');
            for ($i = $last_weekday; $i < 7; $i++) {
                echo '<div class="swb-calendar-day other-month"></div>';
            }
            ?>
        </div>
        <?php
    }

    /**
     * Renderizza un singolo giorno del calendario
     */
    private function render_calendar_day($current_date, $today, $slots_by_date, $is_past_month): void {
        $date_key = $current_date->format('Y-m-d');
        $day_num = $current_date->format('j');
        $weekday = (int)$current_date->format('N');
        $is_weekend = ($weekday == 6 || $weekday == 7);
        $is_today = ($current_date->format('Y-m-d') == $today->format('Y-m-d'));

        // Controlla se la data è marcata come chiusura globale
        $is_closed = SWB_Closures_Manager::is_closed_date($date_key);

        $classes = array('swb-calendar-day');
        if ($is_weekend) $classes[] = 'weekend';
        if ($is_today) $classes[] = 'today';
        if ($is_closed) $classes[] = 'closed';

        echo '<div class="' . implode(' ', $classes) . '" data-date="' . esc_attr($date_key) . '">';
        echo '<div class="swb-calendar-day-number">' . $day_num . '</div>';

        // Container per gli slot
        echo '<div class="swb-slots-container">';

        // Mostra slot per questo giorno
        if (isset($slots_by_date[$date_key])) {
            $this->render_day_slots($slots_by_date[$date_key], $date_key);
        }

        echo '</div>'; // fine swb-slots-container

        // Pulsante elimina tutti gli slot del giorno (solo se ci sono slot)
        if (isset($slots_by_date[$date_key]) && count($slots_by_date[$date_key]) > 0) {
            echo '<button class="swb-delete-day-btn" onclick="swbDeleteDaySlots(\'' . esc_attr($date_key) . '\')" title="Elimina tutti gli slot del giorno">'
                 . '<span class="dashicons dashicons-trash"></span>'
                 . '</button>';
        }

        // Pulsante aggiungi: non mostrare se weekend, passato, o giorno di chiusura
        if (!$is_weekend && $current_date >= $today && !$is_closed) {
            echo '<button class="swb-add-slot-btn" onclick="swbAddSlot(\'' . esc_attr($date_key) . '\')" title="Aggiungi slot">'
                 . '<span class="dashicons dashicons-plus-alt"></span>'
                 . '</button>';
        }

        echo '</div>';
    }

    /**
     * Renderizza gli slot di un giorno
     */
    private function render_day_slots($day_slots, $date_key): void {
        $slot_count = count($day_slots);
        $day_bookings = 0;

        foreach ($day_slots as $slot) {
            $start = substr($slot->slot_start_time, 0, 5);
            $status_class = '';
            if ($slot->current_bookings >= $slot->max_bookings) {
                $status_class = 'full';
            } elseif ($slot->current_bookings > 0) {
                $status_class = 'booked';
            }

            $day_bookings += $slot->current_bookings;

            // Crea URL per filtrare gli appuntamenti (solo se ci sono prenotazioni)
            $filter_url = '';
            if ($slot->current_bookings > 0) {
                $filter_url = add_query_arg(array(
                    'post_type' => 'appuntamento',
                    'swb_filter_slot' => $slot->id,
                    'swb_filter_date' => $date_key,
                    'swb_filter_time' => $start
                ), admin_url('edit.php'));
            }

            $title = 'Click: modifica slot';
            if ($slot->current_bookings > 0) {
                $title .= ' | Visualizza ' . $slot->current_bookings . ' prenotazioni (link nella modale)';
            }
            $title .= "\n" . $start . ' - Prenotazioni: ' . $slot->current_bookings . '/' . $slot->max_bookings;

            // Costruisce l'attributo onclick in modo chiaro (evita backslash nella concat)
            // Usa sprintf per costruire la chiamata JS in modo sicuro
            $onclick = sprintf(
                "swbHandleSlotClick(this, %d, '%s', '%s')",
                intval($slot->id),
                esc_js($date_key),
                esc_js($start)
            );
            // La riga sopra costruisce un JS handler: swbHandleSlotClick(this, ID, 'YYYY-MM-DD', 'HH:MM')

            echo '<div class="swb-slot ' . esc_attr($status_class) . '" '
                . 'data-slot-id="' . esc_attr($slot->id) . '" '
                . 'data-filter-url="' . esc_attr($filter_url) . '" '
                . 'data-has-bookings="' . ($slot->current_bookings > 0 ? '1' : '0') . '" '
                . 'onclick="' . esc_attr($onclick) . '" '
                . 'title="' . esc_attr($title) . '">'
                . esc_html($start) . ' (' . intval($slot->current_bookings) . '/' . intval($slot->max_bookings) . ')</div>';
        }

        echo '<div class="swb-slot-count">';
        echo $slot_count . ' slot';
        if ($day_bookings > 0) {
            // URL per filtrare la lista appuntamenti per la data del giorno
            $day_filter_url = add_query_arg(array(
                'post_type' => 'appuntamento',
                'swb_filter_date' => $date_key
            ), admin_url('edit.php'));

            echo ' | <strong><a href="' . esc_url($day_filter_url) . '" target="_blank" rel="noopener noreferrer" style="color:#0073aa; text-decoration:underline;">' . intval($day_bookings) . ' prenotazioni</a></strong>';
        }
        echo '</div>';
    }

    /**
     * Renderizza la navigazione tra i mesi
     */
    private function render_month_navigation($service_id, $month_str): void {
        // Calcola mese precedente e successivo
        $prev_month = date('Y-m', strtotime($month_str . '-01 -1 month'));
        $next_month = date('Y-m', strtotime($month_str . '-01 +1 month'));
        ?>
        <!-- Navigazione Mesi -->
        <div class="swb-month-navigation" style="display: flex; justify-content: center; align-items: center; gap: 20px; margin-top: 30px; padding: 20px;">
            <a href="?page=swb-calendar&service_id=<?php echo intval($service_id); ?>&month=<?php echo esc_attr($prev_month); ?>"
               class="button button-secondary"
               style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">←</span>
                <span><?php echo date_i18n('F Y', strtotime($prev_month . '-01')); ?></span>
            </a>

            <div style="font-weight: bold; font-size: 16px; color: #0073aa;">
                <?php echo date_i18n('F Y', strtotime($month_str . '-01')); ?>
            </div>

            <a href="?page=swb-calendar&service_id=<?php echo intval($service_id); ?>&month=<?php echo esc_attr($next_month); ?>"
               class="button button-secondary"
               style="display: flex; align-items: center; gap: 8px;">
                <span><?php echo date_i18n('F Y', strtotime($next_month . '-01')); ?></span>
                <span style="font-size: 18px;">→</span>
            </a>
        </div>
        <?php
    }
}

