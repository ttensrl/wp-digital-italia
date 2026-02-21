<?php
/**
 * Custom template tags for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package digital-italia
 */

if ( ! function_exists( 'digital_italia_posted_on' ) ) :
    /**
     * Prints HTML with meta information for the current post-date/time.
     */
    function digital_italia_posted_on($format = 'd F Y', $pre = 'Posted on', $post = '') {
        $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';

        $time_string = sprintf(
            $time_string,
            esc_attr( get_the_date( DATE_W3C ) ),
            esc_html( get_the_date( $format ) ),
        );

        $posted_on = sprintf(
        /* translators: %s: post date. */
            esc_html_x( '%s', 'post date', 'wp-digital-italia' ),
            '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
        );

        echo '<span class="posted-on">' . $pre . ' ' . $posted_on . ' '.$post . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

    }
endif;

if ( ! function_exists( 'digital_italia_updated_on' ) ) :
    /**
     * Prints HTML with meta information for the current post-date/time.
     */
    function digital_italia_updated_on($format = 'd F Y', $pre = 'Updated on', $post = '') {
        if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
            $time_string = '<time class="entry-date updated" datetime="%1$s">%2$s</time>';
        }

        $time_string = sprintf(
            $time_string,
            esc_attr( get_the_modified_date( DATE_W3C ) ),
            esc_html( get_the_modified_date( $format ) ),
        );

        $posted_on = sprintf(
        /* translators: %s: post date. */
            esc_html_x( '%s', 'post date', 'wp-digital-italia' ),
            $time_string
        );

        echo '<span class="posted-on">' . $pre . ' ' . $posted_on . ' '.$post . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

    }
endif;

if ( ! function_exists( 'digital_italia_posted_by' ) ) :
	/**
	 * Prints HTML with meta information for the current author.
	 */
	function digital_italia_posted_by($pre = 'by', $post = '') {

		$byline = sprintf(
			/* translators: %s: post author. */
			esc_html_x( $pre.' %s '.$post, 'post author', 'wp-digital-italia' ),
			'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . ucfirst( esc_html( get_the_author() ) ) . '</a></span>'
		);

		echo '<span class="byline"> ' . $byline . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}
endif;

if ( ! function_exists( 'digital_italia_entry_footer' ) ) :
	/**
	 * Prints HTML with meta information and comments.
	 */
	function digital_italia_entry_footer() {

		if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
			echo '<span class="comments-link ms-4"><svg class="icon icon-sm icon-primary" aria-hidden="true"><use href="' . esc_url(get_template_directory_uri()) . '/dist/images/sprites.svg#it-comment"></use></svg> ';
			comments_popup_link(
				sprintf(
					wp_kses(
						/* translators: %s: post title */
						__( 'Leave a Comment <span class="visually-hidden"> on %s</span>', 'wp-digital-italia' ),
                        array(
                            'span' => array(
                                'class' => array(),
                            ),
                            'svg' => array(
                                'class' => array(),
                                'aria-hidden' => array(),
                            ),
                            'use' => array(
                                'href' => array(),
                            ),
                        )
					),
					wp_kses_post( get_the_title() )
				)
			);
			echo '</span>';
		}

		edit_post_link(
			sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers */
					__( 'Edit <span class="screen-reader-text">%s</span>', 'wp-digital-italia' ),
					array(
						'span' => array(
							'class' => array(),
						),
                        'svg' => array(
                            'class' => array(),
                            'aria-hidden' => array(),
                        ),
                        'use' => array(
                            'href' => array(),
                        ),
					)
				),
				wp_kses_post( get_the_title() )
			),
			'<span class="edit-link ms-4"><svg class="icon icon-sm icon-primary" aria-hidden="true"><use href="' . esc_url(get_template_directory_uri()) . '/dist/images/sprites.svg#it-pencil"></use></svg> ',
			'</span>'
		);
	}
endif;

