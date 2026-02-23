<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( 'wpdi_get_breadcrumbs' ) ) {
    $breadcrumbs = wpdi_get_breadcrumbs( array(
        'separator'        => '<svg class="icon"><use href="' . esc_url( get_template_directory_uri() ) . '/dist/images/sprites.svg#it-chevron-right"></use></svg>',
        'container_class'  => 'breadcrumb-container container-fluid',
        'list_class'       => 'breadcrumb',
        'item_class'       => 'breadcrumb-item',
    ) );
    
    if ( ! empty( $breadcrumbs ) ) {
        echo '<div class="breadcrumb-wrapper p-3">';
        echo $breadcrumbs;
        echo '</div>';
    }
}
