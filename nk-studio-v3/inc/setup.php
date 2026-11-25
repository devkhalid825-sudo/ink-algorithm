<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'nk-studio', NK_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	// Elementor integration flags.
	add_theme_support( 'elementor' );

	register_nav_menus( array(
		'primary'  => __( 'Primary (header overlay menu)', 'nk-studio' ),
		'footer_1' => __( 'Footer column 1', 'nk-studio' ),
		'footer_2' => __( 'Footer column 2', 'nk-studio' ),
		'footer_3' => __( 'Footer column 3', 'nk-studio' ),
		'footer_4' => __( 'Footer column 4', 'nk-studio' ),
		'footer_5' => __( 'Footer column 5', 'nk-studio' ),
	) );
} );

/** Asset version = theme version + file time, so an updated file is never served from an old browser / plugin cache. */
function nk_asset_ver( $rel ) {
	$f = NK_DIR . '/' . ltrim( $rel, '/' );
	return NK_VERSION . ( file_exists( $f ) ? '.' . filemtime( $f ) : '' );
}

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'nk-fonts',
		'https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400;1,9..40,500;1,9..40,600&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'nk-main', NK_URI . '/assets/css/main.css', array( 'nk-fonts' ), nk_asset_ver( '/assets/css/main.css' ) );
	wp_enqueue_style( 'nk-motion', NK_URI . '/assets/css/motion.css', array( 'nk-main' ), nk_asset_ver( '/assets/css/motion.css' ) );
	wp_enqueue_script( 'nk-main', NK_URI . '/assets/js/main.js', array(), nk_asset_ver( '/assets/js/main.js' ), true );
	// Lenis smooth scroll (pinned version, same library the reference site uses).
	wp_enqueue_script( 'nk-lenis', 'https://cdn.jsdelivr.net/npm/lenis@1.1.18/dist/lenis.min.js', array(), '1.1.18', true );
	wp_enqueue_script( 'nk-motion', NK_URI . '/assets/js/nk-motion.js', array( 'nk-main', 'nk-lenis' ), nk_asset_ver( '/assets/js/nk-motion.js' ), true );
	wp_enqueue_script( 'nk-ink-video', NK_URI . '/assets/js/ink-video.js', array(), nk_asset_ver( '/assets/js/ink-video.js' ), true );
	wp_enqueue_script( 'nk-ink-overlap', NK_URI . '/assets/js/ink-overlap.js', array(), nk_asset_ver( '/assets/js/ink-overlap.js' ), true );
	wp_enqueue_script( 'nk-ink-text', NK_URI . '/assets/js/ink-text.js', array( 'nk-motion' ), nk_asset_ver( '/assets/js/ink-text.js' ), true );

	wp_enqueue_style( 'nk-ink', NK_URI . '/assets/css/ink.css', array( 'nk-motion' ), nk_asset_ver( '/assets/css/ink.css' ) );
	// v3.3 premium Strategic Pillars section (loaded after ink.css so it wins over the older pillar styles)
	wp_enqueue_style( 'nk-pillars', NK_URI . '/assets/css/nk-pillars.css', array( 'nk-ink' ), nk_asset_ver( '/assets/css/nk-pillars.css' ) );
	wp_enqueue_script( 'nk-slider', NK_URI . '/assets/js/nk-slider.js', array(), nk_asset_ver( '/assets/js/nk-slider.js' ), true );
	wp_enqueue_script( 'nk-faq', NK_URI . '/assets/js/nk-faq.js', array(), nk_asset_ver( '/assets/js/nk-faq.js' ), true );
	wp_enqueue_script( 'nk-pillars', NK_URI . '/assets/js/nk-pillars.js', array(), nk_asset_ver( '/assets/js/nk-pillars.js' ), true );

	// Accent: orange. Old green values saved by the previous version are treated as "default".
	$accent = strtolower( (string) get_theme_mod( 'nk_accent', '#ff6a1a' ) );
	if ( in_array( $accent, array( '', '#00ffc2', '#1de9b6', '#20e7b7' ), true ) ) {
		$accent = '#ff6a1a';
	}
	$light = '#ff6a1a' === $accent ? '#ff8a3d' : $accent;
	wp_add_inline_style( 'nk-ink', ':root{--nk-accent:' . esc_attr( $accent ) . ';--nk-accent-light:' . esc_attr( $light ) . ';}' );
} );

/* Allow SVG uploads for admins (logos). */
add_filter( 'upload_mimes', function ( $m ) {
	if ( current_user_can( 'manage_options' ) ) {
		$m['svg'] = 'image/svg+xml';
	}
	return $m;
} );

