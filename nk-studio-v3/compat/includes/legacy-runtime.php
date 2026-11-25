<?php
/**
 * Runtime for pages built with the ORIGINAL Neuros widgets, under the NK Studio theme.
 *
 * What the original theme did around those pages, and what this file gives them back — all read-only, nothing is
 * written to the database:
 *  1. Customizer values: the original colours/fonts/options live in theme_mods_neuros. They are merged UNDER the NK
 *     theme's own theme mods (NK keys always win), so the original helper functions see their original settings.
 *  2. Theme library: helper-functions.php + layout-functions.php + customizer-defaults.php are loaded from the original
 *     theme folder the first time they are needed (a bridged plugin template, or a legacy page). The two hooks that
 *     would print the ORIGINAL header/footer are removed immediately, so the NK header/footer stay the only ones.
 *  3. Templates: when an original plugin asks for a theme template (get_template_part), and the NK theme has none of
 *     that name, the template is loaded from the original theme (the blog listing needs content.php for every card).
 *  4. Page frame: on pages that use original widgets the original "page title" banner (templates/page-title) and the
 *     original content wrapper / sidebar are printed around the Elementor content, using the ORIGINAL templates.
 *  5. The 15 original widget areas and the original image sizes are registered again.
 * Everything is skipped while the original theme is the active theme.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nkc_runtime_enabled() {
	return ! nkc_original_theme_active() && (bool) nkc_legacy_theme() && is_dir( WP_PLUGIN_DIR . '/neuros-plugin' );
}

/* ---------------------------------------------------------------- 1. original Customizer values under the NK mods */
add_filter( 'option_theme_mods_' . get_stylesheet(), function ( $mods ) {
	static $orig = null;
	if ( null === $orig ) {
		$orig = array();
		$t    = nkc_runtime_enabled() ? nkc_legacy_theme() : null;
		if ( $t ) {
			$slug = basename( $t['dir'] );
			$o    = get_option( 'theme_mods_' . $slug, array() );
			$orig = is_array( $o ) ? $o : array();
		}
	}
	if ( ! $orig ) {
		return $mods;
	}
	return ( is_array( $mods ) ? $mods : array() ) + $orig; // union: NK's own keys win
} );

/* ---------------------------------------------------------------- 2. theme library (lazy, no header/footer hooks) */
function nkc_load_runtime() {
	static $state = null;
	if ( null !== $state ) {
		return $state;
	}
	$state = false;
	$t     = nkc_runtime_enabled() ? nkc_legacy_theme() : null;
	if ( ! $t ) {
		return false;
	}
	$d = $t['dir'];
	foreach ( array( '/core/helper-functions.php', '/core/layout-functions.php', '/core/customizer/customizer-defaults.php' ) as $f ) {
		if ( ! is_readable( $d . $f ) ) {
			return false;
		}
	}
	nkc_define_metabox_shims();
	require_once $d . '/core/helper-functions.php';
	require_once $d . '/core/layout-functions.php';
	require_once $d . '/core/customizer/customizer-defaults.php';
	// layout-functions.php hooks the ORIGINAL header and footer; the NK theme prints its own.
	remove_action( 'wp_body_open', 'neuros_standard_header' );
	remove_action( 'wp_footer', 'neuros_standard_footer' );
	if ( function_exists( 'init_neuros_customizer_default_values' ) ) {
		remove_action( 'init', 'init_neuros_customizer_default_values', 1 );
		init_neuros_customizer_default_values(); // fills $neuros_customizer_default_values
	}
	return $state = function_exists( 'neuros_get_prefered_option' );
}

/**
 * The original theme reads per-page options through Meta Box (core/metabox.php). Meta Box stores them as plain post
 * meta under the field id, so the same values are read directly (metabox.php itself is not loaded: it registers admin
 * metaboxes and 5000 lines of fields).
 */
