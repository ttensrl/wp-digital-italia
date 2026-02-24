<?php
/**
 * The template for displaying archive for persona post type
 *
 * @package digital-italia
 */

get_header();
?>

    <main id="primary" class="container-fluid py-2 py-md-5">
        <header class="page-header bg-white mb-4 py-4">
            <div class="container-fluid">
                <?php
                the_archive_title( '<h1 class="mb-2">', '</h1>' );
                the_archive_description( '<div class="archive-description">', '</div>' );
                ?>
            </div>
        </header>

        <div class="row g-4">
            <div class="col">
                <section class="px-3">
                    <?php
                    $persona_query = new WP_Query( array(
                            'post_type'      => 'persona',
                            'posts_per_page' => -1,
                            'post_status'    => 'publish',
                            'orderby'        => 'meta_value',
                            'meta_key'       => '_dci_persona_cognome',
                            'order'          => 'ASC',
                    ) );

                    if ( $persona_query->have_posts() ) :

                        // Inizializza array per lettera
                        $persone_by_letter = array_fill_keys( range( 'A', 'Z' ), array() );

                        while ( $persona_query->have_posts() ) :
                            $persona_query->the_post();

                            $id      = get_the_ID();
                            $cognome = get_post_meta( $id, '_dci_persona_cognome', true );
                            $cognome = is_array( $cognome ) ? ( $cognome[0] ?? '' ) : $cognome;
                            $cognome = ! empty( $cognome ) ? $cognome : get_the_title();

                            $initial = strtoupper( mb_substr( $cognome, 0, 1, 'UTF-8' ) );

                            if ( isset( $persone_by_letter[ $initial ] ) ) {
                                $persone_by_letter[ $initial ][] = array(
                                        'cognome'   => $cognome,
                                        'nome'      => get_post_meta( $id, '_dci_persona_nome', true ),
                                        'permalink' => get_permalink(),
                                );
                            }
                        endwhile;
                        wp_reset_postdata();

                        if ( $persona_query->found_posts > 0 ) : ?>

                            <div class="accordion" id="personeAccordion">
                                <?php foreach ( range( 'A', 'Z' ) as $letter ) :
                                    $persone     = $persone_by_letter[ $letter ];
                                    $collapse_id = 'collapse_' . $letter;
                                    $heading_id  = 'heading_' . $letter;
                                    ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="<?php echo esc_attr( $heading_id ); ?>">
                                            <button
                                                    class="accordion-button collapsed"
                                                    type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#<?php echo esc_attr( $collapse_id ); ?>"
                                                    aria-expanded="false"
                                                    aria-controls="<?php echo esc_attr( $collapse_id ); ?>">
                                                <span class="fw-semibold me-2"><?php echo esc_html( $letter ); ?></span>
                                                <span class="badge bg-primary rounded-pill ms-1"><?php echo count( $persone ); ?></span>
                                            </button>
                                        </h2>
                                        <div
                                                id="<?php echo esc_attr( $collapse_id ); ?>"
                                                class="accordion-collapse collapse"
                                                aria-labelledby="<?php echo esc_attr( $heading_id ); ?>"
                                                data-bs-parent="#personeAccordion">
                                            <div class="accordion-body p-0">
                                                <?php if ( ! empty( $persone ) ) : ?>
                                                    <ul class="list-unstyled mb-0">
                                                        <?php foreach ( $persone as $persona ) : ?>
                                                            <li class="border-bottom">
                                                                <a href="<?php echo esc_url( $persona['permalink'] ); ?>"
                                                                   class="text-decoration-none d-flex align-items-center px-4 py-3">
                                                                    <span class="fw-semibold"><?php echo esc_html( $persona['cognome'] ); ?></span>
                                                                    <?php if ( ! empty( $persona['nome'] ) ) : ?>
                                                                        <span class="mx-1 text-muted">,</span>
                                                                        <span><?php echo esc_html( $persona['nome'] ); ?></span>
                                                                    <?php endif; ?>
                                                                    <span class="ms-auto">
                                                                    <svg class="icon icon-sm icon-primary" aria-hidden="true">
                                                                        <use href="#it-arrow-right"></use>
                                                                    </svg>
                                                                </span>
                                                                </a>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else : ?>
                                                    <p class="mb-0 px-4 py-3 text-muted small">
                                                        <?php esc_html_e( 'Nessuna persona per questa lettera.', 'wp-digital-italia' ); ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php else : ?>
                            <div class="alert alert-info" role="alert">
                                <?php esc_html_e( 'Non sono state trovate persone.', 'wp-digital-italia' ); ?>
                            </div>
                        <?php endif; ?>

                    <?php else : ?>
                        <div class="alert alert-info" role="alert">
                            <?php esc_html_e( 'Non sono state trovate persone.', 'wp-digital-italia' ); ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
            <div class="col-lg-4 col-xxl-auto">
                <?php get_sidebar(); ?>
            </div>
        </div>
    </main>

<?php
get_footer();