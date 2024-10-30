<?php
/**
 * LucidLMS General Settings
 *
 * @author        New Normal
 * @category      Admin
 * @package       LucidLMS/Admin
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'LU_Settings_General' ) ) : /**
 * LU_Admin_Settings_General
 */ {
	class LU_Settings_General extends LU_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'general';
			$this->label = __( 'General', 'lucidlms' );

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


			return apply_filters( 'lucidlms_general_settings', array(

				array(
					'title' => __( 'General Options', 'lucidlms' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'general_options'
				),
				array(
					'title'    => __( 'Organization name', 'lucidlms' ),
					'desc'     => __( 'This sets your name on all certificates.', 'lucidlms' ),
					'id'       => 'lucidlms_organization_name',
					'default'  => __( 'LucidLMS', 'lucidlms' ),
					'type'     => 'text',
					'class'    => 'regular-text',
					'desc_tip' => true
				),
				array(
					'title'    => __( 'Logo', 'lucidlms' ),
					'desc'     => __( 'Logo will also appear on certificates.', 'lucidlms' ),
					'id'       => 'lucidlms_organization_logo',
					'default'  => '',
					'type'     => 'file',
					'desc_tip' => true
				),
				array(
					'title'    => __( 'Courses Page', 'lucidlms' ),
					'id'       => 'lucidlms_courses_page_id',
					'type'     => 'single_select_page',
					'default'  => '',
					'css'      => 'min-width:300px;',
					'desc_tip' => __( 'This sets the base page of your courses - this is where your courses archive will be.', 'lucidlms' ),
				),
				array(
					'title'    => __( 'Hide category in sidebar', 'lucidlms' ),
					'id'       => 'lucidlms_course_category_hide',
					'desc'     => __( 'This hides category in sidebar on course page', 'lucidlms' ),
					'type'     => 'checkbox',
					'default'  => '',
					'desc_tip' => true,
					'single'   => true
				),
				array(
					'title'    => __( 'Allow sending data', 'lucidlms' ),
					'id'       => '_lucid_opt_in',
					'desc'     => __( 'Help us support your installation: opt-in to send us initial meta-data about your copy of LucidLMS.', 'lucidlms' ),
					'type'     => 'checkbox',
					'default'  => '',
					'desc_tip' => true,
					'single'   => true
				),
				array( 'type' => 'sectionend', 'id' => 'general_options' ),
				array(
					'title' => __( 'Default Settings for LMS', 'lucidlms' ),
					'type'  => 'title',
					'desc'  => __( 'The following options defines default values for your LMS setup.', 'lucidlms' ),
					'id'    => 'default_options'
				),
				array(
					'title'             => __( 'Availability time (days)', 'lucidlms' ),
					'desc'              => __( 'This sets the default time course is available to student once it started.', 'lucidlms' ),
					'id'                => 'lucidlms_course_default_availability_time',
					'css'               => 'width:50px;',
					'default'           => '30',
					'type'              => 'number',
					'desc_tip'          => true,
					'custom_attributes' => array(
						'min'  => 0,
						'step' => 1
					)
				),
				array(
					'title'             => __( 'Threshold (percents)', 'lucidlms' ),
					'desc'              => __( 'This sets the default threshold for every quiz (may be changed individually).', 'lucidlms' ),
					'id'                => 'lucidlms_quiz_default_threshold',
					'css'               => 'width:50px;',
					'default'           => '80',
					'type'              => 'number',
					'desc_tip'          => true,
					'custom_attributes' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1
					)
				),
				array( 'type' => 'sectionend', 'id' => 'default_options' ),

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

return new LU_Settings_General();
