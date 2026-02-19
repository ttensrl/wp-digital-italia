<?php
    // Leggi i parametri GET direttamente dall'URL (se presenti)
    $preselected_uo_id = isset($_GET['uo_id']) && !empty($_GET['uo_id']) ? intval($_GET['uo_id']) : null;
    $preselected_service_id = isset($_GET['servizio_id']) && !empty($_GET['servizio_id']) ? intval($_GET['servizio_id']) : null;

    // Avvia la sessione solo per dati utente SPID/CIE (se necessario)
    if (!session_id()) {
        session_start();
    }
    $user_data = isset($_SESSION['govauth_user_data']) ? $_SESSION['govauth_user_data'] : array();

    // Log per debug
    if ($preselected_uo_id) {
        error_log('Prenotazione: UO da URL: ' . $preselected_uo_id);
    }
    if ($preselected_service_id) {
        error_log('Prenotazione: Servizio da URL: ' . $preselected_service_id);
    }

    $uffici = get_posts(array(
        'posts_per_page' => -1,
        'post_type' => 'unita_organizzativa'
    ));

    // Filtra solo le unità organizzative che offrono almeno un servizio
    $uffici = dci_filter_uo_with_services($uffici);

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

<div class="it-page-sections-container">

    <!-- Step 1: SERVIZIO + UFFICIO -->
    <section class="firstStep page-step active it-page-section" data-steps="1">

        <!-- Ufficio -->
        <div class="cmp-card mb-40" id="office">
            <div class="card has-bkg-grey shadow-sm p-big">
                <div class="card-header border-0 p-0 mb-lg-30">
                    <div class="d-flex">
                        <h2 class="title-xxlarge mb-0">Ufficio*</h2>
                    </div>
                    <p class="subtitle-small mb-0">
                        Scegli l'ufficio a cui vuoi richiedere l'appuntamento
                    </p>
                </div>
                <div class="card-body p-0">
                    <div class="select-wrapper p-0 select-partials">
                        <label for="office-choice" class="visually-hidden">
                            Tipo di ufficio
                        </label>
                        <select id="office-choice" class="" <?php echo $preselected_uo_id ? 'data-preselected="' . $preselected_uo_id . '"' : ''; ?>>
                            <option value="">
                                Seleziona opzione
                            </option>
                            <?php foreach ($uffici as $uo_id) {
                                $ufficio = get_post($uo_id);
                                $selected = ($preselected_uo_id && $preselected_uo_id == $ufficio->ID) ? 'selected="selected"' : '';
                                echo '<option value="'.$ufficio->ID.'" '.$selected.'>'.$ufficio->post_title.'</option>';
                            } ?>
                        </select>
                    </div>
                    <fieldset id="place-cards-wrapper"></fieldset>
                </div>
            </div>
        </div>

        <!-- Servizio -->
        <div class="cmp-card mb-40" id="service">
            <div class="card has-bkg-grey shadow-sm p-big">
                <div class="card-header border-0 p-0 mb-lg-30">
                    <div class="d-flex">
                        <h2 class="title-xxlarge mb-0">Servizio*</h2>
                    </div>
                    <p class="subtitle-small mb-0">
                        Scegli il servizio per cui vuoi prenotare un appuntamento
                    </p>
                </div>
                <div class="card-body p-0">
                    <div class="select-wrapper p-0 select-partials">
                        <label for="motivo-appuntamento" class="visually-hidden">
                            Servizio
                        </label>
                        <select id="motivo-appuntamento" class=""
                                <?php if ($preselected_service_id): ?>
                                data-preselected-service="<?php echo $preselected_service_id; ?>"
                                <?php endif; ?>>
                            <option value="">
                                Seleziona un servizio
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Step 2: DATA APPUNTAMENTO -->

    <!-- Step 2: DATA APPUNTAMENTO -->
    <section class="d-none page-step it-page-section" data-steps="2">
        <div class="cmp-card mb-40" id="appointment-available" >
            <div class="card has-bkg-grey shadow-sm p-big">
                <div class="card-header border-0 p-0 mb-lg-30">
                    <div class="d-flex">
                    <h2 class="title-xxlarge mb-2">
                        Appuntamenti disponibili*
                    </h2>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Select Mese (popolata dinamicamente dopo selezione ufficio/servizio) -->
                    <div class="select-wrapper p-0 mt-1 select-partials">
                        <label for="appointment-month" class="visually-hidden">
                            Seleziona un mese
                        </label>
                        <select id="appointment-month" class="">
                            <option selected="selected" value="">
                                Seleziona prima ufficio e servizio
                            </option>
                        </select>
                    </div>

                    <!-- Calendario Giorni (popolato dinamicamente) -->
                    <div id="calendar-wrapper" style="display: none;" class="mt-4">
                        <h3 class="h6 mb-3">Seleziona un giorno</h3>
                        <div id="calendar-grid" class="calendar-grid" role="group" aria-label="Calendario giorni disponibili">
                            <!-- Giorni popolati dinamicamente -->
                        </div>
                    </div>

                    <!-- Griglia Orari (popolata dinamicamente) -->
                    <div id="slots-wrapper" style="display: none;" class="mt-4">
                        <h3 class="h6 mb-3">Seleziona un orario</h3>
                        <div id="slots-grid" class="slots-grid" role="radiogroup" aria-label="Orari disponibili">
                            <!-- Slot popolati dinamicamente -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Step 3: DETTAGLI -->
    <section class="d-none page-step it-page-section" data-steps="3">
        <div class="cmp-card mb-40" id="details">
            <div class="card has-bkg-grey shadow-sm p-big">
                <div class="card-header border-0 p-0 mb-lg-30 m-0">
                    <div class="d-flex">
                        <h2 class="title-xxlarge mb-0" >
                            Dettagli
                        </h2>
                    </div>
                    <p class="subtitle-small mb-0 mb-3">
                        Aggiungi ulteriori dettagli (opzionale)
                    </p>
                </div>
                <div class="card-body p-0">
                    <div class="cmp-text-area p-0">
                        <div class="form-group">
                            <label for="form-details" class="visually-hidden">
                                Aggiungi ulteriori dettagli
                            </label>
                            <textarea
                                class="form-control text-area"
                                id="form-details"
                                rows="2"
                            ></textarea>
                            <span class="label">
                                Inserire massimo 200 caratteri
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- truncated for brevity in copy -->


