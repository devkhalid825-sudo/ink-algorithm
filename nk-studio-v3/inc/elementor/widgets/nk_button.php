<?php
namespace NK_Studio\Widgets;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Button extends \NK_Studio\Base {
	public function get_name() { return 'nk_button'; }
	public function get_title() { return __( 'NK Arrow Link', 'nk-studio' ); }
	public function get_icon() { return 'eicon-button'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'nk-studio' ) ) );
		$this->add_control( 'text', array( 'label' => __( 'Text', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'VIEW ALL WORK' ) );
		$this->add_control( 'link', array( 'label' => __( 'Link', 'nk-studio' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );
		$this->add_control( 'arrow', array( 'label' => __( 'Arrow', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'align', array( 'label' => __( 'Alignment', 'nk-studio' ), 'type' => Controls_Manager::CHOOSE, 'default' => 'center', 'options' => array( 'left' => array( 'title' => 'Left', 'icon' => 'eicon-text-align-left' ), 'center' => array( 'title' => 'Center', 'icon' => 'eicon-text-align-center' ), 'right' => array( 'title' => 'Right', 'icon' => 'eicon-text-align-right' ) ), 'selectors' => array( '{{WRAPPER}} .nk-section' => 'text-align: {{VALUE}};' ) ) );
		$this->end_controls_section();
		$this->appearance_controls( 'light', false );
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$this->open( $s, 'nk-section--compact' );
		echo '<div class="nk-container">' . nk_link( $s['text'], $this->link_attrs( $s['link'] ), 'yes' === $s['arrow'] ) . '</div>'; // phpcs:ignore
		$this->close();
	}
}
