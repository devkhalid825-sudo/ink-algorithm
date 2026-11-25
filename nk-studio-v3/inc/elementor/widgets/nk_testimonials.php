<?php
namespace NK_Studio\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reviews / testimonials.
 * v3.7.4: default layout = "Cards" — small label + big heading + a row of review cards (photo, name, role, quote).
 *         The old layout (logo tabs + one big quote + people row) is still there: Layout > "Logo tabs".
 * v3.2:   reviews can come from the dashboard (Reviews menu) — "Automatic" uses them as soon as one is published,
 *         otherwise the cards typed in here are shown.
 */
class Testimonials extends \NK_Studio\Base {
	public function get_name() { return 'nk_testimonials'; }
	public function get_title() { return __( 'NK Testimonials / Reviews', 'nk-studio' ); }
	public function get_icon() { return 'eicon-testimonial-carousel'; }
	public function get_keywords() { return array( 'reviews', 'testimonials', 'clients', 'nk' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'nk-studio' ) ) );
		$this->add_control( 'layout', array(
			'label'   => __( 'Layout', 'nk-studio' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'cards',
			'options' => array(
				'cards' => __( 'Cards (3 in a row)', 'nk-studio' ),
				'tabs'  => __( 'Logo tabs + one big quote (old)', 'nk-studio' ),
			),
		) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Small label above the heading', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'TESTIMONIALS', 'label_block' => true, 'condition' => array( 'layout' => 'cards' ) ) );
		$this->add_control( 'strong', array( 'label' => __( 'Heading', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'What our clients say.', 'label_block' => true ) );
		$this->add_control( 'muted', array( 'label' => __( 'Heading (muted part, optional)', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => '', 'label_block' => true ) );
		$this->tag_control( 'nk_tag', 'h2' );

		$this->add_control( 'source', array(
			'label'       => __( 'Reviews come from', 'nk-studio' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'auto',
			'options'     => array(
				'auto'   => __( 'Automatic (Dashboard > Reviews if any, else the cards below)', 'nk-studio' ),
				'cpt'    => __( 'Dashboard > Reviews only', 'nk-studio' ),
				'manual' => __( 'Only the cards below', 'nk-studio' ),
			),
			'separator'   => 'before',
		) );
		$this->add_control( 'cpt_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => sprintf(
				/* translators: %s: link */
				__( 'Add / edit reviews in the dashboard: %s (title = name, text = review, featured image = photo, plus role, company, logo and stars).', 'nk-studio' ),
				'<a href="' . esc_url( admin_url( 'edit.php?post_type=nk_review' ) ) . '" target="_blank">Reviews</a>'
			),
			'content_classes' => 'elementor-descriptor',
			'condition'       => array( 'source!' => 'manual' ),
		) );
		$this->add_control( 'count', array( 'label' => __( 'Max reviews from the dashboard', 'nk-studio' ), 'type' => Controls_Manager::NUMBER, 'default' => 6, 'min' => 1, 'condition' => array( 'source!' => 'manual' ) ) );

		$rep = new Repeater();
		$rep->add_control( 'name', array( 'label' => __( 'Name', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'John Doe' ) );
		$rep->add_control( 'role', array( 'label' => __( 'Role', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'CEO' ) );
		$rep->add_control( 'company', array( 'label' => __( 'Company name', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Global Retail' ) );
		$rep->add_control( 'quote', array( 'label' => __( 'Quote', 'nk-studio' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'Their AI solutions are a game-changer. We’ve seen significant improvements in campaign performance and ROI.' ) );
		$rep->add_control( 'avatar', array( 'label' => __( 'Photo', 'nk-studio' ), 'type' => Controls_Manager::MEDIA ) );
		$rep->add_control( 'logo', array( 'label' => __( 'Company logo (logo-tabs layout)', 'nk-studio' ), 'type' => Controls_Manager::MEDIA ) );
		$rep->add_control( 'rating', array( 'label' => __( 'Stars (0 = none)', 'nk-studio' ), 'type' => Controls_Manager::NUMBER, 'min' => 0, 'max' => 5, 'default' => 0 ) );
		$this->add_control( 'items', array(
			'label'       => __( 'Cards (used when there are no dashboard reviews)', 'nk-studio' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ name }}} — {{{ company }}}',
			'condition'   => array( 'source!' => 'cpt' ),
			'default'     => array(
				array( 'name' => 'John Doe', 'role' => 'CEO', 'company' => 'Global Retail', 'quote' => 'Their AI solutions are a game-changer. We’ve seen significant improvements in campaign performance and ROI.' ),
				array( 'name' => 'Celine Doe', 'role' => 'CMO', 'company' => 'Tech Innovators', 'quote' => 'The insights provided by their platform have revolutionized our marketing strategy and execution.' ),
				array( 'name' => 'Mike Doe', 'role' => 'Marketing Director', 'company' => 'HealthCo', 'quote' => 'Their expertise in AI marketing is unmatched. We highly recommend their services for future-proofing your brand.' ),
			),
		) );

		$this->add_responsive_control( 'columns', array(
			'label'          => __( 'Cards per row', 'nk-studio' ),
			'type'           => Controls_Manager::SELECT,
			'default'        => '3',
			'tablet_default' => '2',
			'mobile_default' => '1',
			'options'        => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4' ),
			'condition'      => array( 'layout' => 'cards' ),
			'separator'      => 'before',
			'selectors'      => array( '{{WRAPPER}} .nk-testi__cards' => 'grid-template-columns: repeat({{VALUE}}, minmax(0,1fr));' ),
		) );
		$this->add_control( 'card_color', array(
			'label'     => __( 'Card colour', 'nk-studio' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'layout' => 'cards' ),
			'selectors' => array( '{{WRAPPER}} .nk-testi__card' => 'background-color: {{VALUE}};' ),
		) );
		$this->add_control( 'autoplay', array( 'label' => __( 'Autoplay (seconds, 0 = off)', 'nk-studio' ), 'type' => Controls_Manager::NUMBER, 'default' => 0, 'separator' => 'before', 'condition' => array( 'layout' => 'tabs' ) ) );
		$this->end_controls_section();
		$this->appearance_controls( 'dark' ); // Reviews section is solid black by default
	}

	/** Items to show (dashboard reviews or the repeater). */
	private function items( $s ) {
		$src = $s['source'] ?? 'auto';
		if ( 'manual' !== $src && function_exists( 'nk_reviews_items' ) ) {
			$cpt = nk_reviews_items( (int) ( $s['count'] ?? 6 ) );
			if ( $cpt || 'cpt' === $src ) {
				return $cpt;
			}
		}
		return is_array( $s['items'] ?? null ) ? $s['items'] : array();
	}

	/** "CEO, Global Retail" — role plus company (company only added when the role does not already contain it). */
	private function role_line( $it ) {
		$role    = trim( (string) ( $it['role'] ?? '' ) );
		$company = trim( (string) ( $it['company'] ?? '' ) );
		if ( '' !== $company && false === stripos( $role, $company ) ) {
			return '' !== $role ? $role . ', ' . $company : $company;
		}
		return $role;
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$items = array_values( $this->items( $s ) );
		$edit  = class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor && \Elementor\Plugin::$instance->editor->is_edit_mode();
		if ( ! $items && ! $edit ) {
			return;
		}
		$cards = 'tabs' !== ( $s['layout'] ?? 'cards' );
		$this->open( $s, 'nk-testi' . ( $cards ? ' nk-testi--cards' : '' ) );

		if ( $cards ) {
			echo '<div class="nk-container">';
			if ( ! empty( $s['eyebrow'] ) ) {
				echo '<p class="nk-testi__eyebrow nk-center">' . esc_html( $s['eyebrow'] ) . '</p>';
			}
			echo nk_headline( esc_html( $s['strong'] ), esc_html( $s['muted'] ), false, $this->tag( $s, 'nk_tag', 'h2' ), 'nk-center nk-testi__title' ); // phpcs:ignore
			if ( ! $items ) {
				echo '<p class="nk-empty nk-center">' . esc_html__( 'No reviews yet — add some under Dashboard > Reviews > Add review.', 'nk-studio' ) . '</p></div>';
				$this->close();
				return;
			}
			echo '<div class="nk-testi__cards">';
			foreach ( $items as $it ) {
				$name = trim( (string) ( $it['name'] ?? '' ) );
				$line = $this->role_line( $it );
				$r    = min( 5, max( 0, (int) ( $it['rating'] ?? 0 ) ) );
				$ini  = function_exists( 'mb_substr' ) ? mb_strtoupper( mb_substr( $name, 0, 1 ) ) : strtoupper( substr( $name, 0, 1 ) );
				echo '<article class="nk-testi__card"><header class="nk-testi__head">';
				echo '<span class="nk-testi__avatar">' . ( ! empty( $it['avatar']['url'] ) ? $this->img( $it['avatar'], 'thumbnail' ) : '<i>' . esc_html( $ini ) . '</i>' ) . '</span>'; // phpcs:ignore
				echo '<span class="nk-testi__who"><span class="nk-testi__name">' . esc_html( $name ) . '</span>';
				if ( '' !== $line ) {
					echo '<span class="nk-testi__role">' . esc_html( $line ) . '</span>';
				}
				echo '</span></header><blockquote class="nk-testi__text">';
				if ( $r ) {
					echo '<span class="nk-testi__stars" aria-label="' . esc_attr( $r . '/5' ) . '">' . esc_html( str_repeat( '★', $r ) ) . '</span>';
				}
				echo '<p>“' . esc_html( (string) ( $it['quote'] ?? '' ) ) . '”</p></blockquote></article>';
			}
			echo '</div></div>';
			$this->close();
			return;
		}

		/* ---- old layout: logo tabs + one big quote + people row ---- */
		echo '<div class="nk-container" data-nk-testi data-autoplay="' . esc_attr( (int) ( $s['autoplay'] ?? 0 ) ) . '">';
		echo nk_headline( esc_html( $s['strong'] ), esc_html( $s['muted'] ), true, $this->tag( $s, 'nk_tag', 'h2' ), 'nk-center' ); // phpcs:ignore

		if ( ! $items ) {
			echo '<p class="nk-empty nk-center">' . esc_html__( 'No reviews yet — add some under Dashboard > Reviews > Add review.', 'nk-studio' ) . '</p></div>';
			$this->close();
			return;
		}

		echo '<div class="nk-testi__logos">';
		foreach ( $items as $i => $it ) {
			$company = (string) ( $it['company'] ?? '' );
			$label   = '' !== $company ? $company : (string) ( $it['name'] ?? '' );
			$inner   = ! empty( $it['logo']['url'] ) ? $this->img( $it['logo'], 'medium' ) : '<span>' . esc_html( $label ) . '</span>';
			echo '<button type="button" class="nk-testi__logo' . ( 0 === $i ? ' is-active' : '' ) . '" data-i="' . (int) $i . '" aria-label="' . esc_attr( $label ) . '">' . $inner . '</button>'; // phpcs:ignore
		}
		echo '</div><div class="nk-testi__stage">';
		foreach ( $items as $i => $it ) {
			$r = min( 5, max( 0, (int) ( $it['rating'] ?? 0 ) ) );
			echo '<blockquote class="nk-testi__quote' . ( 0 === $i ? ' is-active' : '' ) . '" data-i="' . (int) $i . '">';
			if ( $r ) {
				echo '<span class="nk-testi__stars" aria-label="' . esc_attr( $r . '/5' ) . '">' . esc_html( str_repeat( '★', $r ) ) . '</span>';
			}
			echo '<p>' . esc_html( $it['quote'] ?? '' ) . '</p></blockquote>';
		}
		echo '</div><div class="nk-testi__people">';
		foreach ( $items as $i => $it ) {
			echo '<button type="button" class="nk-testi__person' . ( 0 === $i ? ' is-active' : '' ) . '" data-i="' . (int) $i . '">';
			echo '<span class="nk-testi__avatar">' . ( ! empty( $it['avatar']['url'] ) ? $this->img( $it['avatar'], 'thumbnail' ) : '<i></i>' ) . '</span>'; // phpcs:ignore
			echo '<span class="nk-testi__name">' . esc_html( $it['name'] ?? '' ) . '</span><span class="nk-testi__role">' . esc_html( $it['role'] ?? '' ) . '</span></button>';
		}
		echo '</div></div>';
		$this->close();
	}
}
