<?php
/**
 * Dashboard content (all editable in wp-admin, shown by the NK widgets in Elementor):
 *  - Work          (nk_work)          projects  -> NK Work Grid  (Home "Work" section)
 *  - Reviews       (nk_review)        client reviews -> NK Testimonials (Home "Testimonials" section)
 *  - Social Videos (nk_social_video)  YouTube / Shorts / Instagram Reels / TikTok / Facebook / Vimeo / uploaded MP4
 *                                     -> NK Social Videos (Home "Social videos" section)
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	register_post_type( 'nk_work', array(
		'labels'        => array(
			'name'          => __( 'Work', 'nk-studio' ),
			'singular_name' => __( 'Project', 'nk-studio' ),
			'add_new'       => __( 'Add project', 'nk-studio' ),
			'add_new_item'  => __( 'Add new project', 'nk-studio' ),
			'edit_item'     => __( 'Edit project', 'nk-studio' ),
			'all_items'     => __( 'All projects', 'nk-studio' ),
		),
		'public'        => true,
		'has_archive'   => false, // /work/ is a normal page (editable in Elementor) that lists projects.
		'rewrite'       => array( 'slug' => 'work' ),
		'menu_icon'     => 'dashicons-portfolio',
		'menu_position' => 20,
		'show_in_rest'  => true,
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'custom-fields', 'elementor' ),
	) );
	register_taxonomy( 'nk_work_cat', 'nk_work', array(
		'labels'            => array( 'name' => __( 'Work categories', 'nk-studio' ), 'singular_name' => __( 'Work category', 'nk-studio' ) ),
		'hierarchical'      => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'work-category' ),
	) );

	register_post_type( 'nk_review', array(
		'labels'             => array(
			'name'          => __( 'Reviews', 'nk-studio' ),
			'singular_name' => __( 'Review', 'nk-studio' ),
			'add_new'       => __( 'Add review', 'nk-studio' ),
			'add_new_item'  => __( 'Add new review', 'nk-studio' ),
			'edit_item'     => __( 'Edit review', 'nk-studio' ),
			'all_items'     => __( 'All reviews', 'nk-studio' ),
		),
		'public'             => false,
		'show_ui'            => true,
		'show_in_rest'       => true,
		'menu_icon'          => 'dashicons-format-quote',
		'menu_position'      => 21,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
	) );

	register_post_type( 'nk_social_video', array(
		'labels'        => array(
			'name'          => __( 'Social Videos', 'nk-studio' ),
			'singular_name' => __( 'Social video', 'nk-studio' ),
			'add_new'       => __( 'Add video', 'nk-studio' ),
			'add_new_item'  => __( 'Add new social video', 'nk-studio' ),
			'edit_item'     => __( 'Edit social video', 'nk-studio' ),
			'all_items'     => __( 'All videos', 'nk-studio' ),
		),
		'public'        => false,
		'show_ui'       => true,
		'show_in_rest'  => false, // classic screen, so the meta box + media picker are always there
		'menu_icon'     => 'dashicons-video-alt3',
		'menu_position' => 22,
		'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
	) );

	register_post_type( 'nk_blog', array(
		'labels'        => array(
			'name'          => __( 'Blogs', 'nk-studio' ),
			'singular_name' => __( 'Blog', 'nk-studio' ),
			'add_new'       => __( 'Add Blog', 'nk-studio' ),
			'add_new_item'  => __( 'Add new blog', 'nk-studio' ),
			'edit_item'     => __( 'Edit blog', 'nk-studio' ),
			'all_items'     => __( 'All blogs', 'nk-studio' ),
		),
		/*
		 * v3.8.0: the site blog is the normal WordPress `post` type (original posts, URLs, categories, tags, Yoast).
		 * nk_blog is kept registered only so old NK settings never fatal, but it is NOT public (no /blogs/ archive that
		 * would hijack the original /blogs/ page), has no admin menu and is never seeded.
		 * Re-enable the screen with: add_filter( 'nk_enable_demo_blog_cpt', '__return_true' );
		 */
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => (bool) apply_filters( 'nk_enable_demo_blog_cpt', false ),
		'show_in_rest'       => false,
		'has_archive'        => false,
		'rewrite'            => false,
		'menu_icon'     => 'dashicons-welcome-write-blog',
		'menu_position' => 23,
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
	) );
	register_taxonomy( 'nk_blog_cat', 'nk_blog', array(
		'labels'            => array( 'name' => __( 'Blog categories', 'nk-studio' ), 'singular_name' => __( 'Blog category', 'nk-studio' ) ),
		'hierarchical'      => true,
		'show_in_rest'      => false,
		'show_admin_column' => true,
		'public'            => false,
		'show_ui'           => (bool) apply_filters( 'nk_enable_demo_blog_cpt', false ),
		'rewrite'           => false,
	) );
} );

