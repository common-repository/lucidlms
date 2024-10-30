<?php
/**
 * Single Course Share
 *
 * Sharing plugins can hook into here or you can add your own code directly.
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

?>

<?php do_action( 'lucidlms_single_course_share' ); // Sharing plugins can hook into here