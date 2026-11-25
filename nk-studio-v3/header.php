<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'nk-body' ); ?>>
<?php wp_body_open(); ?>
<div class="nk-cursor" aria-hidden="true"><i></i></div>

<div class="ink-bg" aria-hidden="true"></div>
<div class="ink-grain" aria-hidden="true"></div>

<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) : ?>
<header class="nk-header ink-header" id="nk-header" data-theme="dark">
	<div class="nk-header__inner ink-header__row">
		<a class="ink-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php
			if ( nk_use_logo_images() && has_custom_logo() ) {
				echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'full', false, array( 'class' => 'nk-logo__img', 'alt' => esc_attr( get_bloginfo( 'name' ) ) ) ); // phpcs:ignore
			} else {
				echo '<span>' . esc_html( nk_brand_text() ) . '</span>';
			}
			?>
		</a>

		<div class="ink-header__right">
			<nav class="ink-nav" aria-label="<?php esc_attr_e( 'Primary', 'nk-studio' ); ?>">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'depth'          => 2,
					'items_wrap'     => '<ul>%3$s</ul>',
					'fallback_cb'    => 'nk_primary_nav_fallback',
				) );
				?>
			</nav>

			<?php $cta = nk_opt( 'nk_header_cta_text', 'Get Started' ); if ( $cta ) : ?>
				<a class="ink-btn ink-btn--dark" href="<?php echo esc_url( nk_resolve_url( nk_opt( 'nk_header_cta_url', '/contact/' ) ) ); ?>">
					<?php echo esc_html( $cta ); ?>
					<span class="ink-btn__icon" aria-hidden="true"><svg viewBox="0 0 12 12" fill="none"><path d="M4.5 2.5 8 6l-3.5 3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
				</a>
			<?php endif; ?>

			<button class="nk-header__menu-btn ink-menu-btn" type="button" aria-expanded="false" aria-controls="nk-overlay"><?php echo esc_html( nk_opt( 'nk_menu_text', 'MENU' ) ); ?></button>
		</div>
	</div>
</header>

<div class="nk-overlay" id="nk-overlay" hidden>
	<button class="nk-overlay__close nk-ulink" type="button"><?php esc_html_e( 'CLOSE', 'nk-studio' ); ?></button>
	<nav class="nk-overlay__nav" aria-label="<?php esc_attr_e( 'Menu', 'nk-studio' ); ?>">
		<?php
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'container'      => false,
			'depth'          => 2,
			'items_wrap'     => '<ul>%3$s</ul>',
			'fallback_cb'    => 'nk_primary_nav_fallback',
		) );
		?>
	</nav>
</div>
<?php endif; ?>

<main id="nk-main" class="nk-main">
