<?php
/**
 * Installation related functions and actions.
 *
 * @author        New Normal
 * @category    Admin
 * @package    LucidLMS/Classes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'LU_Install' ) ) :

	/**
	 * LU_Install Class
	 */
	class LU_Install {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			register_activation_hook( LU_PLUGIN_FILE, array( $this, 'install' ) );

			add_action( 'admin_init', array( $this, 'install_actions' ) );
			add_action( 'admin_init', array( $this, 'check_version' ), 5 );
		}

		/**
		 * check_version function.
		 *
		 * @access public
		 * @return void
		 */
		public function check_version() {
			if ( ! defined( 'IFRAME_REQUEST' ) && ( get_option( 'lucidlms_version' ) != LU()->version || get_option( 'lucidlms_db_version' ) != LU()->version ) ) {
				$this->install();

				do_action( 'lucidlms_updated' );
			}
		}

		/**
		 * Install actions such as installing pages when a button is clicked.
		 */
		public function install_actions() {
			// Install - Add pages button
			if ( ! empty( $_GET['install_lucidlms_pages'] ) ) {

				self::create_pages();

				// We no longer need to install pages
				delete_option( '_lucid_needs_pages' );
				delete_transient( '_lucid_activation_redirect' );

				// Flush rewrite rules to create permalinks for new installed pages
				flush_rewrite_rules();

				// Redirect to Settings page
				if ( get_template() == 'lucidlms-theme' ) {
					wp_redirect( admin_url( 'themes.php?page=lucid-installation' ) );
					exit;
				} else {
					wp_redirect( admin_url( 'admin.php?page=lucid-settings' ) );
					exit;
				}

				// Skip button
			} elseif ( ! empty( $_GET['skip_install_lucidlms_pages'] ) ) {

				// We no longer need to install pages
				delete_option( '_lucid_needs_pages' );
				delete_transient( '_lucid_activation_redirect' );

				// Redirect to Settings page
				if ( get_template() == 'lucidlms-theme' ) {
					wp_redirect( admin_url( 'themes.php?page=lucid-installation' ) );
					exit;
				} else {
					wp_redirect( admin_url( 'admin.php?page=lucid-settings' ) );
					exit;
				}
			}

			if ( ! empty( $_GET['allow_opt_in'] ) && $_GET['allow_opt_in'] == true ) {
				update_option( '_lucid_opt_in', 'yes' );
			}

			if ( get_option( '_lucid_opt_in' ) == 'yes' && get_option( '_lucid_opt_in_got_install' ) != 1 ) {
				$this->get_stats();
			}
		}

		/**
		 * Install LUCID
		 */
		public function install() {
			$this->create_options();
			$this->create_roles();

			// Register post types
			include_once( 'class-lucid-post-types.php' );
			LU_Post_types::register_post_types();
			LU_Post_types::register_taxonomies();

			// TODO: Also register endpoints - this needs to be done prior to rewrite rule flush
//			LU()->query->init_query_vars();
//			LU()->query->add_endpoints();

			$this->create_terms();

			// Update version
			update_option( 'lucidlms_db_version', LU()->version );
			update_option( 'lucidlms_version', LU()->version );


			// Check if pages are needed
			if ( lucid_get_page_id( 'courses' ) < 1 ) {
				update_option( '_lucid_needs_pages', 1 );
			}

			flush_rewrite_rules();

			// Redirect to welcome screen
			set_transient( '_lucid_activation_redirect', 1, 60 * 60 );
		}

		/**
		 * Create pages that the plugin relies on, storing page id's in variables.
		 *
		 * @access public
		 * @return void
		 */
		public static function create_pages() {
			$pages = apply_filters( 'lucidlms_create_pages', array(
				'courses'        => array(
					'name'    => _x( 'courses', 'Page slug', 'lucidlms' ),
					'title'   => _x( 'Courses', 'Page title', 'lucidlms' ),
					'content' => ''
				),
				'studentprofile' => array(
					'name'    => _x( 'student-profile', 'Page slug', 'lucidlms' ),
					'title'   => _x( 'Student Profile', 'Page title', 'lucidlms' ),
					'content' => '[' . apply_filters( 'lucidlms_student_profile_shortcode_tag', 'lucidlms_student_profile' ) . ']'
				),
			) );

			foreach ( $pages as $key => $page ) {
				lucid_create_page( esc_sql( $page['name'] ), 'lucidlms_' . $key . '_page_id', $page['title'], $page['content'], ! empty( $page['parent'] ) ? lucid_get_page_id( $page['parent'] ) : '' );
			}
		}

		/**
		 * Add the default terms for lucid taxonomies - course types, course element types and score card statuses. Modify this at your own risk.
		 *
		 * @access public
		 * @return void
		 */
		private function create_terms() {

			$taxonomies = apply_filters( 'lucid_base_terms', array(
				'course_type'         => array(
					'course' => __( 'Course', 'lucidlms' ),
				),
				'course_element_type' => array(
					'lesson' => __( 'Lesson', 'lucidlms' ),
					'quiz'   => __( 'Quiz', 'lucidlms' ),
				),
				'score_card_status'   => array(
					'sc_started'   => __( 'Started', 'lucidlms' ),
					'sc_completed' => __( 'Completed', 'lucidlms' ),
					'sc_expired'   => __( 'Expired', 'lucidlms' ),
				),
				'question_type'       => array(
					'open'            => __( 'Open', 'lucidlms' ),
					'multiple_choice' => __( 'Multiple choice', 'lucidlms' ),
					'single_choice'   => __( 'Single choice', 'lucidlms' ),
				),
			) );

			foreach ( $taxonomies as $taxonomy => $terms ) {
				foreach ( $terms as $slug => $name ) {
					if ( ! get_term_by( 'slug', sanitize_title( $slug ), $taxonomy ) ) {
						wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
					}
				}
			}
		}

		/**
		 * Default options
		 *
		 * Sets up the default options used on the settings page
		 *
		 * @access public
		 */
		function create_options() {
			// Include settings so that we can run through defaults
			include_once( 'admin/class-lucid-admin-settings.php' );

			$settings = LU_Admin_Settings::get_settings_pages();

			foreach ( $settings as $section ) {
				foreach ( $section->get_settings() as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
		}

		/**
		 * Create roles and capabilities
		 */
		public function create_roles() {
			global $wp_roles;

			if ( class_exists( 'WP_Roles' ) ) {
				if ( ! isset( $wp_roles ) ) {
					$wp_roles = new WP_Roles();
				}
			}

			if ( is_object( $wp_roles ) ) {

				// Student role
				add_role( 'student', __( 'Student', 'lucidlms' ), array(
					'read'         => true,
					'edit_posts'   => false,
					'delete_posts' => false
				) );

				$capabilities = $this->get_core_capabilities();

				foreach ( $capabilities as $cap_group ) {
					foreach ( $cap_group as $cap ) {
						$wp_roles->add_cap( 'administrator', $cap );
					}
				}
			}
		}

		/**
		 * Get capabilities for LucidLMS - these are assigned to admin manager during installation or reset
		 *
		 * @access public
		 * @return array
		 */
		public function get_core_capabilities() {
			$capabilities = array();

			$capabilities['core'] = array(
				'manage_lucidlms',
				'view_lucidlms_reports'
			);

			$capability_types = array( 'course', 'course_element', 'score_card', 'question' );

			foreach ( $capability_types as $capability_type ) {

				$capabilities[ $capability_type ] = array(
					// Post type
					"edit_{$capability_type}",
					"read_{$capability_type}",
					"delete_{$capability_type}",
					"edit_{$capability_type}s",
					"edit_others_{$capability_type}s",
					"publish_{$capability_type}s",
					"read_private_{$capability_type}s",
					"delete_{$capability_type}s",
					"delete_private_{$capability_type}s",
					"delete_published_{$capability_type}s",
					"delete_others_{$capability_type}s",
					"edit_private_{$capability_type}s",
					"edit_published_{$capability_type}s",

					// Terms
					"manage_{$capability_type}_terms",
					"edit_{$capability_type}_terms",
					"delete_{$capability_type}_terms",
					"assign_{$capability_type}_terms"
				);
			}

			return $capabilities;
		}

		/**
		 * lucidlms_remove_roles function.
		 *
		 * @access public
		 * @return void
		 */
		public function remove_roles() {
			global $wp_roles;

			if ( class_exists( 'WP_Roles' ) ) {
				if ( ! isset( $wp_roles ) ) {
					$wp_roles = new WP_Roles();
				}
			}

			if ( is_object( $wp_roles ) ) {

				$capabilities = $this->get_core_capabilities();

				foreach ( $capabilities as $cap_group ) {
					foreach ( $cap_group as $cap ) {
						$wp_roles->remove_cap( 'administrator', $cap );
					}
				}

				remove_role( 'student' );
			}
		}

		/**
		 * Get site stats
		 *
		 * @access public
		 * @return void
		 */
		public function get_stats() {

			$url  = 'http://stat.lucidlms.com/api/v1/installs';
			$body = array(
				'install[domain]' => get_site_url(),
				'install[email]'  => get_option( 'admin_email' )
			);

			$response = wp_remote_post( $url, array(
					'method' => 'POST',
					'body'   => $body,
				)
			);

			update_option( '_lucid_opt_in_got_install', 1 );
		}

	}

endif;

return new LU_Install();