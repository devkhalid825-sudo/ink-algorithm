<?php
namespace NK_Studio\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Project cards: pulled from the Work post type, or entered manually. */
class Work_Grid extends \NK_Studio\Base {
	public function get_name() { return 'nk_work_grid'; }
	public function get_title() { return __( 'NK Work Grid', 'nk-studio' ); }
	public function get_icon() { return 'eicon-gallery-grid'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'nk-studio' ) ) );
		$this->add_control( 'strong', array( 'label' => __( 'Heading (bold)', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Work', 'label_block' => true ) );
		$this->add_control( 'muted', array( 'label' => __( 'Heading (muted)', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'what we build.', 'label_block' => true ) );
		$this->tag_control( 'nk_tag', 'h2' );
		$this->tag_control( 'card_tag', 'h3', __( 'Card title HTML tag', 'nk-studio' ) );
		$this->add_control( 'source', array( 'label' => __( 'Source', 'nk-studio' ), 'type' => Controls_Manager::SELECT, 'default' => 'cpt', 'options' => array( 'cpt' => __( 'Dashboard > Work (projects)', 'nk-studio' ), 'manual' => __( 'Manual cards', 'nk-studio' ) ) ) );
		$this->add_control( 'cpt_note', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => sprintf( __( 'Upload / edit projects in the dashboard: %s. Order = Page attributes > Order.', 'nk-studio' ), '<a href="' . esc_url( admin_url( 'edit.php?post_type=nk_work' ) ) . '" target="_blank">Work</a>' ), 'content_classes' => 'elementor-descriptor', 'condition' => array( 'source' => 'cpt' ) ) );
		$this->add_control( 'count', array( 'label' => __( 'Number of projects', 'nk-studio' ), 'type' => Controls_Manager::NUMBER, 'default' => 4, 'condition' => array( 'source' => 'cpt' ) ) );
		$this->add_control( 'category', array( 'label' => __( 'Category slug (optional)', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'source' => 'cpt' ) ) );
		$this->add_responsive_control( 'columns', array( 'label' => __( 'Columns', 'nk-studio' ), 'type' => Controls_Manager::SELECT, 'default' => '2', 'tablet_default' => '2', 'mobile_default' => '1', 'options' => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4' ), 'selectors' => array( '{{WRAPPER}} .nk-cards' => 'grid-template-columns: repeat({{VALUE}}, minmax(0,1fr));' ) ) );

		$rep = new Repeater();
		$rep->add_control( 'image', array( 'label' => __( 'Image', 'nk-studio' ), 'type' => Controls_Manager::MEDIA ) );
		$rep->add_control( 'badge', array( 'label' => __( 'Badge', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'INVESTOR READINESS' ) );
		$rep->add_control( 'title', array( 'label' => __( 'Title', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Pomelo' ) );
		$rep->add_control( 'sector', array( 'label' => __( 'Sector', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Finance & Fintech' ) );
		$rep->add_control( 'desc', array( 'label' => __( 'Description', 'nk-studio' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'A digital foundation built to grow with Pomelo.' ) );
		$rep->add_control( 'link', array( 'label' => __( 'Link', 'nk-studio' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'items', array( 'label' => __( 'Cards', 'nk-studio' ), 'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(), 'title_field' => '{{{ title }}}', 'condition' => array( 'source' => 'manual' ), 'default' => array( array( 'title' => 'Pomelo' ) ) ) );

		$this->add_control( 'cta_text', array( 'label' => __( 'Bottom link text', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'VIEW ALL WORK' ) );
		$this->add_control( 'cta_link', array( 'label' => __( 'Bottom link', 'nk-studio' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/work/' ) ) );
		$this->end_controls_section();
		$this->appearance_controls( 'dark' );
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$ctag = $this->tag( $s, 'card_tag', 'h3' );
		$this->open( $s );
		echo '<div class="nk-container">';
		echo nk_headline( esc_html( $s['strong'] ), esc_html( $s['muted'] ), true, $this->tag( $s, 'nk_tag', 'h2' ), 'nk-center' ); // phpcs:ignore
		echo '<div class="nk-cards">';

		if ( 'manual' === $s['source'] ) {
			foreach ( (array) $s['items'] as $it ) {
				$href = ! empty( $it['link']['url'] ) ? $it['link']['url'] : '#';
				echo '<article class="nk-card"><a class="nk-card__media" href="' . esc_url( $href ) . '">' . $this->img( $it['image'], 'large' ); // phpcs:ignore
				if ( $it['badge'] ) {
					echo '<span class="nk-badge">' . esc_html( strtoupper( $it['badge'] ) ) . '</span>';
				}
				echo '</a><div class="nk-card__body"><' . $ctag . ' class="nk-card__title"><a href="' . esc_url( $href ) . '">' . esc_html( $it['title'] ) . '</a>'; // phpcs:ignore
				if ( $it['sector'] ) {
					echo ' <span class="nk-card__sector">(' . esc_html( strtoupper( $it['sector'] ) ) . ')</span>';
				}
				echo '</' . $ctag . '>'; // phpcs:ignore
				if ( $it['desc'] ) {
					echo '<p class="nk-card__desc">' . esc_html( $it['desc'] ) . '</p>';
				}
				echo '</div></article>';
			}
		} else {
			$args = array( 'post_type' => 'nk_work', 'posts_per_page' => max( 1, (int) $s['count'] ), 'orderby' => 'menu_order date', 'order' => 'ASC' );
			if ( ! empty( $s['category'] ) ) {
				$args['tax_query'] = array( array( 'taxonomy' => 'nk_work_cat', 'field' => 'slug', 'terms' => sanitize_title( $s['category'] ) ) );
			}
			$q = new \WP_Query( $args );
			if ( $q->have_posts() ) {
				while ( $q->have_posts() ) {
					$q->the_post();
					nk_render_post_card( get_the_ID() );
				}
				wp_reset_postdata();
			} else {
				if ( current_user_can( 'edit_posts' ) ) { // visitors never see this note
					echo '<p class="nk-empty">' . esc_html__( 'No projects yet — add some under Dashboard > Work > Add project (title, excerpt, featured image, badge, sector).', 'nk-studio' ) . '</p>';
				}
			}
		}
		echo '</div>';
		if ( $s['cta_text'] ) {
			echo '<div class="nk-center nk-mt">' . nk_link( $s['cta_text'], $this->link_attrs( $s['cta_link'] ) ) . '</div>'; // phpcs:ignore
		}
		echo '</div>';
		$this->close();
	}
}
