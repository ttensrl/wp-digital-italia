<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package digital-italia
 */
$hide_title = $args['hide_title'] ?? false;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('px-3'); ?>>
    <header class="entry-header text-center">
        <?php
        if (!$hide_title) {
            the_title('<h1 class="entry-title text-primary">', '</h1>');
        }
        ?>
    </header><!-- .entry-header -->


	<?php digital_italia_post_thumbnail(); ?>

	<div class="entry-content">
		<?php
		the_content();

		wp_link_pages(
			array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'wp-digital-italia' ),
				'after'  => '</div>',
			)
		);
		?>
	</div><!-- .entry-content -->

	<?php if ( get_edit_post_link() ) : ?>
        <footer class="entry-footer pe-4 pt-4 pb-3 text-end">
            <?php digital_italia_entry_footer(); ?>
        </footer><!-- .entry-footer -->
	<?php endif; ?>
</article><!-- #post-<?php the_ID(); ?> -->
