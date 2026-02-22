<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
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
                            if ( is_home() && ! is_front_page() ) :
                                single_post_title( '<h1 class="page-title">', '</h1>' );
                            else :
                                ?>
                                <h1 class="page-title">Articoli</h1>
                                <?php
                            endif;
                            ?>
                        </header><!-- .page-header -->

                        <?php
                        while ( have_posts() ) :
                            the_post();

                            get_template_part( 'template-parts/content', 'search' );

                        endwhile;

                        dci_bootstrap_pagination();

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
get_footer();
