<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Customizer value with default. */
function nk_opt( $key, $default = '' ) {
	$v = get_theme_mod( $key, null );
	if ( null !== $v ) {
		return $v;
	}
	if ( '' !== $default ) {
		return $default;
	}
	// v3.8.3: Customizer defaults were only used inside the Customizer; on the site an unsaved option was empty
	$d = function_exists( 'nk_customizer_defaults' ) ? nk_customizer_defaults() : array();
	return $d[ $key ] ?? $default;
}

/**
 * Header brand text. Old saved defaults ("Ink Algorithm", "nk", …) are upgraded to INKNALGORITHM.
 * Edit in Customize > NK General > Text logo.
 */
function nk_brand_text() {
	$t   = trim( (string) get_theme_mod( 'nk_logo_text', 'INKN ALGORITHM' ) );
	$old = array( '', 'nk', 'ink algorithm', 'inkalgorithm', 'ink & algorithm', 'ink &amp; algorithm', 'ink n algorithm', 'inknalgorithm' );
	if ( in_array( strtolower( $t ), $old, true ) ) {
		$t = 'INKN ALGORITHM';
	}
	return get_theme_mod( 'nk_logo_upper', true ) ? strtoupper( $t ) : $t;
}

/**
 * v3.8.3: header and footer show the TEXT brand (Customize > NK General > Text logo). A WordPress "custom logo" image
 * (kept from the old site) or the footer 3D mark is only used when this box is ticked: Customize > NK General.
 */
function nk_use_logo_images() {
	return (bool) get_theme_mod( 'nk_use_logo_images', false );
}

/** Site logo: text brand, or the uploaded custom logo when "use logo images" is on. */
function nk_logo( $class = 'nk-logo' ) {
	$out = '<a class="' . esc_attr( $class ) . '" href="' . esc_url( home_url( '/' ) ) . '" aria-label="' . esc_attr( get_bloginfo( 'name' ) ) . '">';
	if ( nk_use_logo_images() && has_custom_logo() ) {
		$id  = get_theme_mod( 'custom_logo' );
		$out .= wp_get_attachment_image( $id, 'full', false, array( 'class' => 'nk-logo__img' ) );
	} else {
		$out .= '<span class="nk-logo__mark">' . esc_html( nk_brand_text() ) . '</span>';
	}
	return $out . '</a>';
}

/** Headline helper: strong + muted part + accent dot. */
function nk_headline( $strong, $muted = '', $dot = true, $tag = 'h2', $class = '' ) {
	$tag = in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p', 'span' ), true ) ? $tag : 'h2';
	$h   = '<' . $tag . ' class="nk-headline ' . esc_attr( $class ) . '">';
	$h  .= '<span class="nk-headline__strong">' . wp_kses_post( $strong ) . ( $dot ? '<b class="nk-dot">.</b>' : '' ) . '</span>';
	if ( $muted ) {
		$h .= ' <span class="nk-headline__muted">' . wp_kses_post( $muted ) . '</span>';
	}
	return $h . '</' . $tag . '>';
}

/** Underlined arrow link. */
function nk_link( $text, $url = '#', $arrow = true, $extra = '' ) {
	if ( is_array( $url ) ) {
		$attrs = ' href="' . esc_url( $url['url'] ?? '#' ) . '"';
		if ( ! empty( $url['is_external'] ) ) {
			$attrs .= ' target="_blank"';
		}
		$attrs .= ! empty( $url['nofollow'] ) ? ' rel="nofollow noopener"' : ( ! empty( $url['is_external'] ) ? ' rel="noopener"' : '' );
	} else {
		$attrs = ' href="' . esc_url( $url ) . '"';
	}
	$svg = $arrow ? '<svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true"><path d="M2 8h11M9 3.5 13.5 8 9 12.5" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>' : '';
	return '<a class="nk-link ' . esc_attr( $extra ) . '"' . $attrs . '><span>' . esc_html( $text ) . '</span>' . $svg . '</a>';
}

