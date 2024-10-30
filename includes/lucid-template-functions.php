<?php
/**
 * LucidLMS Template
 *
 * Functions for the templating system.
 *
 * @author        New Normal
 * @category      Core
 * @package       LucidLMS/Functions
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/*************************************************
 * General things
 ************************************************/

/**
 * Handle redirects before content is output - hooked into template_redirect so is_page works.
 *
 * @return void
 */
function lucid_template_redirect() {
	global $wp_query, $wp;

	// When default permalinks are enabled, redirect courses page to post type archive url
	if ( ! empty( $_GET['page_id'] ) && get_option( 'permalink_structure' ) == "" && $_GET['page_id'] == lucid_get_page_id( 'courses' ) ) {
		wp_safe_redirect( get_post_type_archive_link( 'course' ) );
		exit;
	} // Redirect to the course page if we have a single course
	elseif ( is_search() && is_post_type_archive( 'course' ) && apply_filters( 'lucidlms_redirect_single_search_result', true ) && $wp_query->post_count == 1 ) {
		$course = get_course( $wp_query->post );

		wp_safe_redirect( get_permalink( $course->id ), 302 );
		exit;
	}

}

add_action( 'template_redirect', 'lucid_template_redirect' );

/**
 * When the_post is called, put course data into a global.
 *
 * @param $post
 *
 * @return bool|LU_Course
 */
function lucid_setup_course_data( $post ) {
	unset( $GLOBALS['course'] );
	unset( $GLOBALS['scorecard'] );

	if ( is_int( $post ) ) {
		$post = get_post( $post );
	}

	if ( empty( $post->post_type ) || ! in_array( $post->post_type, array( 'course', 'course_element' ) ) ) {
		return false;
	}


	/** @var $course LU_Course|null */
	$course = null;
	/** @var $score_card LU_Score_Card|null */
	$score_card = null;

	if ( $post->post_type === 'course_element' ) {
		$course_element = get_course_element( $post );
		$course         = $course_element->get_parent_course();
	} else {
		$course = get_course( $post );
	}

	if ( ! empty( $course ) && $user_id = get_current_user_id() ) {
		$score_cards = lucidlms_get_score_card( $user_id, array(
			'course_id' => $course->id,
		) );
		if ( ! empty( $score_cards ) ) {
			if ( 1 == sizeof( $score_cards ) ) {
				// yeah, it is only one, exclusive
				$score_card = $score_cards[0];
			} else {
				/** @var $current_score_card LU_Score_Card */
				foreach ( $score_cards as $current_score_card ) {
					// search started score card (it might be only one)
					if ( 'sc_started' == $current_score_card->get_status() ) {
						$score_card = $current_score_card;
						break;
					}
				}

				// we still not find
				$score_card = empty( $score_card ) ? $score_cards[0] : $score_card;

			}
		}
	}

	$GLOBALS['course']    = $course;
	$GLOBALS['scorecard'] = $score_card;

	return $GLOBALS['course'];
}

add_action( 'the_post', 'lucid_setup_course_data' );

/**
 * When the_post is called, put course element data into a global.
 * Put quiz results from scorecard into a global as well.
 *
 * @param $post
 *
 * @return bool|LU_Lesson|LU_Quiz
 */
function lucid_setup_course_element_data( $post ) {
	unset( $GLOBALS['course_element'] );
	unset( $GLOBALS['course_element_results'] );

	if ( is_int( $post ) ) {
		$post = get_post( $post );
	}

	if ( empty( $post->post_type ) || ! in_array( $post->post_type, array( 'course_element' ) ) ) {
		return false;
	}

	$course_element         = get_course_element( $post );
	$course_element_results = null;

	if ( $course_element && ( $user_id = get_current_user_id() ) ) {
		if ( $course = $course_element->get_parent_course() ) {

			/** @var LU_Score_Card $score_card */
			$score_card = lucidlms_get_current_score_card( $user_id, $course->id );
			if ( $score_card ) {

				if ( isset( $score_card->score_card_elements[ $course_element->id ] ) ) {
					$course_element_results = $score_card->score_card_elements[ $course_element->id ];
				}
			}
		}
	}

	$GLOBALS['course_element']         = $course_element;
	$GLOBALS['course_element_results'] = $course_element_results;

	return $GLOBALS['course_element'];
}

