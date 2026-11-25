<?php
/**
 * Path bridge: when a file of an ORIGINAL plugin (neuros-plugin, lastudio-element-kit) asks WordPress for the theme's
 * directory/URL, answer with the original theme's (wp-content/themes/neuros) — so assets, icon fonts and templates the
 * widgets expect from the theme resolve exactly as on the live site. Calls from any other file (NK theme, Elementor,
 * other plugins) are untouched, so the NK design cannot be affected.
 *
 * Does nothing when the original theme folder is not installed, when disabled in settings, or when the original theme
 * is the active theme.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Plugin folders whose theme-path calls are bridged. */
function nkc_bridged_plugin_dirs() {
	return apply_filters( 'nkc_bridged_plugins', array( 'neuros-plugin', 'lastudio-element-kit' ) );
}

/**
 * The original theme. Production runs "neuros" itself (verified: template = stylesheet = neuros), so both template_*
 * and stylesheet_* answers are its folder. If a child (Template: <parent>) is listed too, stylesheet_* answers use the child. Returns array( 'dir','uri','child_dir','child_uri' ) or null.
 */
function nkc_legacy_theme() {
	static $r = false;
	if ( false !== $r ) {
		return $r;
	}
	$r = null;
	$s = nkc_settings();
	if ( empty( $s['bridge_paths'] ) || nkc_original_theme_active() ) {
		return $r;
	}
	$found = array();
	foreach ( array_filter( array_map( 'trim', explode( ',', (string) $s['legacy_slugs'] ) ) ) as $slug ) {
		$dir = get_theme_root( $slug ) . '/' . $slug;
		if ( is_dir( $dir ) && file_exists( $dir . '/style.css' ) ) {
			$found[ $slug ] = array( 'dir' => $dir, 'uri' => get_theme_root_uri( $slug ) . '/' . $slug );
		}
	}
	if ( ! $found ) {
		return $r;
	}
	// parent = the theme that is not a child (no "Template:" header); child = one whose Template points to the parent
	$parent = null;
	$child  = null;
	foreach ( $found as $slug => $t ) {
		$hdr = get_file_data( $t['dir'] . '/style.css', array( 'Template' => 'Template' ) );
		if ( ! empty( $hdr['Template'] ) && isset( $found[ $hdr['Template'] ] ) ) {
			$child = $t;
		} elseif ( null === $parent ) {
			$parent = $t;
		}
	}
	$parent = $parent ? $parent : reset( $found );
	$r      = array( 'dir' => $parent['dir'], 'uri' => $parent['uri'], 'child_dir' => $child ? $child['dir'] : $parent['dir'], 'child_uri' => $child ? $child['uri'] : $parent['uri'] );
	return $r;
}

/** Is the code that asked for the theme path one of the original plugins? */
function nkc_caller_is_bridged() {
	static $memo = array();
	$frames = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 12 ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
	foreach ( $frames as $f ) {
		if ( empty( $f['file'] ) ) {
			continue;
		}
		$file = wp_normalize_path( $f['file'] );
		if ( isset( $memo[ $file ] ) ) {
			if ( null === $memo[ $file ] ) {
				continue; // WordPress internals / this plugin: look further up
			}
			return $memo[ $file ];
		}
		if ( false !== strpos( $file, '/wp-includes/' ) || false !== strpos( $file, '/wp-admin/' ) || 0 === strpos( $file, wp_normalize_path( NK_NEUROS_COMPAT_DIR ) ) ) {
			$memo[ $file ] = null;
			continue;
		}
		$hit = false;
		foreach ( nkc_bridged_plugin_dirs() as $p ) {
			if ( false !== strpos( $file, '/plugins/' . $p . '/' ) ) {
				$hit = true;
				break;
			}
		}
		// files of the original theme itself (its library/templates are loaded by this plugin) also see their own folder
		$lt = nkc_legacy_theme();
		if ( ! $hit && $lt && 0 === strpos( $file, wp_normalize_path( $lt['dir'] ) . '/' ) ) {
			$hit = true;
		}
		return $memo[ $file ] = $hit;
	}
	return false;
}

$nkc_boot_bridge = function () {
	if ( ! nkc_legacy_theme() ) {
		return;
	}
	foreach ( array( 'template_directory' => 'dir', 'stylesheet_directory' => 'child_dir' ) as $tag => $key ) {
		add_filter( $tag, function ( $dir ) use ( $key ) {
			$t = nkc_legacy_theme();
			return $t && nkc_caller_is_bridged() ? $t[ $key ] : $dir;
		}, 99 );
	}
	foreach ( array( 'template_directory_uri' => 'uri', 'stylesheet_directory_uri' => 'child_uri' ) as $tag => $key ) {
		add_filter( $tag, function ( $uri ) use ( $key ) {
			$t = nkc_legacy_theme();
			return $t && nkc_caller_is_bridged() ? $t[ $key ] : $uri;
		}, 99 );
	}
};
if ( did_action( 'plugins_loaded' ) ) {
	$nkc_boot_bridge();
} else {
	add_action( 'plugins_loaded', $nkc_boot_bridge, 1 );
}
