<?php
/**
 * Admin Dashboard
 *
 * Functions used for displaying dashboard in admin.
 *
 * @author 		New Normal
 * @category 	Admin
 * @package 	LucidLMS/Admin/Dashboard
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'LU_Admin_Dashboard' ) ) :

	/**
	 * LU_Admin_Dashboard Class
	 */
	class LU_Admin_Dashboard {

		/**
		 * Handles output of the dashboard page in admin.
		 */
		public static function output() {
			include_once( 'views/html-admin-page-dashboard.php' );
		}

	}

endif;