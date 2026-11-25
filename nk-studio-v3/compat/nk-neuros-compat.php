<?php
/**
 * NK Neuros Compatibility — BUNDLED in the NK Studio theme (v3.11.0). Same code as the plugin nk-neuros-compat 1.4.0, so the
 * theme alone is enough (one upload). It keeps the ORIGINAL Neuros widgets (neuros-plugin, lastudio-element-kit) working under
 * the NK theme: helper functions, original page frame / fonts / CSS on original pages, sidebars, AJAX listings, WPForms select
 * wrapper, diagnostics (Tools > NK Neuros Compat). It never writes to pages, posts, menus or Elementor data.
 * Needs the original theme folder "neuros" to stay installed (inactive).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NK_NEUROS_COMPAT_VERSION', '1.4.0' );
define( 'NK_NEUROS_COMPAT_BUNDLED', true );
define( 'NK_NEUROS_COMPAT_DIR', trailingslashit( wp_normalize_path( __DIR__ ) ) );
define( 'NK_NEUROS_COMPAT_URL', trailingslashit( get_template_directory_uri() ) . 'compat/' );
/* captured original CSS lives in uploads/ so it survives a theme update (tools/capture-legacy.py writes there) */
$nkc_up = wp_upload_dir( null, false );
define( 'NK_NEUROS_COMPAT_CAP_DIR', trailingslashit( wp_normalize_path( $nkc_up['basedir'] ) ) . 'nk-neuros-compat/captured/' );
define( 'NK_NEUROS_COMPAT_CAP_URL', trailingslashit( $nkc_up['baseurl'] ) . 'nk-neuros-compat/captured/' );
unset( $nkc_up );

/** Settings with defaults. */
function nkc_settings() {
	$s = get_option( 'nkc_settings', array() );
	return wp_parse_args( is_array( $s ) ? $s : array(), array(
		'bridge_paths'   => 1,          // give the original plugins the original theme's paths/URLs
		'legacy_css'     => 'layered',  // original theme CSS on original pages: layered (NK header/footer win conflicts) | plain | off
		'theme_supports' => '',         // comma separated add_theme_support() flags the original plugins ask for
		'legacy_slugs'   => 'neuros', // the theme the live site runs (verified: template = stylesheet = neuros)
	) );
}

/** True while the ORIGINAL theme is the active theme (it defines its own helpers; we must not redeclare them). */
function nkc_original_theme_active() {
	$s     = nkc_settings();
	$slugs = array_filter( array_map( 'trim', explode( ',', (string) $s['legacy_slugs'] ) ) );
	return in_array( get_template(), $slugs, true ) || in_array( get_stylesheet(), $slugs, true );
}

require_once NK_NEUROS_COMPAT_DIR . 'includes/helpers.php';
require_once NK_NEUROS_COMPAT_DIR . 'includes/path-bridge.php';
require_once NK_NEUROS_COMPAT_DIR . 'includes/legacy-assets.php';
require_once NK_NEUROS_COMPAT_DIR . 'includes/legacy-runtime.php';
require_once NK_NEUROS_COMPAT_DIR . 'includes/legacy-sidebars.php';
require_once NK_NEUROS_COMPAT_DIR . 'includes/legacy-ajax.php';
require_once NK_NEUROS_COMPAT_DIR . 'includes/orphans.php';
require_once NK_NEUROS_COMPAT_DIR . 'includes/probe.php';

if ( is_admin() ) {
	require_once NK_NEUROS_COMPAT_DIR . 'includes/scanner.php';
	require_once NK_NEUROS_COMPAT_DIR . 'includes/admin.php';
}

/* Optional theme-support flags the original plugins check with current_theme_supports(). Empty by default. */
add_action( 'after_setup_theme', function () {
	$s = nkc_settings();
	foreach ( array_filter( array_map( 'sanitize_key', explode( ',', (string) $s['theme_supports'] ) ) ) as $flag ) {
		add_theme_support( $flag );
	}
}, 20 );
