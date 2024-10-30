<?php
/**
 * The template for displaying course content in the single-course.php template
 *
 * Override this template by copying it to yourtheme/lucidlms/content-single-course.php
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>

<?php
/**
 * lucidlms_before_single_course hook
 */
do_action( 'lucidlms_before_single_course' );

if ( post_password_required() ) {
	echo get_the_password_form();

	return;
}
?>

<article id="course-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php
	/**
	 * lucidlms_before_single_course_header hook
	 *
	 * @see lucidlms_show_course_image - 20
	 */
	do_action( 'lucidlms_before_single_course_header' );
	?>

	<div class="entry-header">

		<?php
		/**
		 * lucidlms_single_course_header hook
		 *
		 * @see lucidlms_template_single_title - 5
		 * @see lucidlms_template_single_meta - 40
		 * @see lucidlms_template_single_sharing - 50
		 */
		do_action( 'lucidlms_single_course_header' );

		?>
	</div>

	<div class="entry-content">
		<?php
		/**
		 * lucidlms_after_single_course_header hook
		 *
		 * @see lucidlms_template_single_description - 10
		 * @see lucidlms_course_content_elements - 20
		 * @see lucidlms_template_single_start_course - 50
		 */
		do_action( 'lucidlms_after_single_course_header' );
		?>
	</div>

</article><!-- #course-<?php the_ID(); ?> -->

<?php
/**
 * lucidlms_after_single_course hook
 */
do_action( 'lucidlms_after_single_course' );
