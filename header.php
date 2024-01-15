<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package digital-italia
 */
$belong_administration_logo = get_theme_mod( 'belong_administration_logo' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php get_template_part( 'template-parts/cookie-bar' ); ?>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'digital-italia' ); ?></a>

    <!-- Nuovo Header -->
    <header class="it-header-wrapper">
        <!-- Menu principale in alto (responsive) -->
        <div class="it-header-slim-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="it-header-slim-wrapper-content">
                            <?php
                            if ( $belong_administration_logo ) {
                                echo '<a class="d-none d-lg-block navbar-brand" href="' . esc_url( home_url( '/' ) ) . '">';
                                echo '<img src="' . esc_url( $belong_administration_logo ) . '" alt="Logo Amministrazione Belong" height="30">';
                                echo '</a>';
                            }
                            ?>
                            <div class="nav-mobile">
                                <nav aria-label="Navigazione accessoria">
                                    <a class="it-opener d-lg-none" data-bs-toggle="collapse" href="#menu1a" role="button" aria-expanded="false" aria-controls="menu4">
                                        <span>Ente appartenenza</span>
                                        <svg class="icon" aria-hidden="true">
                                            <use href="<?php echo get_template_directory_uri(); ?>/bootstrap-italia/svg/sprites.svg#it-expand"></use>
                                        </svg>
                                    </a>
                                    <div class="link-list-wrapper collapse" id="menu1a">
                                        <?php
                                        wp_nav_menu(
                                            array(
                                                'theme_location' => 'top-menu',
                                                'container' => false,
                                                'menu_class' => 'list-item',
                                                'fallback_cb' => '__return_false',
                                                'items_wrap' => '<ul id="%1$s" class="link-list %2$s">%3$s</ul>',
                                                'depth' => 1,
                                                'walker' => new bootstrap_5_wp_simple_menu_walker()
                                            )
                                        );
                                        ?>
                                    </div>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="it-nav-wrapper">
            <!-- Header con logo a sinistra e ricerca a destra -->
            <div class="it-header-center-wrapper theme-light">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="it-header-center-content-wrapper">
                                <div class="it-brand-wrapper">
                                    <a href="#">
                                        <?php the_custom_logo(); ?>
                                    </a>
                                </div>
                                <div class="it-right-zone">
                                    <?php
                                    // Verifica se almeno un campo dei social è popolato
                                    $facebook_url = get_theme_mod('site_socials_fb');
                                    $twitter_url = get_theme_mod('site_socials_twitter');
                                    $instagram_url = get_theme_mod('site_socials_instagram');

                                    if (!empty($facebook_url) || !empty($twitter_url) || !empty($instagram_url)) {
                                        ?>
                                        <div class="it-socials d-none d-md-flex">
                                            <span>Seguici su:</span>
                                            <ul>
                                                <?php
                                                // Facebook
                                                if (!empty($facebook_url)) {
                                                    echo '<li class="ms-3"><a href="' . esc_url($facebook_url) . '" aria-label="Facebook" target="_blank"><svg class="icon ms-0"><use href="' . esc_url(get_template_directory_uri()) . '/bootstrap-italia/svg/sprites.svg#it-facebook"></use></svg></a></li>';
                                                }

                                                // Twitter
                                                if (!empty($twitter_url)) {
                                                    echo '<li class="ms-3"><a href="' . esc_url($twitter_url) . '" aria-label="Twitter" target="_blank"><svg class="icon ms-0"><use href="' . esc_url(get_template_directory_uri()) . '/bootstrap-italia/svg/sprites.svg#it-twitter"></use></svg></a></li>';
                                                }

                                                // Instagram
                                                if (!empty($instagram_url)) {
                                                    echo '<li class="ms-3"><a href="' . esc_url($instagram_url) . '" aria-label="Instagram" target="_blank"><svg class="icon ms-0"><use href="' . esc_url(get_template_directory_uri()) . '/bootstrap-italia/svg/sprites.svg#it-instagram"></use></svg></a></li>';
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                        <?php
                                    }
                                    ?>


                                    <div class="it-search-wrapper no-print">
                                        <span class="d-none d-md-block">Cerca</span>
                                        <a href="#" class="search-link rounded-icon" data-bs-toggle="modal" data-bs-target="#search-modal" aria-label="Mostra ricerca">
                                            <svg class="icon"><use xlink:href="<?php echo get_template_directory_uri(); ?>/bootstrap-italia/svg/sprites.svg#it-search"></use></svg>
                                            <span class="visually-hidden">Mostra ricerca</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Altro menu sotto (responsive) -->
            <div class="it-header-navbar-wrapper">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <!--start nav-->
                            <nav class="navbar navbar-expand-lg has-megamenu" aria-label="Navigazione principale">
                                <button class="custom-navbar-toggler" type="button" aria-controls="nav1" aria-expanded="false" aria-label="Mostra/Nascondi la navigazione" data-bs-toggle="navbarcollapsible" data-bs-target="#nav1">
                                    <svg class="icon bg-override">
                                        <use href="<?php echo get_template_directory_uri(); ?>/bootstrap-italia/svg/sprites.svg#it-burger"></use>
                                    </svg>
                                </button>
                                <div class="navbar-collapsable" id="nav1" style="display: none;">
                                    <div class="overlay" style="display: none;"></div>
                                    <div class="close-div">
                                        <button class="btn close-menu" type="button">
                                            <span class="visually-hidden">Nascondi la navigazione</span>
                                            <svg class="icon">
                                                <use href="<?php echo get_template_directory_uri(); ?>/bootstrap-italia/svg/sprites.svg#it-close-big"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="menu-wrapper">
                                        <?php
                                        wp_nav_menu(
                                            array(
                                                'theme_location' => 'main-menu',
                                                'container' => false,
                                                'menu_class' => '',
                                                'fallback_cb' => '__return_false',
                                                'items_wrap' => '<ul id="%1$s" class="navbar-nav %2$s">%3$s</ul>',
                                                'depth' => 2,
                                                'walker' => new bootstrap_5_wp_main_menu_walker()
                                            )
                                        );
                                        ?>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div class="modal fade search-modal" id="search-modal" tabindex="-1" data-focus-mouse="false" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-fullscreen m-0" role="document">
            <div class="modal-content perfect-scrollbar">
                <div class="modal-body">
                    <div class="container">
                        <div class="row variable-gutters">
                            <div class="col">
                                <div class="modal-title">
                                    <button class="btn btn-link  d-md-none d-flex align-items-center px-2 me-2" type="button" data-bs-toggle="modal" data-bs-target="#search-modal" aria-label="Chiudi">
                                        <svg class="icon icon-sm icon-secondary">
                                            <use href="<?php echo get_template_directory_uri(); ?>/bootstrap-italia/svg/sprites.svg#it-arrow-left"></use>
                                        </svg>
                                    </button>
                                    <h2 class="mb-0">Ricerca</h2>
                                    <button class="btn-close search-link d-none d-md-block me-0" type="button" data-bs-toggle="modal" data-bs-target="#search-modal" aria-label="Chiudi" data-focus-mouse="false">
                                        <svg class="icon icon-md icon-secondary">
                                            <use href="<?php echo get_template_directory_uri(); ?>/bootstrap-italia/svg/sprites.svg#it-close-big"></use>
                                        </svg>
                                    </button>
                                </div>
                                <?php get_search_form(); ?>
                            </div>
                        </div>
                        <?php
                        /*
                        <!-- SUGGERIMENTI RISULTATI -->
                        <div class="row variable-gutters">
                            <div class="col-lg-5">
                                <div class="searches-list-wrapper">
                                    <div class="other-link-title">FORSE STAVI CERCANDO</div>
                                    <ul class="searches-list list-unstyled">
                                        <li>
                                            <a href="#">Rilascio Carta Identità Elettronica (CIE)</a>
                                        </li>
                                        <li>
                                            <a href="#">Cambio di residenza</a>
                                        </li>
                                        <li>
                                            <a href="#">Tributi online</a>
                                        </li>
                                        <li>
                                            <a href="#">Prenotazione appuntamenti</a>
                                        </li>
                                        <li>
                                            <a href="#">Rilascio tessera elettorale</a>
                                        </li>
                                        <li>
                                            <a href="#">Voucher connettività</a>
                                        </li>
                                    </ul><!-- /searches-list -->
                                </div><!-- /searches-list-wrapper -->
                            </div>
                        </div>
                        */
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Nuovo Header -->