/** Social icon SVGs (compact). */
function nk_social_icon( $key ) {
	$icons = array(
		'instagram' => '<path d="M7 3h10a4 4 0 0 1 4 4v10a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V7a4 4 0 0 1 4-4Zm5 5a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm5.5-2a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z"/>',
		'linkedin'  => '<path d="M4 9h4v11H4V9Zm2-5a2.2 2.2 0 1 1 0 4.4A2.2 2.2 0 0 1 6 4Zm4 5h3.8v1.6c.6-1 1.9-1.9 3.8-1.9 3.6 0 4.4 2.3 4.4 5.4V20h-4v-5.2c0-1.3 0-2.9-1.8-2.9s-2.1 1.4-2.1 2.8V20h-4V9Z"/>',
		'x'         => '<path d="M17.8 3h3l-6.6 7.6L22 21h-6.1l-4.8-6.3L5.6 21h-3l7-8.1L2 3h6.2l4.3 5.8L17.8 3Zm-1 16.2h1.7L7.3 4.7H5.5l11.3 14.5Z"/>',
		'behance'   => '<path d="M3 6h6c2.2 0 3.5 1.1 3.5 3 0 1.2-.6 2.1-1.6 2.5 1.4.4 2.2 1.5 2.2 3 0 2.2-1.6 3.5-4 3.5H3V6Zm2.6 4.6h2.7c.9 0 1.4-.4 1.4-1.2S9.200 8.200 8.300 8.200H5.600v2.400Zm0 5.200h3c1 0 1.600-.5 1.600-1.400s-.6-1.400-1.600-1.400h-3v2.800ZM15 6.800h5.500v1.400H15V6.800Zm2.900 3.400c2.400 0 3.900 1.700 3.900 4.200v.6h-5.300c.1 1.100.9 1.700 2 1.700.8 0 1.400-.3 1.700-.9h1.500c-.4 1.500-1.700 2.400-3.300 2.400-2.300 0-3.800-1.600-3.800-3.900 0-2.400 1.500-4.100 3.300-4.100Zm-1.800 3.200h3.600c-.1-1-.7-1.700-1.800-1.700-1 0-1.700.6-1.800 1.700Z"/>',
		'facebook'  => '<path d="M14 8V6.200c0-.7.200-1.200 1.200-1.200H17V2h-2.700C11.500 2 10 3.600 10 6v2H7.500v3H10v11h4V11h2.700l.3-3H14Z"/>',
		'youtube'   => '<path d="M21.600 7.200a2.500 2.500 0 0 0-1.800-1.800C18.200 5 12 5 12 5s-6.200 0-7.800.4A2.500 2.500 0 0 0 2.400 7.200C2 8.800 2 12 2 12s0 3.200.4 4.800a2.500 2.500 0 0 0 1.800 1.800C5.800 19 12 19 12 19s6.200 0 7.800-.4a2.500 2.500 0 0 0 1.800-1.800c.4-1.600.4-4.800.4-4.800s0-3.200-.4-4.800ZM10 15V9l5.200 3L10 15Z"/>',
		'web'       => '<path d="M3 5h3l2 9 2.500-9H13l2.500 9L17.500 5H21l-3.700 14H14l-2-7.500L10 19H6.700L3 5Z"/>',
	);
	if ( ! isset( $icons[ $key ] ) ) {
		return '';
	}
	return '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">' . $icons[ $key ] . '</svg>';
}

/** Whether current page is built with Elementor. */
function nk_is_elementor_page( $id = 0 ) {
	$id = $id ?: get_the_ID();
	return $id && get_post_meta( $id, '_elementor_edit_mode', true ) === 'builder';
}

