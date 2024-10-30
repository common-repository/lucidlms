<?php
/**
 * Questions review
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $course_element, $course_element_results;

$questions = $course_element->get_questions_list(true);

$questions_results = $course_element_results !== null ? $course_element_results->questions : false;

// Get out of here if there's no question answers at all
if ( ! $questions_results ) {
	return;
}

echo '<h4>' . __( 'Review your answers before submitting them', 'lucidlms' ) . '</h4>';

$is_all_answers_exists = true; ?>

	<ol class="review-questions">
		<?php foreach ( $questions as $id => $question ) {

			$is_answer_exists = isset( $questions_results[ $id ] );

			if ( ! $is_answer_exists ) {
				$is_all_answers_exists = false;
			}

			?>
			<li>
				<span class="question-title"><?php echo sprintf( __( 'Q: %s', 'lucidlms' ), $question['question_text'] ); ?></span>
				<span class="question-user-answer"><?php echo sprintf( __( 'A: %s', 'lucidlms' ), $is_answer_exists ? implode( ', ', $questions_results[ $id ]['answers'] ) : '<span class="no-answer">' . __( 'Answer is missing.', 'lucidlms' ) . '</span>' ); ?></span>
			</li>
		<?php } ?>
	</ol>

<?php
if ( $is_all_answers_exists ) { ?>
	<p class="lucidlms-info">
		<?php echo '<button class="complete-quiz">' . __( 'Complete quiz', 'lucidlms' ) . '</button>'; ?>
	</p>
<?php } else {
	_e( 'We did not receive all the answers from you, come back when you are done with all answers.', 'lucidlms' );
}