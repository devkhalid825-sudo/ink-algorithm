<?php
namespace NK_Studio\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Big headline hero with animated 3D slash, optional floating images, subtext and CTA. */
class Hero extends \NK_Studio\Base {
	public function get_name() { return 'nk_hero'; }
	public function get_title() { return __( 'NK Hero', 'nk-studio' ); }
	public function get_icon() { return 'eicon-banner'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'nk-studio' ) ) );
		$this->add_control( 'line1', array( 'label' => __( 'Headline line 1', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Move the', 'label_block' => true ) );
		$this->add_control( 'line2', array( 'label' => __( 'Headline line 2', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'needle', 'label_block' => true ) );
		$this->tag_control( 'nk_tag', 'h1' );
		$this->add_control( 'dot', array( 'label' => __( 'Accent dot after headline', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'text', array( 'label' => __( 'Sub text', 'nk-studio' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'An independent branding and digital studio for ambitious organizations at pivotal moments of growth and change. We combine business, creativity and technology to build brands and digital experiences that move them forward.' ) );
		$this->add_control( 'cta_text', array( 'label' => __( 'CTA text', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'SEE OUR IMPACT' ) );
		$this->add_control( 'cta_link', array( 'label' => __( 'CTA link', 'nk-studio' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '#impact' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'visual', array( 'label' => __( 'Visual', 'nk-studio' ) ) );
		$this->add_control( 'slash', array( 'label' => __( 'Show 3D slash', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'grid', array( 'label' => __( 'Show grid / floor background', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'images_note', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => __( 'Optional floating images shown right of line 1 (like the “Build clarity.” variation).', 'nk-studio' ), 'content_classes' => 'elementor-descriptor' ) );
		$this->add_control( 'dock', array(
			'label'       => __( 'Scroll video lands next to line 1', 'nk-studio' ),
			'type'        => Controls_Manager::SWITCHER,
			'default'     => 'yes',
			'description' => __( 'The NK Scroll Video shrinks and keeps playing right of the first word (e.g. "Build"), line 2 stays below. Replaces the floating images while on.', 'nk-studio' ),
		) );
		$this->add_responsive_control( 'dock_h', array(
			'label'      => __( 'Video size (height, × headline size)', 'nk-studio' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'em' ),
			'range'      => array( 'em' => array( 'min' => 0.3, 'max' => 1, 'step' => 0.02 ) ),
			'default'    => array( 'unit' => 'em', 'size' => 0.62 ),
			'condition'  => array( 'dock' => 'yes' ),
			'selectors'  => array( '{{WRAPPER}} .nk-hero__dock' => 'height: {{SIZE}}em;' ),
		) );
		$rep = new Repeater();
		$rep->add_control( 'image', array( 'label' => __( 'Image', 'nk-studio' ), 'type' => Controls_Manager::MEDIA ) );
		$this->add_control( 'images', array( 'label' => __( 'Floating images', 'nk-studio' ), 'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(), 'default' => array() ) );
		$this->end_controls_section();

		$this->appearance_controls( 'light' );
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$this->open( $s, 'nk-hero' . ( 'yes' === $s['grid'] ? ' has-grid' : '' ) );
		if ( 'yes' === $s['grid'] ) {
			echo '<div class="nk-hero__bg" aria-hidden="true"><canvas data-nk-canvas="floor"></canvas></div>';
		}
		echo '<div class="nk-hero__inner">';
		$tag = $this->tag( $s, 'nk_tag', 'h1' );
		$dock = ! isset( $s['dock'] ) || 'yes' === $s['dock'];
		echo '<' . $tag . ' class="nk-headline nk-hero__headline' . ( $dock ? ' nk-hero__headline--dock' : '' ) . '">'; // phpcs:ignore
		echo '<span class="nk-hero__line">' . esc_html( $s['line1'] );
		$thumbs = array_filter( (array) $s['images'], function ( $it ) {
			return ! empty( $it['image']['url'] );
		} );
		if ( $dock ) {
			echo '<span class="nk-hero__thumbs nk-hero__dock" aria-hidden="true"><span class="ink-video-slot ink-video-slot--inline" data-ink-video-dock></span></span>';
		} elseif ( $thumbs ) {
			echo '<span class="nk-hero__thumbs">';
			foreach ( $thumbs as $it ) {
				echo '<span class="nk-hero__thumb">' . $this->img( $it['image'], 'medium' ) . '</span>'; // phpcs:ignore
			}
			echo '</span>';
		}
		echo '</span> ';
		echo '<span class="nk-hero__line">' . esc_html( $s['line2'] ) . ( 'yes' === $s['dot'] ? '<b class="nk-dot">.</b>' : '' ) . '</span>';
		echo '</' . $tag . '>'; // phpcs:ignore
		if ( 'yes' === $s['slash'] ) {
			echo nk_slash_svg(); // phpcs:ignore
		}
		if ( $s['text'] ) {
			echo '<p class="nk-hero__text">' . esc_html( $s['text'] ) . '</p>';
		}
		if ( $s['cta_text'] ) {
			echo '<div class="nk-hero__cta">' . nk_link( $s['cta_text'], $this->link_attrs( $s['cta_link'] ) ) . '</div>'; // phpcs:ignore
		}
		echo '</div>';
		$this->close();
	}
}

