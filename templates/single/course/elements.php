<?php
/**
 * Course elements area
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $course, $scorecard;

$sc_status = $scorecard !== null ? $scorecard->get_status() : '';

if ( $course->is_type( 'course' ) ) {

	$course_visibility = $course->visibility;
	$sequential_logic  = $course->sequential_logic;
	$course_elements   = $course->get_elements_list();

	echo apply_filters( 'lucidlms_course_content_elements_title', '<h2 id="lucidlms-curriculum">' . __( 'Curriculum', 'lucidlms' ) . '</h2>' );

	echo '<ul class="course-elements">';

	$is_open_by_sequential_logic = false;
	$previous_is_completed       = true; // to open first
	foreach ( $course_elements as $course_element_id => $course_element ) {

		$course_element_class = get_course_element( $course_element_id );

		// Sequential logic for every course element
		if ( $sequential_logic ) {
			$is_open_by_sequential_logic = $previous_is_completed;
		} else {
			$is_open_by_sequential_logic = true;
		}

		$course_element_status      = isset( $scorecard->score_card_elements[ $course_element_id ] ) ? $scorecard->score_card_elements[ $course_element_id ]->status : '';
		$course_element_status_name = isset( $scorecard->score_card_elements[ $course_element_id ] ) ? $scorecard->score_card_elements[ $course_element_id ]->get_status_name() : '';
		$previous_is_completed      = $course_element_status == 'completed';

		// Build html for the course element
		$element       = '';
		$element_start = '<li>';
		$element_end   = '</li>';
		$element_icon  = sprintf( '<i class="content-icon fa %s" title="%s"></i>', ( 'quiz' == $course_element['type'] ) ? 'fa-question' : 'fa-book', $course_element['type_name'] );


		if ( apply_filters( 'lucidlms_course_element_passing_logic', ( $sc_status == 'sc_started' && $is_open_by_sequential_logic ) || ( $sc_status == 'sc_completed' && $course_visibility ), $course_element_id, $course, $scorecard ) ) {

			$element_start .= '<a href="' . $course_element_class->get_permalink() . '">';
			$element_end = '</a>' . $element_end;
		}

		$element .= $course_element['type_name'];
		$element .= apply_filters( 'lucidlms_course_content_elements_title_separator', ' - ' );
		$element .= apply_filters( 'lucidlms_course_elements_title', $course_element['title'], $course_element_id, $scorecard );
		$element .= ! empty( $course_element_status_name ) ? ' (' . $course_element_status_name . ')' : '';

		$element = apply_filters( 'lucidlms_course_content_elements_title_additional_info', $element );

		echo apply_filters( 'lucidlms_course_content_elements_title_html', $element_start . $element_icon . $element . $element_end, $course_element_id );
	}
	echo '</ul>';

}