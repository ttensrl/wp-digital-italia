<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * WP Digital Italia - Breadcrumbs
 *
 * Lightweight, reusable Breadcrumbs class without using output buffering.
 * Provides generate() which returns HTML string and display() which echoes it.
 */
class WPDI_Breadcrumbs {

    /** Default arguments */
    protected $defaults = array(
        'separator'     => '&gt;',
        'container'     => 'nav',
        'container_class' => 'breadcrumb-container',
        'list_class'    => 'breadcrumb',
        'item_class'    => 'breadcrumb-item',
        'home_label'    => 'Home',
        'aria_label'    => 'Percorso di navigazione',
        'show_on_front' => false, // whether to show on front page
        'echo'          => false, // whether generate should echo (we keep no ob_start), display() will echo
        'wrap_links'    => true, // wrap crumb items in <a>
        'microdata'     => true, // include schema.org markup
    );

    public function __construct( $args = array() ) {
        $this->defaults = apply_filters( 'wpdi_breadcrumbs_defaults', $this->defaults );
    }

    /**
     * Generate breadcrumb HTML and return as string
     * @param array $args
     * @return string
     */
    public function generate(array $args = array() ): string
    {
        $args = wp_parse_args( $args, $this->defaults );

        // Early return for front page when not showing
        if ( is_front_page() && ! $args['show_on_front'] ) {
            return '';
        }

        $items = array();

        // Home
        $home_label = $args['home_label'];
        $home_url = home_url( '/' );
        $items[] = $this->make_item( $home_label, $home_url, 1, $args );

        $position = 2;

        if ( is_home() ) {
            // Blog page
            $page_for_posts = get_option( 'page_for_posts' );
            $blog_title = $page_for_posts ? get_the_title( $page_for_posts ) : __( 'Blog', 'wp-digital-italia' );
            $blog_url = get_permalink( $page_for_posts );
            $items[] = $this->make_item( $blog_title, $blog_url, $position++, $args, true );
        } elseif ( is_singular() ) {
            global $post;
            if ( is_singular( 'post' ) ) {
                $cats = get_the_category( $post->ID );
                if ( ! empty( $cats ) ) {
                    $cat = $cats[0];
                    $ancestors = get_ancestors( $cat->term_id, 'category' );
                    $ancestors = array_reverse( $ancestors );
                    foreach ( $ancestors as $anc_id ) {
                        $term = get_term( $anc_id, 'category' );
                        if ( $term && ! is_wp_error( $term ) ) {
                            $items[] = $this->make_item( $term->name, get_category_link( $term ), $position++, $args );
                        }
                    }
                    $items[] = $this->make_item( $cat->name, get_category_link( $cat ), $position++, $args );
                }
                // finally current post
                $items[] = $this->make_item( get_the_title( $post ), get_permalink( $post ), $position++, $args, true );
            } else {
                // For pages and custom post types
                if ( is_page() ) {
                    $ancestors = get_post_ancestors( $post );
                    $ancestors = array_reverse( $ancestors );
                    foreach ( $ancestors as $ancestor_id ) {
                        $items[] = $this->make_item( get_the_title( $ancestor_id ), get_permalink( $ancestor_id ), $position++, $args );
                    }
                    $items[] = $this->make_item( get_the_title( $post ), get_permalink( $post ), $position++, $args, true );
                } else {
                    // custom post type single
                    $post_type = get_post_type_object( get_post_type( $post ) );
                    if ( $post_type && ! empty( $post_type->labels->singular_name ) ) {
                        $archive_link = get_post_type_archive_link( $post->post_type );
                        if ( $archive_link ) {
                            $items[] = $this->make_item( $post_type->labels->name, $archive_link, $position++, $args );
                        }

                        if ( 'bando' === $post->post_type ) {
                            $data_publish = get_post_meta( $post->ID, '_dci_bando_data_publish', true );
                            $year = ! empty( $data_publish )
                                ? date( 'Y', strtotime( $data_publish ) )
                                : get_the_date( 'Y', $post );
                            $year_url = home_url( '/bandi/' . $year . '/' );
                            $items[] = $this->make_item( $year, $year_url, $position++, $args );
                        }
                    }
                    $items[] = $this->make_item( get_the_title( $post ), get_permalink( $post ), $position++, $args, true );
                }
            }
        } elseif ( is_post_type_archive() ) {
            $post_type = get_query_var( 'post_type' );
            if ( ! $post_type ) {
                $post_type = get_post_type();
            }
            if ( $post_type ) {
                $post_type_obj = get_post_type_object( $post_type );
                if ( $post_type_obj ) {
                    $items[] = $this->make_item( $post_type_obj->labels->name, get_post_type_archive_link( $post_type ), $position++, $args, true );
                }
            }
        } elseif ( is_category() || is_tax() ) {
            $term = get_queried_object();
            if ( $term ) {
                $ancestors = get_ancestors( $term->term_id, $term->taxonomy );
                $ancestors = array_reverse( $ancestors );
                foreach ( $ancestors as $anc_id ) {
                    $t = get_term( $anc_id, $term->taxonomy );
                    if ( $t && ! is_wp_error( $t ) ) {
                        $items[] = $this->make_item( $t->name, get_term_link( $t ), $position++, $args );
                    }
                }
                $items[] = $this->make_item( $term->name, get_term_link( $term ), $position++, $args, true );
            }
        } elseif ( is_search() ) {
            $items[] = $this->make_item( sprintf( "Search results for \"%s\"", get_search_query() ), '', $position++, $args, true );
        } elseif ( is_author() ) {
            $author = get_queried_object();
            if ( $author ) {
                $items[] = $this->make_item( sprintf( 'Author: %s', get_the_author_meta( 'display_name', $author->ID ) ), '', $position++, $args, true );
            }
        } elseif ( is_date() ) {
            if ( is_day() ) {
                $items[] = $this->make_item( get_the_date(), '', $position++, $args, true );
            } elseif ( is_month() ) {
                $items[] = $this->make_item( get_the_date( 'F Y' ), '', $position++, $args, true );
            } elseif ( is_year() ) {
                $items[] = $this->make_item( get_the_date( 'Y' ), '', $position++, $args, true );
            }
        } elseif ( is_404() ) {
            $items[] = $this->make_item( '404', '', $position++, $args, true );
        } elseif ( is_archive() ) {
            $title = get_the_archive_title();
            $items[] = $this->make_item( $title, '', $position++, $args, true );
        }

        // Pagination suffix
        if ( get_query_var( 'paged' ) && (int) get_query_var( 'paged' ) > 1 ) {
            $paged = (int) get_query_var( 'paged' );
            $items[] = $this->make_item( sprintf( __( 'Page %d', 'wp-digital-italia' ), $paged ), '', $position++, $args, true );
        }

        // Build HTML
        if ( empty( $items ) ) {
            return '';
        }

        $container = esc_attr( $args['container'] );
        $container_class = esc_attr( $args['container_class'] );
        $list_class = esc_attr( $args['list_class'] );

        $attr = '';
        $aria_label = esc_attr( ! empty( $args['aria_label'] ) ? $args['aria_label'] : $this->defaults['aria_label'] );
        if ( $args['microdata'] ) {
            $attr = ' aria-label="' . $aria_label . '" itemscope itemtype="https://schema.org/BreadcrumbList"';
        } else {
            $attr = ' aria-label="' . $aria_label . '"';
        }

        $html = "<{$container} class=\"{$container_class}\"{$attr}>";
        $html .= "<ol class=\"{$list_class}\">";

        foreach ( $items as $item_html ) {
            $html .= $item_html;
        }

        $html .= '</ol>';
        $html .= '</' . esc_attr( $container ) . '>';

        return $html;
    }

