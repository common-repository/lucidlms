<?php
/**
 * The template for displaying course content within loops.
 *
 * Override this template by copying it to yourtheme/lucidlms/content-course.php
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $course, $lucidlms_loop, $more;

$more = null; //to prevent full excerpt output when we're on Courses list made by page

// Store loop count we're currently on
if ( empty( $lucidlms_loop['loop'] ) ) {
	$lucidlms_loop['loop'] = 0;
}

// Store column count for displaying the grid
if ( empty( $lucidlms_loop['columns'] ) ) {
	$lucidlms_loop['columns'] = apply_filters( 'loop_courses_columns', 4 );
}

// Ensure visibility
if ( ! $course ) {
	return;
}

// Increase loop count
$lucidlms_loop['loop'] ++;

// Extra post classes
$classes = array();
if ( 0 == ( $lucidlms_loop['loop'] - 1 ) % $lucidlms_loop['columns'] || 1 == $lucidlms_loop['columns'] ) {
	$classes[] = 'first';
}
if ( 0 == $lucidlms_loop['loop'] % $lucidlms_loop['columns'] ) {
	$classes[] = 'last';
}

$tag_count = sizeof( get_the_terms( $course->id, 'course_tag' ) );

?>
<article <?php post_class( $classes ); ?>>

	<?php do_action( 'lucidlms_before_courses_loop_item' ); ?>

	<a href="<?php the_permalink(); ?>" class="entry-header">

		<?php
		/**
		 * lucidlms_before_courses_loop_item_title hook
		 *
		 * @see lucidlms_template_loop_start_course - 10
		 * @see lucidlms_template_loop_course_thumbnail - 10
		 */
		do_action( 'lucidlms_before_courses_loop_item_title' );
		?>

		<h3><?php the_title(); ?></h3>

		<?php
		/**
		 * lucidlms_after_courses_loop_item_title hook
		 */
		do_action( 'lucidlms_after_courses_loop_item_title' );
		?>

	</a>

    <div class="entry-meta">
        <?php the_tags( sprintf('<span class="tags">%s ', _n( 'Tag:', 'Tags:', $tag_count, 'lucidlms' )), ', ', '</span>' ); ?>
    </div>

    <div class="entry-content">
        <?php do_action( 'lucidlms_after_courses_loop_item' ); ?>
	</div>

</article>