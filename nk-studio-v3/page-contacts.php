<?php
/** Contact layout. Elementor owns the widget settings; this template owns the grid. */
defined( 'ABSPATH' ) || exit;

if ( ! did_action( 'elementor/loaded' ) || isset( $_GET['elementor-preview'] ) ) {
	require __DIR__ . '/page.php';
	return;
}
$document = \Elementor\Plugin::$instance->documents->get( get_queried_object_id() );
$widgets = array();
$collect = static function ( $nodes ) use ( &$collect, &$widgets ) {
	foreach ( $nodes as $node ) {
		if ( ! empty( $node['widgetType'] ) ) {
			$widgets[] = $node;
		} elseif ( ! empty( $node['elements'] ) ) {
			$collect( $node['elements'] );
		}
	}
};
if ( $document ) {
	$collect( $document->get_elements_data() );
}
$form_index = array_search( 'neuros_wpforms', array_column( $widgets, 'widgetType' ), true );
if ( false === $form_index ) {
	require __DIR__ . '/page.php';
	return;
}
$render = static function ( $nodes ) {
	foreach ( $nodes as $node ) {
		$widget = \Elementor\Plugin::$instance->elements_manager->create_element_instance( $node );
		if ( $widget ) {
			$widget->print_element();
		}
	}
};
get_header();
?>
<section class="nk-contact-layout" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
	<div class="nk-contact-layout__row">
		<div>
			<?php $render( array_slice( $widgets, 0, 2 ) ); ?>
			<div class="nk-contact-layout__details">
				<?php foreach ( array_chunk( array_slice( $widgets, 2, max( 0, $form_index - 2 ) ), 2 ) as $pair ) : ?>
					<div><?php $render( $pair ); ?></div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="nk-contact-layout__form"><?php $render( array( $widgets[ $form_index ] ) ); ?></div>
	</div>
	<?php $render( array_slice( $widgets, $form_index + 1 ) ); ?>
</section>
<?php get_footer(); ?>
