<?php
namespace NK_Studio\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * INKNALGORITHM hero: bottom-aligned headline, social proof, description + 2 buttons,
 * over the orange gradient with the scroll-animated 3D glass logo (assets/js/ink-hero-3d.js).
 * All text is editable here; the 3D environment is not (only on/off + model URL).
 */
class Ink_Hero extends \NK_Studio\Base {
	public function get_name() { return 'nk_ink_hero'; }
	public function get_title() { return __( 'Ink Hero (3D glass)', 'nk-studio' ); }
	public function get_icon() { return 'eicon-banner'; }
	public function get_keywords() { return array( 'ink', 'hero', '3d', 'nk' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'nk-studio' ) ) );
		$this->add_control( 'title', array(
			'label'       => __( 'Headline (Enter = new line)', 'nk-studio' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 4,
			'default'     => "Elevate your\nmarketing with\nAI Solutions.",
		) );
		$this->add_control( 'title_tag', array(
			'label'   => __( 'Headline tag', 'nk-studio' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'h1',
			'options' => \NK_Studio\Base::tag_options(),
		) );
		$this->add_control( 'proof', array(
			'label'       => __( 'Social proof text', 'nk-studio' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Trusted by 10k+ businesses',
			'label_block' => true,
		) );
		$rep = new Repeater();
		$rep->add_control( 'image', array( 'label' => __( 'Avatar', 'nk-studio' ), 'type' => Controls_Manager::MEDIA ) );
		$this->add_control( 'avatars', array(
			'label'   => __( 'Avatars', 'nk-studio' ),
			'type'    => Controls_Manager::REPEATER,
			'fields'  => $rep->get_controls(),
			'default' => array(
				array( 'image' => array( 'url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=80&h=80&fit=crop&q=80' ) ),
				array( 'image' => array( 'url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=80&h=80&fit=crop&q=80' ) ),
				array( 'image' => array( 'url' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=80&h=80&fit=crop&q=80' ) ),
				array( 'image' => array( 'url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=80&h=80&fit=crop&q=80' ) ),
			),
		) );
		$this->add_control( 'avatars_more', array(
			'label'       => __( 'Last avatar bubble (e.g. 10k+)', 'nk-studio' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '10k+',
		) );
		$this->add_control( 'stars', array(
			'label'        => __( 'Show 5 stars', 'nk-studio' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );
		$this->add_control( 'desc', array(
			'label'   => __( 'Description', 'nk-studio' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Discover how our AI-driven strategies transform your marketing, delivering unparalleled results and efficiency.',
		) );
		$this->add_control( 'btn1_text', array( 'label' => __( 'Button 1 text (white)', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Get Started', 'separator' => 'before' ) );
		$this->add_control( 'btn1_link', array( 'label' => __( 'Button 1 link', 'nk-studio' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/contact/' ) ) );
		$this->add_control( 'btn2_text', array( 'label' => __( 'Button 2 text (glass)', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Discover More' ) );
		$this->add_control( 'btn2_link', array( 'label' => __( 'Button 2 link', 'nk-studio' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '#work' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'visual', array( 'label' => __( '3D model', 'nk-studio' ) ) );
		$this->add_control( 'enable_3d', array(
			'label'       => __( 'Show 3D glass logo', 'nk-studio' ),
			'type'        => Controls_Manager::SWITCHER,
			'default'     => 'yes',
			'description' => __( 'The model stays fixed behind the whole page and turns while you scroll.', 'nk-studio' ),
		) );
		$this->add_control( 'model_url', array(
			'label'       => __( 'Custom .glb URL (optional)', 'nk-studio' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'placeholder' => __( 'Leave empty = built-in INKNALGORITHM mark', 'nk-studio' ),
			'condition'   => array( 'enable_3d' => 'yes' ),
		) );
		$this->end_controls_section();

		/* ---- Style ---- */
		$this->start_controls_section( 'style', array( 'label' => __( 'Hero style', 'nk-studio' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'title_typo',
			'label'    => __( 'Headline typography', 'nk-studio' ),
			'selector' => '{{WRAPPER}} .ink-hero__title',
		) );
		$this->add_control( 'title_color', array( 'label' => __( 'Headline colour', 'nk-studio' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .ink-hero__title' => 'color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'desc_typo',
			'label'    => __( 'Description typography', 'nk-studio' ),
			'selector' => '{{WRAPPER}} .ink-hero__desc',
		) );
		$this->add_control( 'desc_color', array( 'label' => __( 'Description colour', 'nk-studio' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .ink-hero__desc, {{WRAPPER}} .ink-hero__proof' => 'color: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'min_h', array(
			'label'      => __( 'Min height', 'nk-studio' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'vh', 'px' ),
			'range'      => array( 'vh' => array( 'min' => 40, 'max' => 150 ), 'px' => array( 'min' => 300, 'max' => 1400 ) ),
			'selectors'  => array( '{{WRAPPER}} .ink-hero' => 'min-height: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_control( 'aside_glass', array(
			'label'        => __( 'Glass card behind description + buttons', 'nk-studio' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'description'  => __( 'Frosted glass panel so the text stays crisp when the 3D icon passes behind it.', 'nk-studio' ),
		) );
		$this->add_control( 'aside_glass_bg', array(
			'label'     => __( 'Glass card tint', 'nk-studio' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'aside_glass' => 'yes' ),
			'selectors' => array( '{{WRAPPER}} .ink-hero__aside--glass' => 'background-color: {{VALUE}};' ),
		) );
		$this->add_responsive_control( 'pad', array(
			'label'      => __( 'Padding', 'nk-studio' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'vh', '%' ),
			'selectors'  => array( '{{WRAPPER}} .ink-hero' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
		) );
		$this->end_controls_section();
	}

	private function btn( $text, $link, $class, $arrow ) {
		if ( '' === trim( (string) $text ) ) {
			return '';
		}
		$url  = ! empty( $link['url'] ) ? $link['url'] : '#';
		$attr = ' href="' . esc_url( $url ) . '"';
		if ( ! empty( $link['is_external'] ) ) {
			$attr .= ' target="_blank" rel="noopener"';
		}
		$icon = $arrow ? '<span class="ink-btn__icon" aria-hidden="true"><svg viewBox="0 0 12 12" fill="none"><path d="M4.5 2.5 8 6l-3.5 3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>' : '';
		return '<a class="ink-btn ' . esc_attr( $class ) . '"' . $attr . '>' . esc_html( $text ) . $icon . '</a>';
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$tag = $this->tag( $s, 'title_tag', 'h1' );

		echo '<section class="nk-section ink-hero" data-nk-theme="dark">';

		if ( 'yes' === $s['enable_3d'] ) {
			$model = ! empty( $s['model_url'] ) ? $s['model_url'] : NK_URI . '/assets/models/ink-algorithm-mark.glb';
			echo '<div class="ink-hero__anchor" data-ink3d data-model="' . esc_url( $model ) . '" aria-hidden="true"></div>';
		} else {
			echo '<div class="ink-hero__anchor" data-ink3d-off aria-hidden="true"></div>';
		}

		echo '<div class="ink-container ink-hero__grid"><div class="ink-hero__main">';
		if ( $s['title'] ) {
			$title = nl2br( wp_kses( $s['title'], array( 'br' => array(), 'em' => array(), 'strong' => array(), 'span' => array( 'class' => array() ) ) ) );
			echo '<' . $tag . ' class="ink-hero__title" data-ink-chars>' . $title . '</' . $tag . '>'; // phpcs:ignore
		}
		$avatars = array_filter( (array) $s['avatars'], function ( $a ) {
			return ! empty( $a['image']['url'] );
		} );
		$more = isset( $s['avatars_more'] ) ? trim( (string) $s['avatars_more'] ) : '';
		if ( $avatars || $s['proof'] ) {
			echo '<div class="ink-proof ink-reveal ink-reveal--2">';
			if ( $avatars ) {
				echo '<div class="ink-avatars">';
				foreach ( $avatars as $a ) {
					echo $this->img( $a['image'], 'thumbnail' ); // phpcs:ignore
				}
				if ( '' !== $more ) {
					echo '<span class="ink-avatars__more">' . esc_html( $more ) . '</span>';
				}
				echo '</div>';
			}
			echo '<div class="ink-proof__meta">';
			if ( ! isset( $s['stars'] ) || 'yes' === $s['stars'] ) {
				$star = '<svg viewBox="0 0 12 12" aria-hidden="true"><path fill="currentColor" d="M6 .6l1.6 3.4 3.7.5-2.7 2.6.7 3.7L6 9 2.7 10.8l.7-3.7L.7 4.5l3.7-.5z"/></svg>';
				echo '<span class="ink-stars" aria-label="5/5">' . str_repeat( $star, 5 ) . '</span>'; // phpcs:ignore
			}
			if ( $s['proof'] ) {
				echo '<p class="ink-hero__proof">' . esc_html( $s['proof'] ) . '</p>';
			}
			echo '</div></div>';
		}
		$glass = ! isset( $s['aside_glass'] ) || 'yes' === $s['aside_glass'];
		echo '</div><div class="ink-hero__aside' . ( $glass ? ' ink-hero__aside--glass ink-reveal ink-reveal--3' : '' ) . '">';
		if ( $s['desc'] ) {
			echo '<p class="ink-hero__desc ink-reveal ink-reveal--4">' . esc_html( $s['desc'] ) . '</p>';
		}
		$b1 = $this->btn( $s['btn1_text'], $s['btn1_link'], 'ink-btn--light', true );
		$b2 = $this->btn( $s['btn2_text'], $s['btn2_link'], 'ink-btn--glass', false );
		if ( $b1 || $b2 ) {
			echo '<div class="ink-hero__ctas ink-reveal ink-reveal--5">' . $b1 . $b2 . '</div>'; // phpcs:ignore
		}
		echo '</div></div></section>';
	}
}
