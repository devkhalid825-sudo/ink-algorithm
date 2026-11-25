<?php
/**
 * v3.8.0: every automatic admin_init upgrade of the Home page was REMOVED. The helpers below only run when an
 * administrator explicitly clicks a tool in Appearance > NK Studio Setup (and only if NK_ENABLE_DEMO_TOOLS is defined).
 * Page ID 18007 (imported NK Home) is never rebuilt, relabelled, re-templated or patched automatically.
 *
 * v3.0 — Home page in Elementor's NEW format (Flexbox Containers) + reusable Home components.
 *
 * - Home is one container per section, each labelled in the Navigator ("Hero", "Work", …),
 *   with a CSS class (nk-block--{key}) and an anchor id, so the whole page is edited in Elementor.
 * - Old-format pages (Section > Column, or leftovers from the previous theme) are rebuilt once in the new format.
 *   NK widget settings already saved on the page are kept. A backup is stored; restore it from
 *   Appearance > NK Studio Setup.
 * - Every Home section is also saved as an Elementor template ("NK Home · Hero" …, category "NK Home Sections").
 *   Insert one on any inner page with the "NK Saved Section" widget (linked: edit the template once, every page updates)
 *   or as a copy from Elementor's template library (folder icon > My Templates).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** The Home layout: key => [ label, widget, default settings ]. */
function nk_home_sections() {
	$id = 'nk_el_id';
	return array(
		'hero'         => array( 'Hero (3D)', 'nk_ink_hero', array() ),
		'clients'      => array( 'Client logos', 'nk_logo_strip', array(
			'items' => array(
				array( '_id' => $id(), 'text' => 'LA NACION' ),
				array( '_id' => $id(), 'text' => 'Interbank' ),
				array( '_id' => $id(), 'text' => 'STELLA ARTOIS' ),
				array( '_id' => $id(), 'text' => 'MSBC' ),
				array( '_id' => $id(), 'text' => 'WARREN LOTAS' ),
			),
		) ),
		'video'        => array( 'Scroll video', 'nk_video', array() ),
		'build'        => array( 'Build clarity', 'nk_hero', array(
			'line1' => 'Build', 'line2' => 'clarity', 'grid' => '', 'slash' => '',
			'text'  => 'Growth brings new questions. We turn complex challenges into clear priorities, shared decisions and experiences people can understand and use.',
			'cta_text' => 'SEE THE OUTCOMES', 'nk_min_height' => array( 'unit' => 'vh', 'size' => 85 ),
			'images' => array( array( '_id' => $id() ), array( '_id' => $id() ), array( '_id' => $id() ) ),
		) ),
		'impact'       => array( 'Impact stats', 'nk_stats', array() ),
		'features'     => array( 'Strategic Pillars', 'nk_features', array() ),
		'work'         => array( 'Work', 'nk_work_grid', array() ),
		'deliver'      => array( 'How we deliver', 'nk_process', array() ),
		'insights'     => array( 'Insights', 'nk_insights', array() ),
		'testimonials' => array( 'Testimonials', 'nk_testimonials', array() ),
		'social'       => array( 'Social videos', 'nk_social_videos', array() ),
		'faq'          => array( 'FAQs', 'nk_faq', array() ),
		'studio'       => array( 'Studio', 'nk_studio', array() ),
	);
}

/** One section = a full-width Flexbox Container (new format) holding the NK widget. */
function nk_block( $key, $label, $widget, $settings = array() ) {
	$el = nk_el_widget( $widget, $settings );
	$el['settings'] = array_merge( $el['settings'], array(
		'flex_direction' => 'column',
		'_title'         => $label,
		'css_classes'    => 'nk-block nk-block--' . $key,
		'_element_id'    => $key,
	) );
	return $el;
}

/** Home elements in the new format. $reuse = settings already saved on the page, per widget type, in page order. */
function nk_home_elements( $reuse = array() ) {
	$els = array();
	foreach ( nk_home_sections() as $key => $def ) {
		list( $label, $widget, $settings ) = $def;
		if ( ! empty( $reuse[ $widget ] ) ) {
			$settings = array_shift( $reuse[ $widget ] );
		}
		$els[] = nk_block( $key, $label, $widget, is_array( $settings ) ? $settings : array() );
	}
	return $els;
}

/** Walk an Elementor tree. */
function nk_el_walk( $els, $cb ) {
	foreach ( (array) $els as $el ) {
		if ( ! is_array( $el ) ) {
			continue;
		}
		$cb( $el );
		if ( ! empty( $el['elements'] ) ) {
			nk_el_walk( $el['elements'], $cb );
		}
	}
}

