<?php
/**
 * Template per il singolo post type Dipartimento
 */

get_header();
?>

    <main id="primary" class="container-fluid py-5">
        <div class="row">
            <div class="col-lg">
                <div class="px-3">

                    <?php
                    $has_map = false;
                    $lat = '';
                    $lng = '';
                    while (have_posts()) :
                        the_post();
                        $post_id = get_the_ID();
                        $email = get_post_meta($post_id, '_dci_dipartimento_email', true);
                        $telefono = get_post_meta($post_id, '_dci_dipartimento_telefono', true);
                        $indirizzo = get_post_meta($post_id, '_dci_dipartimento_indirizzo', true);
                        $lat = get_post_meta($post_id, '_dci_dipartimento_latitudine', true);
                        $lng = get_post_meta($post_id, '_dci_dipartimento_longitudine', true);
                        $organigramma = get_post_meta($post_id, '_dci_dipartimento_organigramma', true);
                        $has_map = $lat && $lng;
                        $map_title = get_the_title();
                        ?>

                        <article id="post-<?php the_ID(); ?>" <?php post_class('card card-big bg-white shadow-sm'); ?>>
                            <header class="card-body">
                                <div class="row mb-3">
                                    <div class="col-xxl">
                                        <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                                    </div>
                                </div>
                            </header>

                            <?php if (has_post_thumbnail()) : ?>
                            <figure class="figure px-0 img-full">
                                <?php digital_italia_post_thumbnail('figure-img img-fluid'); ?>
                            </figure>
                            <div class="card-body pb-3">
                                <?php else : ?>
                                <div class="card-body border-top border-light pb-3">
                                    <?php endif; ?>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <?php if ($email) : ?>
                                                <div class="mb-3">
                                                    <small><?php _e('Email', 'digital-italia'); ?></small>
                                                    <p class="fw-semibold mb-0">
                                                        <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                                                    </p>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($telefono) : ?>
                                                <div class="mb-3">
                                                    <small><?php _e('Telefono', 'digital-italia'); ?></small>
                                                    <p class="fw-semibold mb-0">
                                                        <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $telefono)); ?>"><?php echo esc_html($telefono); ?></a>
                                                    </p>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($indirizzo) : ?>
                                                <div class="mb-3">
                                                    <small><?php _e('Indirizzo', 'digital-italia'); ?></small>
                                                    <p class="fw-semibold mb-0"><?php echo nl2br(esc_html($indirizzo)); ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($has_map) : ?>
                                        <div class="col-md-6">
                                            <div id="dipartimento-map" style="height: 250px; width: 100%; border-radius: 8px;"></div>
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php
                                    the_content();
                                    wp_link_pages(array(
                                            'before' => '<div class="page-links">' . esc_html__('Pages:', 'digital-italia'),
                                            'after' => '</div>',
                                    ));
                                    ?>
                                </div>
                        </article>

                        <?php if (!empty($organigramma) && is_array($organigramma)) : ?>
                        <div class="card card-big bg-white shadow-sm mt-4">
                            <div class="card-body">
                                <h2 class="h3 mb-4"><?php _e('Organigramma', 'digital-italia'); ?></h2>
                                <div class="row">
                                    <?php foreach ($organigramma as $entry) :
                                        $persona_id = $entry['persona_id'] ?? '';
                                        $ruolo_org = $entry['ruolo'] ?? '';

                                        if ($persona_id) :
                                            $persona = get_post($persona_id);
                                            if ($persona) :
                                                $nome = get_post_meta($persona_id, '_dci_persona_nome', true);
                                                $cognome = get_post_meta($persona_id, '_dci_persona_cognome', true);
                                                $full_name = trim($nome . ' ' . $cognome);
                                                if (!$full_name) {
                                                    $full_name = $persona->post_title;
                                                }
                                                ?>
                                                <div class="col-md-6 col-lg-4 mb-3">
                                                    <div class="card h-100">
                                                        <?php if (has_post_thumbnail($persona_id)) : ?>
                                                            <div class="text-center pt-3">
                                                                <?php echo get_the_post_thumbnail($persona_id, 'thumbnail', array('class' => 'rounded-circle')); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="card-body text-center">
                                                            <h3 class="h5 card-title mb-1">
                                                                <a href="<?php echo get_permalink($persona_id); ?>"><?php echo esc_html($full_name); ?></a>
                                                            </h3>
                                                            <?php if ($ruolo_org) : ?>
                                                                <p class="card-text text-muted"><?php echo esc_html($ruolo_org); ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php
                                            endif;
                                        endif;
                                    endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php
                    endwhile;
                    ?>

                </div>
            </div>
        </div>
    </main>

    <?php if ($has_map) : ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
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

<?php
get_footer();
