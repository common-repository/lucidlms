<?php
/**
 * Question: open type
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

<?php $question_results = $course_element_results !== null && isset( $course_element_results->questions[ $id ] ) ? $course_element_results->questions[ $id ]['answers'] : false; ?>
<textarea name="<?php echo $id; ?>" cols="200" rows="5" placeholder="<?php _e( 'Answer the question here', 'lucidlms' ); ?>"><?php echo isset( $question_results['open'] ) ? $question_results['open'] : ''; ?></textarea>