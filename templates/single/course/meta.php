<?php
/**
 * Single Course Meta
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $post, $course;

$cat_count = sizeof( get_the_terms( $post->ID, 'course_cat' ) );
$tag_count = sizeof( get_the_terms( $post->ID, 'course_tag' ) );
?>
<div class="meta entry-meta">

	<?php do_action( 'lucidlms_course_meta_start' ); ?>

	<?php echo $course->get_categories( ', ', '<span class="categories">' . _n( 'Category:', 'Categories:', $cat_count, 'lucidlms' ) . ' ', '.</span>' ); ?>
    <?php the_tags( sprintf('<span class="tags">%s ', _n( 'Tag:', 'Tags:', $tag_count, 'lucidlms' )), ', ', '</span>' ); ?>

	<?php do_action( 'lucidlms_course_meta_end' ); ?>

</div>