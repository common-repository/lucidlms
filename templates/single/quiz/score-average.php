<?php
/**
 * Quiz score results - course threshold type: average
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $course_element, $course_element_results;

if ( $course_element->threshold && $course_element_results->score ) {
	?>

	<p class="lucidlms-info">
		<?php if ( $course_element_results->score >= $course_element->threshold ) {
			echo sprintf( __( 'Congratulations! You\'ve passed the quiz. Your score is %s out of 100.', 'lucidlms' ), $course_element_results->score );
		} else {
			echo sprintf( __( 'Sorry! You didn\'t get needed score. Threshold is %s and your score is %s. Please try again if possible.', 'lucidlms' ), $course_element->threshold, $course_element_results->score );
		} ?>
	</p>
<?php
}

