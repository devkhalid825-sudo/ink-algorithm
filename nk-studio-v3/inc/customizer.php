<?php
/**
 * Customizer: everything in the header and footer is editable here
 * (Appearance > Customize > NK Header / NK Footer) plus menus.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nk_customizer_defaults() {
	return array(
		'nk_accent'          => '#ff6a1a',
		'nk_logo_text'       => 'INKNALGORITHM',
		'nk_header_cta_text' => 'Get Started',
		'nk_header_cta_url'  => '/contact/',
		'nk_menu_text'       => 'MENU',
		'nk_footer_big_logo' => 'nk',
		'nk_footer_headline' => 'Move the Needle.',
		'nk_footer_muted'    => 'We partner with ambitious organizations at the moments that define what’s next.',
		'nk_footer_text'     => '',
		'nk_footer_email'    => 'info@inknalgorithm.com',
		'nk_footer_phone'    => '+1 630-297-0428',
		'nk_footer_address'  => '1812 McCormick Ln, Hanover Park, IL 60133',
		'nk_news_logo'       => 'nk.news',
		'nk_news_text'       => 'Every two weeks, we take you behind the scenes. Around 1,000 words on creativity, business and what we’re learning through our work.',
		'nk_news_placeholder'=> 'YOUR EMAIL HERE',
		'nk_news_action'     => '',
		'nk_news_note'       => 'We’ll only email you about /nk.news. Read our Privacy Policy. Unsubscribe at any time.',
		'nk_members_text'    => '',
		'nk_copyright'       => '© Ink & Algorithm ' . gmdate( 'Y' ) . '. All rights reserved.',
		'nk_lang_label'      => '',
	);
}

add_action( 'customize_register', function ( $wp_customize ) {
	$d = nk_customizer_defaults();

	$text = function ( $section, $key, $label, $type = 'text', $sanitize = 'sanitize_text_field' ) use ( $wp_customize, $d ) {
		$wp_customize->add_setting( $key, array(
			'default'           => $d[ $key ] ?? '',
			'sanitize_callback' => $sanitize,
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( $key, array( 'label' => $label, 'section' => $section, 'type' => $type ) );
	};

	/* ---- General ---- */
	$wp_customize->add_section( 'nk_general', array( 'title' => __( 'NK General', 'nk-studio' ), 'priority' => 30 ) );
	$wp_customize->add_setting( 'nk_accent', array( 'default' => $d['nk_accent'], 'sanitize_callback' => 'sanitize_hex_color' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'nk_accent', array( 'label' => __( 'Accent colour', 'nk-studio' ), 'section' => 'nk_general' ) ) );
	$text( 'nk_general', 'nk_logo_text', __( 'Text logo (used when no logo image is uploaded)', 'nk-studio' ) );
	$wp_customize->add_setting( 'nk_mesh_bg', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'nk_mesh_bg', array( 'label' => __( 'Hero mesh background (fine network lines + perspective grid floor)', 'nk-studio' ), 'section' => 'nk_general', 'type' => 'checkbox' ) );
	$wp_customize->add_setting( 'nk_blogs_template', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'nk_blogs_template', array( 'label' => __( 'Blogs page in the NK design (instead of the old Neuros blog listing)', 'nk-studio' ), 'section' => 'nk_general', 'type' => 'checkbox' ) );
	$wp_customize->add_setting( 'nk_use_logo_images', array( 'default' => false, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'nk_use_logo_images', array( 'label' => __( 'Use logo IMAGES (site logo / footer 3D mark) instead of the text logo', 'nk-studio' ), 'section' => 'nk_general', 'type' => 'checkbox' ) );
	$wp_customize->add_setting( 'nk_logo_upper', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'nk_logo_upper', array( 'label' => __( 'Text logo in CAPITAL letters', 'nk-studio' ), 'section' => 'nk_general', 'type' => 'checkbox' ) );

	/* ---- Header ---- */
	$wp_customize->add_section( 'nk_header', array( 'title' => __( 'NK Header', 'nk-studio' ), 'priority' => 31, 'description' => __( 'Logo image: Site Identity. Menu: Appearance > Menus > "Primary".', 'nk-studio' ) ) );
	$text( 'nk_header', 'nk_header_cta_text', __( 'CTA text', 'nk-studio' ) );
	$text( 'nk_header', 'nk_header_cta_url', __( 'CTA link', 'nk-studio' ), 'text', 'esc_url_raw' );
	$text( 'nk_header', 'nk_menu_text', __( 'Menu button text', 'nk-studio' ) );

	/* ---- Footer ---- */
	$wp_customize->add_section( 'nk_footer', array( 'title' => __( 'NK Footer', 'nk-studio' ), 'priority' => 32, 'description' => __( 'Link columns: Appearance > Menus, assign to "Footer column 1-5". The menu name becomes the column title.', 'nk-studio' ) ) );
	$text( 'nk_footer', 'nk_footer_big_logo', __( 'Footer mark caption (under the brand, e.g. Studio)', 'nk-studio' ) );
	$wp_customize->add_setting( 'nk_footer_logo_img', array( 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'nk_footer_logo_img', array( 'label' => __( 'Big footer logo image (optional)', 'nk-studio' ), 'section' => 'nk_footer', 'mime_type' => 'image' ) ) );
	$text( 'nk_footer', 'nk_footer_headline', __( 'Headline (bold)', 'nk-studio' ) );
	$text( 'nk_footer', 'nk_footer_muted', __( 'Headline (muted continuation)', 'nk-studio' ), 'textarea' );
	$text( 'nk_footer', 'nk_footer_text', __( 'Paragraph', 'nk-studio' ), 'textarea' );
	$text( 'nk_footer', 'nk_footer_email', __( 'Email address', 'nk-studio' ), 'text', 'sanitize_email' );
	$text( 'nk_footer', 'nk_news_logo', __( 'Newsletter title', 'nk-studio' ) );
	$text( 'nk_footer', 'nk_news_text', __( 'Newsletter text', 'nk-studio' ), 'textarea' );
	$text( 'nk_footer', 'nk_news_placeholder', __( 'Newsletter field placeholder', 'nk-studio' ) );
	$text( 'nk_footer', 'nk_news_action', __( 'Newsletter form action URL (Mailchimp/Brevo/etc. — leave empty to disable)', 'nk-studio' ), 'text', 'esc_url_raw' );
	$text( 'nk_footer', 'nk_news_note', __( 'Newsletter small print', 'nk-studio' ), 'textarea' );
	$text( 'nk_footer', 'nk_footer_phone', __( 'Phone (Get in Touch)', 'nk-studio' ) );
	$text( 'nk_footer', 'nk_footer_address', __( 'Address (Our Location)', 'nk-studio' ) );
	$text( 'nk_footer', 'nk_members_text', __( 'Memberships line', 'nk-studio' ) );
	$text( 'nk_footer', 'nk_copyright', __( 'Copyright line', 'nk-studio' ) );
	$text( 'nk_footer', 'nk_lang_label', __( 'Language label (leave empty to hide)', 'nk-studio' ) );
	foreach ( array( 'web' => 'Website (W.)', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'x' => 'X / Twitter', 'behance' => 'Behance', 'facebook' => 'Facebook', 'youtube' => 'YouTube' ) as $k => $label ) {
		$text( 'nk_footer', 'nk_social_' . $k, $label . ' URL', 'text', 'esc_url_raw' );
	}
} );