function nkc_define_metabox_shims() {
	if ( ! function_exists( 'neuros_post_options' ) ) {
		function neuros_post_options() {
			return class_exists( 'RWMB_Loader' );
		}
	}
	if ( ! function_exists( 'neuros_get_post_option' ) ) {
		function neuros_get_post_option( $name, $default = false ) {
			if ( ! class_exists( 'RWMB_Loader' ) ) {
				return $default;
			}
			$id = get_the_ID();
			$v  = $id ? get_post_meta( $id, $name, true ) : '';
			return $v ? $v : $default;
		}
	}
	if ( ! function_exists( 'neuros_get_post_value' ) ) {
		function neuros_get_post_value( $name, $default = false ) {
			return neuros_get_post_option( $name, $default );
		}
	}
	if ( ! function_exists( 'neuros_get_post_image' ) ) {
		function neuros_get_post_image( $name, $size = 'large', $default = false ) {
			$id  = get_the_ID();
			$ids = $id ? get_post_meta( $id, $name, false ) : array();
			$out = '';
			foreach ( (array) $ids as $att ) {
				$url = is_numeric( $att ) ? wp_get_attachment_image_url( (int) $att, $size ) : '';
				if ( $url ) {
					$out .= '<div class="image_wrapper"><img src="' . esc_url( $url ) . '" alt="' . esc_attr( get_post_meta( (int) $att, '_wp_attachment_image_alt', true ) ) . '"></div>';
				}
			}
			return '' !== $out ? $out : $default;
		}
	}
	if ( ! function_exists( 'neuros_get_post_time' ) ) {
		function neuros_get_post_time( $name, $default = false ) {
			return neuros_get_post_option( $name, $default );
		}
	}
}

/* ---------------------------------------------------------------- 3. theme templates for the original plugins */
add_action( 'get_template_part', function ( $slug, $name, $templates, $args = array() ) {
	if ( ! nkc_runtime_enabled() || ! is_array( $templates ) || ! nkc_caller_is_bridged() ) {
		return;
	}
	$t = nkc_legacy_theme();
	foreach ( $templates as $tpl ) {
		if ( locate_template( $tpl ) ) {
			return; // the active theme has it: never override
		}
		$file = $t['dir'] . '/' . $tpl;
		if ( is_readable( $file ) ) {
			if ( ! nkc_load_runtime() ) {
				return;
			}
			nkc_with_original_filters( function () use ( $file, $args ) {
				load_template( $file, false, is_array( $args ) ? $args : array() );
			} );
			return; // WordPress then finds nothing in the active theme and prints nothing more
		}
	}
}, 10, 4 );

/** Load the first existing template of a get_template_part()-style list: the active theme's, else the original theme's. */
function nkc_legacy_template_part( $templates, $args = array() ) {
	$t = nkc_runtime_enabled() ? nkc_legacy_theme() : null;
	if ( ! $t || ! nkc_load_runtime() ) {
		return false;
	}
	foreach ( (array) $templates as $tpl ) {
		$file = locate_template( $tpl );
		if ( ! $file && is_readable( $t['dir'] . '/' . $tpl ) ) {
			$file = $t['dir'] . '/' . $tpl;
		}
		if ( $file ) {
			nkc_with_original_filters( function () use ( $file, $args ) {
				load_template( $file, false, is_array( $args ) ? $args : array() );
			} );
			return true;
		}
	}
	return false;
}

/** The original theme's excerpt settings, only while an original template renders. */
function nkc_with_original_filters( $fn ) {
	$len  = function () { return 41; };
	$more = function () { return '...'; };
	add_filter( 'excerpt_length', $len, 99 );
	add_filter( 'excerpt_more', $more, 99 );
	try {
		$fn();
	} finally {
		remove_filter( 'excerpt_length', $len, 99 );
		remove_filter( 'excerpt_more', $more, 99 );
	}
}

