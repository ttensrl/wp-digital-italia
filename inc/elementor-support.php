<?php
add_filter( 'the_content', function( $content ) {
    if ( ! is_singular() || ! class_exists( '\Elementor\Plugin' ) ) {
        return $content;
    }

    // Esegui solo se il post corrente è quello della pagina principale
    if ( get_the_ID() !== get_queried_object_id() ) {
        return $content;
    }

    $document = \Elementor\Plugin::$instance->documents->get( get_the_ID() );

    if ( $document && $document->is_built_with_elementor() ) {
        $breadcrumbs = '';
        if ( function_exists( 'wpdi_get_breadcrumbs' ) ) {
            $breadcrumbs = wpdi_get_breadcrumbs();
        }

        return '<div class="container-fluid">' . $breadcrumbs . $content . '</div>';
    }

    return $content;
}, 10 );

add_action('wp_enqueue_scripts', function () {
    if ( is_singular('post') ) {
        $post_id = get_queried_object_id();
        if ( ! $post_id ) return;

        $has_elementor = get_post_meta($post_id, '_elementor_data', true);

        if ( empty($has_elementor) ) {
            wp_dequeue_style('elementor-frontend');
            wp_dequeue_style('elementor-post-' . $post_id);
            wp_dequeue_script('elementor-frontend');
            wp_dequeue_script('elementor-frontend-modules');
            wp_dequeue_script('elementor-waypoints');
        }
    }
}, 9999);