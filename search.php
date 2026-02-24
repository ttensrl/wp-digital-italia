<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package digital-italia
 */

get_header();
?>
    <?php if ( have_posts() ) : ?>
	<main id="primary" class="container-fluid py-2 py-md-5">
        <div class="row">
            <div class="col">
                <section class="px-3">
                    <header class="page-header mb-4">
                        <h1 class="page-title">
                            <?php
                            /* translators: %s: search query. */
                            printf( esc_html__( 'Search Results for: %s', 'wp-digital-italia' ), '<span>' . get_search_query() . '</span>' );
                            ?>
                        </h1>
                    </header><!-- .page-header -->

                    <?php
                    /* Start the Loop */

                    while ( have_posts() ) :
                        the_post();

                        /**
                         * Run the loop for the search to output the results.
                         * If you want to overload this in a child theme then include a file
                         * called content-search.php and that will be used instead.
                         */
                        get_template_part( 'template-parts/content', 'search' );

                    endwhile;

                    the_posts_navigation();

                    ?>
                </section>
            </div>
            <!-- SIDEBAR -->
            <div class="col-lg-4 col-xxl-auto">
                <?php get_sidebar(); ?>
            </div>
        </div>
	</main><!-- #main -->

    <?php
    else : ?>
    <main id="primary" class="container py-5">

        <?php get_template_part( 'template-parts/content', 'none' ); ?>

	</main>
    <?php
    endif;
    ?>

<?php
get_footer();