if ( ! function_exists( 'digital_italia_category_links' ) ) :
    function digital_italia_category_links( $post_id = false ) {

        $categories = get_the_terms( $post_id, 'category');

        if ( empty( $categories ) ) {
            /** This filter is documented in wp-includes/category-template.php */
            return apply_filters( 'the_category', __( 'Uncategorized', 'wp-digital-italia' ));
        }

        $thelist = '<ul class="post-categories list-unstyled d-flex flex-wrap gap-2 mb-0 pt-1">';
        foreach ( $categories as $category ) {
            $thelist .= "\n\t<li class='list-inline-item me-0'>";
            $thelist .= '<a class="chip chip-primary chip-simple" href="' . esc_url( get_category_link( $category->term_id ) ) . '" >' . ucfirst($category->name) . '</a></li>';
        }
        $thelist .= '</ul>';

        return apply_filters( 'the_category', $thelist );
    }

endif;

if ( ! function_exists( 'digital_italia_tag_links' ) ) :
    function digital_italia_tag_links( $post_id = 0 ) {
        $tags = get_the_terms( $post_id, 'post_tag');
        if(!$tags){
            return false;
        }

        $thelist = '<ul class="post-tags list-unstyled d-flex flex-wrap gap-2 mb-0 pt-1">';
        foreach ( $tags as $tag ) {
            $thelist .= "\n\t<li class='list-inline-item me-0'>";
            $thelist .= '<a class="chip chip-primary chip-simple" href="' . esc_url( get_category_link( $tag->term_id ) ) . '" >' . ucfirst($tag->name) . '</a></li>';
        }
        $thelist .= '</ul>';

        return $thelist;
    }

endif;

if ( ! function_exists( 'digital_italia_taxonomy' ) ) :
    /**
     * Prints HTML with meta information for the categories and tags and.
     */
    function digital_italia_taxonomy() {
        // Hide category and tag text for pages.
        if ( 'post' === get_post_type() ) {
            $taxonomy = '';

            /* translators: used between list items, there is a space after the comma */
            $categories_list = digital_italia_category_links( esc_html__( '', 'wp-digital-italia' ) );
            if ( $categories_list ) {
                /* translators: 1: list of categories. */
                $taxonomy .= '<section>';
                $taxonomy .= '<header><h4 class="small mb-0">Category:</h4></header>';
                $taxonomy .= $categories_list;
                $taxonomy .= '</section>';
            }

            /* translators: used between list items, there is a space after the comma */
            $tags_list = digital_italia_tag_links( esc_html_x( '', 'tags list', 'wp-digital-italia' ) );
            if ( $tags_list ) {
                /* translators: 1: list of tags. */
                $taxonomy .= '<section class="mt-4">';
                $taxonomy .= '<header><h4 class="small mb-0">Topics:</h4></header>';
                $taxonomy .= $tags_list;
                $taxonomy .= '</section>';
            }

            printf($taxonomy);
        }
    }
endif;

if ( ! function_exists( 'digital_italia_post_thumbnail' ) ) :
	/**
	 * Displays an optional post thumbnail.
	 *
	 * Wraps the post thumbnail in an anchor element on index views, or a div
	 * element when on single views.
	 */
	function digital_italia_post_thumbnail($class = 'post-image') {
		if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
			return;
		}

		if ( is_singular() ) :
			?>

			<div class="post-thumbnail">
				<?php the_post_thumbnail('',['class' => $class] ); ?>
			</div><!-- .post-thumbnail -->

		<?php else : ?>

			<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<?php
					the_post_thumbnail(
						'post-thumbnail',
						array(
							'alt' => the_title_attribute(
								array(
									'echo' => false,
								)
							),
						)
					);
				?>
			</a>

			<?php
		endif; // End is_singular().
	}
endif;

if ( ! function_exists( 'wp_body_open' ) ) :
	/**
	 * Shim for sites older than 5.2.
	 *
	 * @link https://core.trac.wordpress.org/ticket/12563
	 */
	function wp_body_open() {
		do_action( 'wp_body_open' );
	}
endif;