add_action( 'the_post', 'lucid_setup_course_element_data' );

/*************************************************
 * Template functions: Global things
 ************************************************/

if ( ! function_exists( 'lucidlms_output_content_container' ) ) {

	/**
	 * Output the start of the page container.
	 *
	 * @access public
	 * @return void
	 */
	function lucidlms_output_content_container() {
		lucid_get_template( 'general/container-start.php' );
	}
}

if ( ! function_exists( 'lucidlms_output_content_container_end' ) ) {

	/**
	 * Output the end of the page container.
	 *
	 * @access public
	 * @return void
	 */
	function lucidlms_output_content_container_end() {
		lucid_get_template( 'general/container-end.php' );
	}
}

if ( ! function_exists( 'lucidlms_get_sidebar' ) ) {

	/**
	 * Get the courses sidebar template.
	 *
	 * @access public
	 * @return void
	 */
	function lucidlms_get_sidebar() {
		lucid_get_template( 'general/sidebar.php' );
	}
}

/*************************************************
 * Template functions: Courses Loop
 ************************************************/

if ( ! function_exists( 'lucidlms_page_title' ) ) {

	/**
	 * lucidlms_page_title function.
	 *
	 * @param  boolean $echo
	 *
	 * @return string
	 */
	function lucidlms_page_title( $echo = true ) {

		if ( is_search() ) {
			$page_title = sprintf( __( 'Search Results: &ldquo;%s&rdquo;', 'lucidlms' ), get_search_query() );

			if ( get_query_var( 'paged' ) ) {
				$page_title .= sprintf( __( '&nbsp;&ndash; Page %s', 'lucidlms' ), get_query_var( 'paged' ) );
			}

		} elseif ( is_tax() ) {

			$page_title = single_term_title( "", false );

		} else {

			$courses_page_id = lucid_get_page_id( 'courses' );
			$page_title      = get_the_title( $courses_page_id );

		}

		$page_title = apply_filters( 'lucidlms_page_title', $page_title );

		if ( $echo ) {
			echo $page_title;
		} else {
			return $page_title;
		}
	}
}

if ( ! function_exists( 'lucidlms_load_all_courses' ) ) {

	/**
	 * Get all courses template.
	 *
	 * @access        public
	 * @subpackage    Loop
	 * @return void
	 */
	function lucidlms_load_all_courses() {
		lucid_get_template( 'all-courses.php' );
	}
}

if ( ! function_exists( 'lucidlms_load_category_courses' ) ) {

	/**
	 * Get category courses template.
	 *
	 * @access        public
	 * @subpackage    Loop
	 *
	 * @param string $category_slug
	 *
	 * @return void
	 */
	function lucidlms_load_category_courses( $category_slug ) {

		$args = array(
			'category_slug' => $category_slug
		);

		lucid_get_template( 'courses-by-category.php', $args );
	}
}

if ( ! function_exists( 'lucidlms_load_searched_courses' ) ) {

	/**
	 * Get searched courses template.
	 *
	 * @access        public
	 * @subpackage    Loop
	 *
	 * @param string $search_query
	 *
	 * @return void
	 */
	function lucidlms_load_searched_courses( $search_query ) {
		$args = array(
			'search_query' => $search_query
		);

		lucid_get_template( 'searched-courses.php', $args );
	}
}

if ( ! function_exists( 'lucidlms_template_loop_readmore_course' ) ) {

	/**
	 * Get the read more template for the loop.
	 *
	 * @access        public
	 * @subpackage    Loop
	 * @return void
	 */
	function lucidlms_template_loop_readmore_course() {
		lucid_get_template( 'loop/readmore-course.php' );
	}
}

if ( ! function_exists( 'lucidlms_template_loop_course_thumbnail' ) ) {

	/**
	 * Get the course thumbnail for the loop.
	 *
	 * @access        public
	 * @subpackage    Loop
	 * @return void
	 */
	function lucidlms_template_loop_course_thumbnail() {
		echo lucidlms_get_course_thumbnail();
	}
}

