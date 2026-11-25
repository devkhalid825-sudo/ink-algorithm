<?php
namespace NK_Studio;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared base: category, "Appearance" style controls (theme, spacing, accent, headline typography)
 * and the section wrapper used by every NK widget.
 */
abstract class Base extends Widget_Base {

	public function get_categories() {
		return array( 'nk-studio' );
	}

	public function get_icon() {
		return 'eicon-star-o';
	}

	public function get_keywords() {
		return array( 'nk', 'studio' );
	}

	/** Call at the end of register_controls() in each widget. */
	protected function appearance_controls( $default_theme = 'light', $with_headline = true ) {
		$this->start_controls_section( 'nk_appearance', array(
			'label' => __( 'Appearance', 'nk-studio' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'nk_theme', array(
			'label'   => __( 'Section theme', 'nk-studio' ),
			'type'    => Controls_Manager::SELECT,
			'default' => $default_theme,
			'options' => array(
				'light' => __( 'Light (white)', 'nk-studio' ),
				'grey'  => __( 'Light grey', 'nk-studio' ),
				'dark'  => __( 'Dark (black)', 'nk-studio' ),
			),
		) );

		$this->add_control( 'nk_bg_color', array(
			'label'     => __( 'Background colour (override)', 'nk-studio' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .nk-section' => 'background-color: {{VALUE}};' ),
		) );

		$this->add_control( 'nk_text_color', array(
			'label'     => __( 'Text colour (override)', 'nk-studio' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .nk-section' => 'color: {{VALUE}};' ),
		) );

		$this->add_control( 'nk_accent_color', array(
			'label'     => __( 'Accent colour (override)', 'nk-studio' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .nk-section' => '--nk-accent: {{VALUE}};' ),
		) );

		$this->add_responsive_control( 'nk_padding', array(
			'label'      => __( 'Section padding', 'nk-studio' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'vh', 'rem', '%' ),
			'selectors'  => array( '{{WRAPPER}} .nk-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
		) );

		$this->add_responsive_control( 'nk_min_height', array(
			'label'      => __( 'Min height', 'nk-studio' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', 'vh' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 1600 ), 'vh' => array( 'min' => 0, 'max' => 200 ) ),
			'selectors'  => array( '{{WRAPPER}} .nk-section' => 'min-height: {{SIZE}}{{UNIT}};' ),
		) );

		if ( $with_headline ) {
			$this->add_group_control( Group_Control_Typography::get_type(), array(
				'name'     => 'nk_headline_typo',
				'label'    => __( 'Headline typography', 'nk-studio' ),
				'selector' => '{{WRAPPER}} .nk-headline',
			) );
			$this->add_control( 'nk_headline_color', array(
				'label'     => __( 'Headline colour', 'nk-studio' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .nk-headline__strong' => 'color: {{VALUE}};' ),
			) );
			$this->add_control( 'nk_muted_color', array(
				'label'     => __( 'Muted headline colour', 'nk-studio' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .nk-headline__muted' => 'color: {{VALUE}};' ),
			) );
		}

		$this->end_controls_section();
	}

	/** Allowed HTML tags for headings (SEO: pick H1–H6, or a neutral div/p/span). */
	public static function tag_options() {
		return array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6', 'div' => 'div', 'p' => 'p', 'span' => 'span' );
	}

	/** "HTML tag" select control, used by every widget that prints a headline. */
	protected function tag_control( $id = 'nk_tag', $default = 'h2', $label = '' ) {
		$this->add_control( $id, array(
			'label'   => $label ? $label : __( 'Headline HTML tag', 'nk-studio' ),
			'type'    => Controls_Manager::SELECT,
			'default' => $default,
			'options' => self::tag_options(),
		) );
	}

	/** Validated tag value from settings. */
	protected function tag( $s, $id = 'nk_tag', $default = 'h2' ) {
		$t = isset( $s[ $id ] ) ? (string) $s[ $id ] : '';
		return isset( self::tag_options()[ $t ] ) ? $t : $default;
	}

	/** Opening <section> tag with theme attribute. */
	protected function open( $s, $extra = '' ) {
		$theme = $s['nk_theme'] ?? 'light';
		printf(
			'<section class="nk-section nk-w-%1$s nk-theme-%2$s %3$s" data-nk-theme="%2$s">',
			esc_attr( $this->get_name() ),
			esc_attr( $theme ),
			esc_attr( $extra )
		);
	}

	protected function close() {
		echo '</section>';
	}

	/** <img> from an Elementor MEDIA value. */
	protected function img( $media, $size = 'large', $class = '' ) {
		if ( empty( $media['url'] ) ) {
			return '';
		}
		if ( ! empty( $media['id'] ) ) {
			return wp_get_attachment_image( (int) $media['id'], $size, false, array( 'class' => $class, 'loading' => 'lazy' ) );
		}
		return '<img class="' . esc_attr( $class ) . '" src="' . esc_url( $media['url'] ) . '" alt="" loading="lazy">';
	}

	/** Repeater item attribute helper for Elementor inline editing. */
	protected function link_attrs( $link ) {
		return array(
			'url'         => $link['url'] ?? '#',
			'is_external' => ! empty( $link['is_external'] ),
			'nofollow'    => ! empty( $link['nofollow'] ),
		);
	}
}
