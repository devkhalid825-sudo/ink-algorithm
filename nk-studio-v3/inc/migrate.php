<?php
/**
 * v3.6 Move to another site — copy everything you built in Elementor from one WordPress site to another
 * (e.g. test.inknalgorithm.com -> inknalgorithm.com), exactly as it is.
 *
 * Export (source site):  Appearance > NK Studio Setup > "Move to another site" > Create package
 *   one .zip with: every Elementor page + saved template + Elementor global colours/fonts (Kit) + Customizer
 *   (header / footer / logo) + menus + Work, Reviews, Social Videos + every picture / video they use.
 *   Blog posts are NOT included (unless ticked) so the real blog of the target site is never touched.
 *
 * Import (target site, same theme active):  same box > Import package (upload the zip, or paste its link)
 *   pictures/videos go into the Media Library, all links are rewritten to the new domain, pages are matched by
 *   their slug (existing ones updated, a snapshot is saved first), Home is set as the homepage.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nk_pkg_dir() {
	$u = wp_upload_dir();
	$d = trailingslashit( $u['basedir'] ) . 'nk-export';
	if ( ! is_dir( $d ) ) {
		wp_mkdir_p( $d );
		@file_put_contents( $d . '/index.html', '' ); // phpcs:ignore
	}
	return $d;
}

/* ---------------------------------------------------------------- helpers */

/** Walk any value; $fn( &$value, $key ) is called for every array (before children) and every string. */
function nk_pkg_walk( &$v, $fn, $key = null ) {
	if ( is_array( $v ) ) {
		$fn( $v, $key );
		foreach ( $v as $k => &$child ) {
			nk_pkg_walk( $child, $fn, $k );
		}
		unset( $child );
	} elseif ( is_string( $v ) ) {
		$fn( $v, $key );
	}
}

function nk_pkg_upload_urls( $s ) {
	$u    = wp_upload_dir();
	$base = preg_quote( preg_replace( '#^https?:#', '', $u['baseurl'] ), '#' );
	preg_match_all( '#(?:https?:)?' . $base . '/[^"\'\s\)\]<>]+#i', $s, $m );
	return array_unique( $m[0] );
}

/* ---------------------------------------------------------------- EXPORT */

