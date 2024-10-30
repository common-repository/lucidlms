<?php
/**
 * Single course excerpt
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $course;

if ( ! $course->content ) {
	return;
}

?>

<div class="excerpt">
	<?php echo apply_filters( 'lucidlms_excerpt', get_the_excerpt() ) ?>
</div>