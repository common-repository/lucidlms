<?php
/**
 * Quiz Notices
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $course_element, $course_element_results, $course, $scorecard;

$status       = $course_element_results !== null ? $course_element_results->get_status() : '';
$is_completed = $status == 'completed' || $status == 'failed';

$passed_attempts  = !is_null($course_element_results) && !empty($course_element_results->passed_attempts) ? $course_element_results->passed_attempts : 0;
$allowed_attempts = $course_element->attempts;

$next_course_element = $course_element->get_next_course_element();

// if $allowed_attempts equals false or zero, then user is allowed to pass this quiz as much as he/she wants.
$user_can_try_again = !$allowed_attempts || ($allowed_attempts > $passed_attempts);

$continue_button_text = ('failed' != $status) ? __( 'Continue', 'lucidlms' ) : __( 'Review previous lessons', 'lucidlms' );

if( $is_completed ): ?>

    <?php if( 'sc_completed' == $scorecard->get_status() ): ?>
        <p class="lucidlms-info" class="element-completed">

            <?php printf( __( 'Congratulations! You have successfully completed the course. Check your e-mail for an issued certificate or download it by clicking <a href="%s" target="_blank">here</a>.', 'lucidlms' ), $scorecard->get_certificate_link() ); ?>
        </p>
    <?php endif; ?>

    <?php do_action( 'before_quiz_completed_notices', $status, $user_can_try_again ) ?>

    <?php if( $next_course_element && ( ('completed' == $status) || !$course->sequential_logic ) ): // if course element exists and state is not failed if sequental logic enabled ?>
        <form class="lucidlms-info" action="<?php echo get_the_permalink( $next_course_element->id ) ?>" method="post">
            <a href="<?php echo $course->get_permalink() ?>" class="back">&larr; <?php _e( 'Back to the course', 'lucidlms' ); ?></a>
            <input type="submit" value="<?php printf("%s %s &rarr;", __( 'Next', 'lucidlms' ), strtolower($next_course_element->get_type_name())) ?>" />
        </form>
    <?php else: ?>
        <form class="lucidlms-info" action="<?php echo $course->get_permalink() ?>" method="post">
            <input type="submit" value="<?php echo $continue_button_text ?>" />
        </form>
    <?php endif; ?>

    <?php if( $user_can_try_again ): ?>
        <form class="lucidlms-info" method="POST" action="<?php echo $course_element->try_again_course_element_url(); ?>">
            <input class="button" type="submit" value="<?php echo $course_element->try_again_course_element_text(); ?>" />
            <span class="lucidlms-note">
                <?php _e( 'Note: results of your previous attempt will be lost.', 'lucidlms' ); ?>
            </span>
        </form>
    <?php endif; ?>

    <?php return add_filter( 'lucidlms_start_quiz', '__return_false' );

endif; // end if completed
