<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package digital-italia
 */

get_header();
?>
    <main id="primary" class="container-fluid py-5">
        <div class="row">
            <div class="col-lg">

                <?php
                while (have_posts()) :
                    the_post();

                    get_template_part('template-parts/content', 'page');

                    // If comments are open or we have at least one comment, load up the comment template.
                    if (comments_open() || get_comments_number()) :
                        comments_template();
                    endif;

                endwhile; // End of the loop.
                ?>
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
