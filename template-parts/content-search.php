<?php
/**
 * Template part for displaying results in search pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package digital-italia
 */

?>

<div class="card-wrapper flex-grow-1">
    <article id="post-<?php the_ID(); ?>" <?php post_class('card card-bg border-bottom-card mt-4 mx-0'); ?>>
        <?php if ( has_post_thumbnail() ) : ?>
        <div class="img-responsive-wrapper">
            <div class="img-responsive ratio ratio-21x9 pb-0 h-auto">
                <figure class="img-wrapper">
                    <?php digital_italia_post_thumbnail('large', ['class' => 'w-100 h-100', 'style' => 'object-fit: cover;']); ?>
                </figure>
            </div>
        </div>
        <?php endif; ?>
        <div class="card-body pb-3 d-flex flex-column flex-hd-row">

            <header>
                <div class="category-top">
                    <span class="category" href="#"><?php printf(get_post_type()) ?></span>
                    <?php if (get_the_modified_date() != get_the_date()) : ?>
                    <span class="data text-muted"><?php digital_italia_updated_on('d F Y', 'Aggiornato il'); ?></span>
                    <?php endif; ?>
                </div><!-- .entry-date -->
                    <?php the_title( sprintf( '<h3 class="card-title h3"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h3>' ); ?>
            </header><!-- .entry-header -->

            <div class="entry-summary">
                <?php the_excerpt(); ?>
                <?php if ( 'post' === get_post_type() ) : ?>
                <div class="mt-3 fw-semibold small fst-italic">
                    <?php digital_italia_posted_on('d F Y', 'Pubblicato il'); ?> <?php digital_italia_posted_by('da'); ?><!-- .entry-meta -->
                </div>
                <?php endif; ?>
            </div><!-- .entry-summary -->

            <?php if ( 'post' === get_post_type() ) : ?>
                <div class="pt-4">
                    <?php digital_italia_taxonomy(); ?><!-- .entry-taxonomy -->
                </div>
            <?php endif; ?>

        </div>

        <footer class="entry-footer mb-n5 pe-4 pt-4 pb-3 text-end">
            <?php digital_italia_entry_footer(); ?>
        </footer><!-- .entry-footer -->
    </article>
</div><!-- #post-<?php the_ID(); ?> -->
