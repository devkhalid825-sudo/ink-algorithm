<?php
namespace NK_Studio\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tall cards: latest blog posts, or videos.
 * v3.5: cards can play videos — YouTube / Shorts, Vimeo, Instagram, TikTok, Facebook link or an MP4 uploaded from
 * your computer. Three sources:
 *  - Blog posts (each post: edit > "Card video" box on the right)
 *  - Dashboard > Social Videos
 *  - Cards typed in here (video link or upload + cover + title + tag + link)
 */
class Insights extends \NK_Studio\Base {
	public function get_name() { return 'nk_insights'; }
	public function get_title() { return __( 'NK Insights (Posts / Videos)', 'nk-studio' ); }
	public function get_icon() { return 'eicon-posts-grid'; }
	public function get_keywords() { return array( 'insights', 'posts', 'blog', 'video', 'youtube', 'nk' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'nk-studio' ) ) );
		$this->add_control( 'strong', array( 'label' => __( 'Heading (bold)', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Insights', 'label_block' => true ) );
		$this->add_control( 'muted', array( 'label' => __( 'Heading (muted)', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Ideas, stories and perspectives.', 'label_block' => true ) );
		$this->tag_control( 'nk_tag', 'h2' );

		$this->add_control( 'source', array(
			'label'     => __( 'Cards come from', 'nk-studio' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'posts',
			'separator' => 'before',
			'options'   => array(
				'posts'  => __( 'Blog posts (picture or "Card video" of each post)', 'nk-studio' ),
				'videos' => __( 'Dashboard > Social Videos', 'nk-studio' ),
				'manual' => __( 'Cards below (video link / upload)', 'nk-studio' ),
			),
		) );
		$this->add_control( 'posts_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => __( 'To show a video on a post card: Posts > edit the post > box "Card video" on the right > paste a YouTube link or upload an MP4. Featured image = cover.', 'nk-studio' ),
			'content_classes' => 'elementor-descriptor',
			'condition'       => array( 'source' => 'posts' ),
		) );
		$this->add_control( 'videos_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => sprintf( __( 'Add videos in %s (link or MP4 upload, featured image = cover, title = card text).', 'nk-studio' ), '<a href="' . esc_url( admin_url( 'edit.php?post_type=nk_social_video' ) ) . '" target="_blank">Social Videos</a>' ),
			'content_classes' => 'elementor-descriptor',
			'condition'       => array( 'source' => 'videos' ),
		) );
		$this->add_control( 'count', array( 'label' => __( 'Number of cards', 'nk-studio' ), 'type' => Controls_Manager::NUMBER, 'default' => 4, 'condition' => array( 'source!' => 'manual' ) ) );
		$this->add_control( 'category', array( 'label' => __( 'Category slug (optional)', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'source' => 'posts' ) ) );

		$rep = new Repeater();
		$rep->add_control( 'video_url', array( 'label' => __( 'Video link (YouTube, Shorts, Vimeo, Instagram, TikTok…)', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'placeholder' => 'https://www.youtube.com/watch?v=…' ) );
		$rep->add_control( 'video_file', array( 'label' => __( '…or upload a video (MP4)', 'nk-studio' ), 'type' => Controls_Manager::MEDIA, 'media_types' => array( 'video' ) ) );
		$rep->add_control( 'cover', array( 'label' => __( 'Cover picture (or a picture card without video)', 'nk-studio' ), 'type' => Controls_Manager::MEDIA ) );
		$rep->add_control( 'title', array( 'label' => __( 'Text under the card', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'default' => 'Card title' ) );
		$rep->add_control( 'badge', array( 'label' => __( 'Tag on the card', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'VIDEO' ) );
		$rep->add_control( 'link', array( 'label' => __( 'Link of the text (optional)', 'nk-studio' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'items', array(
			'label'       => __( 'Cards', 'nk-studio' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'condition'   => array( 'source' => 'manual' ),
			'default'     => array(
				array( 'title' => 'Card one' ),
				array( 'title' => 'Card two' ),
				array( 'title' => 'Card three' ),
				array( 'title' => 'Card four' ),
			),
		) );

		$this->add_control( 'video_mode', array(
			'label'       => __( 'Videos on the cards', 'nk-studio' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'click',
			'separator'   => 'before',
			'options'     => array(
				'click'   => __( 'Cover + play button, plays in the card on click (with sound)', 'nk-studio' ),
				'preview' => __( 'Plays silently on a loop in the card (MP4, YouTube, Vimeo)', 'nk-studio' ),
			),
		) );

		$this->add_responsive_control( 'columns', array( 'label' => __( 'Columns', 'nk-studio' ), 'type' => Controls_Manager::SELECT, 'default' => '4', 'tablet_default' => '2', 'mobile_default' => '1', 'options' => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4' ), 'selectors' => array( '{{WRAPPER}} .nk-cards' => 'grid-template-columns: repeat({{VALUE}}, minmax(0,1fr));' ) ) );
		$this->add_control( 'cta_text', array( 'label' => __( 'Bottom link text', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'EXPLORE ALL INSIGHTS' ) );
		$this->add_control( 'cta_link', array( 'label' => __( 'Bottom link', 'nk-studio' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/insights/' ) ) );
		$this->add_control( 'black_gradient', array(
			'label'        => __( 'Black gradient background (no orange)', 'nk-studio' ),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => __( 'On', 'nk-studio' ),
			'label_off'    => __( 'Off', 'nk-studio' ),
			'return_value' => 'yes',
			'default'      => 'yes',
			'separator'    => 'before',
			'description'  => __( 'On: the orange page gradient fades into black behind this section. Off: the orange page background shows through, as before.', 'nk-studio' ),
		) );
		$this->end_controls_section();
		$this->appearance_controls( 'dark' );
	}

	/** One card that is not a blog post (social video / manual). */
	private function card( $video, $cover, $title, $badge, $link, $mode ) {
		echo '<article class="nk-card is-tall' . ( $video ? ' has-video' : '' ) . '">';
		if ( $video ) {
			echo nk_card_video_media( $video, $cover, $badge, $mode, $title ); // phpcs:ignore
		} else {
			$tag = ! empty( $link['url'] ) ? 'a' : 'div';
			echo '<' . $tag . ' class="nk-card__media"' . ( 'a' === $tag ? ' href="' . esc_url( $link['url'] ) . '"' . ( ! empty( $link['is_external'] ) ? ' target="_blank" rel="noopener"' : '' ) : '' ) . '>'; // phpcs:ignore
			if ( $cover ) {
				echo '<img src="' . esc_url( $cover ) . '" alt="" loading="lazy">';
			}
			if ( $badge ) {
				echo '<span class="nk-badge">' . esc_html( strtoupper( $badge ) ) . '</span>';
			}
			echo '</' . $tag . '>'; // phpcs:ignore
		}
		echo '<div class="nk-card__body"><h3 class="nk-card__title nk-card__title--plain">';
		if ( ! empty( $link['url'] ) ) {
			echo '<a href="' . esc_url( $link['url'] ) . '"' . ( ! empty( $link['is_external'] ) ? ' target="_blank" rel="noopener"' : '' ) . '>' . esc_html( $title ) . '</a>';
		} else {
			echo esc_html( $title );
		}
		echo '</h3></div></article>';
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$src  = $s['source'] ?? 'posts';
		$mode = $s['video_mode'] ?? 'click';
		$this->open( $s, 'nk-insights' . ( 'yes' === ( $s['black_gradient'] ?? 'yes' ) ? ' nk-bg-blackgrad' : '' ) );
		echo '<div class="nk-container">';
		echo nk_headline( esc_html( $s['strong'] ), esc_html( $s['muted'] ), true, $this->tag( $s, 'nk_tag', 'h2' ), 'nk-center' ); // phpcs:ignore
		echo '<div class="nk-cards nk-cards--tall">';
		$shown = 0;

		if ( 'videos' === $src ) {
			$ids = get_posts( array( 'post_type' => 'nk_social_video', 'post_status' => 'publish', 'numberposts' => max( 1, (int) $s['count'] ), 'fields' => 'ids', 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ) ) );
			foreach ( $ids as $id ) {
				$v = function_exists( 'nk_social_video_data' ) ? nk_social_video_data( $id ) : null;
				if ( ! $v ) {
					continue;
				}
				$link = ! empty( $v['url'] ) ? array( 'url' => $v['url'], 'is_external' => true ) : array();
				$this->card( $v, $v['thumb'], $v['caption'], $v['label'], $link, $mode );
				$shown++;
			}
		} elseif ( 'manual' === $src ) {
			foreach ( (array) ( $s['items'] ?? array() ) as $it ) {
				$v = null;
				if ( ! empty( $it['video_file']['url'] ) ) {
					$v = array( 'type' => 'file', 'src' => $it['video_file']['url'], 'thumb' => '', 'platform' => 'file', 'label' => __( 'Video', 'nk-studio' ), 'url' => '', 'vertical' => false );
				} elseif ( ! empty( $it['video_url'] ) && function_exists( 'nk_video_info' ) ) {
					$v = nk_video_info( $it['video_url'] );
				}
				$cover = '';
				if ( ! empty( $it['cover']['id'] ) ) {
					$cover = (string) wp_get_attachment_image_url( (int) $it['cover']['id'], 'large' );
				} elseif ( ! empty( $it['cover']['url'] ) ) {
					$cover = $it['cover']['url'];
				}
				$this->card( $v, $cover, (string) ( $it['title'] ?? '' ), (string) ( $it['badge'] ?? '' ), $it['link'] ?? array(), $mode );
				$shown++;
			}
		} else {
			$args = array( 'post_type' => 'post', 'posts_per_page' => max( 1, (int) $s['count'] ), 'ignore_sticky_posts' => true );
			if ( ! empty( $s['category'] ) ) {
				$args['category_name'] = sanitize_title( $s['category'] );
			}
			$q = new \WP_Query( $args );
			while ( $q->have_posts() ) {
				$q->the_post();
				nk_render_post_card( get_the_ID(), array( 'ratio' => 'is-tall', 'video_mode' => $mode ) );
				$shown++;
			}
			wp_reset_postdata();
		}

		if ( ! $shown ) {
			echo '<p class="nk-empty">' . esc_html( 'videos' === $src ? __( 'No videos yet — add them under Dashboard > Social Videos.', 'nk-studio' ) : __( 'No posts yet.', 'nk-studio' ) ) . '</p>';
		}
		echo '</div>';
		if ( $s['cta_text'] ) {
			echo '<div class="nk-center nk-mt">' . nk_link( $s['cta_text'], $this->link_attrs( $s['cta_link'] ) ) . '</div>'; // phpcs:ignore
		}
		echo '</div>';
		$this->close();
	}
}
