<?php
/**
 * LucidLMS General Settings Emails
 *
 * @author        New Normal
 * @category      Admin
 * @package       LucidLMS/Admin
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'LU_Settings_Emails' ) ) : /**
 * LU_Settings_Emails
 */ {
	class LU_Settings_Emails extends LU_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'emails';
			$this->label = __( 'Emails', 'lucidlms' );

			add_filter( 'lucidlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'lucidlms_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'lucidlms_settings_save_' . $this->id, array( $this, 'save' ) );
		}

		/**
		 * Get settings array
		 *
		 * @return array
		 */
		public function get_settings() {

			return apply_filters( 'lucidlms_emails_settings', array(

				array(
					'title' => __( 'Emails settings', 'lucidlms' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'emails_options'
				),
				array(
					'title'    => __( '"From" email address', 'lucidlms' ),
					'id'       => 'lucidlms_emails_from_address',
					'default'  => get_option( 'admin_email' ),
					'type'     => 'email',
					'desc'     => __( 'Uses admin email by default, change for whatever you want', 'lucidlms' ),
					'desc_tip' => true
				),
				array(
					'title'    => __( 'Email subject: completed course', 'lucidlms' ),
					'id'       => 'lucidlms_emails_subject_course_completed',
					'default'  => __( 'Course "{course_name}" completed. Download your certificate', 'lucidlms' ),
					'type'     => 'text',
					'class'    => 'regular-text',
					'desc'     => __( 'Available variables: {site_name}, {course_name}.', 'lucidlms' ),
					'desc_tip' => true
				),
				array( 'type' => 'sectionend', 'id' => 'emails_options' ),

			) ); // End general settings
		}

		/**
		 * Save settings
		 */
		public function save() {
			$settings = $this->get_settings();

			LU_Admin_Settings::save_fields( $settings );
		}

	}
}

endif;

return new LU_Settings_Emails();
