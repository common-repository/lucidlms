<?php
/**
 * Plugin Name: LucidLMS
 * Plugin URI: http://lucidlms.com
 * Description: A beautiful learning management toolkit.
 * Version: 1.0.5
 * Author: New Normal LLC
 * Author URI: http://newnormal.agency/
 * Requires at least: 4.1
 * Tested up to: 4.7.3
 *
 * Text Domain: lucidlms
 * Domain Path: /i18n/languages/
 *
 * License:     GPL2

LucidLMS is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

LucidLMS is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with LucidLMS. If not, see http://www.gnu.org/licenses/gpl-2.0.html.
 */

/**
 * @package  LucidLMS
 * @category Core
 * @author   New Normal
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'LucidLMS' ) ) : /**
 * Main LucidLMS Class
 *
 * @class      LucidLMS
 * @version    1.0.0
 */ {
	final class LucidLMS {

		/**
		 * @var string
		 */
		public $version = '1.0.5';

		/**
		 * @var LucidLMS The single instance of the class
		 * @since 1.0
		 */
		protected static $_instance = null;

		/**
		 * @var LU_Query $query
		 */
		public $query = null;

		/**
		 * @var LU_Course_Factory The instance of course factory class
		 */
		public $course_factory = null;

		/**
		 * @var LU_Course_Element_Factory The instance of course element factory class
		 */
		public $course_element_factory = null;

		/**
		 * Store here all plugin taxonomies (question_type, score_card_type etc), their titles and ids
		 * @var LU_Core_Taxonomies
		 */
		public $taxonomies;

		/**
		 * Main LucidLMS Instance
		 *
		 * Ensures only one instance of LucidLMS is loaded or can be loaded.
		 *
		 * @since 1.0
		 * @static
		 * @see   LU()
		 * @return LucidLMS
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @since 1.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'lucidlms' ), '1.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'lucidlms' ), '1.0' );
		}

		/**
		 * LucidLMS Constructor.
		 * @access public
		 * @return LucidLMS
		 */
		public function __construct() {
			// Auto-load classes on demand
			if ( function_exists( "__autoload" ) ) {
				spl_autoload_register( "__autoload" );
			}

			spl_autoload_register( array( $this, 'autoload' ) ); // build our paths.

			// Define constants
			$this->define_constants();

			// Include required files
			$this->includes(); // Include required core files used in admin and on the frontend.

			// Hooks
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
			add_action( 'init', array( $this, 'init' ), 100 );
			add_action( 'lucidlms_init', array( $this, 'include_template_functions' ) );
			add_action( 'lucidlms_init', array( 'LU_Shortcodes', 'init' ) ); // add shortcode support (for page templates)
			add_action( 'after_setup_theme', array( $this, 'setup_environment' ) ); // don't think we need it now

			// Loaded action
			do_action( 'lucidlms_loaded' );
		}

		/**
		 * Auto-load in-accessible properties on demand.
		 *
		 * @param mixed $key
		 *
		 * @return mixed
		 */
		public function __get( $key ) {
			if ( method_exists( $this, $key ) ) {
				return $this->$key();
			} else {
				return false;
			}
		}

		/**
		 * Show action links on the plugin screen
		 *
		 * @param mixed $links
		 *
		 * @return array
		 */
		public function action_links( $links ) {
			return array_merge( array(
				'<a href="' . admin_url( 'admin.php?page=lucid-settings' ) . '">' . __( 'Settings', 'lucidlms' ) . '</a>',
			), $links );
		}

		/**
		 * Auto-load LudicLMS classes on demand to reduce memory consumption.
		 *
		 * @param mixed $class
		 *
		 * @return void
		 */
		public function autoload( $class ) {

			$path  = $this->plugin_path() . '/includes/';
			$class = strtolower( $class );
			$file  = 'class-' . str_replace( array( 'lu', '_' ), array( 'lucid', '-' ), $class ) . '.php';

			if ( strpos( $class, 'lu_shortcode_' ) === 0 ) {
				$path .= 'shortcodes/';
			} elseif ( strpos( $class, 'lu_meta_box' ) === 0 ) {
				$path .= 'admin/post-types/meta-boxes/';
			} elseif ( strpos( $class, 'lu_admin' ) === 0 ) {
				$path .= 'admin/';
			}

			if ( $path && is_readable( $path . $file ) ) {
				include_once( $path . $file );

				return;
			}
		}

		/**
		 * Define LucidLMS Constants
		 */
		private function define_constants() {
			define( 'LU_PLUGIN_FILE', __FILE__ );
			define( 'LU_VERSION', $this->version );

			if ( ! defined( 'LU_TEMPLATE_PATH' ) ) {
				define( 'LU_TEMPLATE_PATH', $this->template_path() );
			}

            // elements types taxonomies constants
            define( 'ATYPE', 'course_type' );
            define( 'AETYPE', 'course_element_type' );
            define( 'SCSTATUS', 'score_card_status' );
            define( 'QTYPE', 'question_type' );

            $wp_date_format = get_option('date_format');
            define( 'LUCID_DATE_FORMAT', $wp_date_format ? $wp_date_format : 'Y/m/d');

        }

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		private function includes() {
			include_once( 'includes/lucid-core-functions.php' );
			include_once( 'includes/class-lucid-install.php' );

			if ( is_admin() ) {
				include_once( 'includes/admin/class-lucid-admin.php' );
			}

			if ( defined( 'DOING_AJAX' ) ) {
				$this->ajax_includes();
			}

			if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
				$this->frontend_includes();
			}

			// Query class
			$this->query = include( 'includes/class-lucid-query.php' );                // The main query class

			// Post types
			include_once( 'includes/class-lucid-post-types.php' );                        // Registers post types

			// Post status
			include_once( 'includes/class-lucid-post-status.php' );                        // Registers custom post status

			// Include abstract classes
			include_once( 'includes/abstracts/abstract-lucid-base-course.php' );            // Courses
			include_once( 'includes/abstracts/abstract-lucid-course-element.php' );            // Course Elements

			// Classes (used on all pages)
			include_once( 'includes/class-lucid-course-factory.php' );                // Course factory
			include_once( 'includes/class-lucid-course-element-factory.php' );                // Course Element factory

			// Include template hooks in time for themes to remove/modify them
			include_once( 'includes/lucid-template-hooks.php' );

			// Certificates class
            require_once ('includes/libs/fpdf/fpdf.php');
            include_once( 'includes/class-lucid-certificate.php' );

            // Cron
            include_once('includes/class-lucid-cron.php');

			//Include changed wordpress emails
			include_once('includes/lucid-wp-emails.php');
		}

		/**
		 * Include required ajax files.
		 */
		public function ajax_includes() {
			include_once( 'includes/class-lucid-ajax.php' );                    // Ajax functions for admin and the front-end
		}

		/**
		 * Include required frontend files.
		 */
		public function frontend_includes() {
			include_once( 'includes/class-lucid-template-loader.php' );            // Template Loader
			include_once( 'includes/class-lucid-frontend-scripts.php' );        // Frontend Scripts
			include_once( 'includes/class-lucid-request-handler.php' );            // Request Handlers
//			include_once( 'includes/class-lucid-customer.php' ); 				    // Customer class // TODO[future-releases] we maybe need autosaving on shutdown from here
			include_once( 'includes/class-lucid-shortcodes.php' );                // Shortcodes class
		}

		/**
		 * Init LucidLMS when WordPress Initialises.
		 */
		public function init() {
			// Before init action
			do_action( 'before_lucidlms_init' );

			// Set up localisation
			$this->load_plugin_textdomain(); // TODO[future-releases] add localization files.

			// Load class instances
			$this->course_factory         = new LU_Course_Factory();
			$this->course_element_factory = new LU_Course_Element_Factory();
            $this->taxonomies = new LU_Core_Taxonomies(); // init taxonomies terms class

			// Classes/actions loaded for the frontend and for ajax requests
			if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
				// Class instances
				// add here classes initialization for AJAX methods if needed (maybe scorecard for ajax usage)
			}

			// Email Actions
			$email_actions = array(
				'lucidlms_course_completed'
			);

			foreach ( $email_actions as $action ) {
				add_action( $action, array( $this, 'send_transactional_email' ), 10, 10 );
			}

            // init certificates
            add_action( 'lucidlms_init', 'LU_Certificate::generate_certificate' );

            // schedule cron task if not exist
            if( !wp_next_scheduled('lucidlms_run_cron_task') ) {
                wp_schedule_event(time(), 'hourly', 'lucidlms_run_cron_task');
            }
            // set the CRON method that should be executed
            add_action('lucidlms_run_cron_task', 'LU_Cron::run_all_methods');

            // disable admin bar for student
            if( !current_user_can('manage_lucidlms') ){
                add_filter('show_admin_bar', '__return_false');
            }

            // Init action
			do_action( 'lucidlms_init' );
		}

        /**
		 * Function used to Init LucidLMS Template Functions - This makes them pluggable by plugins and themes.
		 */
		public function include_template_functions() {
			include_once( 'includes/lucid-template-functions.php' );
		}

		/**
		 * Load Localisation files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present
		 */
		public function load_plugin_textdomain() {
			$locale = apply_filters( 'plugin_locale', get_locale(), 'lucidlms' );

			// Admin Locale
			if ( is_admin() ) {
				load_textdomain( 'lucidlms', WP_LANG_DIR . "/lucidlms/lucidlms-admin-$locale.mo" );
				load_textdomain( 'lucidlms', dirname( __FILE__ ) . "/i18n/languages/lucidlms-admin-$locale.mo" );
			}

			// Global + Frontend Locale
			load_textdomain( 'lucidlms', WP_LANG_DIR . "/lucidlms/lucidlms-$locale.mo" );
			load_plugin_textdomain( 'lucidlms', false, plugin_basename( dirname( __FILE__ ) ) . "/i18n/languages" );
		}

		/**
		 * Ensure theme and server variable compatibility.
		 */
		public function setup_environment() {
			// IIS
			if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
				$_SERVER['REQUEST_URI'] = substr( $_SERVER['PHP_SELF'], 1 );
				if ( isset( $_SERVER['QUERY_STRING'] ) ) {
					$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
				}
			}

			// NGINX Proxy
			if ( ! isset( $_SERVER['REMOTE_ADDR'] ) && isset( $_SERVER['HTTP_REMOTE_ADDR'] ) ) {
				$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_REMOTE_ADDR'];
			}

			if ( ! isset( $_SERVER['HTTPS'] ) && ! empty( $_SERVER['HTTP_HTTPS'] ) ) {
				$_SERVER['HTTPS'] = $_SERVER['HTTP_HTTPS'];
			}

			// Support for hosts which don't use HTTPS, and use HTTP_X_FORWARDED_PROTO
			if ( ! isset( $_SERVER['HTTPS'] ) && ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ) {
				$_SERVER['HTTPS'] = '1';
			}
		}

		/** Helper functions ******************************************************/

		/**
		 * Get the plugin url.
		 *
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		/**
		 * Get the template path.
		 *
		 * @return string
		 */
		public function template_path() {
			return apply_filters( 'LU_TEMPLATE_PATH', 'lucidlms/' );
		}

		/**
		 * Get Ajax URL.
		 *
		 * @return string
		 */
		public function ajax_url() {
			return admin_url( 'admin-ajax.php', 'relative' );
		}

		/**
		 * Init the mailer and call the notifications for the current filter.
		 * @internal param array $args (default: array())
		 * @return void
		 */
		public function send_transactional_email() {
			$this->mailer();
			$args = func_get_args();
			do_action_ref_array( current_filter() . '_notification', $args );
		}

		/** Load Instances on demand **********************************************/

		/**
		 * Email Class.
		 *
		 * @return LU_Emails|LucidLMS
		 */
		public function mailer() {
			return LU_Emails::instance();
		}

	}
}

endif;

/**
 * Returns the main instance of LucidLMS to prevent the need to use globals.
 *
 * @since  1.0
 * @return LucidLMS
 */
function LU() {
	return LucidLMS::instance();
}

// Global for backwards compatibility.
$GLOBALS['lucidlms'] = LU();