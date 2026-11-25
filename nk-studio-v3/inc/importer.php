<?php
/**
 * Demo content: pages (built with the NK widgets, fully editable in Elementor), menus,
 * sample projects and posts, front page + posts page settings.
 *
 * v3.8.0: NOTHING here runs automatically any more (no after_switch_theme import, no menu sync, no Home upgrades).
 * The demo tools are only reachable when wp-config.php defines NK_ENABLE_DEMO_TOOLS = true, and only ever
 * create pages that are missing/empty. They never create sample posts, blogs or projects.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nk_el_id() {
	return substr( md5( uniqid( '', true ) ), 0, 7 );
}

/** Wrap one widget in a full-width, zero-padding container. */
function nk_el_widget( $type, $settings = array() ) {
	$zero = array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true );
	return array(
		'id'       => nk_el_id(),
		'elType'   => 'container',
		'settings' => array(
			'content_width' => 'full',
			'padding'       => $zero,
			'margin'        => $zero,
			'flex_gap'      => array( 'unit' => 'px', 'size' => 0, 'column' => '0', 'row' => '0', 'isLinked' => true ),
		),
		'elements' => array(
			array( 'id' => nk_el_id(), 'elType' => 'widget', 'widgetType' => $type, 'settings' => $settings, 'elements' => array() ),
		),
		'isInner'  => false,
	);
}

function nk_demo_pages() {
	$text = function ( $strong, $muted, $body ) {
		return array( nk_el_widget( 'nk_text_block', array( 'strong' => $strong, 'muted' => $muted, 'text' => '<p>' . $body . '</p>', 'nk_padding' => array( 'unit' => 'px', 'top' => '190', 'right' => '0', 'bottom' => '120', 'left' => '0', 'isLinked' => false ) ) ) );
	};

	$home = nk_home_elements(); // new format: one labelled container per section (inc/elementor/home-builder.php)

	return array(
		'home'         => array( 'title' => 'Home', 'els' => $home ),
		'work'         => array( 'title' => 'Work', 'els' => array( nk_el_widget( 'nk_work_grid', array( 'strong' => 'Work', 'muted' => 'what we build.', 'count' => 24, 'nk_padding' => array( 'unit' => 'px', 'top' => '190', 'right' => '0', 'bottom' => '120', 'left' => '0', 'isLinked' => false ) ) ) ) ),
		'services'     => array( 'title' => 'Services', 'els' => $text( 'Services', 'Branding, digital and more.', 'Describe your services here.' ) ),
		'programs'     => array( 'title' => 'Programs', 'els' => $text( 'Programs', 'Focused engagements.', 'Describe your programs here.' ) ),
		'method'       => array( 'title' => 'Method', 'els' => $text( 'Method', 'How we work.', 'Explain your process here.' ) ),
		'studio'       => array( 'title' => 'Studio', 'els' => array( nk_el_widget( 'nk_studio', array( 'nk_padding' => array( 'unit' => 'px', 'top' => '170', 'right' => '0', 'bottom' => '80', 'left' => '0', 'isLinked' => false ) ) ) ) ),
		'insights'     => array( 'title' => 'Insights', 'els' => array(), 'posts_page' => true ),
		'impact'       => array( 'title' => 'Impact', 'els' => array( nk_el_widget( 'nk_stats', array( 'marquee' => '', 'nk_padding' => array( 'unit' => 'px', 'top' => '190', 'right' => '0', 'bottom' => '120', 'left' => '0', 'isLinked' => false ) ) ) ) ),
		'awards'       => array( 'title' => 'Awards', 'els' => $text( 'Awards', 'Recognition.', 'List your awards here.' ) ),
		'archive'      => array( 'title' => 'Archive', 'els' => $text( 'Archive', 'Past work.', 'Add archived projects here.' ) ),
		'testimonials' => array( 'title' => 'Testimonials', 'els' => array( nk_el_widget( 'nk_testimonials', array( 'nk_padding' => array( 'unit' => 'px', 'top' => '190', 'right' => '0', 'bottom' => '120', 'left' => '0', 'isLinked' => false ) ) ) ) ),
		'faqs'         => array( 'title' => 'FAQs', 'els' => $text( 'FAQs', 'Answers.', 'Add your questions here.' ) ),
		'clients'      => array( 'title' => 'Clients', 'els' => array( nk_el_widget( 'nk_logo_strip', array( 'marquee' => 'yes', 'nk_padding' => array( 'unit' => 'px', 'top' => '190', 'right' => '0', 'bottom' => '120', 'left' => '0', 'isLinked' => false ) ) ) ) ),
		'careers'      => array( 'title' => 'Careers', 'els' => $text( 'Careers', 'Join us.', 'Open positions go here.' ) ),
		'contact'      => array( 'title' => 'Contact', 'els' => $text( 'Contact', 'Let’s talk.', 'Write to us at <a href="mailto:' . esc_attr( nk_opt( 'nk_footer_email', 'hi@nk.studio' ) ) . '">' . esc_html( nk_opt( 'nk_footer_email', 'hi@nk.studio' ) ) . '</a>. Replace this text, or drop a form widget (Elementor Pro / any form plugin) below.' ) ),
	);
}

