<?php
/* Template Name: Prenota appuntamento
 *
 * Prenota appuntamento template file per il tema wp-digital-italia
 *
 */

function wpdi_enqueue_booking_script(): void
{
    wp_enqueue_script( 'wpdi-utils', get_template_directory_uri() . '/js/utils.js', array(), false, true );

    $booking_path = get_template_directory() . '/js/booking.js';
    $booking_uri  = get_template_directory_uri() . '/js/booking.js';

    if ( wp_script_is( 'servizio-booking', 'registered' ) || wp_script_is( 'servizio-booking', 'enqueued' ) ) {
        if ( ! wp_script_is( 'servizio-booking', 'enqueued' ) ) {
            wp_enqueue_script( 'servizio-booking' );
        }
        $handle = 'servizio-booking';
    } else {
        $version = file_exists( $booking_path ) ? filemtime( $booking_path ) : false;
        wp_register_script( 'wpdi-booking', $booking_uri, array( 'jquery' ), $version, true );
        wp_enqueue_script( 'wpdi-booking' );
        $handle = 'wpdi-booking';
    }

    $inline_script = 'window.spritesSvgPath = "' . get_template_directory_uri() . '/assets/img/sprites.svg";';
    wp_add_inline_script( $handle, $inline_script, 'before' );

    wp_localize_script( $handle, 'url',        [ admin_url( 'admin-ajax.php' ) . '?action=get_available_appointments' ] );
    wp_localize_script( $handle, 'urlConfirm', [ admin_url( 'admin-ajax.php' ) ] );
    wp_localize_script( $handle, 'bookingAjax', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'booking_nonce' )
    ) );
}
add_action( 'wp_enqueue_scripts', 'wpdi_enqueue_booking_script' );

get_header();
?>

    <main id="primary" class="container-fluid py-5">
        <div class="row">
            <div class="col-lg">

                <?php
                while ( have_posts() ) :
                    the_post();

                    get_template_part( 'template-parts/content', 'page' );

                    if ( comments_open() || get_comments_number() ) :
                        comments_template();
                    endif;

                endwhile;
                ?>

                <div id="form-steps">

                    <?php get_template_part( 'template-parts/prenotazione/tabs' ); ?>

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