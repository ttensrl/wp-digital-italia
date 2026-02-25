<?php
/**
 * The template for displaying archive for bando post type
 *
 * @package digital-italia
 */

get_header();

global $wpdb;
$year = get_query_var( 'bando_year' );
?>

	<main id="main-content">

		<header class="page-header bg-white mb-4 py-4">
			<div class="container-fluid">
				<?php
                if ( ! empty( $year ) ) :
					?>
					<h1 class="mb-2"><?php echo esc_html( $year ); ?></h1>
					<?php
				else :
                    the_archive_title( '<h1 class="mb-2">', '</h1>' );
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
						get_template_part( 'template-parts/content/bando' );

					endwhile;

					$years = $wpdb->get_col( "
						SELECT DISTINCT YEAR( pm.meta_value )
						FROM {$wpdb->posts} p
						INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
						WHERE p.post_type = 'bando' 
							AND p.post_status = 'publish'
							AND pm.meta_key = '_dci_bando_data_publish'
							AND pm.meta_value != ''
						ORDER BY YEAR( pm.meta_value ) DESC
					" );

					if ( $years ) :
						?>
						<nav aria-label="Filtro per anno" class="mt-4">
							<ul class="pagination justify-content-center flex-wrap gap-2">
								<li class="page-item<?php echo empty( $year ) ? ' active' : ''; ?>">
									<a class="page-link" href="<?php echo home_url( '/bandi/' ); ?>">Tutti</a>
								</li>
								<?php foreach ( $years as $y ) : ?>
									<li class="page-item<?php echo $year == $y ? ' active' : ''; ?>">
										<a class="page-link" href="<?php echo home_url( '/bandi/' . $y . '/' ); ?>"><?php echo esc_html( $y ); ?></a>
									</li>
								<?php endforeach; ?>
							</ul>
						</nav>
						<?php
					endif;

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
