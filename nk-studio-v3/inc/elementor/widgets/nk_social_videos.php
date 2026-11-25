<?php
namespace NK_Studio\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NK Social Videos — reels / shorts / videos from YouTube, Instagram, TikTok, Facebook, Vimeo or uploaded MP4.
 * Videos come from Dashboard > Social Videos (or are typed in here). Click a card: the video plays in place.
 */
class Social_Videos extends \NK_Studio\Base {
	public function get_name() { return 'nk_social_videos'; }
	public function get_title() { return __( 'NK Social Videos', 'nk-studio' ); }
	public function get_icon() { return 'eicon-youtube'; }
	public function get_keywords() { return array( 'video', 'reels', 'shorts', 'tiktok', 'instagram', 'youtube', 'social', 'nk' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'nk-studio' ) ) );
		$this->add_control( 'strong', array( 'label' => __( 'Heading (bold)', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Social', 'label_block' => true ) );
		$this->add_control( 'muted', array( 'label' => __( 'Heading (muted)', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Reels, shorts and behind the scenes.', 'label_block' => true ) );
		$this->tag_control( 'nk_tag', 'h2' );

		$rep = new Repeater();
		$rep->add_control( 'url', array( 'label' => __( 'Video link', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'placeholder' => 'https://www.youtube.com/shorts/…' ) );
		$rep->add_control( 'file', array( 'label' => __( '…or video file (MP4)', 'nk-studio' ), 'type' => Controls_Manager::MEDIA, 'media_types' => array( 'video' ) ) );
		$rep->add_control( 'cover', array( 'label' => __( 'Cover picture', 'nk-studio' ), 'type' => Controls_Manager::MEDIA ) );
		$rep->add_control( 'caption', array( 'label' => __( 'Caption', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$this->add_control( 'items', array(
			'label'       => __( 'Videos — click “Add Item”, paste a YouTube / Shorts / Instagram / TikTok / Facebook / Vimeo link (or upload an MP4)', 'nk-studio' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ caption || url }}}',
			'condition'   => array( 'source!' => 'cpt' ),
			'default'     => array( array( 'url' => '', 'caption' => '' ) ),
		) );

		$this->add_control( 'show_caption', array( 'label' => __( 'Show caption', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'separator' => 'before' ) );
		$this->add_control( 'show_platform', array( 'label' => __( 'Show platform badge', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'cta_text', array( 'label' => __( 'Bottom link text', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$this->add_control( 'cta_link', array( 'label' => __( 'Bottom link (e.g. your Instagram)', 'nk-studio' ), 'type' => Controls_Manager::URL ) );
		$this->end_controls_section();

		$this->start_controls_section( 'grid_style', array( 'label' => __( 'Video cards', 'nk-studio' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'columns', array(
			'label'          => __( 'Columns', 'nk-studio' ),
			'type'           => Controls_Manager::SELECT,
			'default'        => '4',
			'tablet_default' => '3',
			'mobile_default' => '2',
			'options'        => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ),
			'selectors'      => array( '{{WRAPPER}} .nk-sv__grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0,1fr));' ),
		) );
		$this->add_control( 'ratio', array(
			'label'     => __( 'Card shape', 'nk-studio' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => '9 / 16',
			'options'   => array( '9 / 16' => __( 'Vertical 9:16 (reels / shorts)', 'nk-studio' ), '4 / 5' => '4:5', '1 / 1' => __( 'Square', 'nk-studio' ), '16 / 9' => __( 'Wide 16:9', 'nk-studio' ) ),
			'selectors' => array( '{{WRAPPER}} .nk-sv__media' => 'aspect-ratio: {{VALUE}};' ),
		) );
		$this->add_responsive_control( 'gap', array(
			'label'      => __( 'Gap', 'nk-studio' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
			'selectors'  => array( '{{WRAPPER}} .nk-sv__grid' => 'gap: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_responsive_control( 'radius', array(
			'label'      => __( 'Corner radius', 'nk-studio' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
			'selectors'  => array( '{{WRAPPER}} .nk-sv__media' => 'border-radius: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_control( 'black_gradient', array(
			'label'        => __( 'Black gradient background (no orange)', 'nk-studio' ),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => __( 'On', 'nk-studio' ),
			'label_off'    => __( 'Off', 'nk-studio' ),
			'return_value' => 'yes',
			'default'      => '',
			'separator'    => 'before',
			'description'  => __( 'On: the orange page gradient fades into black behind this section. Off: the orange page background shows through, as before.', 'nk-studio' ),
		) );
		$this->end_controls_section();

		$this->appearance_controls( 'dark' );
		$this->add_control( 'source', array(
			'label'     => __( 'Videos come from', 'nk-studio' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'auto',
			'options'   => array(
				'auto'   => __( 'Automatic (Dashboard > Social Videos if any, else the Videos list above)', 'nk-studio' ),
				'cpt'    => __( 'Dashboard > Social Videos only', 'nk-studio' ),
				'manual' => __( 'Only the Videos list above', 'nk-studio' ),
			),
			'separator' => 'before',
		) );
		$this->add_control( 'cpt_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => sprintf(
				/* translators: %s: link */
				__( 'Add videos in the dashboard: %s — paste a YouTube / Shorts, Instagram Reel, TikTok, Facebook or Vimeo link, or upload an MP4. Featured image = cover.', 'nk-studio' ),
				'<a href="' . esc_url( admin_url( 'edit.php?post_type=nk_social_video' ) ) . '" target="_blank">Social Videos</a>'
			),
			'content_classes' => 'elementor-descriptor',
			'condition'       => array( 'source!' => 'manual' ),
		) );
		$this->add_control( 'count', array( 'label' => __( 'Max videos', 'nk-studio' ), 'type' => Controls_Manager::NUMBER, 'default' => 8, 'min' => 1, 'condition' => array( 'source!' => 'manual' ) ) );

	}

	/** Videos to show, as nk_video_info() arrays + caption. */
	private function videos( $s ) {
		$src = $s['source'] ?? 'auto';
		$out = array();
		if ( 'manual' !== $src && function_exists( 'nk_social_video_data' ) ) {
			$ids = get_posts( array( 'post_type' => 'nk_social_video', 'post_status' => 'publish', 'numberposts' => max( 1, (int) ( $s['count'] ?? 8 ) ), 'fields' => 'ids', 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ) ) );
			foreach ( $ids as $id ) {
				$v = nk_social_video_data( $id );
				if ( $v ) {
					$out[] = $v;
				}
			}
			if ( $out || 'cpt' === $src ) {
				return $out;
			}
		}
		foreach ( (array) ( $s['items'] ?? array() ) as $it ) {
			$v = null;
			if ( ! empty( $it['file']['url'] ) ) {
				$v = array( 'type' => 'file', 'src' => $it['file']['url'], 'thumb' => '', 'platform' => 'file', 'label' => __( 'Video', 'nk-studio' ), 'url' => '', 'vertical' => false );
			} elseif ( ! empty( $it['url'] ) && function_exists( 'nk_video_info' ) ) {
				$v = nk_video_info( $it['url'] );
			}
			if ( ! $v ) {
				continue;
			}
			if ( ! empty( $it['cover']['url'] ) ) {
				$v['thumb'] = ! empty( $it['cover']['id'] ) ? (string) wp_get_attachment_image_url( (int) $it['cover']['id'], 'large' ) : $it['cover']['url'];
			}
			$v['caption'] = (string) ( $it['caption'] ?? '' );
			$out[]        = $v;
		}
		return $out;
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$videos = $this->videos( $s );
		$edit   = class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor && \Elementor\Plugin::$instance->editor->is_edit_mode();
		if ( ! $videos && ! $edit ) {
			return; // nothing added yet: the section stays hidden on the live site
		}
		$this->open( $s, 'nk-sv' . ( 'yes' === ( $s['black_gradient'] ?? '' ) ? ' nk-bg-blackgrad' : '' ) );
		echo '<div class="nk-container">';
		echo nk_headline( esc_html( $s['strong'] ), esc_html( $s['muted'] ), true, $this->tag( $s, 'nk_tag', 'h2' ), 'nk-center' ); // phpcs:ignore

		if ( ! $videos ) {
			echo '<div class="nk-sv__empty">' . esc_html__( 'No videos yet. Left panel → Content → “Videos” → Add Item → paste a video link (YouTube, Shorts, Instagram, TikTok, Facebook, Vimeo) or upload an MP4. (Or: Dashboard → Social Videos → Add video.) This section stays hidden on the live site until a video is added.', 'nk-studio' ) . '</div>';
		} else {
			echo '<div class="nk-sv__grid">';
			$play = '<span class="nk-sv__play" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22"><path d="M8 5.5v13l11-6.5z" fill="currentColor"/></svg></span>';
			foreach ( $videos as $v ) {
				$cap   = (string) ( $v['caption'] ?? '' );
				$badge = 'yes' === ( $s['show_platform'] ?? 'yes' ) ? '<span class="nk-sv__badge nk-sv__badge--' . esc_attr( $v['platform'] ) . '">' . esc_html( $v['label'] ) . '</span>' : '';
				$cover = $v['thumb'] ? '<img class="nk-sv__cover" src="' . esc_url( $v['thumb'] ) . '" alt="' . esc_attr( $cap ) . '" loading="lazy">' : '';
				if ( ! $cover && 'file' === $v['type'] ) {
					$cover = '<video class="nk-sv__cover" src="' . esc_url( $v['src'] ) . '#t=0.5" muted playsinline preload="metadata"></video>';
				}
				if ( ! $cover ) {
					$cover = '<span class="nk-sv__ph nk-sv__ph--' . esc_attr( $v['platform'] ) . '">' . esc_html( $v['label'] ) . '</span>';
				}
				echo '<figure class="nk-sv__item' . ( ! empty( $v['vertical'] ) ? ' is-vertical' : '' ) . '">';
				if ( 'link' === $v['type'] ) {
					echo '<a class="nk-sv__media" href="' . esc_url( $v['src'] ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( $cap ? $cap : $v['label'] ) . '">' . $cover . $play . $badge . '</a>'; // phpcs:ignore
				} else {
					echo '<div role="button" tabindex="0" class="nk-sv__media" data-nk-sv="' . esc_attr( $v['type'] ) . '" data-src="' . esc_url( $v['src'] ) . '" aria-label="' . esc_attr( sprintf( __( 'Play: %s', 'nk-studio' ), $cap ? $cap : $v['label'] ) ) . '">' . $cover . $play . $badge . '</div>'; // phpcs:ignore
				}
				if ( 'yes' === ( $s['show_caption'] ?? 'yes' ) && ( $cap || ! empty( $v['url'] ) ) ) {
					echo '<figcaption class="nk-sv__cap">';
					if ( $cap ) {
						echo '<span>' . esc_html( $cap ) . '</span>';
					}
					if ( ! empty( $v['url'] ) && 'link' !== $v['type'] ) {
						echo '<a href="' . esc_url( $v['url'] ) . '" target="_blank" rel="noopener">' . esc_html( sprintf( __( 'Open on %s ↗', 'nk-studio' ), $v['label'] ) ) . '</a>';
					}
					echo '</figcaption>';
				}
				echo '</figure>';
			}
			echo '</div>';
		}
		if ( ! empty( $s['cta_text'] ) ) {
			echo '<div class="nk-center nk-mt">' . nk_link( $s['cta_text'], $this->link_attrs( $s['cta_link'] ?? array() ) ) . '</div>'; // phpcs:ignore
		}
		echo '</div>';
		$this->close();
	}
}
