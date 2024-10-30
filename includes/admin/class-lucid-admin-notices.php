<?php
/**
 * Display notices in admin.
 *
 * @author        New Normal
 * @category      Admin
 * @package       LucidLMS/Admin
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'LU_Admin_Notices' ) ) : /**
 * LU_Admin_Notices Class
 */ {
	class LU_Admin_Notices {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			add_action( 'admin_print_styles', array( $this, 'add_notices' ) );
		}

		/**
		 * Add notices + styles if needed.
		 */
		public function add_notices() {
			if ( get_option( '_lucid_needs_pages' ) == 1 ) {
				wp_enqueue_style( 'lucidlms-install', plugins_url( '/assets/css/install.css', LU_PLUGIN_FILE ) );
				add_action( 'admin_notices', array( $this, 'install_notice' ) );
			}
			if ( get_option( '_lucid_opt_in' ) != 'yes' ) {
				add_action( 'admin_notices', array( $this, 'opt_in_notice' ) );
			}
			if ( get_option( 'users_can_register' ) != 1 ) {
				add_action( 'admin_notices', array( $this, 'users_can_register_notice' ) );
			}
		}

		/**
		 * Show the install notices
		 */
		public function install_notice() {

			// If we have just installed, show a message with the install pages button
			if ( get_option( '_lucid_needs_pages' ) == 1 && get_template() != 'lucidlms-theme' ) {
				include( 'views/html-notice-install.php' );
			}
		}

		/**
		 * Show the install notices
		 */
		public function opt_in_notice() {
			if ( get_option( '_lucid_opt_in' ) != 'yes' ) {
				include( 'views/html-notice-opt-in.php' );
			}
		}

		public function users_can_register_notice() {
			if ( get_option( 'users_can_register' ) != 1 && get_option( 'lucid-installation' ) == 'complete' ) {
				include( 'views/html-notice-users-can-register.php' );
			}
		}
	}
}

endif;

return new LU_Admin_Notices();