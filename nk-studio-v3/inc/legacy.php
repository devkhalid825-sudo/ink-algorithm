<?php
/**
 * v3.8.0 — Legacy (original Neuros) inner pages under the NK Studio header/footer.
 *
 * What this file does (all read-only, nothing is written to the database):
 *  1. Detects original inner pages (Elementor content without NK widgets: Neuros/LaStudio widgets or core widgets) and
 *     adds the body class `nk-legacy-page`, so a small stylesheet (assets/css/legacy.css) can clear the fixed NK header
 *     and keep the old layout intact. The NK front page (NK widgets) is never affected.
 *  2. Resolves header/footer links to the REAL page URLs of the original site (e.g. /contacts/ not /contact/).
 *  3. Lets Elementor Theme Builder (if present) keep controlling single / archive / 404 locations.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * True when the current singular document is an ORIGINAL inner page: it has Elementor content and does not use any NK
 * widget. That covers pages built with the Neuros widgets (neuros_*, lakit-*) AND pages built only with core Elementor
 * widgets (legal pages, FAQ, contact …) — all of them start at y = 0 and need the NK header cleared.
 * The NK front page (and any page that uses an nk_* widget) is never "legacy".
 */
function nk_is_legacy_page( $post_id = 0 ) {
	static $cache = array();
	$post_id = $post_id ? (int) $post_id : (int) get_queried_object_id();
	if ( ! $post_id ) {
		return false;
	}
	if ( isset( $cache[ $post_id ] ) ) {
		return $cache[ $post_id ];
	}
	$hit  = false;
	$data = (string) get_post_meta( $post_id, '_elementor_data', true );
	if ( '' !== $data && 'builder' === get_post_meta( $post_id, '_elementor_edit_mode', true ) ) {
		$hit = false === strpos( $data, '"widgetType":"nk_' ) && (int) get_option( 'page_on_front' ) !== $post_id; // v3.8.1: no longer skips pages that merely embed a template (old pages with Elementor templates need the header offset too)
	}
	return $cache[ $post_id ] = (bool) apply_filters( 'nk_is_legacy_page', $hit, $post_id );
}

/** Original inner pages get `nk-legacy-page` (the NK-designed front page never does). */
add_filter( 'body_class', function ( $classes ) {
	if ( is_front_page() && nk_is_elementor_page( get_queried_object_id() ) ) {
		return $classes;
	}
	if ( is_singular() && nk_is_legacy_page() ) {
		$classes[] = 'nk-legacy-page';
	}
	return $classes;
} );

add_action( 'wp_enqueue_scripts', function () {
	if ( is_singular() && nk_is_legacy_page() || ( isset( $_GET['elementor-preview'] ) && nk_is_legacy_page( absint( $_GET['elementor-preview'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		wp_enqueue_style( 'nk-legacy', NK_URI . '/assets/css/legacy.css', array( 'nk-ink' ), nk_asset_ver( '/assets/css/legacy.css' ) );
		wp_enqueue_script( 'nk-legacy-dark', NK_URI . '/assets/js/nk-legacy-dark.js', array(), nk_asset_ver( '/assets/js/nk-legacy-dark.js' ), true );
	}
}, 30 );

/* ---------------------------------------------------------------- URL resolving */

/**
 * Map an NK default path to the first page that really exists on this site.
 * nk_resolve_path( '/contact/' ) -> '/contacts/' on the original site (page slug "contacts").
 */
function nk_resolve_path( $path ) {
	$path = '/' . trim( (string) $path, '/' ) . '/';
	if ( '//' === $path ) {
		$path = '/';
	}
	$alts = apply_filters( 'nk_path_alternatives', array(
		'/contact/'      => array( 'contact', 'contacts', 'contact-us' ),
		'/work/'         => array( 'portfolio', 'work', 'our-work' ),
		'/services/'     => array( 'our-services', 'services' ),
		'/testimonials/' => array( 'review', 'reviews', 'testimonials' ),
		'/faqs/'         => array( 'faq', 'faqs' ),
		'/blog/'         => array( 'blogs', 'blog' ),
	) );
	if ( ! isset( $alts[ $path ] ) ) {
		return $path;
	}
	foreach ( $alts[ $path ] as $slug ) {
		if ( get_page_by_path( $slug, OBJECT, 'page' ) ) {
			return '/' . $slug . '/';
		}
	}
	return $path;
}

/** Full URL for an NK default path, resolved to the real page when one exists. */
function nk_resolve_url( $path ) {
	if ( preg_match( '#^(https?:)?//#i', (string) $path ) ) {
		return $path;
	}
	return home_url( nk_resolve_path( $path ) );
}

/* ---------------------------------------------------------------- Theme Builder locations */

/** Wrap a template body: true when Elementor Pro's Theme Builder rendered the location. */
function nk_theme_builder_location( $location ) {
	return function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( $location );
}

/* ---------------------------------------------------------------- admin notice: compat plugin */
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'activate_plugins' ) || ! did_action( 'elementor/loaded' ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! in_array( $screen->base, array( 'dashboard', 'themes', 'plugins' ), true ) ) {
		return;
	}
	$neuros_active = defined( 'NEUROS_PLUGIN_VERSION' ) || class_exists( 'Neuros_Plugin', false ) || in_array( 'neuros-plugin/neuros-plugin.php', (array) get_option( 'active_plugins', array() ), true );
	if ( $neuros_active && ! defined( 'NK_NEUROS_COMPAT_VERSION' ) ) {
		echo '<div class="notice notice-warning"><p><strong>NK Studio:</strong> ' . esc_html__( 'The original Neuros widgets are active but the "NK Neuros Compatibility" plugin is not. Inner pages that use Neuros widgets (About, Blogs, Reviews …) can show a fatal error. Install and activate nk-neuros-compat (1.1.0 or newer).', 'nk-studio' ) . '</p></div>';
	}
} );
