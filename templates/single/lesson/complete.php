<?php
/**
 * Lesson Complete button
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $course_element, $course_element_results, $scorecard, $course;

$sc_status                  = $scorecard !== null ? $scorecard->get_status() : '';
$sc_course_element_status = $course_element_results !== null ? $course_element_results->get_status() : '';
$next_course_element = $course_element->get_next_course_element();

if ( $sc_course_element_status == 'completed' || $sc_status == 'sc_completed' || $sc_status == 'sc_expired' ) {

	// Check if user can view forum
	if ( function_exists('bbpress') ) {
		if ( bbp_user_can_view_forum() ) {
			$forum_id = get_post_meta( $course->id, '_bbp_forum_id', true );

			// Show course forum link
			if ( ! empty( $forum_id ) ) { ?>
				<div class="lucidlms-bbp-course-forum">
					<?php echo '<a href="' . esc_url( bbp_get_forum_permalink( $forum_id ) ) . '"><button>' . __( 'Discuss This Lesson', 'lucidlms' ) . '</button></a>'; ?>
				</div>
			<?php }
		}
	}
	?>

    <p class="lucidlms-info" id="element-completed">
        <?php if( 'sc_completed' == $sc_status) {
            printf( __( 'Congratulations! You have successfully completed the course. Check your e-mail for an issued certificate or download it by clicking <a href="%s" target="_blank">here</a>.', 'lucidlms' ), $scorecard->get_certificate_link() );
        } else _e( 'You may review the lesson at any time.', 'lucidlms' );
        ?>
	</p>

    <?php if( $next_course_element &&  ('sc_expired' != $sc_status) ): // if next course exist ?>
        <form class="lucidlms-info" action="<?php echo get_the_permalink( $next_course_element->id ) ?>" method="post">
            <a href="<?php echo $course->get_permalink() ?>" class="back">&larr; <?php _e( 'Back to the course', 'lucidlms' ); ?></a>
            <input type="submit" value="<?php printf("%s %s &rarr;", __( 'Next', 'lucidlms' ), strtolower($next_course_element->get_type_name())) ?>" />
        </form>
    <?php else:  ?>
        <form class="lucidlms-info" action="<?php echo $course->get_permalink() ?>" method="post">
            <input type="submit" value="<?php _e( 'Continue', 'lucidlms' ); ?>" />
        </form>
    <?php endif; ?>

    <?php
	return;
}

?>

<?php do_action( 'lucidlms_before_complete_lesson_button' ); ?>

	<div class="handle-area">

		<?php
		$duration             = floatval($course_element->duration);

        $show_complete_button = true;

		// Check if we need to activate timer
		if ( $duration && is_numeric( $duration ) ) {

			$duration = round($duration * MINUTE_IN_SECONDS);

			$time_spent               = $course_element_results !== null ? $course_element_results->time_spent : 0;
			$current_start_in_seconds = $duration - $time_spent;

            if ( $current_start_in_seconds ) {
				$show_complete_button = false;
				$current_start        = gmdate( "H:i:s", $current_start_in_seconds );
				echo '<p>' . __( 'You may not advance to the next section, until the timer is at 0:00 and you click "Mark Complete"', 'lucidlms' ) . '</p>';
				echo '<button id="lesson-timer" disabled>' . $current_start . '</button>';
			}
		}

		$form_classes = $show_complete_button ? 'show' : '';
		?>

		<form id="course-element-complete" class="<?php echo $form_classes ?>" method="POST" action="<?php echo $course_element->complete_course_element_url(); ?>">
			<input id="lesson-duration" type="hidden" value="<?php echo $duration; ?>" />
			<input class="button" type="submit" value="<?php echo $course_element->complete_course_element_text(); ?>">
		</form>

	</div>

<?php do_action( 'lucidlms_after_complete_lesson_button' ); ?>