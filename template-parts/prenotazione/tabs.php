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
$currentYear  = intval(date('Y'));

for ($i = 0; $i < 12; $i++) {
    $months[] = array(
            'value' => $currentMonth,
            'year'  => $currentYear,
            'label' => date_i18n('F Y', mktime(0, 0, 0, $currentMonth, 1, $currentYear))
    );
    $currentMonth++;
    if ($currentMonth > 12) {
        $currentMonth = 1;
        $currentYear++;
    }
}
?>

<?php
// Recupera tutti i post di tipo 'servizio'
$servizi = get_posts(array(
        'post_type'      => 'servizio',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
));
?>

<!-- ============================================================
     STEPPER – Prenotazione appuntamento
     Conforme alle linee guida Bootstrap Italia 2.x
     ============================================================ -->
<div class="steppers" id="booking-stepper">

    <!-- ── Header stepper ── -->
    <div class="steppers-header">
        <ul>
            <li data-step="1" class="active" aria-current="step">
                <span class="steppers-number">1</span>
                Servizio e data
                <svg class="icon steppers-success" aria-hidden="true"><use href="#it-check"></use></svg>
                <span class="visually-hidden">Passo attivo</span>
            </li>
            <li data-step="2">
                <span class="steppers-number">2</span>
                Richiedente
            </li>
            <li data-step="3">
                <span class="steppers-number">3</span>
                Riepilogo
            </li>
        </ul>
        <span class="steppers-index" aria-hidden="true">1 / 3</span>
    </div>

    <!-- ── Contenuto step ── -->
    <div class="steppers-content" aria-live="polite">

        <!-- ══════════════════════════════════════
             STEP 1 – Servizio, mese, giorno, orario
             ══════════════════════════════════════ -->
        <div data-steps="1" class="active" role="group" aria-labelledby="step1-heading">

            <h2 id="step1-heading" class="h4 mb-4">Scegli il servizio e la disponibilità</h2>

            <!-- Selezione servizio -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">

                    <div class="form-group">
                        <label for="booking-service" class="active">
                            Servizio <span class="text-danger" aria-hidden="true">*</span>
                            <span class="visually-hidden">(obbligatorio)</span>
                        </label>
                        <div class="select-wrapper">
                            <select
                                    id="booking-service"
                                    name="servizio"
                                    class="form-control"
                                    required
                                    aria-required="true"
                                    aria-describedby="booking-service-hint"
                            >
                                <option value=""><?php echo esc_html__('Seleziona un servizio', 'wp-digital-italia'); ?></option>
                                <?php if (!empty($servizi)) : ?>
                                    <?php foreach ($servizi as $servizio) : ?>
                                        <option
                                                value="<?php echo esc_attr($servizio->ID); ?>"
                                                <?php selected($preselected_service_id, $servizio->ID); ?>
                                        >
                                            <?php echo esc_html(get_the_title($servizio->ID)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <option value="" disabled><?php echo esc_html__('Nessun servizio disponibile', 'wp-digital-italia'); ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <small id="booking-service-hint" class="form-text">
                            Seleziona il servizio per cui desideri prenotare un appuntamento.
                        </small>
                    </div>

                </div>
            </div>

            <!-- Selezione mese, giorno e orario (visibile dopo selezione servizio) -->
            <div class="card shadow-sm mb-4" id="appointment-available">
                <div class="card-body p-4">

                    <!-- Mese -->
                    <div class="form-group">
                        <label for="booking-month" class="active">
                            Mese <span class="text-danger" aria-hidden="true">*</span>
                            <span class="visually-hidden">(obbligatorio)</span>
                        </label>
                        <div class="select-wrapper">
                            <select
                                    id="booking-month"
                                    class="form-control"
                                    required
                                    aria-required="true"
                                    disabled
                            >
                                <option value=""><?php echo esc_html__('Seleziona prima il servizio', 'wp-digital-italia'); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Calendario Giorni -->
                    <div id="calendar-wrapper" class="mt-4 d-none">
                        <h3 id="calendar-title" class="h6 mb-3 d-none">Seleziona un giorno</h3>
                        <div id="calendar-grid" class="calendar-grid" role="group"
                             aria-label="Calendario giorni disponibili">
                            <!-- Giorni popolati dinamicamente -->
                        </div>
                    </div>

                    <!-- Griglia Orari -->
                    <div id="slots-wrapper" class="mt-4 d-none">
                        <h3 class="h6 mb-3">Seleziona un orario</h3>
                        <div id="slots-grid" class="slots-grid" role="radiogroup" aria-label="Orari disponibili">
                            <!-- Slot popolati dinamicamente -->
                        </div>
                    </div>

                </div>
            </div>

        </div><!-- /step 1 -->


        <!-- ══════════════════════════════════════
             STEP 2 – Dati richiedente
             ══════════════════════════════════════ -->
        <div data-steps="2" class="d-none" role="group" aria-labelledby="step2-heading">

            <h2 id="step2-heading" class="h4 mb-4">Dati del richiedente</h2>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">

                    <p class="text-muted mb-4">
                        I campi contrassegnati con <span class="text-danger" aria-hidden="true">*</span>
                        <span class="visually-hidden">asterisco</span> sono obbligatori.
                    </p>

                    <!-- Nome + Cognome su riga -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <div class="form-group mb-0">
                                <label for="applicant-name">
                                    Nome <span class="text-danger" aria-hidden="true">*</span>
                                    <span class="visually-hidden">(obbligatorio)</span>
                                </label>
                                <input
                                        type="text"
                                        id="applicant-name"
                                        name="nome"
                                        class="form-control"
                                        autocomplete="given-name"
                                        required
                                        aria-required="true"
                                />
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group mb-0">
                                <label for="applicant-surname">
                                    Cognome <span class="text-danger" aria-hidden="true">*</span>
                                    <span class="visually-hidden">(obbligatorio)</span>
                                </label>
                                <input
                                        type="text"
                                        id="applicant-surname"
                                        name="cognome"
                                        class="form-control"
                                        autocomplete="family-name"
                                        required
                                        aria-required="true"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Email + Telefono su riga -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <div class="form-group mb-0">
                                <label for="applicant-email">
                                    Indirizzo e-mail <span class="text-danger" aria-hidden="true">*</span>
                                    <span class="visually-hidden">(obbligatorio)</span>
                                </label>
                                <input
                                        type="email"
                                        id="applicant-email"
                                        name="email"
                                        class="form-control"
                                        autocomplete="email"
                                        inputmode="email"
                                        required
                                        aria-required="true"
                                        aria-describedby="applicant-email-hint"
                                />
                                <small id="applicant-email-hint" class="form-text">
                                    La conferma di prenotazione verrà inviata a questo indirizzo.
                                </small>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group mb-0">
                                <label for="applicant-phone">Numero di telefono</label>
                                <input
                                        type="tel"
                                        id="applicant-phone"
                                        name="telefono"
                                        class="form-control"
                                        autocomplete="tel"
                                        inputmode="tel"
                                        aria-describedby="applicant-phone-hint"
                                />
                                <small id="applicant-phone-hint" class="form-text">Facoltativo.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Messaggio -->
                    <div class="form-group mb-0">
                        <label for="applicant-message">Note o richieste particolari</label>
                        <textarea
                                id="applicant-message"
                                name="messaggio"
                                class="form-control"
                                rows="4"
                                aria-describedby="applicant-message-hint"
                        ></textarea>
                        <small id="applicant-message-hint" class="form-text">
                            Facoltativo. Massimo 500 caratteri.
                        </small>
                    </div>

                </div>
            </div>

        </div><!-- /step 2 -->


        <!-- ══════════════════════════════════════
             STEP 3 – Riepilogo
             ══════════════════════════════════════ -->
        <div data-steps="3" class="d-none" role="group" aria-labelledby="step3-heading">

            <h2 id="step3-heading" class="h4 mb-4">Riepilogo della prenotazione</h2>

            <p class="text-muted mb-4">
                Controlla i dati inseriti prima di inviare la richiesta.
                Se vuoi modificare qualcosa, usa il pulsante <strong>Indietro</strong>.
            </p>

            <!-- Sezione: Appuntamento -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <svg class="icon icon-white icon-sm" aria-hidden="true"><use href="#it-calendar"></use></svg>
                        <span class="fw-semibold">Appuntamento</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <dl class="mb-0">

                        <div class="d-flex align-items-start px-4 py-3 border-bottom">
                            <dt class="text-muted col-5 col-md-3 mb-0 fw-normal">Servizio</dt>
                            <dd id="review-service" class="col mb-0 fw-semibold">—</dd>
                        </div>

                        <div class="d-flex align-items-start px-4 py-3 border-bottom">
                            <dt class="text-muted col-5 col-md-3 mb-0 fw-normal">Data</dt>
                            <dd id="review-date" class="col mb-0 fw-semibold">—</dd>
                        </div>

                        <div class="d-flex align-items-start px-4 py-3">
                            <dt class="text-muted col-5 col-md-3 mb-0 fw-normal">Orario</dt>
                            <dd id="review-time" class="col mb-0 fw-semibold">—</dd>
                        </div>

                    </dl>
                </div>
            </div>

            <!-- Sezione: Richiedente -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <svg class="icon icon-white icon-sm" aria-hidden="true"><use href="#it-user"></use></svg>
                        <span class="fw-semibold">Richiedente</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <dl class="mb-0">

                        <div class="d-flex align-items-start px-4 py-3 border-bottom">
                            <dt class="text-muted col-5 col-md-3 mb-0 fw-normal">Nome</dt>
                            <dd id="review-name" class="col mb-0 fw-semibold">—</dd>
                        </div>

                        <div class="d-flex align-items-start px-4 py-3 border-bottom">
                            <dt class="text-muted col-5 col-md-3 mb-0 fw-normal">Cognome</dt>
                            <dd id="review-surname" class="col mb-0 fw-semibold">—</dd>
                        </div>

                        <div class="d-flex align-items-start px-4 py-3 border-bottom">
                            <dt class="text-muted col-5 col-md-3 mb-0 fw-normal">E-mail</dt>
                            <dd id="review-email" class="col mb-0 fw-semibold">—</dd>
                        </div>

                        <div class="d-flex align-items-start px-4 py-3 border-bottom">
                            <dt class="text-muted col-5 col-md-3 mb-0 fw-normal">Telefono</dt>
                            <dd id="review-phone" class="col mb-0 fw-semibold text-muted fst-italic" data-empty="Non fornito">—</dd>
                        </div>

                        <div class="d-flex align-items-start px-4 py-3">
                            <dt class="text-muted col-5 col-md-3 mb-0 fw-normal">Note</dt>
                            <dd id="review-message" class="col mb-0 fw-semibold text-muted fst-italic" data-empty="Nessuna nota">—</dd>
                        </div>

                    </dl>
                </div>
            </div>

            <!-- Avviso privacy -->
            <div class="alert alert-info d-flex gap-3 align-items-start" role="alert">
                <svg class="icon icon-info flex-shrink-0 mt-1" aria-hidden="true"><use href="#it-info-circle"></use></svg>
                <div>
                    I dati inseriti verranno trattati nel rispetto del Regolamento (UE) 2016/679
                    (GDPR) e del Codice in materia di protezione dei dati personali.
                    <a href="/privacy" class="alert-link">Informativa sulla privacy</a>.
                </div>
            </div>

        </div><!-- /step 3 -->

    </div><!-- /steppers-content -->

    <!-- Feedback area per messaggi di validazione (aria-live) -->
    <div id="booking-feedback" class="text-danger small visually-hidden px-4 mt-2" role="status" aria-live="polite"></div>

    <!-- ── Navigazione stepper ── -->
    <nav class="steppers-nav mt-4" aria-label="Navigazione tra i passi">

        <button
                type="button"
                class="btn btn-outline-primary btn-sm steppers-btn-prev btn-back-step"
                disabled
                aria-label="Torna al passo precedente"
        >
            <svg class="icon icon-primary me-1" aria-hidden="true">
                <use href="#it-chevron-left"></use>
            </svg>
            Indietro
        </button>

        <div class="steppers-dots d-none d-lg-flex gap-2" aria-hidden="true">
            <span class="steppers-dot active"></span>
            <span class="steppers-dot"></span>
            <span class="steppers-dot"></span>
        </div>

        <button
                type="button"
                class="btn btn-primary btn-sm steppers-btn-next btn-next-step"
                aria-label="Vai al passo successivo"
                disabled
        >
            Avanti
            <svg class="icon icon-white ms-1" aria-hidden="true">
                <use href="#it-chevron-right"></use>
            </svg>
        </button>

        <button
                type="button"
                class="btn btn-primary btn-sm steppers-btn-confirm d-none"
                aria-label="Invia la prenotazione"
        >
            <svg class="icon icon-white me-1" aria-hidden="true">
                <use href="#it-check"></use>
            </svg>
            Invia prenotazione
        </button>

    </nav>

</div><!-- /booking-stepper -->