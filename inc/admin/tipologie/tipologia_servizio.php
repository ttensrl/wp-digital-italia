<?php

/**
 * Definisce post type Servizio
 */
add_action( 'init', 'dci_register_post_type_servizio' );
function dci_register_post_type_servizio() {

	/** servizio **/
	$labels = array(
		'name'                  => _x( 'Servizi', 'Post Type General Name', 'digital-italia' ),
		'singular_name'         => _x( 'Servizio', 'Post Type Singular Name', 'digital-italia' ),
		'add_new'               => _x( 'Aggiungi un Servizio', 'Post Type Singular Name', 'digital-italia' ),
		'add_new_item'          => _x( 'Aggiungi un Servizio', 'Post Type Singular Name', 'digital-italia' ),
		'featured_image'        => __( 'Logo Identificativo del Servizio', 'digital-italia' ),
		'edit_item'             => _x( 'Modifica il Servizio', 'Post Type Singular Name', 'digital-italia' ),
		'view_item'             => _x( 'Visualizza il Servizio', 'Post Type Singular Name', 'digital-italia' ),
		'set_featured_image'    => __( 'Seleziona Logo' ),
		'remove_featured_image' => __( 'Rimuovi Logo' , 'digital-italia' ),
		'use_featured_image'    => __( 'Usa come Logo' , 'digital-italia' ),
	);

	$args = array(
		'label'            => __( 'Servizio', 'digital-italia' ),
		'labels'           => $labels,
		'supports'         => array( 'title', 'editor' ),
		'hierarchical'     => false,
		'public'           => true,
        'menu_position'    => 5,
        'menu_icon'        => 'dashicons-id-alt',
		'has_archive'      => false,
        'description'      => __( "I servizi che il comune mette a disposizione del cittadino.", 'digital-italia' ),
        'show_in_rest'       => true,
        'rest_base'          => 'servizi',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
	);

	register_post_type( 'servizio', $args );
}
