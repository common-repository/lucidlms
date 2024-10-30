<?php
/**
 * LucidLMS General Settings Woocommerce Integration
 *
 * @author 		New Normal
 * @category 	Admin
 * @package 	LucidLMS/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'LU_Settings_Woocommerce_Integration' ) ) :

    /**
     * LU_Settings_Woocommerce_Integration
     */
    class LU_Settings_Woocommerce_Integration extends LU_Settings_Page {

        /**
         * Constructor.
         */
        public function __construct() {
            $this->id    = 'woocommerce_integration';
            $this->label = __( 'Woocommerce', 'lucidlms' );

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


            return apply_filters( 'lucidlms_woocommerce_integration_settings', array(

                array(
                    'title' => __( 'Woocommerce Integration', 'lucidlms' ),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'woocommerce_integration_options'
                ),

                array(
                    'title' => __( 'Integration status', 'lucidlms' ),
                    'desc' 		=> __( 'Turn it on to enable WooCommerce integration', 'lucidlms' ),
                    'id' 		=> 'lucidlms_woocommerce_integration_enabled',
                    'default'	=> 'no',
                    'type' 		=> 'radio',
                    'options'   => array(
                        'yes' => __('Enabled', 'lucidlms'),
                        'no' => __('Disabled', 'lucidlms'),
                    ),
                    'class'     => 'regular-radio',
                    'desc_tip'	=>  true
                ),

                array( 'type' => 'sectionend', 'id' => 'woocommerce_integration_options' ),

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

endif;

return new LU_Settings_Woocommerce_Integration();
