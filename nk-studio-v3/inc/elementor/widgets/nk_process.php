<?php
namespace NK_Studio\Widgets;

use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * v3.7.4 — "How we deliver": detailed section that replaces the old "Align what matters" statement.
 * Left: small label, big heading, a few paragraphs, button. Right: a stack of glass cards (icon, title, text).
 * Everything is editable in Elementor (texts, icons, button, colours).
 */
class Process extends \NK_Studio\Base {
	public function get_name() { return 'nk_process'; }
	public function get_title() { return __( 'NK How We Work (detail)', 'nk-studio' ); }
	public function get_icon() { return 'eicon-info-box'; }
	public function get_keywords() { return array( 'process', 'how we work', 'deliver', 'method', 'detail', 'nk' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'nk-studio' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Small label', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => '', 'label_block' => true ) );
		$this->add_control( 'heading', array( 'label' => __( 'Heading', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'How We Work in Any Industry', 'label_block' => true ) );
		$this->tag_control( 'nk_tag', 'h2' );
		$this->add_control( 'dot', array( 'label' => __( 'Accent dot after the heading', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'text', array(
			'label'       => __( 'Text (a new line = a new paragraph)', 'nk-studio' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 10,
			'default'     => "At Ink & Algorithm, our process adapts seamlessly across industries, so every solution is tailored, effective and results-driven. We start by understanding your business, your audience and your challenges, then craft a strategy that fits your goals.\nFrom concept to design and execution, we combine creativity with advanced technology: AR & VR, 3D visualization, animation or interactive solutions, built with precision, innovation and scalability in mind.\nWe work collaboratively and keep you involved at every stage. Our focus is not just visually compelling work, but experiences that engage users, solve real problems and drive measurable growth, whatever the industry.",
		) );
		$this->add_control( 'cta_text', array( 'label' => __( 'Button text', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Let’s Talk' ) );
		$this->add_control( 'cta_link', array( 'label' => __( 'Button link', 'nk-studio' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/contact/' ) ) );

		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'Icon', 'nk-studio' ), 'type' => Controls_Manager::ICONS, 'default' => array( 'value' => 'fas fa-star', 'library' => 'fa-solid' ) ) );
		$rep->add_control( 'title', array( 'label' => __( 'Title', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Title', 'label_block' => true ) );
		$rep->add_control( 'desc', array( 'label' => __( 'Text', 'nk-studio' ), 'type' => Controls_Manager::TEXTAREA, 'default' => '' ) );
		$this->add_control( 'items', array(
			'label'       => __( 'Points (right side)', 'nk-studio' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'separator'   => 'before',
			'default'     => array(
				array( 'icon' => array( 'value' => 'fas fa-users', 'library' => 'fa-solid' ), 'title' => 'Our Customers are our Priority', 'desc' => 'Engineered for measurable ROI: we combine cinematic 3D fidelity with lightweight web performance to deliver experiences that drive revenue growth and reduce customer friction.' ),
				array( 'icon' => array( 'value' => 'fas fa-gamepad', 'library' => 'fa-solid' ), 'title' => 'Allow Us to Solve Your Problems', 'desc' => 'Full-stack creative technology: from CAD data preparation and photorealistic shading to custom frontend integration and analytics tracking, we manage the complete development lifecycle.' ),
				array( 'icon' => array( 'value' => 'fas fa-handshake', 'library' => 'fa-solid' ), 'title' => 'We Build Positive Relationships', 'desc' => 'Agile, milestone-driven delivery: transparent sprint planning, weekly interactive builds and dedicated US-timezone project management.' ),
				array( 'icon' => array( 'value' => 'fas fa-key', 'library' => 'fa-solid' ), 'title' => 'Agile Sprint Execution & Transparent Engineering', 'desc' => 'Structured 1-to-2-week sprints with guaranteed US-timezone overlap. From CAD ingestion and polygon optimization to live staging links, you get direct Slack / Teams access to our lead engineers and weekly review builds.' ),
			),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'cards_style', array( 'label' => __( 'Point cards', 'nk-studio' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'card_bg', array(
			'label'     => __( 'Card colour', 'nk-studio' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .nk-process__item' => 'background-color: {{VALUE}}; background-image: none;' ),
		) );
		$this->add_control( 'icon_color', array(
			'label'     => __( 'Icon colour', 'nk-studio' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .nk-process__icon' => 'color: {{VALUE}};' ),
		) );
		$this->end_controls_section();

		$this->appearance_controls( 'light' );
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$tag = $this->tag( $s, 'nk_tag', 'h2' );
		$this->open( $s, 'nk-process' );
		echo '<div class="nk-container"><div class="nk-process__grid">';

		/* left: label, heading, paragraphs, button */
		echo '<div class="nk-process__main">';
		if ( ! empty( $s['eyebrow'] ) ) {
			echo '<p class="nk-process__eyebrow">' . esc_html( $s['eyebrow'] ) . '</p>';
		}
		if ( ! empty( $s['heading'] ) ) {
			echo '<' . $tag . ' class="nk-headline nk-process__title">' . esc_html( $s['heading'] ) . ( 'yes' === ( $s['dot'] ?? 'yes' ) ? '<b class="nk-dot">.</b>' : '' ) . '</' . $tag . '>'; // phpcs:ignore
		}
		$paras = preg_split( '/\r\n|\r|\n/', (string) ( $s['text'] ?? '' ) );
		echo '<div class="nk-process__text">';
		foreach ( (array) $paras as $p ) {
			if ( '' !== trim( $p ) ) {
				echo '<p>' . esc_html( trim( $p ) ) . '</p>';
			}
		}
		echo '</div>';
		if ( ! empty( $s['cta_text'] ) ) {
			$l   = $this->link_attrs( $s['cta_link'] ?? array() );
			$ext = ! empty( $l['is_external'] ) ? ' target="_blank" rel="noopener"' : '';
			echo '<a class="ink-btn ink-btn--light nk-process__cta" href="' . esc_url( $l['url'] ) . '"' . $ext . '>' . esc_html( $s['cta_text'] ) . '<span class="ink-btn__icon" aria-hidden="true"><svg viewBox="0 0 12 12" fill="none"><path d="M4.5 2.5 8 6l-3.5 3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span></a>'; // phpcs:ignore
		}
		echo '</div>';

		/* right: points */
		echo '<ol class="nk-process__list">';
		foreach ( (array) ( $s['items'] ?? array() ) as $i => $it ) {
			echo '<li class="nk-process__item"><span class="nk-process__icon" aria-hidden="true">';
			if ( ! empty( $it['icon']['value'] ) ) {
				Icons_Manager::render_icon( $it['icon'], array( 'aria-hidden' => 'true' ) );
			} else {
				echo '<b>' . esc_html( sprintf( '%02d', $i + 1 ) ) . '</b>';
			}
			echo '</span><div class="nk-process__body"><h3 class="nk-process__item-title">' . esc_html( $it['title'] ?? '' ) . '</h3>';
			if ( ! empty( $it['desc'] ) ) {
				echo '<p>' . esc_html( $it['desc'] ) . '</p>';
			}
			echo '</div></li>';
		}
		echo '</ol>';

		echo '</div></div>';
		$this->close();
	}
}
