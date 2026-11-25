<?php
/**
 * v3.4 Safe updates — your content survives every new theme upload.
 *
 * Where things live:
 *  - Theme zip (this folder)  = only code + design (PHP, CSS, JS, default pictures). Replacing it never touches content.
 *  - Database                  = everything you edit: Elementor pages / templates, Media uploads, Work, Reviews,
 *                                Social Videos, menus, and Customizer settings (header / footer / logo).
 *
 * What used to lose content, and the fix here:
 *  1. "Re-import demo pages" overwrote pages the importer once created (Home too) even after you edited them in
 *     Elementor  ->  pages you edited are never overwritten again (see importer + the hooks below).
 *  2. Uploading the zip as a NEW theme (other folder name, e.g. "nk-studio-v3-1") gave an empty Customizer
 *     (WordPress keeps Customizer settings per theme folder)  ->  settings are copied over automatically,
 *     and a warning shows when the folder name is not the usual one.
 *  3. No way back  ->  an automatic snapshot (Home + all Elementor pages/templates + Customizer) is saved every
 *     time a new theme version is uploaded, plus Export / Import of a backup file.
 *     All under Appearance > NK Studio Setup.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NK_FOLDER', 'nk-studio-v3' );

/* ---------------------------------------------------------------- 1. edited pages are yours */
function nk_release_demo_flag( $post_id ) {
	if ( get_post_meta( $post_id, '_nk_demo', true ) ) {
		delete_post_meta( $post_id, '_nk_demo' );
	}
}
add_action( 'save_post_page', function ( $post_id, $post, $update ) {
	if ( $update && ! wp_is_post_revision( $post_id ) && ! ( defined( 'NK_IMPORTING' ) && NK_IMPORTING ) ) {
		nk_release_demo_flag( $post_id );
	}
}, 10, 3 );
add_action( 'elementor/document/after_save', function ( $document ) {
	if ( is_object( $document ) && method_exists( $document, 'get_main_id' ) ) {
		nk_release_demo_flag( (int) $document->get_main_id() );
	}
} );
/* ---------------------------------------------------------------- 2. Customizer settings follow the theme */
function nk_carry_theme_mods() {
	$here = get_option( 'theme_mods_' . get_stylesheet(), array() );
	if ( is_array( $here ) && array_filter( array_keys( $here ), function ( $k ) { return 0 === strpos( (string) $k, 'nk_' ); } ) ) {
		return; // this theme folder already has NK settings
	}
	global $wpdb;
	$rows = $wpdb->get_results( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'theme\\_mods\\_nk%'" ); // phpcs:ignore
	$best = null;
	$bn   = 0;
	foreach ( (array) $rows as $r ) {
		if ( 'theme_mods_' . get_stylesheet() === $r->option_name ) {
			continue;
		}
		$m = maybe_unserialize( $r->option_value );
		if ( ! is_array( $m ) ) {
			continue;
		}
		$n = count( array_filter( array_keys( $m ), function ( $k ) { return 0 === strpos( (string) $k, 'nk_' ); } ) );
		if ( $n > $bn ) {
			$best = $m;
			$bn   = $n;
		}
	}
	if ( $best ) {
		$here = is_array( $here ) ? $here : array();
		update_option( 'theme_mods_' . get_stylesheet(), array_merge( $best, $here ) );
	}
}
/* v3.8.0: copying Customizer settings from another NK theme folder is opt-in: define( 'NK_ALLOW_THEME_MOD_CARRY', true ); */
if ( defined( 'NK_ALLOW_THEME_MOD_CARRY' ) && NK_ALLOW_THEME_MOD_CARRY ) {
	add_action( 'after_switch_theme', 'nk_carry_theme_mods', 1 );
}
add_action( 'admin_notices', function () {
	if ( NK_FOLDER === get_stylesheet() || ! current_user_can( 'switch_themes' ) ) {
		return;
	}
	echo '<div class="notice notice-warning"><p><strong>NK Studio:</strong> ' . sprintf(
		/* translators: 1: current folder, 2: expected folder */
		esc_html__( 'This theme is installed in the folder "%1$s" instead of "%2$s". Next time update it with Appearance > Themes > Add New > Upload > "Replace current with uploaded", so WordPress keeps one theme and all your settings.', 'nk-studio' ),
		esc_html( get_stylesheet() ),
		esc_html( NK_FOLDER )
	) . '</p></div>';
} );

/* ---------------------------------------------------------------- 3. snapshots, export, import */
function nk_snapshot_data() {
	$data = array(
		'nk_backup'  => 1,
		'theme'      => NK_VERSION,
		'site'       => home_url( '/' ),
		'time'       => time(),
		'theme_mods' => get_option( 'theme_mods_' . get_stylesheet(), array() ),
		'front'      => (int) get_option( 'page_on_front' ),
		'posts'      => array(),
	);
	$types = array( 'page', 'elementor_library', 'nk_work' );
	$ids   = get_posts( array( 'post_type' => $types, 'post_status' => array( 'publish', 'draft', 'private' ), 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_elementor_data' ) ); // phpcs:ignore
	foreach ( $ids as $id ) {
		$p = get_post( $id );
		$data['posts'][] = array(
			'id'       => $id,
			'type'     => $p->post_type,
			'slug'     => $p->post_name,
			'title'    => $p->post_title,
			'home_key' => get_post_meta( $id, '_nk_home_key', true ),
			'data'     => get_post_meta( $id, '_elementor_data', true ),
			'settings' => get_post_meta( $id, '_elementor_page_settings', true ),
		);
	}
	return $data;
}

function nk_restore_snapshot( $data, $with_mods = true ) {
	if ( ! is_array( $data ) || empty( $data['nk_backup'] ) || ! isset( $data['posts'] ) ) {
		return false;
	}
	// the current state is saved first, so a restore can itself be undone
	nk_auto_snapshot( 'before-restore' );
	$n = 0;
	foreach ( (array) $data['posts'] as $p ) {
		$id = 0;
		$cur = ! empty( $p['id'] ) ? get_post( (int) $p['id'] ) : null;
		if ( $cur && $cur->post_type === $p['type'] && ( $cur->post_name === $p['slug'] || 'elementor_library' === $p['type'] ) ) {
			$id = (int) $p['id'];
		} elseif ( ! empty( $p['home_key'] ) ) {
			$f  = get_posts( array( 'post_type' => 'elementor_library', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_nk_home_key', 'meta_value' => $p['home_key'] ) ); // phpcs:ignore
			$id = $f ? (int) $f[0] : 0;
		} elseif ( ! empty( $p['slug'] ) ) {
			$f  = get_page_by_path( $p['slug'], OBJECT, $p['type'] );
			$id = $f ? (int) $f->ID : 0;
		}
		if ( ! $id && ! empty( $data['front'] ) && (int) $data['front'] === (int) $p['id'] ) {
			$id = (int) get_option( 'page_on_front' );
		}
		if ( ! $id || ! is_string( $p['data'] ) || '' === $p['data'] ) {
			continue;
		}
		update_post_meta( $id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $id, '_elementor_data', wp_slash( $p['data'] ) );
		if ( ! empty( $p['settings'] ) ) {
			update_post_meta( $id, '_elementor_page_settings', $p['settings'] );
		}
		delete_post_meta( $id, '_elementor_css' );
		delete_post_meta( $id, '_elementor_element_cache' );
		nk_release_demo_flag( $id );
		$n++;
	}
	if ( $with_mods && ! empty( $data['theme_mods'] ) && is_array( $data['theme_mods'] ) ) {
		update_option( 'theme_mods_' . get_stylesheet(), $data['theme_mods'] );
	}
	if ( function_exists( 'nk_clear_elementor_cache' ) ) {
		nk_clear_elementor_cache();
	}
	return $n;
}

/** Keep the last 5 automatic snapshots (stored outside autoload). */
function nk_auto_snapshot( $why ) {
	$snaps = get_option( 'nk_snapshots', array() );
	$snaps = is_array( $snaps ) ? $snaps : array();
	$data  = nk_snapshot_data();
	if ( ! $data['posts'] ) {
		return;
	}
	array_unshift( $snaps, array( 'time' => time(), 'why' => $why, 'ver' => NK_VERSION, 'data' => $data ) );
	update_option( 'nk_snapshots', array_slice( $snaps, 0, 5 ), false );
}

/* v3.8.0: no automatic snapshot on theme upload (it copied every Elementor document into wp_options). Use Export in NK Studio Setup. */

/* export: download a backup file */
add_action( 'admin_post_nk_export', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'nk_backup' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'nk-studio' ) );
	}
	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="nk-studio-backup-' . gmdate( 'Y-m-d-His' ) . '.json"' );
	echo wp_json_encode( nk_snapshot_data() );
	exit;
} );

/* import a backup file / restore a snapshot */
add_action( 'admin_post_nk_import', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'nk_backup' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'nk-studio' ) );
	}
	$res = false;
	if ( isset( $_POST['nk_snapshot'] ) ) {
		$snaps = get_option( 'nk_snapshots', array() );
		$i     = absint( $_POST['nk_snapshot'] );
		if ( isset( $snaps[ $i ]['data'] ) ) {
			$res = nk_restore_snapshot( $snaps[ $i ]['data'] );
		}
	} elseif ( ! empty( $_FILES['nk_file']['tmp_name'] ) && is_uploaded_file( $_FILES['nk_file']['tmp_name'] ) ) { // phpcs:ignore
		$json = file_get_contents( $_FILES['nk_file']['tmp_name'] ); // phpcs:ignore
		$res  = nk_restore_snapshot( json_decode( (string) $json, true ), ! empty( $_POST['nk_mods'] ) );
	}
	wp_safe_redirect( add_query_arg( 'nk_restored', false === $res ? 'fail' : (int) $res, admin_url( "themes.php?page=nk-setup" ) ) );
	exit;
} );