/** Card used by archives, Work grid and Insights widgets. */
function nk_render_post_card( $post_id, $args = array() ) {
	$args  = wp_parse_args( $args, array( 'badge' => '', 'caption_sector' => true, 'ratio' => '', 'video_mode' => 'click' ) );
	$type  = get_post_type( $post_id );
	$badge = $args['badge'];
	if ( ! $badge ) {
		if ( 'nk_work' === $type ) {
			$badge = get_post_meta( $post_id, '_nk_badge', true );
		} else {
			$cats  = get_the_category( $post_id );
			$badge = $cats ? $cats[0]->name : '';
		}
	}
	$sector = 'nk_work' === $type ? get_post_meta( $post_id, '_nk_sector', true ) : '';
	$title  = get_the_title( $post_id );
	$desc   = 'nk_work' === $type ? get_the_excerpt( $post_id ) : '';
	// v3.2: a project can open an external link (Work > Edit > Card details > External link)
	$ext    = 'nk_work' === $type ? (string) get_post_meta( $post_id, '_nk_link', true ) : '';
	$href   = $ext ? $ext : get_permalink( $post_id );
	$tgt    = $ext ? ' target="_blank" rel="noopener"' : '';
	$video  = function_exists( 'nk_post_card_video' ) ? nk_post_card_video( $post_id ) : null;
	?>
	<article class="nk-card <?php echo esc_attr( $args['ratio'] ); ?><?php echo $video ? ' has-video' : ''; ?>">
		<?php if ( $video ) : // v3.5: card video (post / project edit screen > Card video) ?>
			<?php echo nk_card_video_media( $video, (string) get_the_post_thumbnail_url( $post_id, 'large' ), $badge, $args['video_mode'], $title ); // phpcs:ignore ?>
		<?php else : ?>
		<a class="nk-card__media" href="<?php echo esc_url( $href ); ?>"<?php echo $tgt; // phpcs:ignore ?>>
			<?php
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, 'large', array( 'loading' => 'lazy' ) ); // phpcs:ignore
			}
			if ( $badge ) {
				echo '<span class="nk-badge">' . esc_html( strtoupper( $badge ) ) . '</span>';
			}
			?>
		</a>
		<?php endif; ?>
		<div class="nk-card__body">
			<?php if ( 'nk_work' === $type ) : ?>
				<h3 class="nk-card__title"><a href="<?php echo esc_url( $href ); ?>"<?php echo $tgt; // phpcs:ignore ?>><?php echo esc_html( $title ); ?></a><?php if ( $sector && $args['caption_sector'] ) : ?> <span class="nk-card__sector">(<?php echo esc_html( strtoupper( $sector ) ); ?>)</span><?php endif; ?></h3>
				<?php if ( $desc ) : ?><p class="nk-card__desc"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
			<?php else : ?>
				<h3 class="nk-card__title nk-card__title--plain"><a href="<?php echo esc_url( $href ); ?>"<?php echo $tgt; // phpcs:ignore ?>><?php echo esc_html( $title ); ?></a></h3>
			<?php endif; ?>
		</div>
	</article>
	<?php
}

/** Wireframe 3D-looking slash (pure SVG, colour = --nk-accent). */
function nk_slash_svg() {
	$lines = '';
	$n     = 16;
	for ( $i = 1; $i < $n; $i++ ) {
		$f      = $i / $n;
		$lines .= sprintf( '<path d="M%.1f %.1f L%.1f %.1f"/>', 90 - 80 * $f, 8 + 452 * $f, 152 - 80 * $f, 452 * $f );
	}
	return '<svg class="nk-slash" viewBox="0 0 160 460" aria-hidden="true" preserveAspectRatio="xMidYMid meet">'
		. '<g fill="none" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round">'
		. '<path d="M90 8 L152 0 L72 452 L10 460 Z" fill="currentColor" fill-opacity=".12"/>'
		. '<path d="M121 4 L41 456"/>'
		. $lines
		. '</g></svg>';
}

/**
 * Primary navigation fallback (used only while no menu is assigned to "Primary").
 * Same five items as before, but each points at the page that REALLY exists on the site
 * (portfolio / our-services / review / faq …), not at a demo slug.
 */
