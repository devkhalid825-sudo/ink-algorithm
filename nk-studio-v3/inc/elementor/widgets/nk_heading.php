<?php
namespace NK_Studio\Widgets;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Heading extends \NK_Studio\Base {
	public function get_name() { return 'nk_heading'; }
	public function get_title() { return __( 'NK Section Heading', 'nk-studio' ); }
	public function get_icon() { return 'eicon-heading'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'nk-studio' ) ) );
		$this->add_control( 'strong', array( 'label' => __( 'Bold text', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Work', 'label_block' => true ) );
		$this->add_control( 'dot', array( 'label' => __( 'Accent dot', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'muted', array( 'label' => __( 'Muted text', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'what we build.', 'label_block' => true ) );
		$this->add_control( 'tag', array( 'label' => 'HTML tag', 'type' => Controls_Manager::SELECT, 'default' => 'h2', 'options' => \NK_Studio\Base::tag_options() ) );
		$this->add_control( 'align', array( 'label' => __( 'Alignment', 'nk-studio' ), 'type' => Controls_Manager::CHOOSE, 'default' => 'center', 'options' => array( 'left' => array( 'title' => 'Left', 'icon' => 'eicon-text-align-left' ), 'center' => array( 'title' => 'Center', 'icon' => 'eicon-text-align-center' ), 'right' => array( 'title' => 'Right', 'icon' => 'eicon-text-align-right' ) ), 'selectors' => array( '{{WRAPPER}} .nk-section' => 'text-align: {{VALUE}};' ) ) );
		$this->end_controls_section();
		$this->appearance_controls( 'light' );
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$this->open( $s, 'nk-section--compact' );
		echo '<div class="nk-container">' . nk_headline( esc_html( $s['strong'] ), esc_html( $s['muted'] ), 'yes' === $s['dot'], $this->tag( $s, 'tag', 'h2' ) ) . '</div>'; // phpcs:ignore
		$this->close();
	}
}
