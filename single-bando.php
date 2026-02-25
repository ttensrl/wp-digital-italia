<?php
/**
 * Template per il singolo post type Bando
 */

get_header();

while (have_posts()) :
    the_post();
    $post_id = get_the_ID();

    $tipo                           = get_post_meta($post_id, '_dci_bando_tipo', true);
    $contratto                      = get_post_meta($post_id, '_dci_bando_contratto', true);
    $amministrazione_aggiudicatrice = get_post_meta($post_id, '_dci_bando_amministrazione_aggiudicatrice', true);
    $tipo_amministrazione           = get_post_meta($post_id, '_dci_bando_tipo_amministrazione', true);
    $importo                        = get_post_meta($post_id, '_dci_bando_importo', true);
    $cig                            = get_post_meta($post_id, '_dci_bando_cig', true);
    $cpv                            = get_post_meta($post_id, '_dci_bando_cpv', true);
    $data_publish                   = get_post_meta($post_id, '_dci_bando_data_publish', true);
    $data_scadenza                  = get_post_meta($post_id, '_dci_bando_data_scadenza', true);
    $allegati                       = get_post_meta($post_id, '_dci_bando_allegati', true);
    $termini                        = get_post_meta($post_id, '_dci_bando_termini', true);
    $data_aggiudicazione            = get_post_meta($post_id, '_dci_bando_data_aggiudicazione', true);
    $aggiudicatari                  = get_post_meta($post_id, '_dci_bando_aggiudicatari', true);

    $nav_sections = [];
    $nav_sections[] = ['id' => 'dettagli', 'label' => __('Dettagli', 'wp-digital-italia')];
    if (!empty($allegati)) {
        $nav_sections[] = ['id' => 'documentazione', 'label' => __('Documentazione', 'wp-digital-italia')];
    }
    if (!empty($aggiudicatari)) {
        $nav_sections[] = ['id' => 'aggiudicatari', 'label' => __('Aggiudicatari', 'wp-digital-italia')];
    }
    if (!empty($termini)) {
        $nav_sections[] = ['id' => 'termini', 'label' => __('Termini', 'wp-digital-italia')];
    }
    $nav_sections[] = ['id' => 'descrizione', 'label' => __('Descrizione', 'wp-digital-italia')];
    ?>

    <main id="main-content">

        <!-- Page Header -->
        <header class="page-header bg-white mb-4 py-4">
            <div class="container-fluid">
                <h1 class="mb-2"><?php the_title(); ?></h1>
                <?php if ($tipo) : ?>
                    <span class="badge bg-primary"><?php echo esc_html(ucfirst($tipo)); ?></span>
                <?php endif; ?>
                <?php if ($cig) : ?>
                    <span class="badge bg-secondary ms-2">[CIG: <?php echo esc_html($cig); ?>]</span>
                <?php endif; ?>
            </div>
        </header>

        <div class="container-fluid py-5">
            <div class="row g-4">

                <!-- Sidebar indice -->
                <aside class="col-12 col-lg-3" aria-label="<?php esc_attr_e('Indice della pagina', 'wp-digital-italia'); ?>">
                    <div class="sticky-top" style="top: 80px;">
                        <nav id="page-index" aria-label="Indice della pagina">
                            <p class="text-uppercase fw-semibold text-muted small mb-2 ps-3">
                                <?php _e('Indice della pagina', 'wp-digital-italia'); ?>
                            </p>
                            <ul class="nav flex-column border-start border-primary border-2">
                                <?php foreach ($nav_sections as $section) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link px-3 py-1 text-body text-decoration-none"
                                           href="#<?php echo esc_attr($section['id']); ?>">
                                            <?php echo esc_html($section['label']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </nav>
                    </div>
                </aside>

                <!-- Contenuto principale -->
                <div class="col-12 col-lg-9">

                    <!-- ===== DETTAGLI: griglia con card ===== -->
                    <section id="dettagli" class="mb-5 pb-4 border-bottom" aria-labelledby="dettagli-title">
                        <h2 id="dettagli-title" class="h4 mb-4">
                            <?php _e('Dettagli', 'wp-digital-italia'); ?>
                        </h2>

                        <?php if ($data_publish) : ?>
                            <p class="mb-3">
                                <strong><?php _e('Data di pubblicazione:', 'wp-digital-italia'); ?></strong>
                                <?php echo esc_html($data_publish); ?>
                            </p>
                        <?php endif; ?>

                        <div class="row g-3">
                            <?php if ($tipo) : ?>
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1"><?php _e('Tipo', 'wp-digital-italia'); ?></p>
                                    <p class="fw-semibold mb-0"><?php echo esc_html(ucfirst($tipo)); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($contratto) : ?>
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1"><?php _e('Contratto', 'wp-digital-italia'); ?></p>
                                    <p class="fw-semibold mb-0"><?php echo esc_html(ucfirst($contratto)); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($amministrazione_aggiudicatrice) : ?>
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1"><?php _e('Amministrazione Aggiudicatrice', 'wp-digital-italia'); ?></p>
                                    <p class="fw-semibold mb-0"><?php echo esc_html($amministrazione_aggiudicatrice); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($tipo_amministrazione) : ?>
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1"><?php _e('Tipo di Amministrazione', 'wp-digital-italia'); ?></p>
                                    <p class="fw-semibold mb-0"><?php echo esc_html($tipo_amministrazione); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($importo) : ?>
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1"><?php _e('Importo', 'wp-digital-italia'); ?></p>
                                    <p class="fw-semibold mb-0"><?php echo esc_html($importo); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($cpv) : ?>
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1"><?php _e('Codice CPV', 'wp-digital-italia'); ?></p>
                                    <p class="fw-semibold mb-0"><?php echo esc_html($cpv); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($data_scadenza) : ?>
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1"><?php _e('Data di scadenza del bando', 'wp-digital-italia'); ?></p>
                                    <p class="fw-semibold mb-0 text-danger"><?php echo esc_html($data_scadenza); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- ===== DOCUMENTAZIONE: stile MEF ===== -->
                    <?php if (!empty($allegati) && is_array($allegati)) : ?>
                        <section id="documentazione" class="mb-5 pb-4 border-bottom" aria-labelledby="documentazione-title">
                            <h2 id="documentazione-title" class="h4 mb-4">
                                <?php _e('Documentazione', 'wp-digital-italia'); ?>
                            </h2>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($allegati as $allegato) :
                                    $allegato_titolo = $allegato['titolo'] ?? '';
                                    $allegato_data   = $allegato['data'] ?? '';
                                    $allegato_file   = $allegato['file'] ?? '';
                                    if (!$allegato_file) continue;

                                    $file_url  = $allegato_file;
                                    $file_name = $allegato_titolo ?: basename($file_url);
                                    ?>
                                    <li class="py-3 border-bottom">
                                        <?php if ($allegato_data) : ?>
                                            <strong class="text-dark text-uppercase">
                                                <?php echo esc_html($allegato_data); ?>
                                            </strong>
                                            &ndash;
                                        <?php endif; ?>
                                        <a href="<?php echo esc_url($file_url); ?>"
                                           class="text-decoration-none d-inline-flex align-items-center gap-2"
                                           target="_blank"
                                           rel="noopener noreferrer">
                                            <!-- Icona file SVG -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor"
                                                 class="flex-shrink-0 text-primary" viewBox="0 0 16 16" aria-hidden="true">
                                                <path d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0H4zm0 1h5v3.5A1.5 1.5 0 0 0 10.5 6H14v8a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zm6 0v3h3l-3-3z"/>
                                            </svg>
                                            <span class="text-primary"><?php echo esc_html($file_name); ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </section>
                    <?php endif; ?>

                    <!-- ===== AGGIUDICATARI ===== -->
                    <?php if (!empty($aggiudicatari) && is_array($aggiudicatari)) : ?>
                        <section id="aggiudicatari" class="mb-5 pb-4 border-bottom" aria-labelledby="aggiudicatari-title">
                            <h2 id="aggiudicatari-title" class="h4 mb-4">
                                <?php _e('Aggiudicatari', 'wp-digital-italia'); ?>
                            </h2>

                            <?php if ($data_aggiudicazione) : ?>
                                <p class="mb-4">
                                    <strong><?php _e('Data di aggiudicazione in VIA DEFINITIVA EFFICACE:', 'wp-digital-italia'); ?></strong>
                                    <?php echo esc_html($data_aggiudicazione); ?>
                                </p>
                            <?php endif; ?>

                            <?php foreach ($aggiudicatari as $aggiudicatario) :
                                $lotto = $aggiudicatario['lotto'] ?? '';
                                $valore = $aggiudicatario['valore'] ?? '';
                                $aggiudicatari_list = $aggiudicatario['aggiudicatari'] ?? '';
                                ?>
                                <div class="mb-4">
                                    <?php if ($lotto) : ?>
                                        <p class="fw-semibold mb-2">
                                            <?php echo esc_html($lotto); ?>
                                            <?php if ($valore) : ?>
                                                – <?php _e('Valore di aggiudicazione pari a', 'wp-digital-italia'); ?> <?php echo esc_html($valore); ?>
                                            <?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if ($aggiudicatari_list) : ?>
                                        <ol class="ps-4 mb-0">
                                            <?php
                                            $lines = explode("\n", $aggiudicatari_list);
                                            foreach ($lines as $line) {
                                                $line = trim($line);
                                                if (!empty($line)) {
                                                    echo '<li>' . esc_html($line) . '</li>';
                                                }
                                            }
                                            ?>
                                        </ol>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </section>
                    <?php endif; ?>

                    <!-- ===== TERMINI: stile MEF ===== -->
                    <?php if (!empty($termini) && is_array($termini)) : ?>
                        <section id="termini" class="mb-5 pb-4 border-bottom" aria-labelledby="termini-title">
                            <h2 id="termini-title" class="h4 mb-4">
                                <?php _e('Termini', 'wp-digital-italia'); ?>
                            </h2>

                            <p class="fw-semibold text-uppercase small mb-3">
                                <?php _e('Estremi di pubblicazione', 'wp-digital-italia'); ?>
                            </p>

                            <ul class="list-unstyled mb-0">
                                <?php foreach ($termini as $termine) :
                                    $termine_titolo = $termine['titolo'] ?? '';
                                    $termine_data   = $termine['data'] ?? '';
                                    ?>
                                    <li class="d-flex align-items-start mb-3">
                                        <!-- Icona calendario SVG -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                             class="me-2 flex-shrink-0 text-secondary mt-1" viewBox="0 0 16 16" aria-hidden="true">
                                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v1h14V3a1 1 0 0 0-1-1H2zm13 3H1v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V5z"/>
                                        </svg>
                                        <span>
                                            <?php if ($termine_titolo) : ?>
                                                <?php echo esc_html($termine_titolo); ?>:
                                            <?php endif; ?>
                                            <?php if ($termine_data) : ?>
                                                <strong><?php echo esc_html($termine_data); ?></strong>
                                            <?php endif; ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </section>
                    <?php endif; ?>

                    <!-- ===== DESCRIZIONE ===== -->
                    <section id="descrizione" class="mb-5" aria-labelledby="descrizione-title">
                        <h2 id="descrizione-title" class="h4 mb-4">
                            <?php _e('Descrizione', 'wp-digital-italia'); ?>
                        </h2>
                        <div class="entry-content">
                            <?php
                            the_content();
                            wp_link_pages([
                                    'before' => '<div class="page-links">' . esc_html__('Pagine:', 'wp-digital-italia'),
                                    'after'  => '</div>',
                            ]);
                            ?>
                        </div>
                    </section>

                </div><!-- /.col-lg-9 -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </main>

<?php endwhile; ?>

<?php get_footer();