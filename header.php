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
                            <div class="it-socials d-none d-md-flex">
                                <ul>
                                    <?php
                                    // Verifica se i campi dei social sono popolati
                                    $facebook_url = get_theme_mod('site_socials_fb');
                                    $twitter_url = get_theme_mod('site_socials_twitter');
                                    $instagram_url = get_theme_mod('site_socials_instagram');

                                    // Facebook
                                    if (!empty($facebook_url)) {
                                        echo '<li><a href="' . esc_url($facebook_url) . '" aria-label="Facebook" target="_blank"><svg class="icon"><use href="' . esc_url(get_template_directory_uri()) . '/bootstrap-italia/svg/sprites.svg#it-facebook"></use></svg></a></li>';
                                    }

                                    // Twitter
                                    if (!empty($twitter_url)) {
                                        echo '<li><a href="' . esc_url($twitter_url) . '" aria-label="Twitter" target="_blank"><svg class="icon"><use href="' . esc_url(get_template_directory_uri()) . '/bootstrap-italia/svg/sprites.svg#it-twitter"></use></svg></a></li>';
                                    }

                                    // Instagram
                                    if (!empty($instagram_url)) {
                                        echo '<li><a href="' . esc_url($instagram_url) . '" aria-label="Instagram" target="_blank"><svg class="icon"><use href="' . esc_url(get_template_directory_uri()) . '/bootstrap-italia/svg/sprites.svg#it-instagram"></use></svg></a></li>';
                                    }
                                    ?>
                                </ul>
                            </div>

                            <div class="it-search-wrapper no-print">
                                <div class="d-flex">
                                    <form method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" accept-charset="UTF-8" class="d-flex">
                                        <div class="input-group pl-5 pl-lg-0">
                                            <label for="main-search" class="visually-hidden">Ricerca</label>
                                            <input type="text" class="form-control" name="s" id="main-search" placeholder="Cerca..." value="<?php echo get_search_query(); ?>">
                                            <div class="input-group-append" id="button-addon2-wrapper">
                                                <button class="btn px-3 rounded-0 d-flex align-items-center" type="submit" aria-label="Submit">
                                                    <svg class="icon icon-sm text-secondary">
                                                        <use xlink:href="<?php echo get_template_directory_uri(); ?>/bootstrap-italia/svg/sprites.svg#it-search"></use>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

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
    <!-- /Nuovo Header -->