function nk_pkg_build( $with_posts = false ) {
	if ( ! class_exists( 'ZipArchive' ) ) {
		return new WP_Error( 'nozip', __( 'This server has no ZipArchive (PHP zip extension). Ask your host to enable it.', 'nk-studio' ) );
	}
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 0 ); // phpcs:ignore
	}
	wp_raise_memory_limit( 'admin' );

	$u      = wp_upload_dir();
	$media  = array(); // old id => true
	$loose  = array(); // relative paths of files without attachment
	$collect = function ( $data ) use ( &$media, &$loose, $u ) {
		nk_pkg_walk( $data, function ( &$v ) use ( &$media, &$loose, $u ) {
			if ( is_array( $v ) ) {
				if ( isset( $v['id'], $v['url'] ) && is_numeric( $v['id'] ) && (int) $v['id'] > 0 && 'attachment' === get_post_type( (int) $v['id'] ) ) {
					$media[ (int) $v['id'] ] = true;
				}
				return;
			}
			foreach ( nk_pkg_upload_urls( $v ) as $url ) {
				$clean = preg_replace( '/-\d+x\d+(?=\.[a-z0-9]+$)/i', '', strtok( $url, '?#' ) );
				$id    = attachment_url_to_postid( $clean );
				if ( $id ) {
					$media[ $id ] = true;
				} else {
					$rel = preg_replace( '#^.*?/uploads/#', '', $clean );
					if ( $rel && file_exists( trailingslashit( $u['basedir'] ) . $rel ) ) {
						$loose[ $rel ] = true;
					}
				}
			}
		} );
	};

	$pkg = array(
		'nk_package' => 2,
		'theme'      => NK_VERSION,
		'site'       => untrailingslashit( home_url() ),
		'uploads'    => untrailingslashit( $u['baseurl'] ),
		'time'       => time(),
		'front'      => '',
		'posts'      => array(),
		'kit'        => array(),
		'theme_mods' => array(),
		'menus'      => array(),
		'media'      => array(),
	);

	$front = (int) get_option( 'page_on_front' );
	if ( $front ) {
		$pkg['front'] = get_post_field( 'post_name', $front );
	}

	/* pages / templates with Elementor content + NK post types */
	$types = array( 'page', 'elementor_library', 'nk_work', 'nk_review', 'nk_social_video', 'nk_blog' );
	if ( $with_posts ) {
		$types[] = 'post';
	}
	$kit_id = (int) get_option( 'elementor_active_kit' );
	$ids    = get_posts( array( 'post_type' => $types, 'post_status' => array( 'publish', 'draft', 'private', 'pending' ), 'numberposts' => -1, 'fields' => 'ids' ) );
	foreach ( $ids as $id ) {
		if ( $id === $kit_id ) {
			continue;
		}
		$p  = get_post( $id );
		$ed = get_post_meta( $id, '_elementor_data', true );
		if ( in_array( $p->post_type, array( 'page', 'elementor_library' ), true ) && ! $ed ) {
			continue; // only pages built with Elementor
		}
		$item = array(
			'id'        => $id,
			'type'      => $p->post_type,
			'slug'      => $p->post_name,
			'title'     => $p->post_title,
			'status'    => $p->post_status,
			'content'   => $p->post_content,
			'excerpt'   => $p->post_excerpt,
			'order'     => (int) $p->menu_order,
			'parent'    => $p->post_parent ? get_post_field( 'post_name', $p->post_parent ) : '',
			'thumb'     => (int) get_post_thumbnail_id( $id ),
			'meta'      => array(),
			'terms'     => array(),
			'elementor' => $ed ? json_decode( $ed, true ) : null,
		);
		foreach ( array( '_elementor_page_settings', '_elementor_template_type', '_elementor_edit_mode', '_wp_page_template', '_nk_home_key', '_nk_badge', '_nk_sector', '_nk_link', '_nk_video_url', '_nk_video_file', '_nk_open_url', '_nk_role', '_nk_company', '_nk_logo', '_nk_rating', '_nk_gallery' ) as $mk ) {
			$mv = get_post_meta( $id, $mk, true );
			if ( '' !== $mv && null !== $mv ) {
				$item['meta'][ $mk ] = $mv;
			}
		}
		foreach ( get_object_taxonomies( $p->post_type ) as $tax ) {
			$t = wp_get_object_terms( $id, $tax, array( 'fields' => 'all' ) );
			if ( $t && ! is_wp_error( $t ) ) {
				$item['terms'][ $tax ] = array_map( function ( $x ) { return array( 'slug' => $x->slug, 'name' => $x->name ); }, $t );
			}
		}
		foreach ( array( '_nk_logo', '_nk_video_file' ) as $mk ) {
			if ( ! empty( $item['meta'][ $mk ] ) ) {
				$media[ (int) $item['meta'][ $mk ] ] = true;
			}
		}
		if ( ! empty( $item['meta']['_nk_gallery'] ) ) {
			foreach ( array_filter( explode( ',', (string) $item['meta']['_nk_gallery'] ) ) as $gid ) {
				$media[ (int) $gid ] = true;
			}
		}
		if ( $item['thumb'] ) {
			$media[ $item['thumb'] ] = true;
		}
		$collect( $item['elementor'] );
		$collect( $item['meta'] );
		$collect( $item['content'] );
		$pkg['posts'][] = $item;
	}

	/* Elementor Site Settings (global colours, fonts, layout) */
	if ( $kit_id ) {
		$pkg['kit'] = get_post_meta( $kit_id, '_elementor_page_settings', true );
		$collect( $pkg['kit'] );
	}

	/* Customizer */
	$mods = get_option( 'theme_mods_' . get_stylesheet(), array() );
	$mods = is_array( $mods ) ? $mods : array();
	unset( $mods['nav_menu_locations'], $mods['sidebars_widgets'] );
	if ( ! empty( $mods['custom_logo'] ) ) {
		$media[ (int) $mods['custom_logo'] ] = true;
	}
	$pkg['theme_mods'] = $mods;
	$collect( $mods );

	/* menus in theme locations */
	foreach ( (array) get_nav_menu_locations() as $loc => $menu_id ) {
		$menu = $menu_id ? wp_get_nav_menu_object( $menu_id ) : null;
		if ( ! $menu ) {
			continue;
		}
		$items = array();
		foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $mi ) {
			$items[] = array(
				'key'    => (int) $mi->ID,
				'parent' => (int) $mi->menu_item_parent,
				'title'  => $mi->title,
				'type'   => $mi->type,
				'object' => $mi->object,
				'slug'   => 'post_type' === $mi->type ? get_post_field( 'post_name', (int) $mi->object_id ) : ( 'taxonomy' === $mi->type ? ( get_term( (int) $mi->object_id )->slug ?? '' ) : '' ),
				'url'    => $mi->url,
				'class'  => implode( ' ', array_filter( (array) $mi->classes ) ),
				'target' => $mi->target,
			);
		}
		$pkg['menus'][ $loc ] = array( 'name' => $menu->name, 'items' => $items );
	}

	/* files */
	$name = 'nk-design-' . gmdate( 'Ymd-His' ) . '-' . wp_generate_password( 20, false ) . '.zip';
	$path = nk_pkg_dir() . '/' . $name;
	$zip  = new ZipArchive();
	if ( true !== $zip->open( $path, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
		return new WP_Error( 'zip', __( 'Could not create the package file in the uploads folder.', 'nk-studio' ) );
	}
	foreach ( array_keys( $media ) as $mid ) {
		$file = get_attached_file( $mid );
		if ( ! $file || ! file_exists( $file ) ) {
			continue;
		}
		$rel = ltrim( str_replace( wp_normalize_path( $u['basedir'] ), '', wp_normalize_path( $file ) ), '/' );
		$zip->addFile( $file, 'media/' . $rel );
		$pkg['media'][] = array(
			'id'      => $mid,
			'rel'     => $rel,
			'url'     => wp_get_attachment_url( $mid ),
			'title'   => get_the_title( $mid ),
			'alt'     => get_post_meta( $mid, '_wp_attachment_image_alt', true ),
			'caption' => get_post_field( 'post_excerpt', $mid ),
			'mime'    => get_post_mime_type( $mid ),
		);
	}
	$pkg['loose'] = array();
	foreach ( array_keys( $loose ) as $rel ) {
		$zip->addFile( trailingslashit( $u['basedir'] ) . $rel, 'media/' . $rel );
		$pkg['loose'][] = $rel;
	}
	$zip->addFromString( 'package.json', wp_json_encode( $pkg ) );
	$zip->close();

	return array(
		'file'  => $path,
		'url'   => trailingslashit( $u['baseurl'] ) . 'nk-export/' . $name,
		'size'  => filesize( $path ),
		'pages' => count( $pkg['posts'] ),
		'media' => count( $pkg['media'] ) + count( $pkg['loose'] ),
	);
}

