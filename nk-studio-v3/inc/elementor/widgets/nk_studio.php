<?php
namespace NK_Studio\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blog section (v3.7.4): giant headline + the latest blog posts as cards + closing paragraph + button.
 * Every card can show: ONE picture, SEVERAL pictures (slider with arrows, dots and swipe) or a VIDEO.
 * Posts get their picture from the post's Featured image; in Elementor you can also give any post its own
 * pictures / video (Content > "Pictures / video for a post"). Old "locations" cards are still available.
 */
class Studio extends \NK_Studio\Base {
	public function get_name() { return 'nk_studio'; }
	public function get_title() { return __( 'NK Studio / Blog', 'nk-studio' ); }
	public function get_icon() { return 'eicon-image-box'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'nk-studio' ) ) );
		$this->add_control( 'big', array( 'label' => __( 'Giant headline', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Our Blog', 'label_block' => true ) );
		$this->tag_control( 'nk_tag', 'h2' );
		$this->add_control( 'dot', array( 'label' => __( 'Accent dot', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'scroll', array( 'label' => __( 'Slide headline on scroll', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => '' ) );
		$this->add_control( 'source', array(
			'label'     => __( 'Cards come from', 'nk-studio' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'posts',
			'separator' => 'before',
			'options'   => array(
				'posts'  => __( 'Your blog posts (Posts) — latest first', 'nk-studio' ),
				'manual' => __( 'Cards typed below (photos / slider / video)', 'nk-studio' ),
			),
		) );
		$this->add_control( 'blogs_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => sprintf(
				/* translators: %s: link */
				__( 'These are your real blog posts, newest first. Write or edit them in %s. To show only some posts, pick them in "Show only these posts" below.', 'nk-studio' ),
				'<a href="' . esc_url( admin_url( 'edit.php' ) ) . '" target="_blank">' . __( 'Posts', 'nk-studio' ) . '</a>'
			),
			'content_classes' => 'elementor-descriptor',
			'condition'       => array( 'source!' => 'manual' ),
		) );
		$pick_opts = array();
		foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 120 ) ) as $pp ) {
			$pick_opts[ (string) $pp->post_name ] = $pp->post_title ? $pp->post_title : $pp->post_name; // slug: stays valid on another domain
		}
		$this->add_control( 'pick', array(
			'label'       => __( 'Show only these posts (optional)', 'nk-studio' ),
			'type'        => Controls_Manager::SELECT2,
			'multiple'    => true,
			'options'     => $pick_opts,
			'label_block' => true,
			'description' => __( 'Empty = the newest posts. Choose posts to show exactly those, in the order chosen.', 'nk-studio' ),
			'condition'   => array( 'source!' => 'manual' ),
		) );
		$this->add_control( 'count', array( 'label' => __( 'Number of posts', 'nk-studio' ), 'type' => Controls_Manager::NUMBER, 'default' => 4, 'min' => 1, 'max' => 12, 'condition' => array( 'source!' => 'manual' ) ) );
		$this->add_responsive_control( 'columns', array(
			'label'          => __( 'Cards per row', 'nk-studio' ),
			'type'           => Controls_Manager::SELECT,
			'default'        => '2',
			'tablet_default' => '2',
			'mobile_default' => '1',
			'options'        => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4' ),
			'selectors'      => array( '{{WRAPPER}} .nk-cards--blog, {{WRAPPER}} .nk-cards--loc' => 'grid-template-columns: repeat({{VALUE}}, minmax(0,1fr));' ),
		) );

		/* --- pictures / video for a blog post (posts mode) --- */
		$opts = array( '' => __( '— choose a post —', 'nk-studio' ) );
		foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => array( 'publish', 'draft', 'future', 'private' ), 'numberposts' => 80 ) ) as $p ) {
			$opts[ (string) $p->post_name ] = $p->post_title ? $p->post_title : $p->post_name; // slug (not ID): stays the same on another domain
		}
		$ov = new Repeater();
		$ov->add_control( 'post', array( 'label' => __( 'Which post', 'nk-studio' ), 'type' => Controls_Manager::SELECT, 'options' => $opts, 'default' => '', 'label_block' => true ) );
		$ov->add_control( 'gallery', array( 'label' => __( 'Pictures (1 = single picture, 2 or more = slider)', 'nk-studio' ), 'type' => Controls_Manager::GALLERY ) );
		$ov->add_control( 'video_url', array( 'label' => __( 'Video link (optional) — YouTube, Vimeo, Instagram, TikTok…', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$ov->add_control( 'video_file', array( 'label' => __( '…or upload a video (MP4)', 'nk-studio' ), 'type' => Controls_Manager::MEDIA, 'media_types' => array( 'video' ) ) );
		$this->add_control( 'overrides', array(
			'label'       => __( 'Pictures / video for a post', 'nk-studio' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $ov->get_controls(),
			'title_field' => __( 'Card pictures / video', 'nk-studio' ),
			'condition'   => array( 'source' => 'posts' ),
			'description' => __( 'Add a row, choose the post, then add one picture, several pictures (slider) or a video. Posts without a row use their Featured image.', 'nk-studio' ),
		) );
		$this->add_control( 'fallback', array( 'label' => __( 'Picture for posts that have none', 'nk-studio' ), 'type' => Controls_Manager::MEDIA, 'condition' => array( 'source' => 'posts' ) ) );

		/* --- cards typed here (manual mode) --- */
		$rep = new Repeater();
		$rep->add_control( 'gallery', array( 'label' => __( 'Pictures (1 = single picture, 2 or more = slider)', 'nk-studio' ), 'type' => Controls_Manager::GALLERY ) );
		$rep->add_control( 'image', array( 'label' => __( 'Single photo (if no pictures above)', 'nk-studio' ), 'type' => Controls_Manager::MEDIA ) );
		$rep->add_control( 'video_url', array( 'label' => __( 'Video link (optional) — YouTube, Vimeo, Instagram, TikTok…', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$rep->add_control( 'video_file', array( 'label' => __( '…or upload a video (MP4)', 'nk-studio' ), 'type' => Controls_Manager::MEDIA, 'media_types' => array( 'video' ) ) );
		$rep->add_control( 'label', array( 'label' => __( 'Tag on the card', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'BLOG' ) );
		$rep->add_control( 'date', array( 'label' => __( 'Date (optional)', 'nk-studio' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'title', array( 'label' => __( 'Title (optional)', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$rep->add_control( 'excerpt', array( 'label' => __( 'Short text (optional)', 'nk-studio' ), 'type' => Controls_Manager::TEXTAREA ) );
		$rep->add_control( 'link', array( 'label' => __( 'Link (optional)', 'nk-studio' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'items', array(
			'label'       => __( 'Cards', 'nk-studio' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ title || label }}}',
			'condition'   => array( 'source' => 'manual' ),
			'default'     => array( array( 'label' => 'BLOG', 'title' => 'First story' ), array( 'label' => 'BLOG', 'title' => 'Second story' ) ),
		) );
		$this->add_control( 'video_mode', array(
			'label'   => __( 'Videos on the cards', 'nk-studio' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'click',
			'options' => array(
				'click'   => __( 'Cover + play, plays on click (with sound)', 'nk-studio' ),
				'preview' => __( 'Plays silently on a loop (MP4, YouTube, Vimeo)', 'nk-studio' ),
			),
		) );
		$this->add_control( 'text', array( 'label' => __( 'Paragraph', 'nk-studio' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'Ideas, stories and updates from our studio: 3D, AR & VR, interactive web experiences and what we learn while building products that grow.' ) );
		$this->add_control( 'cta_text', array( 'label' => __( 'Button text (below the text)', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'SEE MORE BLOGS' ) );
		$this->add_control( 'cta_link', array( 'label' => __( 'Button link (empty = your Blogs page)', 'nk-studio' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'back_top', array( 'label' => __( 'Show “Back to top”', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => '' ) );
		$this->end_controls_section();
		$this->appearance_controls( 'light' );
	}

	/** URL list from an Elementor gallery value (or a single media value). */
	private function urls( $gallery ) {
		$out = array();
		foreach ( (array) $gallery as $g ) {
			if ( ! is_array( $g ) ) {
				continue;
			}
			$u = ! empty( $g['id'] ) ? (string) wp_get_attachment_image_url( (int) $g['id'], 'large' ) : '';
			if ( ! $u && ! empty( $g['url'] ) ) {
				$u = (string) $g['url'];
			}
			if ( $u ) {
				$out[] = $u;
			}
		}
		return $out;
	}

	/** Video array (as nk_video_info) from a link / uploaded file, or null. */
	private function video_of( $row ) {
		if ( ! empty( $row['video_file']['url'] ) ) {
			return array( 'type' => 'file', 'src' => $row['video_file']['url'], 'thumb' => '', 'platform' => 'file', 'label' => __( 'Video', 'nk-studio' ), 'url' => '', 'vertical' => false );
		}
		if ( ! empty( $row['video_url'] ) && function_exists( 'nk_video_info' ) ) {
			return nk_video_info( $row['video_url'] );
		}
		return null;
	}

	/** The picture area of a card: video, slider (2+ pictures), one picture, or an empty placeholder. */
	private function media_html( $urls, $video, $badge, $mode, $title, $href = '', $blank = false ) {
		if ( $video && function_exists( 'nk_card_video_media' ) ) {
			return nk_card_video_media( $video, $urls ? $urls[0] : '', $badge, $mode, $title ); // phpcs:ignore
		}
		$tag_b = $badge ? '<span class="nk-badge">' . esc_html( strtoupper( $badge ) ) . '</span>' : '';
		if ( count( $urls ) > 1 ) {
			$h = '<div class="nk-card__media nk-slider" data-nk-slider><div class="nk-slider__track">';
			foreach ( $urls as $u ) {
				$h .= '<img src="' . esc_url( $u ) . '" alt="' . esc_attr( $title ) . '" loading="lazy">';
			}
			$h .= '</div><button type="button" class="nk-slider__btn nk-slider__btn--prev" aria-label="' . esc_attr__( 'Previous', 'nk-studio' ) . '">‹</button>';
			$h .= '<button type="button" class="nk-slider__btn nk-slider__btn--next" aria-label="' . esc_attr__( 'Next', 'nk-studio' ) . '">›</button><div class="nk-slider__dots">';
			foreach ( $urls as $i => $u ) {
				$h .= '<i' . ( 0 === $i ? ' class="is-on"' : '' ) . '></i>';
			}
			return $h . '</div>' . $tag_b . '</div>';
		}
		$img = $urls ? '<img src="' . esc_url( $urls[0] ) . '" alt="' . esc_attr( $title ) . '" loading="lazy">' : '';
		if ( $href ) {
			return '<a class="nk-card__media" href="' . esc_url( $href ) . '"' . ( $blank ? ' target="_blank" rel="noopener"' : '' ) . '>' . $img . $tag_b . '</a>';
		}
		return '<div class="nk-card__media">' . $img . $tag_b . '</div>';
	}

	/** Card text area: date, title, short text, "Read more". */
	private function body_html( $date, $title, $excerpt, $href = '', $blank = false ) {
		if ( '' === $date . $title . $excerpt ) {
			return '';
		}
		$ext = $blank ? ' target="_blank" rel="noopener"' : '';
		$h   = '<div class="nk-card__body">';
		if ( '' !== $date ) {
			$h .= '<span class="nk-post__date">' . esc_html( $date ) . '</span>';
		}
		if ( '' !== $title ) {
			$h .= '<h3 class="nk-card__title nk-post__title">' . ( $href ? '<a href="' . esc_url( $href ) . '"' . $ext . '>' . esc_html( $title ) . '</a>' : esc_html( $title ) ) . '</h3>';
		}
		if ( '' !== $excerpt ) {
			$h .= '<p class="nk-post__excerpt">' . esc_html( $excerpt ) . '</p>';
		}
		if ( $href ) {
			$h .= '<a class="nk-post__more" href="' . esc_url( $href ) . '"' . $ext . '>' . esc_html__( 'Read more', 'nk-studio' ) . ' <span aria-hidden="true">→</span></a>';
		}
		return $h . '</div>';
	}

	/** Latest blog posts as cards. Empty string = no posts (the typed cards are shown instead). */
	private function posts_html( $s ) {
		$src = $s['source'] ?? 'blogs';
		if ( 'manual' === $src ) {
			return '';
		}
		$count = max( 1, (int) ( $s['count'] ?? 4 ) );
		$ids   = array();
		$pick  = array_values( array_filter( array_map( 'sanitize_title', (array) ( $s['pick'] ?? array() ) ) ) );
		if ( $pick ) {
			$ids = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_name__in' => $pick, 'orderby' => 'post_name__in', 'numberposts' => count( $pick ), 'fields' => 'ids', 'ignore_sticky_posts' => true ) );
		}
		if ( ! $ids ) { // v3.8.3: only REAL posts (the old demo "nk_blog" entries are never shown)
			$ids = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => $count, 'fields' => 'ids', 'ignore_sticky_posts' => true ) );
		}
		if ( ! $ids ) {
			return '';
		}
		$mode = $s['video_mode'] ?? 'click';
		$h    = '<div class="nk-cards nk-cards--loc nk-cards--blog">';
		foreach ( $ids as $id ) {
			$ptype = get_post_type( $id );
			$row   = null;
			$urls  = array();

			if ( 'nk_blog' === $ptype ) {
				$g = get_post_meta( $id, '_nk_gallery', true );
				if ( $g ) {
					foreach ( array_filter( explode( ',', (string) $g ) ) as $gid ) {
						$u = wp_get_attachment_image_url( (int) $gid, 'large' );
						if ( $u ) {
							$urls[] = $u;
						}
					}
				}
			}

			// If no gallery images from nk_blog, check Elementor overrides
			if ( ! $urls ) {
				foreach ( (array) ( $s['overrides'] ?? array() ) as $o ) {
					$key = (string) ( $o['post'] ?? '' );
					if ( '' !== $key && ( $key === (string) get_post_field( 'post_name', $id ) || ( ctype_digit( $key ) && (int) $key === (int) $id ) ) ) {
						$row = $o;
						break;
					}
				}
				if ( $row ) {
					$urls = $this->urls( $row['gallery'] ?? array() );
				}
			}

			// Fallback: featured image -> first post img -> fallback control
			if ( ! $urls ) {
				$thumb = get_the_post_thumbnail_url( $id, 'large' );
				if ( $thumb ) {
					$urls[] = $thumb;
				} elseif ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', (string) get_post_field( 'post_content', $id ), $m ) ) {
					$urls[] = $m[1];
				} elseif ( ! empty( $s['fallback']['url'] ) ) {
					$urls = $this->urls( array( $s['fallback'] ) );
				}
			}

			// Video
			$video = null;
			if ( 'nk_blog' === $ptype ) {
				$vf = get_post_meta( $id, '_nk_video_file', true );
				$vu = get_post_meta( $id, '_nk_video_url', true );
				if ( $vf ) {
					$video = array( 'type' => 'file', 'src' => wp_get_attachment_url( (int) $vf ), 'thumb' => '', 'platform' => 'file', 'label' => __( 'Video', 'nk-studio' ), 'url' => '', 'vertical' => false );
				} elseif ( $vu && function_exists( 'nk_video_info' ) ) {
					$video = nk_video_info( $vu );
				}
			}
			if ( ! $video && $row ) {
				$video = $this->video_of( $row );
			}
			if ( ! $video && function_exists( 'nk_post_card_video' ) ) {
				$video = nk_post_card_video( $id );
			}

			// Badge / Category
			$badge = '';
			if ( 'nk_blog' === $ptype ) {
				$badge = (string) get_post_meta( $id, '_nk_badge', true );
				if ( '' === $badge ) {
					$terms = wp_get_object_terms( $id, 'nk_blog_cat' );
					if ( $terms && ! is_wp_error( $terms ) ) {
						$badge = $terms[0]->name;
					}
				}
			}
			if ( '' === $badge ) {
				$cats  = get_the_category( $id );
				$badge = $cats ? $cats[0]->name : '';
			}

			// Link
			$custom_link = 'nk_blog' === $ptype ? (string) get_post_meta( $id, '_nk_link', true ) : '';
			$href        = $custom_link ? $custom_link : get_permalink( $id );
			$title       = get_the_title( $id );
			$ex          = wp_trim_words( wp_strip_all_tags( get_the_excerpt( $id ) ), 22 );
			$h          .= '<article class="nk-card nk-card--loc nk-card--post' . ( $video ? ' has-video' : '' ) . '">';
			$h          .= $this->media_html( $urls, $video, $badge, $mode, $title, $href );
			$h          .= $this->body_html( get_the_date( 'M j, Y', $id ), $title, $ex, $href ) . '</article>';
		}
		return $h . '</div>';
	}

	/** Cards typed in Elementor. */
	private function manual_html( $s ) {
		$mode = $s['video_mode'] ?? 'click';
		$h    = '<div class="nk-cards nk-cards--loc nk-cards--blog">';
		foreach ( (array) ( $s['items'] ?? array() ) as $it ) {
			$urls = $this->urls( $it['gallery'] ?? array() );
			if ( ! $urls && ! empty( $it['image']['url'] ) ) {
				$urls = $this->urls( array( $it['image'] ) );
			}
			$video = $this->video_of( $it );
			$href  = (string) ( $it['link']['url'] ?? '' );
			$blank = ! empty( $it['link']['is_external'] );
			$title = (string) ( $it['title'] ?? '' );
			$body  = $this->body_html( (string) ( $it['date'] ?? '' ), $title, (string) ( $it['excerpt'] ?? '' ), $href, $blank );
			$h    .= '<article class="nk-card nk-card--loc' . ( '' !== $body ? ' nk-card--post' : '' ) . ( $video ? ' has-video' : '' ) . '">';
			$h    .= $this->media_html( $urls, $video, (string) ( $it['label'] ?? '' ), $mode, $title ? $title : (string) ( $it['label'] ?? '' ), '' === $body ? $href : '', $blank ) . $body . '</article>';
		}
		return $h . '</div>';
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$tag = $this->tag( $s, 'nk_tag', 'h2' );
		$this->open( $s, 'nk-studio-sec' );
		echo '<div class="nk-bigtext" ' . ( 'yes' === ( $s['scroll'] ?? '' ) ? 'data-nk-slide' : '' ) . '><' . $tag . ' class="nk-headline nk-bigtext__inner">' . esc_html( $s['big'] ) . ( 'yes' === ( $s['dot'] ?? 'yes' ) ? '<b class="nk-dot">.</b>' : '' ) . '</' . $tag . '></div>'; // phpcs:ignore
		$cards = $this->posts_html( $s );
		if ( '' === $cards ) {
			$cards = $this->manual_html( $s );
		}
		echo '<div class="nk-container">' . $cards; // phpcs:ignore
		echo '<div class="nk-studio-sec__foot"><p class="nk-studio-sec__text">' . esc_html( $s['text'] ) . '</p>';
		if ( ! empty( $s['cta_text'] ) ) {
			$cl  = $this->link_attrs( $s['cta_link'] ?? array() );
			$cta = 'VIEW ALL POSTS' === strtoupper( trim( (string) $s['cta_text'] ) ) ? 'SEE MORE BLOGS' : $s['cta_text']; // old default wording
			if ( empty( $s['cta_link']['url'] ) ) {
				$pp        = (int) get_option( 'page_for_posts' );
				$cl['url'] = $pp ? get_permalink( $pp ) : nk_resolve_url( '/blog/' ); // -> the real /blogs/ page
			}
			echo nk_link( $cta, $cl ); // phpcs:ignore
		}
		if ( 'yes' === ( $s['back_top'] ?? '' ) ) {
			echo nk_link( __( 'BACK TO TOP', 'nk-studio' ), '#', false, 'nk-backtop' ); // phpcs:ignore
		}
		echo '</div></div>';
		$this->close();
	}
}