/* ---------------------------------------------------------------- 4. page frame (title banner, wrapper, sidebar) */
add_filter( 'the_content', function ( $content ) {
	static $done = false;
	if ( $done || is_admin() || ! is_singular( 'page' ) || ! in_the_loop() || ! is_main_query() || ! nkc_runtime_enabled() ) {
		return $content;
	}
	if ( (int) get_the_ID() !== (int) get_queried_object_id() || ! nkc_is_neuros_page() ) {
		return $content;
	}
	$level = ob_get_level();
	try {
		if ( ! nkc_load_runtime() ) {
			return $content;
		}
		$t        = nkc_legacy_theme();
		$title_on = 'on' === neuros_get_prefered_option( 'page_title_status' );
		$out      = '';
		if ( $title_on ) {
			$cls = 'top-page-wrapper';
			if ( neuros_get_prefered_option( 'header_style' ) ) {
				$cls .= ' header-' . neuros_get_prefered_option( 'header_style' );
			}
			if ( neuros_get_prefered_option( 'page_top_border_radius' ) ) {
				$cls .= ' top-page-wrapper-br-' . neuros_get_prefered_option( 'page_top_border_radius' );
			}
			if ( neuros_get_prefered_option( 'header_position' ) ) {
				$cls .= ' header-position-' . neuros_get_prefered_option( 'header_position' );
			}
			ob_start();
			load_template( $t['dir'] . '/templates/page-title/page-title.php', false );
			$out .= '<div class="' . esc_attr( $cls ) . ' nkc-top-page">' . ob_get_clean() . '</div>';
		}
		$sb       = neuros_get_sidebar_args();
		$ccls     = 'content-wrapper';
		$ccls    .= 'on' === neuros_get_prefered_option( 'content_top_margin' ) ? ' content-wrapper-remove-top-margin' : '';
		$ccls    .= 'on' === neuros_get_prefered_option( 'content_bottom_margin' ) ? ' content-wrapper-remove-bottom-margin' : '';
		$ccls    .= ' content-wrapper-sidebar-position-' . $sb['sidebar_position'];
		$sidebar  = '';
		if ( 'none' !== $sb['sidebar_position'] && is_readable( $t['dir'] . '/sidebar.php' ) ) {
			ob_start();
			load_template( $t['dir'] . '/sidebar.php', false );
			$sidebar = ob_get_clean();
		}
		$done = true;
		return $out . '<div class="' . esc_attr( $ccls ) . '"><div class="content"><div class="content-inner">' . $content . '</div></div>' . $sidebar . '</div>';
	} catch ( \Throwable $e ) {
		while ( ob_get_level() > $level ) {
			ob_end_clean();
		}
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'nk-neuros-compat page frame: ' . $e->getMessage() ); // phpcs:ignore
		}
		return $content;
	}
}, 99 );

/* ---------------------------------------------------------------- 5. original image sizes (same names and crops) */
add_action( 'after_setup_theme', function () {
	if ( ! nkc_runtime_enabled() ) {
		return;
	}
	$sizes = array(
		'neuros_post_thumbnail_mobile' => array( 575, 274 ), 'neuros_post_thumbnail_tablet' => array( 991, 472 ),
		'neuros_post_grid_2_columns' => array( 960, 718 ), 'neuros_post_grid_3_columns' => array( 640, 478 ),
		'neuros_post_grid_4_columns' => array( 500, 374 ), 'neuros_post_grid_5_columns' => array( 384, 287 ), 'neuros_post_grid_6_columns' => array( 320, 239 ),
		'neuros_portfolio_thumbnail' => array( 835, 653 ), 'neuros_team_thumbnail' => array( 535, 551, array( 'right', 'center' ) ),
	);
	foreach ( $sizes as $n => $s ) {
		add_image_size( $n, $s[0], $s[1], isset( $s[2] ) ? $s[2] : array( 'center', 'center' ) );
	}
}, 20 );

/* ---------------------------------------------------------------- 6. WPForms dropdown wrapper (themes/neuros/functions.php:609)
   The original theme wraps every WPForms <select> in .select-wrap; its CSS styles the arrow/box through that wrapper. */
add_action( 'wpforms_display_field_select', function ( $field ) {
	if ( ! nkc_runtime_enabled() ) {
		return;
	}
	echo '<div class="select-wrap' . ( isset( $field['multiple'] ) && ! empty( $field['multiple'] ) ? ' multiple' : '' ) . ( ! empty( $field['size'] ) && isset( $field['size'] ) ? ' wpforms-field-' . esc_attr( $field['size'] ) : '' ) . '">'; // phpcs:ignore
}, 5, 1 );
add_action( 'wpforms_display_field_select', function () {
	if ( nkc_runtime_enabled() ) {
		echo '</div>';
	}
}, 15 );
