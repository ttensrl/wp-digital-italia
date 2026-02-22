<?php
require_once(get_template_directory() . '/classes/bootstrap_5_wp_main_menu_walker.php');
require_once(get_template_directory() . '/classes/bootstrap_5_wp_simple_menu_walker.php');
require_once(get_template_directory() . '/classes/bootstrap_5_wp_inline_menu_walker.php');
require_once(get_template_directory() . '/inc/block-functions.php');
require_once(get_template_directory() . '/inc/elementor-support.php');
require_once(get_template_directory() . '/companion-plugin/inc/cmb2/cmb2.php');
require_once(get_template_directory() . '/inc/utils.php');
require_once(get_template_directory() . '/inc/breadcrumbs.php');

/**
 * digital-italia functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package digital-italia
 */

if ( ! defined( '_S_VERSION' ) ) {
    // Replace the version number of the theme on each release.
    define( '_S_VERSION', '1.0.0' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function digital_italia_setup(): void
{

    /*
        * Make theme available for translation.
        * Translations can be filed in the /languages/ directory.
        * If you're building a theme based on digital-italia, use a find and replace
        * to change 'wp-digital-italia' to the name of your theme in all the template files.
        */
    load_theme_textdomain( 'wp-digital-italia', get_template_directory() . '/languages' );

    // Add default posts and comments RSS feed links to head.
    add_theme_support( 'automatic-feed-links' );

    /*
        * Let WordPress manage the document title.
        * By adding theme support, we declare that this theme does not use a
        * hard-coded <title> tag in the document head, and expect WordPress to
        * provide it for us.
        */
    add_theme_support( 'title-tag' );

    /*
        * Enable support for Post Thumbnails on posts and pages.
        *
        * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
        */
    add_theme_support( 'post-thumbnails' );

    add_image_size('loop-thumb', 640, 420, true);

    // This theme uses wp_nav_menu() in one location.
    register_nav_menus(
        array(
            'top-menu' => esc_html__( 'Top Menu', 'wp-digital-italia' ),
            'main-menu' => esc_html__( 'Main Menu', 'wp-digital-italia' ),
            'footer-one' => esc_html__( 'Footer One', 'wp-digital-italia' ),
            'footer-two' => esc_html__( 'Footer Two', 'wp-digital-italia' ),
            'footer-three' => esc_html__( 'Footer Three', 'wp-digital-italia' ),
            'footer-four' => esc_html__( 'Footer Four', 'wp-digital-italia' ),
            'footer-bottom' => esc_html__( 'Footer Bottom', 'wp-digital-italia' ),
        )
    );

    /*
        * Switch default core markup for search form, comment form, and comments
        * to output valid HTML5.
        */
    add_theme_support(
        'html5',
        array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        )
    );

    // Set up the WordPress core custom background feature.
    add_theme_support(
        'custom-background',
        apply_filters(
            'digital_italia_custom_background_args',
            array(
                'default-color' => 'ffffff',
                'default-image' => '',
            )
        )
    );

    // Add theme support for selective refresh for widgets.
    add_theme_support( 'customize-selective-refresh-widgets' );

    /**
     * Add support for core custom logo.
     *
     * @link https://codex.wordpress.org/Theme_Logo
     */
    add_theme_support(
        'custom-logo',
        array(
            'height'      => 250,
            'width'       => 250,
            'flex-width'  => true,
            'flex-height' => true,
        )
    );

}
add_action( 'after_setup_theme', 'digital_italia_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function digital_italia_content_width() {
    $GLOBALS['content_width'] = apply_filters( 'digital_italia_content_width', 640 );
}
add_action( 'after_setup_theme', 'digital_italia_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function digital_italia_widgets_init(): void
{
    register_sidebar(
        array(
            'name'          => esc_html__( 'Sidebar', 'wp-digital-italia' ),
            'id'            => 'sidebar-1',
            'description'   => esc_html__( 'Add widgets here.', 'wp-digital-italia' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s sidebar-wrapper it-line-right-side border-0"><div class="sidebar-linklist-wrapper"><div class="link-list-wrapper">',
            'after_widget'  => '</div></div></section>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        )
    );
}
add_action( 'widgets_init', 'digital_italia_widgets_init' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
    require get_template_directory() . '/inc/jetpack.php';
}

/**
 * BOOTSTRAP ITALIA SUPPORT
 */

if ( ! function_exists( 'get_menu_by_location' ) ):
    function get_menu_by_location( $theme_location ) {
        $theme_locations = get_nav_menu_locations();
        $menu_obj = get_term( $theme_locations[ $theme_location ], 'nav_menu' );
        if ( $menu_obj ) {
            return wp_get_nav_menu_items($menu_obj->term_id, array());
        }
        return [];
    }
endif;

add_action( 'wp_enqueue_scripts', function() {

    /**
     * ASSETS COMPILATI DA VITE (bootstrap-italia + leaflet)
     */
    wp_enqueue_style(
        'theme-dist-style',
        get_template_directory_uri() . '/dist/css/main.css',
        [],
        _S_VERSION
    );

    wp_enqueue_style( 'digital-italia-style', get_stylesheet_uri(), array(), _S_VERSION );
    wp_style_add_data( 'digital-italia-style', 'rtl', 'replace' );

    wp_enqueue_script( 'digital-italia-navigation', get_template_directory_uri() . '/assets/js/navigation.js', array(), _S_VERSION, true );

    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }

    wp_register_script(
        'theme-dist-script',
        get_template_directory_uri() . '/dist/js/main.js',
        [],
        _S_VERSION,
        true
    );

    $bundle_options = [
        'cookie_expire' => get_theme_mod('cookie_expiration_setting', 365)
    ];
    /**
     * OPZIONI COOKIES
     */
    $cookies_vars = [];
    $prefix = 'cookies_settings_consent_';
    $options = get_theme_mods();
    foreach ($options as $option_name => $option_value) {
        if (str_starts_with($option_name, $prefix)) {
            $cookies_vars[] = $option_name;
        }
    }
    wp_localize_script( 'theme-dist-script', 'cookiesVars', $cookies_vars );
    wp_localize_script( 'theme-dist-script', 'cookiesSettings', $bundle_options );
    wp_enqueue_script('theme-dist-script');

    if(!is_admin()) {
        wp_deregister_script('jquery');
        wp_register_script( 'jquery', includes_url('/js/jquery/jquery.js'), false, false, true );
        wp_enqueue_script('jquery');
    }

    /**
     * DISPATCHER
     */
    wp_enqueue_script( 'cookies-dispatcher', get_template_directory_uri() . '/assets/js/cookies-dispatcher.js', ['cookies-settings'], _S_VERSION, true);

    // Enqueue script per il pulsante prenota servizio
    wp_register_script( 'servizio-booking', get_template_directory_uri() . '/assets/js/booking.js', ['jquery', 'theme-dist-script'], _S_VERSION, true );
    wp_enqueue_script( 'servizio-booking' );

    // Enqueue booking CSS (stile minimale per calendario e slot)
    wp_enqueue_style( 'booking-style', get_template_directory_uri() . '/assets/css/booking.css', array(), _S_VERSION );

    // Enqueue script per la mappa leaflet (ora globale)
    wp_register_script(
        'dci-leaflet-map',
        get_template_directory_uri() . '/assets/js/leaflet-map.js',
        [ 'theme-dist-script' ],
        _S_VERSION,
        true
    );
    wp_enqueue_script('dci-leaflet-map');

} );

function digital_italia_sanitize_checkbox( $checked ) {
    return ( ( isset( $checked ) && true == $checked ) ? true : false );
}

add_action( 'customize_register', function( $wp_customize ) {
    // Aggiungi il controllo di caricamento file
    $wp_customize->add_setting( 'belong_administration_logo', array(
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_setting( 'white_logo', array(
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( new WP_Customize_Upload_Control( $wp_customize, 'belong_administration_logo', array(
        'label'    => 'Logo Amministrazione d\'appartenenza',
        'section'  => 'title_tagline',
        'settings' => 'belong_administration_logo',
    ) ) );

    $wp_customize->add_control( new WP_Customize_Upload_Control( $wp_customize, 'white_logo', array(
        'label'    => 'Logo chiaro per Footer',
        'section'  => 'title_tagline',
        'settings' => 'white_logo',
    ) ) );

    // Aggiungi la sezione "Site Contact"
    $wp_customize->add_section( 'site_contact', array(
        'title'    => 'Site Contact',
        'priority' => 30,
    ) );

    // Aggiungi i campi per la sezione "Site Contact"
    $wp_customize->add_setting( 'site_contact_email', array(
        'sanitize_callback' => 'sanitize_email',
    ) );
    $wp_customize->add_setting( 'site_contact_phone', array(
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_setting( 'site_contact_address', array(
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_setting( 'site_contact_vat', array(
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_setting( 'site_contact_city', array(
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_setting( 'site_contact_urp', array(
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_setting( 'site_contact_administration_transparent', array(
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'site_contact_email', array(
        'label'    => 'Email',
        'section'  => 'site_contact',
        'settings' => 'site_contact_email',
    ) );

    $wp_customize->add_control( 'site_contact_phone', array(
        'label'    => 'Telefono',
        'section'  => 'site_contact',
        'settings' => 'site_contact_phone',
    ) );

    $wp_customize->add_control( 'site_contact_urp', array(
        'label'    => 'URL Ufficio relazioni con il pubblico',
        'section'  => 'site_contact',
        'settings' => 'site_contact_urp',
    ) );

    $wp_customize->add_control( 'site_contact_administration_transparent', array(
        'label'    => 'URL Amministrazione Trasparente',
        'section'  => 'site_contact',
        'settings' => 'site_contact_administration_transparent',
    ) );

    $wp_customize->add_control( 'site_contact_city', array(
        'label'    => 'Città',
        'section'  => 'site_contact',
        'settings' => 'site_contact_city',
    ) );

    $wp_customize->add_control( 'site_contact_address', array(
        'label'    => 'Indirizzo',
        'section'  => 'site_contact',
        'settings' => 'site_contact_address',
    ) );

    $wp_customize->add_control( 'site_contact_vat', array(
        'label'    => 'Partita IVA',
        'section'  => 'site_contact',
        'settings' => 'site_contact_vat',
    ) );

    // Aggiungi la sezione "Site Socials"
    $wp_customize->add_section( 'site_socials', array(
        'title'    => 'Site Socials',
        'priority' => 31,
    ) );

    // Aggiungi i campi per la sezione "Site Socials"
    $wp_customize->add_setting( 'site_socials_fb', array(
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_setting( 'site_socials_instagram', array(
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_setting( 'site_socials_twitter', array(
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'site_socials_fb', array(
        'label'    => 'Facebook',
        'section'  => 'site_socials',
        'settings' => 'site_socials_fb',
    ) );

    $wp_customize->add_control( 'site_socials_instagram', array(
        'label'    => 'Instagram',
        'section'  => 'site_socials',
        'settings' => 'site_socials_instagram',
    ) );

    $wp_customize->add_control( 'site_socials_twitter', array(
        'label'    => 'Twitter',
        'section'  => 'site_socials',
        'settings' => 'site_socials_twitter',
    ) );

    // COOKIE
    $wp_customize->add_section( 'cookies_settings_section', array(
        'title' => 'Cookies Settings',
        'priority' => 32
    ) );

    // Aggiungi il controllo per il testo del banner
    $wp_customize->add_setting( 'banner_text_setting', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_textarea_field',
    ) );
    $wp_customize->add_control( 'banner_text_setting', array(
        'label'   => 'Testo del Banner',
        'section' => 'cookies_settings_section',
        'type'    => 'textarea',
    ) );

    $wp_customize->add_setting('cookie_expiration_setting', array(
        'default' => 365,
        'sanitize_callback' => 'absint',
    ));

    $wp_customize->add_control('cookie_expiration_setting', array(
        'label' => 'Scadenza dei Cookie (in giorni)',
        'section' => 'cookies_settings_section',
        'type' => 'number',
    ));

    $wp_customize->add_setting( 'privacy_page_setting', array(
        'default' => '',
        'sanitize_callback' => 'absint',
    ));

    $wp_customize->add_control( 'privacy_page_setting', array(
        'label' => 'Pagina della policy',
        'type'  => 'dropdown-pages',
        'section' => 'cookies_settings_section',
    ));

    $wp_customize->add_setting( 'cookies_settings_consent_analytics', array(
        'default' => false,
        'sanitize_callback' => 'digital_italia_sanitize_checkbox',
    ) );
    $wp_customize->add_control( 'cookies_settings_consent_analytics', array(
        'label'   => 'Abilita cookies di Analytics',
        'section' => 'cookies_settings_section',
        'type'    => 'checkbox',
    ) );

    $wp_customize->add_setting( 'cookies_settings_consent_marketing', array(
        'default' => false,
        'sanitize_callback' => 'digital_italia_sanitize_checkbox',
    ) );
    $wp_customize->add_control( 'cookies_settings_consent_marketing', array(
        'label'   => 'Abilita cookies di Marketing',
        'section' => 'cookies_settings_section',
        'type'    => 'checkbox',
    ) );

    $wp_customize->add_setting( 'cookies_settings_consent_social', array(
        'default' => false,
        'sanitize_callback' => 'digital_italia_sanitize_checkbox',
    ) );
    $wp_customize->add_control( 'cookies_settings_consent_social', array(
        'label'   => 'Abilita cookies Social',
        'section' => 'cookies_settings_section',
        'type'    => 'checkbox',
    ) );

    $wp_customize->add_setting( 'cookies_settings_consent_youtube', array(
        'default' => false,
        'sanitize_callback' => 'digital_italia_sanitize_checkbox',
    ) );

    $wp_customize->add_control( 'cookies_settings_consent_youtube', array(
        'label'   => 'Abilita cookies di YouTube',
        'section' => 'cookies_settings_section',
        'type'    => 'checkbox',
    ) );
});

add_filter( 'image_size_names_choose', function ($sizes){
    return array_merge( $sizes, array(
        'loop-thumb' => __( 'Loop Thumb', 'wp-digital-italia' ),
    ) );
});

// Hook to override the block render callback
add_action( 'init', 'digital_italia_render_blocks');

/**
 * Define booking system constants BEFORE loading classes
 */
if (!defined('SWB_PLUGIN_URL')) {
    define('SWB_PLUGIN_URL', get_template_directory_uri() . '/inc/includes/');
}

/**
 * Include custom post types
 */
require_once(get_template_directory() . '/inc/admin/tipologie/tipologia_servizio.php');
require_once(get_template_directory() . '/inc/admin/tipologie/tipologia_appuntamento.php');
require_once(get_template_directory() . '/inc/admin/tipologie/tipologia_persona.php');
require_once(get_template_directory() . '/inc/admin/tipologie/tipologia_dipartimento.php');

/**
 * Include booking system
 */
require_once(get_template_directory() . '/inc/includes/class-swb-slot-manager.php');
require_once(get_template_directory() . '/inc/includes/class-swb-closures-manager.php');
require_once(get_template_directory() . '/inc/includes/class-swb-modals.php');
require_once(get_template_directory() . '/inc/includes/class-swb-calendar-view.php');
require_once(get_template_directory() . '/inc/includes/class-swb-admin.php');
require_once(get_template_directory() . '/inc/includes/class-swb-api.php');

/**
 * Initialize booking system
 */
function digital_italia_booking_init() {
    $slot_manager = new SWB_Slot_Manager();
    $slot_manager->init();

    $admin = new SWB_Admin();
    $admin->init();

    $api = new SWB_API();
    $api->init();
}
add_action('after_setup_theme', 'digital_italia_booking_init', 20);

/**
 * Create booking slots table on theme activation
 */
function digital_italia_create_booking_table(): void
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . 'booking_slots';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        service_id bigint(20) NOT NULL,
        uo_id bigint(20) NOT NULL,
        slot_date date NOT NULL,
        slot_start_time time NOT NULL,
        slot_end_time time NOT NULL,
        max_bookings int(11) DEFAULT 1,
        current_bookings int(11) DEFAULT 0,
        is_active tinyint(1) DEFAULT 1,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY service_id (service_id),
        KEY uo_id (uo_id),
        KEY slot_date (slot_date),
        KEY is_active (is_active)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    $reservations_table = $wpdb->prefix . 'booking_reservations';

    $sql2 = "CREATE TABLE IF NOT EXISTS $reservations_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        slot_id bigint(20) NOT NULL,
        user_email varchar(255) NOT NULL,
        user_name varchar(255) NOT NULL,
        user_phone varchar(50),
        booking_date datetime DEFAULT CURRENT_TIMESTAMP,
        status enum('pending','confirmed','cancelled') DEFAULT 'pending',
        notes text,
        PRIMARY KEY  (id),
        KEY slot_id (slot_id),
        KEY user_email (user_email),
        KEY status (status)
    ) $charset_collate;";

    dbDelta($sql2);
}
add_action('after_switch_theme', 'digital_italia_create_booking_table');

// Fallback per funzioni DCI (definite nel tema child) - evitare errori se il child non è attivo
if (!function_exists('dci_filter_uo_with_services')) {
    function dci_filter_uo_with_services($unita_organizzative) {
        if (empty($unita_organizzative) || !is_array($unita_organizzative)) {
            return array();
        }

        $filtered = array();

        foreach ($unita_organizzative as $uo) {
            if (!is_object($uo) || !isset($uo->ID)) {
                continue;
            }

            $servizi_offerti_raw = get_post_meta($uo->ID, '_dci_unita_organizzativa_elenco_servizi_offerti', true);
            $servizi_offerti = is_string($servizi_offerti_raw)
                ? maybe_unserialize($servizi_offerti_raw)
                : $servizi_offerti_raw;

            if (!empty($servizi_offerti) && is_array($servizi_offerti) && count($servizi_offerti) > 0) {
                $filtered[] = $uo;
                if (function_exists('error_log')) {
                    error_log('DCI: UO "' . $uo->post_title . '" (ID: ' . $uo->ID . ') offre ' . count($servizi_offerti) . ' servizi');
                }
            } else {
                if (function_exists('error_log')) {
                    error_log('DCI: UO "' . $uo->post_title . '" (ID: ' . $uo->ID . ') ESCLUSA - nessun servizio offerto');
                }
            }
        }

        if (function_exists('error_log')) {
            error_log('DCI: Filtrate ' . count($filtered) . ' UO su ' . count($unita_organizzative) . ' totali');
        }

        return $filtered;
    }
}

if (!function_exists('dci_uo_offers_service')) {
    function dci_uo_offers_service($uo_id, $service_id) {
        $servizi_offerti_raw = get_post_meta($uo_id, '_dci_unita_organizzativa_elenco_servizi_offerti', true);
        $servizi_offerti = is_string($servizi_offerti_raw)
            ? maybe_unserialize($servizi_offerti_raw)
            : $servizi_offerti_raw;

        if (empty($servizi_offerti) || !is_array($servizi_offerti)) {
            return false;
        }

        return in_array($service_id, array_map('intval', $servizi_offerti));
    }
}