<?php
/**
 * Question: single choice type
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $course_element_results;

/**
 * We use extracted variables in template
 *
 * @var $id
 * @var $question
 */
?>

	<h4><?php echo $question['question_text']; ?></h4>
<?php echo $question['question_text_extended']; ?>

<?php

$question_results = $course_element_results !== null && isset( $course_element_results->questions[ $id ] ) ? $course_element_results->questions[ $id ]['answers'] : false;

foreach ( $question['answers'] as $answer_id => $answer_array ) {
	?>
	<div class="radio">
		<label>
			<input type="radio" name="<?php echo $id; ?>" value="<?php echo $answer_id; ?>" <?php echo isset($question_results[$answer_id]) ? 'checked' : ''; ?> />
			<span data-answer-id="<?php echo $answer_id; ?>" class="answer"><?php echo $answer_array['answer_extended'] ? $answer_array['answer_extended'] : $answer_array['answer']; ?></span>
		</label>
	</div>
<?php } ?>