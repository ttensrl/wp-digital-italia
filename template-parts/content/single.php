<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package digital-italia
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('card card-big bg-white shadow-sm'); ?> >
    <header class="card-body">
        <div class="row mb-3">

            <div class="post_head_left col-xxl">
                <?php

                the_title( '<h1 class="entry-title me-xxl-5">', '</h1>' );

                if ( 'post' === get_post_type() ) :
                ?>
                <div class="post_meta row mt-5">
                    <div class="col-md-6">
                        <div class="pe-md-5">
                            <small>Data:</small>
                            <p class="fw-semibold font-monospace text-nowrap mb-0">
                                <?php
                                digital_italia_posted_on('d F Y', '');
                                ?>
                                <?php if (get_the_modified_date() != get_the_date()) : ?>
                                    <small class="d-block fw-semibold text-muted font-sans-serif">
                                        (<?php digital_italia_updated_on('d M Y', 'Aggiornato il'); ?>)
                                    </small>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="ps-md-5 pt-4 pt-md-0">
                            <small>Autore:</small>
                            <p class="fw-semibold mb-0">
                                <?php
                                digital_italia_posted_by('');
                                ?>
                            </p>
                        </div>
                    </div>
                </div><!-- .entry-meta -->
            </div>
            <div class="post_head_right col-xxl-4 ms-auto d-flex flex-column">
                <div class="flex-grow-1 pt-5 pt-xxl-0 ps-xxl-5 mt-5 mt-xxl-0">
                    <?php digital_italia_taxonomy(); ?><!-- .entry-taxonomy -->
                </div>
            </div><!-- .entry-taxonomy -->
            <?php endif; ?>

        </div>
    </header><!-- .entry-header -->

    <?php if ( has_post_thumbnail() ) : ?>
        <figure class="figure px-0 img-full">
            <?php digital_italia_post_thumbnail('figure-img img-fluid'); ?>
        </figure>
    <div class="card-body pb-3">

    <?php else : ?>

    <div class="card-body border-top border-light pb-3">

    <?php endif; ?>

        <?php
        the_content(
            sprintf(
                wp_kses(
                    /* translators: %s: Name of current post. Only visible to screen readers */
                    __( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'wp-digital-italia' ),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                wp_kses_post( get_the_title() )
            )
        );

        wp_link_pages(
            array(
                'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'wp-digital-italia' ),
                'after'  => '</div>',
            )
        );
        ?>
    </div><!-- .entry-content -->
    <footer class="entry-footer mb-n5 pe-4 pt-4 pb-3 text-end">
        <?php digital_italia_entry_footer(); ?>
    </footer><!-- .entry-footer -->
</article><!-- #post-<?php the_ID(); ?> -->
