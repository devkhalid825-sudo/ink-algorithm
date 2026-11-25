<?php
/**
 * /blogs/ in the NK dark design (see inc/blogs.php). Reads the real WordPress posts: every published article, newest first,
 * new articles appear automatically. Filter pills = categories; pagination = ?pg=N (works without JavaScript).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$per   = max( 3, (int) apply_filters( 'nk_blogs_per_page', 9 ) );
$paged = isset( $_GET['pg'] ) ? max( 1, absint( $_GET['pg'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
$topic = isset( $_GET['topic'] ) ? sanitize_title( wp_unslash( $_GET['topic'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$args  = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => $per,
	'paged'               => $paged,
	'ignore_sticky_posts' => true,
);
if ( $topic ) {
	$args['category_name'] = $topic;
}
$q    = new WP_Query( $args );
$base = get_permalink( get_queried_object_id() );
$intro = has_excerpt( get_queried_object_id() ) ? get_the_excerpt( get_queried_object_id() ) : __( 'Ideas, stories and updates from our studio: 3D, AR & VR, interactive web experiences and what we learn while building products that grow.', 'nk-studio' );
?>
<section class="nk-section nk-blogs" data-nk-theme="dark">
	<div class="nk-container">
		<header class="nk-blogs__head">
			<?php echo nk_headline( esc_html( get_the_title( get_queried_object_id() ) ), '', true, 'h1' ); // phpcs:ignore ?>
			<p class="nk-blogs__intro"><?php echo esc_html( $intro ); ?></p>
		</header>

		<?php
		$cats = get_categories( array( 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 14 ) );
		if ( $cats ) :
			?>
			<nav class="nk-blogs__topics" aria-label="<?php esc_attr_e( 'Topics', 'nk-studio' ); ?>">
				<a class="<?php echo $topic ? '' : 'is-on'; ?>" href="<?php echo esc_url( $base ); ?>"><?php esc_html_e( 'All', 'nk-studio' ); ?></a>
				<?php foreach ( $cats as $c ) : ?>
					<a class="<?php echo $topic === $c->slug ? 'is-on' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'topic', $c->slug, $base ) ); ?>"><?php echo esc_html( $c->name ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<div class="nk-blogs__grid">
			<?php
			if ( $q->have_posts() ) {
				$nk_i = 0;
				while ( $q->have_posts() ) {
					$q->the_post();
					echo nk_blog_card( get_the_ID(), 0 === $nk_i && 1 === $paged && ! $topic ); // phpcs:ignore
					$nk_i++;
				}
			} else {
				echo '<p class="nk-empty">' . esc_html__( 'No articles yet.', 'nk-studio' ) . '</p>';
			}
			wp_reset_postdata();
			?>
		</div>

		<?php
		if ( $q->max_num_pages > 1 ) {
			$links = paginate_links( array(
				'base'      => esc_url_raw( add_query_arg( 'pg', '%#%', $topic ? add_query_arg( 'topic', $topic, $base ) : $base ) ),
				'format'    => '',
				'current'   => $paged,
				'total'     => (int) $q->max_num_pages,
				'end_size'  => 1,
				'mid_size'  => 2,
				'prev_text' => '←',
				'next_text' => '→',
				'type'      => 'array',
			) );
			if ( $links ) {
				echo '<nav class="nk-blogs__pager" aria-label="' . esc_attr__( 'Pagination', 'nk-studio' ) . '">' . implode( '', $links ) . '</nav>'; // phpcs:ignore
			}
		}
		?>
	</div>
</section>
<?php get_footer();
