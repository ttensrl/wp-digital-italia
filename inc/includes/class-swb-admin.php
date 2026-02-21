<?php
/**
 * Gestisce l'interfaccia admin per gli slot
 */

if (!defined('ABSPATH')) {
    exit;
}

class SWB_Admin {

    private SWB_Slot_Manager $slot_manager;
    private SWB_Calendar_View $calendar_view;

    public function __construct() {
        $this->slot_manager = new SWB_Slot_Manager();
        $this->calendar_view = new SWB_Calendar_View($this->slot_manager);
    }

    public function init(): void
    {
        error_log('SWB_Admin::init() - Inizializzazione classe SWB_Admin');
        add_action('admin_menu', array($this, 'add_admin_menu'), 99);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post_servizio', array($this, 'save_service_slots_meta'), 10, 2);

        // Filtri per la lista appuntamenti
        add_action('restrict_manage_posts', array($this, 'add_appuntamento_filters'));
        add_filter('parse_query', array($this, 'filter_appuntamenti_by_slot'));
        add_filter('manage_appuntamento_posts_columns', array($this, 'add_appuntamento_columns'));
        add_action('manage_appuntamento_posts_custom_column', array($this, 'fill_appuntamento_columns'), 10, 2);
    }

    /**
     * Aggiunge menu admin
     */
    public function add_admin_menu(): void
    {
        error_log('SWB_Admin::add_admin_menu() - Chiamato hook admin_menu');
        global $menu;

        // Crea menu principale Appuntamenti
        add_menu_page(
            'Appuntamenti',
            'Appuntamenti',
            'manage_options',
            'appuntamenti',
            array($this, 'render_appuntamenti_page'),
            'dashicons-calendar-alt',
            5
        );

        // Sottomenu: Lista Appuntamenti
        add_submenu_page(
            'appuntamenti',
            'Tutti gli Appuntamenti',
            'Tutti gli Appuntamenti',
            'manage_options',
            'edit.php?post_type=appuntamento'
        );

        // Sottomenu: Genera Slot
        add_submenu_page(
            'appuntamenti',
            'Genera Slot',
            'Genera Slot',
            'manage_options',
            'swb-generate-slots',
            array($this, 'render_generate_page')
        );

        // Sottomenu: Calendario Slot
        add_submenu_page(
            'appuntamenti',
            'Calendario Slot',
            'Calendario',
            'manage_options',
            'swb-calendar',
            array($this, 'render_calendar_page')
        );

        // Sottomenu: Giorni di Chiusura
        add_submenu_page(
            'appuntamenti',
            'Giorni di Chiusura',
            'Giorni di Chiusura',
            'manage_options',
            'swb-closed-days',
            array($this, 'render_closed_days_page')
        );
    }

