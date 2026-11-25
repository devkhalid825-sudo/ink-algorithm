<?php
/**
 * The original theme's AJAX handler for its listings (wp_ajax_pagination), re-provided for the NK theme.
 *
 * The original Neuros widgets (blog / services / projects / team listings: page numbers, category filters) call
 *   admin-ajax.php?action=pagination
 * That action was registered by the ORIGINAL THEME (themes/neuros/functions.php: ajax_pagination()). Under the NK theme it did
 * not exist, so admin-ajax answered "0" (HTTP 400) and the widget printed "AJAX ERROR". This is the same handler (same
 * request, same HTML), with the unsafe parts of the original closed: the original ran WP_Query with ANY arguments sent by the
 * visitor (so a visitor could list drafts/private posts). Here only listing arguments are accepted and the status is always
 * "publish".
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	if ( nkc_original_theme_active() || ! nkc_legacy_theme() ) {
		return; // the original theme registers its own handler
	}
	add_action( 'wp_ajax_pagination', 'nkc_ajax_pagination' );
	add_action( 'wp_ajax_nopriv_pagination', 'nkc_ajax_pagination' );
}, 5 );

/** Strings of a decoded JSON object, cleaned (the widget flags: "yes", numbers, a CSS class, button text). */
function nkc_clean_widget( $v ) {
	if ( is_array( $v ) ) {
		return array_map( 'nkc_clean_widget', $v );
	}
	return is_string( $v ) ? sanitize_text_field( $v ) : $v;
}

function nkc_ajax_pagination() {
	$paged  = isset( $_POST['paged'] ) ? max( 1, absint( $_POST['paged'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
	$raw    = isset( $_POST['args'] ) ? json_decode( wp_unslash( $_POST['args'] ), true ) : array(); // phpcs:ignore
	$raw    = is_array( $raw ) ? $raw : array();
	$widget = isset( $_POST['widget'] ) ? json_decode( wp_unslash( $_POST['widget'] ), true ) : array(); // phpcs:ignore
	$widget = is_array( $widget ) ? nkc_clean_widget( $widget ) : array();
	$term   = isset( $_POST['filter_term'] ) ? sanitize_title( wp_unslash( $_POST['filter_term'] ) ) : ''; // phpcs:ignore
	$tax    = isset( $_POST['filter_taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['filter_taxonomy'] ) ) : ''; // phpcs:ignore
	$id     = isset( $_POST['id'] ) ? sanitize_key( wp_unslash( $_POST['id'] ) ) : ''; // phpcs:ignore
	$class  = isset( $_POST['classes'] ) ? trim( preg_replace( '/[^A-Za-z0-9_\- ]/', '', wp_unslash( $_POST['classes'] ) ) ) : ''; // phpcs:ignore

	/* listing arguments only, and only published content */
	$ptype = isset( $raw['post_type'] ) && is_string( $raw['post_type'] ) ? sanitize_key( $raw['post_type'] ) : 'post';
	$po    = post_type_exists( $ptype ) ? get_post_type_object( $ptype ) : null;
	if ( ! $po || ! $po->public ) {
		$ptype = 'post';
	}
	$args = array(
		'post_type'           => $ptype,
		'post_status'         => 'publish',
		'posts_per_page'      => isset( $raw['posts_per_page'] ) ? max( 1, min( 24, (int) $raw['posts_per_page'] ) ) : 6,
		'paged'               => $paged,
		'ignore_sticky_posts' => ! empty( $raw['ignore_sticky_posts'] ),
	);
	if ( isset( $raw['orderby'] ) && is_string( $raw['orderby'] ) && in_array( $raw['orderby'], array( 'date', 'title', 'menu_order', 'rand', 'modified', 'ID', 'name' ), true ) ) {
		$args['orderby'] = $raw['orderby'];
	}
	if ( isset( $raw['order'] ) && in_array( strtoupper( (string) $raw['order'] ), array( 'ASC', 'DESC' ), true ) ) {
		$args['order'] = strtoupper( $raw['order'] );
	}
	foreach ( array( 'category_name', 'tag' ) as $k ) {
		if ( ! empty( $raw[ $k ] ) && is_string( $raw[ $k ] ) ) {
			$args[ $k ] = sanitize_text_field( $raw[ $k ] );
		}
	}
	if ( ! empty( $raw['post__in'] ) ) {
		$ids = array_filter( array_map( 'absint', is_array( $raw['post__in'] ) ? $raw['post__in'] : explode( ',', (string) $raw['post__in'] ) ) );
		if ( $ids ) {
			$args['post__in'] = $ids;
		}
	}
	if ( ! empty( $raw['orig_offset'] ) ) {
		$args['offset'] = (int) $raw['orig_offset'] + ( $paged - 1 ) * $args['posts_per_page'];
	}
	if ( $term && $tax && 'all' !== $term && taxonomy_exists( $tax ) && is_object_in_taxonomy( $ptype, $tax ) ) {
		$args[ $tax ] = $term; // same behaviour as the original (taxonomy slug as query var)
	}
	$link_base = isset( $raw['link_base'] ) && is_string( $raw['link_base'] ) ? esc_url_raw( $raw['link_base'] ) : '';
	if ( $link_base && wp_parse_url( $link_base, PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST ) ) {
		$link_base = ''; // never build page links to another host
	}

	$query        = new WP_Query( $args );
	$listing_type = isset( $widget['listing_type'] ) ? $widget['listing_type'] : '';
	$parts        = array( 'content-' . $ptype . '.php', 'content.php' );

	echo '<div class="' . esc_attr( $class ) . '">';
	while ( $query->have_posts() ) {
		$query->the_post();
		nkc_legacy_template_part( $parts, $widget );
	}
	if ( 'masonry' === $listing_type ) {
		echo '<div class="grid-sizer"></div>';
	}
	wp_reset_postdata();
	echo '</div>';

	if ( isset( $widget['show_pagination'] ) && 'yes' === $widget['show_pagination'] && $query->max_num_pages > 1 ) {
		echo '<div class="content-pagination"><nav class="navigation pagination" role="navigation"><h2 class="screen-reader-text">' . esc_html__( 'Pagination', 'neuros' ) . '</h2><div class="nav-links">';
		echo paginate_links( array( // phpcs:ignore WordPress.Security.EscapeOutput
			'base'               => $link_base . '/?' . esc_attr( $id ) . '-paged=%#%',
			'current'            => max( 1, $paged ),
			'total'              => $query->max_num_pages,
			'end_size'           => 2,
			'before_page_number' => '<span class="button-inner"></span>',
			'prev_text'          => esc_html__( 'Previous', 'neuros' ) . '<span class="button-inner"></span><span class="icon-button-arrow left"></span><span class="icon-button-arrow right"></span>',
			'next_text'          => esc_html__( 'Next', 'neuros' ) . '<span class="button-inner"></span><span class="icon-button-arrow left"></span><span class="icon-button-arrow right"></span>',
			'add_args'           => false,
		) );
		echo '</div></nav></div>';
	}
	wp_die();
}