/* Reviews / social videos / blogs: classic editor screen (simple fields), works with or without the block editor. */
add_filter( 'use_block_editor_for_post_type', function ( $use, $type ) {
	return in_array( $type, array( 'nk_review', 'nk_social_video', 'nk_blog' ), true ) ? false : $use;
}, 10, 2 );

add_filter( 'enter_title_here', function ( $text, $post ) {
	if ( 'nk_review' === $post->post_type ) {
		return __( 'Client name (e.g. Hannah Single)', 'nk-studio' );
	}
	if ( 'nk_social_video' === $post->post_type ) {
		return __( 'Caption (e.g. Behind the scenes – Bean Trailer)', 'nk-studio' );
	}
	if ( 'nk_blog' === $post->post_type ) {
		return __( 'Blog title (e.g. The last tango.)', 'nk-studio' );
	}
	return $text;
}, 10, 2 );

/* ------------------------------------------------------------------ meta fields */
function nk_meta_fields() {
	return array(
		'nk_work'         => array(
			'_nk_badge'  => array( 'text', __( 'Badge on image (e.g. INVESTOR READINESS)', 'nk-studio' ) ),
			'_nk_sector' => array( 'text', __( 'Sector (e.g. Finance & Fintech)', 'nk-studio' ) ),
			'_nk_link'   => array( 'url', __( 'External link (optional — the card opens this instead of the project page)', 'nk-studio' ) ),
			'_nk_video_url'  => array( 'url', __( 'Card video link (optional) — YouTube / Shorts, Vimeo, Instagram, TikTok', 'nk-studio' ) ),
			'_nk_video_file' => array( 'video', __( '…or upload a card video (MP4 / WebM)', 'nk-studio' ) ),
		),
		'post'            => array(
			'_nk_video_url'  => array( 'url', __( 'Card video link — YouTube / Shorts, Vimeo, Instagram Reel, TikTok, Facebook', 'nk-studio' ) ),
			'_nk_video_file' => array( 'video', __( '…or upload a video from your computer (MP4 / WebM)', 'nk-studio' ) ),
		),
		'nk_review'       => array(
			'_nk_role'    => array( 'text', __( 'Role / position (e.g. Associate Director, NY)', 'nk-studio' ) ),
			'_nk_company' => array( 'text', __( 'Company name (shown when there is no logo)', 'nk-studio' ) ),
			'_nk_logo'    => array( 'image', __( 'Company logo', 'nk-studio' ) ),
			'_nk_rating'  => array( 'rating', __( 'Stars', 'nk-studio' ) ),
		),
		'nk_social_video' => array(
			'_nk_video_url'  => array( 'url', __( 'Video link — YouTube / Shorts, Instagram Reel, TikTok, Facebook, Vimeo', 'nk-studio' ) ),
			'_nk_video_file' => array( 'video', __( '…or upload a video file (MP4 / WebM)', 'nk-studio' ) ),
			'_nk_open_url'   => array( 'url', __( 'Open on platform link (optional, e.g. your Instagram profile)', 'nk-studio' ) ),
		),
		'nk_blog'         => array(
			'_nk_gallery'    => array( 'gallery', __( 'Multiple Images (Gallery slider on card)', 'nk-studio' ) ),
			'_nk_badge'      => array( 'text', __( 'Tag / Badge (e.g. UNCATEGORIZED, empty = category name)', 'nk-studio' ) ),
			'_nk_link'       => array( 'url', __( 'Custom link (optional — empty opens the blog post)', 'nk-studio' ) ),
			'_nk_video_url'  => array( 'url', __( 'Video link (optional) — YouTube, Vimeo, MP4 link', 'nk-studio' ) ),
			'_nk_video_file' => array( 'video', __( '…or upload a video file (MP4 / WebM)', 'nk-studio' ) ),
		),
	);
}