/* ---------------------------------------------------------------- IMPORT */

function nk_pkg_import( $zip_path, $opts = array() ) {
	$opts = wp_parse_args( $opts, array( 'home' => true, 'mods' => true, 'menus' => true ) );
	if ( ! class_exists( 'ZipArchive' ) ) {
		return new WP_Error( 'nozip', __( 'This server has no ZipArchive (PHP zip extension).', 'nk-studio' ) );
	}
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 0 ); // phpcs:ignore
	}
	wp_raise_memory_limit( 'admin' );
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$zip = new ZipArchive();
	if ( true !== $zip->open( $zip_path ) ) {
		return new WP_Error( 'open', __( 'This is not a valid package (.zip).', 'nk-studio' ) );
	}
	$pkg = json_decode( (string) $zip->getFromName( 'package.json' ), true );
	if ( empty( $pkg['nk_package'] ) ) {
		$zip->close();
		return new WP_Error( 'pkg', __( 'This zip is not an NK Studio package (package.json missing). Use "Create package" on the other site.', 'nk-studio' ) );
	}

	if ( function_exists( 'nk_auto_snapshot' ) ) {
		nk_auto_snapshot( 'before importing a package from ' . ( $pkg['site'] ?? '?' ) );
	}

	$u        = wp_upload_dir();
	$id_map   = array(); // old attachment id => new id
	$url_map  = array(); // old url (no scheme) => new url
	$old_site = preg_replace( '#^https?:#', '', (string) $pkg['site'] );
	$new_site = preg_replace( '#^https?:#', '', untrailingslashit( home_url() ) );
	$old_up   = preg_replace( '#^https?:#', '', (string) $pkg['uploads'] );
	$new_up   = preg_replace( '#^https?:#', '', untrailingslashit( $u['baseurl'] ) );
	$report   = array( 'media' => 0, 'reused' => 0, 'created' => 0, 'updated' => 0 );

	/* 1. media */
	foreach ( (array) $pkg['media'] as $m ) {
		$src_key = $pkg['site'] . '#' . $m['id'];
		$found   = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_nk_src', 'meta_value' => $src_key ) ); // phpcs:ignore
		if ( $found ) {
			$new = (int) $found[0];
			$report['reused']++;
		} else {
			$bytes = $zip->getFromName( 'media/' . $m['rel'] );
			if ( false === $bytes ) {
				continue;
			}
			$up = wp_upload_bits( basename( $m['rel'] ), null, $bytes, dirname( $m['rel'] ) !== '.' && preg_match( '#^\d{4}/\d{2}$#', dirname( $m['rel'] ) ) ? dirname( $m['rel'] ) : null );
			if ( ! empty( $up['error'] ) ) {
				continue;
			}
			$new = wp_insert_attachment( array(
				'post_mime_type' => $m['mime'],
				'post_title'     => $m['title'],
				'post_excerpt'   => $m['caption'],
				'post_status'    => 'inherit',
			), $up['file'] );
			if ( ! $new || is_wp_error( $new ) ) {
				continue;
			}
			wp_update_attachment_metadata( $new, wp_generate_attachment_metadata( $new, $up['file'] ) );
			if ( $m['alt'] ) {
				update_post_meta( $new, '_wp_attachment_image_alt', $m['alt'] );
			}
			update_post_meta( $new, '_nk_src', $src_key );
			$report['media']++;
		}
		$id_map[ (int) $m['id'] ] = $new;
		$old_url                  = preg_replace( '#^https?:#', '', $m['url'] );
		$url_map[ $old_url ]      = wp_get_attachment_url( $new );
	}
	foreach ( (array) ( $pkg['loose'] ?? array() ) as $rel ) {
		$bytes = $zip->getFromName( 'media/' . $rel );
		$dest  = trailingslashit( $u['basedir'] ) . $rel;
		if ( false !== $bytes && ! file_exists( $dest ) ) {
			wp_mkdir_p( dirname( $dest ) );
			file_put_contents( $dest, $bytes ); // phpcs:ignore
		}
	}
	$zip->close();

	/* rewrite one string: picture urls (+ their size variants) and the old domain */
	$fix_str = function ( $s ) use ( $url_map, $old_site, $new_site, $old_up, $new_up ) {
		if ( false === strpos( $s, $old_site ) && false === strpos( $s, $old_up ) ) {
			return $s;
		}
		foreach ( $url_map as $old => $new ) {
			$dot  = strrpos( $old, '.' );
			$stem = preg_quote( substr( $old, 0, $dot ), '#' );
			$ext  = preg_quote( substr( $old, $dot ), '#' );
			$s    = preg_replace( '#(?:https?:)?' . $stem . '(?:-\d+x\d+)?' . $ext . '#i', $new, $s );
		}
		$s = str_replace( array( 'https:' . $old_up, 'http:' . $old_up, $old_up ), set_url_scheme( $new_up ), $s );
		return str_replace( array( 'https:' . $old_site, 'http:' . $old_site, $old_site ), set_url_scheme( '//' . ltrim( $new_site, '/' ) ), $s );
	};
	$post_map = array(); // old post id => new post id (for template references)
	$fix      = function ( $data ) use ( $fix_str, $id_map, &$post_map ) {
		nk_pkg_walk( $data, function ( &$v, $key ) use ( $fix_str, $id_map, &$post_map ) {
			if ( is_array( $v ) ) {
				if ( isset( $v['id'], $v['url'] ) && is_numeric( $v['id'] ) && isset( $id_map[ (int) $v['id'] ] ) ) {
					$v['id']  = $id_map[ (int) $v['id'] ];
					$v['url'] = wp_get_attachment_url( $v['id'] );
				}
				foreach ( array( 'template_id', 'nk_template' ) as $tk ) {
					if ( isset( $v[ $tk ] ) && is_numeric( $v[ $tk ] ) && isset( $post_map[ (int) $v[ $tk ] ] ) ) {
						$v[ $tk ] = (string) $post_map[ (int) $v[ $tk ] ];
					}
				}
				return;
			}
			$v = $fix_str( $v );
		} );
		return $data;
	};

	/* 2. posts: templates first (pages may embed them), then the rest */
	$posts = (array) $pkg['posts'];
	usort( $posts, function ( $a, $b ) { return ( 'elementor_library' === $b['type'] ) <=> ( 'elementor_library' === $a['type'] ); } );
	$saved = array();
	foreach ( $posts as $p ) {
		$existing = 0;
		if ( 'elementor_library' === $p['type'] && ! empty( $p['meta']['_nk_home_key'] ) ) {
			$f        = get_posts( array( 'post_type' => 'elementor_library', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_nk_home_key', 'meta_value' => $p['meta']['_nk_home_key'] ) ); // phpcs:ignore
			$existing = $f ? (int) $f[0] : 0;
		}
		if ( ! $existing && $p['slug'] ) {
			$f        = get_posts( array( 'post_type' => $p['type'], 'name' => $p['slug'], 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) );
			$existing = $f ? (int) $f[0] : 0;
		}
		$args = array(
			'post_type'    => $p['type'],
			'post_title'   => $p['title'],
			'post_name'    => $p['slug'],
			'post_status'  => $p['status'],
			'post_content' => $fix_str( (string) $p['content'] ),
			'post_excerpt' => (string) $p['excerpt'],
			'menu_order'   => (int) $p['order'],
		);
		if ( $existing ) {
			$args['ID'] = $existing;
			$nid        = wp_update_post( wp_slash( $args ), true );
			$report['updated']++;
		} else {
			$nid = wp_insert_post( wp_slash( $args ), true );
			$report['created']++;
		}
		if ( is_wp_error( $nid ) || ! $nid ) {
			continue;
		}
		$post_map[ (int) $p['id'] ] = (int) $nid;
		$saved[]                    = array( $nid, $p );
	}
	foreach ( $saved as $pair ) {
		list( $nid, $p ) = $pair;
		foreach ( (array) $p['meta'] as $mk => $mv ) {
			if ( in_array( $mk, array( '_nk_logo', '_nk_video_file' ), true ) ) {
				$mv = $id_map[ (int) $mv ] ?? '';
			} elseif ( '_nk_gallery' === $mk && $mv ) {
				$g_ids = array();
				foreach ( array_filter( explode( ',', (string) $mv ) ) as $old_gid ) {
					$g_ids[] = $id_map[ (int) $old_gid ] ?? $old_gid;
				}
				$mv = implode( ',', $g_ids );
			} else {
				$mv = $fix( $mv );
			}
			if ( '' === $mv ) {
				delete_post_meta( $nid, $mk );
			} else {
				update_post_meta( $nid, $mk, is_string( $mv ) ? wp_slash( $mv ) : $mv );
			}
		}
		if ( is_array( $p['elementor'] ) ) {
			update_post_meta( $nid, '_elementor_edit_mode', 'builder' );
			update_post_meta( $nid, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.25.0' );
			update_post_meta( $nid, '_elementor_data', wp_slash( wp_json_encode( $fix( $p['elementor'] ) ) ) );
			delete_post_meta( $nid, '_elementor_css' );
			delete_post_meta( $nid, '_elementor_element_cache' );
		}
		if ( ! empty( $p['thumb'] ) && isset( $id_map[ (int) $p['thumb'] ] ) ) {
			set_post_thumbnail( $nid, $id_map[ (int) $p['thumb'] ] );
		}
		foreach ( (array) $p['terms'] as $tax => $terms ) {
			if ( ! taxonomy_exists( $tax ) ) {
				continue;
			}
			$tids = array();
			foreach ( $terms as $t ) {
				$e = term_exists( $t['slug'], $tax );
				if ( ! $e ) {
					$e = wp_insert_term( $t['name'], $tax, array( 'slug' => $t['slug'] ) );
				}
				if ( ! is_wp_error( $e ) ) {
					$tids[] = (int) ( is_array( $e ) ? $e['term_id'] : $e );
				}
			}
			wp_set_object_terms( $nid, $tids, $tax );
		}
		if ( ! empty( $p['parent'] ) ) {
			$par = get_page_by_path( $p['parent'], OBJECT, $p['type'] );
			if ( $par ) {
				wp_update_post( array( 'ID' => $nid, 'post_parent' => $par->ID ) );
			}
		}
		if ( function_exists( 'nk_release_demo_flag' ) ) {
			nk_release_demo_flag( $nid );
		}
	}

	/* the theme's own sample projects (Pomelo, Global Coalition …) are removed when real projects came in */
	if ( array_filter( $posts, function ( $p ) { return 'nk_work' === $p['type']; } ) ) {
		foreach ( get_posts( array( 'post_type' => 'nk_work', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_nk_demo_sample' ) ) as $sid ) { // phpcs:ignore
			if ( ! in_array( (int) $sid, $post_map, true ) ) {
				wp_trash_post( $sid );
			}
		}
	}

	/* 3. Elementor global colours / fonts */
	$kit_id = (int) get_option( 'elementor_active_kit' );
	if ( $kit_id && ! empty( $pkg['kit'] ) && is_array( $pkg['kit'] ) ) {
		update_post_meta( $kit_id, '_elementor_page_settings', $fix( $pkg['kit'] ) );
		delete_post_meta( $kit_id, '_elementor_css' );
	}

	/* 4. Customizer */
	if ( $opts['mods'] && ! empty( $pkg['theme_mods'] ) ) {
		$mods = $fix( $pkg['theme_mods'] );
		if ( ! empty( $mods['custom_logo'] ) ) {
			$mods['custom_logo'] = $id_map[ (int) $mods['custom_logo'] ] ?? '';
		}
		$cur = get_option( 'theme_mods_' . get_stylesheet(), array() );
		$cur = is_array( $cur ) ? $cur : array();
		update_option( 'theme_mods_' . get_stylesheet(), array_merge( $cur, $mods ) );
	}

	/* 5. menus */
	if ( $opts['menus'] && ! empty( $pkg['menus'] ) ) {
		$locs = get_nav_menu_locations();
		foreach ( $pkg['menus'] as $loc => $menu ) {
			$obj = wp_get_nav_menu_object( $menu['name'] );
			if ( $obj ) {
				foreach ( (array) wp_get_nav_menu_items( $obj->term_id, array( 'post_status' => 'any' ) ) as $old ) {
					wp_delete_post( $old->ID, true );
				}
				$mid = $obj->term_id;
			} else {
				$mid = wp_create_nav_menu( $menu['name'] );
			}
			if ( is_wp_error( $mid ) ) {
				continue;
			}
			$keymap = array();
			foreach ( $menu['items'] as $it ) {
				$args = array(
					'menu-item-title'     => $it['title'],
					'menu-item-status'    => 'publish',
					'menu-item-classes'   => $it['class'],
					'menu-item-target'    => $it['target'],
					'menu-item-parent-id' => $keymap[ $it['parent'] ] ?? 0,
				);
				$obj_id = 0;
				if ( 'post_type' === $it['type'] && $it['slug'] ) {
					$f      = get_posts( array( 'post_type' => $it['object'], 'name' => $it['slug'], 'numberposts' => 1, 'fields' => 'ids', 'post_status' => 'any' ) );
					$obj_id = $f ? (int) $f[0] : 0;
				} elseif ( 'taxonomy' === $it['type'] && $it['slug'] ) {
					$t      = get_term_by( 'slug', $it['slug'], $it['object'] );
					$obj_id = $t ? (int) $t->term_id : 0;
				}
				if ( $obj_id ) {
					$args += array( 'menu-item-type' => $it['type'], 'menu-item-object' => $it['object'], 'menu-item-object-id' => $obj_id );
				} else {
					$args += array( 'menu-item-type' => 'custom', 'menu-item-url' => $fix_str( $it['url'] ) );
				}
				$new_item               = wp_update_nav_menu_item( $mid, 0, $args );
				$keymap[ $it['key'] ]   = is_wp_error( $new_item ) ? 0 : $new_item;
			}
			$locs[ $loc ] = $mid;
		}
		set_theme_mod( 'nav_menu_locations', $locs );
	}

	/* 6. homepage */
	if ( $opts['home'] && ! empty( $pkg['front'] ) ) {
		$home = get_page_by_path( $pkg['front'] );
		if ( $home ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home->ID );
		}
	}

	/* the theme's one-time Home upgrades must never run over the imported Home */
	foreach ( array( 'nk_home_final_v3015', 'nk_v3_upgraded', 'nk_social_added_v32', 'nk_home_upgraded_v374', 'nk_awards_removed_v374', 'nk_testi_items_v374d', 'nk_faq_added_v374g', 'nk_studio_blog_v374i', 'nk_blogs_seeded_v375', 'nk_ink_hero_added', 'nk_video_added', 'nk_demo_imported' ) as $flag ) {
		if ( ! get_option( $flag ) ) {
			update_option( $flag, NK_VERSION );
		}
	}
	if ( function_exists( 'nk_clear_elementor_cache' ) ) {
		nk_clear_elementor_cache();
	}
	flush_rewrite_rules();
	return $report;
}

/* ---------------------------------------------------------------- admin actions */

add_action( 'admin_post_nk_pkg_export', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'nk_pkg' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'nk-studio' ) );
	}
	$r = nk_pkg_build( ! empty( $_POST['nk_posts'] ) );
	if ( is_wp_error( $r ) ) {
		set_transient( 'nk_pkg_msg', array( 'error', $r->get_error_message() ), 600 );
	} else {
		set_transient( 'nk_pkg_msg', array( 'export', $r ), 3600 );
	}
	wp_safe_redirect( admin_url( 'themes.php?page=nk-setup#nk-move' ) );
	exit;
} );

