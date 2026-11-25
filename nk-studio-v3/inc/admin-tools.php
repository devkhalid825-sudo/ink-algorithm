<?php
/**
 * Appearance > NK Studio Setup (v3.8.0).
 *
 * Read-only status first; every action below is an explicit, nonce-protected button.
 * Nothing on this screen runs on its own, and nothing deletes or rewrites existing pages, posts or menus.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Original widget types the inner pages rely on. */
function nk_neuros_widget_inventory() {
	return array( 'neuros_heading', 'neuros_button', 'neuros_icon_box', 'neuros_special_text', 'neuros_image_carousel', 'neuros_content_slider', 'neuros_team_members', 'neuros_blog_listing', 'neuros_wpforms', 'neuros_video_button', 'neuros_gallery', 'neuros_testimonial_carousel', 'lakit-tabs' );
}

/** Recommended primary navigation: label => candidate page slugs (first existing wins). */
function nk_recommended_menu() {
	return array(
		'Home'     => array(),
		'Work'     => array( 'portfolio', 'work' ),
		'Services' => array( 'our-services', 'services' ),
		'Reviews'  => array( 'review', 'reviews', 'testimonials' ),
		'FAQ'      => array( 'faq', 'faqs' ),
		'Blog'     => array( 'blogs', 'blog' ),
		'Contact'  => array( 'contacts', 'contact', 'contact-us' ),
	);
}

add_action( 'admin_menu', function () {
	add_theme_page( __( 'NK Studio Setup', 'nk-studio' ), __( 'NK Studio Setup', 'nk-studio' ), 'manage_options', 'nk-setup', 'nk_render_setup_page' );
} );

function nk_setup_back( $msg ) {
	wp_safe_redirect( add_query_arg( array( 'page' => 'nk-setup', 'nk_msg' => rawurlencode( $msg ) ), admin_url( 'themes.php' ) ) );
	exit;
}

/* ---- action: set front page (only the two reading options) */
add_action( 'admin_post_nk_set_front', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'nk_set_front' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'nk-studio' ) );
	}
	$id = absint( $_POST['nk_front_id'] ?? 0 );
	$p  = $id ? get_post( $id ) : null;
	if ( ! $p || 'page' !== $p->post_type || 'publish' !== $p->post_status ) {
		nk_setup_back( sprintf( 'Page %d is not a published page. Nothing changed.', $id ) );
	}
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $id );
	nk_setup_back( sprintf( 'Front page set to "%s" (ID %d). show_on_front=page. page_for_posts was NOT changed.', $p->post_title, $id ) );
} );

/* ---- action: create a NEW menu with the real URLs and assign it to the Primary location */
add_action( 'admin_post_nk_make_menu', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'nk_make_menu' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'nk-studio' ) );
	}
	$name = 'NK Primary (original URLs)';
	if ( wp_get_nav_menu_object( $name ) ) {
		nk_setup_back( 'A menu named "' . $name . '" already exists. Nothing changed (edit it in Appearance > Menus).' );
	}
	$want    = isset( $_POST['nk_items'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['nk_items'] ) ) : array();
	$menu_id = wp_create_nav_menu( $name );
	if ( is_wp_error( $menu_id ) ) {
		nk_setup_back( 'Could not create the menu: ' . $menu_id->get_error_message() );
	}
	$pos = 1;
	foreach ( nk_recommended_menu() as $label => $slugs ) {
		if ( ! in_array( $label, $want, true ) ) {
			continue;
		}
		if ( 'Home' === $label ) {
			wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => $label, 'menu-item-url' => home_url( '/' ), 'menu-item-type' => 'custom', 'menu-item-status' => 'publish', 'menu-item-position' => $pos++ ) );
			continue;
		}
		foreach ( $slugs as $slug ) {
			$page = get_page_by_path( $slug, OBJECT, 'page' );
			if ( $page && 'publish' === $page->post_status ) {
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'     => $label,
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $page->ID,
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-position'  => $pos++,
				) );
				break;
			}
		}
	}
	$loc            = get_theme_mod( 'nav_menu_locations', array() );
	$loc['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $loc );
	nk_setup_back( 'Menu "' . $name . '" created and assigned to the Primary location. Existing menus were not modified.' );
} );

/* ---- tools that rewrite content: only when the site owner opts in via wp-config.php */
add_action( 'admin_post_nk_demo_tool', function () {
	if ( ! defined( 'NK_ENABLE_DEMO_TOOLS' ) || ! NK_ENABLE_DEMO_TOOLS || ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'nk_demo_tool' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'nk-studio' ) );
	}
	$msg = '';
	switch ( sanitize_key( $_POST['nk_action'] ?? '' ) ) {
		case 'reimport':
			nk_import_demo( true );
			$msg = 'Missing/empty demo pages created. Pages with content were not touched.';
			break;
		case 'home_restore':
			$msg = nk_home_restore_backup() ? 'Previous Home restored.' : 'No backup found.';
			break;
		case 'home_new':
			nk_enable_containers();
			$msg = nk_home_to_new_format( 'rebuild' ) ? 'Home rebuilt from theme defaults (backup saved).' : 'No front page set.';
			break;
		case 'templates':
			$msg = sprintf( '%d Home sections saved as templates.', nk_sync_home_templates( true ) );
			break;
	}
	nk_setup_back( $msg );
} );

