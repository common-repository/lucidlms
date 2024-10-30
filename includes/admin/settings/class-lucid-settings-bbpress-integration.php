<?php
/**
 * LucidLMS General Settings bbPress Integration
 *
 * @author 		New Normal
 * @category 	Admin
 * @package 	LucidLMS/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'LU_Settings_bbPress_Integration' ) ) :

	/**
	 * LU_Settings_bbPress_Integration
	 */
	class LU_Settings_bbPress_Integration extends LU_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'bbpress_integration';
			$this->label = __( 'bbPress', 'lucidlms' );

			add_filter( 'lucidlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'lucidlms_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'lucidlms_settings_save_' . $this->id, array( $this, 'save' ) );
            add_action( 'lucidlms_settings_bbpress_integration_options', array($this, 'output_after_title_description'));
		}

		/**
		 * Get settings array
		 *
		 * @return array
		 */
		public function get_settings() {
            $bbpress_settings = array(

                array(
                    'title' => __( 'bbPress Integration', 'lucidlms' ),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'bbpress_integration_options'
                ),

                array(
                    'title' => __( 'Integration status', 'lucidlms' ),
                    'desc' 		=> __( 'Turn it on to enable bbPress integration', 'lucidlms' ),
                    'id' 		=> 'lucidlms_bbpress_integration_enabled',
                    'default'	=> 'no',
                    'type' 		=> 'radio',
                    'options'   => array(
                        'yes' => __('Enabled', 'lucidlms'),
                        'no' => __('Disabled', 'lucidlms'),
                    ),
                    'class'     => 'regular-radio',
                    'desc_tip'	=>  true
                )
            );
            if( is_bbpress_enabled() && get_option('lucidlms_bbpress_integration_enabled') == 'yes'):
                if( $uncreated_forums = lucidlms_bbpress_get_uncreated_forums() ){
                    $bbpress_settings[] = array(
                        'title' => __( 'Create all forums', 'lucidlms' ),
                        'desc' 	=> __( 'Generate new forum for each course if not already exist', 'lucidlms' ),
                        'id' => 'lucidlms_bbpress_create_forums',
                        'type' => 'submit',
                        'action' => 'create',
                        'value' => sprintf( __( 'Create %s forum(s)', 'lucidlms' ), count($uncreated_forums) ),
                        'class' => 'create-forums button',
                        'desc_tip'	=>  true,
                    );
                }

                if( $undeleted_forums = lucidlms_bbpress_get_undeleted_forums() ){
                    $bbpress_settings[] = array(
                        'title' => __( 'Delete all forums', 'lucidlms' ),
                        'desc' 	=> __( 'Delete all forums related to courses', 'lucidlms' ),
                        'id' => 'lucidlms_bbpress_delete_forums',
                        'type' => 'submit',
                        'action' => 'delete',
                        'value' => sprintf( __( 'Delete %s forum(s)', 'lucidlms' ), count($undeleted_forums) ),
                        'class' => 'delete-forums button',
                        'desc_tip'	=>  true,
                    );
                }
            endif; //if bbpress enabled

            $bbpress_settings[] = array( 'type' => 'sectionend', 'id' => 'bbpress_integration_options' );

			return apply_filters( 'lucidlms_bbpress_integration_settings', $bbpress_settings);
		}

		/**
		 * Save settings
		 */
		public function save() {
			$settings = $this->get_settings();

			LU_Admin_Settings::save_fields( $settings );
		}

        /**
         * Description for bbPress section in settings
         */
        public function output_after_title_description(){
            echo '<p class="bbpress-description">';
            _e('Discussions will appear inside lesson pages', 'lucidlms');
            echo '</p>';
        }

	}

endif;

return new LU_Settings_bbPress_Integration();
