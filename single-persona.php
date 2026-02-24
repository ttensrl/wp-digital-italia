<?php
/**
 * Template per il singolo post type Persona
 */

get_header();
?>

<main id="primary" class="container-fluid py-5">
    <div class="row">
        <div class="col-lg">
            <div class="px-3">

                <?php
                while (have_posts()) :
                    the_post();
                    $post_id = get_the_ID();
                    $nome = get_post_meta($post_id, '_dci_persona_nome', true);
                    $cognome = get_post_meta($post_id, '_dci_persona_cognome', true);
                    $email = get_post_meta($post_id, '_dci_persona_email', true);
                    $telefono = get_post_meta($post_id, '_dci_persona_telefono', true);
                    $ruolo = get_post_meta($post_id, '_dci_persona_ruolo', true);
                    $incarico_corrente = dci_get_incarico_corrente($post_id);
                ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class('card card-big bg-white shadow-sm'); ?>>
                    <header class="card-body">
                        <div class="row mb-3">
                            <div class="col-xxl">
                                <?php
                                $full_name = trim($nome . ' ' . $cognome);
                                if ($full_name) {
                                    echo '<h1 class="entry-title">' . esc_html($full_name) . '</h1>';
                                } else {
                                    the_title('<h1 class="entry-title">', '</h1>');
                                }

                                if ($ruolo) : ?>
                                    <p class="text-muted fs-5 mt-2"><?php echo esc_html($ruolo); ?></p>
                                <?php endif; ?>

                                <?php if ($incarico_corrente && !empty($incarico_corrente['incarico'])) : ?>
                                    <p class="text-primary fs-5 mt-2 fw-bold">
                                        <?php 
                                        echo esc_html($incarico_corrente['incarico']);
                                        if (!empty($incarico_corrente['data_inizio'])) {
                                            echo ' <span class="text-muted fw-normal">(' . __('dal', 'wp-digital-italia') . ' ' . esc_html($incarico_corrente['data_inizio']) . ')</span>';
                                        }
                                        ?>
                                    </p>
                                <?php endif; ?>
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
                                        <small><?php _e('Email', 'wp-digital-italia'); ?></small>
                                        <p class="fw-semibold mb-0">
                                            <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <?php if ($telefono) : ?>
                                    <div class="mb-3">
                                        <small><?php _e('Telefono', 'wp-digital-italia'); ?></small>
                                        <p class="fw-semibold mb-0">
                                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $telefono)); ?>"><?php echo esc_html($telefono); ?></a>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php
                        the_content();
                        wp_link_pages(array(
                            'before' => '<div class="page-links">' . esc_html__('Pages:', 'wp-digital-italia'),
                            'after'  => '</div>',
                        ));
                        ?>
                    </div>
                </article>

                <?php
                endwhile;
                ?>

            </div>
        </div>
    </div>
</main>

<?php
get_footer();
