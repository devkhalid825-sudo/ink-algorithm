<?php
/**
 * Original-theme assets for pages built with the original widgets.
 *
 * The original Neuros theme registered/enqueued these on every page; under the NK theme they do not exist, so the
 * original widgets lose their sliders/filters (script dependency "owl-carousel" / "neuros-theme" missing, see
 * neuros-plugin.php: elementor_widgets) and their styling. This file loads them ONLY on pages whose Elementor data
 * contains an original widget (neuros_* / lakit-*), reading from the original theme folder. Nothing is written anywhere.
 *
 * Source of truth (Neuros 2.2.1, themes/neuros/functions.php neuros_css_js()):
 *   CSS  css/theme.css  (+ inline Customizer CSS from css/custom/custom.php  -> captured/inline.css)
 *   JS   jquery-cookie, owl-carousel, isotope, neuros-theme (theme.js) with ajax_params / neuros_ajaxurl / theme vars
 *   Fonts Google Fonts URL built from Customizer fonts                       -> captured/fonts-url.txt
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Does this post use an original widget? (reads saved Elementor data only) */
function nkc_is_neuros_page( $post_id = 0 ) {
	static $cache = array();
	$post_id = $post_id ? (int) $post_id : (int) get_queried_object_id();
	if ( ! $post_id ) {
		return false;
	}
	if ( isset( $cache[ $post_id ] ) ) {
		return $cache[ $post_id ];
	}
	$data = (string) get_post_meta( $post_id, '_elementor_data', true );
	$hit  = '' !== $data && ( false !== strpos( $data, '"widgetType":"neuros_' ) || false !== strpos( $data, '"widgetType":"lakit-' ) );
	// original inner pages built only with core Elementor widgets (privacy policy, terms ...) had the same original page
	// frame; the NK theme (3.8.1+) already knows which pages those are (Elementor content without NK widgets, not the front page)
	if ( ! $hit && '' !== $data && function_exists( 'nk_is_legacy_page' ) ) {
		$hit = (bool) nk_is_legacy_page( $post_id );
	}
	return $cache[ $post_id ] = (bool) apply_filters( 'nkc_is_neuros_page', $hit, $post_id );
}

