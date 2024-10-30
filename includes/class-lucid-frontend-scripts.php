<?php

/**
 * Handle frontend scripts and styles
 *
 * @class          LU_Frontend_Scripts
 * @version        1.0.0
 * @package        LucidLMS/Classes/
 * @category       Class
 * @author         New Normal
 */
class LU_Frontend_Scripts {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
		add_action( 'wp_print_scripts', array( $this, 'check_jquery' ), 25 );
	}

	/**
	 * Get styles for the frontend
	 * @return array
	 */
	public static function get_styles() {
		wp_enqueue_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css' );

		return apply_filters( 'lucidlms_enqueue_styles', array(
			'font-awesome'  => array(
				'src'     => '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css',
				'deps'    => '',
				'version' => LU_VERSION,
				'media'   => 'all'
			),
			'lucidlms-main' => array(
				'src'     => str_replace( array(
						'http:',
						'https:'
					), '', LU()->plugin_url() ) . '/assets/css/frontend/main.css',
				'deps'    => '',
				'version' => LU_VERSION,
				'media'   => 'all'
			)
		) );
	}

	/**
	 * Register/queue frontend scripts.
	 *
	 * @access public
	 * @return void
	 */
	public function load_scripts() {
		global $post, $wp, $current_user;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$assets_path          = str_replace( array( 'http:', 'https:' ), '', LU()->plugin_url() ) . '/assets/';
		$frontend_script_path = $assets_path . 'js/frontend/';

		// Register any scripts for later use, or used as dependencies
		wp_register_script( 'moment-js', LU()->plugin_url() . '/assets/js/admin/moment' . $suffix . '.js', array(), LU_VERSION );
		wp_register_script( 'timer-js', $frontend_script_path . 'timer' . $suffix . '.js', array( 'jquery' ), LU_VERSION, true );
		wp_register_script( 'lucid-single-lesson', $frontend_script_path . 'single-lesson' . $suffix . '.js', array(
			'jquery',
			'timer-js'
		), LU_VERSION, true );
		wp_register_script( 'lucid-single-quiz', $frontend_script_path . 'single-quiz' . $suffix . '.js', array(
			'jquery',
			'timer-js'
		), LU_VERSION, true );
		wp_register_script( 'lucid-student-profile', $frontend_script_path . 'student-profile' . $suffix . '.js', array(
			'jquery',
		), LU_VERSION, true );
        wp_register_script( 'expand-js', $assets_path . 'expand-js/expand' . $suffix . '.js', array(
            'jquery',
        ), LU_VERSION, true );
        wp_register_script( 'lucid-courses', $frontend_script_path . 'courses' . $suffix . '.js', array(
			'jquery', 'expand-js',
        ), LU_VERSION, true );

		// Queue frontend scripts conditionally
		if ( is_lesson() ) {
			wp_enqueue_script( 'lucid-single-lesson' );
		}

		if ( is_quiz() ) {
			wp_enqueue_script( 'lucid-single-quiz' );
		}

		if ( is_courses() || is_course_tag() || is_course_category() ) {
            wp_enqueue_script( 'lucid-courses' );
        }

		if ( is_student_profile_page() ) {
			wp_enqueue_script( 'lucid-student-profile' );
		}

		// Variables for JS scripts
		wp_localize_script( 'lucid-single-lesson', 'lucid_single_lesson_params', apply_filters( 'lucid_single_lesson_params', array(
			'ajax_url'                     => LU()->ajax_url(),
			'post_id'                      => isset( $post->ID ) ? $post->ID : '',
			'save_time_spent_lesson_nonce' => wp_create_nonce( "save-time-spent-lesson" ),
		) ) );

        wp_localize_script( 'lucid-courses', 'lucid_courses_params', apply_filters( 'lucid_courses_params', array(
			'ajax_url'                      => LU()->ajax_url(),
			'get_all_courses_nonce'      => wp_create_nonce( "get-all-courses" ),
			'get_category_courses_nonce' => wp_create_nonce( "get-category-courses" ),
            'get_searched_courses_nonce'       => wp_create_nonce( "get-searched-courses" )
		) ) );

		wp_localize_script( 'lucid-single-quiz', 'lucid_single_quiz_params', apply_filters( 'lucid_single_quiz_params', array(
			'ajax_url'                   => LU()->ajax_url(),
			'post_id'                    => isset( $post->ID ) ? $post->ID : '',
			'save_answer_nonce'          => wp_create_nonce( "save-answer" ),
			'update_review_screen_nonce' => wp_create_nonce( 'update-review-screen' ),
			'complete_quiz_nonce'        => wp_create_nonce( 'complete-quiz' )
		) ) );

		// CSS Styles
		$enqueue_styles = $this->get_styles();

		if ( $enqueue_styles ) {
			foreach ( $enqueue_styles as $handle => $args ) {
				wp_enqueue_style( $handle, $args['src'], $args['deps'], $args['version'], $args['media'] );
			}
		}
	}

	/**
	 * LucidLMS requires jQuery 1.8 since it uses functions like .on() for events and .parseHTML.
	 * If, by the time wp_print_scrips is called, jQuery is outdated (i.e not
	 * using the version in core) we need to deregister it and register the
	 * core version of the file.
	 *
	 * @access public
	 * @return void
	 */
	public function check_jquery() {
		global $wp_scripts;

		// Enforce minimum version of jQuery
		if ( ! empty( $wp_scripts->registered['jquery']->ver ) && ! empty( $wp_scripts->registered['jquery']->src ) && 0 >= version_compare( $wp_scripts->registered['jquery']->ver, '1.8' ) ) {
			wp_deregister_script( 'jquery' );
			wp_register_script( 'jquery', '/wp-includes/js/jquery/jquery.js', array(), '1.8' );
			wp_enqueue_script( 'jquery' );
		}
	}

}

new LU_Frontend_Scripts();
