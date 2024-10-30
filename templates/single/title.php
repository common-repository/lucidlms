<?php
/**
 * Single title
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $course, $course_element;

// Show different icons based on instance type
$type_html = '<i class="fa fa-file-text fa-1x"></i>';

if ( $course ) {
	$course_type = $course->get_type();
}

if ( $course_element ) {
	$course_element_type = $course_element->get_type();

	switch ( $course_element_type ) {
		case 'lesson':
			$type_html = '<i class="fa fa-info-circle fa-1x"></i>';
			break;
		case 'quiz':
			$type_html = '<i class="fa fa-question-circle fa-1x"></i>';
			break;
	}
}
?>

<h1 class="title entry-title">
	<?php echo apply_filters( 'lucidlms_single_type_title_icon', $type_html, $course, $course_element ); ?>
	<?php echo apply_filters( 'lucidlms_single_type_title', get_the_title(), $course, $course_element ); ?>
</h1>