if ( ! function_exists( 'lucidlms_get_course_thumbnail' ) ) {

	/**
	 * Get the course thumbnail, or the placeholder if not set.
	 *
	 * @access        public
	 * @subpackage    Loop
	 *
	 * @param string $size (default: 'thumbnail')
	 * @param int $placeholder_width (default: 0)
	 * @param int $placeholder_height (default: 0)
	 *
	 * @return string
	 */
	function lucidlms_get_course_thumbnail( $size = 'thumbnail', $placeholder_width = 0, $placeholder_height = 0 ) {
		global $post;

		if ( has_post_thumbnail() ) {
			return get_the_post_thumbnail( $post->ID, $size );
		} else {
			return lucid_placeholder_img( $size );
		}
	}
}

if ( ! function_exists( 'lucidlms_get_course_thumbnail_link' ) ) {

	/**
	 * Get the course thumbnail link, or the placeholder link if not set.
	 *
	 * @access        public
	 * @subpackage    Loop
	 *
	 * @param string $size (default: 'thumbnail')
	 *
	 * @return string
	 */
	function lucidlms_get_course_thumbnail_link( $size = 'thumbnail' ) {
		global $post;

		if ( has_post_thumbnail() ) {
			return lucid_course_thumbnail_link( $post->ID, $size );
		} else {
			return lucid_placeholder_img_link( $size );
		}
	}
}

if ( ! function_exists( 'lucidlms_pagination' ) ) {

	/**
	 * Output the pagination.
	 *
	 * @access        public
	 * @subpackage    Loop
	 * @return void
	 */
	function lucidlms_pagination() {
		lucid_get_template( 'loop/pagination.php' );
	}
}

/*************************************************
 * Template functions: General Single
 ************************************************/

if ( ! function_exists( 'lucidlms_template_single_title' ) ) {

	/**
	 * Output the course title.
	 *
	 * @access        public
	 * @subpackage    single
	 * @return void
	 */
	function lucidlms_template_single_title() {
		lucid_get_template( 'single/title.php' );
	}
}

if ( ! function_exists( 'lucidlms_template_single_description' ) ) {

	/**
	 * Output the description content.
	 *
	 * @access        public
	 * @subpackage    single
	 * @return void
	 */
	function lucidlms_template_single_description() {
		lucid_get_template( 'single/description.php' );
	}
}

/*************************************************
 * Template functions: Single Course
 ************************************************/

if ( ! function_exists( 'lucidlms_show_course_image' ) ) {

	/**
	 * Output the course image before the single course header.
	 *
	 * @access        public
	 * @subpackage    single/course
	 * @return void
	 */
	function lucidlms_show_course_image() {
		lucid_get_template( 'single/course/image.php' );
	}
}

if ( ! function_exists( 'lucidlms_template_loop_excerpt' ) ) {

	/**
	 * Output the course short description (excerpt).
	 *
	 * @access        public
	 * @subpackage    single/course
	 * @return void
	 */
	function lucidlms_template_loop_excerpt() {
		lucid_get_template( 'loop/excerpt.php' );
	}
}

if ( ! function_exists( 'lucidlms_template_single_meta' ) ) {

	/**
	 * Output the course meta.
	 *
	 * @access        public
	 * @subpackage    single/course
	 * @return void
	 */
	function lucidlms_template_single_meta() {
		lucid_get_template( 'single/course/meta.php' );
	}
}

if ( ! function_exists( 'lucidlms_template_single_sharing' ) ) {

	/**
	 * Output the course sharing.
	 *
	 * @access        public
	 * @subpackage    single/course
	 * @return void
	 */
	function lucidlms_template_single_sharing() {
		lucid_get_template( 'single/course/share.php' );
	}
}

if ( ! function_exists( 'lucidlms_course_content_elements' ) ) {

	/**
	 * Output course element for a course if exists.
	 *
	 * @access        public
	 * @subpackage    single/course
	 * @return void
	 */
	function lucidlms_course_content_elements() {
		lucid_get_template( 'single/course/elements.php' );
	}
}

