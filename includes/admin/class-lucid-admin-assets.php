<?php
/**
 * Load admin assets.
 *
 * @author        New Normal
 * @category      Admin
 * @package       LucidLMS/Admin
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'LU_Admin_Assets' ) ) : /**
 * LU_Admin_Assets Class
 */ {
	class LU_Admin_Assets {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array(
				$this,
				'admin_styles'
			) );
			add_action( 'admin_enqueue_scripts', array(
				$this,
				'admin_scripts'
			) );
		}

		/**
		 * Enqueue styles
		 */
		public function admin_styles() {
			global $wp_scripts;

			$screen = get_current_screen();

			if ( in_array( $screen->id, lucid_get_screen_ids() ) ) {

				// Admin styles for LucidLMS pages only
				wp_enqueue_style( 'lucidlms_admin_styles', LU()->plugin_url() . '/assets/css/admin.css', array(), LU_VERSION );
				wp_enqueue_style( 'bootstrap_styles', LU()->plugin_url() . '/assets/bootstrap/css/bootstrap.min.css', array(), LU_VERSION );
				wp_enqueue_style( 'bootstrap_theme_styles', LU()->plugin_url() . '/assets/bootstrap/css/bootstrap-theme.min.css', array(), LU_VERSION );
				wp_enqueue_style( 'bootstrap_datetimepicker_styles', LU()->plugin_url() . '/assets/css/bootstrap-datetimepicker.min.css', array(), LU_VERSION );
				wp_enqueue_style( 'bootstrap_switch_styles', LU()->plugin_url() . '/assets/css/bootstrap-switch.min.css', array(), LU_VERSION );
				wp_enqueue_style( 'chosen', LU()->plugin_url() . '/assets/chosen/chosen.min.css', array(), LU_VERSION );

				// Summernote require font-awesome
				wp_enqueue_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css' );
				wp_enqueue_style( 'summernote', LU()->plugin_url() . '/assets/css/summernote.min.css', array(), LU_VERSION );

			}

			do_action( 'lucidlms_admin_css' );
		}


		/**
		 * Enqueue scripts
		 */
		public function admin_scripts() {
			global $wp_query, $post;

			$screen = get_current_screen();
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			// Register scripts
			wp_register_script( 'bootstrap', LU()->plugin_url() . '/assets/bootstrap/js/bootstrap' . $suffix . '.js', array(), LU_VERSION );

			wp_register_script( 'moment-js', LU()->plugin_url() . '/assets/js/admin/moment' . $suffix . '.js', array(), LU_VERSION );

			wp_register_script( 'bootstrap_datetimepicker', LU()->plugin_url() . '/assets/js/admin/bootstrap-datetimepicker' . $suffix . '.js', array(
				'jquery',
				'moment-js',
				'bootstrap'
			), LU_VERSION );

			wp_register_script( 'bootstrap_switch', LU()->plugin_url() . '/assets/js/admin/bootstrap-switch' . $suffix . '.js', array(
				'jquery',
				'bootstrap'
			), LU_VERSION );

			wp_register_script( 'chosen', LU()->plugin_url() . '/assets/chosen/chosen.jquery' . $suffix . '.js', array(
				'jquery',
			), LU_VERSION );

			wp_register_script( 'summernote', LU()->plugin_url() . '/assets/js/admin/summernote' . $suffix . '.js', array(
				'jquery',
				'bootstrap'
			), LU_VERSION );

			wp_register_script( 'expand-js', LU()->plugin_url() . '/assets/expand-js/expand' . $suffix . '.js', array(
				'jquery',
			), LU_VERSION, TRUE );

			wp_register_script( 'lucidlms_admin', LU()->plugin_url() . '/assets/js/admin/lucidlms_admin.js', array(
				'jquery',
				'jquery-ui-sortable',
				'bootstrap',
				'bootstrap_switch',
				'bootstrap_datetimepicker',
				'chosen',
				'expand-js',
				'summernote'
			), LU_VERSION );

			// LucidLMS admin pages
			if ( in_array( $screen->id, lucid_get_screen_ids() ) ) {
				wp_enqueue_script( 'lucidlms_admin' );
				$params = array(
					'post_id'                                => isset( $post->ID ) ? $post->ID : '',
					'plugin_url'                             => LU()->plugin_url(),
					'ajax_url'                               => admin_url( 'admin-ajax.php' ),
					'post_url'                               => admin_url( 'post.php' ),
					'wp_timezone'                            => lucidlms_get_timezone_string(),
					'create_course_nonce'		             => wp_create_nonce( 'create-course' ),
					'create_course_element_nonce'            => wp_create_nonce( "create-course-element" ),
					'remove_course_element_nonce'            => wp_create_nonce( "remove-course-element" ),
					'get_course_elements_nonce'              => wp_create_nonce( "get-course-elements" ),
					'create_question_nonce'                  => wp_create_nonce( "create-question" ),
					'edit_question_nonce'                    => wp_create_nonce( 'edit-question' ),
					'insert_questions_nonce'                 => wp_create_nonce( 'insert-questions' ),
					'manage_questions_categories_modal_nonce'=> wp_create_nonce( 'manage-questions-categories-modal' ),
					'insert_questions_modal_nonce'           => wp_create_nonce( 'insert-questions-modal' ),
					'question_remove_category_nonce'         => wp_create_nonce( 'question-remove-category' ),
					'question_add_category_nonce'            => wp_create_nonce( 'question-add-category' ),
					'save_edit_question_nonce'               => wp_create_nonce( 'save-edit-question' ),
					'remove_question_nonce'                  => wp_create_nonce( "remove-question" ),
					'get_questions_nonce'                    => wp_create_nonce( "get-questions" ),
					'change_course_type_nonce'               => wp_create_nonce( 'change_course_type' ),
					'filter_questions_nonce'                 => wp_create_nonce( 'filter-questions' ),
					'change_course_element_type_nonce'       => wp_create_nonce( 'change_course_element_type' ),
					'reorder_course_elements_nonce'          => wp_create_nonce( 'reorder-course-elements' ),
					'reorder_questions_nonce'                => wp_create_nonce( 'reorder-questions' ),
					'get_available_courses_nonce'            => wp_create_nonce( 'get-available-courses' ),
					'change_course_status_nonce'             => wp_create_nonce( 'change-course-status' ),
					'create_new_question_category_nonce'     => wp_create_nonce( 'create-new-question-category' ),
					'edit_question_category_nonce'           => wp_create_nonce( 'edit-question-category' ),
					'remove_question_category_nonce'         => wp_create_nonce( 'remove-question-category' ),
					'i18n_type_an_answer'                    => esc_js( __( 'Type an answer', 'lucidlms' ) ),
					'i18n_question'                          => esc_js( __( 'Question', 'lucidlms' ) ),
					'i18n_question_pool'                     => esc_js( __( 'Question Pool', 'lucidlms' ) ),
					'i18n_close'                             => esc_js( __( 'Close', 'lucidlms' ) ),
					'i18n_save'                              => esc_js( __( 'Save changes', 'lucidlms' ) ),
					'i18n_insert'                            => esc_js( __( 'Insert Questions', 'lucidlms' ) ),
					'i18n_insert_questions_modal_title'      => esc_js( __( 'Select questions to insert', 'lucidlms' ) ),
					'i18n_create_course_alert'               => esc_js( __( 'Please enter the course name.', 'lucidlms' ) ),
					'i18n_create_element_alert'              => esc_js( __( 'Please choose the type and enter the name.', 'lucidlms' ) ),
					'i18n_create_question_empty_answer'      => esc_js( __( 'An answer cannot be empty.', 'lucidlms' ) ),
					'i18n_create_question_no_correct_answer' => esc_js( __( 'Question of this type should have at least one correct answer.', 'lucidlms' ) ),
					'i18n_correct_answer'                    => esc_js( __( 'Correct', 'lucidlms' ) ),
					'i18n_incorrect_answer'                  => esc_js( __( 'Wrong', 'lucidlms' ) ),
					'i18n_no_categories'                     => esc_js( __( 'No categories', 'lucidlms' ) ),
					'i18n_manage_question_categories_title'  => esc_js( __( 'Manage categories', 'lucidlms' ) ),
					'i18n_error_category_cannot_be_empty'    => esc_js( __( 'Category name cannot be empty', 'lucidlms' ) ),
					'i18n_confirm_delete_category'           => esc_js( __( 'Are you really want to delete the category? Questions will be not deleted', 'lucidlms' ) ),
				);

				wp_localize_script( 'lucidlms_admin', 'lucidlms_admin', $params );
			}

			// Course specific
			if ( in_array( $screen->id, array( 'course', 'edit-course' ) ) ) {

			}

		}
	}
}

endif;

return new LU_Admin_Assets();
