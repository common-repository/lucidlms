<?php
/**
 * Loop Read more Course
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $course, $scorecard;

$status            = $scorecard !== null ? $scorecard->get_status() : '';
$continue_course = $status == 'sc_started' || $status == 'sc_expired';
$continue_course_url = $continue_course ? get_permalink() : esc_url( $course->start_course_url() );
$continue_course_text = $continue_course ? __( 'Go to course', 'lucidlms' ) :  $course->start_course_text();

echo apply_filters( 'lucidlms_loop_course_link',
	sprintf( '<a href="%s" rel="nofollow" class="read-more"><button>%s</button></a>',
		$continue_course_url,
		$continue_course_text
	),
	$course );

if ( ! $course->content ) {
	return;
}