add_action( 'admin_post_nk_pkg_delete', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'nk_pkg' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'nk-studio' ) );
	}
	foreach ( (array) glob( nk_pkg_dir() . '/nk-design-*.zip' ) as $f ) {
		@unlink( $f ); // phpcs:ignore
	}
	set_transient( 'nk_pkg_msg', array( 'ok', __( 'Package files deleted from this site.', 'nk-studio' ) ), 600 );
	wp_safe_redirect( admin_url( 'themes.php?page=nk-setup#nk-move' ) );
	exit;
} );

add_action( 'admin_post_nk_pkg_import', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'nk_pkg' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'nk-studio' ) );
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	$tmp  = '';
	$link = isset( $_POST['nk_pkg_url'] ) ? esc_url_raw( trim( wp_unslash( $_POST['nk_pkg_url'] ) ) ) : '';
	if ( ! empty( $_FILES['nk_pkg_file']['tmp_name'] ) && is_uploaded_file( $_FILES['nk_pkg_file']['tmp_name'] ) ) { // phpcs:ignore
		$tmp = $_FILES['nk_pkg_file']['tmp_name']; // phpcs:ignore
	} elseif ( $link ) {
		$dl = download_url( $link, 1800 );
		if ( is_wp_error( $dl ) ) {
			set_transient( 'nk_pkg_msg', array( 'error', __( 'Could not download the package from that link: ', 'nk-studio' ) . $dl->get_error_message() ), 600 );
			wp_safe_redirect( admin_url( 'themes.php?page=nk-setup#nk-move' ) );
			exit;
		}
		$tmp = $dl;
	}
	if ( ! $tmp ) {
		set_transient( 'nk_pkg_msg', array( 'error', __( 'Choose the package file or paste its link. (Big file? Upload limit of this server may be too small — use the link instead.)', 'nk-studio' ) ), 600 );
	} else {
		$r = nk_pkg_import( $tmp, array( 'home' => ! empty( $_POST['nk_home'] ), 'mods' => ! empty( $_POST['nk_mods'] ), 'menus' => ! empty( $_POST['nk_menus'] ) ) );
		if ( $link ) {
			@unlink( $tmp ); // phpcs:ignore
		}
		set_transient( 'nk_pkg_msg', is_wp_error( $r ) ? array( 'error', $r->get_error_message() ) : array( 'import', $r ), 600 );
	}
	wp_safe_redirect( admin_url( 'themes.php?page=nk-setup#nk-move' ) );
	exit;
} );

