<?php get_header(); ?>
<section class="nk-section nk-theme-light" data-nk-theme="light" style="min-height:80vh;display:grid;place-items:center;text-align:center">
	<div>
		<?php echo nk_headline( '404', esc_html__( 'Page not found', 'nk-studio' ), true, 'h1' ); // phpcs:ignore ?>
		<p><?php echo nk_link( __( 'BACK HOME', 'nk-studio' ), home_url( '/' ) ); // phpcs:ignore ?></p>
	</div>
</section>
<?php get_footer();
