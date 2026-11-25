<?php
/**
 * Theme helper functions that the original Neuros widgets call but which the original theme provides.
 *
 * SOURCE (verified on the server, Neuros 2.2.1):
 *   neuros_output_code()             themes/neuros/core/helper-functions.php:204
 *   neuros_get_all_taxonomy_terms()  themes/neuros/core/layout-functions.php:763
 *   neuros_get_all_post_list()       themes/neuros/core/layout-functions.php:842
 * The bodies below are copies of those functions (same signatures, same return shapes). Do not "improve" them: the
 * saved Elementor widget settings depend on these exact return values (slug => name, ID => title).
 *
 * Global namespace on purpose: the widgets call them unqualified from namespace Neuros\Widgets and PHP falls back to
 * the global function. Each is function_exists-guarded (the original theme and the old MU shim guard the same way), so
 * activating the original theme again never causes "cannot redeclare".
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Output Code
if ( ! function_exists( 'neuros_output_code' ) ) {
	function neuros_output_code( $code ) {
		return $code;
	}
}

// Get all Taxonomy Terms Array  (slug => name)
if ( ! function_exists( 'neuros_get_all_taxonomy_terms' ) ) {
	function neuros_get_all_taxonomy_terms( $post_type = null, $taxonomy = null ) {
		$terms_arr = [];
		if ( ! empty( $post_type ) && ! empty( $taxonomy ) ) {
			$terms = get_terms(
				[
					'taxonomy'     => $taxonomy,
					'type'         => $post_type,
					'child_of'     => 0,
					'parent'       => '',
					'orderby'      => 'name',
					'order'        => 'ASC',
					'hierarchical' => 1,
					'exclude'      => '',
					'include'      => '',
					'number'       => 0,
					'pad_counts'   => false,
				]
			);
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) { // is_wp_error guard: an unregistered taxonomy must not fatal
				foreach ( $terms as $key => $term ) {
					$terms_arr[ $term->slug ] = $term->name;
				}
			}
		}

		return $terms_arr;
	}
}

// Get all post of type list  (ID => title)
if ( ! function_exists( 'neuros_get_all_post_list' ) ) {
	function neuros_get_all_post_list( $type = '' ) {
		$post_list = [];
		if ( ! empty( $type ) ) {
			$all_posts = get_posts(
				[
					'post_type'   => sanitize_key( $type ),
					'numberposts' => '-1',
				]
			);

			if ( $all_posts > 0 ) {
				foreach ( $all_posts as $post ) {
					setup_postdata( $post );
					$post_list[ $post->ID ] = $post->post_title;
				}
			} else {
				$post_list = array(
					'no_posts' => esc_html__( 'No Posts Were Found', 'neuros' ),
				);
			}
		}

		return $post_list;
	}
}