/** "Move to another site" box (NK Studio Setup page). */
function nk_move_box() {
	$msg = get_transient( 'nk_pkg_msg' );
	echo '<div id="nk-move" style="background:#fff;border:1px solid #c3c4c7;border-left:4px solid #2271b1;padding:14px 18px;margin:0 0 24px;max-width:900px">';
	echo '<h2 style="margin-top:0">' . esc_html__( 'Move to another site (exact copy)', 'nk-studio' ) . '</h2>';
	if ( $msg ) {
		delete_transient( 'nk_pkg_msg' );
		if ( 'export' === $msg[0] ) {
			$r = $msg[1];
			echo '<div class="notice notice-success inline"><p><strong>' . esc_html__( 'Package ready', 'nk-studio' ) . '</strong> — ' . esc_html( sprintf( __( '%1$d pages / templates / items, %2$d pictures & videos, %3$s.', 'nk-studio' ), $r['pages'], $r['media'], size_format( $r['size'] ) ) ) . '</p>';
			echo '<p><a class="button button-primary" href="' . esc_url( $r['url'] ) . '">' . esc_html__( 'Download package (.zip)', 'nk-studio' ) . '</a></p>';
			echo '<p>' . esc_html__( 'Or copy this link and paste it on the other site (best for big files):', 'nk-studio' ) . '<br><input type="text" readonly class="large-text code" onclick="this.select()" value="' . esc_attr( $r['url'] ) . '"></p>';
			echo '<p class="description">' . esc_html__( 'The link is secret but public: delete the package below once the other site has imported it.', 'nk-studio' ) . '</p></div>';
		} elseif ( 'import' === $msg[0] ) {
			$r = $msg[1];
			echo '<div class="notice notice-success inline"><p><strong>' . esc_html__( 'Imported!', 'nk-studio' ) . '</strong> ' . esc_html( sprintf( __( '%1$d pages/items created, %2$d updated, %3$d pictures/videos added (%4$d were already here). Now: Elementor > Tools > Regenerate Files & Data, then clear your cache plugin.', 'nk-studio' ), $r['created'], $r['updated'], $r['media'], $r['reused'] ) ) . '</p></div>';
		} else {
			echo '<div class="notice notice-' . ( 'error' === $msg[0] ? 'error' : 'success' ) . ' inline"><p>' . esc_html( $msg[1] ) . '</p></div>';
		}
	}

	echo '<h3>' . esc_html__( 'A. On the site you built (e.g. test.inknalgorithm.com)', 'nk-studio' ) . '</h3>';
	echo '<p>' . esc_html__( 'Packs every Elementor page and template, Elementor colours & fonts, header / footer / logo (Customizer), menus, Work, Reviews, Social Videos and all their pictures and videos.', 'nk-studio' ) . '</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block;margin:0 10px 10px 0">';
	wp_nonce_field( 'nk_pkg' );
	echo '<input type="hidden" name="action" value="nk_pkg_export">';
	echo '<label style="display:block;margin-bottom:8px"><input type="checkbox" name="nk_posts" value="1"> ' . esc_html__( 'also blog posts (leave OFF when the other site has its own real blog)', 'nk-studio' ) . '</label>';
	echo '<button class="button button-primary">' . esc_html__( 'Create package', 'nk-studio' ) . '</button></form>';
	if ( glob( nk_pkg_dir() . '/nk-design-*.zip' ) ) {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block">';
		wp_nonce_field( 'nk_pkg' );
		echo '<input type="hidden" name="action" value="nk_pkg_delete"><button class="button">' . esc_html__( 'Delete package files', 'nk-studio' ) . '</button></form>';
	}

	echo '<h3>' . esc_html__( 'B. On the other site (e.g. inknalgorithm.com) — same NK Studio theme active', 'nk-studio' ) . '</h3>';
	echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" onsubmit="return confirm(\'' . esc_js( __( 'Import this package? Pages with the same address (slug) are replaced by the imported ones. A snapshot of this site is saved first.', 'nk-studio' ) ) . '\')">';
	wp_nonce_field( 'nk_pkg' );
	echo '<input type="hidden" name="action" value="nk_pkg_import">';
	echo '<p>' . esc_html__( 'Package file:', 'nk-studio' ) . ' <input type="file" name="nk_pkg_file" accept=".zip"> &nbsp; ' . esc_html__( 'or package link:', 'nk-studio' ) . ' <input type="url" name="nk_pkg_url" class="regular-text" placeholder="https://test…/wp-content/uploads/nk-export/nk-design-….zip"></p>';
	echo '<p><label><input type="checkbox" name="nk_home" value="1" checked> ' . esc_html__( 'make the imported Home the homepage', 'nk-studio' ) . '</label> &nbsp; ';
	echo '<label><input type="checkbox" name="nk_mods" value="1" checked> ' . esc_html__( 'header / footer / logo (Customizer)', 'nk-studio' ) . '</label> &nbsp; ';
	echo '<label><input type="checkbox" name="nk_menus" value="1" checked> ' . esc_html__( 'menus', 'nk-studio' ) . '</label></p>';
	echo '<p><button class="button button-primary">' . esc_html__( 'Import package', 'nk-studio' ) . '</button></p>';
	echo '<p class="description">' . esc_html__( 'Blog posts, comments and users of this site are not touched (unless the package contains posts). Install the same plugins (Elementor etc.) here first.', 'nk-studio' ) . '</p>';
	echo '</form></div>';
}

/* sites set up before v3.6: mark the theme's sample projects so an import can clear them */
add_action( 'admin_init', function () {
	if ( get_option( 'nk_samples_marked_v36' ) || ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	update_option( 'nk_samples_marked_v36', 1 );
	$samples = array( 'Pomelo' => 'A digital foundation built to grow with Pomelo.', 'Global Coalition' => 'Making space for meaningful connections.', 'Project Three' => 'Replace with your own project description.', 'Project Four' => 'Replace with your own project description.' );
	foreach ( get_posts( array( 'post_type' => 'nk_work', 'numberposts' => -1, 'post_status' => 'any' ) ) as $p ) {
		if ( isset( $samples[ $p->post_title ] ) && $samples[ $p->post_title ] === $p->post_excerpt && ! has_post_thumbnail( $p ) ) {
			update_post_meta( $p->ID, '_nk_demo_sample', 1 );
		}
	}
} );

/* =====================================================================
   v3.7 ONE-ZIP MIGRATION
   Test site:  NK Studio Setup > "Download theme + my content"  ->  one theme zip that carries everything
               (theme files + content/design.zip with all Elementor pages, templates, pictures, videos, header /
               footer, menus, Work, Reviews, Social Videos).
   Live site:  Appearance > Themes > Upload that zip > Activate (or "Replace current with uploaded").
               On the next admin page the bundled design is imported automatically, once per package.
   The same zip uploaded back on the site that made it does nothing (your newer edits there are never overwritten).
   ===================================================================== */

function nk_bundle_path() {
	return NK_DIR . '/content/design.zip';
}

/** Build "theme + content" zip. Returns array( url, size ) or WP_Error. */
function nk_build_theme_bundle( $with_posts = false ) {
	$pkg = nk_pkg_build( $with_posts );
	if ( is_wp_error( $pkg ) ) {
		return $pkg;
	}
	$u    = wp_upload_dir();
	$name = NK_FOLDER . '-with-content-' . gmdate( 'Ymd-His' ) . '-' . wp_generate_password( 16, false ) . '.zip';
	$out  = nk_pkg_dir() . '/' . $name;
	$zip  = new ZipArchive();
	if ( true !== $zip->open( $out, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
		return new WP_Error( 'zip', __( 'Could not create the zip in the uploads folder.', 'nk-studio' ) );
	}
	$root = wp_normalize_path( untrailingslashit( get_stylesheet_directory() ) );
	$it   = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $it as $f ) {
		$path = wp_normalize_path( $f->getPathname() );
		$rel  = ltrim( substr( $path, strlen( $root ) ), '/' );
		if ( '' === $rel || 0 === strpos( $rel, 'content/' ) || 0 === strpos( $rel, '.git' ) || $f->isDir() ) {
			continue;
		}
		$zip->addFile( $path, NK_FOLDER . '/' . $rel );
	}
	$zip->addFile( $pkg['file'], NK_FOLDER . '/content/design.zip' );
	$zip->addFromString( NK_FOLDER . '/content/README.txt', "Design package from " . home_url() . " (" . gmdate( 'Y-m-d H:i' ) . " UTC).\nUpload this theme zip on the live site and activate it: the design is imported automatically once.\n" );
	$zip->close();
	@unlink( $pkg['file'] ); // phpcs:ignore — only the bundle is kept
	return array(
		'url'   => trailingslashit( $u['baseurl'] ) . 'nk-export/' . $name,
		'size'  => filesize( $out ),
		'pages' => $pkg['pages'],
		'media' => $pkg['media'],
	);
}

add_action( 'admin_post_nk_bundle_export', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'nk_pkg' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'nk-studio' ) );
	}
	$r = nk_build_theme_bundle( ! empty( $_POST['nk_posts'] ) );
	set_transient( 'nk_bundle_msg', is_wp_error( $r ) ? array( 'error', $r->get_error_message() ) : array( 'ok', $r ), 3600 );
	wp_safe_redirect( admin_url( 'themes.php?page=nk-setup' ) );
	exit;
} );

