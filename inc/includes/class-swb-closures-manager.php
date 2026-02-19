<?php
/**
 * Gestisce i giorni di chiusura globali
 */

if (!defined('ABSPATH')) {
    exit;
}

class SWB_Closures_Manager {

    /**
     * Aggiunge un giorno di chiusura (data singola o ricorrente settimanale)
     * @param string|null $closed_date formato YYYY-MM-DD
     * @param int $is_recurring 0|1
     * @param int|null $weekday 1..7 (se ricorrente)
     * @param string|null $reason
     * @return int|false ID inserito o false
     */
    public static function add_closed_day($closed_date = null, $is_recurring = 0, $weekday = null, $reason = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'booking_closed_days';

        $is_recurring = intval($is_recurring) ? 1 : 0;

        if ($is_recurring) {
            $weekday = $weekday ? intval($weekday) : null;
            if ($weekday < 1 || $weekday > 7) {
                return false;
            }

            // Evita duplicati ricorrenti
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(1) FROM $table WHERE is_recurring = 1 AND weekday = %d",
                $weekday
            ));

            if ($exists) {
                return false;
            }

            $result = $wpdb->insert($table, array(
                'closed_date' => null,
                'is_recurring' => 1,
                'weekday' => $weekday,
                'reason' => sanitize_text_field($reason)
            ), array('%s','%d','%d','%s'));

            if ($result) return intval($wpdb->insert_id);
            return false;
        } else {
            if (empty($closed_date)) return false;
            // Validate date
            $d = DateTime::createFromFormat('Y-m-d', $closed_date);
            if (!$d || $d->format('Y-m-d') !== $closed_date) return false;

            // Evita duplicati
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(1) FROM $table WHERE closed_date = %s",
                $closed_date
            ));

            if ($exists) return false;

            $result = $wpdb->insert($table, array(
                'closed_date' => $closed_date,
                'is_recurring' => 0,
                'weekday' => null,
                'reason' => sanitize_text_field($reason)
            ), array('%s','%d','%s','%s'));

            if ($result) return intval($wpdb->insert_id);
            return false;
        }
    }

    /**
     * Rimuove un giorno chiuso per ID
     * @param int $id
     * @return bool
     */
    public static function remove_closed_day($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'booking_closed_days';
        $id = intval($id);
        if (!$id) return false;
        $deleted = $wpdb->delete($table, array('id' => $id), array('%d'));
        return $deleted !== false;
    }

    /**
     * Lista i giorni chiusi globali o per un mese
     * @param string|null $month formato YYYY-MM (opzionale)
     * @return array
     */
    public static function list_closed_days($month = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'booking_closed_days';

        $results = array();

        // Query singole date
        if ($month) {
            // Calcola inizio/fine mese
            $parts = explode('-', $month);
            if (count($parts) !== 2) return array();
            $year = intval($parts[0]);
            $m = intval($parts[1]);
            $start = sprintf('%04d-%02d-01', $year, $m);
            $end = date('Y-m-t', strtotime($start));

            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE is_recurring = 0 AND closed_date BETWEEN %s AND %s",
                $start, $end
            ));

            foreach ($rows as $r) {
                $results[] = array(
                    'id' => intval($r->id),
                    'closed_date' => $r->closed_date,
                    'is_recurring' => intval($r->is_recurring),
                    'weekday' => $r->weekday,
                    'reason' => $r->reason
                );
            }

            // Aggiungi ricorrenze settimanali per il mese
            $recurrences = $wpdb->get_results(
                "SELECT * FROM $table WHERE is_recurring = 1"
            );

            foreach ($recurrences as $rec) {
                $weekday = intval($rec->weekday); // 1..7
                // Cicla tutto il mese e aggiungi le date che corrispondono
                $current = new DateTime($start);
                while ($current->format('Y-m-d') <= $end) {
                    if (intval($current->format('N')) === $weekday) {
                        $results[] = array(
                            'id' => intval($rec->id),
                            'closed_date' => $current->format('Y-m-d'),
                            'is_recurring' => 1,
                            'weekday' => $weekday,
                            'reason' => $rec->reason
                        );
                    }
                    $current->modify('+1 day');
                }
            }

            return $results;
        } else {
            // Senza mese: ritorna tutto
            $rows = $wpdb->get_results("SELECT * FROM $table");

            foreach ($rows as $r) {
                $results[] = array(
                    'id' => intval($r->id),
                    'closed_date' => $r->closed_date,
                    'is_recurring' => intval($r->is_recurring),
                    'weekday' => $r->weekday,
                    'reason' => $r->reason
                );
            }

            return $results;
        }
    }

    /**
     * Controlla se una data è chiusa (globale)
     * @param string $date formato YYYY-MM-DD
     * @return bool
     */
    public static function is_closed_date($date) {
        global $wpdb;
        $table = $wpdb->prefix . 'booking_closed_days';

        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!$d) return false;

        // Controlla singole date
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(1) FROM $table WHERE is_recurring = 0 AND closed_date = %s",
            $date
        ));

        if ($exists) return true;

        // Controlla ricorrenze settimanali
        $weekday = intval($d->format('N'));
        $exists_rec = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(1) FROM $table WHERE is_recurring = 1 AND weekday = %d",
            $weekday
        ));

        return (bool)$exists_rec;
    }
}