add_action( 'add_meta_boxes', function () {
	$titles = array(
		'nk_work'         => __( 'Card details', 'nk-studio' ),
		'nk_review'       => __( 'Review details', 'nk-studio' ),
		'nk_social_video' => __( 'Video', 'nk-studio' ),
		'post'            => __( 'Card video (Insights section)', 'nk-studio' ),
		'nk_blog'         => __( 'Blog Details & Multiple Images Gallery', 'nk-studio' ),
	);
	foreach ( nk_meta_fields() as $type => $fields ) {
		add_meta_box( 'nk_meta_' . $type, $titles[ $type ], 'nk_meta_box', $type, in_array( $type, array( 'nk_work', 'post' ), true ) ? 'side' : 'normal', 'high', array( 'fields' => $fields ) );
	}
} );

function nk_meta_box( $post, $box ) {
	wp_nonce_field( 'nk_meta_save', 'nk_meta_nonce' );
	foreach ( $box['args']['fields'] as $key => $f ) {
		list( $type, $label ) = $f;
		$val  = get_post_meta( $post->ID, $key, true );
		$name = esc_attr( ltrim( $key, '_' ) );
		echo '<p class="nk-field"><label><strong>' . esc_html( $label ) . '</strong></label><br>';
		switch ( $type ) {
			case 'gallery':
				$ids = array_filter( array_map( 'absint', explode( ',', (string) $val ) ) );
				echo '<div class="nk-gallery-field">';
				echo '<input type="hidden" name="' . $name . '" class="nk-gallery-ids" value="' . esc_attr( implode( ',', $ids ) ) . '">';
				echo '<div class="nk-gallery-preview" style="display:flex;flex-wrap:wrap;gap:8px;margin:8px 0">';
				foreach ( $ids as $img_id ) {
					$thumb_url = wp_get_attachment_image_url( $img_id, 'thumbnail' );
					if ( $thumb_url ) {
						echo '<div class="nk-gallery-item" data-id="' . (int) $img_id . '" style="position:relative;width:72px;height:72px;border-radius:6px;overflow:hidden;border:1px solid #ccc;background:#f0f0f0">';
						echo '<img src="' . esc_url( $thumb_url ) . '" style="width:100%;height:100%;object-fit:cover;display:block">';
						echo '<button type="button" class="nk-gallery-del" style="position:absolute;top:2px;right:2px;background:rgba(0,0,0,0.75);color:#fff;border:none;border-radius:50%;width:18px;height:18px;line-height:16px;text-align:center;font-size:12px;cursor:pointer;padding:0" title="' . esc_attr__( 'Remove', 'nk-studio' ) . '">&times;</button>';
						echo '</div>';
					}
				}
				echo '</div>';
				echo '<button type="button" class="button button-secondary nk-gallery-pick">' . esc_html__( 'Add / Choose Images', 'nk-studio' ) . '</button> ';
				echo '<button type="button" class="button-link nk-gallery-clear"' . ( $ids ? '' : ' style="display:none"' ) . '>' . esc_html__( 'Clear all', 'nk-studio' ) . '</button>';
				echo '<p class="description" style="margin-top:6px">' . esc_html__( 'Select multiple images from the Media Library. If multiple images are chosen, the card displays them in a swipe slider.', 'nk-studio' ) . '</p>';
				echo '</div>';
				break;
			case 'rating':
				echo '<select name="' . $name . '">';
				foreach ( array( 0 => __( 'No stars', 'nk-studio' ), 5 => '★★★★★', 4 => '★★★★', 3 => '★★★', 2 => '★★', 1 => '★' ) as $n => $t ) {
					echo '<option value="' . (int) $n . '"' . selected( (int) $val, $n, false ) . '>' . esc_html( $t ) . '</option>';
				}
				echo '</select>';
				break;
			case 'image':
			case 'video':
				$id    = (int) $val;
				$prev  = '';
				if ( $id ) {
					$prev = 'image' === $type ? wp_get_attachment_image( $id, 'thumbnail', false, array( 'style' => 'max-width:120px;height:auto;display:block;margin:6px 0' ) ) : '<code style="display:block;margin:6px 0">' . esc_html( basename( (string) get_attached_file( $id ) ) ) . '</code>';
				}
				echo '<span class="nk-media" data-type="' . esc_attr( $type ) . '">';
				echo '<input type="hidden" name="' . $name . '" value="' . ( $id ? (int) $id : '' ) . '">';
				echo '<span class="nk-media__prev">' . $prev . '</span>'; // phpcs:ignore
				echo '<button type="button" class="button nk-media__pick">' . esc_html__( 'Choose / upload', 'nk-studio' ) . '</button> ';
				echo '<button type="button" class="button-link nk-media__clear"' . ( $id ? '' : ' style="display:none"' ) . '>' . esc_html__( 'Remove', 'nk-studio' ) . '</button>';
				echo '</span>';
				break;
			default:
				echo '<input type="' . ( 'url' === $type ? 'url' : 'text' ) . '" class="widefat" name="' . $name . '" value="' . esc_attr( $val ) . '"' . ( 'url' === $type ? ' placeholder="https://"' : '' ) . '>';
		}
		echo '</p>';
	}
	if ( 'post' === $post->post_type ) {
		echo '<p class="description">' . esc_html__( 'The Insights cards on Home show this video instead of the picture. Featured image = cover picture (YouTube covers are picked automatically).', 'nk-studio' ) . '</p>';
	} elseif ( 'nk_work' === $post->post_type ) {
		echo '<p class="description">' . esc_html__( 'Title = project name, Excerpt = one-line description, Featured image = card image. Order: Page attributes > Order (small numbers first).', 'nk-studio' ) . '</p>';
	} elseif ( 'nk_review' === $post->post_type ) {
		echo '<p class="description">' . esc_html__( 'Title = client name, main text box = the review, Featured image = client photo. Order: Page attributes > Order.', 'nk-studio' ) . '</p>';
	} elseif ( 'nk_blog' === $post->post_type ) {
		echo '<p class="description">' . esc_html__( 'Title = blog name, main text / Excerpt = short story description, Featured image = default cover, Multiple Images = slider on card. Order: Page attributes > Order.', 'nk-studio' ) . '</p>';
	} else {
		echo '<p class="description">' . esc_html__( 'Featured image = cover picture (YouTube covers are picked automatically if you leave it empty). Order: Page attributes > Order.', 'nk-studio' ) . '</p>';
	}
}

