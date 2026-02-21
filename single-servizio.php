<?php
/**
 * Template per il singolo post type Servizio
 * Rimuove i link di navigazione prev/next e aggiunge un pulsante "Prenota appuntamento".
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

                    // Usa lo stesso template parti del tema per il contenuto
                    get_template_part('template-parts/content/single', get_post_type());

                    // Pulsante Prenota Appuntamento (solo se abilitato)
                    $post_id = get_the_ID();
                    $booking_enabled = get_post_meta($post_id, '_swb_slots_enabled', true);

                    if ($booking_enabled === '1') :
                        $booking_button_label = esc_html__('Prenota appuntamento', 'wp-digital-italia');
                        ?>
                        <div class="mt-4">
                            <a href="#" class="btn btn-primary swb-booking-button" data-post-id="<?php echo esc_attr($post_id); ?>">
                                <?php echo $booking_button_label; ?>
                            </a>
                        </div>
                    <?php endif;

                    // Non includiamo la_post_navigation per questo post type (richiesta)

                    // Se i commenti sono aperti o ci sono commenti, carichiamo il template dei commenti.
                    if (comments_open() || get_comments_number()) :
                        comments_template();
                    endif;

                endwhile; // End of the loop.
                ?>

            </div>
        </div>
    </div>
    <!-- /ROW -->
</main><!-- #main -->

<?php
get_footer();

