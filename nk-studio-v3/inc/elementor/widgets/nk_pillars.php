<?php
namespace NK_Studio\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Strategic Pillars / Services Section (Light Theme)
 * Shows headline + 3 white pillar cards with pill tags and "View Details →".
 */
class Pillars extends \NK_Studio\Base {
	public function get_name() { return 'nk_pillars'; }
	public function get_title() { return __( 'NK Pillars (tag cards, light)', 'nk-studio' ); }
	public function get_icon() { return 'eicon-columns'; }
	public function get_keywords() { return array( 'pillars', 'services', 'cards', 'nk' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'nk-studio' ) ) );

		$this->add_control( 'tag', array(
			'label'       => __( 'Section tag / breadcrumb', 'nk-studio' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '/ Services /',
			'label_block' => true,
		) );

		$this->add_control( 'title', array(
			'label'       => __( 'Headline', 'nk-studio' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Our Strategic Pillars',
			'label_block' => true,
		) );

		$this->add_control( 'subtitle', array(
			'label'       => __( 'Subtitle', 'nk-studio' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 2,
			'default'     => 'Focusing on high-performance architecture, immersive WebGL integrations, and automated enterprise workflows.',
		) );

		$rep = new Repeater();
		$rep->add_control( 'tags', array(
			'label'       => __( 'Pill tags (comma-separated)', 'nk-studio' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Shopify Plus, Next.js Architecture, Headless Storefronts',
			'label_block' => true,
		) );
		$rep->add_control( 'title', array(
			'label'       => __( 'Title', 'nk-studio' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Web & E-Commerce',
			'label_block' => true,
		) );
		$rep->add_control( 'desc', array(
			'label'       => __( 'Description', 'nk-studio' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 3,
			'default'     => 'High-performance, scalable headless commerce engineered for high-volume brands with lightning-fast catalog speeds and custom checkout flows.',
		) );
		$rep->add_control( 'link_text', array(
			'label'   => __( 'Link text', 'nk-studio' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'View Details →',
		) );
		$rep->add_control( 'link', array(
			'label'   => __( 'Link URL', 'nk-studio' ),
			'type'    => Controls_Manager::URL,
			'default' => array( 'url' => '/services/' ),
		) );

		$this->add_control( 'cards', array(
			'label'       => __( 'Pillars Cards', 'nk-studio' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'default'     => array(
				array(
					'tags'      => 'Shopify Plus, Next.js Architecture, Headless Storefronts',
					'title'     => 'Web & E-Commerce',
					'desc'      => 'High-performance, scalable headless commerce engineered for high-volume brands with lightning-fast catalog speeds and custom checkout flows.',
					'link_text' => 'View Details →',
					'link'      => array( 'url' => '/services/' ),
				),
				array(
					'tags'      => 'WebGL / PlayCanvas, Sub-2MB, 2MB Optimization, Dynamic BOM Sync',
					'title'     => '3D Product Configurators',
					'desc'      => 'Interactive 60fps WebGL configurators engineered with sub-2MB asset budgets, real-time PBR materials, and direct live inventory/BOM sync.',
					'link_text' => 'View Details →',
					'link'      => array( 'url' => '/services/' ),
				),
				array(
					'tags'      => 'CPQ Pricing Engines, Dealer Portals, ERP / CRM Pipelines',
					'title'     => 'Custom Solutions',
					'desc'      => 'Tailored CPQ pricing calculations, secure B2B dealer ordering portals, and automated ERP/CRM data integrations for manufacturing workflows.',
					'link_text' => 'View Details →',
					'link'      => array( 'url' => '/services/' ),
				),
			),
		) );

		$this->end_controls_section();

		$this->appearance_controls( 'light' );
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$this->open( $s, 'nk-pillars nk-section--light' );

		echo '<div class="nk-container">';

		echo '<div class="nk-pillars__head nk-center">';
		if ( ! empty( $s['tag'] ) ) {
			echo '<span class="nk-pillars__tag">' . esc_html( $s['tag'] ) . '</span>';
		}
		if ( ! empty( $s['title'] ) ) {
			echo '<h2 class="nk-pillars__title">' . esc_html( $s['title'] ) . '</h2>';
		}
		if ( ! empty( $s['subtitle'] ) ) {
			echo '<p class="nk-pillars__sub">' . esc_html( $s['subtitle'] ) . '</p>';
		}
		echo '</div>';

		$cards = (array) $s['cards'];
		if ( ! empty( $cards ) ) {
			echo '<div class="nk-pillars__grid">';
			foreach ( $cards as $c ) {
				echo '<article class="nk-pillar-card">';

				// Tags list
				if ( ! empty( $c['tags'] ) ) {
					$tag_list = array_filter( array_map( 'trim', explode( ',', $c['tags'] ) ) );
					if ( ! empty( $tag_list ) ) {
						echo '<div class="nk-pillar-card__tags">';
						foreach ( $tag_list as $t ) {
							echo '<span class="nk-pillar-tag">' . esc_html( $t ) . '</span>';
						}
						echo '</div>';
					}
				}

				if ( ! empty( $c['title'] ) ) {
					echo '<h3 class="nk-pillar-card__title">' . esc_html( $c['title'] ) . '</h3>';
				}

				if ( ! empty( $c['desc'] ) ) {
					echo '<p class="nk-pillar-card__desc">' . esc_html( $c['desc'] ) . '</p>';
				}

				if ( ! empty( $c['link_text'] ) ) {
					$url = ! empty( $c['link']['url'] ) ? $c['link']['url'] : '#';
					echo '<div class="nk-pillar-card__action">';
					echo '<a class="nk-pillar-card__link" href="' . esc_url( $url ) . '">' . esc_html( $c['link_text'] ) . '</a>';
					echo '</div>';
				}

				echo '</article>';
			}
			echo '</div>';
		}

		echo '</div>';

		$this->close();
	}
}
