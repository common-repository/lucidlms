<?php
/**
 * Displayed when no courses are found matching the current query.
 *
 * Override this template by copying it to yourtheme/lucidlms/loop/no-courses.php
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>
<p class="lucidlms-info"><?php _e( 'No courses were found.', 'lucidlms' ); ?></p>