function nk_import_demo( $force = false ) {
	if ( ! $force && get_option( 'nk_demo_imported' ) ) {
		return;
	}
	if ( ! defined( 'NK_IMPORTING' ) ) {
		define( 'NK_IMPORTING', true );
	}
	$ids = array();
	foreach ( nk_demo_pages() as $slug => $page ) {
		$existing = get_page_by_path( $slug );
		// A page is "empty" if it has no text and no Elementor data (e.g. WordPress' own blank Home page).
		$is_empty = $existing && '' === trim( wp_strip_all_tags( $existing->post_content ) ) && ! get_post_meta( $existing->ID, '_elementor_data', true );
		if ( $existing && ! $is_empty ) {
			// v3.4: never overwrite a page that has content — not even one the importer created once and you edited
			// since. Re-import only creates pages that are missing or empty.
			$ids[ $slug ] = $existing->ID;
			continue;
		}
		if ( $existing && ! $force && ! $is_empty ) {
			$ids[ $slug ] = $existing->ID;
			continue;
		}
		$args = array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $page['title'], 'post_name' => $slug, 'post_content' => '' );
		if ( $existing ) {
			$args['ID'] = $existing->ID;
			$id         = wp_update_post( $args );
		} else {
			$id = wp_insert_post( $args );
		}
		if ( is_wp_error( $id ) || ! $id ) {
			continue;
		}
		$ids[ $slug ] = $id;
		update_post_meta( $id, '_nk_demo', 1 );
		if ( ! empty( $page['els'] ) ) {
			update_post_meta( $id, '_elementor_edit_mode', 'builder' );
			update_post_meta( $id, '_elementor_template_type', 'wp-page' );
			update_post_meta( $id, '_elementor_version', nk_elementor_version() );
			update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( $page['els'] ) ) );
			update_post_meta( $id, '_wp_page_template', 'default' );
			delete_post_meta( $id, '_elementor_css' );
		}
	}

	flush_rewrite_rules();
	update_option( 'nk_demo_imported', NK_VERSION );
}

function nk_import_menus( $ids ) {
	$link = function ( $slug, $hash = '' ) use ( $ids ) {
		return isset( $ids[ $slug ] ) ? get_permalink( $ids[ $slug ] ) . $hash : home_url( '/' );
	};
	$menus = array(
		'primary'  => array( 'Primary', array( 'HOME' => 'home', 'WORK' => 'work', 'SERVICES' => 'services', 'REVIEW' => 'testimonials', 'FAQ' => 'faqs' ) ),
		'footer_1' => array( 'Company', array( 'Home' => 'home', 'Work' => 'work', 'Method' => 'method', 'Studio' => 'studio', 'Contact' => 'contact' ) ),
		'footer_2' => array( 'Services', array( 'Overview' => array( 'services', '' ), 'Branding' => array( 'services', '#branding' ), 'Digital' => array( 'services', '#digital' ), 'Labs' => array( 'services', '#labs' ), 'Boost' => array( 'services', '#boost' ) ) ),
		'footer_3' => array( 'Programs', array( 'Overview' => array( 'programs', '' ), 'Foundation' => array( 'programs', '#foundation' ), 'Investor Readiness' => array( 'programs', '#investor-readiness' ), 'Global Launch' => array( 'programs', '#global-launch' ), 'Digital Reset' => array( 'programs', '#digital-reset' ), 'Product Discovery' => array( 'programs', '#product-discovery' ) ) ),
		'footer_4' => array( 'Discover', array( 'Impact' => 'impact', 'Awards' => 'awards', 'Archive' => 'archive' ) ),
		'footer_5' => array( 'Learn', array( 'Insights' => 'insights', 'Testimonials' => 'testimonials', 'FAQs' => 'faqs', 'Clients' => 'clients', 'Careers' => 'careers' ) ),
	);
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	foreach ( $menus as $loc => $def ) {
		list( $name, $items ) = $def;
		$menu = wp_get_nav_menu_object( $name );
		if ( $menu ) {
			if ( ! empty( $locations[ $loc ] ) ) {
				continue; // user already configured this location
			}
			$menu_id = $menu->term_id;
		} else {
			$menu_id = wp_create_nav_menu( $name );
			if ( is_wp_error( $menu_id ) ) {
				continue;
			}
			$pos = 1;
			foreach ( $items as $label => $target ) {
				$slug = is_array( $target ) ? $target[0] : $target;
				$hash = is_array( $target ) ? $target[1] : '';
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'    => $label,
					'menu-item-url'      => $link( $slug, $hash ),
					'menu-item-type'     => 'custom',
					'menu-item-status'   => 'publish',
					'menu-item-position' => $pos++,
				) );
			}
		}
		$locations[ $loc ] = $menu_id;
	}
	set_theme_mod( 'nav_menu_locations', $locations );
}
