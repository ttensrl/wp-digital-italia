<?php

/**
 * Definisce post type Dipartimento
 */
add_action( 'init', 'dci_register_post_type_dipartimento' );
function dci_register_post_type_dipartimento() {

    $labels = array(
        'name'                  => _x( 'Dipartimenti', 'Post Type General Name', 'digital-italia' ),
        'singular_name'         => _x( 'Dipartimento', 'Post Type Singular Name', 'digital-italia' ),
        'add_new'               => _x( 'Aggiungi un Dipartimento', 'Post Type Singular Name', 'digital-italia' ),
        'add_new_item'          => _x( 'Aggiungi un Dipartimento', 'Post Type Singular Name', 'digital-italia' ),
        'featured_image'        => __( 'Logo del Dipartimento', 'digital-italia' ),
        'edit_item'             => _x( 'Modifica il Dipartimento', 'Post Type Singular Name', 'digital-italia' ),
        'view_item'             => _x( 'Visualizza il Dipartimento', 'Post Type Singular Name', 'digital-italia' ),
        'set_featured_image'    => __( 'Seleziona Logo' ),
        'remove_featured_image' => __( 'Rimuovi Logo' , 'digital-italia' ),
        'use_featured_image'    => __( 'Usa come Logo' , 'digital-italia' ),
    );

    $args = array(
        'label'            => __( 'Dipartimento', 'digital-italia' ),
        'labels'           => $labels,
        'supports'         => array( 'title', 'editor', 'thumbnail' ),
        'hierarchical'     => true,
        'public'           => true,
        'menu_position'    => 5,
        'menu_icon'        => 'dashicons-building',
        'has_archive'      => true,
        'description'      => __( "Dipartimenti dell'organizzazione.", 'digital-italia' ),
        'show_in_rest'       => true,
        'rest_base'          => 'dipartimenti',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );

    register_post_type( 'dipartimento', $args );
}

/**
 * Crea i metabox del post type Dipartimento
 */
add_action( 'cmb2_init', 'dci_add_dipartimento_metaboxes' );
function dci_add_dipartimento_metaboxes()
{
    $prefix = '_dci_dipartimento_';

    $cmb_dati = new_cmb2_box(array(
        'id' => $prefix . 'box_dati',
        'title' => __('Dati Dipartimento'),
        'object_types' => array('dipartimento'),
        'context' => 'normal',
        'priority' => 'high',
    ));

    $cmb_dati->add_field( array(
        'id' => $prefix . 'email',
        'desc' => __( 'Email di contatto' , 'digital-italia' ),
        'name'  => __( 'Email', 'digital-italia' ),
        'type' => 'text_email',
    ) );

    $cmb_dati->add_field( array(
        'id' => $prefix . 'telefono',
        'desc' => __( 'Telefono' , 'digital-italia' ),
        'name'  => __( 'Telefono', 'digital-italia' ),
        'type' => 'text',
    ) );

    $cmb_dati->add_field( array(
        'id' => $prefix . 'indirizzo',
        'desc' => __( 'Indirizzo completo (via, numero, città, CAP)' , 'digital-italia' ),
        'name'  => __( 'Indirizzo', 'digital-italia' ),
        'type' => 'textarea_small',
    ) );

    $cmb_dati->add_field( array(
        'id' => $prefix . 'latitudine',
        'name'  => __( 'Latitudine', 'digital-italia' ),
        'desc' => __( 'Coordinate generate automaticamente dall\'indirizzo' , 'digital-italia' ),
        'type' => 'text',
        'attributes' => array(
            'type' => 'number',
            'step' => 'any',
            'readonly' => 'readonly',
        ),
    ) );

    $cmb_dati->add_field( array(
        'id' => $prefix . 'longitudine',
        'name'  => __( 'Longitudine', 'digital-italia' ),
        'type' => 'text',
        'attributes' => array(
            'type' => 'number',
            'step' => 'any',
            'readonly' => 'readonly',
        ),
    ) );

    $cmb_organigramma = new_cmb2_box(array(
        'id' => $prefix . 'box_organigramma',
        'title' => __('Organigramma'),
        'object_types' => array('dipartimento'),
        'context' => 'normal',
        'priority' => 'high',
    ));

    $group_field_id = $cmb_organigramma->add_field( array(
        'id'          => $prefix . 'organigramma',
        'type'        => 'group',
        'description' => __( 'Persone che compongono l\'organigramma del dipartimento', 'digital-italia' ),
        'options'     => array(
            'group_title'       => __( 'Persona {#}', 'digital-italia' ),
            'add_button'        => __( 'Aggiungi Persona', 'digital-italia' ),
            'remove_button'     => __( 'Rimuovi Persona', 'digital-italia' ),
            'sortable'          => true,
        ),
    ) );

    $cmb_organigramma->add_group_field( $group_field_id, array(
        'name'       => __( 'Persona', 'digital-italia' ),
        'id'         => 'persona_id',
        'type'       => 'select',
        'options_cb' => 'dci_get_persone_options',
    ) );

    $cmb_organigramma->add_group_field( $group_field_id, array(
        'name'       => __( 'Ruolo nel dipartimento', 'digital-italia' ),
        'desc'       => __( 'es: Responsabile, Dirigente, Addetto', 'digital-italia' ),
        'id'         => 'ruolo',
        'type'       => 'text',
    ) );
}

add_action( 'admin_enqueue_scripts', 'dci_dipartimento_admin_scripts' );
function dci_dipartimento_admin_scripts( $hook ) {
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

    $options = array( '' => __( '— Seleziona una persona —', 'digital-italia' ) );
    foreach ( $persone as $persona ) {
        $options[ $persona->ID ] = $persona->post_title;
    }

    return $options;
}
