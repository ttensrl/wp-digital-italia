<?php

/**
 * Definisce post type Dipartimento
 */
add_action( 'init', 'dci_register_post_type_dipartimento' );
function dci_register_post_type_dipartimento(): void
{

    $labels = array(
        'name'                  => _x( 'Dipartimenti', 'Post Type General Name', 'wp-digital-italia' ),
        'singular_name'         => _x( 'Dipartimento', 'Post Type Singular Name', 'wp-digital-italia' ),
        'add_new'               => _x( 'Aggiungi un Dipartimento', 'Post Type Singular Name', 'wp-digital-italia' ),
        'add_new_item'          => _x( 'Aggiungi un Dipartimento', 'Post Type Singular Name', 'wp-digital-italia' ),
        'featured_image'        => __( 'Logo del Dipartimento', 'wp-digital-italia' ),
        'edit_item'             => _x( 'Modifica il Dipartimento', 'Post Type Singular Name', 'wp-digital-italia' ),
        'view_item'             => _x( 'Visualizza il Dipartimento', 'Post Type Singular Name', 'wp-digital-italia' ),
        'set_featured_image'    => __( 'Seleziona Logo' ),
        'remove_featured_image' => __( 'Rimuovi Logo' , 'wp-digital-italia' ),
        'use_featured_image'    => __( 'Usa come Logo' , 'wp-digital-italia' ),
    );

    $args = array(
        'label'            => __( 'Dipartimento', 'wp-digital-italia' ),
        'labels'           => $labels,
        'supports'         => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
        'hierarchical'     => true,
        'public'           => true,
        'menu_position'    => 5,
        'menu_icon'        => 'dashicons-building',
        'has_archive'      => true,
        'description'      => __( "Dipartimenti dell'organizzazione.", 'wp-digital-italia' ),
        'show_in_rest'       => true,
        'rest_base'          => 'dipartimenti',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'show_in_nav_menus'   => true,
    );

    register_post_type( 'dipartimento', $args );
}

/**
 * Crea i metabox del post type Dipartimento
 */