if ( ! function_exists( 'lucidlms_course_bbpress_forums' ) ) {

	/**
	 * Output course bbpress forums.
	 *
	 * @access        public
	 * @subpackage    single/course
	 * @return void
	 */
	function lucidlms_course_bbpress_forums() {
		lucid_get_template( 'single/course/forums.php' );
	}
}

if ( ! function_exists( 'lucidlms_template_single_start_course' ) ) {

	/**
	 * Trigger the single start course action.
	 *
	 * @access        public
	 * @subpackage    single/course
	 * @return void
	 */
	function lucidlms_template_single_start_course() {
		global $course;
		do_action( 'lucidlms_' . $course->get_type() . '_start', $course->id );
	}
}

if ( ! function_exists( 'lucidlms_course_start' ) ) {

	/**
	 * Output the course start area.
	 *
	 * @access        public
	 * @subpackage    single/course
	 * @return void
	 */
	function lucidlms_course_start() {
		global $course;
		if ( $course->course_status == 'publish' ) {
			lucid_get_template( 'single/course/start.php' );
		}
	}
}

if ( ! function_exists( 'lucidlms_course_progress' ) ) {

	/**
	 * Output the course progress area.
	 *
	 * @access        public
	 * @subpackage    single/course
	 * @return void
	 */
	function lucidlms_course_progress() {
		lucid_get_template( 'single/course/progress.php' );
	}
}

if ( ! function_exists( 'lucidlms_course_complete' ) ) {

	/**
	 * Output the course complete area.
	 *
	 * @access        public
	 * @subpackage    single/course
	 * @return void
	 */
	function lucidlms_course_complete() {
		lucid_get_template( 'single/course/complete.php' );
	}
}

/*************************************************
 * Template functions: Single Course Element
 ************************************************/

if ( ! function_exists( 'lucidlms_template_single_interaction_course_element' ) ) {

	/**
	 * Trigger the single interaction area course element action.
	 *
	 * @access        public
	 * @subpackage    single/course-element
	 * @return void
	 */
	function lucidlms_template_single_interaction_course_element() {
		global $course_element;
		do_action( 'lucidlms_' . $course_element->get_type() . '_interaction_area' );
	}
}

if ( ! function_exists( 'lucidlms_lesson_complete_area' ) ) {

	/**
	 * Output the lesson complete button.
	 *
	 * @access        public
	 * @subpackage    single/course-element
	 * @return void
	 */
	function lucidlms_lesson_complete() {
		lucid_get_template( 'single/lesson/complete.php' );
	}
}

if ( ! function_exists( 'lucidlms_quiz' ) ) {

	/**
	 * Output the quiz index file.
	 *
	 * @access        public
	 * @subpackage    single/course-element
	 * @return void
	 */
	function lucidlms_quiz() {
		lucid_get_template( 'single/quiz/index.php' );
	}
}

if ( ! function_exists( 'lucidlms_quiz_results_wrap' ) ) {

	/**
	 * Output results wrapper.
	 *
	 * @access        public
	 * @subpackage    single/course-element
	 * @return void
	 */
	function lucidlms_quiz_results_wrap() {
		lucid_get_template( 'single/quiz/results-wrap.php' );
	}
}

if ( ! function_exists( 'lucidlms_quiz_notices' ) ) {

	/**
	 * Output quiz notices.
	 *
	 * @access        public
	 * @subpackage    single/course-element
	 * @return void
	 */
	function lucidlms_quiz_notices() {
		lucid_get_template( 'single/quiz/notices.php' );
	}
}

if ( ! function_exists( 'quiz_status_notice' ) ) {
	/**
	 * Quiz status text
	 *
	 * @param $status string
	 * @param $user_can_try_again bool
	 */
	function quiz_status_notice( $status, $user_can_try_again ) {

		if ( 'failed' == $status ) {
			if ( $user_can_try_again ) {
				$status_message = __( 'Quiz attempt unsuccessful. Please click the "Try Again" button below.', 'lucidlms' );
			} else {
				$status_message = __( 'You\'ve failed the quiz and reached the maximum number of attempts to try.', 'lucidlms' );
			}
		} else {
			$status_message = sprintf( __( 'You\'ve %s the quiz.', 'lucidlms' ), $status );;
		}

		echo '<p class="lucidlms-info">' . $status_message . '</p>';
	}
}