/** Old format = uses Section/Column, or has no NK widget at all (content left from the previous theme). */
function nk_el_is_old_format( $els ) {
	$legacy = false;
	$nk     = 0;
	nk_el_walk( $els, function ( $el ) use ( &$legacy, &$nk ) {
		$type = $el['elType'] ?? '';
		if ( 'section' === $type || 'column' === $type ) {
			$legacy = true;
		}
		if ( 'widget' === $type && 0 === strpos( (string) ( $el['widgetType'] ?? '' ), 'nk_' ) ) {
			$nk++;
		}
	} );
	return $legacy || 0 === $nk;
}

/** Settings of NK widgets already on the page, grouped by widget type (page order). */
function nk_el_collect_nk_settings( $els ) {
	$out = array();
	nk_el_walk( $els, function ( $el ) use ( &$out ) {
		$w = (string) ( $el['widgetType'] ?? '' );
		if ( 'widget' === ( $el['elType'] ?? '' ) && 0 === strpos( $w, 'nk_' ) && 'nk_template' !== $w ) {
			$out[ $w ][] = isset( $el['settings'] ) && is_array( $el['settings'] ) ? $el['settings'] : array();
		}
	} );
	return $out;
}

/** Already new format: give top-level containers a Navigator label, class and anchor (nothing else changes). */
function nk_el_label_blocks( $els ) {
	$keys_by_widget = array();
	foreach ( nk_home_sections() as $key => $def ) {
		$keys_by_widget[ $def[1] ][] = array( $key, $def[0] );
	}
	$used = array();
	foreach ( $els as $i => $el ) {
		if ( 'container' !== ( $el['elType'] ?? '' ) || empty( $el['elements'][0]['widgetType'] ) || 1 !== count( $el['elements'] ) ) {
			continue;
		}
		$w = $el['elements'][0]['widgetType'];
		$n = $used[ $w ] ?? 0;
		$used[ $w ] = $n + 1;
		if ( empty( $keys_by_widget[ $w ][ $n ] ) ) {
			continue;
		}
		list( $key, $label ) = $keys_by_widget[ $w ][ $n ];
		$s = isset( $el['settings'] ) && is_array( $el['settings'] ) ? $el['settings'] : array();
		if ( empty( $s['_title'] ) ) {
			$s['_title'] = $label;
		}
		if ( empty( $s['css_classes'] ) || false === strpos( $s['css_classes'], 'nk-block' ) ) {
			$s['css_classes'] = trim( ( $s['css_classes'] ?? '' ) . ' nk-block nk-block--' . $key );
		}
		if ( empty( $s['_element_id'] ) ) {
			$s['_element_id'] = $key;
		}
		$s['content_width'] = $s['content_width'] ?? 'full';
		$els[ $i ]['settings'] = $s;
	}
	return $els;
}

function nk_elementor_version() {
	return defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.25.0';
}

/** Save Elementor data on a post + mark it as built with the current Elementor (new format). */
function nk_save_el_data( $post_id, $els ) {
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_version', nk_elementor_version() );
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( array_values( $els ) ) ) );
	delete_post_meta( $post_id, '_elementor_css' );
	delete_post_meta( $post_id, '_elementor_element_cache' );
}

