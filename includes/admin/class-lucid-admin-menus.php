<?php
/**
 * Setup menus in WP admin.
 *
 * @author 		New Normal
 * @category 	Admin
 * @package 	LucidLMS/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'LU_Admin_Menus' ) ) :

	/**
	 * LU_Admin_Menus Class
	 */
	class LU_Admin_Menus {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			// Add menus
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );
//			add_action( 'admin_menu', array( $this, 'reports_menu' ), 20 ); // TODO: maybe we should make reports as a standalone feature (not score cards)
			add_action( 'admin_menu', array( $this, 'dashboard_menu' ), 9 );
			add_action( 'admin_menu', array( $this, 'question_pool_menu' ), 20 );
			add_action( 'admin_menu', array( $this, 'settings_menu' ), 30 );

			add_action( 'admin_head', array( $this, 'menu_highlight' ) );
			add_filter( 'menu_order', array( $this, 'menu_order' ) );
			add_filter( 'custom_menu_order', array( $this, 'custom_menu_order' ) );
		}

		/**
		 * Add menu items
		 */
		public function admin_menu() {
			global $menu;

			if ( current_user_can( 'manage_lucidlms' ) )
				$menu[] = array( '', 'read', 'separator-lucidlms', '', 'wp-menu-separator lucidlms' );

			add_menu_page( __( 'LucidLMS', 'lucidlms' ), __( 'LucidLMS', 'lucidlms' ), 'manage_lucidlms', 'lucidlms' , array( $this, 'settings_page' ), plugins_url('lucidlms/assets/images/lucid_logo16x16.png'), '57.7' );
		}

		/**
		 * Add menu item
		 */
		public function dashboard_menu() {
			add_submenu_page( 'lucidlms', __( 'Dashboard', 'lucidlms' ),  __( 'Dashboard', 'lucidlms' ) , 'manage_lucidlms', 'lucid-dashboard', array( $this, 'dashboard_page' ) );
		}

        /**
		 * Add menu item
		 */
		public function question_pool_menu() {
			add_submenu_page( 'lucidlms', __( 'Question Pool', 'lucidlms' ),  __( 'Question Pool', 'lucidlms' ) , 'manage_lucidlms', 'lucid-question-pool', array( $this, 'question_pool_page' ) );
		}

		/**
		 * Add menu item
		 */
		public function reports_menu() {
			add_submenu_page( 'lucidlms', __( 'Reports', 'lucidlms' ),  __( 'Reports', 'lucidlms' ) , 'view_lucidlms_reports', 'lucid-reports', array( $this, 'reports_page' ) );
		}

		/**
		 * Add menu item
		 */
		public function settings_menu() {
			add_submenu_page( 'lucidlms', __( 'LucidLMS Settings', 'lucidlms' ),  __( 'Settings', 'lucidlms' ) , 'manage_lucidlms', 'lucid-settings', array( $this, 'settings_page' ) );
		}

		/**
		 * Highlights the correct top level admin menu item for post type add screens.
		 *
		 * @access public
		 * @return void
		 */
		public function menu_highlight() {
			global $menu, $submenu, $parent_file, $submenu_file, $self, $post_type, $taxonomy;

			if ( isset( $submenu['lucidlms'] ) && isset( $submenu['lucidlms'][1] ) ) {
				$submenu['lucidlms'][0] = $submenu['lucidlms'][1];
				unset( $submenu['lucidlms'][1] );
			}
		}

		/**
		 * Reorder LucidLMS menu items in admin.
		 *
		 * @param mixed $menu_order
		 * @return array
		 */
		public function menu_order( $menu_order ) {
			// Initialize our custom order array
			$lucidlms_menu_order = array();

			// Get the index of our custom separator
			$lucidlms_separator = array_search( 'separator-lucidlms', $menu_order );

			// Get indexes of needed menus
			$lucidlms_course = array_search( 'edit.php?post_type=course', $menu_order );
			$lucidlms_question = array_search( 'edit.php?post_type=question', $menu_order );
			$lucidlms_instructors = array_search( 'edit.php?post_type=instructors', $menu_order );

			// Loop through menu order and do some rearranging
			foreach ( $menu_order as $index => $item ) :

				if ( ( ( 'lucidlms' ) == $item ) ) :
					$lucidlms_menu_order[] = 'separator-lucidlms';
//					$lucidlms_menu_order[] = 'lucid-dashboard';
					$lucidlms_menu_order[] = $item;
					$lucidlms_menu_order[] = 'edit.php?post_type=course';
					$lucidlms_menu_order[] = 'edit.php?post_type=question';
					$lucidlms_menu_order[] = 'edit.php?post_type=instructors';
					unset( $menu_order[$lucidlms_separator] );
//					unset( $menu_order[$lucidlms_dashboard] );
					unset( $menu_order[$lucidlms_course] );
					unset( $menu_order[$lucidlms_question] );
					unset( $menu_order[$lucidlms_instructors] );
				elseif ( !in_array( $item, array( 'separator-lucidlms' ) ) ) :
					$lucidlms_menu_order[] = $item;
				endif;

			endforeach;

			// Return order
			return $lucidlms_menu_order;
		}

		/**
		 * custom_menu_order
		 * @return bool
		 */
		public function custom_menu_order() {
			if ( ! current_user_can( 'manage_lucidlms' ) )
				return false;
			return true;
		}

		/**
		 * Init the reports page
		 */
		public function reports_page() {
//			include_once( 'class-lucid-admin-reports.php' );
//			LU_Admin_Reports::output();
		}

		/**
		 * Init the dashboard page
		 */
		public function dashboard_page() {
			include_once( 'class-lucid-admin-dashboard.php' );
			LU_Admin_Dashboard::output();
		}
        /**
		 * Init the question pool page
		 */
		public function question_pool_page() {
			include_once( 'class-lucid-admin-question-pool.php' );
			LU_Admin_Question_Pool::output();
		}

		/**
		 * Init the settings page
		 */
		public function settings_page() {
			include_once( 'class-lucid-admin-settings.php' );
			LU_Admin_Settings::output();
		}
	}

endif;

return new LU_Admin_Menus();