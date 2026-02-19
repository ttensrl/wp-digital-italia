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
                    get_template_part('template-parts/content', get_post_type());

                    // Pulsante Prenota Appuntamento
                    // Se è disponibile la funzione JS per aprire la modale di prenotazione (swbOpenBookingModal o simile), usiamola.
                    // Altrimenti rimandiamo a una pagina di prenotazione (/prenota) o all'ancora #prenota.
                    $booking_button_label = esc_html__('Prenota appuntamento', 'digital-italia');

                    // Prova a usare un identificatore data-post-id per permettere alle chiamate JS di aprire la modale per questo servizio
                    $post_id = get_the_ID();
                    ?>

                    <div class="mt-4">
                        <a href="#" class="btn btn-primary swb-booking-button" data-post-id="<?php echo esc_attr($post_id); ?>">
                            <?php echo $booking_button_label; ?>
                        </a>
                    </div>

                    <?php
                    // Non includiamo la_post_navigation per questo post type (richiesta)

                    // Se i commenti sono aperti o ci sono commenti, carichiamo il template dei commenti.
                    if (comments_open() || get_comments_number()) :
                        comments_template();
                    endif;

                endwhile; // End of the loop.
                ?>

            </div>
        </div>
        <!-- SIDEBAR -->
        <div class="col-lg-4 col-xxl-auto">
            <?php get_sidebar(); ?>
        </div>
    </div>
    <!-- /ROW -->
</main><!-- #main -->

<?php
get_footer();

