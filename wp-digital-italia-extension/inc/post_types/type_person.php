<?php

/**
 * Definisce post type Persona
 */
add_action( 'init', 'dci_register_post_type_persona' );
function dci_register_post_type_persona() {

    $labels = array(
        'name'                  => _x( 'Persone', 'Post Type General Name', 'wp-digital-italia' ),
        'singular_name'         => _x( 'Persona', 'Post Type Singular Name', 'wp-digital-italia' ),
        'add_new'               => _x( 'Aggiungi una Persona', 'Post Type Singular Name', 'wp-digital-italia' ),
        'add_new_item'          => _x( 'Aggiungi una Persona', 'Post Type Singular Name', 'wp-digital-italia' ),
        'featured_image'        => __( 'Foto della Persona', 'wp-digital-italia' ),
        'edit_item'             => _x( 'Modifica la Persona', 'Post Type Singular Name', 'wp-digital-italia' ),
        'view_item'             => _x( 'Visualizza la Persona', 'Post Type Singular Name', 'wp-digital-italia' ),
        'set_featured_image'    => __( 'Seleziona Foto' ),
        'remove_featured_image' => __( 'Rimuovi Foto' , 'wp-digital-italia' ),
        'use_featured_image'    => __( 'Usa come Foto' , 'wp-digital-italia' ),
    );

    $args = array(
        'label'            => __( 'Persona', 'wp-digital-italia' ),
        'labels'           => $labels,
        'supports'         => array( 'title', 'editor', 'thumbnail' ),
        'hierarchical'     => false,
        'public'           => true,
        'menu_position'    => 5,
        'menu_icon'        => 'dashicons-admin-users',
        'has_archive'      => true,
        'description'      => __( "Persone che compongono l'organizzazione.", 'wp-digital-italia' ),
        'show_in_rest'       => true,
        'rest_base'          => 'persone',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'show_in_nav_menus'   => true,
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
        'title' => __('Dati Persona', 'wp-digital-italia'),
        'object_types' => array('persona'),
        'context' => 'normal',
        'priority' => 'high',
    ));

    $cmb_dati->add_field( array(
        'id' => $prefix . 'nome',
        'desc' => __( 'Nome' , 'wp-digital-italia' ),
        'name'  => __( 'Nome *', 'wp-digital-italia' ),
        'type' => 'text',
        'attributes'    => array(
            'required'    => 'required',
        ),
    ) );

    $cmb_dati->add_field( array(
        'id' => $prefix . 'cognome',
        'desc' => __( 'Cognome' , 'wp-digital-italia' ),
        'name'  => __( 'Cognome *', 'wp-digital-italia' ),
        'type' => 'text',
        'attributes'    => array(
            'required'    => 'required',
        ),
    ) );

    $cmb_dati->add_field( array(
        'id' => $prefix . 'email',
        'desc' => __( 'Email' , 'wp-digital-italia' ),
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
        'id' => $prefix . 'ruolo',
        'desc' => __( 'Ruolo/Ruolo professionale' , 'wp-digital-italia' ),
        'name'  => __( 'Ruolo', 'wp-digital-italia' ),
        'type' => 'text',
    ) );

    $cmb_incarichi = new_cmb2_box(array(
        'id' => $prefix . 'box_incarichi',
        'title' => __('Incarichi', 'wp-digital-italia'),
        'object_types' => array('persona'),
        'context' => 'normal',
        'priority' => 'high',
    ));

    $group_incarichi_id = $cmb_incarichi->add_field( array(
        'id'          => $prefix . 'incarichi',
        'type'        => 'group',
        'description' => __( 'Storico degli incarichi ricoperti dalla persona', 'wp-digital-italia' ),
        'options'     => array(
            'group_title'       => __( 'Incarico {#}', 'wp-digital-italia' ),
            'add_button'        => __( 'Aggiungi Incarico', 'wp-digital-italia' ),
            'remove_button'     => __( 'Rimuovi Incarico', 'wp-digital-italia' ),
            'sortable'          => true,
        ),
    ) );

    $cmb_incarichi->add_group_field( $group_incarichi_id, array(
        'name'       => __( 'Incarico', 'wp-digital-italia' ),
        'id'         => 'incarico',
        'type'       => 'text',
    ) );

    $cmb_incarichi->add_group_field( $group_incarichi_id, array(
        'name'       => __( 'Data inizio', 'wp-digital-italia' ),
        'id'         => 'data_inizio',
        'type'       => 'text_date',
    ) );

    $cmb_incarichi->add_group_field( $group_incarichi_id, array(
        'name'       => __( 'Data fine', 'wp-digital-italia' ),
        'desc'       => __( 'Lascia vuoto per incarico corrente', 'wp-digital-italia' ),
        'id'         => 'data_fine',
        'type'       => 'text_date',
    ) );
}

function dci_get_incarico_corrente( $post_id = null ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }

    $incarichi = dci_get_meta( 'incarichi', '_dci_persona_', $post_id );

    if ( ! $incarichi || ! is_array( $incarichi ) ) {
        return null;
    }

    foreach ( $incarichi as $incarico ) {
        if ( empty( $incarico['data_fine'] ) ) {
            return $incarico;
        }
    }

    return null;
}