function nk_render_setup_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	global $wpdb;
	$front      = (int) get_option( 'page_on_front' );
	$show       = get_option( 'show_on_front' );
	$posts_page = (int) get_option( 'page_for_posts' );
	$counts     = array(
		'post'    => (int) wp_count_posts( 'post' )->publish,
		'page'    => (int) wp_count_posts( 'page' )->publish,
		'nk_blog' => post_type_exists( 'nk_blog' ) ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='nk_blog' AND post_status='publish'" ) : 0, // phpcs:ignore
	);
	$blog_page = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE post_type='page' AND post_status='publish' AND ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_elementor_data' AND meta_value LIKE '%neuros_blog_listing%') LIMIT 1" ); // phpcs:ignore

	echo '<div class="wrap"><h1>' . esc_html__( 'NK Studio Setup', 'nk-studio' ) . ' <small style="font-size:13px;opacity:.6">v' . esc_html( NK_VERSION ) . '</small></h1>';
	if ( ! empty( $_GET['nk_msg'] ) ) { // phpcs:ignore
		echo '<div class="notice notice-success"><p>' . esc_html( wp_unslash( rawurldecode( (string) $_GET['nk_msg'] ) ) ) . '</p></div>'; // phpcs:ignore
	}
	echo '<p style="max-width:860px">' . esc_html__( 'Nothing on this page runs automatically. Existing pages, posts, menus and Elementor layouts are never rewritten by the theme. Every button below is explicit.', 'nk-studio' ) . '</p>';

	/* ---- status */
	echo '<h2>' . esc_html__( 'Status', 'nk-studio' ) . '</h2><table class="widefat striped" style="max-width:860px"><tbody>';
	$row = function ( $k, $v ) {
		echo '<tr><th style="width:260px">' . esc_html( $k ) . '</th><td>' . $v . '</td></tr>'; // phpcs:ignore
	};
	$front_html = 'page' === $show && $front && get_post( $front )
		? esc_html( get_the_title( $front ) ) . ' (ID ' . (int) $front . ') — <a href="' . esc_url( admin_url( 'post.php?post=' . $front . '&action=elementor' ) ) . '">Edit with Elementor</a>'
		: '<strong style="color:#b32d2e">' . esc_html( 'show_on_front=' . $show . ' — no static front page set' ) . '</strong>';
	$row( 'Front page', $front_html );
	$row( 'Posts page (page_for_posts)', $posts_page ? esc_html( get_the_title( $posts_page ) ) . ' (ID ' . $posts_page . ')' : '0 — not set. This is fine: the blog is the Elementor page' . ( $blog_page ? ' ID ' . (int) $blog_page : ' /blogs/' ) . ' using the original listing widget; nothing is changed automatically.' );
	$row( 'Published posts / pages', (int) $counts['post'] . ' posts, ' . (int) $counts['page'] . ' pages' );
	$row( 'NK demo blogs (nk_blog)', $counts['nk_blog'] ? '<strong style="color:#b32d2e">' . (int) $counts['nk_blog'] . ' found — these are demo entries from an older NK version, not your blog posts. They are hidden from the site; trash them in the database/admin when convenient.</strong>' : '0 (good)' );
	$row( 'Compatibility plugin', defined( 'NK_NEUROS_COMPAT_VERSION' ) ? 'NK Neuros Compatibility ' . esc_html( NK_NEUROS_COMPAT_VERSION ) . ' active' : '<strong style="color:#b32d2e">not active — pages using Neuros widgets can fatal</strong>' );

	if ( did_action( 'elementor/loaded' ) ) {
		$wm = \Elementor\Plugin::$instance->widgets_manager;
		$ok = array();
		$no = array();
		foreach ( nk_neuros_widget_inventory() as $w ) {
			if ( $wm->get_widget_types( $w ) ) {
				$ok[] = $w;
			} else {
				$no[] = $w;
			}
		}
		$row( 'Original widgets registered', esc_html( count( $ok ) . ' / ' . count( nk_neuros_widget_inventory() ) ) . ( $no ? ' — missing: <code>' . esc_html( implode( ', ', $no ) ) . '</code> (activate neuros-plugin / lastudio-element-kit)' : ' — all present' ) );
	}
	echo '</tbody></table>';

	/* ---- set front page */
	echo '<h2>' . esc_html__( 'Front page', 'nk-studio' ) . '</h2><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" onsubmit="return confirm(\'Set this page as the static front page?\')">';
	wp_nonce_field( 'nk_set_front' );
	echo '<input type="hidden" name="action" value="nk_set_front">';
	echo '<p>' . esc_html__( 'Page ID of the imported NK homepage:', 'nk-studio' ) . ' <input type="number" name="nk_front_id" value="' . esc_attr( $front && 'page' === $show ? $front : 18007 ) . '" style="width:110px"> <button class="button">' . esc_html__( 'Use as front page', 'nk-studio' ) . '</button></p>';
	echo '<p class="description">' . esc_html__( 'Changes only "Your homepage displays" in Settings > Reading. The page itself is never edited.', 'nk-studio' ) . '</p></form>';

	/* ---- menu */
	echo '<h2>' . esc_html__( 'Navigation (original URLs)', 'nk-studio' ) . '</h2>';
	$loc  = get_nav_menu_locations();
	$cur  = ! empty( $loc['primary'] ) ? wp_get_nav_menu_object( $loc['primary'] ) : null;
	echo '<p>' . esc_html__( 'Current Primary menu:', 'nk-studio' ) . ' <strong>' . esc_html( $cur ? $cur->name : 'none (the theme fallback is shown)' ) . '</strong></p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" onsubmit="return confirm(\'Create a NEW menu and assign it to the Primary location? Existing menus are not changed.\')">';
	wp_nonce_field( 'nk_make_menu' );
	echo '<input type="hidden" name="action" value="nk_make_menu"><table class="widefat striped" style="max-width:860px"><thead><tr><th>Use</th><th>Label</th><th>Resolves to</th></tr></thead><tbody>';
	foreach ( nk_recommended_menu() as $label => $slugs ) {
		$url = home_url( '/' );
		$hit = 'Home' === $label;
		foreach ( $slugs as $slug ) {
			$p = get_page_by_path( $slug, OBJECT, 'page' );
			if ( $p && 'publish' === $p->post_status ) {
				$url = get_permalink( $p );
				$hit = true;
				break;
			}
		}
		$checked = $hit && in_array( $label, array( 'Home', 'Work', 'Services', 'Reviews', 'FAQ' ), true ) ? ' checked' : '';
		echo '<tr><td><input type="checkbox" name="nk_items[]" value="' . esc_attr( $label ) . '"' . $checked . ( $hit ? '' : ' disabled' ) . '></td><td>' . esc_html( $label ) . '</td><td>' . ( $hit ? esc_html( $url ) : '<em>no matching page found</em>' ) . '</td></tr>';
	}
	echo '</tbody></table><p><button class="button button-primary">' . esc_html__( 'Create menu "NK Primary (original URLs)" and use it as Primary', 'nk-studio' ) . '</button></p>';
	echo '<p class="description">' . esc_html__( 'Default selection matches the NK design (Home, Work, Services, Reviews, FAQ). Tick Blog / Contact to add them. Items link to the real pages, so slugs and SEO URLs are untouched.', 'nk-studio' ) . '</p></form>';

	/* ---- backup (kept) */
	if ( function_exists( 'nk_safe_update_box' ) ) {
		nk_safe_update_box();
	}

	/* ---- opt-in tools */
	if ( defined( 'NK_ENABLE_DEMO_TOOLS' ) && NK_ENABLE_DEMO_TOOLS ) {
		echo '<details style="margin-top:28px;max-width:900px;background:#fff;border:1px solid #c3c4c7;border-left:4px solid #d63638;padding:10px 18px"><summary style="cursor:pointer;font-weight:600">' . esc_html__( 'Demo / rebuild tools (enabled by NK_ENABLE_DEMO_TOOLS) — can change pages', 'nk-studio' ) . '</summary>';
		$btn = function ( $action, $label, $confirm ) {
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block;margin:8px 8px 0 0" onsubmit="return confirm(\'' . esc_js( $confirm ) . '\')">';
			wp_nonce_field( 'nk_demo_tool' );
			echo '<input type="hidden" name="action" value="nk_demo_tool"><button class="button" name="nk_action" value="' . esc_attr( $action ) . '">' . esc_html( $label ) . '</button></form>';
		};
		$btn( 'reimport', 'Create missing demo pages', 'Creates demo pages only where the slug is missing or empty. Continue?' );
		$btn( 'templates', 'Save Home sections as templates', 'Overwrites "NK Home ·" templates. Continue?' );
		$btn( 'home_new', 'Rebuild Home from theme defaults', 'THIS REPLACES the Elementor data of the front page. A backup is stored. Continue?' );
		$btn( 'home_restore', 'Restore previous Home backup', 'Home goes back to an older version. Continue?' );
		echo '</details>';
	}
	echo '</div>';
}