/** Package site address (without opening the whole import). */
function nk_bundle_source_site( $file ) {
	if ( ! class_exists( 'ZipArchive' ) ) {
		return '';
	}
	$z = new ZipArchive();
	if ( true !== $z->open( $file ) ) {
		return '';
	}
	$p = json_decode( (string) $z->getFromName( 'package.json' ), true );
	$z->close();
	return is_array( $p ) ? (string) ( $p['site'] ?? '' ) : '';
}

/* live site: import the bundled design automatically, once per package (before any one-time Home upgrade) */
add_action( 'admin_init', function () {
	$file = nk_bundle_path();
	if ( ! file_exists( $file ) || ! current_user_can( 'manage_options' ) || wp_doing_ajax() ) {
		return;
	}
	$hash = md5_file( $file );
	if ( get_option( 'nk_bundle_imported' ) === $hash ) {
		return;
	}
	update_option( 'nk_bundle_imported', $hash ); // set first: a failed / slow import never loops
	$src  = nk_bundle_source_site( $file );
	$here = untrailingslashit( home_url() );
	if ( ! $src || preg_replace( '#^https?:#', '', $src ) === preg_replace( '#^https?:#', '', $here ) ) {
		return; // uploaded back on the site that made it: keep your newer edits
	}
	$r = nk_pkg_import( $file, array( 'home' => true, 'mods' => true, 'menus' => true ) );
	set_transient( 'nk_bundle_done', is_wp_error( $r ) ? array( 'error', $r->get_error_message() ) : array( 'ok', $r, $src ), DAY_IN_SECONDS );
}, 5 );

