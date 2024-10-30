<?php
/**
 * Quiz score results - course threshold type: final
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $course_element, $course_element_results;

if ( $course_element->threshold && $course_element_results->score >= 0 ) {

	$is_final = $course_element->is_final();
	?>

    <p class="lucidlms-info align-center">
		<?php
		if ( ! $is_final ) {
			_e( 'Congratulations! You\'ve passed the quiz.', 'lucidlms' );

			return;
		}
		?>

		<?php if ( $course_element_results->score >= $course_element->threshold ) {
			echo sprintf( __( 'Congratulations! You\'ve passed the final quiz. Your score is %s out of 100.', 'lucidlms' ), $course_element_results->score );
		} else {
			echo sprintf( __( 'Sorry, you did not receive a passing score. The threshold is %s and your score is %s. Please try again.', 'lucidlms' ), $course_element->threshold, $course_element_results->score );
		} ?>
    </p>
	<?php
}