/** Box shown on top of Appearance > NK Studio Setup. */
function nk_safe_update_box() {
	if ( isset( $_GET['nk_restored'] ) ) { // phpcs:ignore
		$r = sanitize_key( $_GET['nk_restored'] ); // phpcs:ignore
		echo 'fail' === $r
			? '<div class="notice notice-error"><p>' . esc_html__( 'That backup could not be read.', 'nk-studio' ) . '</p></div>'
			: '<div class="notice notice-success"><p>' . esc_html( sprintf( __( 'Restored %d pages / templates. (Your state just before the restore was saved as a snapshot too.)', 'nk-studio' ), (int) $r ) ) . '</p></div>';
	}
	$snaps = get_option( 'nk_snapshots', array() );
	echo '<div style="background:#fff;border:1px solid #c3c4c7;border-left:4px solid #00a32a;padding:14px 18px;margin:16px 0 24px;max-width:900px">';
	echo '<h2 style="margin-top:0">' . esc_html__( 'Safe theme updates', 'nk-studio' ) . '</h2>';
	echo '<ol style="margin-left:18px">';
	echo '<li>' . wp_kses_post( __( 'Before updating: <strong>Download backup</strong> (below).', 'nk-studio' ) ) . '</li>';
	echo '<li>' . wp_kses_post( __( 'Appearance &gt; Themes &gt; Add New &gt; Upload Theme &gt; choose the new zip &gt; <strong>Replace current with uploaded</strong>. Do not delete the old theme first and do not install it as a second theme.', 'nk-studio' ) ) . '</li>';
	echo '<li>' . wp_kses_post( __( 'After updating: Elementor &gt; Tools &gt; <strong>Regenerate Files &amp; Data</strong>, then clear your cache plugin.', 'nk-studio' ) ) . '</li>';
	echo '<li>' . wp_kses_post( __( 'That is all. Do <strong>not</strong> press anything under "Advanced tools" for an update.', 'nk-studio' ) ) . '</li>';
	echo '</ol>';
	echo '<p>' . esc_html__( 'Your pictures, texts, projects, reviews and videos are stored in the database, not in the theme, so replacing the theme never deletes them.', 'nk-studio' ) . '</p>';

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block;margin:0 10px 10px 0">';
	wp_nonce_field( 'nk_backup' );
	echo '<input type="hidden" name="action" value="nk_export"><button class="button button-primary">' . esc_html__( 'Download backup (pages + templates + Customizer)', 'nk-studio' ) . '</button></form>';

	echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block;margin:0 0 10px" onsubmit="return confirm(\'' . esc_js( __( 'Restore pages from this backup file?', 'nk-studio' ) ) . '\')">';
	wp_nonce_field( 'nk_backup' );
	echo '<input type="hidden" name="action" value="nk_import"><input type="file" name="nk_file" accept=".json" required> ';
	echo '<label><input type="checkbox" name="nk_mods" value="1" checked> ' . esc_html__( 'also Customizer settings', 'nk-studio' ) . '</label> ';
	echo '<button class="button">' . esc_html__( 'Restore from file', 'nk-studio' ) . '</button></form>';

	if ( $snaps ) {
		echo '<h3>' . esc_html__( 'Automatic snapshots (saved every time a new theme version is uploaded)', 'nk-studio' ) . '</h3><ul>';
		foreach ( $snaps as $i => $sn ) {
			echo '<li style="margin-bottom:6px"><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline" onsubmit="return confirm(\'' . esc_js( __( 'Restore all pages to this snapshot?', 'nk-studio' ) ) . '\')">';
			wp_nonce_field( 'nk_backup' );
			echo '<input type="hidden" name="action" value="nk_import"><input type="hidden" name="nk_snapshot" value="' . (int) $i . '">';
			echo esc_html( wp_date( 'Y-m-d H:i', (int) $sn['time'] ) . ' — ' . $sn['why'] . ' (' . count( $sn['data']['posts'] ) . ' pages/templates)' ) . ' ';
			echo '<button class="button button-small">' . esc_html__( 'Restore', 'nk-studio' ) . '</button></form></li>';
		}
		echo '</ul>';
	}
	echo '</div>';
}
