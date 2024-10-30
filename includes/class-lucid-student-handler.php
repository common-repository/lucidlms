<?php
/**
 * LucidLMS Student Functions
 *
 * @author        New Normal
 * @category      Core
 * @package       LucidLMS/Functions
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class LU_Student_Handler {

	/**
	 * Construct
	 */
	public function __construct() {

		add_action( 'user_register', array( $this, 'register_role' ) );
		add_action( 'user_register', array( $this, 'save_custom_fields' ) );
		add_action( 'personal_options_update', array( $this, 'user_profile_update_display_name' ) );
		add_action( 'edit_user_profile_update', array( $this, 'user_profile_update_display_name' ) );
		add_action( 'user_register', array( $this, 'register_update_display_name' ) );

	}

	/**
	 * Update user to specified role
	 *
	 * @param        $user_id
	 * @param string $password
	 * @param array  $meta
	 */
	public function register_role( $user_id, $password = "", $meta = array() ) {
		if ( isset( $_POST['role'] ) ) {
			$userdata         = array();
			$userdata['ID']   = $user_id;
			$userdata['role'] = $_POST['role'];

			//only allow if user role is student
			if ( $userdata['role'] == 'student' ) {
				wp_update_user( $userdata );
			}
		}
	}

	/**
	 * Save custom fields during registration
	 *
	 * @param $user_id
	 */
	public function save_custom_fields( $user_id ) {
		if ( isset( $_POST['first_name'] ) ) {
			update_user_meta( $user_id, 'first_name', sanitize_text_field( $_POST['first_name']) );
		}
		if ( isset( $_POST['last_name'] ) ) {
			update_user_meta( $user_id, 'last_name', sanitize_text_field( $_POST['last_name']) );
		}

        if( isset( $_POST['user_email'] ) ){
            wp_update_user( array(
                'ID'    => $user_id,
                'user_email' => sanitize_text_field( $_POST['user_email'] ),
            ) );
        }
	}

	/**
	 * Update display_name during profile update
	 *
	 * @param $user_id
	 *
	 * @return bool
	 */
	public function user_profile_update_display_name( $user_id ) {

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}
        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';

		//set the display name
		$display_name = trim( $first_name . " " . $last_name );
		if ( empty($display_name) ) {
			$display_name = get_current_user();
		}

		$args = array(
			'ID'           => $user_id,
			'display_name' => $display_name,
            'first_name'   => sanitize_text_field($_POST['first_name']),
            'last_name'    => sanitize_text_field($_POST['last_name']),
		);
		wp_update_user( $args );

		return true;
	}

	/**
	 * Update display_name during registration
	 *
	 * @param $user_id
	 */
	public function register_update_display_name( $user_id ) {
		//set the display name
		$info = get_userdata( $user_id );

		$display_name = trim( $info->first_name . ' ' . $info->last_name );
		if ( ! $display_name ) {
			$display_name = $info->user_login;
		}

		$args = array(
			'ID'           => $user_id,
			'display_name' => $display_name
		);

		wp_update_user( $args );
	}
}

new LU_Student_Handler();