<?php
/**
 * Quiz Results upon completion
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $course_element, $course_element_results, $course;

$questions = $course_element->get_questions_list(true);
$status    = $course_element_results !== null ? $course_element_results->get_status() : '';

// Don't show results if the quiz is not completed
//if ( $status !== 'completed' ) {
//	return;
//}
?>

<?php echo '<h4>' . __( 'Quiz results', 'lucidlms' ) . '</h4>'; ?>
	<ol class="quiz-results">
		<?php foreach ( $questions as $id => $question ) { ?>
			<li>
				<span class="question-title"><?php echo sprintf( __( 'Q: %s', 'lucidlms' ), $question['question_text'] ); ?></span>
				<?php if ( isset( $course_element_results->questions[ $id ] ) ) { ?>
					<span class="question-user-answer"><?php echo sprintf( __( 'A: %s', 'lucidlms' ), implode( ', ', $course_element_results->questions[ $id ]['answers'] ) ) ?></span>
					<span class="question-is-correct <?php echo $course_element_results->questions[ $id ]['is_answers_correct'] ? 'correct' : 'incorrect'; ?>">
						<?php echo $course_element_results->questions[ $id ]['is_answers_correct'] ? __( 'Correct', 'lucidlms' ) : __( 'Incorrect', 'lucidlms' ); ?>
					</span>
				<?php } else { ?>
					<span class="question-user-answer"><?php _e( 'A: missing', 'lucidlms' ) ?></span>
				<?php } ?>
			</li>
		<?php } ?>
	</ol>

<?php

$parent_course_threshold_type = $course->threshold_type;

// Show score if needed
do_action( 'lucidlms_calculate_quiz_score', $parent_course_threshold_type );