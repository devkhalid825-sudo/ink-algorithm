<?php
/** Generic loop: blog home, archives, search. */
get_header();

/* Elementor Pro Theme Builder "Archive" template wins when the site has one (blog home / category / tag / search). */
if ( ( is_archive() || is_home() || is_search() ) && function_exists( 'nk_theme_builder_location' ) && nk_theme_builder_location( 'archive' ) ) {
	get_footer();
	return;
}
?>
<section class="nk-section nk-theme-light nk-archive" data-nk-theme="light">
	<div class="nk-container">
		<?php
		if ( is_home() ) {
			$title = get_the_title( get_option( 'page_for_posts' ) ) ?: __( 'Insights', 'nk-studio' );
		} elseif ( is_search() ) {
			$title = sprintf( __( 'Search: %s', 'nk-studio' ), get_search_query() );
		} else {
			$title = get_the_archive_title();
		}
		echo nk_headline( esc_html( $title ), '', true, 'h1' ); // phpcs:ignore
		if ( is_category() || is_tag() || is_tax() ) {
			the_archive_description( '<div class="nk-prose nk-archive__desc">', '</div>' );
		}
		?>
		<div class="nk-cards">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) :
					the_post();
					nk_render_post_card( get_the_ID() );
				endwhile;
			else :
				echo '<p>' . esc_html__( 'Nothing found.', 'nk-studio' ) . '</p>';
			endif;
			?>
		</div>
		<div class="nk-pagination"><?php the_posts_pagination(); ?></div>
	</div>
</section>
<?php get_footer();