add_action( 'cmb2_init', 'dci_add_dipartimento_metaboxes' );
function dci_add_dipartimento_metaboxes(): void
{
    $prefix = '_dci_dipartimento_';

    $cmb_dati = new_cmb2_box(array(
        'id' => $prefix . 'box_dati',
        'title' => __('Dati Dipartimento', 'wp-digital-italia'),
        'object_types' => array('dipartimento'),
        'context' => 'normal',
        'priority' => 'high',
    ));

    $cmb_dati->add_field( array(
        'id' => $prefix . 'email',
        'desc' => __( 'Email di contatto' , 'wp-digital-italia' ),
        'name'  => __( 'Email', 'wp-digital-italia' ),
        'type' => 'text_email',
    ) );

    $cmb_dati->add_field( array(
        'id' => $prefix . 'telefono',
        'desc' => __( 'Telefono' , 'wp-digital-italia' ),
        'name'  => __( 'Telefono', 'wp-digital-italia' ),
        'type' => 'text',
    ) );

    $cmb_dati->add_field( array(
        'id' => $prefix . 'indirizzo',
        'desc' => __( 'Indirizzo completo (via, numero, città, CAP)' , 'wp-digital-italia' ),
        'name'  => __( 'Indirizzo', 'wp-digital-italia' ),
        'type' => 'textarea_small',
    ) );

    $cmb_dati->add_field( array(
        'id' => $prefix . 'latitudine',
        'name'  => __( 'Latitudine', 'wp-digital-italia' ),
        'desc' => __( 'Coordinate generate automaticamente dall\'indirizzo' , 'wp-digital-italia' ),
        'type' => 'text',
        'attributes' => array(
            'type' => 'number',
            'step' => 'any',
            'readonly' => 'readonly',
        ),
    ) );

    $cmb_dati->add_field( array(
        'id' => $prefix . 'longitudine',
        'name'  => __( 'Longitudine', 'wp-digital-italia' ),
        'type' => 'text',
        'attributes' => array(
            'type' => 'number',
            'step' => 'any',
            'readonly' => 'readonly',
        ),
    ) );

    $cmb_organigramma = new_cmb2_box(array(
        'id' => $prefix . 'box_organigramma',
        'title' => __('Organigramma', 'wp-digital-italia'),
        'object_types' => array('dipartimento'),
        'context' => 'normal',
        'priority' => 'high',
    ));

    $group_field_id = $cmb_organigramma->add_field( array(
        'id'          => $prefix . 'organigramma',
        'type'        => 'group',
        'description' => __( 'Persone che compongono l\'organigramma del dipartimento', 'wp-digital-italia' ),
        'options'     => array(
            'group_title'       => __( 'Persona {#}', 'wp-digital-italia' ),
            'add_button'        => __( 'Aggiungi Persona', 'wp-digital-italia' ),
            'remove_button'     => __( 'Rimuovi Persona', 'wp-digital-italia' ),
            'sortable'          => true,
        ),
    ) );

    $cmb_organigramma->add_group_field( $group_field_id, array(
        'name'       => __( 'Persona', 'wp-digital-italia' ),
        'id'         => 'persona_id',
        'type'       => 'select',
        'options_cb' => 'dci_get_persone_options',
    ) );

    $cmb_organigramma->add_group_field( $group_field_id, array(
        'name'       => __( 'Ruolo nel dipartimento', 'wp-digital-italia' ),
        'desc'       => __( 'es: Responsabile, Dirigente, Addetto', 'wp-digital-italia' ),
        'id'         => 'ruolo',
        'type'       => 'text',
    ) );

    $cmb_organigramma->add_field( array(
        'name'       => __( 'Responsabile', 'wp-digital-italia' ),
        'desc'       => __( 'Persona responsabile del dipartimento', 'wp-digital-italia' ),
        'id'         => $prefix . 'responsabile',
        'type'       => 'select',
        'options_cb' => 'dci_get_persone_options',
    ) );

    $cmb_servizi = new_cmb2_box(array(
        'id' => $prefix . 'box_servizi',
        'title' => __('Servizi', 'wp-digital-italia'),
        'object_types' => array('dipartimento'),
        'context' => 'normal',
        'priority' => 'high',
    ));

    $cmb_servizi->add_field( array(
        'id'         => $prefix . 'servizi',
        'name'       => __( 'Servizi erogati', 'wp-digital-italia' ),
        'desc'       => __( 'Seleziona i servizi collegati a questo dipartimento', 'wp-digital-italia' ),
        'type'       => 'multicheck',
        'options_cb' => 'dci_get_servizi_options',
    ) );
}

add_action( 'admin_enqueue_scripts', 'dci_dipartimento_admin_scripts' );
function dci_dipartimento_admin_scripts( $hook ): void
{
    global $post_type;

    if ( ( $hook === 'post-new.php' || $hook === 'post.php' ) && $post_type === 'dipartimento' ) {
        wp_enqueue_script(
            'dci-admin-geocoding',
            get_template_directory_uri() . '/assets/js/admin-geocoding.js',
            array( 'jquery' ),
            _S_VERSION,
            true
        );
    }
}

function dci_get_persone_options() {
    $persone = get_posts( array(
        'post_type'      => 'persona',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );

    $options = array( '' => __( '— Seleziona una persona —', 'wp-digital-italia' ) );
    foreach ( $persone as $persona ) {
        $options[ $persona->ID ] = $persona->post_title;
    }

    return $options;
}

function dci_get_responsabile( $post_id = null ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }
    return dci_get_meta( 'responsabile', '_dci_dipartimento_', $post_id );
}

function dci_get_servizi_options() {
    $servizi = get_posts( array(
        'post_type'      => 'servizio',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );

    $options = array();
    foreach ( $servizi as $servizio ) {
        $options[ $servizio->ID ] = $servizio->post_title;
    }

    return $options;
}