/* Warn clearly when Elementor is missing (all page sections are Elementor widgets). */
add_action( 'admin_notices', function () {
	if ( did_action( 'elementor/loaded' ) || ! current_user_can( 'install_plugins' ) ) {
		return;
	}
	$url = admin_url( 'plugin-install.php?s=Elementor+Website+Builder&tab=search&type=term' );
	echo '<div class="notice notice-error"><p><strong>NK Studio:</strong> Elementor plugin is not active, so page sections will not show. <a href="' . esc_url( $url ) . '">Install / activate Elementor (free)</a>.</p></div>';
} );

/* ------------------------------------------------------------------
 * Ink Hero 3D: three.js + scene module, only on pages that use the widget
 * ------------------------------------------------------------------ */
define( 'NK_THREE_CDN', 'https://cdn.jsdelivr.net/npm/three@0.170.0/' );

function nk_page_has_ink_hero() {
	if ( isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return true;
	}
	if ( ! is_singular() ) {
		return false;
	}
	$data = nk_elementor_data_deep( get_queried_object_id() );
	// Ink Hero or an NK Statement ("Empower brands.", "Align what matters.") both use the 3D environment.
	return is_string( $data ) && ( false !== strpos( $data, 'nk_ink_hero' ) || false !== strpos( $data, 'nk_statement' ) );
}

add_action( 'wp_enqueue_scripts', function () {
	if ( ! nk_page_has_ink_hero() ) {
		return;
	}
	if ( function_exists( 'wp_enqueue_script_module' ) ) { // WordPress 6.5+
		wp_register_script_module( 'three', NK_THREE_CDN . 'build/three.module.js', array(), null );
		wp_register_script_module( 'three/addons/loaders/GLTFLoader.js', NK_THREE_CDN . 'examples/jsm/loaders/GLTFLoader.js', array( 'three' ), null );
		wp_register_script_module( 'three/addons/environments/RoomEnvironment.js', NK_THREE_CDN . 'examples/jsm/environments/RoomEnvironment.js', array( 'three' ), null );
		wp_enqueue_script_module(
			'nk-ink-hero-3d',
			NK_URI . '/assets/js/ink-hero-3d.js',
			array( 'three', 'three/addons/loaders/GLTFLoader.js', 'three/addons/environments/RoomEnvironment.js' ),
			nk_asset_ver( '/assets/js/ink-hero-3d.js' )
		);
	} else { // older WordPress: print the import map + module by hand
		add_action( 'wp_head', function () {
			echo '<script type="importmap">' . wp_json_encode( array( 'imports' => array( 'three' => NK_THREE_CDN . 'build/three.module.js', 'three/addons/' => NK_THREE_CDN . 'examples/jsm/' ) ), JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
		}, 1 );
		add_action( 'wp_footer', function () {
			echo '<script type="module" src="' . esc_url( NK_URI . '/assets/js/ink-hero-3d.js?ver=' . nk_asset_ver( '/assets/js/ink-hero-3d.js' ) ) . '"></script>' . "\n";
		}, 99 );
	}
}, 20 );

/* v3.9.0: mesh + floor background. Loaded on: hero pages, pages with an "Our Blogs" (nk_studio) section, original inner pages
   and the /blogs/ template. Switch: Customize > NK General > Hero mesh background. */
function nk_page_wants_mesh() {
	if ( ! get_theme_mod( 'nk_mesh_bg', true ) ) {
		return false;
	}
	if ( function_exists( 'nk_is_blogs_template' ) && nk_is_blogs_template() ) {
		return true;
	}
	if ( nk_page_has_ink_hero() || ( function_exists( 'nk_is_legacy_page' ) && is_singular( 'page' ) && nk_is_legacy_page() ) ) {
		return true;
	}
	if ( is_singular() ) {
		$data = nk_elementor_data_deep( get_queried_object_id() );
		return is_string( $data ) && false !== strpos( $data, '"widgetType":"nk_studio"' );
	}
	return false;
}
add_action( 'wp_enqueue_scripts', function () {
	if ( nk_page_wants_mesh() ) {
		wp_enqueue_script( 'nk-ink-mesh', NK_URI . '/assets/js/ink-mesh.js', array(), nk_asset_ver( '/assets/js/ink-mesh.js' ), true );
	}
}, 21 );

/* v3.8.0: the automatic one-time Elementor cache DELETE was removed. After installing, use Elementor > Tools > Regenerate CSS & Data. */

/* .glb files served from the theme need the right MIME on some hosts (harmless otherwise). */
add_filter( 'upload_mimes', function ( $m ) {
	if ( current_user_can( 'manage_options' ) ) {
		$m['glb'] = 'model/gltf-binary';
	}
	return $m;
} );
