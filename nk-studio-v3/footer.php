<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
</main>

<?php
$nk_arrow = '<span class="ink-btn__icon" aria-hidden="true"><svg viewBox="0 0 12 12" fill="none"><path d="M4.5 2.5 8 6l-3.5 3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';
$nk_tag   = trim( (string) nk_opt( 'nk_footer_big_logo', 'nk' ) );
if ( '' === $nk_tag || 'nk' === strtolower( $nk_tag ) ) {
	$nk_tag = 'Studio';
}
$nk_img = nk_use_logo_images() ? (int) get_theme_mod( 'nk_footer_logo_img' ) : 0;
$nk_news = (string) nk_opt( 'nk_news_action' );
?>
<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) : ?>
<footer class="nk-footer ink-footer" data-nk-theme="dark">
	<div class="ink-footer__wrap">

		<!-- 1. call to action + newsletter -->
		<div class="ink-footer__top<?php echo $nk_news ? '' : ' ink-footer__top--solo'; ?>">
			<div class="ink-footer__cta">
				<?php echo nk_headline( esc_html( nk_opt( 'nk_footer_headline' ) ), esc_html( nk_opt( 'nk_footer_muted' ) ), false, 'h2', 'nk-footer__headline ink-footer__headline' ); // phpcs:ignore ?>
				<div class="ink-footer__btns">
					<a class="ink-btn ink-btn--light" href="<?php echo esc_url( nk_resolve_url( nk_opt( 'nk_header_cta_url', '/contact/' ) ) ); ?>"><?php echo esc_html( nk_opt( 'nk_header_cta_text', 'Get Started' ) ); ?><?php echo $nk_arrow; // phpcs:ignore ?></a>
					<?php if ( nk_opt( 'nk_footer_email' ) ) : ?>
						<a class="ink-btn ink-btn--glass" href="mailto:<?php echo esc_attr( nk_opt( 'nk_footer_email' ) ); ?>"><?php echo esc_html( nk_opt( 'nk_footer_email' ) ); ?></a>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( $nk_news ) : // v3.8.3: the newsletter box is shown only when a subscribe URL is set (Customize > NK Footer) ?>
			<div class="ink-footer__news">
				<div class="ink-footer__newstitle"><?php echo esc_html( nk_opt( 'nk_news_logo', 'nk.news' ) ); ?></div>
				<p><?php echo esc_html( nk_opt( 'nk_news_text' ) ); ?></p>
				<?php $action = nk_opt( 'nk_news_action' ); ?>
				<form class="ink-news-form" <?php echo $action ? 'action="' . esc_url( $action ) . '" method="post" target="_blank"' : 'onsubmit="return false"'; ?>>
					<input type="email" name="EMAIL" required placeholder="<?php echo esc_attr( nk_opt( 'nk_news_placeholder', 'YOUR EMAIL HERE' ) ); ?>" aria-label="Email">
					<button type="submit" aria-label="<?php esc_attr_e( 'Subscribe', 'nk-studio' ); ?>"><svg viewBox="0 0 16 16" width="16" height="16"><path d="M2 8h11M9 3.5 13.5 8 9 12.5" fill="none" stroke="currentColor" stroke-width="1.8"/></svg></button>
				</form>
				<small><?php echo wp_kses_post( nk_opt( 'nk_news_note' ) ); ?></small>
			</div>
			<?php endif; ?>
		</div>

	</div>

	<!-- v3.7.4: everything below the line under the "Get Started" block is solid black, white text -->
	<div class="ink-footer__black">
	<div class="ink-footer__wrap ink-footer__wrap--low">

		<!-- 2. brand + link columns -->
		<div class="ink-footer__mid">
			<div class="ink-footer__brand">
				<?php if ( $nk_img ) : ?>
					<a class="nk-footer__biglogo" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo wp_get_attachment_image( $nk_img, 'full' ); // phpcs:ignore ?></a>
				<?php else : ?>
					<a class="ink-footmark" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( nk_brand_text() ); ?>">
						<?php if ( nk_use_logo_images() ) : ?><span class="ink-dock" data-ink3d-dock aria-hidden="true"></span><?php endif; ?>
						<span class="ink-footmark__text">
							<span class="ink-footmark__word"><?php echo esc_html( nk_brand_text() ); ?><sup>®</sup></span>
							<span class="ink-footmark__tag"><?php echo esc_html( $nk_tag ); ?></span>
						</span>
					</a>
				<?php endif; ?>
				<?php if ( nk_opt( 'nk_footer_text' ) ) : ?>
					<p class="ink-footer__text"><?php echo esc_html( nk_opt( 'nk_footer_text' ) ); ?></p>
				<?php endif; ?>
			</div>

			<div class="ink-footer__cols">
				<?php
				$nk_cols  = nk_footer_columns();
				$nk_menu  = false;
				for ( $i = 1; $i <= 5; $i++ ) {
					$loc = 'footer_' . $i;
					if ( ! has_nav_menu( $loc ) ) {
						continue;
					}
					$nk_menu   = true;
					$locations = get_nav_menu_locations();
					$menu      = wp_get_nav_menu_object( $locations[ $loc ] );
					echo '<div class="ink-footer__col"><h3>' . esc_html( $menu ? $menu->name : '' ) . '</h3>';
					wp_nav_menu( array( 'theme_location' => $loc, 'container' => false, 'depth' => 1, 'items_wrap' => '<ul>%3$s</ul>' ) );
					echo '</div>';
				}
				if ( ! $nk_menu ) : // no footer menu assigned: contact + sitemap + quick links of the company
					?>
					<div class="ink-footer__col ink-footer__col--info">
						<h3><?php echo esc_html( $nk_cols['touch']['title'] ); ?></h3>
						<?php if ( $nk_cols['touch']['phone'] ) : ?><p><a href="tel:<?php echo esc_attr( preg_replace( '/[^+\d]/', '', $nk_cols['touch']['phone'] ) ); ?>"><?php echo esc_html( $nk_cols['touch']['phone'] ); ?></a></p><?php endif; ?>
						<?php if ( $nk_cols['touch']['email'] ) : ?><h3 class="ink-footer__sub"><?php esc_html_e( 'Email', 'nk-studio' ); ?></h3><p><a href="mailto:<?php echo esc_attr( $nk_cols['touch']['email'] ); ?>"><?php echo esc_html( $nk_cols['touch']['email'] ); ?></a></p><?php endif; ?>
					</div>
					<?php if ( $nk_cols['location']['text'] ) : ?>
					<div class="ink-footer__col ink-footer__col--info">
						<h3><?php echo esc_html( $nk_cols['location']['title'] ); ?></h3>
						<p><?php echo esc_html( $nk_cols['location']['text'] ); ?></p>
					</div>
					<?php endif; ?>
					<?php foreach ( array( 'sitemap', 'quick' ) as $nk_k ) : if ( empty( $nk_cols[ $nk_k ]['links'] ) ) { continue; } ?>
					<div class="ink-footer__col">
						<h3><?php echo esc_html( $nk_cols[ $nk_k ]['title'] ); ?></h3>
						<ul>
							<?php foreach ( $nk_cols[ $nk_k ]['links'] as $nk_l ) : ?>
								<li><a href="<?php echo esc_url( $nk_l[1] ); ?>"<?php echo 0 === strpos( $nk_l[1], home_url() ) ? '' : ' target="_blank" rel="noopener"'; ?>><?php echo esc_html( $nk_l[0] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>

		<!-- 3. bottom bar -->
		<div class="ink-footer__bar">
			<small class="ink-footer__copy"><?php echo esc_html( nk_opt( 'nk_copyright' ) ); ?></small>
			<?php if ( nk_opt( 'nk_members_text' ) ) : ?><p class="ink-footer__members"><?php echo esc_html( nk_opt( 'nk_members_text' ) ); ?></p><?php endif; ?>
			<div class="ink-footer__right">
				<div class="ink-footer__social">
					<?php
					foreach ( array( 'web', 'instagram', 'linkedin', 'x', 'behance', 'facebook', 'youtube' ) as $k ) {
						$u = nk_social_url( $k );
						if ( $u ) {
							echo '<a href="' . esc_url( $u ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( $k ) . '">' . nk_social_icon( $k ) . '</a>'; // phpcs:ignore
						}
					}
					?>
				</div>
				<?php if ( nk_opt( 'nk_lang_label' ) ) : ?>
					<span class="ink-footer__lang"><?php echo esc_html( nk_opt( 'nk_lang_label' ) ); ?></span>
				<?php endif; ?>
				<a class="ink-footer__top-btn nk-backtop" href="#" aria-label="<?php esc_attr_e( 'Back to top', 'nk-studio' ); ?>"><svg viewBox="0 0 16 16" width="14" height="14"><path d="M8 13V3M3.5 7.5 8 3l4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.8"/></svg></a>
			</div>
		</div>
	</div>

	<!-- 4. giant wordmark -->
	<div class="ink-footer__giant" aria-hidden="true"><span data-ink-chars><?php echo esc_html( nk_brand_text() ); ?></span></div>
	</div>
</footer>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