add_action( 'admin_notices', function () {
	$d = get_transient( 'nk_bundle_done' );
	if ( ! $d || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	delete_transient( 'nk_bundle_done' );
	if ( 'error' === $d[0] ) {
		echo '<div class="notice notice-error"><p><strong>NK Studio:</strong> ' . esc_html__( 'The design inside this theme could not be imported: ', 'nk-studio' ) . esc_html( $d[1] ) . '</p></div>';
		return;
	}
	$r = $d[1];
	echo '<div class="notice notice-success is-dismissible"><p><strong>NK Studio:</strong> ' . esc_html( sprintf( __( 'Your design from %1$s is now on this site — %2$d pages/items created, %3$d updated, %4$d pictures/videos added. Open the site to check it (clear your cache plugin if you use one).', 'nk-studio' ), $d[2], $r['created'], $r['updated'], $r['media'] ) ) . ' <a href="' . esc_url( home_url( '/' ) ) . '" target="_blank">' . esc_html__( 'View site', 'nk-studio' ) . '</a></p></div>';
} );

/** Big simple box on top of NK Studio Setup. */
function nk_bundle_box() {
	$msg = get_transient( 'nk_bundle_msg' );
	echo '<div style="background:#fff;border:1px solid #c3c4c7;border-left:4px solid #2271b1;padding:16px 20px;margin:16px 0 24px;max-width:900px">';
	echo '<h2 style="margin-top:0">' . esc_html__( 'Move this site to the live domain — one zip', 'nk-studio' ) . '</h2>';
	if ( $msg ) {
		delete_transient( 'nk_bundle_msg' );
		if ( 'error' === $msg[0] ) {
			echo '<div class="notice notice-error inline"><p>' . esc_html( $msg[1] ) . '</p></div>';
		} else {
			$r = $msg[1];
			echo '<div class="notice notice-success inline"><p><strong>' . esc_html__( 'Ready!', 'nk-studio' ) . '</strong> ' . esc_html( sprintf( __( '%1$d pages/items, %2$d pictures & videos, %3$s.', 'nk-studio' ), $r['pages'], $r['media'], size_format( $r['size'] ) ) ) . '</p>';
			echo '<p><a class="button button-primary button-hero" href="' . esc_url( $r['url'] ) . '">' . esc_html__( 'Download theme + my content (.zip)', 'nk-studio' ) . '</a></p></div>';
		}
	}
	echo '<ol style="margin-left:18px;font-size:14px;line-height:1.7">';
	echo '<li>' . wp_kses_post( __( 'Here (test site): click <strong>Create zip</strong>, then <strong>Download</strong>.', 'nk-studio' ) ) . '</li>';
	echo '<li>' . wp_kses_post( __( 'Live site: Appearance &gt; Themes &gt; Add New &gt; Upload Theme &gt; this zip &gt; <strong>Install</strong> &gt; <strong>Activate</strong> (if NK Studio is already there: <strong>Replace current with uploaded</strong>).', 'nk-studio' ) ) . '</li>';
	echo '<li>' . wp_kses_post( __( 'Done — every Elementor page, picture, video, header / footer and menu appears on the live site. Its blog posts stay as they are.', 'nk-studio' ) ) . '</li>';
	echo '</ol>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'nk_pkg' );
	echo '<input type="hidden" name="action" value="nk_bundle_export">';
	echo '<p><button class="button button-primary button-hero">' . esc_html__( 'Create zip (theme + my content)', 'nk-studio' ) . '</button></p>';
	echo '<p><label><input type="checkbox" name="nk_posts" value="1"> ' . esc_html__( 'also include the blog posts of this site (leave OFF — the live site keeps its own blog)', 'nk-studio' ) . '</label></p>';
	echo '<p class="description">' . esc_html__( 'Install Elementor (and the same plugins) on the live site first. Before importing, the live site saves a snapshot (Restore under "Safe theme updates").', 'nk-studio' ) . '</p>';
	echo '</form></div>';
}
