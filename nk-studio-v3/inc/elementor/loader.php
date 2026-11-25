<?php
/**
 * Registers the "NK Studio" widget category and all widgets.
 * To add a new component: drop a class file in inc/elementor/widgets/ and add its name to $widgets below.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'elementor/elements/categories_registered', function ( $manager ) {
	$manager->add_category( 'nk-studio', array(
		'title' => __( 'NK Studio', 'nk-studio' ),
		'icon'  => 'eicon-star',
	), 1 );
} );

add_action( 'elementor/widgets/register', function ( $widgets_manager ) {
	require_once __DIR__ . '/class-base.php';

	$widgets = array(
		'Ink_Hero'     => 'nk_ink_hero',
		'Video'        => 'nk_video',
		'Heading'      => 'nk_heading',
		'Hero'         => 'nk_hero',
		'Logo_Strip'   => 'nk_logo_strip',
		'Stats'        => 'nk_stats',
		'Statement'    => 'nk_statement',
		'Process'      => 'nk_process',
		'Faq'          => 'nk_faq',
		'Features'     => 'nk_features',
		'Pillars'      => 'nk_pillars',
		'Work_Grid'    => 'nk_work_grid',
		'Insights'     => 'nk_insights',
		'Testimonials' => 'nk_testimonials',
		'Social_Videos' => 'nk_social_videos',
		'Studio'       => 'nk_studio',
		'Button'       => 'nk_button',
		'Text_Block'   => 'nk_text_block',
		'Template'     => 'nk_template',
	);

	foreach ( $widgets as $class => $slug ) {
		$file = __DIR__ . '/widgets/' . $slug . '.php';
		if ( file_exists( $file ) ) {
			require_once $file;
			$fqcn = '\\NK_Studio\\Widgets\\' . $class;
			$widgets_manager->register( new $fqcn() );
		}
	}
} );

/* Assets also needed inside the Elementor editor/preview. */
add_action( 'elementor/preview/enqueue_styles', function () {
	wp_enqueue_style( 'nk-main' );
	wp_enqueue_style( 'nk-ink' );
	wp_enqueue_style( 'nk-pillars' );
} );
