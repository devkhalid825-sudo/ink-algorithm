<?php
namespace NK_Studio\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Strategic Pillars — the 3 cards on Home ("Web & E-Commerce", "3D Product Configurators", "Custom Solutions").
 * Picture (or a built-in visual) + text under it.
 *
 * v3.2: every card is editable in Elementor — click a card title / text in the preview and type (inline editing),
 * or open Content > Cards in the panel. The preview updates instantly (JS template below).
 * The built-in card pictures are re-pointed to this theme's own files, so they never show as broken images.
 */
class Features extends \NK_Studio\Base {
	public function get_name() { return 'nk_features'; }
	public function get_title() { return __( 'NK Strategic Pillars (3 cards)', 'nk-studio' ); }
	public function get_icon() { return 'eicon-gallery-grid'; }
	public function get_keywords() { return array( 'features', 'pillars', 'services', 'cards', 'nk' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'nk-studio' ) ) );

		$this->add_control( 'tag', array(
			'label'       => __( 'Small label above the headline (empty = hide)', 'nk-studio' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Strategic Pillars',
			'label_block' => true,
		) );

		$this->add_control( 'title', array(
			'label'       => __( 'Headline', 'nk-studio' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Built to move brands forward.',
			'label_block' => true,
		) );
		$this->tag_control( 'title_tag', 'h2' );

		$this->add_control( 'subtitle', array(
			'label'   => __( 'Subtitle', 'nk-studio' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 2,
			'default' => 'Premium web, immersive 3D and smart automation, crafted with precision for brands that lead.',
		) );
		$this->add_control( 'show_label', array( 'label' => __( 'Show small label above the headline', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => '' ) );
		$this->add_control( 'show_sub', array( 'label' => __( 'Show subtitle', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => '' ) );
		$this->add_control( 'highlight', array(
			'label'       => __( 'Glowing words in the headline (must match the headline text)', 'nk-studio' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'brands forward.',
			'label_block' => true,
			'description' => __( 'These words rise in last on the live site. Empty = none.', 'nk-studio' ),
		) );

		$rep = new Repeater();
		$rep->add_control( 'image', array(
			'label'       => __( 'Picture (remove it = built-in visual)', 'nk-studio' ),
			'type'        => Controls_Manager::MEDIA,
		) );
		$rep->add_control( 'glow_type', array(
			'label'   => __( 'Built-in visual (when no picture)', 'nk-studio' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'sphere',
			'options' => array(
				'sphere' => __( 'Sphere with orange ring', 'nk-studio' ),
				'wave'   => __( 'Orange particle wave', 'nk-studio' ),
				'disc'   => __( 'Glass disc / rim glow', 'nk-studio' ),
			),
		) );
		$rep->add_control( 'title', array(
			'label'       => __( 'Card title', 'nk-studio' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Card title',
			'label_block' => true,
		) );
		$rep->add_control( 'desc', array(
			'label'   => __( 'Card text', 'nk-studio' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 3,
			'default' => 'Card description goes here.',
		) );
		$rep->add_control( 'tags', array(
			'label'       => __( 'Pill tags (comma-separated, optional)', 'nk-studio' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
		) );
		$rep->add_control( 'link_text', array(
			'label'   => __( 'Link text (optional, e.g. View details →)', 'nk-studio' ),
			'type'    => Controls_Manager::TEXT,
			'default' => '',
		) );
		$rep->add_control( 'link', array(
			'label' => __( 'Card link (optional)', 'nk-studio' ),
			'type'  => Controls_Manager::URL,
		) );

		$this->add_control( 'cards', array(
			'label'       => __( 'Cards', 'nk-studio' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'default'     => array(
				array(
					'glow_type' => 'sphere',
					'image'     => array( 'url' => NK_URI . '/assets/img/pillar-1.jpg' ),
					'title'     => 'Web & E-Commerce',
					'desc'      => 'Fast, scalable storefronts built for high-volume brands.',
				),
				array(
					'glow_type' => 'wave',
					'image'     => array( 'url' => NK_URI . '/assets/img/pillar-2.jpg' ),
					'title'     => '3D Product Configurators',
					'desc'      => 'Real-time 3D experiences that let customers build and see.',
				),
				array(
					'glow_type' => 'disc',
					'image'     => array( 'url' => NK_URI . '/assets/img/pillar-3.jpg' ),
					'title'     => 'Custom Solutions',
					'desc'      => 'Pricing engines, dealer portals and automation, tailored to you.',
				),
			),
		) );
		$this->tag_control( 'card_tag', 'h3', __( 'Card title HTML tag', 'nk-studio' ) );

		$this->add_control( 'fx_heading', array( 'label' => __( 'Motion', 'nk-studio' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_control( 'tilt', array( 'label' => __( '3D tilt + light follows the mouse', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'stagger', array( 'label' => __( 'Middle card lower (editorial rhythm) + scroll parallax', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'numbers', array( 'label' => __( 'Number + arrow on the pictures', 'nk-studio' ), 'type' => Controls_Manager::SWITCHER, 'default' => '' ) );
		$this->add_control( 'cta_text', array( 'label' => __( 'Button under the cards (empty = hide)', 'nk-studio' ), 'type' => Controls_Manager::TEXT, 'default' => '', 'separator' => 'before' ) );
		$this->add_control( 'cta_link', array( 'label' => __( 'Button link', 'nk-studio' ), 'type' => Controls_Manager::URL ) );

		$this->end_controls_section();

		/* ---- card style ---- */
		$this->start_controls_section( 'card_style', array( 'label' => __( 'Cards', 'nk-studio' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'cols', array(
			'label'          => __( 'Columns', 'nk-studio' ),
			'type'           => Controls_Manager::SELECT,
			'default'        => '3',
			'tablet_default' => '3',
			'mobile_default' => '1',
			'options'        => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4' ),
			'selectors'      => array( '{{WRAPPER}} .nk-features__grid' => '--nk-pc-cols: {{VALUE}};' ),
		) );
		$this->add_responsive_control( 'gap', array(
			'label'      => __( 'Gap between cards', 'nk-studio' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 120 ) ),
			'selectors'  => array( '{{WRAPPER}} .nk-features__grid' => 'gap: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_control( 'ratio', array(
			'label'     => __( 'Picture shape', 'nk-studio' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => '4 / 4.3',
			'options'   => array( '4 / 4.3' => __( 'Tall (default)', 'nk-studio' ), '1 / 1' => __( 'Square', 'nk-studio' ), '4 / 3' => '4:3', '3 / 4' => '3:4', '16 / 9' => '16:9' ),
			'selectors' => array( '{{WRAPPER}} .nk-pc__media' => 'aspect-ratio: {{VALUE}};' ),
		) );
		$this->add_responsive_control( 'radius', array(
			'label'      => __( 'Picture corner radius', 'nk-studio' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
			'selectors'  => array( '{{WRAPPER}} .nk-pc__media' => 'border-radius: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'card_title_typo',
			'label'    => __( 'Card title typography', 'nk-studio' ),
			'selector' => '{{WRAPPER}} .nk-feature-card__title',
		) );
		$this->add_control( 'card_title_color', array( 'label' => __( 'Card title colour', 'nk-studio' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .nk-feature-card__title' => 'color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'card_desc_typo',
			'label'    => __( 'Card text typography', 'nk-studio' ),
			'selector' => '{{WRAPPER}} .nk-feature-card__desc',
		) );
		$this->add_control( 'card_desc_color', array( 'label' => __( 'Card text colour', 'nk-studio' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .nk-feature-card__desc' => 'color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'nk_title_typo',
			'label'    => __( 'Headline typography', 'nk-studio' ),
			'selector' => '{{WRAPPER}} .nk-features__title',
		) );
		$this->add_control( 'nk_title_color', array( 'label' => __( 'Headline colour', 'nk-studio' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .nk-features__title' => 'color: {{VALUE}};' ) ) );
		$this->add_control( 'nk_sub_color', array( 'label' => __( 'Subtitle colour', 'nk-studio' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .nk-features__sub' => 'color: {{VALUE}};' ) ) );
		$this->end_controls_section();

		$this->appearance_controls( 'dark', false );
	}

	/**
	 * The picture to show, or null. The built-in pictures (assets/img/pillar-1/2/3.jpg) are saved on the page with the
	 * full theme URL; after the theme folder or the domain / http(s) changes that URL broke. It is now re-pointed to
	 * this theme's own file, so the pictures always show.
	 */
	public static function resolve_image( $media ) {
		if ( empty( $media['url'] ) ) {
			return null;
		}
		if ( empty( $media['id'] ) && preg_match( '#/assets/img/(pillar-[0-9]+\.(?:jpe?g|png|webp))(?:\?.*)?$#i', $media['url'], $m ) ) {
			if ( ! file_exists( NK_DIR . '/assets/img/' . $m[1] ) ) {
				return null;
			}
			$media['url'] = NK_URI . '/assets/img/' . $m[1];
		}
		return $media;
	}

	private function render_preset_visual( $type ) {
		switch ( $type ) {
			case 'wave':
				return '<div class="nk-feature-visual nk-feature-visual--wave"><div class="nk-feature-visual__wave-mesh"></div></div>';
			case 'disc':
				return '<div class="nk-feature-visual nk-feature-visual--disc"><div class="nk-feature-visual__disc-lens"></div></div>';
			case 'sphere':
			default:
				return '<div class="nk-feature-visual nk-feature-visual--sphere"><div class="nk-feature-visual__sphere-orb"></div><div class="nk-feature-visual__orbit-ring"></div></div>';
		}
	}


	private static function arrow_svg() {
		return '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	}

	/**
	 * v3.3 premium layout:
	 *  - eyebrow (01 — 03 / label) with a drawing line, headline words rise from a mask, glowing highlight words
	 *    with a drawn underline, subtitle fades up
	 *  - tall glass cards: 3D tilt + light that follow the mouse, picture parallax inside the card, number, arrow,
	 *    tags, a line that fills on hover; cards rise in one after another; middle card sits lower + scroll parallax
	 *  - phones: a swipe carousel (cards snap)
	 * Motion: assets/js/nk-pillars.js · styles: assets/css/nk-pillars.css
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$htag  = $this->tag( $s, 'title_tag', 'h2' );
		$ctag  = $this->tag( $s, 'card_tag', 'h3' );
		$cards = array_values( is_array( $s['cards'] ?? null ) ? $s['cards'] : array() );
		$n     = count( $cards );
		$flags = 'nk-features nk-section--dark nk-pillars-x'
			. ( 'yes' === ( $s['stagger'] ?? 'yes' ) ? ' nk-pillars-x--stagger' : '' )
			. ( 'yes' === ( $s['numbers'] ?? '' ) ? '' : ' nk-pillars-x--nonum' );

		// the section opens with data attributes for the motion script
		ob_start();
		$this->open( $s, $flags );
		$open = ob_get_clean();
		$open = preg_replace( '/^<section /', '<section data-nk-pillars data-tilt="' . ( 'yes' === ( $s['tilt'] ?? 'yes' ) ? '1' : '0' ) . '" data-highlight="' . esc_attr( (string) ( $s['highlight'] ?? '' ) ) . '" ', $open, 1 );
		echo $open; // phpcs:ignore

		echo '<span class="nk-pc__glow" aria-hidden="true"><span class="nk-pc__aura"></span></span>';
		echo '<div class="nk-container">';

		echo '<div class="nk-features__head nk-pc__head">';
		if ( ! empty( $s['tag'] ) && 'yes' === ( $s['show_label'] ?? '' ) ) {
			$this->add_render_attribute( 'tag', 'class', 'nk-pc__eyebrow-label' );
			$this->add_inline_editing_attributes( 'tag', 'none' );
			echo '<div class="nk-pc__eyebrow"><span class="nk-pc__count">(01 — ' . esc_html( str_pad( (string) max( 1, $n ), 2, '0', STR_PAD_LEFT ) ) . ')</span><i class="nk-pc__rule"></i><span ' . $this->get_render_attribute_string( 'tag' ) . '>' . esc_html( $s['tag'] ) . '</span></div>'; // phpcs:ignore
		}
		if ( ! empty( $s['title'] ) ) {
			$this->add_render_attribute( 'title', 'class', 'nk-features__title nk-pc__title' );
			$this->add_inline_editing_attributes( 'title', 'none' );
			echo '<' . $htag . ' ' . $this->get_render_attribute_string( 'title' ) . '>' . esc_html( $s['title'] ) . '</' . $htag . '>'; // phpcs:ignore
		}
		if ( ! empty( $s['subtitle'] ) && 'yes' === ( $s['show_sub'] ?? '' ) ) {
			$this->add_render_attribute( 'subtitle', 'class', 'nk-features__sub nk-pc__sub' );
			$this->add_inline_editing_attributes( 'subtitle', 'none' );
			echo '<p ' . $this->get_render_attribute_string( 'subtitle' ) . '>' . esc_html( $s['subtitle'] ) . '</p>'; // phpcs:ignore
		}
		echo '</div>';

		if ( $cards ) {
			echo '<div class="nk-features__grid nk-pc__grid">';
			foreach ( $cards as $i => $c ) {
				$has_link = ! empty( $c['link']['url'] );
				$el       = $has_link ? 'a' : 'div';
				$href     = '';
				if ( $has_link ) {
					$href = ' href="' . esc_url( $c['link']['url'] ) . '"' . ( ! empty( $c['link']['is_external'] ) ? ' target="_blank" rel="noopener"' : '' );
				}
				echo '<' . $el . ' class="nk-feature-card nk-pc elementor-repeater-item-' . esc_attr( $c['_id'] ?? '' ) . '" style="--i:' . (int) $i . '"' . $href . '>'; // phpcs:ignore
				echo '<div class="nk-pc__frame">';
				echo '<div class="nk-pc__media">';
				$pic = self::resolve_image( $c['image'] ?? array() );
				if ( $pic ) {
					echo $this->img( $pic, 'large' ); // phpcs:ignore
				} else {
					echo $this->render_preset_visual( $c['glow_type'] ?? 'sphere' ); // phpcs:ignore
				}
				echo '<span class="nk-pc__shade" aria-hidden="true"></span>';
				if ( 'yes' === ( $s['numbers'] ?? '' ) ) {
					echo '<span class="nk-pc__num">' . esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ) . '</span>';
					echo '<span class="nk-pc__arrow">' . self::arrow_svg() . '</span>'; // phpcs:ignore
				}
				echo '</div>';

				echo '<div class="nk-feature-card__body nk-pc__body">';
				if ( ! empty( $c['title'] ) ) {
					$k = $this->get_repeater_setting_key( 'title', 'cards', $i );
					$this->add_render_attribute( $k, 'class', 'nk-feature-card__title nk-pc__ttl' );
					$this->add_inline_editing_attributes( $k, 'none' );
					echo '<' . $ctag . ' ' . $this->get_render_attribute_string( $k ) . '>' . esc_html( $c['title'] ) . '</' . $ctag . '>'; // phpcs:ignore
				}
				if ( ! empty( $c['desc'] ) ) {
					$k = $this->get_repeater_setting_key( 'desc', 'cards', $i );
					$this->add_render_attribute( $k, 'class', 'nk-feature-card__desc nk-pc__desc' );
					$this->add_inline_editing_attributes( $k, 'none' );
					echo '<p ' . $this->get_render_attribute_string( $k ) . '>' . esc_html( $c['desc'] ) . '</p>'; // phpcs:ignore
				}
				if ( ! empty( $c['tags'] ) ) {
					echo '<div class="nk-feature-card__tags nk-pc__tags">';
					foreach ( array_filter( array_map( 'trim', explode( ',', $c['tags'] ) ) ) as $t ) {
						echo '<span class="nk-feature-tag">' . esc_html( $t ) . '</span>';
					}
					echo '</div>';
				}
				if ( ! empty( $c['link_text'] ) ) {
					echo '<span class="nk-feature-card__link nk-pc__more">' . esc_html( $c['link_text'] ) . '</span>';
				}
				echo '<i class="nk-pc__line" aria-hidden="true"></i>';
				echo '</div>';

				echo '<span class="nk-pc__glare" aria-hidden="true"></span>';
				echo '</div>';
				echo '</' . $el . '>'; // phpcs:ignore
			}
			echo '</div>';
			if ( $n > 1 ) {
				echo '<div class="nk-pc__dots" aria-hidden="true">' . str_repeat( '<i></i>', $n ) . '</div>'; // phpcs:ignore
			}
		}

		if ( ! empty( $s['cta_text'] ) ) {
			echo '<div class="nk-pc__cta">' . nk_link( $s['cta_text'], $this->link_attrs( $s['cta_link'] ?? array() ) ) . '</div>'; // phpcs:ignore
		}

		echo '</div>';
		$this->close();
	}

	/** Elementor editor: same markup, instant preview + inline editing. */
	protected function content_template() {
		$base = esc_js( NK_URI . '/assets/img/' );
		?>
		<#
		var tags = <?php echo wp_json_encode( array_keys( self::tag_options() ) ); ?>;
		var htag = tags.indexOf( settings.title_tag ) > -1 ? settings.title_tag : 'h2';
		var ctag = tags.indexOf( settings.card_tag ) > -1 ? settings.card_tag : 'h3';
		var theme = settings.nk_theme || 'dark';
		var cards = settings.cards || [];
		function pad( n ) { return ( n < 10 ? '0' : '' ) + n; }
		function nkVisual( t ) {
			if ( 'wave' === t ) { return '<div class="nk-feature-visual nk-feature-visual--wave"><div class="nk-feature-visual__wave-mesh"></div></div>'; }
			if ( 'disc' === t ) { return '<div class="nk-feature-visual nk-feature-visual--disc"><div class="nk-feature-visual__disc-lens"></div></div>'; }
			return '<div class="nk-feature-visual nk-feature-visual--sphere"><div class="nk-feature-visual__sphere-orb"></div><div class="nk-feature-visual__orbit-ring"></div></div>';
		}
		var cls = 'nk-features nk-section--dark nk-pillars-x' + ( 'yes' === settings.stagger ? ' nk-pillars-x--stagger' : '' ) + ( 'yes' === settings.numbers ? '' : ' nk-pillars-x--nonum' );
		view.addRenderAttribute( 'tag', 'class', 'nk-pc__eyebrow-label' );
		view.addInlineEditingAttributes( 'tag', 'none' );
		view.addRenderAttribute( 'title', 'class', 'nk-features__title nk-pc__title' );
		view.addInlineEditingAttributes( 'title', 'none' );
		view.addRenderAttribute( 'subtitle', 'class', 'nk-features__sub nk-pc__sub' );
		view.addInlineEditingAttributes( 'subtitle', 'none' );
		#>
		<section data-nk-pillars data-tilt="{{ 'yes' === settings.tilt ? 1 : 0 }}" data-highlight="{{ settings.highlight }}" class="nk-section nk-w-nk_features nk-theme-{{ theme }} {{ cls }}" data-nk-theme="{{ theme }}">
			<span class="nk-pc__glow" aria-hidden="true"><span class="nk-pc__aura"></span></span>
			<div class="nk-container">
				<div class="nk-features__head nk-pc__head">
					<# if ( settings.tag && 'yes' === settings.show_label ) { #><div class="nk-pc__eyebrow"><span class="nk-pc__count">(01 — {{ pad( Math.max( 1, cards.length ) ) }})</span><i class="nk-pc__rule"></i><span {{{ view.getRenderAttributeString( 'tag' ) }}}>{{ settings.tag }}</span></div><# } #>
					<# if ( settings.title ) { #><{{ htag }} {{{ view.getRenderAttributeString( 'title' ) }}}>{{ settings.title }}</{{ htag }}><# } #>
					<# if ( settings.subtitle && 'yes' === settings.show_sub ) { #><p {{{ view.getRenderAttributeString( 'subtitle' ) }}}>{{ settings.subtitle }}</p><# } #>
				</div>
				<# if ( cards.length ) { #>
				<div class="nk-features__grid nk-pc__grid">
					<# _.each( cards, function( c, i ) {
						var url = c.image && c.image.url ? c.image.url : '';
						var pm = url && ! c.image.id ? url.match( /\/assets\/img\/(pillar-[0-9]+\.(jpe?g|png|webp))/i ) : null;
						if ( pm ) { url = '<?php echo $base; // phpcs:ignore ?>' + pm[1]; }
						var hasLink = c.link && c.link.url;
						var el = hasLink ? 'a' : 'div';
						var tk = view.getRepeaterSettingKey( 'title', 'cards', i );
						var dk = view.getRepeaterSettingKey( 'desc', 'cards', i );
						view.addRenderAttribute( tk, 'class', 'nk-feature-card__title nk-pc__ttl' );
						view.addInlineEditingAttributes( tk, 'none' );
						view.addRenderAttribute( dk, 'class', 'nk-feature-card__desc nk-pc__desc' );
						view.addInlineEditingAttributes( dk, 'none' );
					#>
					<{{ el }} class="nk-feature-card nk-pc elementor-repeater-item-{{ c._id }}" style="--i:{{ i }}"<# if ( hasLink ) { #> href="{{ c.link.url }}"<# } #>>
						<div class="nk-pc__frame">
							<div class="nk-pc__media">
								<# if ( url ) { #><img src="{{ url }}" alt=""><# } else { #>{{{ nkVisual( c.glow_type ) }}}<# } #>
								<span class="nk-pc__shade" aria-hidden="true"></span>
								<# if ( 'yes' === settings.numbers ) { #><span class="nk-pc__num">{{ pad( i + 1 ) }}</span>
								<span class="nk-pc__arrow"><?php echo self::arrow_svg(); // phpcs:ignore ?></span><# } #>
							</div>
							<div class="nk-feature-card__body nk-pc__body">
								<# if ( c.title ) { #><{{ ctag }} {{{ view.getRenderAttributeString( tk ) }}}>{{ c.title }}</{{ ctag }}><# } #>
								<# if ( c.desc ) { #><p {{{ view.getRenderAttributeString( dk ) }}}>{{ c.desc }}</p><# } #>
								<# if ( c.tags ) { #><div class="nk-feature-card__tags nk-pc__tags"><# _.each( c.tags.split( ',' ), function( t ) { t = t.trim(); if ( t ) { #><span class="nk-feature-tag">{{ t }}</span><# } } ); #></div><# } #>
								<# if ( c.link_text ) { #><span class="nk-feature-card__link nk-pc__more">{{ c.link_text }}</span><# } #>
								<i class="nk-pc__line" aria-hidden="true"></i>
							</div>
							<span class="nk-pc__glare" aria-hidden="true"></span>
						</div>
					</{{ el }}>
					<# } ); #>
				</div>
				<# if ( cards.length > 1 ) { #><div class="nk-pc__dots" aria-hidden="true"><# _.each( cards, function() { #><i></i><# } ); #></div><# } #>
				<# } #>
				<# if ( settings.cta_text ) { #><div class="nk-pc__cta"><a class="nk-link" href="{{ settings.cta_link && settings.cta_link.url ? settings.cta_link.url : '#' }}">{{ settings.cta_text }}</a></div><# } #>
			</div>
		</section>
		<?php
	}
}
