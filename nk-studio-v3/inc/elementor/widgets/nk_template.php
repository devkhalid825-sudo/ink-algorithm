<?php
namespace NK_Studio\Widgets;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NK Saved Section — shows a saved Elementor template (e.g. "NK Home · Work") on any page.
 * Linked: edit the template once and every page that uses it updates. Works with Elementor free.
 */
class Template extends \NK_Studio\Base {
	public function get_name() { return 'nk_template'; }
	public function get_title() { return __( 'NK Saved Section (Home components)', 'nk-studio' ); }
	public function get_icon() { return 'eicon-library-upload'; }
	public function get_keywords() { return array( 'nk', 'template', 'home', 'section', 'global', 'reuse' ); }

	private function template_options() {
		$opts  = array( '' => __( '— Choose —', 'nk-studio' ) );
		$posts = get_posts( array( 'post_type' => 'elementor_library', 'post_status' => 'publish', 'numberposts' => 300, 'orderby' => 'title', 'order' => 'ASC' ) );
		foreach ( $posts as $p ) {
			$type = get_post_meta( $p->ID, '_elementor_template_type', true );
			if ( in_array( $type, array( 'kit', 'popup' ), true ) ) {
				continue;
			}
			$opts[ (string) $p->ID ] = $p->post_title . ( $type ? ' (' . $type . ')' : '' );
		}
		return $opts;
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Section', 'nk-studio' ) ) );
		$this->add_control( 'template_id', array(
			'label'       => __( 'Template', 'nk-studio' ),
			'type'        => Controls_Manager::SELECT2,
			'options'     => $this->template_options(),
			'default'     => '',
			'label_block' => true,
		) );
		$this->add_control( 'help', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => __( 'Every Home section is saved as "NK Home · …" (Templates > Saved Templates). Edit the template once and every page that shows it updates. Need a separate copy instead? Use the folder icon > My Templates > Insert.', 'nk-studio' ),
			'content_classes' => 'elementor-descriptor',
		) );
		$this->end_controls_section();
	}

	protected function render() {
		static $stack = array();
		$id = (int) $this->get_settings_for_display( 'template_id' );
		$editor = \Elementor\Plugin::$instance->editor->is_edit_mode();

		if ( ! $id || 'publish' !== get_post_status( $id ) ) {
			if ( $editor ) {
				echo '<div style="padding:40px;text-align:center;border:1px dashed rgba(127,127,127,.6);font:14px/1.4 sans-serif">' . esc_html__( 'NK Saved Section: choose a template in the panel.', 'nk-studio' ) . '</div>';
			}
			return;
		}
		if ( in_array( $id, $stack, true ) || get_the_ID() === $id ) {
			return; // never render a template inside itself
		}
		$stack[] = $id;
		echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $id, true ); // phpcs:ignore
		array_pop( $stack );

		if ( $editor ) {
			printf(
				'<p style="margin:0;padding:8px;text-align:center;font:12px sans-serif"><a href="%s" target="_blank" rel="noopener">%s</a></p>',
				esc_url( admin_url( 'post.php?post=' . $id . '&action=elementor' ) ),
				esc_html__( 'Edit this section (opens the template)', 'nk-studio' )
			);
		}
	}
}
