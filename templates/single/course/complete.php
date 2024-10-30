<?php
/**
 * Course Complete button (output only for course type)
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

if ( $scorecard === null || ( $status !== 'sc_completed' ) || ! $course->is_type( 'course' ) ) {
	return;
}

/**
 * Complete course and send an email with certificate download link
 *
 * @see lucidlms_complete_course()
 */
//do_action( 'lucidlms_complete_course' );

?>
<p class="lucidlms-info">
	<?php printf( __( 'Congratulations! You have successfully completed the course. Check your e-mail for an issued certificate or download it by clicking <a href="%s" target="_blank">here</a>.', 'lucidlms' ), $scorecard->get_certificate_link() ); ?>
</p>
<form class="lucidlms-info" action="<?php echo get_permalink( lucid_get_page_id( 'studentprofile' ) ); ?>" method="post">
	<input type="submit" value="<?php _e( 'Return to student profile', 'lucidlms' ); ?>" />
</form>