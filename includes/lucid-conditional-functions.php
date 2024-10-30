<?php
/**
 * LucidLMS Conditional Functions
 *
 * Functions for determining the current query/page.
 *
 * @author 		New Normal
 * @category 	Core
 * @package 	LucidLMS/Functions
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists( 'is_courses' ) ) {

	/**
	 * is_courses - Returns true when viewing the course element type archive (courses).
	 *
	 * @access public
	 * @return bool
	 */
	function is_courses() {
		return ( is_post_type_archive( 'course' ) || is_page( lucid_get_page_id( 'courses' ) ) ) ? true : false;
	}
}

if ( ! function_exists( 'is_course_category' ) ) {

	/**
	 * is_course_category - Returns true when viewing a course category.
	 *
	 * @access public
	 * @param string $term (default: '') The term slug your checking for. Leave blank to return true on any.
	 * @return bool
	 */
	function is_course_category( $term = '' ) {
		return is_tax( 'course_cat', $term );
	}
}

if ( ! function_exists( 'is_course_tag' ) ) {

	/**
	 * is_course_tag - Returns true when viewing a course tag.
	 *
	 * @access public
	 * @param string $term (default: '') The term slug your checking for. Leave blank to return true on any.
	 * @return bool
	 */
	function is_course_tag( $term = '' ) {
		return is_tax( 'course_tag', $term );
	}
}

if ( ! function_exists( 'is_course' ) ) {

	/**
	 * is_course - Returns true when viewing a single course.
	 *
	 * @access public
	 * @return bool
	 */
	function is_course() {
		return is_singular( array( 'course' ) );
	}
}

if ( ! function_exists( 'is_course_element' ) ) {

	/**
	 * is_course_element - Returns true when viewing a single course element.
	 *
	 * @access public
	 * @return bool
	 */
	function is_course_element() {
		return is_singular( array( 'course_element' ) );
	}
}

if ( ! function_exists( 'is_lesson' ) ) {

	/**
	 * is_lesson - Returns true when viewing a single lesson.
	 *
	 * @access public
	 * @return bool
	 */
	function is_lesson() {
		return has_term( 'lesson', AETYPE );
	}
}

if ( ! function_exists( 'is_quiz' ) ) {

	/**
	 * is_quiz - Returns true when viewing a single quiz.
	 *
	 * @access public
	 * @return bool
	 */
	function is_quiz() {
		return has_term( 'quiz', AETYPE );
	}
}

if ( ! function_exists( 'is_student_profile_page' ) ) {

	/**
	 * is_student_profile_page - Returns true when viewing a student profile page.
	 *
	 * @access public
	 * @return bool
	 */
	function is_student_profile_page() {
		return is_page( lucid_get_page_id( 'studentprofile' ) ) || apply_filters( 'lucidlms_is_studentprofile_page', false ) ? true : false;
	}
}

if ( ! function_exists( 'is_completed_course_page' ) ) {

	/**
	 * is_completed_course_page - Returns true when viewing the completed course page.
	 *
	 * @access public
	 * @return bool
	 */
	function is_completed_course_page() {
		global $wp;

		return ( is_singular( array( 'course' ) ) && isset( $wp->query_vars['completed'] ) ) ? true : false;
	}
}

if ( ! function_exists( 'is_ajax' ) ) {

	/**
	 * is_ajax - Returns true when the page is loaded via ajax.
	 *
	 * @access public
	 * @return bool
	 */
	function is_ajax() {
		return defined( 'DOING_AJAX' );
	}
}