function nk_clear_elementor_cache() {
	if ( did_action( 'elementor/loaded' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
}

/**
 * Put the Home page in the new format.
 * $mode: 'auto' (rebuild only if old format, otherwise just label) | 'rebuild' (always rebuild, keeping NK settings).
 * Returns 'rebuilt' | 'labelled' | false.
 */
function nk_home_to_new_format( $mode = 'auto' ) {
	$home = (int) get_option( 'page_on_front' );
	if ( ! $home ) {
		return false;
	}
	$raw = get_post_meta( $home, '_elementor_data', true );
	$els = is_string( $raw ) && '' !== $raw ? json_decode( $raw, true ) : array();
	$els = is_array( $els ) ? $els : array();

	if ( 'rebuild' !== $mode && $els && ! nk_el_is_old_format( $els ) ) {
		nk_save_el_data( $home, nk_el_label_blocks( $els ) );
		nk_clear_elementor_cache();
		return 'labelled';
	}

	// Backup (last 3) — restore from Appearance > NK Studio Setup.
	if ( $raw ) {
		$backups = get_option( 'nk_home_backups', array() );
		array_unshift( $backups, array( 'time' => time(), 'post' => $home, 'data' => $raw ) );
		update_option( 'nk_home_backups', array_slice( $backups, 0, 3 ), false );
	}
	nk_save_el_data( $home, nk_home_elements( nk_el_collect_nk_settings( $els ) ) );
	update_post_meta( $home, '_wp_page_template', 'default' );
	nk_clear_elementor_cache();
	return 'rebuilt';
}

function nk_home_restore_backup() {
	$backups = get_option( 'nk_home_backups', array() );
	if ( empty( $backups[0]['post'] ) || empty( $backups[0]['data'] ) ) {
		return false;
	}
	$b = array_shift( $backups );
	update_post_meta( (int) $b['post'], '_elementor_data', wp_slash( $b['data'] ) );
	delete_post_meta( (int) $b['post'], '_elementor_css' );
	delete_post_meta( (int) $b['post'], '_elementor_element_cache' );
	update_option( 'nk_home_backups', $backups, false );
	nk_clear_elementor_cache();
	return true;
}

/**
 * Save every Home section as an Elementor template (category "NK Home Sections").
 * $overwrite false = only create missing ones (your edits to existing templates are kept).
 */
function nk_sync_home_templates( $overwrite = false ) {
	if ( ! post_type_exists( 'elementor_library' ) ) {
		return 0;
	}
	$home = (int) get_option( 'page_on_front' );
	$raw  = $home ? get_post_meta( $home, '_elementor_data', true ) : '';
	$els  = is_string( $raw ) && '' !== $raw ? json_decode( $raw, true ) : array();
	if ( ! is_array( $els ) || ! $els || nk_el_is_old_format( $els ) ) {
		$els = nk_home_elements();
	}

	$cat = 0;
	if ( taxonomy_exists( 'elementor_library_category' ) ) {
		$term = term_exists( 'nk-home-sections', 'elementor_library_category' );
		if ( ! $term ) {
			$term = wp_insert_term( 'NK Home Sections', 'elementor_library_category', array( 'slug' => 'nk-home-sections' ) );
		}
		$cat = is_array( $term ) ? (int) $term['term_id'] : 0;
	}

	$done = 0;
	foreach ( $els as $el ) {
		if ( 'container' !== ( $el['elType'] ?? '' ) ) {
			continue;
		}
		$key   = sanitize_key( $el['settings']['_element_id'] ?? '' );
		$label = $el['settings']['_title'] ?? '';
		if ( ! $key || ! $label ) {
			continue;
		}
		$found = get_posts( array( 'post_type' => 'elementor_library', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_nk_home_key', 'meta_value' => $key ) ); // phpcs:ignore
		if ( $found && ! $overwrite ) {
			continue;
		}
		$args = array( 'post_type' => 'elementor_library', 'post_status' => 'publish', 'post_title' => 'NK Home · ' . $label );
		if ( $found ) {
			$args['ID'] = $found[0];
			$tid        = wp_update_post( $args );
		} else {
			$tid = wp_insert_post( $args );
		}
		if ( ! $tid || is_wp_error( $tid ) ) {
			continue;
		}
		$el['id'] = nk_el_id(); // fresh ids so template CSS never clashes with Home
		update_post_meta( $tid, '_nk_home_key', $key );
		update_post_meta( $tid, '_elementor_template_type', 'section' );
		nk_save_el_data( $tid, array( $el ) );
		if ( taxonomy_exists( 'elementor_library_type' ) ) {
			wp_set_object_terms( $tid, 'section', 'elementor_library_type' );
		}
		if ( $cat ) {
			wp_set_object_terms( $tid, array( $cat ), 'elementor_library_category' );
		}
		$done++;
	}
	nk_clear_elementor_cache();
	return $done;
}

/** Elementor's Flexbox Container (+ nested elements) on, so new sections are added in the new format. */
function nk_enable_containers() {
	foreach ( array( 'container', 'nested-elements' ) as $f ) {
		if ( 'active' !== get_option( 'elementor_experiment-' . $f ) ) {
			update_option( 'elementor_experiment-' . $f, 'active' );
		}
	}
}

/* One-time (v3.0.15): Home is rebuilt EXACTLY like the final design (fresh defaults, every section a labelled
   Elementor container). The previous Home is saved as a backup: Appearance > NK Studio Setup > Restore previous Home. */

/* One-time v3.0 upgrade (runs once for admins after the theme update). */

/**
 * v3.2 one-time: add the "Social videos" section (NK Social Videos) to the existing Home, right after Testimonials.
 * Nothing else on the page changes; a backup is saved first (Appearance > NK Studio Setup > Restore previous Home).
 * On the live site the section stays hidden until a video is added under Dashboard > Social Videos.
 */
function nk_add_social_videos_to_home() {
	$home = (int) get_option( 'page_on_front' );
	if ( ! $home ) {
		return false;
	}
	$raw = get_post_meta( $home, '_elementor_data', true );
	if ( ! is_string( $raw ) || '' === $raw || false !== strpos( $raw, 'nk_social_videos' ) ) {
		return false;
	}
	$els = json_decode( $raw, true );
	if ( ! is_array( $els ) || ! $els ) {
		return false;
	}
	$at = null;
	foreach ( $els as $i => $el ) {
		if ( false !== strpos( wp_json_encode( $el ), '"nk_testimonials"' ) ) {
			$at = $i + 1;
		}
	}
	if ( null === $at ) { // no testimonials: before the Studio section, else at the end
		foreach ( $els as $i => $el ) {
			if ( false !== strpos( wp_json_encode( $el ), '"nk_studio"' ) ) {
				$at = $i;
				break;
			}
		}
	}
	if ( null === $at ) {
		$at = count( $els );
	}
	$backups = get_option( 'nk_home_backups', array() );
	array_unshift( $backups, array( 'time' => time(), 'post' => $home, 'data' => $raw ) );
	update_option( 'nk_home_backups', array_slice( $backups, 0, 3 ), false );

	array_splice( $els, $at, 0, array( nk_block( 'social', 'Social videos', 'nk_social_videos', array() ) ) );
	nk_save_el_data( $home, $els );
	nk_clear_elementor_cache();
	return true;
}


/**
 * v3.7.4 one-time: the "Align what matters" statement on Home becomes the detailed "How we deliver" section
 * (NK How We Work). A backup is saved first (Appearance > NK Studio Setup > Restore previous Home).
 * Also moves the Reviews heading to the new "What our clients say." cards design (only if it still has the old default texts).
 */
function nk_upgrade_home_v374() {
	$home = (int) get_option( 'page_on_front' );
	if ( ! $home ) {
		return false;
	}
	$raw = get_post_meta( $home, '_elementor_data', true );
	if ( ! is_string( $raw ) || '' === $raw ) {
		return false;
	}
	$els = json_decode( $raw, true );
	if ( ! is_array( $els ) || ! $els ) {
		return false;
	}
	$changed = false;

	foreach ( $els as $i => $el ) {
		$json = wp_json_encode( $el );
		// 1. Align what matters (statement) -> How we deliver
		if ( false !== strpos( $json, '"nk_statement"' ) && ( 'align' === ( $el['settings']['_element_id'] ?? '' ) || false !== stripos( $json, 'Align what' ) ) && false === strpos( $json, '"nk_process"' ) ) {
			$els[ $i ] = nk_block( 'deliver', 'How we deliver', 'nk_process', array() );
			$changed   = true;
			continue;
		}
	}

	// 2. Reviews: old default heading -> new one (walk by reference)
	$fix = function ( &$list ) use ( &$fix, &$changed ) {
		foreach ( $list as &$node ) {
			if ( ! is_array( $node ) ) {
				continue;
			}
			if ( 'widget' === ( $node['elType'] ?? '' ) && 'nk_testimonials' === ( $node['widgetType'] ?? '' ) ) {
				$st = isset( $node['settings'] ) && is_array( $node['settings'] ) ? $node['settings'] : array();
				$old_strong = ! isset( $st['strong'] ) || 'Voices' === $st['strong'];
				$old_muted  = ! isset( $st['muted'] ) || 'From ambitious leaders.' === $st['muted'];
				if ( $old_strong && $old_muted ) {
					$st['strong'] = 'What our clients say.';
					$st['muted']  = '';
					$node['settings'] = $st;
					$changed = true;
				}
			}
			if ( ! empty( $node['elements'] ) && is_array( $node['elements'] ) ) {
				$fix( $node['elements'] );
			}
		}
		unset( $node );
	};
	$fix( $els );

	if ( ! $changed ) {
		return false;
	}
	$backups = get_option( 'nk_home_backups', array() );
	array_unshift( $backups, array( 'time' => time(), 'post' => $home, 'data' => $raw ) );
	update_option( 'nk_home_backups', array_slice( $backups, 0, 3 ), false );
	nk_save_el_data( $home, $els );
	nk_clear_elementor_cache();
	return true;
}


/* v3.7.4b one-time: remove the Awards strip ("The most awarded in LatAm") from the existing Home (backup saved first). */

/* v3.7.4d one-time: old sample review cards (Hannah Single / Client Name) on Home become the new sample cards. */

/* v3.7.4g one-time: add the FAQ section to the existing Home, right before the Studio ("Shaped by people") section. */

/* v3.7.4i one-time: the "Shaped by people" section becomes the blog section (heading "Our Blog", latest posts as cards). */

/** Elementor data of a post plus the data of templates it embeds (NK Saved Section), for asset detection. */
function nk_elementor_data_deep( $post_id, $depth = 0 ) {
	$data = (string) get_post_meta( $post_id, '_elementor_data', true );
	if ( $depth < 3 && false !== strpos( $data, 'nk_template' ) && preg_match_all( '/"template_id":"?(\d+)/', $data, $m ) ) {
		foreach ( array_unique( $m[1] ) as $tid ) {
			$data .= nk_elementor_data_deep( (int) $tid, $depth + 1 );
		}
	}
	return $data;
}

/* Elementor Pro users: header/footer can also be built in Theme Builder (falls back to the theme's own). */
add_action( 'elementor/theme/register_locations', function ( $manager ) {
	$manager->register_all_core_location();
} );
