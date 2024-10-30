<?php
/**
 * Single Course Image
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $post;

?>
<div class="thumbnail entry-thumbnail">

	<?php
	if ( has_post_thumbnail() ) {

		$image_title = esc_attr( get_the_title( get_post_thumbnail_id() ) );
		$image_link  = wp_get_attachment_url( get_post_thumbnail_id() );
		$image       = get_the_post_thumbnail( $post->ID, apply_filters( 'single_course_large_thumbnail_size', 'medium' ), array(
			'title' => $image_title
		) );

		echo apply_filters( 'lucidlms_single_course_image_html', $image, $post->ID );

	} else {

		echo apply_filters( 'lucidlms_single_course_image_html', lucid_placeholder_img('medium'), $post->ID );

	}
	?>

</div>