    /**
     * Echo generated breadcrumbs
     * @param array $args
     */
    public function display( $args = array() ) {
        echo $this->generate( $args );
    }

    /**
     * Helper to make an item (li) with microdata when requested
     */
    protected function make_item( $label, $url = '', $position = 0, $args = array(), $is_current = false ) {
        $label = wp_strip_all_tags( $label );
        $item_class = esc_attr( $args['item_class'] );
        $separator_html = ' <span class="separator" aria-hidden="true">' . $args['separator'] . '</span>';

        // build li classes
        $li_classes = $item_class;
        if ( $is_current ) {
            $li_classes .= ' active';
        }

        $li = '<li class="' . $li_classes . '"';
        if ( ! empty( $args['microdata'] ) ) {
            $li .= ' itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"';
        }
        if ( $is_current ) {
            $li .= ' aria-current="page"';
        }
        $li .= '>';

        if ( $url && ! $is_current && ! empty( $args['wrap_links'] ) ) {
            if ( ! empty( $args['microdata'] ) ) {
                $li .= '<a href="' . esc_url( $url ) . '" itemprop="item"><span itemprop="name">' . esc_html( $label ) . '</span></a>';
                $li .= '<meta itemprop="position" content="' . intval( $position ) . '" />';
            } else {
                $li .= '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
            }
            // add separator after non-current linked items
            $li .= $separator_html;
        } else {
            if ( ! empty( $args['microdata'] ) ) {
                $li .= '<span itemprop="name">' . esc_html( $label ) . '</span>';
                $li .= '<meta itemprop="position" content="' . intval( $position ) . '" />';
            } else {
                $li .= '<span>' . esc_html( $label ) . '</span>';
            }
        }

        $li .= '</li>';
        // Add separator (we'll rely on CSS for most styling; separator can be inserted between items if needed)
        return $li;
    }
}

/**
 * Helper: get instance and return HTML
 */
function wpdi_get_breadcrumbs( $args = array() ) {
    $bc = new WPDI_Breadcrumbs();
    return $bc->generate( $args );
}

/**
 * Helper: echo breadcrumbs
 */
function wpdi_breadcrumbs( $args = array() ) {
    $bc = new WPDI_Breadcrumbs();
    $bc->display( $args );
}

