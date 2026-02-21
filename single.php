<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package digital-italia
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

                    get_template_part('template-parts/content/single', get_post_type());

                    the_post_navigation(
                        array(
                            'prev_text' => '<span class="nav-subtitle">' . esc_html__('Previous:', 'digital-italia') . '</span> <span class="nav-title">%title</span>',
                            'next_text' => '<span class="nav-subtitle">' . esc_html__('Next:', 'digital-italia') . '</span> <span class="nav-title">%title</span>',
                        )
                    );

                    // If comments are open or we have at least one comment, load up the comment template.
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
