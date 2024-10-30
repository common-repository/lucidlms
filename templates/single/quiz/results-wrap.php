<?php
/**
 * Quiz Results wrap (used to show quiz results upon loading quiz page)
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $course_element_results;

$status = $course_element_results !== null ? $course_element_results->get_status() : '';

// we show result if quiz was previously completed
if ( $status == 'completed' ) {
	?>
	<p class="lucidlms-info">
		<button class="show-quiz-results"><?php _e( 'Show your previous results', 'lucidlms' ); ?></button>
	</p>
	<div class="quiz-completed">
		<?php do_action( 'lucidlms_completed_quiz_results' ); ?>
	</div>
	<!-- .quiz-completed -->
<?php
}