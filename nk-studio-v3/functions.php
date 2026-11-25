<?php
/**
 * NK Studio theme bootstrap.
 *
 * v3.12.0 — production-content safe: nothing in this theme creates, rewrites or deletes
 * pages, posts, menus or Elementor data on its own. See README.md.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NK_VERSION', '3.12.0' );
define( 'NK_DIR', get_template_directory() );
define( 'NK_URI', get_template_directory_uri() );

require NK_DIR . '/inc/setup.php';
require NK_DIR . '/inc/customizer.php';
require NK_DIR . '/inc/post-types.php';
require NK_DIR . '/inc/template-functions.php';
require NK_DIR . '/inc/safe-update.php';
/*
 * Package export/import (inc/migrate.php) can overwrite pages by slug, so it is OFF by default.
 * Enable only for a deliberate site-to-site move:  define( 'NK_ENABLE_PACKAGE_TOOLS', true );  in wp-config.php
 */
if ( defined( 'NK_ENABLE_PACKAGE_TOOLS' ) && NK_ENABLE_PACKAGE_TOOLS ) {
	require NK_DIR . '/inc/migrate.php';
}
require NK_DIR . '/inc/importer.php';
require NK_DIR . '/inc/elementor/home-builder.php';
require NK_DIR . '/inc/elementor/loader.php';
require NK_DIR . '/inc/legacy.php';
require NK_DIR . '/inc/blogs.php';
require NK_DIR . '/inc/admin-tools.php';

/*
 * v3.11.0: the Neuros compatibility layer (formerly the plugin "nk-neuros-compat") is BUNDLED in the theme, so this one ZIP is
 * enough. If the old plugin is still active it loads first and wins; the notice below asks to deactivate it.
 */
if ( ! defined( 'NK_NEUROS_COMPAT_VERSION' ) ) {
	require NK_DIR . '/compat/nk-neuros-compat.php';
} elseif ( ! defined( 'NK_NEUROS_COMPAT_BUNDLED' ) ) {
	add_action( 'admin_notices', function () {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		echo '<div class="notice notice-warning"><p><strong>NK Studio:</strong> the plugin "NK Neuros Compatibility" (v' . esc_html( NK_NEUROS_COMPAT_VERSION ) . ') is still active. The theme now contains the newer version of it: <a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">deactivate (and delete) the plugin</a> to use the bundled one.</p></div>';
	} );
}
