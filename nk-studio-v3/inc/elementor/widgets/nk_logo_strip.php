<?php
namespace NK_Studio\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Left label + row of client logos / award badges (image or plain text). Optional marquee. */
class Logo_Strip extends \NK_Studio\Base {
	public function get_name() { return 'nk_logo_strip'; }
	public function get_title() { return __( 'NK Logo Strip', 'nk-studio' ); }
	public function get_icon() { return 'eicon-slider-push'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'nk-studio' ) ) );
		$this->add_control( 'label', array( 'label' => __( 'Label', 'nk-studio' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'The ambitious brands we help move forward.' ) );
		$rep = new Repeater();
		$rep->add_control( 'image', array( 'label' => __( 'Logo image', 'nk-studio' ), 'type' => Controls_Manager::MEDIA ) );
		$rep->add_control( 'text', array( 'label' => __( 'Or text logo', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Brand' ) );
		$rep->add_control( 'count', array( 'label' => __( 'Small number (e.g. awards count)', 'nk-studio' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'link', array( 'label' => __( 'Link', 'nk-studio' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'items', array(
			'label'       => __( 'Logos', 'nk-studio' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ text }}}',
			'default'     => array(
				array( 'text' => 'LA NACION' ),
				array( 'text' => 'Interbank' ),
				array( 'text' => 'STELLA ARTOIS' ),
				array( 'text' => 'MSBC' ),
				array( 'text' => 'WARREN LOTAS' ),
			),
		) );
		$this->add_control( 'marquee', array( 'label' => __( 'Scroll as marquee', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => '' ) );
		$this->add_control( 'grayscale', array( 'label' => __( 'Grayscale logos', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
		$this->appearance_controls( 'light', false );
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$this->open( $s, 'nk-strip' . ( 'yes' === $s['grayscale'] ? ' is-gray' : '' ) . ( 'yes' === $s['marquee'] ? ' is-marquee' : '' ) );
		echo '<div class="nk-strip__inner">';
		echo '<p class="nk-strip__label"><i>/</i> ' . esc_html( $s['label'] ) . '</p>';
		$render = function () use ( $s ) {
			foreach ( $s['items'] as $it ) {
				$inner = ! empty( $it['image']['url'] ) ?$this->img( $it['image'], 'medium' ) : '<span class="nk-strip__text">' . esc_html( $it['text'] ) . '</span>';
				if ( $it['count'] ) {
					$inner .= '<small>(' . esc_html( $it['count'] ) . ')</small>';
				}
				if ( ! empty( $it['link']['url'] ) ) {
					$inner = '<a href="' . esc_url( $it['link']['url'] ) . '">' . $inner . '</a>';
				}
				echo '<div class="nk-strip__item">' . $inner . '</div>'; // phpcs:ignore
			}
		};
		echo '<div class="nk-strip__track-wrap"><div class="nk-strip__track">';
		$render();
		echo '</div>';
		if ( 'yes' === $s['marquee'] ) {
			echo '<div class="nk-strip__track" aria-hidden="true">';
			$render();
			echo '</div>';
		}
		echo '</div></div>';
		$this->close();
	}
}
