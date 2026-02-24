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

	<main id="main-content">

		<header class="page-header bg-white mb-4 py-4">
			<div class="container-fluid">
				<?php
				if ( is_home() && ! is_front_page() ) :
					single_post_title( '<h1 class="mb-2">', '</h1>' );
				else :
					?>
					<h1 class="mb-2">Articoli</h1>
					<?php
				endif;
				?>
			</div>
		</header>

		<div class="container-fluid py-5">
			<div class="row g-4">
				<div class="col">
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
				</div>
                <div class="col-lg-4 col-xxl-auto">
                    <?php get_sidebar(); ?>
                </div>
			</div>
		</div>

	</main>

<?php
get_footer();