function nk_primary_nav_fallback() {
	$is_home = is_front_page() || is_home();
	$items   = array(
		array( 'home', 'Home', home_url( '/' ), $is_home, '' ),
		array( 'work', 'Work', null, false, array( 'portfolio', 'work' ) ),
		array( 'services', 'Services', null, false, array( 'our-services', 'services' ) ),
		array( 'review', 'Review', null, false, array( 'review', 'reviews', 'testimonials' ) ),
		array( 'faq', 'FAQ', null, false, array( 'faq', 'faqs' ) ),
	);
	echo '<ul id="nk-primary-menu" class="nk-menu">';
	foreach ( $items as $it ) {
		list( $key, $label, $url, $current, $slugs ) = $it;
		if ( null === $url ) {
			$url = home_url( '/#' . $key );
			foreach ( $slugs as $slug ) {
				$p = get_page_by_path( $slug, OBJECT, 'page' );
				if ( $p ) {
					$url     = get_permalink( $p );
					$current = is_page( $p->ID );
					break;
				}
			}
		}
		echo '<li class="menu-item menu-item-' . esc_attr( $key ) . ( 'services' === $key ? ' menu-item-has-children' : '' ) . ( $current ? ' current-menu-item' : '' ) . '"><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
	}
	echo '</ul>';
}


/** Social links of the footer: Customizer value, else the company profiles. */
function nk_social_url( $key ) {
	$defaults = apply_filters( 'nk_social_defaults', array(
		'facebook'  => 'https://www.facebook.com/profile.php?id=61561005684253',
		'linkedin'  => 'https://www.linkedin.com/company/inknalgorithm',
		'youtube'   => 'https://www.youtube.com/channel/UCO9FivXzum2uSepy3I7gntQ',
		'instagram' => 'https://www.instagram.com/inknalgorithm/',
		'behance'   => 'https://www.behance.net/ink_algorithm',
	) );
	$v = get_theme_mod( 'nk_social_' . $key, '' );
	return '' !== (string) $v ? (string) $v : ( $defaults[ $key ] ?? '' );
}

/**
 * Footer link columns: Get in Touch / Our Location / Sitemap / Quick Links.
 * A footer menu assigned to Appearance > Menus > footer_1..5 always wins; the defaults below are only used where no menu
 * is assigned, and point at pages that really exist on this site (nk_resolve_url).
 */
function nk_footer_columns() {
	$phone = trim( (string) get_theme_mod( 'nk_footer_phone', '+1 630-297-0428' ) );
	$mail  = trim( (string) get_theme_mod( 'nk_footer_email', 'info@inknalgorithm.com' ) );
	$addr  = trim( (string) get_theme_mod( 'nk_footer_address', '1812 McCormick Ln, Hanover Park, IL 60133' ) );
	$page  = function ( $slug, $label ) {
		$p = get_page_by_path( $slug, OBJECT, 'page' );
		return $p ? array( $label, get_permalink( $p ) ) : null;
	};
	$sitemap = array_filter( array(
		array( 'Home', home_url( '/' ) ),
		$page( 'portfolio', 'Work' ),
		$page( 'our-services', 'Services' ),
		$page( 'review', 'Review' ),
		array( 'FAQ', home_url( '/#faq' ) ),
		array( 'Book Consultation', 'https://calendly.com/bilal-lania-elipsestudio/15-mins-meeting' ),
	) );
	$quick = array_filter( array(
		$page( 'virtual-reality-vr', 'Virtual Reality Services in USA' ),
		$page( 'privacy-policy', 'Privacy Policy' ),
		$page( 'terms-conditions', 'Terms & Conditions' ),
	) );
	return apply_filters( 'nk_footer_columns', array(
		'touch'    => array( 'title' => 'Get in Touch', 'phone' => $phone, 'email' => $mail ),
		'location' => array( 'title' => 'Our Location', 'text' => $addr ),
		'sitemap'  => array( 'title' => 'Sitemap', 'links' => array_values( $sitemap ) ),
		'quick'    => array( 'title' => 'Quick Links', 'links' => array_values( $quick ) ),
	) );
}