add_action( 'save_post', function ( $id, $post ) {
	$all = nk_meta_fields();
	if ( empty( $all[ $post->post_type ] ) || ! isset( $_POST['nk_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nk_meta_nonce'] ), 'nk_meta_save' ) || ! current_user_can( 'edit_post', $id ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	foreach ( $all[ $post->post_type ] as $key => $f ) {
		$raw = isset( $_POST[ ltrim( $key, '_' ) ] ) ? wp_unslash( $_POST[ ltrim( $key, '_' ) ] ) : ''; // phpcs:ignore
		switch ( $f[0] ) {
			case 'gallery':
				$ids = array_filter( array_map( 'absint', explode( ',', (string) $raw ) ) );
				$v   = implode( ',', $ids );
				break;
			case 'url':
				$v = esc_url_raw( trim( (string) $raw ) );
				break;
			case 'image':
			case 'video':
			case 'rating':
				$v = absint( $raw );
				if ( 'rating' === $f[0] ) {
					$v = min( 5, $v );
				}
				break;
			default:
				$v = sanitize_text_field( (string) $raw );
		}
		if ( '' === $v || 0 === $v ) {
			delete_post_meta( $id, $key );
		} else {
			update_post_meta( $id, $key, $v );
		}
	}
}, 10, 2 );

/* Media picker for the logo / video file fields + multiple gallery picker. */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, array( 'nk_review', 'nk_social_video', 'nk_work', 'post', 'nk_blog' ), true ) ) {
		return;
	}
	wp_enqueue_media();
	wp_add_inline_script( 'media-editor', "jQuery(function($){\n$(document).on('click','.nk-media__pick',function(e){e.preventDefault();var box=$(this).closest('.nk-media'),type=box.data('type');\nvar f=wp.media({title:type==='video'?'Choose a video':'Choose an image',library:{type:type==='video'?'video':'image'},multiple:false});\nf.on('select',function(){var a=f.state().get('selection').first().toJSON();box.find('input').val(a.id);\nbox.find('.nk-media__prev').html(type==='video'?$('<code style=\"display:block;margin:6px 0\">').text(a.filename):$('<img style=\"max-width:120px;height:auto;display:block;margin:6px 0\">').attr('src',(a.sizes&&a.sizes.thumbnail?a.sizes.thumbnail.url:a.url)));box.find('.nk-media__clear').show();});f.open();});\n$(document).on('click','.nk-media__clear',function(e){e.preventDefault();var box=$(this).closest('.nk-media');box.find('input').val('');box.find('.nk-media__prev').empty();$(this).hide();});\n\n$(document).on('click','.nk-gallery-pick',function(e){e.preventDefault();var box=$(this).closest('.nk-gallery-field'),input=box.find('.nk-gallery-ids'),prev=box.find('.nk-gallery-preview'),clearBtn=box.find('.nk-gallery-clear');\nvar f=wp.media({title:'Select Blog Images (Multiple)',button:{text:'Add to Blog Gallery'},library:{type:'image'},multiple:true});\nf.on('select',function(){var sel=f.state().get('selection');var cur=input.val()?input.val().split(',').map(Number):[];sel.map(function(att){var a=att.toJSON();if(cur.indexOf(a.id)===-1){cur.push(a.id);var u=(a.sizes&&a.sizes.thumbnail)?a.sizes.thumbnail.url:a.url;prev.append($('<div class=\"nk-gallery-item\" data-id=\"'+a.id+'\" style=\"position:relative;width:72px;height:72px;border-radius:6px;overflow:hidden;border:1px solid #ccc;background:#f0f0f0\"><img src=\"'+u+'\" style=\"width:100%;height:100%;object-fit:cover;display:block\"><button type=\"button\" class=\"nk-gallery-del\" style=\"position:absolute;top:2px;right:2px;background:rgba(0,0,0,0.75);color:#fff;border:none;border-radius:50%;width:18px;height:18px;line-height:16px;text-align:center;font-size:12px;cursor:pointer;padding:0\" title=\"Remove\">&times;</button></div>'));}});input.val(cur.join(','));if(cur.length)clearBtn.show();});f.open();});\n$(document).on('click','.nk-gallery-del',function(e){e.preventDefault();var it=$(this).closest('.nk-gallery-item'),box=it.closest('.nk-gallery-field'),id=it.data('id'),input=box.find('.nk-gallery-ids');var ids=input.val()?input.val().split(',').map(Number):[];ids=ids.filter(function(x){return x!==id;});input.val(ids.join(','));it.remove();if(!ids.length)box.find('.nk-gallery-clear').hide();});\n$(document).on('click','.nk-gallery-clear',function(e){e.preventDefault();var box=$(this).closest('.nk-gallery-field');box.find('.nk-gallery-ids').val('');box.find('.nk-gallery-preview').empty();$(this).hide();});\n});" );
} );

