<?php
namespace NK_Studio\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Scrolling row of big numbers + captions ("240+ AWARDS"). */
class Stats extends \NK_Studio\Base {
	public function get_name() { return 'nk_stats'; }
	public function get_title() { return __( 'NK Stats Marquee', 'nk-studio' ); }
	public function get_icon() { return 'eicon-number-field'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'nk-studio' ) ) );
		$this->add_control( 'label', array( 'label' => __( 'Label', 'nk-studio' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'The difference our work makes where it matters.' ) );
		$rep = new Repeater();
		$rep->add_control( 'value', array( 'label' => __( 'Value', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => '240+' ) );
		$rep->add_control( 'caption', array( 'label' => __( 'Caption', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'AWARDS' ) );
		$this->add_control( 'items', array(
			'label'       => __( 'Stats', 'nk-studio' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ value }}} {{{ caption }}}',
			'default'     => array(
				array( 'value' => '3M+', 'caption' => 'USER SESSIONS' ),
				array( 'value' => '240+', 'caption' => 'AWARDS' ),
				array( 'value' => '40%', 'caption' => 'FASTER DELIVERY' ),
				array( 'value' => '35%+', 'caption' => 'BETTER RETENTION' ),
				array( 'value' => 'Weeks,', 'caption' => 'NOT MONTHS' ),
			),
		) );
		$this->add_control( 'dock', array(
			'label'       => __( 'Landing slot for the scroll video', 'nk-studio' ),
			'type'        => Controls_Manager::SWITCHER,
			'default'     => 'yes',
			'description' => __( 'The NK Scroll Video shrinks and plays here (first section below the video that has this on).', 'nk-studio' ),
		) );
		$this->add_control( 'marquee', array( 'label' => __( 'Scroll as marquee', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
		$this->appearance_controls( 'light', false );
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$this->open( $s, 'nk-strip nk-stats' . ( 'yes' === $s['marquee'] ? ' is-marquee' : '' ) );
		echo '<div class="nk-strip__inner">';
		echo '<div class="nk-strip__lead">';
		if ( ! isset( $s['dock'] ) || 'yes' === $s['dock'] ) {
			echo '<div class="ink-video-slot" data-ink-video-dock aria-hidden="true"></div>';
		}
		echo '<p class="nk-strip__label"><i>/</i> ' . esc_html( $s['label'] ) . '</p></div>';
		$render = function () use ( $s ) {
			foreach ( $s['items'] as $it ) {
				echo '<div class="nk-strip__item nk-stat"><strong>' . esc_html( $it['value'] ) . '</strong><span>' . esc_html( $it['caption'] ) . '</span></div>';
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
