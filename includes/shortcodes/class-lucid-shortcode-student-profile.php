<?php

/**
 * Student profile shortcode
 *
 * Shows the 'student profile' where the student can view started courses.
 *
 * @author        New Normal
 * @category      Shortcodes
 * @package       WooCommerce/Shortcodes/Student_Profile
 * @version       1.0.0
 */
class LU_Shortcode_Student_Profile {

	/**
	 * Get the shortcode content.
	 *
	 * @access public
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public static function get( $atts ) {
		return LU_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}

	/**
	 * Output the shortcode.
	 *
	 * @access public
	 *
	 * @param array $atts
	 *
	 * @return void
	 */
	public static function output( $atts ) {

		if ( ! is_user_logged_in() ) {

			$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';

			switch ( $action ) {
				case 'lostpassword':
					self::lost_password_form();

					break;

				case 'register':
					self::register_form();

					break;

				case 'editprofile':
					self::edit_profile_form();

					break;

				case 'login':
				default:
					self::login_form();

					break;

			}

		} else {

			$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
			switch ( $action ) {
				case 'editprofile':
				case 'update-user':
					self::edit_profile_form();

					break;

				default:
					self::student_profile( $atts );

					break;

			}
		}
	}

	/**
	 * Student profile page
	 *
	 * @param  array $atts
	 */
	private static function student_profile( $atts ) {
		lucid_get_template( 'student-profile/page.php', array(
			'current_user' => get_user_by( 'id', get_current_user_id() )
		) );
	}

	/**
	 * Lost password form for student profile
	 */
	private static function lost_password_form() {
		lucid_get_template( 'student-profile/lost-password-form.php' );
	}

	/**
	 * Register form for student profile
	 *
	 * TODO: add errors validation (so it not redirects to native WP page)
	 */
	private static function register_form() {
		lucid_get_template( 'student-profile/register-form.php' );
	}

	/**
	 * Edit profile form for student profile
	 */
	private static function edit_profile_form() {

		if ( class_exists( 'WooCommerce' ) && get_option( 'lucidlms_woocommerce_integration_enabled' ) === 'yes' ) {

			global $current_user;
			wp_get_current_user();

			$load_address = 'billing';

			$address = WC()->countries->get_address_fields( get_user_meta( get_current_user_id(), $load_address . '_country', true ), $load_address . '_' );

			wp_enqueue_script( 'wc-country-select' );
			wp_enqueue_script( 'wc-address-i18n' );

			// Prepare values
			foreach ( $address as $key => $field ) {

				$value = get_user_meta( get_current_user_id(), $key, true );

				if ( ! $value ) {
					switch ( $key ) {
						case 'billing_email' :
							$value = $current_user->user_email;
							break;
						case 'billing_country' :
							$value = WC()->countries->get_base_country();
							break;
						case 'billing_state' :
							$value = WC()->countries->get_base_state();
							break;
					}
				}

				$address[ $key ]['value'] = apply_filters( 'lucid_student_profile_edit_address_field_value', $value, $key, $load_address );
			}

			lucid_get_template( 'student-profile/edit-profile-form.php', array(
				'load_address' => $load_address,
				'address'      => apply_filters( 'lucid_address_to_edit', $address )
			) );

		} else {
			lucid_get_template( 'student-profile/edit-profile-form.php' );

		}
	}

	/**
	 * Login form for student profile
	 */
	private static function login_form() {
		lucid_get_template( 'student-profile/login-form.php' );
	}

}