/* ------------------------------------------------------------------ admin list columns */
add_filter( 'manage_nk_work_posts_columns', function ( $c ) {
	return array_slice( $c, 0, 1, true ) + array( 'nk_thumb' => __( 'Image', 'nk-studio' ) ) + array_slice( $c, 1, null, true ) + array( 'nk_sector' => __( 'Sector', 'nk-studio' ), 'menu_order' => __( 'Order', 'nk-studio' ) );
} );
add_filter( 'manage_nk_review_posts_columns', function ( $c ) {
	return array_slice( $c, 0, 1, true ) + array( 'nk_thumb' => __( 'Photo', 'nk-studio' ) ) + array_slice( $c, 1, null, true ) + array( 'nk_company' => __( 'Company', 'nk-studio' ), 'nk_rating' => __( 'Stars', 'nk-studio' ), 'menu_order' => __( 'Order', 'nk-studio' ) );
} );
add_filter( 'manage_nk_social_video_posts_columns', function ( $c ) {
	return array_slice( $c, 0, 1, true ) + array( 'nk_thumb' => __( 'Cover', 'nk-studio' ) ) + array_slice( $c, 1, null, true ) + array( 'nk_platform' => __( 'Platform', 'nk-studio' ), 'menu_order' => __( 'Order', 'nk-studio' ) );
} );
add_filter( 'manage_nk_blog_posts_columns', function ( $c ) {
	return array_slice( $c, 0, 1, true ) + array( 'nk_thumb' => __( 'Cover', 'nk-studio' ) ) + array_slice( $c, 1, null, true ) + array( 'nk_gallery_count' => __( 'Gallery Images', 'nk-studio' ), 'menu_order' => __( 'Order', 'nk-studio' ) );
} );
foreach ( array( 'nk_work', 'nk_review', 'nk_social_video', 'nk_blog' ) as $nk_pt ) {
	add_action( 'manage_' . $nk_pt . '_posts_custom_column', function ( $col, $id ) {
		switch ( $col ) {
			case 'nk_thumb':
				$img = get_the_post_thumbnail( $id, array( 56, 56 ), array( 'style' => 'width:56px;height:56px;object-fit:cover;border-radius:6px' ) );
				if ( ! $img && 'nk_social_video' === get_post_type( $id ) ) {
					$v = nk_social_video_data( $id );
					$img = $v && $v['thumb'] ? '<img src="' . esc_url( $v['thumb'] ) . '" style="width:56px;height:56px;object-fit:cover;border-radius:6px" alt="">' : '';
				}
				if ( ! $img && 'nk_blog' === get_post_type( $id ) ) {
					$g = get_post_meta( $id, '_nk_gallery', true );
					if ( $g ) {
						$first_id = absint( strtok( $g, ',' ) );
						if ( $first_id ) {
							$u = wp_get_attachment_image_url( $first_id, 'thumbnail' );
							$img = $u ? '<img src="' . esc_url( $u ) . '" style="width:56px;height:56px;object-fit:cover;border-radius:6px" alt="">' : '';
						}
					}
				}
				echo $img ? $img : '—'; // phpcs:ignore
				break;
			case 'nk_gallery_count':
				$g = get_post_meta( $id, '_nk_gallery', true );
				$cnt = $g ? count( array_filter( explode( ',', $g ) ) ) : 0;
				echo $cnt ? esc_html( sprintf( _n( '%d image', '%d images', $cnt, 'nk-studio' ), $cnt ) ) : '—';
				break;
			case 'nk_sector':
				echo esc_html( get_post_meta( $id, '_nk_sector', true ) );
				break;
			case 'nk_company':
				echo esc_html( get_post_meta( $id, '_nk_company', true ) );
				break;
			case 'nk_rating':
				$r = (int) get_post_meta( $id, '_nk_rating', true );
				echo $r ? esc_html( str_repeat( '★', $r ) ) : '—';
				break;
			case 'nk_platform':
				$v = nk_social_video_data( $id );
				echo esc_html( $v ? $v['label'] : '—' );
				break;
			case 'menu_order':
				echo (int) get_post_field( 'menu_order', $id );
				break;
		}
	}, 10, 2 );
}

