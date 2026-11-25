<?php
/**
 * v3.9.0 — the /blogs/ page in the NK dark theme.
 *
 * The page itself (Elementor page "Blogs") is NOT modified or rewritten: it keeps its slug, title, SEO data and content in
 * the database. On the site the theme prints a native listing instead of the old light Neuros archive (rounded banner,
 * outline "Blogs" text, AJAX grid): NK header, mesh section, dark post cards, category pills, pagination, NK footer.
 * Inside the Elementor editor / preview the page is untouched, so it stays editable. Turn off with
 * Customize > NK General > "Blogs page in the NK design".
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Slugs of the pages shown with the NK blog template. */
function nk_blogs_slugs() {
	return (array) apply_filters( 'nk_blogs_slugs', array( 'blogs' ) );
}

function nk_is_blogs_template() {
	static $r = null;
	if ( null !== $r ) {
		return $r;
	}
	$r = false;
	if ( is_admin() || ! is_page() || ! get_theme_mod( 'nk_blogs_template', true ) ) {
		return $r;
	}
	if ( isset( $_GET['elementor-preview'] ) || isset( $_GET['elementor_library'] ) || ( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return $r;
	}
	$page = get_queried_object();
	$r    = $page && in_array( $page->post_name, nk_blogs_slugs(), true );
	return $r;
}

add_filter( 'template_include', function ( $tpl ) {
	return nk_is_blogs_template() ? NK_DIR . '/templates/blog-archive.php' : $tpl;
}, 99 );

add_filter( 'body_class', function ( $c ) {
	if ( nk_is_blogs_template() ) {
		$c[] = 'nk-blogs-page';
	}
	return $c;
} );

/* The /blogs/ page holds an original Neuros widget, so it would count as an "original inner page" (white canvas, old assets).
   Its own NK template is not one: no legacy canvas, and the compat plugin does not load the old theme's CSS/JS for it. */
add_filter( 'nk_is_legacy_page', function ( $hit, $post_id ) {
	return nk_is_blogs_template() && (int) get_queried_object_id() === (int) $post_id ? false : $hit;
}, 10, 2 );
add_filter( 'nkc_load_legacy_assets', function ( $load ) {
	return nk_is_blogs_template() ? false : $load;
} );

/** Reading time in minutes. */
function nk_reading_minutes( $post_id ) {
	return max( 1, (int) ceil( str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) ) / 200 ) );
}

/** One post card (solid panel: the page mesh never shows over it). $featured = wide card for the first article. */
function nk_blog_card( $post_id, $featured = false ) {
	$href  = get_permalink( $post_id );
	$title = get_the_title( $post_id );
	$cats  = get_the_category( $post_id );
	$badge = $cats ? $cats[0]->name : '';
	$img   = get_the_post_thumbnail_url( $post_id, $featured ? 'full' : 'large' );
	if ( ! $img && preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', (string) get_post_field( 'post_content', $post_id ), $m ) ) {
		$img = $m[1];
	}
	$ex = wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post_id ) ), $featured ? 42 : 26 );
	$h  = '<article class="nk-bcard' . ( $featured ? ' nk-bcard--featured' : '' ) . '">';
	$h .= '<a class="nk-bcard__media" href="' . esc_url( $href ) . '" aria-label="' . esc_attr( $title ) . '">';
	if ( $img ) {
		$h .= '<img src="' . esc_url( $img ) . '" alt="' . esc_attr( $title ) . '" loading="lazy">';
	}
	if ( $badge ) {
		$h .= '<span class="nk-bcard__chip">' . esc_html( $badge ) . '</span>';
	}
	$h .= '</a><div class="nk-bcard__body">';
	$h .= '<div class="nk-bcard__meta"><span>' . esc_html( get_the_date( 'M j, Y', $post_id ) ) . '</span><i aria-hidden="true"></i><span>' . esc_html( sprintf( /* translators: %d: minutes */ __( '%d min read', 'nk-studio' ), nk_reading_minutes( $post_id ) ) ) . '</span></div>';
	$h .= '<h3 class="nk-bcard__title"><a href="' . esc_url( $href ) . '">' . esc_html( $title ) . '</a></h3>';
	if ( $ex ) {
		$h .= '<p class="nk-bcard__excerpt">' . esc_html( $ex ) . '</p>';
	}
	$h .= '<a class="nk-bcard__more" href="' . esc_url( $href ) . '">' . esc_html__( 'Read article', 'nk-studio' ) . '<span aria-hidden="true">→</span></a></div></article>';
	return $h;
}
