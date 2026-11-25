<?php
namespace NK_Studio\Widgets;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Headline + rich text + optional CTA. Use for inner-page intros. */
class Text_Block extends \NK_Studio\Base {
	public function get_name() { return 'nk_text_block'; }
	public function get_title() { return __( 'NK Text Block', 'nk-studio' ); }
	public function get_icon() { return 'eicon-text'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'nk-studio' ) ) );
		$this->add_control( 'strong', array( 'label' => __( 'Headline (bold)', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Services', 'label_block' => true ) );
		$this->add_control( 'muted', array( 'label' => __( 'Headline (muted)', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'What we do best.', 'label_block' => true ) );
		$this->tag_control( 'nk_tag', 'h1' );
		$this->add_control( 'text', array( 'label' => __( 'Text', 'nk-studio' ), 'type' => Controls_Manager::WYSIWYG, 'default' => '<p>Write your content here.</p>' ) );
		$this->add_control( 'cta_text', array( 'label' => __( 'Link text', 'nk-studio' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'cta_link', array( 'label' => __( 'Link', 'nk-studio' ), 'type' => Controls_Manager::URL ) );
		$this->end_controls_section();
		$this->appearance_controls( 'light' );
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$this->open( $s );
		echo '<div class="nk-container nk-container--narrow nk-center">';
		echo nk_headline( esc_html( $s['strong'] ), esc_html( $s['muted'] ), true, $this->tag( $s, 'nk_tag', 'h1' ) ); // phpcs:ignore
		echo '<div class="nk-prose">' . wp_kses_post( $s['text'] ) . '</div>';
		if ( $s['cta_text'] ) {
			echo '<p>' . nk_link( $s['cta_text'], $this->link_attrs( $s['cta_link'] ) ) . '</p>'; // phpcs:ignore
		}
		echo '</div>';
		$this->close();
	}
}
