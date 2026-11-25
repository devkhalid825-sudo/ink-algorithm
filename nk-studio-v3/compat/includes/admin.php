<?php
/**
 * Tools > NK Neuros Compat — diagnostics + the few opt-in switches. Read-only except the Settings / Orphans forms,
 * which only store plugin options (nkc_settings, nkc_orphans).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () {
	add_management_page( 'NK Neuros Compat', 'NK Neuros Compat', 'manage_options', 'nk-neuros-compat', 'nkc_render_page' );
} );

function nkc_expected_widgets() {
	return array( 'neuros_heading', 'neuros_button', 'neuros_icon_box', 'neuros_special_text', 'neuros_image_carousel', 'neuros_content_slider', 'neuros_team_members', 'neuros_blog_listing', 'neuros_wpforms', 'neuros_video_button', 'neuros_gallery', 'neuros_testimonial_carousel', 'lakit-tabs' );
}

function nkc_h( $s ) {
	return esc_html( (string) $s );
}

function nkc_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$msg    = '';
	$action = isset( $_POST['nkc_action'] ) ? sanitize_key( wp_unslash( $_POST['nkc_action'] ) ) : '';
	if ( $action && check_admin_referer( 'nkc_page' ) ) {
		if ( 'settings' === $action ) {
			update_option( 'nkc_settings', array(
				'bridge_paths'   => empty( $_POST['bridge_paths'] ) ? 0 : 1,
				'legacy_css'     => isset( $_POST['legacy_css'] ) && in_array( $_POST['legacy_css'], array( 'layered', 'plain', 'off' ), true ) ? $_POST['legacy_css'] : 'layered',
				'theme_supports' => sanitize_text_field( wp_unslash( $_POST['theme_supports'] ?? '' ) ),
				'legacy_slugs'   => sanitize_text_field( wp_unslash( $_POST['legacy_slugs'] ?? 'neuros' ) ),
			) );
			$msg = 'Settings saved.';
		}
		if ( 'orphans' === $action ) {
			$cfg = array( 'types' => array(), 'taxes' => array() );
			foreach ( (array) ( $_POST['types'] ?? array() ) as $name => $on ) {
				$name = sanitize_key( $name );
				$cfg['types'][ $name ] = array(
					'public' => ! empty( $_POST['pub_t'][ $name ] ) ? 1 : 0,
					'slug'   => sanitize_title( wp_unslash( $_POST['slug_t'][ $name ] ?? '' ) ),
					'taxes'  => array_filter( array_map( 'sanitize_key', array_keys( (array) ( $_POST['taxes'] ?? array() ) ) ) ),
				);
			}
			foreach ( (array) ( $_POST['taxes'] ?? array() ) as $name => $on ) {
				$name = sanitize_key( $name );
				$cfg['taxes'][ $name ] = array(
					'public' => ! empty( $_POST['pub_x'][ $name ] ) ? 1 : 0,
					'slug'   => sanitize_title( wp_unslash( $_POST['slug_x'][ $name ] ?? '' ) ),
					'types'  => array_keys( $cfg['types'] ),
				);
			}
			update_option( 'nkc_orphans', $cfg );
			$msg = 'Saved. Selected types/taxonomies are registered from the next page load. Go to Settings > Permalinks > Save once if you enabled public URLs.';
		}
	}
	$s = nkc_settings();

	echo '<div class="wrap"><h1>NK Neuros Compatibility <small style="opacity:.6">v' . nkc_h( NK_NEUROS_COMPAT_VERSION ) . '</small></h1>';
	if ( $msg ) {
		echo '<div class="notice notice-success"><p>' . nkc_h( $msg ) . '</p></div>';
	}
	echo '<p style="max-width:900px">This plugin only <em>reads</em> your content. It provides the theme helpers the original Neuros widgets need and reports anything else that is missing under the NK Studio theme.</p>';

	/* ---------- status */
	$legacy = nkc_legacy_theme();
	echo '<h2>1. Status</h2><table class="widefat striped" style="max-width:900px"><tbody>';
	$rows = array(
		'Active theme'                       => get_stylesheet() . ( nkc_original_theme_active() ? ' (ORIGINAL theme: helpers are provided by it, not by this plugin)' : '' ),
		'neuros_output_code()'               => function_exists( 'neuros_output_code' ) ? 'defined' : 'MISSING',
		'neuros_get_all_post_list()'         => function_exists( 'neuros_get_all_post_list' ) ? 'defined' : 'MISSING',
		'Captured original CSS (captured/)'  => nkc_capture_status(),
		'neuros_get_all_taxonomy_terms()'    => function_exists( 'neuros_get_all_taxonomy_terms' ) ? 'defined' : 'MISSING',
		'Original theme folder'              => $legacy ? 'found (parent + child where installed) — path bridge active for: ' . implode( ', ', nkc_bridged_plugin_dirs() ) : ( empty( $s['bridge_paths'] ) ? 'bridge disabled in settings' : 'not found (themes/' . $s['legacy_slugs'] . ') — bridge inactive' ),
		'Elementor'                          => did_action( 'elementor/loaded' ) ? 'active' : 'NOT ACTIVE',
		'neuros-plugin'                      => is_dir( WP_PLUGIN_DIR . '/neuros-plugin' ) ? ( is_plugin_active( 'neuros-plugin/neuros-plugin.php' ) ? 'active' : 'installed (check it is active)' ) : 'NOT INSTALLED',
		'lastudio-element-kit'               => is_dir( WP_PLUGIN_DIR . '/lastudio-element-kit' ) ? 'installed' : 'NOT INSTALLED',
	);
	foreach ( $rows as $k => $v ) {
		echo '<tr><th style="width:280px">' . nkc_h( $k ) . '</th><td>' . nkc_h( $v ) . '</td></tr>';
	}
	echo '</tbody></table>';

	/* ---------- widget inventory */
	echo '<h2>2. Widget inventory (from your saved Elementor data)</h2>';
	$usage = nkc_widget_usage();
	$wm    = did_action( 'elementor/loaded' ) ? \Elementor\Plugin::$instance->widgets_manager : null;
	$all   = array_unique( array_merge( nkc_expected_widgets(), array_keys( $usage ) ) );
	sort( $all );
	echo '<table class="widefat striped" style="max-width:900px"><thead><tr><th>Widget type</th><th>Registered</th><th>Pages/templates using it</th><th>Instances</th></tr></thead><tbody>';
	foreach ( $all as $w ) {
		if ( 0 !== strpos( $w, 'neuros_' ) && 0 !== strpos( $w, 'lakit' ) ) {
			continue;
		}
		$reg   = $wm && $wm->get_widget_types( $w ) ? 'yes' : '<strong style="color:#b32d2e">NO</strong>';
		$pages = array();
		foreach ( array_keys( $usage[ $w ] ?? array() ) as $pid ) {
			$pages[] = '<a href="' . esc_url( get_edit_post_link( $pid ) ) . '">' . nkc_h( get_the_title( $pid ) ?: '#' . $pid ) . '</a>';
		}
		echo '<tr><td><code>' . nkc_h( $w ) . '</code></td><td>' . $reg . '</td><td>' . ( $pages ? implode( ', ', $pages ) : '<em>not used in saved data</em>' ) . '</td><td>' . (int) array_sum( $usage[ $w ] ?? array() ) . '</td></tr>'; // phpcs:ignore
	}
	echo '</tbody></table>';

	/* ---------- dependency scan */
	echo '<h2>3. Dependency scan (static, nothing is executed)</h2>';
	echo '<form method="post">';
	wp_nonce_field( 'nkc_page' );
	echo '<input type="hidden" name="nkc_action" value="scan"><p><button class="button button-primary">Scan neuros-plugin &amp; lastudio-element-kit</button></p></form>';
	if ( 'scan' === $action ) {
		$r = nkc_scan();
		echo '<p>Scanned: ' . nkc_h( implode( ', ', $r['plugins'] ) ?: 'nothing (plugins not found)' ) . ' — ' . (int) $r['files'] . ' function calls analysed.</p>';
		$list = function ( $title, $map, $none ) {
			echo '<h3>' . nkc_h( $title ) . '</h3>';
			if ( ! $map ) {
				echo '<p style="color:#007017">' . nkc_h( $none ) . '</p>';
				return;
			}
			echo '<table class="widefat striped" style="max-width:1000px"><tbody>';
			foreach ( $map as $name => $where ) {
				$where = array_values( array_unique( (array) $where ) );
				echo '<tr><td style="width:340px"><code>' . nkc_h( $name ) . '</code></td><td>' . nkc_h( implode( ', ', array_slice( $where, 0, 6 ) ) . ( count( $where ) > 6 ? ' … +' . ( count( $where ) - 6 ) : '' ) ) . '</td></tr>';
			}
			echo '</tbody></table>';
		};
		$list( 'A. Unresolved functions (these cause "Call to undefined function")', $r['unresolved'], 'None — every called function is defined.' );
		$list( 'B. Theme path usage (served by the path bridge when the original theme folder is installed)', $r['theme_paths'], 'None.' );
		$checks = array();
		foreach ( $r['theme_checks'] as $c ) {
			$checks[ $c[0] ][] = $c[1];
		}
		$list( 'C. Theme feature / setting checks', $checks, 'None.' );
		$list( 'D. Hooks the plugin listens to that nothing fires (the original theme fired them)', $r['orphan_hooks'], 'None.' );
		$deps = array();
		foreach ( $r['deps'] as $d ) {
			$deps[ $d[0] ][] = $d[1];
		}
		$list( 'E. Script/style handles that look theme-provided', $deps, 'None.' );
		echo '<p class="description">Anything in A is a real fatal waiting to happen. Read that function in <code>wp-content/themes/neuros</code>, then add it to <code>includes/helpers.php</code> with its original behaviour.</p>';
	}

	/* ---------- orphans */
	echo '<h2>4. Content types in the database that are not registered</h2>';
	$o = nkc_find_orphans();
	$c = nkc_orphan_config();
	if ( ! $o['types'] && ! $o['taxes'] && ! $c['types'] && ! $c['taxes'] ) {
		echo '<p style="color:#007017">None — every post type and taxonomy that has rows is registered.</p>';
	} else {
		echo '<form method="post">';
		wp_nonce_field( 'nkc_page' );
		echo '<input type="hidden" name="nkc_action" value="orphans"><table class="widefat striped" style="max-width:900px"><thead><tr><th>Register</th><th>Name</th><th>Kind</th><th>Rows</th><th>Public URLs</th><th>URL slug</th></tr></thead><tbody>';
		$names_t = array_unique( array_merge( array_keys( $o['types'] ), array_keys( $c['types'] ) ) );
		foreach ( $names_t as $n ) {
			$on = isset( $c['types'][ $n ] );
			echo '<tr><td><input type="checkbox" name="types[' . esc_attr( $n ) . ']" value="1"' . checked( $on, true, false ) . '></td><td><code>' . nkc_h( $n ) . '</code></td><td>post type</td><td>' . (int) ( $o['types'][ $n ] ?? 0 ) . '</td><td><input type="checkbox" name="pub_t[' . esc_attr( $n ) . ']" value="1"' . checked( ! empty( $c['types'][ $n ]['public'] ), true, false ) . '></td><td><input type="text" name="slug_t[' . esc_attr( $n ) . ']" value="' . esc_attr( $c['types'][ $n ]['slug'] ?? '' ) . '" placeholder="e.g. team"></td></tr>';
		}
		$names_x = array_unique( array_merge( array_keys( $o['taxes'] ), array_keys( $c['taxes'] ) ) );
		foreach ( $names_x as $n ) {
			$on = isset( $c['taxes'][ $n ] );
			echo '<tr><td><input type="checkbox" name="taxes[' . esc_attr( $n ) . ']" value="1"' . checked( $on, true, false ) . '></td><td><code>' . nkc_h( $n ) . '</code></td><td>taxonomy</td><td>' . (int) ( $o['taxes'][ $n ] ?? 0 ) . '</td><td><input type="checkbox" name="pub_x[' . esc_attr( $n ) . ']" value="1"' . checked( ! empty( $c['taxes'][ $n ]['public'] ), true, false ) . '></td><td><input type="text" name="slug_x[' . esc_attr( $n ) . ']" value="' . esc_attr( $c['taxes'][ $n ]['slug'] ?? '' ) . '"></td></tr>';
		}
		echo '</tbody></table><p><button class="button">Save selection</button></p><p class="description">Use the same URL slug the live site uses (see the URL audit) so team/portfolio permalinks keep working, then re-save Settings &gt; Permalinks. Rows are never created or changed.</p></form>';
	}

	/* ---------- http self-check */
	echo '<h2>5. HTTP self-check</h2><form method="post">';
	wp_nonce_field( 'nkc_page' );
	echo '<input type="hidden" name="nkc_action" value="http"><p><textarea name="paths" rows="6" cols="60" placeholder="/about/&#10;/our-services/&#10;/blogs/">' . esc_textarea( isset( $_POST['paths'] ) ? wp_unslash( $_POST['paths'] ) : "/\n/about/\n/our-services/\n/portfolio/\n/blogs/\n/contacts/\n/review/\n/TowelConfigurator/" ) . '</textarea></p><p><button class="button">Fetch these paths as me</button></p></form>'; // phpcs:ignore
	if ( 'http' === $action ) {
		$cookies = array();
		foreach ( $_COOKIE as $k => $v ) {
			$cookies[] = new WP_Http_Cookie( array( 'name' => $k, 'value' => $v ) );
		}
		echo '<table class="widefat striped" style="max-width:1000px"><thead><tr><th>Path</th><th>HTTP</th><th>Bytes</th><th>Fatal markers</th></tr></thead><tbody>';
		foreach ( array_slice( array_filter( array_map( 'trim', explode( "\n", wp_unslash( (string) $_POST['paths'] ) ) ) ), 0, 40 ) as $p ) { // phpcs:ignore
			$res = wp_remote_get( home_url( '/' . ltrim( $p, '/' ) ), array( 'timeout' => 25, 'redirection' => 3, 'cookies' => $cookies, 'sslverify' => false ) );
			if ( is_wp_error( $res ) ) {
				echo '<tr><td>' . nkc_h( $p ) . '</td><td colspan="3">' . nkc_h( $res->get_error_message() ) . '</td></tr>';
				continue;
			}
			$body  = wp_remote_retrieve_body( $res );
			$marks = array();
			foreach ( array( 'Fatal error', 'There has been a critical error', 'Call to undefined', 'Uncaught' ) as $m ) {
				if ( false !== stripos( $body, $m ) ) {
					$marks[] = $m;
				}
			}
			$code = (int) wp_remote_retrieve_response_code( $res );
			echo '<tr><td>' . nkc_h( $p ) . '</td><td>' . ( $code >= 400 ? '<strong style="color:#b32d2e">' . $code . '</strong>' : $code ) . '</td><td>' . strlen( $body ) . '</td><td>' . ( $marks ? '<strong style="color:#b32d2e">' . nkc_h( implode( ', ', $marks ) ) . '</strong>' : '—' ) . '</td></tr>'; // phpcs:ignore
		}
		echo '</tbody></table><p class="description">Add <code>?nkc_probe=1</code> to a page URL (logged in) and view source: the last lines list scripts/styles that were silently skipped because a dependency is missing.</p>';
	}

	/* ---------- settings */
	echo '<h2>6. Settings</h2><form method="post">';
	wp_nonce_field( 'nkc_page' );
	echo '<input type="hidden" name="nkc_action" value="settings"><table class="form-table"><tbody>';
	echo '<tr><th>Path bridge</th><td><label><input type="checkbox" name="bridge_paths" value="1"' . checked( ! empty( $s['bridge_paths'] ), true, false ) . '> Give the original plugins the original theme\'s directory/URL (needs the <code>neuros</code> theme folder to stay installed, inactive).</label></td></tr>';
	echo '<tr><th>Original theme folder(s)</th><td><input type="text" name="legacy_slugs" value="' . esc_attr( $s['legacy_slugs'] ) . '"></td></tr>';
	echo '<tr><th>Original theme CSS/JS</th><td><select name="legacy_css">';
	foreach ( array( 'layered' => 'layered (recommended: NK header/footer win conflicts)', 'plain' => 'plain (original CSS, normal cascade)', 'off' => 'off (JS only)' ) as $k => $l ) {
		echo '<option value="' . esc_attr( $k ) . '"' . selected( $s['legacy_css'], $k, false ) . '>' . esc_html( $l ) . '</option>';
	}
	echo '</select> <span class="description">Loaded only on pages that contain original Neuros / LaStudio widgets. Compare quickly with <code>?nkc_css=plain</code> / <code>?nkc_css=off</code> (admins).</span></td></tr>';
	echo '<tr><th>Extra theme-support flags</th><td><input type="text" name="theme_supports" value="' . esc_attr( $s['theme_supports'] ) . '" placeholder="only flags listed under scan section C"></td></tr>';
	echo '</tbody></table><p><button class="button button-primary">Save settings</button></p></form></div>';
}
