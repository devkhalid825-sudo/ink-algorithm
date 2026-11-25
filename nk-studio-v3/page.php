<?php
/** Pages: Elementor content renders edge-to-edge; classic content gets a readable container. */
get_header();
while ( have_posts() ) :
	the_post();
	if ( nk_is_elementor_page() ) {
		the_content();
	} elseif ( is_front_page() && '' === trim( wp_strip_all_tags( get_the_content() ) ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			echo '<section class="nk-section nk-theme-light" style="min-height:70vh;display:grid;place-items:center;text-align:center"><div><h1 class="nk-headline">Home is empty</h1><p><a class="nk-link" href="' . esc_url( admin_url( 'themes.php?page=nk-setup' ) ) . '"><span>Open NK Studio Setup &rarr; Re-import demo pages</span></a></p></div></section>';
		}
	} else {
		echo '<article class="nk-prose"><h1 class="nk-headline">' . esc_html( get_the_title() ) . '</h1>';
		the_content();
		echo '</article>';
	}
endwhile;
get_footer();