function nkc_legacy_css_mode() {
	$s = nkc_settings();
	$m = $s['legacy_css'];
	if ( isset( $_GET['nkc_css'] ) && current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$m = sanitize_key( wp_unslash( $_GET['nkc_css'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}
	return in_array( $m, array( 'layered', 'plain', 'off' ), true ) ? $m : 'layered';
}

function nkc_captured( $file ) {
	$f = NK_NEUROS_COMPAT_CAP_DIR . $file;
	return is_readable( $f ) ? trim( (string) file_get_contents( $f ) ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions
}

function nkc_capture_status() {
	$have = array();
	foreach ( array( 'inline.css', 'body-bridge.css', 'fonts-url.txt' ) as $f ) {
		$have[] = $f . ( '' !== nkc_captured( $f ) ? ' ✔' : ' ✘' );
	}
	return implode( '   ', $have ) . '  (run tools/capture-legacy.py once if ✘)';
}

add_action( 'wp_enqueue_scripts', function () {
	if ( nkc_original_theme_active() ) {
		return; // the original theme loads its own assets
	}
	$t = nkc_legacy_theme();
	if ( ! $t ) {
		return;
	}
	$preview = isset( $_GET['elementor-preview'] ) ? absint( $_GET['elementor-preview'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
	$is      = ( is_singular() && nkc_is_neuros_page() ) || ( $preview && nkc_is_neuros_page( $preview ) );
	if ( ! apply_filters( 'nkc_load_legacy_assets', $is ) ) {
		return;
	}
	$uri = $t['uri'];
	$hdr = get_file_data( $t['dir'] . '/style.css', array( 'Version' => 'Version' ) );
	$ver = ! empty( $hdr['Version'] ) ? $hdr['Version'] : null;

	/* ---- scripts: same handles, files and dependencies as the original theme */
	if ( ! wp_script_is( 'jquery-cookie', 'registered' ) ) {
		wp_register_script( 'jquery-cookie', $uri . '/js/jquery.cookie.min.js', array( 'jquery' ), false, true );
	}
	if ( ! wp_script_is( 'owl-carousel', 'registered' ) ) {
		wp_register_script( 'owl-carousel', $uri . '/js/owl.carousel.min.js', array( 'jquery' ), false, true );
	}
	if ( ! wp_script_is( 'isotope', 'registered' ) ) { // neuros-plugin registers its own copy first, as on the live site
		wp_register_script( 'isotope', $uri . '/js/isotope.min.js', array(), false, true );
	}
	if ( ! wp_script_is( 'neuros-theme', 'registered' ) ) {
		wp_register_script( 'neuros-theme', $uri . '/js/theme.js', array( 'jquery', 'owl-carousel', 'isotope' ), false, true );
		wp_localize_script( 'neuros-theme', 'ajax_params', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		wp_localize_script( 'neuros-theme', 'neuros_ajaxurl', array( 'url' => esc_url( admin_url( 'admin-ajax.php' ) ) ) );
		wp_localize_script( 'neuros-theme', 'theme', array( 'rtl' => (bool) is_rtl() ) );
	}
	foreach ( array( 'jquery-cookie', 'owl-carousel', 'isotope', 'neuros-theme' ) as $h ) {
		wp_enqueue_script( $h );
	}

	/* ---- styles */
	$mode = nkc_legacy_css_mode();
	if ( 'off' === $mode ) {
		return;
	}
	// captured inline Customizer CSS is served as a real (cacheable) file, not repeated inline in every page.
	// captured/pages/<slug>.css (that page's own inline CSS: page-title colours/images, per-page options) wins over the generic captured/inline.css
	$slug        = is_singular() ? get_post_field( 'post_name', get_queried_object_id() ) : '';
	$inline_file = NK_NEUROS_COMPAT_CAP_DIR . 'inline.css';
	$inline_rel  = 'inline.css';
	if ( $slug && preg_match( '/^[a-z0-9-]+$/', $slug ) && is_readable( NK_NEUROS_COMPAT_CAP_DIR . 'pages/' . $slug . '.css' ) && filesize( NK_NEUROS_COMPAT_CAP_DIR . 'pages/' . $slug . '.css' ) > 0 ) {
		$inline_file = NK_NEUROS_COMPAT_CAP_DIR . 'pages/' . $slug . '.css';
		$inline_rel  = 'pages/' . $slug . '.css';
	}
	$inline_url = is_readable( $inline_file ) && filesize( $inline_file ) > 0 ? NK_NEUROS_COMPAT_CAP_URL . $inline_rel . '?ver=' . filemtime( $inline_file ) : '';
	// lowest layer: the original page-title banner is dark unless the page's own CSS says otherwise (never an empty banner)
	$base = '@layer neuros-base{.page-title-container{background-color:#181818;color:#fff}.page-title-container .page-title{color:#fff}}';
	if ( 'layered' === $mode ) {
		// Original CSS inside a cascade layer: any NK (unlayered) rule beats it, so the NK header/footer cannot be
		// restyled by the old theme's global selectors (body, a, h1.., header, footer ...).
		wp_register_style( 'nkc-neuros-theme', false, array(), null );
		wp_enqueue_style( 'nkc-neuros-theme' );
		$imp = '@layer neuros-base, neuros-legacy;' . "\n" . '@import url("' . esc_url_raw( $uri . '/css/theme.css' . ( $ver ? '?ver=' . rawurlencode( $ver ) : '' ) ) . '") layer(neuros-legacy);';
		if ( $inline_url ) {
			$imp .= "\n@import url(\"" . esc_url_raw( $inline_url ) . "\") layer(neuros-legacy);";
		}
		wp_add_inline_style( 'nkc-neuros-theme', $imp . "\n" . $base );
	} else {
		wp_enqueue_style( 'nkc-neuros-theme', $uri . '/css/theme.css', array(), $ver );
		if ( $inline_url ) {
			wp_enqueue_style( 'nkc-neuros-custom', $inline_url, array( 'nkc-neuros-theme' ), null );
		}
	}
	$fonts = nkc_captured( 'fonts-url.txt' );
	if ( '' === $fonts ) { // not captured yet: the two families the original site uses (Manrope, Sora) so the old pages never fall back to a system font
		$fonts = 'https://fonts.googleapis.com/css?family=' . rawurlencode( 'Manrope:400,500,600,700&subset=latin|Sora:400,500,600&subset=latin' ) . '&display=swap';
	}
	if ( '' !== $fonts && ( 0 === strpos( $fonts, '//fonts.googleapis.com/' ) || 0 === strpos( $fonts, 'https://fonts.googleapis.com/' ) ) ) {
		wp_enqueue_style( 'neuros-fonts', set_url_scheme( $fonts, 'https' ), array(), null );
	}
	$bridge = nkc_captured( 'body-bridge.css' ); // the old <body> typography, applied to the content area only
	if ( '' !== $bridge ) {
		wp_add_inline_style( 'nkc-neuros-theme', $bridge );
	}
}, 20 );
