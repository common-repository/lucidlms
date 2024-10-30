<?php
/**
 * The template for displaying course element content in the single-course-element.php template
 *
 * Override this template by copying it to yourtheme/lucidlms/content-single-course-element.php
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
 * lucidlms_before_single_course_element hook
 */
do_action( 'lucidlms_before_single_course_element' );

if ( post_password_required() ) {
	echo get_the_password_form();

	return;
}
?>

	<article id="course-element-<?php the_ID(); ?>" <?php post_class(); ?>>

		<?php
		/**
		 * lucidlms_before_single_course_element_header hook
         *
         * @see lucidlms_back_to_course_link - 10
		 */
		do_action( 'lucidlms_before_single_course_element_header' );
		?>

		<div class="entry-header">

			<?php
			/**
			 * lucidlms_single_course_element_header hook
			 *
			 * @see lucidlms_template_single_title - 5
			 */
			do_action( 'lucidlms_single_course_element_header' );

			?>
		</div>

		<div class="entry-content">
			<?php
			/**
			 * lucidlms_after_single_course_element_header hook
			 *
			 * @see lucidlms_template_single_description - 10
			 * @see lucidlms_template_single_interaction_course_element - 50
			 */
			do_action( 'lucidlms_after_single_course_element_header' );
			?>
		</div>

	</article><!-- #course-element-<?php the_ID(); ?> -->

<?php
/**
 * lucidlms_after_single_course_element hook
 */
do_action( 'lucidlms_after_single_course_element' );