/* ------------------------------------------------------------------ data helpers used by the widgets */

/**
 * Turn a social / video link into something the page can play.
 * Returns [ type => frame|file|link, src, thumb, platform, label, url ] or null.
 */
function nk_video_info( $url ) {
	$url = trim( html_entity_decode( (string) $url ) );
	if ( '' === $url || ! preg_match( '#^https?://#i', $url ) ) {
		return null;
	}
	$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	$host = preg_replace( '/^(www\.|m\.)/', '', $host );
	$path = (string) wp_parse_url( $url, PHP_URL_PATH );
	$out  = array( 'type' => 'link', 'src' => $url, 'thumb' => '', 'platform' => 'web', 'label' => __( 'Video', 'nk-studio' ), 'url' => $url, 'vertical' => false );

	if ( preg_match( '/\.(mp4|webm|m4v|mov)$/i', $path ) ) {
		return array_merge( $out, array( 'type' => 'file', 'platform' => 'file', 'label' => __( 'Video', 'nk-studio' ) ) );
	}
	if ( in_array( $host, array( 'youtube.com', 'youtu.be', 'youtube-nocookie.com', 'music.youtube.com' ), true ) ) {
		$id = '';
		if ( 'youtu.be' === $host ) {
			$id = trim( $path, '/' );
		} elseif ( preg_match( '#/(shorts|embed|live|v)/([^/?]+)#', $path, $m ) ) {
			$id = $m[2];
		} else {
			parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $q );
			$id = $q['v'] ?? '';
		}
		$id = preg_replace( '/[^A-Za-z0-9_-]/', '', $id );
		if ( $id ) {
			return array_merge( $out, array(
				'type'     => 'frame',
				'src'      => 'https://www.youtube-nocookie.com/embed/' . $id . '?autoplay=1&rel=0&playsinline=1&modestbranding=1',
				'thumb'    => 'https://i.ytimg.com/vi/' . $id . '/hqdefault.jpg',
				'platform' => 'youtube',
				'label'    => false !== strpos( $path, '/shorts/' ) ? 'YouTube Shorts' : 'YouTube',
				'vertical' => false !== strpos( $path, '/shorts/' ),
			) );
		}
	}
	if ( 'vimeo.com' === $host || 'player.vimeo.com' === $host ) {
		if ( preg_match( '#/(?:video/)?(\d+)(?:/([a-z0-9]+))?#i', $path, $m ) ) {
			parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $q );
			$h = ! empty( $m[2] ) ? $m[2] : ( $q['h'] ?? '' );
			return array_merge( $out, array(
				'type'     => 'frame',
				'src'      => 'https://player.vimeo.com/video/' . $m[1] . '?autoplay=1&playsinline=1' . ( $h ? '&h=' . rawurlencode( $h ) : '' ),
				'platform' => 'vimeo',
				'label'    => 'Vimeo',
			) );
		}
	}
	if ( 'tiktok.com' === $host || 'vm.tiktok.com' === $host ) {
		if ( preg_match( '#/video/(\d+)#', $path, $m ) ) {
			return array_merge( $out, array( 'type' => 'frame', 'src' => 'https://www.tiktok.com/embed/v2/' . $m[1], 'platform' => 'tiktok', 'label' => 'TikTok', 'vertical' => true ) );
		}
		return array_merge( $out, array( 'platform' => 'tiktok', 'label' => 'TikTok', 'vertical' => true ) ); // short vm.tiktok link: opens on TikTok
	}
	if ( 'instagram.com' === $host ) {
		if ( preg_match( '#/(p|reel|reels|tv)/([A-Za-z0-9_-]+)#', $path, $m ) ) {
			$kind = 'reels' === $m[1] ? 'reel' : $m[1];
			return array_merge( $out, array( 'type' => 'frame', 'src' => 'https://www.instagram.com/' . $kind . '/' . $m[2] . '/embed/', 'platform' => 'instagram', 'label' => 'Instagram', 'vertical' => true ) );
		}
		return array_merge( $out, array( 'platform' => 'instagram', 'label' => 'Instagram', 'vertical' => true ) );
	}
	if ( in_array( $host, array( 'facebook.com', 'fb.watch', 'web.facebook.com' ), true ) ) {
		return array_merge( $out, array( 'type' => 'frame', 'src' => 'https://www.facebook.com/plugins/video.php?show_text=false&autoplay=true&href=' . rawurlencode( $url ), 'platform' => 'facebook', 'label' => 'Facebook', 'vertical' => false !== strpos( $path, '/reel' ) ) );
	}
	if ( 'linkedin.com' === $host ) {
		return array_merge( $out, array( 'platform' => 'linkedin', 'label' => 'LinkedIn' ) );
	}
	return $out;
}

