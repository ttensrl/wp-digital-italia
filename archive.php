<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package digital-italia
 */

get_header();
?>

	<main id="primary" class="container-fluid py-5">
        <div class="row">
            <div class="col">
                <section class="px-3">
                    <?php if ( have_posts() ) : ?>

                        <header class="page-header mb-4">
                            <?php
                            the_archive_title( '<h1 class="page-title">', '</h1>' );
                            the_archive_description( '<div class="archive-description">', '</div>' );
                            ?>
                        </header><!-- .page-header -->

                        <?php
                        /* Start the Loop */
                        while ( have_posts() ) :
                            the_post();

                            /*
                             * Include the Post-Type-specific template for the content.
                             * If you want to override this in a child theme, then include a file
                             * called content-___.php (where ___ is the Post Type name) and that will be used instead.
                             */
                            get_template_part( 'template-parts/content', 'search' );

                        endwhile;

                        the_posts_navigation();

                    else :

                        get_template_part( 'template-parts/content', 'none' );

                    endif;
                    ?>
                </section>
            </div>
            <!-- SIDEBAR -->
            <div class="col-lg-4 col-xxl-auto">
                <?php get_sidebar(); ?>
            </div>
        </div>
        <!-- /ROW -->

	</main><!-- #main -->

<?php
get_sidebar();
get_footer();
