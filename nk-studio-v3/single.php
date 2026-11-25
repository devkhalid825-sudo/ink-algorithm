<?php
get_header();

/* Elementor Pro Theme Builder "Single" template (if the site has one) wins over the theme template. */
if ( function_exists( 'nk_theme_builder_location' ) && nk_theme_builder_location( 'single' ) ) {
	get_footer();
	return;
}

while ( have_posts() ) :
	the_post();
	if ( nk_is_elementor_page() ) {
		the_content();
		continue;
	}
	?>
	<article <?php post_class( 'nk-section nk-theme-light nk-single' ); ?> data-nk-theme="light">
		<div class="nk-container nk-container--narrow">
			<?php echo nk_headline( esc_html( get_the_title() ), '', true, 'h1' ); // phpcs:ignore ?>
			<?php if ( 'nk_work' === get_post_type() ) : ?>
				<p class="nk-meta"><?php echo esc_html( get_post_meta( get_the_ID(), '_nk_sector', true ) ); ?></p>
			<?php else : ?>
				<p class="nk-meta"><?php echo esc_html( get_the_date() ); ?> · <?php echo wp_kses_post( get_the_category_list( ', ' ) ); ?></p>
			<?php endif; ?>
			<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large', array( 'class' => 'nk-single__img' ) ); } ?>
			<div class="nk-prose"><?php the_content(); ?></div>
			<?php
			$nk_ext = 'nk_work' === get_post_type() ? get_post_meta( get_the_ID(), '_nk_link', true ) : '';
			if ( $nk_ext ) {
				echo '<p class="nk-mt">' . nk_link( __( 'VISIT PROJECT', 'nk-studio' ), array( 'url' => $nk_ext, 'is_external' => true ) ) . '</p>'; // phpcs:ignore
			}
			if ( 'post' === get_post_type() ) {
				// original blog behaviour: tags + previous / next article + comments when the post allows them
				the_tags( '<p class="nk-meta nk-tags">', ', ', '</p>' );
				the_post_navigation( array(
					'prev_text' => '<span class="nk-meta">' . esc_html__( 'Previous', 'nk-studio' ) . '</span> %title',
					'next_text' => '<span class="nk-meta">' . esc_html__( 'Next', 'nk-studio' ) . '</span> %title',
				) );
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
			}
			?>
		</div>
	</article>
	<?php
endwhile;
get_footer();
