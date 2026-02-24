<?php
/**
 * Template per il singolo post type Dipartimento
 * Layout ispirato a Bootstrap Italia / modello Comuni
 */

get_header();

while (have_posts()) :
    the_post();
    $post_id      = get_the_ID();
    $email        = get_post_meta($post_id, '_dci_dipartimento_email', true);
    $telefono     = get_post_meta($post_id, '_dci_dipartimento_telefono', true);
    $indirizzo    = get_post_meta($post_id, '_dci_dipartimento_indirizzo', true);
    $lat          = get_post_meta($post_id, '_dci_dipartimento_latitudine', true);
    $lng          = get_post_meta($post_id, '_dci_dipartimento_longitudine', true);
    $organigramma = get_post_meta($post_id, '_dci_dipartimento_organigramma', true);
    $responsabile_id = dci_get_responsabile($post_id);
    $has_map      = $lat && $lng;
    $map_title    = get_the_title();

    $child_departments = get_posts([
            'post_type'      => 'dipartimento',
            'posts_per_page' => -1,
            'post_parent'    => $post_id,
            'orderby'        => 'title',
            'order'          => 'ASC',
    ]);

    /* Costruiamo l'elenco delle sezioni visibili per il nav laterale */
    $nav_sections = [];
    $nav_sections[] = ['id' => 'descrizione', 'label' => __('Descrizione', 'wp-digital-italia')];
    if ($email || $telefono || $indirizzo || $has_map) {
        $nav_sections[] = ['id' => 'contatti', 'label' => __('Contatti', 'wp-digital-italia')];
    }
    if (!empty($responsabile_id)) {
        $nav_sections[] = ['id' => 'responsabile', 'label' => __('Responsabile', 'wp-digital-italia')];
    }
    if (!empty($organigramma) && is_array($organigramma)) {
        $nav_sections[] = ['id' => 'organigramma', 'label' => __('Organigramma', 'wp-digital-italia')];
    }
    if (!empty($child_departments)) {
        $nav_sections[] = ['id' => 'dipartimenti', 'label' => __('Dipartimenti', 'wp-digital-italia')];
    }
    ?>

    <main id="main-content">

        <!-- ===== HERO / INTESTAZIONE ===== -->
        <header class="page-header bg-white mb-4 py-4">
            <div class="container-fluid">
                <!-- Titolo + sottotitolo -->
                <h1 class="mb-2"><?php the_title(); ?></h1>

                <?php
                $subtitle = get_post_meta($post_id, '_dci_dipartimento_sottotitolo', true);
                if ($subtitle) : ?>
                    <p class="lead text-muted mb-3"><?php echo esc_html($subtitle); ?></p>
                <?php endif; ?>

                <!-- Tag / argomenti (tassonomia opzionale) -->
                <?php
                $terms = get_the_terms($post_id, 'argomento'); // adatta alla tua tassonomia
                if ($terms && !is_wp_error($terms)) : ?>
                    <div class="mb-3">
                        <?php foreach ($terms as $term) : ?>
                            <a href="<?php echo esc_url(get_term_link($term)); ?>"
                               class="badge bg-primary text-decoration-none me-1">
                                <?php echo esc_html($term->name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div><!-- .container -->
        </header><!-- hero -->

        <!-- ===== GRIGLIA: SIDEBAR + CONTENUTO ===== -->
        <div class="container-fluid py-5">
            <div class="row g-4">

                <!-- SIDEBAR SINISTRA: indice della pagina -->
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

                <!-- CONTENUTO PRINCIPALE -->
                <div class="col-12 col-lg-9">

                    <!-- Immagine in evidenza -->
                    <?php if (has_post_thumbnail()) : ?>
                        <figure class="mb-4 ratio ratio-21x9 overflow-hidden">
                            <?php the_post_thumbnail('large', ['class' => 'img-fluid w-100 h-100 img-cover img-cover']); ?>
                        </figure>
                    <?php endif; ?>

                    <!-- DESCRIZIONE -->
                    <section id="descrizione" class="mb-5 pb-4 border-bottom" aria-labelledby="descrizione-title">
                        <h2 id="descrizione-title" class="h4 mb-3">
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

                    <!-- CONTATTI -->
                    <?php if ($email || $telefono || $indirizzo || $has_map) : ?>
                        <section id="contatti" class="mb-5 pb-4 border-bottom" aria-labelledby="contatti-title">
                            <h2 id="contatti-title" class="h4 mb-4">
                                <?php _e('Contatti', 'wp-digital-italia'); ?>
                            </h2>
                            <div class="row g-4">
                                <div class="<?php echo $has_map ? 'col-md-6' : 'col-12'; ?>">

                                    <?php if ($email) : ?>
                                        <div class="mb-3">
                                            <p class="text-muted small mb-1"><?php _e('Email', 'wp-digital-italia'); ?></p>
                                            <p class="fw-semibold mb-0 d-flex align-items-center gap-2">
                                                <svg class="icon icon-primary icon-sm" aria-hidden="true">
                                                    <use href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/images/sprites.svg#it-mail"></use>
                                                </svg>
                                                <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($telefono) : ?>
                                        <div class="mb-3">
                                            <p class="text-muted small mb-1"><?php _e('Telefono', 'wp-digital-italia'); ?></p>
                                            <p class="fw-semibold mb-0 d-flex align-items-center gap-2">
                                                <svg class="icon icon-primary icon-sm" aria-hidden="true">
                                                    <use href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/images/sprites.svg#it-telephone"></use>
                                                </svg>
                                                <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $telefono)); ?>"><?php echo esc_html($telefono); ?></a>
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($indirizzo) : ?>
                                        <div class="mb-3">
                                            <p class="text-muted small mb-1"><?php _e('Indirizzo', 'wp-digital-italia'); ?></p>
                                            <p class="fw-semibold mb-0">
                                                <svg class="icon icon-primary icon-sm" aria-hidden="true">
                                                    <use href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/images/sprites.svg#it-pin"></use>
                                                </svg>
                                                <?php echo nl2br(esc_html($indirizzo)); ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                </div>

                                <?php if ($has_map) : ?>
                                    <div class="col-md-6">
                                        <div id="dipartimento-map" class="rounded" style="height: 260px; width: 100%;"></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <!-- RESPONSABILE -->
                    <?php if ($responsabile_id) :
                        $responsabile = get_post($responsabile_id);
                        if ($responsabile) :
                            $resp_nome     = get_post_meta($responsabile_id, '_dci_persona_nome', true);
                            $resp_cognome  = get_post_meta($responsabile_id, '_dci_persona_cognome', true);
                            $resp_full_name = trim($resp_nome . ' ' . $resp_cognome) ?: $responsabile->post_title;
                            $resp_permalink = get_permalink($responsabile_id);
                            $resp_incarico  = dci_get_incarico_corrente($responsabile_id);
                            ?>
                            <section id="responsabile" class="mb-5 pb-4 border-bottom" aria-labelledby="responsabile-title">
                                <h2 id="responsabile-title" class="h4 mb-4">
                                    <?php _e('Responsabile', 'wp-digital-italia'); ?>
                                </h2>
                                <article class="d-flex gap-3 align-items-start">
                                    <div class="flex-shrink-0 bg-light rounded-2 d-flex align-items-center justify-content-center overflow-hidden"
                                         style="width:80px;height:80px;">
                                        <?php if (has_post_thumbnail($responsabile_id)) : ?>
                                            <?php echo get_the_post_thumbnail($responsabile_id, 'thumbnail', [
                                                    'class' => 'img-fluid',
                                                    'style' => 'width:80px;height:80px;object-fit:cover;',
                                                    'alt'   => esc_attr($resp_full_name),
                                            ]); ?>
                                        <?php else : ?>
                                            <svg class="icon icon-primary" aria-hidden="true" style="width:40px;height:40px;">
                                                <use href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/images/sprites.svg#it-user"></use>
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h3 class="h5 mb-1">
                                            <a class="text-decoration-none" href="<?php echo esc_url($resp_permalink); ?>">
                                                <?php echo esc_html($resp_full_name); ?>
                                            </a>
                                        </h3>
                                        <?php if (!empty($resp_incarico['incarico'])) : ?>
                                            <p class="mb-0 text-muted"><?php echo esc_html($resp_incarico['incarico']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            </section>
                        <?php endif; endif; ?>

                    <!-- ORGANIGRAMMA -->
                    <?php if (!empty($organigramma) && is_array($organigramma)) : ?>
                        <section id="organigramma" class="mb-5 pb-4 border-bottom" aria-labelledby="organigramma-title">
                            <h2 id="organigramma-title" class="h4 mb-4">
                                <?php _e('Organigramma', 'wp-digital-italia'); ?>
                            </h2>
                            <div class="row g-3">
                                <?php foreach ($organigramma as $entry) :
                                    $persona_id = $entry['persona_id'] ?? '';
                                    $ruolo_org  = $entry['ruolo'] ?? '';
                                    if (!$persona_id) continue;
                                    $persona = get_post($persona_id);
                                    if (!$persona) continue;
                                    $nome      = get_post_meta($persona_id, '_dci_persona_nome', true);
                                    $cognome   = get_post_meta($persona_id, '_dci_persona_cognome', true);
                                    $full_name = trim($nome . ' ' . $cognome) ?: $persona->post_title;
                                    $permalink = get_permalink($persona_id);
                                    ?>
                                    <div class="col-12 col-md-6 col-xl-4">
                                        <article class="h-100 p-3 border rounded">
                                            <div class="d-flex gap-3 align-items-start">
                                                <div class="flex-shrink-0 bg-light rounded-2 d-flex align-items-center justify-content-center overflow-hidden"
                                                     style="width:64px;height:64px;">
                                                    <?php if (has_post_thumbnail($persona_id)) : ?>
                                                        <?php echo get_the_post_thumbnail($persona_id, 'thumbnail', [
                                                                'class' => 'img-fluid',
                                                                'style' => 'width:64px;height:64px;object-fit:cover;',
                                                                'alt'   => esc_attr($full_name),
                                                        ]); ?>
                                                    <?php else : ?>
                                                        <svg class="icon icon-primary" aria-hidden="true" style="width:32px;height:32px;">
                                                            <use href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/images/sprites.svg#it-user"></use>
                                                        </svg>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h3 class="h6 mb-1">
                                                        <a class="text-decoration-none" href="<?php echo esc_url($permalink); ?>">
                                                            <?php echo esc_html($full_name); ?>
                                                        </a>
                                                    </h3>
                                                    <?php if ($ruolo_org) : ?>
                                                        <p class="mb-0 text-muted small"><?php echo esc_html($ruolo_org); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </article>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <!-- DIPARTIMENTI FIGLI -->
                    <?php if (!empty($child_departments)) : ?>
                        <section id="dipartimenti" class="mb-5" aria-labelledby="dipartimenti-title">
                            <h2 id="dipartimenti-title" class="h4 mb-4">
                                <?php _e('Dipartimenti', 'wp-digital-italia'); ?>
                            </h2>
                            <div class="row g-4">
                                <?php
                                $placeholder = get_template_directory_uri() . '/assets/images/news-img-placeholder.webp';
                                foreach ($child_departments as $child) :
                                    $child_id      = $child->ID;
                                    $child_email   = get_post_meta($child_id, '_dci_dipartimento_email', true);
                                    $child_telefono = get_post_meta($child_id, '_dci_dipartimento_telefono', true);
                                    $image_url     = has_post_thumbnail($child_id)
                                            ? get_the_post_thumbnail_url($child_id, 'large')
                                            : $placeholder;
                                    ?>
                                    <div class="col-md-4">
                                        <article class="h-100 border rounded overflow-hidden">
                                            <a href="<?php echo esc_url(get_permalink($child_id)); ?>" tabindex="-1" aria-hidden="true">
                                                <img src="<?php echo esc_url($image_url); ?>"
                                                     class="img-fluid w-100"
                                                     style="height:160px;object-fit:cover;"
                                                     alt="<?php echo esc_attr($child->post_title); ?>">
                                            </a>
                                            <div class="p-3">
                                                <h3 class="h5 mb-2">
                                                    <a class="text-decoration-none stretched-link"
                                                       href="<?php echo esc_url(get_permalink($child_id)); ?>">
                                                        <?php echo esc_html($child->post_title); ?>
                                                    </a>
                                                </h3>
                                                <?php if ($child_email || $child_telefono) : ?>
                                                    <div class="small text-muted position-relative" style="z-index:1;">
                                                        <?php if ($child_email) : ?>
                                                            <p class="mb-1 d-flex align-items-center gap-1">
                                                                <svg class="icon icon-primary icon-xs" aria-hidden="true">
                                                                    <use href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/images/sprites.svg#it-mail"></use>
                                                                </svg>
                                                                <a href="mailto:<?php echo esc_attr($child_email); ?>" style="position:relative;z-index:2;">
                                                                    <?php echo esc_html($child_email); ?>
                                                                </a>
                                                            </p>
                                                        <?php endif; ?>
                                                        <?php if ($child_telefono) : ?>
                                                            <p class="mb-0 d-flex align-items-center gap-1">
                                                                <svg class="icon icon-primary icon-xs" aria-hidden="true">
                                                                    <use href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/images/sprites.svg#it-telephone"></use>
                                                                </svg>
                                                                <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $child_telefono)); ?>" style="position:relative;z-index:2;">
                                                                    <?php echo esc_html($child_telefono); ?>
                                                                </a>
                                                            </p>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (has_excerpt($child_id)) : ?>
                                                    <p class="mt-2 mb-0 small">
                                                        <?php echo wp_trim_words(get_the_excerpt($child_id), 20, '…'); ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </article>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                </div><!-- col contenuto -->
            </div><!-- .row -->
        </div><!-- .container -->

    </main><!-- #main-content -->

<?php endwhile; ?>

<?php if ($has_map) : ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof initDipartimentoMap === 'function') {
                initDipartimentoMap(
                    'dipartimento-map',
                        <?php echo floatval($lat); ?>,
                        <?php echo floatval($lng); ?>,
                        <?php echo json_encode($map_title); ?>
                );
            }
        });
    </script>
<?php endif; ?>

<?php get_footer();