if ( ! function_exists( 'lucidlms_quiz_question_view' ) ) {

	/**
	 * Output single question by type within questions loop.
	 *
	 * @access        public
	 * @subpackage    single/course-element
	 *
	 * @param $id
	 * @param $question
	 *
	 * @return void
	 */
	function lucidlms_quiz_question_view( $id, $question ) {
		$args = apply_filters( 'lucidlms_quiz_questions_loop_question_data', array(
			'id'       => $id,
			'question' => $question
		) );

		$template_name = 'single/quiz/content-question-' . str_replace( '_', '-', $question['question_type'] ) . '.php';
		$template      = lucid_locate_template( $template_name );

		// Check if template for current question type exists
		if ( file_exists( $template ) ) {
			lucid_get_template( $template_name, $args );
		}
	}
}

if ( ! function_exists( 'lucidlms_template_quiz_review' ) ) {

	/**
	 * Output quiz review screen
	 *
	 * @access        public
	 * @subpackage    single/course-element
	 * @return void
	 */
	function lucidlms_template_quiz_review() {
		lucid_get_template( 'single/quiz/content-questions-review.php' );
	}
}

if ( ! function_exists( 'lucidlms_completed_quiz_results' ) ) {

	/**
	 * Output completed quiz results
	 *
	 * @access        public
	 * @subpackage    single/course-element
	 * @return void
	 */
	function lucidlms_completed_quiz_results() {
		lucid_get_template( 'single/quiz/results.php' );
	}
}

if ( ! function_exists( 'lucidlms_calculate_quiz_score' ) ) {

	/**
	 * Output calculated quiz score
	 *
	 * @access        public
	 * @subpackage    single/course-element
	 *
	 * @param $parent_course_threshold_type
	 *
	 * @return void
	 */
	function lucidlms_calculate_quiz_score( $parent_course_threshold_type ) {
		$template_name = 'single/quiz/score-' . str_replace( '_', '-', $parent_course_threshold_type ) . '.php';
		$template      = lucid_locate_template( $template_name );

		// Check if template for current question type exists
		if ( file_exists( $template ) ) {
			lucid_get_template( $template_name );
		}
	}

}

/**
 * Magic method that outputs show first class for first element in row only
 * Used at the page with course categories accordion to expand first category automatically
 */
function lucidlms_category_show_first_class( $class = 'shown' ) {
	static $was_first;

	if ( ! $was_first ) {
		echo $class;
	}
	$was_first = true;
}

if ( ! function_exists( 'lucidlms_back_to_course_link' ) ) {
	function lucidlms_back_to_course_link() {
		/** @var LU_Course_Element $course_element */
		global $post, $course_element;
		if ( $course_element && ( $course = $course_element->get_parent_course() ) ) { ?>

            <div class="course-navigation">
                <a class="back-to-course" href="<?php echo $course->get_permalink() ?>"
                   title="<?php _e( 'Back to course', 'lucidlms' ) ?>">
                    &larr;<?php _e( 'Back to course', 'lucidlms' ) ?>
                </a>
            </div>

		<?php }
	}
}

