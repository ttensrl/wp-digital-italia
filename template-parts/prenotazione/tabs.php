<?php
// Leggi i parametri GET direttamente dall'URL (se presenti)
$preselected_service_id = isset($_GET['servizio_id']) && !empty($_GET['servizio_id']) ? intval($_GET['servizio_id']) : null;

// Avvia la sessione solo per dati utente SPID/CIE (se necessario)
if (!session_id()) {
    session_start();
}
$user_data = $_SESSION['govauth_user_data'] ?? array();

// Genera i prossimi 12 mesi partendo dal mese corrente
$months = array();
$currentMonth = intval(date('m'));
$currentYear = intval(date('Y'));

for ($i = 0; $i < 12; $i++) {
    $months[] = array(
        'value' => $currentMonth,
        'year' => $currentYear,
        'label' => date_i18n('F Y', mktime(0, 0, 0, $currentMonth, 1, $currentYear))
    );

    $currentMonth++;
    if ($currentMonth > 12) {
        $currentMonth = 1;
        $currentYear++;
    }
}
?>

<div class="steppers">

    <!-- Header: i tab -->
    <div class="steppers-header">
        <ul>
            <li data-step="1" class="active">
                Luogo
                <svg class="icon steppers-success" aria-hidden="true"><use href="#it-check"></use></svg>
                <span class="visually-hidden">Attivo</span>
            </li>
            <li data-step="2">Data e orario</li>
            <li data-step="3">Riepilogo</li>
        </ul>
        <span class="steppers-index" aria-hidden="true">1/3</span>
    </div>

    <!-- Contenuto: i tuoi step esistenti -->
    <div class="steppers-content" aria-live="polite">

        <div data-steps="1" class="active">
            <!-- step 1 -->
            <div class="select-wrapper">
                <label for="defaultSelect">Servizio*</label>
                <?php
                // Recupera tutti i post di tipo 'servizio'
                $servizi = get_posts( array(
                    'post_type'      => 'servizio',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                ) );
                ?>
                <select id="defaultSelect" name="servizio">
                    <option value=""><?php echo esc_html__("Seleziona un servizio", 'wp-digital-italia'); ?></option>
                    <?php if ( ! empty( $servizi ) ) : ?>
                        <?php foreach ( $servizi as $servizio ) : ?>
                            <option value="<?php echo esc_attr( $servizio->ID ); ?>"><?php echo esc_html( get_the_title( $servizio->ID ) ); ?></option>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <option value=""><?php echo esc_html__( 'Nessun servizio disponibile', 'wp-digital-italia' ); ?></option>
                    <?php endif; ?>
                </select>
            </div>
            
            <!-- Appuntamenti disponibili -->
            <div class="cmp-card mb-40" id="appointment-available">
                <div class="card has-bkg-grey shadow-sm p-big">
                    <div class="card-header border-0 p-0 mb-lg-30">
                        <div class="d-flex">
                            <h2 class="mb-2">
                                Appuntamenti disponibili*
                            </h2>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <!-- Select Mese -->
                        <div class="select-wrapper p-0 mt-1 select-partials">
                            <label for="appointment-month" class="visually-hidden">
                                Seleziona un mese
                            </label>
                            <select id="appointment-month" class="">
                                <option selected="selected" value="">
                                    Seleziona prima il servizio
                                </option>
                            </select>
                        </div>

                        <!-- Calendario Giorni -->
                        <div id="calendar-wrapper" style="display: none;" class="mt-4">
                            <h3 class="h6 mb-3">Seleziona un giorno</h3>
                            <div id="calendar-grid" class="calendar-grid" role="group" aria-label="Calendario giorni disponibili">
                                <!-- Giorni popolati dinamicamente -->
                            </div>
                        </div>

                        <!-- Griglia Orari -->
                        <div id="slots-wrapper" style="display: none;" class="mt-4">
                            <h3 class="h6 mb-3">Seleziona un orario</h3>
                            <div id="slots-grid" class="slots-grid" role="radiogroup" aria-label="Orari disponibili">
                                <!-- Slot popolati dinamicamente -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div data-steps="2" class="d-none">
            <!-- step 2 -->
        </div>
        <div data-steps="3" class="d-none">
            <!-- step 3 - Riepilogo -->
        </div>

    </div>

    <!-- Navigazione -->
    <nav class="steppers-nav">
        <button type="button" class="btn btn-outline-primary btn-sm steppers-btn-prev btn-back-step" disabled>
            <svg class="icon icon-primary"><use href="#it-chevron-left"></use></svg>
            Indietro
        </button>
        <button type="button" class="btn btn-outline-primary btn-sm steppers-btn-next btn-next-step">
            Avanti
            <svg class="icon icon-primary"><use href="#it-chevron-right"></use></svg>
        </button>
        <button type="button" class="btn btn-primary btn-sm steppers-btn-confirm d-none d-lg-block">
            Invia
        </button>
    </nav>

</div>

<script>
// Inizializzazione di base - la logica principale è in booking.js
document.addEventListener('DOMContentLoaded', function() {
    // Disabilita inizialmente il select dei mesi fino a quando un servizio non viene selezionato
    document.getElementById('appointment-month').disabled = true;
    
    // Preseleziona il servizio se presente nell'URL
    const preselectedService = '<?php echo $preselected_service_id ?? ''; ?>';
    const serviceSelect = document.getElementById('defaultSelect');
    
    if (preselectedService && serviceSelect) {
        serviceSelect.value = preselectedService;
    }
});
</script>