<?php
/**
 * The template for home page
 * Template Name: Home
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
        <?php
        while (have_posts()) :
            the_post();

            get_template_part('template-parts/content/page', 'full', ['hide_title' => true]);

            // If comments are open or we have at least one comment, load up the comment template.
            if (comments_open() || get_comments_number()) :
                comments_template();
            endif;

        endwhile; // End of the loop.
        ?>

        <?php
        $latest_posts = new WP_Query([
                'posts_per_page' => 3,
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'desc',
        ]);

        if ($latest_posts->have_posts()) :
            ?>
            <section class="latest-posts mt-5">
                <h2 class="mb-4">Ultime notizie</h2>
                <div class="row">
                    <?php while ($latest_posts->have_posts()) : $latest_posts->the_post(); ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <?php
                                $default_image = get_theme_mod('posts_default_image');
                                $placeholder = get_template_directory_uri() . '/assets/images/news-img-placeholder.webp';
                                $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'medium') : ($default_image ?: $placeholder);
                                ?>
                                <div class="card-img-top">
                                    <a href="<?php the_permalink(); ?>">
                                        <img src="<?php echo esc_url($image_url); ?>" class="img-fluid" alt="<?php the_title_attribute(); ?>">
                                    </a>
                                </div>
                                <div class="card-body">
                                    <h3 class="card-title h5">
                                        <a class="text-decoration-none"
                                           href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h3>
                                    <p class="small text-muted"><?php echo get_the_date(); ?></p>
                                    <p class="card-text"><?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile;
                    wp_reset_postdata(); ?>
                </div>
            </section>
        <?php endif; ?>
    </main><!-- #main -->

<?php
get_footer();