if ( ! function_exists( 'lucidlms_states_selectbox' ) ) {
	function lucidlms_states_selectbox() {
		$states_list = array(
			'AL' => "Alabama",
			'AK' => "Alaska",
			'AZ' => "Arizona",
			'AR' => "Arkansas",
			'CA' => "California",
			'CO' => "Colorado",
			'CT' => "Connecticut",
			'DE' => "Delaware",
			'DC' => "District Of Columbia",
			'FL' => "Florida",
			'GA' => "Georgia",
			'HI' => "Hawaii",
			'ID' => "Idaho",
			'IL' => "Illinois",
			'IN' => "Indiana",
			'IA' => "Iowa",
			'KS' => "Kansas",
			'KY' => "Kentucky",
			'LA' => "Louisiana",
			'ME' => "Maine",
			'MD' => "Maryland",
			'MA' => "Massachusetts",
			'MI' => "Michigan",
			'MN' => "Minnesota",
			'MS' => "Mississippi",
			'MO' => "Missouri",
			'MT' => "Montana",
			'NE' => "Nebraska",
			'NV' => "Nevada",
			'NH' => "New Hampshire",
			'NJ' => "New Jersey",
			'NM' => "New Mexico",
			'NY' => "New York",
			'NC' => "North Carolina",
			'ND' => "North Dakota",
			'OH' => "Ohio",
			'OK' => "Oklahoma",
			'OR' => "Oregon",
			'PA' => "Pennsylvania",
			'RI' => "Rhode Island",
			'SC' => "South Carolina",
			'SD' => "South Dakota",
			'TN' => "Tennessee",
			'TX' => "Texas",
			'UT' => "Utah",
			'VT' => "Vermont",
			'VA' => "Virginia",
			'WA' => "Washington",
			'WV' => "West Virginia",
			'WI' => "Wisconsin",
			'WY' => "Wyoming",
		);

		$current_user_state = '';
		if ( $user_id = get_current_user_id() ) {
			$current_user_state = get_user_meta( get_current_user_id(), 'user_state', true );
		}

		$result = '<select class="user_state" name="user_state" id="user_state">';
		$result .= '<option>' . __( 'Select your state', 'lucidlms' ) . '</option>';
		foreach ( $states_list as $key => $name ) {
			$result .= '<option value="' . esc_attr( $key ) . '"' . selected( $key == $current_user_state, true, false ) . '>' . $name . '</option>';
		}
		$result .= '</select>';

		echo $result;
	}
}

if ( ! function_exists( 'lucidlms_countries_selectbox' ) ) {
	function lucidlms_countries_selectbox() {
		$countries_list = array(
			'AF' => 'Afghanistan',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua And Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BA' => 'Bosnia And Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'BN' => 'Brunei',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos (Keeling) Islands',
			'CO' => 'Columbia',
			'KM' => 'Comoros',
			'CG' => 'Congo',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'CI' => 'Cote D\'Ivorie (Ivory Coast)',
			'HR' => 'Croatia (Hrvatska)',
			'CU' => 'Cuba',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'CD' => 'Democratic Republic Of Congo (Zaire)',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'TP' => 'East Timor',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El Salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands (Malvinas)',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'FX' => 'France, Metropolitan',
			'GF' => 'French Guinea',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard And McDonald Islands',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Laos',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macau',
			'MK' => 'Macedonia',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia',
			'MD' => 'Moldova',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar (Burma)',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'AN' => 'Netherlands Antilles',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'KP' => 'North Korea',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PN' => 'Pitcairn',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RU' => 'Russia',
			'RW' => 'Rwanda',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts And Nevis',
			'LC' => 'Saint Lucia',
			'PM' => 'Saint Pierre And Miquelon',
			'VC' => 'Saint Vincent And The Grenadines',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome And Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SK' => 'Slovak Republic',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia And South Sandwich Islands',
			'KR' => 'South Korea',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard And Jan Mayen',
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syria',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TH' => 'Thailand',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad And Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks And Caicos Islands',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'UK' => 'United Kingdom',
			'US' => 'United States',
			'UM' => 'United States Minor Outlying Islands',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VA' => 'Vatican City (Holy See)',
			'VE' => 'Venezuela',
			'VN' => 'Vietnam',
			'VG' => 'Virgin Islands (British)',
			'VI' => 'Virgin Islands (US)',
			'WF' => 'Wallis And Futuna Islands',
			'EH' => 'Western Sahara',
			'WS' => 'Western Samoa',
			'YE' => 'Yemen',
			'YU' => 'Yugoslavia',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe'
		);

		$current_user_country = '';
		if ( $user_id = get_current_user_id() ) {
			$current_user_country = get_user_meta( get_current_user_id(), 'user_country', true );
		}

		$result = '<select class="user_country" name="user_country" id="user_country">';
		$result .= '<option>' . __( 'Select your country', 'lucidlms' ) . '</option>';
		foreach ( $countries_list as $key => $name ) {
			$result .= '<option value="' . esc_attr( $key ) . '"' . selected( $key == $current_user_country, true, false ) . '>' . $name . '</option>';
		}
		$result .= '</select>';

		echo $result;
	}
}