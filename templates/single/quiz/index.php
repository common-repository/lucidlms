<?php
/**
 * Quiz index file - Questions area
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $course_element, $course_element_results, $scorecard, $course;

$sc_status                = $scorecard !== null ? $scorecard->get_status() : '';
$sc_course_element_status = $course_element_results !== null ? $course_element_results->get_status() : '';
$next_course_element      = $course_element->get_next_course_element();

do_action( 'lucidlms_before_quiz' );

if ( apply_filters( 'lucidlms_start_quiz', true ) ) {
	?>

    <div class="questions-container">

		<?php do_action( 'lucidlms_before_quiz_questions' ); ?>

		<?php $questions = $course_element->get_questions_list( true ); ?>

        <ul class="questions-pagination">

			<?php
			$index             = 1;
			$prev_active       = $active_as_exception = false;
			$questions_results = $course_element_results !== null ? $course_element_results->questions : false;
			foreach ( $questions as $id => $question ) {

				// Check if results exists and if so, check whether specific question has an answer
				$active = $questions_results && isset( $questions_results[ $id ] );

				/**
				 * Activate question as an exception in some cases (custom activation)
				 *
				 * If current question has no answers, then we need to check
				 * whether previous question was answered or not, and if
				 * it was - activate this view for a student.
				 *
				 * We always activate the first item.
				 *
				 * @filter - lucidlms_questions_pagination_custom_activation
				 * Use global $course_element_results variable in filter if needed
				 */
				if ( apply_filters( 'lucidlms_questions_pagination_custom_activation', ! $active && $prev_active || $index === 1, $id ) ) {
					$active_as_exception = true;
				} ?>

                <li data-id="<?php echo $id; ?>">
					<?php echo ( $active || $active_as_exception ) ? '<a href="#">' . $index . '</a>' : $index; ?>
                </li>

				<?php
				$index ++;

				$prev_active         = $active;
				$active_as_exception = false;
			} ?>

            <li data-id="review">
				<?php // If the last previous question was active, then we definitely activate review stage
				echo $prev_active ? '<a href="#">' . __( 'Review', 'lucidlms' ) . '</a>' : __( 'Review', 'lucidlms' ); ?>
            </li>

        </ul>
        <!-- .questions-pagination -->

		<?php
		$duration = floatval( $course_element->duration );

		// Check if we need to activate timer
		if ( $duration && is_numeric( $duration ) ) {

			$duration = round( $duration * MINUTE_IN_SECONDS );

			$time_spent               = $course_element_results !== null ? $course_element_results->time_spent : 0;
			$current_start_in_seconds = $duration - $time_spent;

			if ( $current_start_in_seconds ) {
				$current_start = gmdate( "H:i:s", $current_start_in_seconds );
				echo '<div class="lucidlms-info">';
				echo '<p>' . __( 'You are given a limited time to pass the quiz. If you exceed the time, results will automatically be submitted.', 'lucidlms' ) . '</p>';
				echo '<span id="quiz-timer" disabled>' . $current_start . '</span>';
				echo '</div>';
			}
		}
		?>

        <ul class="handle-area questions">

			<?php foreach ( $questions as $id => $question ) { ?>
                <li data-id="<?php echo $id; ?>" data-type="<?php echo $question['question_type']; ?>">
					<?php do_action( 'lucidlms_template_single_question', $id, $question ); ?>
                </li>
			<?php } ?>

            <li class="review-screen" data-id="review">
				<?php // At the end, we add a review screen
				do_action( 'lucidlms_template_quiz_review' ); ?>
            </li>
        </ul>
        <!-- .questions -->

        <ul class="questions-nav">
            <li class="next">
                <button><?php _e( 'Next', 'lucidlms' ); ?></button>
            </li>
            <li class="prev">
                <a href="#"><?php _e( 'Previous', 'lucidlms' ); ?></a>
            </li>
        </ul>
        <!-- .questions-nav -->

		<?php do_action( 'lucidlms_after_quiz_questions' ); ?>

    </div><!-- .questions-container -->

	<?php

	if ( $sc_course_element_status == 'completed' || $sc_status == 'sc_completed' || $sc_status == 'sc_expired' ) { ?>

        <p class="lucidlms-info" id="element-completed">
			<?php if ( 'sc_completed' == $sc_status ) {
				printf( __( 'Congratulations! You have successfully completed the course. Check your e-mail for an issued certificate or download it by clicking <a href="%s" target="_blank">here</a>.', 'lucidlms' ), $scorecard->get_certificate_link() );
			} ?>
        </p>

		<?php if ( $next_course_element && ( 'sc_expired' != $sc_status ) ): // if next course exist ?>
            <form class="lucidlms-info completed-controls"
                  action="<?php echo get_the_permalink( $next_course_element->id ) ?>" method="post">
                <a href="<?php echo $course->get_permalink() ?>"
                   class="back lms-button lms-btn-gray">&larr; <?php _e( 'Back to the course', 'lucidlms' ); ?></a>
                <input class="lms-button lms-btn-gray next" type="submit"
                       value="<?php printf( "%s %s &rarr;", __( 'Next', 'lucidlms' ), strtolower( $next_course_element->get_type_name() ) ) ?>"/>
            </form>
		<?php else: ?>
            <form class="lucidlms-info" action="<?php echo $course->get_permalink() ?>" method="post">
                <input type="submit" value="<?php _e( 'Continue', 'lucidlms' ); ?>"/>
            </form>
		<?php endif; ?>

		<?php
		return;

	} else { ?>

        <div class="questions-loading-screen">
            <i class="fa fa-cog fa-spin fa-2x"></i>

            <div class="start-quiz-screen lucidlms-info">

                <p>
					<?php if ( $sc_course_element_status == 'started' ) {
						_e( 'This quiz was not completed for some reason, be strong and continue.', 'lucidlms' );
					} else {
						_e( 'Start passing the quiz when you\'re ready. Good luck!', 'lucidlms' );
					} ?>
                </p>

                <button class="lms-button lms-btn-empty">
					<?php if ( $sc_course_element_status == 'started' ) {
						_e( 'Continue quiz', 'lucidlms' );
					} else {
						_e( 'Start quiz', 'lucidlms' );
					} ?>
                </button>
            </div>

        </div><!-- .questions-loading -->

	<?php } ?>

    <div class="quiz-completed">
		<?php do_action( 'lucidlms_completed_quiz_results' ); ?>
    </div>
    <!-- .quiz-completed -->

	<?php
}

do_action( 'lucidlms_after_quiz' );