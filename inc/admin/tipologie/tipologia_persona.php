<?php

/**
 * Definisce post type Persona
 */
add_action( 'init', 'dci_register_post_type_persona' );
function dci_register_post_type_persona() {

    $labels = array(
        'name'                  => _x( 'Persone', 'Post Type General Name', 'digital-italia' ),
        'singular_name'         => _x( 'Persona', 'Post Type Singular Name', 'digital-italia' ),
        'add_new'               => _x( 'Aggiungi una Persona', 'Post Type Singular Name', 'digital-italia' ),
        'add_new_item'          => _x( 'Aggiungi una Persona', 'Post Type Singular Name', 'digital-italia' ),
        'featured_image'        => __( 'Foto della Persona', 'digital-italia' ),
        'edit_item'             => _x( 'Modifica la Persona', 'Post Type Singular Name', 'digital-italia' ),
        'view_item'             => _x( 'Visualizza la Persona', 'Post Type Singular Name', 'digital-italia' ),
        'set_featured_image'    => __( 'Seleziona Foto' ),
        'remove_featured_image' => __( 'Rimuovi Foto' , 'digital-italia' ),
        'use_featured_image'    => __( 'Usa come Foto' , 'digital-italia' ),
    );

    $args = array(
        'label'            => __( 'Persona', 'digital-italia' ),
        'labels'           => $labels,
        'supports'         => array( 'title', 'editor', 'thumbnail' ),
        'hierarchical'     => false,
        'public'           => true,
        'menu_position'    => 5,
        'menu_icon'        => 'dashicons-admin-users',
        'has_archive'      => true,
        'description'      => __( "Persone che compongono l'organizzazione.", 'digital-italia' ),
        'show_in_rest'       => true,
        'rest_base'          => 'persone',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );

    register_post_type( 'persona', $args );
}

/**
 * Crea i metabox del post type Persona
 */
add_action( 'cmb2_init', 'dci_add_persona_metaboxes' );
function dci_add_persona_metaboxes()
{
    $prefix = '_dci_persona_';

    $cmb_dati = new_cmb2_box(array(
        'id' => $prefix . 'box_dati',
        'title' => __('Dati Persona'),
        'object_types' => array('persona'),
        'context' => 'normal',
        'priority' => 'high',
    ));

    $cmb_dati->add_field( array(
        'id' => $prefix . 'nome',
        'desc' => __( 'Nome' , 'digital-italia' ),
        'name'  => __( 'Nome *', 'digital-italia' ),
        'type' => 'text',
        'attributes'    => array(
            'required'    => 'required',
        ),
    ) );

    $cmb_dati->add_field( array(
        'id' => $prefix . 'cognome',
        'desc' => __( 'Cognome' , 'digital-italia' ),
        'name'  => __( 'Cognome *', 'digital-italia' ),
        'type' => 'text',
        'attributes'    => array(
            'required'    => 'required',
        ),
    ) );

    $cmb_dati->add_field( array(
        'id' => $prefix . 'email',
        'desc' => __( 'Email' , 'digital-italia' ),
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
        'id' => $prefix . 'ruolo',
        'desc' => __( 'Ruolo/Ruolo professionale' , 'digital-italia' ),
        'name'  => __( 'Ruolo', 'digital-italia' ),
        'type' => 'text',
    ) );
}