/** One social video post as playable data. */
function nk_social_video_data( $post_id ) {
	$file = (int) get_post_meta( $post_id, '_nk_video_file', true );
	$info = null;
	if ( $file && wp_get_attachment_url( $file ) ) {
		$info = array( 'type' => 'file', 'src' => wp_get_attachment_url( $file ), 'thumb' => '', 'platform' => 'file', 'label' => __( 'Video', 'nk-studio' ), 'url' => '', 'vertical' => false );
	} else {
		$info = nk_video_info( get_post_meta( $post_id, '_nk_video_url', true ) );
	}
	if ( ! $info ) {
		return null;
	}
	$thumb = get_the_post_thumbnail_url( $post_id, 'large' );
	if ( $thumb ) {
		$info['thumb'] = $thumb;
	}
	$open = get_post_meta( $post_id, '_nk_open_url', true );
	if ( $open ) {
		$info['url'] = $open;
	}
	$info['caption'] = get_the_title( $post_id );
	return $info;
}

/** Published reviews in the shape NK Testimonials uses. */
function nk_reviews_items( $count = 12 ) {
	$items = array();
	$q     = get_posts( array( 'post_type' => 'nk_review', 'post_status' => 'publish', 'numberposts' => max( 1, (int) $count ), 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ) ) );
	foreach ( $q as $p ) {
		$logo   = (int) get_post_meta( $p->ID, '_nk_logo', true );
		$avatar = (int) get_post_thumbnail_id( $p->ID );
		$items[] = array(
			'_id'     => 'r' . $p->ID,
			'company' => get_post_meta( $p->ID, '_nk_company', true ),
			'quote'   => trim( wp_strip_all_tags( $p->post_content ) ),
			'name'    => get_the_title( $p ),
			'role'    => get_post_meta( $p->ID, '_nk_role', true ),
			'logo'    => $logo ? array( 'id' => $logo, 'url' => (string) wp_get_attachment_url( $logo ) ) : array( 'url' => '' ),
			'avatar'  => $avatar ? array( 'id' => $avatar, 'url' => (string) wp_get_attachment_url( $avatar ) ) : array( 'url' => '' ),
			'rating'  => (int) get_post_meta( $p->ID, '_nk_rating', true ),
		);
	}
	return $items;
}

