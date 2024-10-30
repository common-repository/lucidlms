<?php
/**
 * Course progress
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

if ( $status === 'sc_started' ) {

	// TODO[future-releases] show some progress.
	?>
	<p class="lucidlms-info">
		<?php _e( 'In order to earn your Certificate of Completion, you must successfully complete all sections of the above course.', 'lucidlms' ); ?>
	</p>
<?php }