<?php
namespace NK_Studio\Widgets;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Full-screen dark statement ("Empower brands.", "Align what matters.") with a 3D background.
 * Background options: built-in animated particle landscape, image, video, or your own embed/HTML
 * (e.g. a Three.js scene hosted anywhere, or pasted <iframe>/<canvas> code).
 */
class Statement extends \NK_Studio\Base {
	public function get_name() { return 'nk_statement'; }
	public function get_title() { return __( 'NK Statement (3D background)', 'nk-studio' ); }
	public function get_icon() { return 'eicon-parallax'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'nk-studio' ) ) );
		$this->add_control( 'line1', array( 'label' => __( 'Headline line 1', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Empower', 'label_block' => true ) );
		$this->add_control( 'line2', array( 'label' => __( 'Headline line 2', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'brands', 'label_block' => true ) );
		$this->tag_control( 'nk_tag', 'h2' );
		$this->add_control( 'dot', array( 'label' => __( 'Accent dot', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'text', array( 'label' => __( 'Text', 'nk-studio' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'We define what makes each brand distinct, then bring it to life through identity and digital experience.' ) );
		$this->add_control( 'cta_text', array( 'label' => __( 'CTA text', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$this->add_control( 'cta_link', array( 'label' => __( 'CTA link', 'nk-studio' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'text_tag', array( 'label' => __( 'Text HTML tag', 'nk-studio' ), 'type' => Controls_Manager::SELECT, 'default' => 'p', 'options' => array( 'p' => 'p', 'div' => 'div', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6', 'span' => 'span' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'bg', array( 'label' => __( '3D / Background', 'nk-studio' ) ) );
		$this->add_control( 'env', array(
			'label'       => __( 'Environment', 'nk-studio' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'ink',
			'options'     => array(
				'ink'     => __( 'Ink (orange gradient + 3D glass logo) — new', 'nk-studio' ),
				'classic' => __( 'Classic (dark + slash / particles) — old', 'nk-studio' ),
			),
			'description' => __( 'Ink: the section is transparent over the orange page gradient and the 3D glass logo glides to the centre behind the text.', 'nk-studio' ),
		) );
		$this->add_control( 'slash', array( 'label' => __( 'Show 3D slash', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'condition' => array( 'env' => 'classic' ) ) );
		$this->add_control( 'bg_type', array(
			'label'   => __( 'Background type', 'nk-studio' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'particles',
			'condition' => array( 'env' => 'classic' ),
			'options' => array(
				'particles' => __( 'Animated particle landscape (built-in)', 'nk-studio' ),
				'image'     => __( 'Image', 'nk-studio' ),
				'video'     => __( 'Video (MP4/WebM URL)', 'nk-studio' ),
				'embed'     => __( 'Custom embed / HTML / Three.js iframe', 'nk-studio' ),
				'none'      => __( 'None (solid colour)', 'nk-studio' ),
			),
		) );
		$this->add_control( 'bg_image', array( 'label' => __( 'Image', 'nk-studio' ), 'type' => Controls_Manager::MEDIA, 'condition' => array( 'env' => 'classic', 'bg_type' => 'image' ) ) );
		$this->add_control( 'bg_video', array( 'label' => __( 'Video URL', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'env' => 'classic', 'bg_type' => 'video' ), 'placeholder' => 'https://…/loop.mp4' ) );
		$this->add_control( 'bg_embed', array( 'label' => __( 'Embed / HTML', 'nk-studio' ), 'type' => Controls_Manager::CODE, 'language' => 'html', 'rows' => 8, 'condition' => array( 'env' => 'classic', 'bg_type' => 'embed' ), 'description' => __( 'Paste an <iframe src="…"> or any HTML/JS (trusted admins only). It is rendered behind the text.', 'nk-studio' ) ) );
		$this->add_control( 'overlay', array( 'label' => __( 'Dark overlay strength', 'nk-studio' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 1, 'step' => 0.05 ) ), 'default' => array( 'size' => 0.35 ), 'condition' => array( 'env' => 'classic' ), 'selectors' => array( '{{WRAPPER}} .nk-statement__overlay' => 'opacity: {{SIZE}};' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'overlap', array( 'label' => __( 'Next section slides over', 'nk-studio' ) ) );
		$this->add_control( 'overlap', array(
			'label'        => __( 'Next section slides up over this one', 'nk-studio' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'description'  => __( 'This statement stays pinned while the section below (e.g. "Work — what we build", "Insights") rises over it with rounded corners; the statement shrinks and darkens underneath. Live site only (the editor shows normal scrolling).', 'nk-studio' ),
		) );
		$this->add_control( 'overlap_bg', array(
			'label'       => __( 'Solid colour for the sliding section (optional)', 'nk-studio' ),
			'type'        => Controls_Manager::COLOR,
			'default'     => '',
			'description' => __( 'Empty = orange frosted glass (same look as the page). Pick a colour to use a solid background instead.', 'nk-studio' ),
			'condition'   => array( 'overlap' => 'yes' ),
		) );
		$this->add_control( 'overlap_radius', array(
			'label'     => __( 'Corner radius of the sliding section', 'nk-studio' ),
			'type'      => Controls_Manager::SLIDER,
			'range'     => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
			'default'   => array( 'size' => 0, 'unit' => 'px' ),
			'condition' => array( 'overlap' => 'yes' ),
		) );
		$this->add_control( 'overlap_shrink', array(
			'label'     => __( 'Statement shrink / darken amount', 'nk-studio' ),
			'type'      => Controls_Manager::SLIDER,
			'range'     => array( 'px' => array( 'min' => 0, 'max' => 1, 'step' => 0.05 ) ),
			'default'   => array( 'size' => 0.5, 'unit' => 'px' ),
			'condition' => array( 'overlap' => 'yes' ),
		) );
		$this->end_controls_section();

		$this->appearance_controls( 'dark' );
	}

	protected function render() {
		$s       = $this->get_settings_for_display();
		$classic = isset( $s['env'] ) && 'classic' === $s['env'];
		$tag     = $this->tag( $s, 'nk_tag', 'h2' );
		$ttag    = in_array( $s['text_tag'] ?? 'p', array( 'p', 'div', 'h3', 'h4', 'h5', 'h6', 'span' ), true ) ? $s['text_tag'] : 'p';

		if ( $classic ) {
			$this->open( $s, 'nk-statement nk-statement--classic' );
			echo '<div class="nk-statement__bg" aria-hidden="true">';
			switch ( $s['bg_type'] ) {
				case 'particles':
					echo '<canvas data-nk-canvas="particles"></canvas>';
					break;
				case 'image':
					echo $this->img( $s['bg_image'], 'full' ); // phpcs:ignore
					break;
				case 'video':
					if ( $s['bg_video'] ) {
						echo '<video src="' . esc_url( $s['bg_video'] ) . '" autoplay muted loop playsinline></video>';
					}
					break;
				case 'embed':
					// Raw markup entered by editors in Elementor (same trust model as Elementor's own HTML widget).
					echo $s['bg_embed']; // phpcs:ignore
					break;
			}
			echo '<div class="nk-statement__overlay"></div></div>';
		} else {
			// New Ink environment: transparent over the page gradient; the 3D logo stops behind it.
			$this->open( $s, 'nk-statement nk-statement--ink' );
		}

		if ( 'yes' === ( $s['overlap'] ?? 'yes' ) ) {
			$bg  = sanitize_hex_color( $s['overlap_bg'] ?? '' );
			$rad = isset( $s['overlap_radius']['size'] ) && '' !== $s['overlap_radius']['size'] ? (float) $s['overlap_radius']['size'] : 0;
			$amt = isset( $s['overlap_shrink']['size'] ) && '' !== $s['overlap_shrink']['size'] ? (float) $s['overlap_shrink']['size'] : 0.5;
			printf(
				'<span class="ink-pin-marker" data-ink-pin data-bg="%1$s" data-radius="%2$s" data-amount="%3$s" hidden></span>',
				esc_attr( $bg && '#140401' !== strtolower( $bg ) ? $bg : 'glass' ),
				esc_attr( max( 0, min( 80, $rad ) ) ),
				esc_attr( max( 0, min( 1, $amt ) ) )
			);
		}
		echo '<div class="nk-statement__inner">';
		echo '<' . $tag . ' class="nk-headline nk-statement__headline"><span class="nk-hero__line">' . esc_html( $s['line1'] ) . '</span> <span class="nk-hero__line">' . esc_html( $s['line2'] ) . ( 'yes' === $s['dot'] ? '<b class="nk-dot">.</b>' : '' ) . '</span></' . $tag . '>'; // phpcs:ignore
		if ( $classic && 'yes' === $s['slash'] ) {
			echo nk_slash_svg(); // phpcs:ignore
		}
		if ( $s['text'] ) {
			echo '<' . $ttag . ' class="nk-hero__text">' . esc_html( $s['text'] ) . '</' . $ttag . '>'; // phpcs:ignore
		}
		if ( $s['cta_text'] ) {
			echo '<div class="nk-hero__cta">' . nk_link( $s['cta_text'], $this->link_attrs( $s['cta_link'] ) ) . '</div>'; // phpcs:ignore
		}
		echo '</div>';
		$this->close();
	}
}
