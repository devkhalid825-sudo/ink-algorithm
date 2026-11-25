<?php
namespace NK_Studio\Widgets;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Showreel video that scales / rises / un-tilts as it scrolls into view (assets/js/ink-video.js).
 * Accepts a Vimeo / YouTube player URL, a direct .mp4 / .webm link, or a video uploaded from the Elementor panel
 * (Video from > My own video). v3.7.4: upload, cover picture, corner radius and shape controls.
 */
class Video extends \NK_Studio\Base {
	const DEFAULT_URL = 'https://player.vimeo.com/video/1234422660?background=1&autoplay=1&muted=1&loop=1&autopause=0&h=a63c3fbbac';

	public function get_name() { return 'nk_video'; }
	public function get_title() { return __( 'NK Scroll Video', 'nk-studio' ); }
	public function get_icon() { return 'eicon-video-camera'; }
	public function get_keywords() { return array( 'video', 'vimeo', 'showreel', 'nk' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Video', 'nk-studio' ) ) );
		$this->add_control( 'source', array(
			'label'   => __( 'Video from', 'nk-studio' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'link',
			'options' => array(
				'upload' => __( 'My own video (upload / Media Library)', 'nk-studio' ),
				'link'   => __( 'Link (Vimeo / YouTube / .mp4 URL)', 'nk-studio' ),
			),
		) );
		$this->add_control( 'file', array(
			'label'       => __( 'Choose / upload video (MP4)', 'nk-studio' ),
			'type'        => Controls_Manager::MEDIA,
			'media_types' => array( 'video' ),
			'condition'   => array( 'source' => 'upload' ),
			'description' => __( 'Click the box, upload an MP4 (or pick one from the Media Library). Keep it short and light (under ~15 MB) so the page stays fast.', 'nk-studio' ),
		) );
		$this->add_control( 'poster', array(
			'label'       => __( 'Cover picture (shown while the video loads)', 'nk-studio' ),
			'type'        => Controls_Manager::MEDIA,
			'condition'   => array( 'source' => 'upload' ),
		) );
		$this->add_control( 'url', array(
			'label'       => __( 'Vimeo player URL or .mp4 file link', 'nk-studio' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 3,
			'default'     => self::DEFAULT_URL,
			'condition'   => array( 'source' => 'link' ),
			'description' => __( 'Vimeo: use the player.vimeo.com link with background=1. YouTube: a youtube.com/embed/… link. Or paste a direct .mp4 / .webm URL.', 'nk-studio' ),
		) );
		$this->add_control( 'animate', array(
			'label'        => __( 'Scroll animation', 'nk-studio' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'separator'    => 'before',
		) );
		$this->add_control( 'label', array( 'label' => __( 'Accessible title', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Showreel' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'Video style', 'nk-studio' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'max_w', array(
			'label'      => __( 'Max width', 'nk-studio' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%' ),
			'range'      => array( 'px' => array( 'min' => 500, 'max' => 2200 ), '%' => array( 'min' => 40, 'max' => 100 ) ),
			'selectors'  => array( '{{WRAPPER}} .ink-video__stage' => 'max-width: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_responsive_control( 'pad', array(
			'label'      => __( 'Section padding', 'nk-studio' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'vh' ),
			'selectors'  => array( '{{WRAPPER}} .ink-video' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
		) );
		$this->add_responsive_control( 'radius', array(
			'label'      => __( 'Corner radius', 'nk-studio' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
			'selectors'  => array( '{{WRAPPER}} .ink-video__card' => '--ink-vr: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_control( 'ratio', array(
			'label'     => __( 'Video shape', 'nk-studio' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => '',
			'options'   => array( '' => __( 'Default (16:9, 4:3 on phones)', 'nk-studio' ), '16 / 9' => '16:9', '21 / 9' => __( 'Wide 21:9', 'nk-studio' ), '4 / 3' => '4:3', '1 / 1' => __( 'Square', 'nk-studio' ) ),
			'selectors' => array( '{{WRAPPER}} .ink-video__card' => 'aspect-ratio: {{VALUE}};' ),
		) );
		$this->end_controls_section();
	}

	/** Only https, and only Vimeo / YouTube players or a direct media file. */
	private function parse( $url ) {
		$url = trim( html_entity_decode( (string) $url ) );
		$p   = wp_parse_url( $url );
		if ( empty( $p['host'] ) || empty( $p['scheme'] ) || 'https' !== strtolower( $p['scheme'] ) ) {
			return null;
		}
		$path = isset( $p['path'] ) ? strtolower( $p['path'] ) : '';
		if ( preg_match( '/\.(mp4|webm|m4v)$/', $path ) ) {
			return array( 'file', esc_url_raw( $url ) );
		}
		$host = strtolower( $p['host'] );
		if ( 'player.vimeo.com' === $host || 'www.youtube-nocookie.com' === $host || 'www.youtube.com' === $host ) {
			return array( 'frame', esc_url_raw( $url ) );
		}
		return null;
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		if ( 'upload' === ( $s['source'] ?? 'link' ) ) {
			$fu = ! empty( $s['file']['url'] ) ? esc_url_raw( $s['file']['url'] ) : '';
			$v  = $fu ? array( 'file', $fu ) : null;
		} else {
			$v = $this->parse( $s['url'] ?? '' );
		}
		$poster = ! empty( $s['poster']['url'] ) ? $s['poster']['url'] : '';
		$anim = 'yes' === ( $s['animate'] ?? 'yes' );
		echo '<section class="nk-section ink-video' . ( $anim ? '' : ' ink-video--static' ) . '" data-nk-theme="dark"><div class="ink-video__stage"><div class="ink-video__card" data-ink-video' . ( $anim ? '' : '="static"' ) . '>';
		if ( $v && 'file' === $v[0] ) {
			echo '<video muted loop playsinline autoplay preload="metadata" aria-label="' . esc_attr( $s['label'] ) . '"' . ( $poster ? ' poster="' . esc_url( $poster ) . '"' : '' ) . ' src="' . esc_url( $v[1] ) . '"></video>';
		} elseif ( $v ) {
			echo '<iframe title="' . esc_attr( $s['label'] ) . '" data-src="' . esc_url( $v[1] ) . '" allow="autoplay; fullscreen; picture-in-picture" referrerpolicy="strict-origin-when-cross-origin" loading="lazy"></iframe>';
		}
		echo '</div></div></section>';
	}
}