    /**
     * Carica script e stili admin
     */
    public function enqueue_admin_scripts($hook) {
        // Debug: mostra quale hook viene passato
        // error_log('SWB DEBUG - Hook: ' . $hook);

        // Carica su tutte le pagine del plugin
        $is_plugin_page = (
            str_contains($hook, 'swb-') ||
            str_contains($hook, 'page_swb-') ||
            str_contains($hook, 'toplevel_page_swb-') ||
            isset($_GET['page']) && str_contains($_GET['page'], 'swb-')
        );

        // Assicura il caricamento anche su swb-closed-days esplicitamente
        if (isset($_GET['page']) && $_GET['page'] === 'swb-closed-days') {
            $is_plugin_page = true;
        }

        // Carica anche sulla pagina di edit/new del post type 'servizio'
        global $post_type;
        $is_servizio_page = ($post_type === 'servizio' && (str_contains($hook, 'post.php') || str_contains($hook, 'post-new.php')));

        // Carica anche sulla pagina lista appuntamenti
        $is_appuntamento_page = ($post_type === 'appuntamento' && str_contains($hook, 'edit.php'));

        if ($is_plugin_page || $is_servizio_page || $is_appuntamento_page) {
            // Enqueue CSS - FORZA il caricamento senza cache
            wp_enqueue_style('swb-admin-css', SWB_PLUGIN_URL . 'assets/css/admin.css', array(), time());

            // Enqueue JS
            wp_enqueue_script('swb-admin-js', SWB_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), time(), true);

            // JS calendario (solo sulla pagina calendario)
            if (isset($_GET['page']) && $_GET['page'] === 'swb-calendar') {
                wp_enqueue_script('swb-calendar-js', SWB_PLUGIN_URL . 'assets/js/calendar.js', array('jquery', 'swb-admin-js'), time(), true);
            }

            // JS giorni chiusi (solo sulla pagina giorni chiusi)
            if (isset($_GET['page']) && $_GET['page'] === 'swb-closed-days') {
                wp_enqueue_script('swb-closed-days-js', SWB_PLUGIN_URL . 'assets/js/closed-days.js', array('jquery', 'swb-admin-js'), time(), true);
            }

            // Localize script con dati necessari
            wp_localize_script('swb-admin-js', 'swbAdmin', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('swb-admin-nonce'),
                'pluginUrl' => SWB_PLUGIN_URL,
                'strings' => array(
                    'confirmDelete' => __('Sei sicuro di voler eliminare questo elemento?', 'wp-digital-italia'),
                    'confirmDeleteSlot' => __('Eliminare questo slot?', 'wp-digital-italia'),
                    'error' => __('Errore', 'wp-digital-italia'),
                    'success' => __('Operazione completata', 'wp-digital-italia'),
                )
            ));
        }
    }

    /**
     * Passa dati al JavaScript della pagina giorni chiusi
     *
     * @param string $current_month Mese corrente (formato Y-m)
     */
    private function enqueue_closed_days_data(string $current_month): void {
        wp_localize_script('swb-closed-days-js', 'swbClosedDays', array(
            'currentMonth' => esc_js($current_month)
        ));
    }

    /**
     * Aggiunge meta box al post type servizio
     */
    public function add_meta_boxes(): void
    {
        add_meta_box(
            'swb_service_slots',
            'Gestione Slot Appuntamenti',
            array($this, 'render_service_meta_box'),
            'servizio',
            'normal',
            'high'
        );
    }

    /**
     * Renderizza meta box nel servizio
     */
    public function render_service_meta_box($post): void
    {
        wp_nonce_field('swb_save_meta', 'swb_meta_nonce');

        $enabled = get_post_meta($post->ID, '_swb_slots_enabled', true);

        // Conta slot attivi (senza filtro per UO)
        $slots = $this->slot_manager->get_all_slots_by_service($post->ID, 0, true);
        $slot_count = count($slots);

        ?>
        <div class="swb-meta-box">
            <p>
                <label>
                    <input type="checkbox" name="swb_slots_enabled" value="1" <?php checked($enabled, '1'); ?>>
                    <strong>Abilita gestione slot per questo servizio</strong>
                </label>
            </p>

            <p><strong>Slot attivi futuri:</strong> <?php echo $slot_count; ?></p>

            <?php if ($enabled): ?>
                <hr>
                <h4>Azioni Rapide</h4>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=swb-generate-slots&service_id=' . $post->ID); ?>" class="button button-primary">
                        Genera Nuovi Slot
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=swb-slots&service_id=' . $post->ID); ?>" class="button">
                        Visualizza Tutti gli Slot
                    </a>
                </p>

                <?php if ($slot_count > 0): ?>
                    <hr>
                    <h4>Prossimi Slot Disponibili</h4>
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Ora</th>
                                <th>Prenotazioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $preview_slots = array_slice($slots, 0, 5);
                            foreach ($preview_slots as $slot):
                            ?>
                                <tr>
                                    <td><?php echo date_i18n('d/m/Y', strtotime($slot->slot_date)); ?></td>
                                    <td><?php echo substr($slot->slot_start_time, 0, 5); ?> - <?php echo substr($slot->slot_end_time, 0, 5); ?></td>
                                    <td><?php echo $slot->current_bookings; ?> / <?php echo $slot->max_bookings; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if ($slot_count > 5): ?>
                        <p><em>Mostrando 5 di <?php echo $slot_count; ?> slot...</em></p>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Salva meta box
     */
    public function save_service_slots_meta($post_id, $post): void
    {
        if (!isset($_POST['swb_meta_nonce']) || !wp_verify_nonce($_POST['swb_meta_nonce'], 'swb_save_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $enabled = isset($_POST['swb_slots_enabled']) ? '1' : '0';
        update_post_meta($post_id, '_swb_slots_enabled', $enabled);
    }

    /**
     * Genera intervalli di tempo in base a start, end e durata, escludendo la pausa
     * @throws Exception
     */
    private function generate_time_slots($start_time, $end_time, $duration_minutes, $break_start = null, $break_end = null): array
    {
        $slots = array();
        $current = new DateTime($start_time);
        $end = new DateTime($end_time);

        $break_start_obj = $break_start ? new DateTime($break_start) : null;
        $break_end_obj = $break_end ? new DateTime($break_end) : null;

        while ($current < $end) {
            $slot_start = clone $current;
            $current->modify("+{$duration_minutes} minutes");

            // Non aggiungere lo slot se supera l'orario di fine
            if ($current > $end) {
                break;
            }

            // Verifica se lo slot cade nella pausa pranzo
            $skip_slot = false;
            if ($break_start_obj && $break_end_obj) {
                // Se lo slot inizia durante la pausa o finisce durante la pausa, lo saltiamo
                if (($slot_start >= $break_start_obj && $slot_start < $break_end_obj) ||
                    ($current > $break_start_obj && $current <= $break_end_obj) ||
                    ($slot_start < $break_start_obj && $current > $break_end_obj)) {
                    $skip_slot = true;
                }
            }

            if (!$skip_slot) {
                $slots[] = array(
                    'start' => $slot_start->format('H:i'),
                    'end' => $current->format('H:i')
                );
            }
        }

        return $slots;
    }

    /**
     * Renderizza pagina genera slot
     * @throws Exception
     */
    public function render_generate_page(): void
    {
        $service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;

        // Gestione form submit
        if (isset($_POST['swb_generate_submit']) && check_admin_referer('swb_generate_slots', 'swb_generate_nonce')) {
            $service_id = intval($_POST['service_id']);
            $uo_id = 0; // Non più necessario, ma mantenuto per compatibilità
            $start_date = sanitize_text_field($_POST['start_date']);
            $end_date = sanitize_text_field($_POST['end_date']);
            $start_time = sanitize_text_field($_POST['start_time']);
            $end_time = sanitize_text_field($_POST['end_time']);
            $slot_duration = intval($_POST['slot_duration']);
            $max_bookings = intval($_POST['max_bookings']);
            $morning_days = isset($_POST['morning_days']) ? array_map('intval', $_POST['morning_days']) : array(1,2,3,4,5);
            $afternoon_days = isset($_POST['afternoon_days']) ? array_map('intval', $_POST['afternoon_days']) : array();

            // Gestione pausa pranzo
            $break_start = null;
            $break_end = null;
            if (isset($_POST['enable_break']) && $_POST['enable_break'] == '1') {
                $break_start = sanitize_text_field($_POST['break_start']);
                $break_end = sanitize_text_field($_POST['break_end']);
            }

            // Genera slot in base ai parametri (con o senza pausa)
            $config = array(
                'morning_slots' => $this->generate_time_slots($start_time, $end_time, $slot_duration, $break_start, $break_end),
                'afternoon_slots' => array(),
                'morning_days' => $morning_days,
                'afternoon_days' => $afternoon_days,
                'max_bookings_per_slot' => $max_bookings
            );

            $count = $this->slot_manager->generate_slots_for_service($service_id, $uo_id, $start_date, $end_date, $config);

            echo '<div class="notice notice-success"><p><strong>✓ Generati ' . $count . ' slot!</strong></p></div>';
        }

        // Lista servizi abilitati
        $args = array(
            'post_type' => 'servizio',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_swb_slots_enabled',
                    'value' => '1'
                )
            )
        );
        $servizi = get_posts($args);

        ?>
        <div class="wrap">
            <h1>Genera Slot Appuntamenti</h1>

            <form method="post" action="">
                <?php wp_nonce_field('swb_generate_slots', 'swb_generate_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th><label for="service_id">Servizio *</label></th>
                        <td>
                            <select name="service_id" id="service_id" required class="regular-text">
                                <option value="">-- Seleziona Servizio --</option>
                                <?php foreach ($servizi as $servizio): ?>
                                    <option value="<?php echo $servizio->ID; ?>" <?php selected($service_id, $servizio->ID); ?>>
                                        <?php echo $servizio->post_title; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Solo servizi con slot abilitati</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="uo_id">Unità Organizzativa *</label></th>
                        <td>
                            <select name="uo_id" id="uo_id" required class="regular-text">
                                <option value="0">-- Nessuna unità organizzativa --</option>
                            </select>
                            <p class="description">Non è più necessario selezionare un'unità organizzativa</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="start_date">Data Inizio *</label></th>
                        <td>
                            <input type="date" name="start_date" id="start_date" required
                                   value="<?php echo date('Y-m-d'); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="end_date">Data Fine *</label></th>
                        <td>
                            <input type="date" name="end_date" id="end_date" required
                                   value="<?php echo date('Y-m-d', strtotime('+3 months')); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="start_time">Orario Inizio *</label></th>
                        <td>
                            <input type="time" name="start_time" id="start_time" required
                                   value="09:00" class="regular-text">
                            <p class="description">Orario di inizio della giornata</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="end_time">Orario Fine *</label></th>
                        <td>
                            <input type="time" name="end_time" id="end_time" required
                                   value="12:00" class="regular-text">
                            <p class="description">Orario di fine della giornata</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="slot_duration">Durata Slot (minuti) *</label></th>
                        <td>
                            <input type="number" name="slot_duration" id="slot_duration" required
                                   value="45" min="15" max="120" step="15" class="regular-text">
                            <p class="description">Durata di ogni slot in minuti (es: 45)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="max_bookings">Posti per Slot *</label></th>
                        <td>
                            <input type="number" name="max_bookings" id="max_bookings" required
                                   value="1" min="1" max="50" class="regular-text">
                            <p class="description">Numero massimo di prenotazioni per ogni slot</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="enable_break">Pausa Pranzo</label></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_break" id="enable_break" value="1">
                                Abilita pausa pranzo
                            </label>
                            <div id="break_time_fields" style="display: none; margin-top: 10px;">
                                <label>Dalle: <input type="time" name="break_start" id="break_start" value="12:00" style="width: 100px;"></label>
                                <label style="margin-left: 10px;">Alle: <input type="time" name="break_end" id="break_end" value="14:00" style="width: 100px;"></label>
                            </div>
                            <p class="description">Durante la pausa non verranno generati slot</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Giorni Attivi</label></th>
                        <td>
                            <fieldset>
                                <label><input type="checkbox" name="morning_days[]" value="1" checked> Lunedì</label><br>
                                <label><input type="checkbox" name="morning_days[]" value="2" checked> Martedì</label><br>
                                <label><input type="checkbox" name="morning_days[]" value="3" checked> Mercoledì</label><br>
                                <label><input type="checkbox" name="morning_days[]" value="4" checked> Giovedì</label><br>
                                <label><input type="checkbox" name="morning_days[]" value="5" checked> Venerdì</label><br>
                            </fieldset>
                            <p class="description">Seleziona i giorni in cui generare gli slot</p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="swb_generate_submit" class="button button-primary" value="Genera Slot">
                </p>
            </form>

            <hr>

            <h3>ℹ️ Come Funziona</h3>
            <p>Il sistema genererà automaticamente gli slot in base ai parametri configurati sopra.</p>
            <p><strong>Esempio:</strong> Se imposti orario 09:00-12:00 con durata 45 minuti, verranno creati gli slot:</p>
            <ul>
                <li>09:00 - 09:45</li>
                <li>09:45 - 10:30</li>
                <li>10:30 - 11:15</li>
                <li>11:15 - 12:00</li>
            </ul>
            <p><em>Gli slot verranno generati solo per i giorni selezionati e nel range di date specificato.</em></p>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Gestione mostra/nascondi campi pausa pranzo
            $('#enable_break').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#break_time_fields').show();
                } else {
                    $('#break_time_fields').hide();
                }
            });

            $('#service_id').on('change', function() {
                var serviceId = $(this).val();
                if (!serviceId) {
                    $('#uo_id').html('<option value="0">-- Nessuna unità organizzativa --</option>');
                    return;
                }

                // Non più necessario recuperare UO dal servizio
                $('#uo_id').html('<option value="0">-- Nessuna unità organizzativa --</option>');
            });

            <?php if ($service_id): ?>
            $('#service_id').trigger('change');
            <?php endif; ?>
        });
        </script>
        <?php
    }

    /**
     * Renderizza pagina principale appuntamenti
     */
    public function render_appuntamenti_page(): void
    {
        ?>
        <div class="wrap">
            <h1>📅 Gestione Appuntamenti</h1>

            <div class="swb-dashboard">
                <div class="swb-dashboard-section">
                    <h2>Appuntamenti</h2>
                    <p>Gestisci le richieste di appuntamento ricevute.</p>
                    <a href="<?php echo admin_url('edit.php?post_type=appuntamento'); ?>" class="button button-primary">
                        Visualizza Tutti gli Appuntamenti
                    </a>
                </div>

                <div class="swb-dashboard-section">
                    <h2>Slot Disponibili</h2>
                    <p>Genera e gestisci gli slot di appuntamento per i servizi.</p>
                    <a href="<?php echo admin_url('admin.php?page=swb-generate-slots'); ?>" class="button button-secondary">
                        Genera Nuovi Slot
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=swb-calendar'); ?>" class="button button-secondary">
                        Visualizza Calendario
                    </a>
                </div>

                <div class="swb-dashboard-section">
                    <h2>Giorni di Chiusura</h2>
                    <p>Imposta giorni di chiusura globale per tutti i servizi.</p>
                    <a href="<?php echo admin_url('admin.php?page=swb-closed-days'); ?>" class="button button-secondary">
                        Gestisci Chiusure
                    </a>
                </div>
            </div>

            <style>
            .swb-dashboard {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            .swb-dashboard-section {
                background: #fff;
                border: 1px solid #e5e5e5;
                border-radius: 8px;
                padding: 20px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .swb-dashboard-section h2 {
                margin-top: 0;
                color: #23282d;
            }
            .swb-dashboard-section p {
                color: #666;
                margin-bottom: 15px;
            }
            .swb-dashboard-section .button {
                margin-right: 10px;
                margin-bottom: 5px;
            }
            </style>
        </div>
        <?php
    }

    /**
     * Renderizza pagina calendario slot
     */
    public function render_calendar_page(): void
    {
        // Lista servizi abilitati
        $args = array(
            'post_type' => 'servizio',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_swb_slots_enabled',
                    'value' => '1'
                )
            )
        );
        $servizi = get_posts($args);

        $service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;
        $current_month = isset($_GET['month']) ? sanitize_text_field($_GET['month']) : date('Y-m');

        ?>
        <div class="wrap swb-calendar-wrap">
            <h1>📅 Calendario Slot Appuntamenti</h1>

            <div class="swb-calendar-filters">
                <form method="get" action="" style="display: flex; gap: 15px; align-items: center; margin: 20px 0;">
                    <input type="hidden" name="page" value="swb-calendar">

                    <div>
                        <label for="service_id"><strong>Servizio:</strong></label>
                        <select name="service_id" id="service_id" required class="regular-text" onchange="this.form.submit()">
                            <option value="">-- Seleziona Servizio --</option>
                            <?php foreach ($servizi as $servizio): ?>
                                <option value="<?php echo $servizio->ID; ?>" <?php selected($service_id, $servizio->ID); ?>>
                                    <?php echo $servizio->post_title; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($service_id): ?>
                    <div>
                        <label for="month"><strong>Mese:</strong></label>
                        <input type="month" name="month" id="month" value="<?php echo esc_attr($current_month); ?>" onchange="this.form.submit()">
                    </div>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (!$service_id): ?>
                <div class="notice notice-info">
                    <p>👆 Seleziona un servizio per visualizzare il calendario</p>
                </div>
            <?php else:
                // FORZA caricamento CSS se non è già stato caricato
                if (!wp_style_is('swb-admin-css', 'enqueued')) {
                    echo '<!-- SWB: CSS non enqueued, carico manualmente -->';
                    echo '<link rel="stylesheet" href="' . SWB_PLUGIN_URL . 'assets/css/admin.css?v=' . time() . '">';
                }

                // Renderizza il calendario e ottieni i dati per il JavaScript
                $calendar_data = $this->calendar_view->render($service_id, $current_month);

                // Renderizza tutte le modali
                SWB_Modals::render_all_modals();

                // Renderizza il JavaScript inline
                $this->render_calendar_javascript($calendar_data);
            endif; ?>
        </div>
        <?php
    }

    /**
     * Renderizza il JavaScript per il calendario
     *
     * @param array $calendar_data Dati dal calendario (service_id, uo_id, month)
     */
    private function render_calendar_javascript(array $calendar_data): void {
        // Passa i dati al JavaScript tramite wp_localize_script
        wp_localize_script('swb-calendar-js', 'swbCalendar', array(
            'serviceId' => intval($calendar_data['service_id']),
            'uoId' => intval($calendar_data['uo_id']),
            'currentMonth' => esc_js($calendar_data['month'])
        ));
    }

    /**
     * Aggiungi filtri alla lista appuntamenti
     */
    public function add_appuntamento_filters($post_type): void
    {
        if ($post_type !== 'appuntamento') {
            return;
        }

        // Mostra messaggio se c'è un filtro attivo
        if (isset($_GET['swb_filter_slot'])) {
            global $wpdb;
            $slot_id = intval($_GET['swb_filter_slot']);
            $date = isset($_GET['swb_filter_date']) ? sanitize_text_field($_GET['swb_filter_date']) : '';
            $time = isset($_GET['swb_filter_time']) ? sanitize_text_field($_GET['swb_filter_time']) : '';

            $table_name = $wpdb->prefix . 'booking_slots';
            $slot = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $slot_id));

            if ($slot) {
                $service = get_post($slot->service_id);

                // Costruisci testo e link in modo sicuro
                $date_display = date_i18n('d/m/Y', strtotime($date));
                $service_title = $service ? esc_html($service->post_title) : '';
                $remove_url = esc_url(admin_url('edit.php?post_type=appuntamento'));

                // Banner compatto per filtro attivo
                echo '<div class="swb-filter-chip" style="display:inline-flex;align-items:center;gap:12px;padding:0 10px;background:#fff;border:1px solid #e6edf3;border-radius:24px;">';
                    echo '<span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:#00a0d2;color:#fff;font-weight:700;">📅</span>';
                    echo '<div style="line-height:1;">';
                        echo '<strong style="display:block;font-size:13px;margin-bottom:2px;">Filtro attivo</strong>';
                        echo '<span style="font-size:13px;color:#333;">' . esc_html($date_display) . ' — ' . esc_html($time);
                        if ($service_title) {
                            echo ' — ' . $service_title;
                        }
                        echo '</span>';
                    echo '</div>';
                    echo '<a href="' . $remove_url . '" class="button" style="margin-left:8px;white-space:nowrap;">Rimuovi filtro</a>';
                echo '</div>';
             }
        }
    }

    /**
     * Filtra gli appuntamenti per slot
     */
    public function filter_appuntamenti_by_slot($query): void
    {
        global $pagenow, $wpdb;

        if ($pagenow === 'edit.php' &&
            isset($_GET['post_type']) &&
            $_GET['post_type'] === 'appuntamento' &&
            isset($_GET['swb_filter_slot']) &&
            $query->is_main_query()) {

            $slot_id = intval($_GET['swb_filter_slot']);

            // Ottieni gli appuntamenti associati a questo slot
            $bookings_table = $wpdb->prefix . 'booking_reservations';

            $appuntamento_ids = $wpdb->get_col($wpdb->prepare(
                "SELECT appuntamento_id FROM $bookings_table WHERE slot_id = %d AND appuntamento_id IS NOT NULL",
                $slot_id
            ));

            if (empty($appuntamento_ids)) {
                // Nessuna prenotazione per questo slot, mostra risultato vuoto
                $query->set('post__in', array(0));
            } else {
                // Filtra per gli ID trovati
                $query->set('post__in', $appuntamento_ids);
            }
        }
        // Se è presente solo il filtro per data (es. clic su "Visualizza prenotazioni del giorno")
        elseif ($pagenow === 'edit.php' &&
            isset($_GET['post_type']) &&
            $_GET['post_type'] === 'appuntamento' &&
            isset($_GET['swb_filter_date']) &&
            $query->is_main_query()) {

            // Filtra gli appuntamenti che sono associati a slot nella data indicata
            $date = sanitize_text_field($_GET['swb_filter_date']);
            // Atteso formato YYYY-MM-DD
            $d = DateTime::createFromFormat('Y-m-d', $date);
            if ($d && $d->format('Y-m-d') === $date) {
                $bookings_table = $wpdb->prefix . 'booking_reservations';
                $slots_table = $wpdb->prefix . 'booking_slots';

                // Ottieni gli IDs degli appuntamenti associati a slot con quella data
                $appuntamento_ids = $wpdb->get_col($wpdb->prepare(
                    "SELECT br.appuntamento_id FROM $bookings_table br JOIN $slots_table s ON br.slot_id = s.id WHERE s.slot_date = %s AND br.appuntamento_id IS NOT NULL",
                    $date
                ));

                // Fallback: se non troviamo tramite la tabella prenotazioni, cerchiamo post con meta _swb_slot_id
                if (empty($appuntamento_ids)) {
                    // Ottieni slot IDs per la data
                    $slot_ids = $wpdb->get_col($wpdb->prepare(
                        "SELECT id FROM $slots_table WHERE slot_date = %s",
                        $date
                    ));

                    if (!empty($slot_ids)) {
                        $postmeta_table = $wpdb->prefix . 'postmeta';
                        // Crea lista di placeholder sicuri
                        $placeholders = implode(',', array_fill(0, count($slot_ids), '%d'));
                        $sql = $wpdb->prepare(
                            "SELECT post_id FROM $postmeta_table WHERE meta_key = '_swb_slot_id' AND meta_value IN ($placeholders)",
                            ...$slot_ids
                        );

                        $pm_post_ids = $wpdb->get_col($sql);

                        if (!empty($pm_post_ids)) {
                            $appuntamento_ids = array_map('intval', $pm_post_ids);
                        }
                    }
                }

                if (empty($appuntamento_ids)) {
                    $query->set('post__in', array(0));
                } else {
                    $query->set('post__in', $appuntamento_ids);
                }
            }
        }
    }

    /**
     * Aggiungi colonne personalizzate alla lista appuntamenti
     */
    public function add_appuntamento_columns($columns) {
        $new_columns = array();

        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;

            // Aggiungi colonne slot dopo il titolo
            if ($key === 'title') {
                $new_columns['swb_slot_info'] = 'Info Slot';
            }
        }

        return $new_columns;
    }

    /**
     * Riempi le colonne personalizzate
     */
    public function fill_appuntamento_columns($column, $post_id) {
        global $wpdb;

        if ($column === 'swb_slot_info') {
            $bookings_table = $wpdb->prefix . 'booking_reservations';
            $slots_table = $wpdb->prefix . 'booking_slots';

            // Trova lo slot associato a questo appuntamento
            $slot_booking = $wpdb->get_row($wpdb->prepare(
                "SELECT br.*, s.slot_date, s.slot_start_time, s.service_id 
                FROM $bookings_table br
                JOIN $slots_table s ON br.slot_id = s.id
                WHERE br.appuntamento_id = %d
                LIMIT 1",
                $post_id
            ));

            if ($slot_booking) {
                $service = get_post($slot_booking->service_id);
                $date_formatted = date_i18n('d/m/Y', strtotime($slot_booking->slot_date));
                $time_formatted = substr($slot_booking->slot_start_time, 0, 5);

                echo '<strong>' . $date_formatted . '</strong> alle <strong>' . $time_formatted . '</strong><br>';
                if ($service) {
                    echo '<small>' . $service->post_title . '</small>';
                }
            } else {
                echo '<em>Nessuno slot associato</em>';
            }
        }
    }

    /**
     * Renderizza la pagina per gestire i giorni di chiusura
     */
    public function render_closed_days_page(): void
    {
        $current_month = isset($_GET['month']) ? sanitize_text_field($_GET['month']) : date('Y-m');

        // Passa i dati al JavaScript
        $this->enqueue_closed_days_data($current_month);

        ?>
        <div class="wrap">
            <h1>Giorni di Chiusura (globali)</h1>

            <form method="get" action="" style="display:flex; gap:15px; align-items:center; margin-bottom:20px;">
                <input type="hidden" name="page" value="swb-closed-days">
                <div>
                    <label for="month"><strong>Mese:</strong></label>
                    <input type="month" name="month" id="month" value="<?php echo esc_attr($current_month); ?>" onchange="this.form.submit()">
                </div>
            </form>

            <div id="swbClosedDaysCalendar">
                <?php
                // Ottieni giorni chiusi per il mese e costruisci mappa per il rendering
                $closed_items = SWB_Closures_Manager::list_closed_days($current_month);
                $closed_by_date = array();
                foreach ($closed_items as $ci) {
                    if (!empty($ci['closed_date'])) {
                        $d = $ci['closed_date'];
                        if (!isset($closed_by_date[$d])) $closed_by_date[$d] = array();
                        $closed_by_date[$d][] = $ci;
                    }
                }

                // Calcola range mese
                list($y, $m) = explode('-', $current_month);
                $year = intval($y);
                $month = intval($m);
                $first_day = new DateTime(sprintf('%04d-%02d-01', $year, $month));
                $last_day = new DateTime($first_day->format('Y-m-t'));
                $today = new DateTime();

                // Navigazione mesi (prev/next) - stessa UI del calendario principale
                $prev_month = date('Y-m', strtotime($current_month . '-01 -1 month'));
                $next_month = date('Y-m', strtotime($current_month . '-01 +1 month'));
                ?>

                <div class="swb-month-navigation" style="display: flex; justify-content: center; align-items: center; gap: 20px; margin-bottom: 12px;">
                    <a href="?page=swb-closed-days&month=<?php echo esc_attr($prev_month); ?>" class="button button-secondary" style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 18px;">←</span>
                        <span><?php echo date_i18n('F Y', strtotime($prev_month . '-01')); ?></span>
                    </a>

                    <div style="font-weight: bold; font-size: 16px; color: #0073aa;">
                        <?php echo date_i18n('F Y', strtotime($current_month . '-01')); ?>
                    </div>

                    <a href="?page=swb-closed-days&month=<?php echo esc_attr($next_month); ?>" class="button button-secondary" style="display: flex; align-items: center; gap: 8px;">
                        <span><?php echo date_i18n('F Y', strtotime($next_month . '-01')); ?></span>
                        <span style="font-size: 18px;">→</span>
                    </a>
                </div>

                <div class="swb-calendar-grid">
                    <div class="swb-calendar-header">Lunedì</div>
                    <div class="swb-calendar-header">Martedì</div>
                    <div class="swb-calendar-header">Mercoledì</div>
                    <div class="swb-calendar-header">Giovedì</div>
                    <div class="swb-calendar-header">Venerdì</div>
                    <div class="swb-calendar-header">Sabato</div>
                    <div class="swb-calendar-header">Domenica</div>

                    <?php
                    $first_weekday = (int)$first_day->format('N');
                    for ($i = 1; $i < $first_weekday; $i++) {
                        echo '<div class="swb-calendar-day other-month"></div>';
                    }

                    $current_date = clone $first_day;
                    while ($current_date <= $last_day) {
                        $date_key = $current_date->format('Y-m-d');
                        $day_num = $current_date->format('j');
                        $weekday = (int)$current_date->format('N');
                        $is_weekend = ($weekday == 6 || $weekday == 7);
                        $is_today = ($current_date->format('Y-m-d') == $today->format('Y-m-d'));

                        $classes = array('swb-calendar-day');
                        if ($is_weekend) $classes[] = 'weekend';
                        if ($is_today) $classes[] = 'today';
                        if (isset($closed_by_date[$date_key])) $classes[] = 'closed';

                        echo '<div class="' . implode(' ', $classes) . '" data-date="' . esc_attr($date_key) . '">';
                        echo '<div class="swb-calendar-day-number">' . $day_num . '</div>';

                        if (isset($closed_by_date[$date_key])) {
                            echo '<div class="swb-closed-badge">Chiuso</div>';
                            foreach ($closed_by_date[$date_key] as $ci) {
                                if (!empty($ci['reason'])) {
                                    echo '<div class="swb-closed-reason">' . esc_html($ci['reason']) . '</div>';
                                }
                            }
                            // Pulsante per rimuovere (solo se chiuso) con icona
                            echo '<button class="swb-close-toggle swb-remove-day" data-date="' . esc_attr($date_key) . '" title="Rimuovi chiusura"><span class="dashicons dashicons-dismiss"></span></button>';
                        } else {
                            // Pulsante per aggiungere chiusura per la data con icona
                            echo '<button class="swb-close-toggle swb-add-day" data-date="' . esc_attr($date_key) . '" title="Aggiungi chiusura"><span class="dashicons dashicons-plus"></span></button>';
                        }

                        echo '</div>';

                        $current_date->modify('+1 day');
                    }

                    $last_weekday = (int)$last_day->format('N');
                    for ($i = $last_weekday; $i < 7; $i++) {
                        echo '<div class="swb-calendar-day other-month"></div>';
                    }
                    ?>
                </div>

                <!-- Navigazione mese (in basso) - stessa UI del calendario principale -->
                <div class="swb-month-navigation" style="display: flex; justify-content: center; align-items: center; gap: 20px; margin-top: 20px;">
                    <a href="?page=swb-closed-days&month=<?php echo esc_attr($prev_month); ?>" class="button button-secondary" style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 18px;">←</span>
                        <span><?php echo date_i18n('F Y', strtotime($prev_month . '-01')); ?></span>
                    </a>

                    <div style="font-weight: bold; font-size: 16px; color: #0073aa;">
                        <?php echo date_i18n('F Y', strtotime($current_month . '-01')); ?>
                    </div>

                    <a href="?page=swb-closed-days&month=<?php echo esc_attr($next_month); ?>" class="button button-secondary" style="display: flex; align-items: center; gap: 8px;">
                        <span><?php echo date_i18n('F Y', strtotime($next_month . '-01')); ?></span>
                        <span style="font-size: 18px;">→</span>
                    </a>
                </div>
            </div>

            <?php
            // Non servono più script inline: il file assets/js/closed-days.js gestisce tutto
            ?>
        </div>
        <?php
    }
}
