<?php
/**
 * LucidLMS Admin Functions
 *
 * @author      New Normal
 * @category    Core
 * @package     LucidLMS/Admin/Functions
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Get all LucidLMS screen ids
 *
 * @return array
 */
function lucid_get_screen_ids() {
	$lucid_screen_id = sanitize_title( __( 'LucidLMS', 'lucidlms' ) );

	return apply_filters( 'lucidlms_screen_ids', array(
		'toplevel_page_' . $lucid_screen_id,
		$lucid_screen_id . '_page_lucid-dashboard',
		$lucid_screen_id . '_page_lucid-question-pool',
//		$lucid_screen_id . '_page_lucid-reports',
		$lucid_screen_id . '_page_lucid-settings',
		'edit-score_card',
		'score_card',
		'course',
		'instructors',
		'edit-course',
		'course_element',
		'edit-course_element',
		'edit-course_cat',
	) );
}

/**
 * Create a page and store the ID in an option.
 *
 * @access public
 *
 * @param mixed $slug Slug for the new page
 * @param mixed $option Option name to store the page's ID
 * @param string $page_title (default: '') Title for the new page
 * @param string $page_content (default: '') Content for the new page
 * @param int $post_parent (default: 0) Parent for the new page
 *
 * @return int page ID
 */
function lucid_create_page( $slug, $option = '', $page_title = '', $page_content = '', $post_parent = 0 ) {
	global $wpdb;

	$option_value = get_option( $option );

	if ( $option_value > 0 && get_post( $option_value ) ) {
		return - 1;
	}

	$page_found = null;

	if ( strlen( $page_content ) > 0 ) {
		// Search for an existing page with the specified page content (typically a shortcode)
		$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
	} else {
		// Search for an existing page with the specified page slug
		$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_name = %s LIMIT 1;", $slug ) );
	}

	if ( $page_found ) {
		if ( ! $option_value ) {
			update_option( $option, $page_found );
		}

		return $page_found;
	}

	$page_data = array(
		'post_status'    => 'publish',
		'post_type'      => 'page',
		'post_author'    => 1,
		'post_name'      => $slug,
		'post_title'     => $page_title,
		'post_content'   => $page_content,
		'post_parent'    => $post_parent,
		'comment_status' => 'closed'
	);
	$page_id   = wp_insert_post( $page_data );

	if ( $option ) {
		update_option( $option, $page_id );
	}

	return $page_id;
}

/**
 * Output admin fields.
 *
 * Loops though the lucidlms options array and outputs each field.
 *
 * @param array $options Opens array to output
 */
function lucidlms_admin_fields( $options ) {
	if ( ! class_exists( 'LU_Admin_Settings' ) ) {
		include 'class-lucid-admin-settings.php';
	}

	LU_Admin_Settings::output_fields( $options );
}

/**
 * Update all settings which are passed.
 *
 * @access public
 *
 * @param array $options
 *
 * @return void
 */
function lucidlms_update_options( $options ) {
	if ( ! class_exists( 'LU_Admin_Settings' ) ) {
		include 'class-lucid-admin-settings.php';
	}

	LU_Admin_Settings::save_fields( $options );
}

/**
 * Get a setting from the settings API.
 *
 * @param mixed $option
 *
 * @return string
 */
function lucidlms_settings_get_option( $option_name, $default = '' ) {
	if ( ! class_exists( 'LU_Admin_Settings' ) ) {
		include 'class-lucid-admin-settings.php';
	}

	return LU_Admin_Settings::get_option( $option_name, $default );
}

/**
 * Add post type name as <body> tag class ('course' for courses and 'quiz/lesson' for course elements)
 *
 * @param $classes
 *
 * @return string
 */
function lucidlms_admin_add_custom_post_type_body_class( $classes ) {
	global $post;
	if ( $post ) {
		switch ( $post->post_type ) {
			case 'course_element':
				$activity_element = get_course_element( $post );
				$classes          .= " $activity_element->course_element_type";
				break;
			case 'course':
				$classes .= " course";
				break;
		}
	}

	return $classes;
}

add_filter( 'admin_body_class', 'lucidlms_admin_add_custom_post_type_body_class' );

/**
 * Change
 *
 * @param $url
 * @param $path
 * @param $blog_id
 *
 * @return string
 */
function add_new_post_url( $url, $path, $blog_id ) {

	global $post;

	if ( ! $post ) {
		return $url;
	}

	if ( $path == "post-new.php?post_type=course_element" ) {
		$course_element = get_course_element( get_the_ID() );
		$course         = $course_element->get_parent_course();
		$path .= "&course_id=" . $course->id;

		return $path;
	}

	return $url;
}

add_filter( 'admin_url', 'add_new_post_url', 10, 3 );