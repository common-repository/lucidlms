<?php
/**
 * LucidLMS Template Hooks
 *
 * Action/filter hooks used for LucidLMS functions/templates
 *
 * @author        New Normal
 * @category      Core
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

/**
 * Content Containers
 *
 * @see lucidlms_output_content_container()
 * @see lucidlms_output_content_container_end()
 */
add_action( 'lucidlms_before_main_content', 'lucidlms_output_content_container', 10 );
add_action( 'lucidlms_after_main_content', 'lucidlms_output_content_container_end', 10 );

/**
 * Sidebar
 *
 * @see lucidlms_get_sidebar()
 */
add_action( 'lucidlms_sidebar', 'lucidlms_get_sidebar', 10 );

/*************************************************
 * Courses Loop
 ************************************************/

/**
 * Course Loop Items
 *
 * @see lucidlms_template_loop_excerpt()
 * @see lucidlms_template_loop_readmore_course()
 * @see lucidlms_template_loop_course_thumbnail()
 */
add_action( 'lucidlms_after_courses_loop_item', 'lucidlms_template_loop_excerpt', 10 );
add_action( 'lucidlms_after_courses_loop_item', 'lucidlms_template_loop_readmore_course', 20 );
add_action( 'lucidlms_before_courses_loop_item_title', 'lucidlms_template_loop_course_thumbnail', 10 );

/**
 * Pagination after courses loops
 *
 * @see lucidlms_pagination()
 */
add_action( 'lucidlms_after_courses_loop', 'lucidlms_pagination', 10 );

/**
 * Get all courses
 *
 * @see lucidlms_load_all_courses()
 */
add_action( 'lucidlms_get_all_courses', 'lucidlms_load_all_courses' );

/**
 * Get category courses
 *
 * @see lucidlms_load_category_courses()
 */
add_action( 'lucidlms_get_category_courses', 'lucidlms_load_category_courses' );

/**
 * Get searched courses
 *
 * @see lucidlms_load_searched_courses()
 */
add_action( 'lucidlms_get_searched_courses', 'lucidlms_load_searched_courses' );

/*************************************************
 * Single Course
 ************************************************/

/**
 * Before Single Course Header Div
 *
 * @see lucidlms_show_course_image()
 */
add_action( 'lucidlms_before_single_course_header', 'lucidlms_show_course_image', 20 );

/**
 * Course Header
 *
 * @see lucidlms_template_single_title()
 * @see lucidlms_template_single_meta()
 * @see lucidlms_template_single_sharing()
 */
add_action( 'lucidlms_single_course_header', 'lucidlms_template_single_title', 5 );
if ( get_option( 'lucidlms_course_category_hide' ) != 'yes' ) {
	add_action( 'lucidlms_single_course_header', 'lucidlms_template_single_meta', 40 );
}
add_action( 'lucidlms_single_course_header', 'lucidlms_template_single_sharing', 50 );

/**
 * Course page content
 *
 * @see lucidlms_template_single_description()
 * @see lucidlms_course_content_elements()
 * @see lucidlms_course_complete()
 */
add_action( 'lucidlms_after_single_course_header', 'lucidlms_template_single_description', 10 );
add_action( 'lucidlms_after_single_course_header', 'lucidlms_course_complete', 20 );
add_action( 'lucidlms_after_single_course_header', 'lucidlms_course_content_elements', 30 );
add_action( 'lucidlms_after_single_course_header', 'lucidlms_course_bbpress_forums', 60 );


/**
 * Start Course button
 *
 * @see lucidlms_template_single_start_course()
 * @see lucidlms_course_start()
 * @see lucidlms_course_progress()
 */
add_action( 'lucidlms_after_single_course_header', 'lucidlms_template_single_start_course', 50 );
add_action( 'lucidlms_course_start', 'lucidlms_course_start', 30 );
add_action( 'lucidlms_course_start', 'lucidlms_course_progress', 30 );

/*************************************************
 * Single Course element
 ************************************************/

/**
 * Course Element Header
 *
 * @see lucidlms_template_single_title()
 */
add_action( 'lucidlms_single_course_element_header', 'lucidlms_template_single_title', 10 );

/**
 * Course Element page content
 *
 * @see lucidlms_template_single_description()
 */
add_action( 'lucidlms_after_single_course_element_header', 'lucidlms_template_single_description', 10 );

/**
 * Course Element Interaction area
 *
 * @see lucidlms_template_single_interaction_course_element()
 * @see lucidlms_lesson_complete()
 * @see lucidlms_quiz()
 */
add_action( 'lucidlms_after_single_course_element_header', 'lucidlms_template_single_interaction_course_element', 50 );
add_action( 'lucidlms_lesson_interaction_area', 'lucidlms_lesson_complete', 30 );
add_action( 'lucidlms_quiz_interaction_area', 'lucidlms_quiz', 30 );

/**
 * Before quiz area
 *
 * @see lucidlms_quiz_results_wrap()
 * @see lucidlms_quiz_notices()
 */
add_action( 'lucidlms_before_quiz', 'lucidlms_quiz_results_wrap', 10 );
add_action( 'lucidlms_before_quiz', 'lucidlms_quiz_notices', 20 );

/**
 * Question template within questions loop
 *
 * @see lucidlms_quiz_question_view()
 */
add_action( 'lucidlms_template_single_question', 'lucidlms_quiz_question_view', 10, 2 );

/**
 * Completed quiz results
 *
 * @see lucidlms_template_quiz_review()
 */
add_action( 'lucidlms_template_quiz_review', 'lucidlms_template_quiz_review', 10 );

/**
 * Completed quiz results
 *
 * @see lucidlms_completed_quiz_results()
 * @see lucidlms_quiz_notices()
 */
add_action( 'lucidlms_completed_quiz_results', 'lucidlms_completed_quiz_results', 10 );

/**
 * Quiz notices
 *
 * @see lucidlms_quiz_notices()
 */
add_action( 'lucidlms_quiz_notices', 'lucidlms_quiz_notices', 20 );

/**
 * Show "You've passed/failed quiz message"
 *
 * @see quiz_status_notice
 */
add_action( 'before_quiz_completed_notices', 'quiz_status_notice', 10, 2 );

/**
 * Calculate quiz score and show appropriate message
 *
 * $see lucidlms_calculate_quiz_score()
 */
add_action( 'lucidlms_calculate_quiz_score', 'lucidlms_calculate_quiz_score', 10 );

/**
 * Add nice "Back to course" link for course elements
 */
add_action( 'lucidlms_before_single_course_element_header', 'lucidlms_back_to_course_link', 10 );

/**
 * Send course name after publish the course to the remote server
 */
add_action( 'publish_course', 'lucidlms_get_publish_course_info', 10, 2 );

/**
 * Send course id after course start to the remote server
 */
add_action( 'lucidlms_started_course', 'lucidlms_get_started_course_info', 10 );
