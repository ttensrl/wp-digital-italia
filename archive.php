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

<main id="primary" class="container-fluid py-2 py-md-5">
    <header class="page-header bg-white mb-4 py-4">
        <div class="container-fluid">
            <?php
            if ( is_home() && ! is_front_page() ) :
                single_post_title( '<h1 class="mb-2">', '</h1>' );
            else :
                the_archive_title( '<h1 class="mb-2">', '</h1>' );
            endif;
            the_archive_description( '<div class="archive-description">', '</div>' );
            ?>
        </div>
    </header>

    <div class="row g-4">
        <div class="col">
            <section class="px-3">
                <?php if ( have_posts() ) : ?>

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
        <div class="col-lg-4 col-xxl-auto">
            <?php get_sidebar(); ?>
        </div>
    </div>
</main>

<?php
get_footer();
