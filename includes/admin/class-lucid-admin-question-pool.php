<?php
/**
 * Admin Question Pool
 *
 * Functions used for displaying question pool in admin.
 *
 * @author 		New Normal
 * @category 	Admin
 * @package 	LucidLMS/Admin/Question_Pool
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'LU_Admin_Question_Pool' ) ) :

	/**
	 * LU_Admin_Question_Pool Class
	 */
	class LU_Admin_Question_Pool {

		/**
		 * Handles output of the dashboard page in admin.
		 */
		public static function output() {
            $categories = get_all_question_categories();
            $questions = get_questions();

            $courses = lucidlms_get_all_courses();

            $available_question_types = LU_Question::get_available_question_types();
            array_unshift( $available_question_types, __( 'Choose a type', 'lucidlms' ) );

			include_once( 'views/html-admin-page-question-pool.php' );
		}

	}

endif;