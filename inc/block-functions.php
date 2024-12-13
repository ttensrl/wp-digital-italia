<?php


function digital_italia_render_blocks(): void
{
    $registry = WP_Block_Type_Registry::get_instance();
    if ( $registry->is_registered( 'core/latest-posts' ) ) {
        $block = $registry->get_registered( 'core/latest-posts' );
        $block->render_callback = 'dgItalyBlockLastPosts';
    }
};

function is_widget_context(): bool
{
    global $wp_current_filter;

    foreach ( $wp_current_filter as $filter ) {
        if ( str_contains($filter, 'widget') || $filter === 'dynamic_sidebar' ) {
            return true;
        }
    }
    return false;
}

/*
 * ********************************************************************************************************************
 * RENDER BLOCKS
 * ********************************************************************************************************************
 */

function dgItalyBlockLastPosts( $attributes, $content ): string
{
    if ( is_widget_context() ) {
        // Allow the default rendering for widgets
        return render_block_core_latest_posts($attributes);
    }
    // Set default values for attributes
    $attributes = wp_parse_args( $attributes, [
        'postsToShow'               => 5,
        'displayPostContent'        => false,
        'postLayout'                => 'list', // Options: 'grid', 'list'
        'displayFeaturedImage'      => false,
        'featuredImageSizeSlug'     => 'thumbnail',
        'displayPostContentRadio'   => 'excerpt', // Options: 'excerpt', 'full'
        'excerptLength'             => 55,
        'displayAuthor'             => false,
        'displayPostDate'           => false,
        'columns'                   => 1,
        'order'                     => 'desc',
        'orderBy'                   => 'date',
        'addLinkToFeaturedImage'    => false,
    ]);

    // Query posts
    $args = [
        'posts_per_page' => $attributes['postsToShow'],
        'post_status'    => 'publish',
        'orderby'        => $attributes['orderBy'],
        'order'          => $attributes['order'],
    ];
    $query = new WP_Query($args);

    // Bootstrap Italia classes
    $layout_class = $attributes['postLayout'] === 'grid' ? 'row' : 'list-group';
    $column_class = $attributes['postLayout'] === 'grid' ? 'col-md-' . (12 / max(1, $attributes['columns'])) : 'list-group-item';

    // Start output
    $output = '<div class="custom-latest-posts ' . esc_attr($layout_class) . '">';

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();

            // Featured image
            $featured_image = '';
            if ( $attributes['displayFeaturedImage'] && has_post_thumbnail() ) {
                $image = get_the_post_thumbnail( get_the_ID(), $attributes['featuredImageSizeSlug'], [ 'class' => 'img-fluid mb-3' ] );
                if ( $attributes['addLinkToFeaturedImage'] ) {
                    $featured_image = '<a href="' . get_permalink() . '">' . $image . '</a>';
                } else {
                    $featured_image = $image;
                }
            }

            // Post content or excerpt
            $post_content = '';
            if ( $attributes['displayPostContent'] ) {
                if ( $attributes['displayPostContentRadio'] === 'excerpt' ) {
                    $post_content = '<p class="text-muted">' . wp_trim_words( get_the_excerpt(), $attributes['excerptLength'], '...' ) . '</p>';
                } else {
                    $post_content = '<p class="text-muted">' . get_the_content() . '</p>';
                }
            }

            // Post meta (author, date)
            $post_meta = '';
            if ( $attributes['displayAuthor'] || $attributes['displayPostDate'] ) {
                $meta_parts = [];
                if ( $attributes['displayAuthor'] ) {
                    $meta_parts[] = 'By ' . get_the_author();
                }
                if ( $attributes['displayPostDate'] ) {
                    $meta_parts[] = get_the_date();
                }
                $post_meta = '<p class="small text-muted">' . implode( ' | ', $meta_parts ) . '</p>';
            }

            // Construct post item
            $output .= '<div class="' . esc_attr($column_class) . '">';
            $output .= '<div class="card">';
            if ( $featured_image ) {
                $output .= '<div class="card-img-top">' . $featured_image . '</div>';
            }
            $output .= '<div class="card-body">';
            $output .= '<h3 class="card-title h5"><a class="text-decoration-none" href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
            $output .= $post_meta;
            $output .= $post_content;
            $output .= '</div>'; // End card-body
            $output .= '</div>'; // End card
            $output .= '</div>'; // End column or list item
        }
        wp_reset_postdata();
    } else {
        $output .= '<p>No posts found.</p>';
    }

    $output .= '</div>'; // End layout

    return $output;
}