/** Card video of a post / project (uploaded file wins over a link), or null. */
function nk_post_card_video( $post_id ) {
	$file = (int) get_post_meta( $post_id, '_nk_video_file', true );
	if ( $file && wp_get_attachment_url( $file ) ) {
		return array( 'type' => 'file', 'src' => wp_get_attachment_url( $file ), 'thumb' => '', 'platform' => 'file', 'label' => __( 'Video', 'nk-studio' ), 'url' => '', 'vertical' => false );
	}
	$url = get_post_meta( $post_id, '_nk_video_url', true );
	return $url ? nk_video_info( $url ) : null;
}

/**
 * Media box of a card with a video.
 * $mode 'click'   = cover + play button, the video plays inside the card on click (sound on)
 *       'preview' = the video plays silently on a loop inside the card (uploaded MP4 / YouTube / Vimeo)
 */
function nk_card_video_media( $v, $cover_url = '', $badge = '', $mode = 'click', $label = '' ) {
	$cover_url = $cover_url ? $cover_url : ( $v['thumb'] ?? '' );
	$badge_html = $badge ? '<span class="nk-badge">' . esc_html( strtoupper( $badge ) ) . '</span>' : '';
	$play = '<span class="nk-sv__play" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22"><path d="M8 5.5v13l11-6.5z" fill="currentColor"/></svg></span>';
	$cover = $cover_url ? '<img class="nk-cv__cover" src="' . esc_url( $cover_url ) . '" alt="" loading="lazy">' : '';

	if ( 'preview' === $mode ) {
		$bg = '';
		if ( 'file' === $v['type'] ) {
			$bg = '<video class="nk-cv__bg" src="' . esc_url( $v['src'] ) . '" muted loop playsinline autoplay preload="metadata"' . ( $cover_url ? ' poster="' . esc_url( $cover_url ) . '"' : '' ) . '></video>';
		} elseif ( 'youtube' === $v['platform'] && preg_match( '#/embed/([A-Za-z0-9_-]+)#', $v['src'], $m ) ) {
			$bg = '<iframe class="nk-cv__bg nk-cv__bg--frame" src="https://www.youtube-nocookie.com/embed/' . esc_attr( $m[1] ) . '?autoplay=1&mute=1&loop=1&playlist=' . esc_attr( $m[1] ) . '&controls=0&playsinline=1&modestbranding=1&rel=0" allow="autoplay; encrypted-media" tabindex="-1" title="" loading="lazy"></iframe>';
		} elseif ( 'vimeo' === $v['platform'] ) {
			$bg = '<iframe class="nk-cv__bg nk-cv__bg--frame" src="' . esc_url( $v['src'] . '&muted=1&loop=1&background=1' ) . '" allow="autoplay" tabindex="-1" title="" loading="lazy"></iframe>';
		}
		if ( $bg ) {
			return '<span class="nk-card__media nk-card__media--video is-preview">' . $cover . $bg . $badge_html . '</span>';
		}
		// other platforms cannot loop silently: fall back to click-to-play
	}
	if ( 'link' === $v['type'] ) {
		return '<a class="nk-card__media nk-card__media--video" href="' . esc_url( $v['src'] ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( $label ? $label : $v['label'] ) . '">' . $cover . $play . $badge_html . '</a>';
	}
	if ( ! $cover && 'file' === $v['type'] ) {
		$cover = '<video class="nk-cv__cover" src="' . esc_url( $v['src'] ) . '#t=0.5" muted playsinline preload="metadata"></video>';
	}
	return '<div class="nk-card__media nk-card__media--video" role="button" tabindex="0" data-nk-sv="' . esc_attr( $v['type'] ) . '" data-src="' . esc_url( $v['src'] ) . '" aria-label="' . esc_attr( sprintf( __( 'Play: %s', 'nk-studio' ), $label ? $label : $v['label'] ) ) . '">' . $cover . $play . $badge_html . '</div>';
}
