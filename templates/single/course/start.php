<?php
/**
 * Course Start button
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $course, $scorecard;

$status = $scorecard !== null ? $scorecard->get_status() : '';

if ( $status == 'sc_started' || $status == 'sc_expired' ) {
	return;
}
?>

<?php do_action( 'lucidlms_before_start_course_button' ); ?>

<div class="handle-area">
    <form id="course-start" method="POST" action="<?php echo $course->start_course_url(); ?>">
        <input class="button" type="submit" value="<?php echo $course->start_course_text( false, true ); ?>">
    </form>
</div>

<?php do_action( 'lucidlms_after_start_course_button' ); ?>
