<?php
namespace NK_Studio\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * v3.7.4 — FAQ accordion: small label, big heading, questions that open / close with a + / − icon.
 * Questions and answers are a repeater in Elementor. Also outputs FAQPage structured data (good for Google).
 */
class Faq extends \NK_Studio\Base {
	public function get_name() { return 'nk_faq'; }
	public function get_title() { return __( 'NK FAQ (questions)', 'nk-studio' ); }
	public function get_icon() { return 'eicon-accordion'; }
	public function get_keywords() { return array( 'faq', 'questions', 'accordion', 'nk' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'nk-studio' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Small label', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => '', 'label_block' => true ) );
		$this->add_control( 'heading', array( 'label' => __( 'Heading', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'FAQs', 'label_block' => true ) );
		$this->tag_control( 'nk_tag', 'h2' );
		$this->add_control( 'intro', array( 'label' => __( 'Short text under the heading', 'nk-studio' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'Everything you need to know before we start working together. Can’t find your answer? Just reach out and we will reply fast.' ) );

		$rep = new Repeater();
		$rep->add_control( 'q', array( 'label' => __( 'Question', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Question?', 'label_block' => true ) );
		$rep->add_control( 'a', array( 'label' => __( 'Answer', 'nk-studio' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 5, 'default' => '' ) );
		$this->add_control( 'items', array(
			'label'       => __( 'Questions', 'nk-studio' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ q }}}',
			'separator'   => 'before',
			'default'     => array(
				array( 'q' => 'Q1: Which industries do you serve?', 'a' => 'We serve various industries such as real estate, education, automotive, entertainment, and more. Our services — 3D web-based configurators, VR, and AR development — are specifically tailored to fit every industry. Our goal is to generate leads and drive real results that foster success.' ),
				array( 'q' => 'How does a 3D web configurator integrate into our existing e-commerce stack?', 'a' => 'Our WebGL engines are built headless and integrate seamlessly into Shopify Plus, Salesforce Commerce Cloud, WooCommerce, and custom React/Next.js architectures. We handle dynamic variant pricing, inventory SKU sync, and automated Bill-of-Materials (BOM) generation directly into your checkout.' ),
				array( 'q' => 'What sets Ink & Algorithm apart from standard 3D rendering studios?', 'a' => 'We are creative technologists and software engineers, not just 3D artists. While traditional studios deliver flat static images, we build browser-native real-time software running at 60 FPS with sub-2MB asset footprints and zero app download requirements.' ),
				array( 'q' => 'How do you handle intellectual property (IP) and code ownership?', 'a' => 'You retain 100% ownership of all custom code, 3D assets, and shaders. Upon final deployment, we provide a complete GitHub repository handoff, technical documentation, and post-launch maintenance SLAs governed under US commercial law.' ),
			),
		) );
		$this->add_control( 'first_open', array( 'label' => __( 'First question open', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => '', 'separator' => 'before' ) );
		$this->add_control( 'multi', array( 'label' => __( 'Allow several open at once', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => '' ) );
		$this->add_control( 'schema', array( 'label' => __( 'Google FAQ data (SEO)', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
		$this->appearance_controls( 'light', false );
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$items = array_filter( (array) ( $s['items'] ?? array() ), function ( $it ) {
			return ! empty( $it['q'] );
		} );
		$tag   = $this->tag( $s, 'nk_tag', 'h2' );
		$this->open( $s, 'nk-faq' );
		echo '<div class="nk-container nk-faq__wrap" data-nk-faq' . ( 'yes' === ( $s['multi'] ?? '' ) ? ' data-multi="1"' : '' ) . '><div class="nk-faq__grid"><div class="nk-faq__head">';
		if ( ! empty( $s['eyebrow'] ) ) {
			echo '<p class="nk-faq__eyebrow">' . esc_html( $s['eyebrow'] ) . '</p>';
		}
		if ( ! empty( $s['heading'] ) ) {
			echo '<' . $tag . ' class="nk-headline nk-faq__title">' . esc_html( $s['heading'] ) . '</' . $tag . '>'; // phpcs:ignore
		}
		if ( ! empty( $s['intro'] ) ) {
			echo '<p class="nk-faq__intro">' . esc_html( $s['intro'] ) . '</p>';
		}
		echo '</div><div class="nk-faq__list">';
		$n      = 0;
		$schema = array();
		foreach ( $items as $it ) {
			$open = ( 0 === $n && 'yes' === ( $s['first_open'] ?? '' ) );
			echo '<div class="nk-faq__item' . ( $open ? ' is-open' : '' ) . '" style="--i:' . (int) $n . '">';
			echo '<button type="button" class="nk-faq__q" aria-expanded="' . ( $open ? 'true' : 'false' ) . '"><span>' . esc_html( $it['q'] ) . '</span><i class="nk-faq__icon" aria-hidden="true"></i></button>';
			echo '<div class="nk-faq__a"><div class="nk-faq__a-in"><p>' . esc_html( $it['a'] ?? '' ) . '</p></div></div></div>';
			$schema[] = array( '@type' => 'Question', 'name' => $it['q'], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => (string) ( $it['a'] ?? '' ) ) );
			$n++;
		}
		echo '</div></div></div>';
		if ( 'yes' === ( $s['schema'] ?? 'yes' ) && $schema ) {
			echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $schema ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . '</script>'; // phpcs:ignore
		}
		$this->close();
	}
}
