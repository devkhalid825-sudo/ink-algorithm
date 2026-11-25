<?php
/**
 * Post types / taxonomies that exist in the DATABASE but are not registered any more.
 *
 * The original Neuros theme (not only its plugin) can register content types such as the team. Under another theme
 * those rows stay in the database but become invisible, and widgets that query them return nothing (and the term
 * helper returns an empty list). The Tools page lists such orphans; for each one you can explicitly opt in to register it
 * with minimal arguments. Nothing is registered unless you tick it, and a type is never registered if something else
 * registered it first. No database rows are created, changed or deleted.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nkc_orphan_config() {
	$c = get_option( 'nkc_orphans', array() );
	$c = is_array( $c ) ? $c : array();
	return wp_parse_args( $c, array( 'types' => array(), 'taxes' => array() ) );
}

add_action( 'init', function () {
	$c = nkc_orphan_config();
	foreach ( $c['types'] as $name => $o ) {
		$name = sanitize_key( $name );
		if ( ! $name || post_type_exists( $name ) ) {
			continue;
		}
		$slug = isset( $o['slug'] ) ? sanitize_title( $o['slug'] ) : '';
		register_post_type( $name, array(
			'label'        => ucwords( str_replace( array( '-', '_' ), ' ', $name ) ),
			'public'       => ! empty( $o['public'] ),
			'show_ui'      => true,
			'show_in_rest' => true,
			'has_archive'  => false,
			'rewrite'      => $slug ? array( 'slug' => $slug ) : false,
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'custom-fields' ),
			'taxonomies'   => array_values( array_filter( (array) ( $o['taxes'] ?? array() ), 'sanitize_key' ) ),
		) );
	}
	foreach ( $c['taxes'] as $name => $o ) {
		$name = sanitize_key( $name );
		if ( ! $name || taxonomy_exists( $name ) ) {
			continue;
		}
		$slug = isset( $o['slug'] ) ? sanitize_title( $o['slug'] ) : '';
		register_taxonomy( $name, array_values( array_filter( (array) ( $o['types'] ?? array() ), 'post_type_exists' ) ), array(
			'label'        => ucwords( str_replace( array( '-', '_' ), ' ', $name ) ),
			'public'       => ! empty( $o['public'] ),
			'hierarchical' => true,
			'show_ui'      => true,
			'show_in_rest' => true,
			'rewrite'      => $slug ? array( 'slug' => $slug ) : false,
		) );
	}
}, 99 );

/** Read-only report: unregistered post types and taxonomies that still have rows. */
function nkc_find_orphans() {
	global $wpdb;
	$types = array();
	foreach ( (array) $wpdb->get_results( "SELECT post_type, COUNT(*) c FROM {$wpdb->posts} WHERE post_status NOT IN ('auto-draft') GROUP BY post_type" ) as $r ) { // phpcs:ignore
		if ( ! post_type_exists( $r->post_type ) ) {
			$types[ $r->post_type ] = (int) $r->c;
		}
	}
	$taxes = array();
	foreach ( (array) $wpdb->get_results( "SELECT taxonomy, COUNT(*) c FROM {$wpdb->term_taxonomy} GROUP BY taxonomy" ) as $r ) { // phpcs:ignore
		if ( ! taxonomy_exists( $r->taxonomy ) ) {
			$taxes[ $r->taxonomy ] = (int) $r->c;
		}
	}
	return array( 'types' => $types, 'taxes' => $taxes );
}
