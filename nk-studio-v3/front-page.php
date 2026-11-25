<?php
/** Front page: if a static page is set it renders via page.php logic; otherwise the latest posts. */
if ( 'page' === get_option( 'show_on_front' ) ) {
	require get_template_directory() . '/page.php';
	return;
}
require get_template_directory() . '/index.php';
