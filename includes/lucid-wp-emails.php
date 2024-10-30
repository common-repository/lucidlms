<?php
/**
 * Modifies default Wordpress emails styles to the corresponding Lucid LMS Theme styles.
 *
 * @author        New Normal
 * @category      Core
 * @package       LucidLMS/Functions
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Set the default content type for email
 *
 * @return string
 */
function lucidlms_emails_content_type() {
	return "text/html";
}

add_filter( "wp_mail_content_type", "lucidlms_emails_content_type" );

/**
 * Set the content of the email sent when the user's password is changed.
 *
 * @param $pass_change_email
 * @param $user
 * @param $userdata
 *
 * @return array
 */
function lucidlms_password_change_email( $pass_change_email, $user, $userdata ) {

	// Set admin email content variables
	$ms_logo         = '<div id="site-logo" style="text-align: center;"><a href="' . esc_url( home_url() ) . '"><img style="max-width: 100%; width: 100px; padding-top: 20px;" src="' . lucidlms_get_theme_mod( 'logo' ) . '"></a></div>';
	$ms_admin_header = '<div style="width:550px; padding:0 20px 20px 20px; background:#fff; margin:0 auto; border:3px #cecece solid; -moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px; color:#454545; line-height:1.5em; " id="email_content">' . $ms_logo;
	$ms_admin_header .= '<h1 style="padding:10px 0 10px; font-family:georgia;font-weight:500;font-size:24px;color:#000; text-align: center; border-bottom:1px solid #cecece; border-top:1px solid #cecece;">' . __( 'Notice of Password Change', 'lucidlms-theme' ) . '</h1>';
	$ms_admin_content = __( 'Hi ###USERNAME###, <br><br>This notice confirms that your password was changed on ###SITENAME###. <br><br>' );
	$ms_admin_content .= __( 'If you did not change your password, please contact the Site Administrator at ###ADMIN_EMAIL###.<br><br>' );
	$ms_admin_content .= __( 'This email has been sent to ###EMAIL###.<br><br>' );
	$ms_admin_content .= __( 'Regards, <br>All at ###SITENAME### <br>###SITEURL###.<br><br>' );
	$ms_admin_footer = '</div></div>';

	// Create email text
	$pass_change_text = $ms_admin_header;
	$pass_change_text .= $ms_admin_content;
	$pass_change_text .= $ms_admin_footer;

	$pass_change_email = array(
		'to'      => $user['user_email'],
		'subject' => __( '[%s] Notice of Password Change' ),
		'message' => $pass_change_text,
		'headers' => '',
	);

	return $pass_change_email;
}

add_filter( "password_change_email", "lucidlms_password_change_email", 10, 3 );


/**
 * Set the content of the email sent when the user's email is changed.
 *
 * @param $email_change_email
 * @param $user
 * @param $userdata
 *
 * @return array
 */
function lucidlms_email_change_email( $email_change_email, $user, $userdata ) {

	// Set admin email content variables
	$ms_logo         = '<div id="site-logo" style="text-align: center;"><a href="' . esc_url( home_url() ) . '"><img style="max-width: 100%; width: 100px; padding-top: 20px;" src="' . lucidlms_get_theme_mod( 'logo' ) . '"></a></div>';
	$ms_admin_header = '<div style="width:550px; padding:0 20px 20px 20px; background:#fff; margin:0 auto; border:3px #cecece solid; -moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px; color:#454545; line-height:1.5em; " id="email_content">' . $ms_logo;
	$ms_admin_header .= '<h1 style="padding:10px 0 10px; font-family:georgia;font-weight:500;font-size:24px;color:#000; text-align: center; border-bottom:1px solid #cecece; border-top:1px solid #cecece;">' . __( 'Notice of Email Change', 'lucidlms-theme' ) . '</h1>';
	$ms_admin_content = __( 'Hi ###USERNAME###, <br><br>This notice confirms that your email was changed on ###SITENAME###. <br><br>' );
	$ms_admin_content .= __( 'If you did not change your email, please contact the Site Administrator at ###ADMIN_EMAIL###.<br><br>' );
	$ms_admin_content .= __( 'This email has been sent to ###EMAIL###.<br><br>' );
	$ms_admin_content .= __( 'Regards, <br>All at ###SITENAME### <br>###SITEURL###.<br><br>' );
	$ms_admin_footer = '</div></div>';

	// Create email text
	$email_change_text = $ms_admin_header;
	$email_change_text .= $ms_admin_content;
	$email_change_text .= $ms_admin_footer;

	$email_change_email = array(
		'to'      => $user['user_email'],
		'subject' => __( '[%s] Notice of Email Change' ),
		'message' => $email_change_text,
		'headers' => '',
	);

	return $email_change_email;
}

add_filter( "email_change_email", "lucidlms_email_change_email", 10, 3 );


/**
 * Modify the message body of the password reset mail.
 *
 * @param $message
 * @param $key
 *
 * @return mixed
 */
function lucidlms_modified_retrieve_password_message( $message, $key ) {
	// Bail if username or email is not entered
	if ( ! isset( $_POST['user_login'] ) ) {
		return;
	}

	// Get user's data
	if ( strpos( $_POST['user_login'], '@' ) ) {
		$user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );
	} else {
		$login     = trim( $_POST['user_login'] );
		$user_data = get_user_by( 'login', $login );
	}

	$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

	// Store some info about the user
	$user_fname = $user_data->user_firstname;
	$user_login = $user_data->user_login;
	$user_email = $user_data->user_email;

	// Assembled the URL for resetting the password
	$reset_url = add_query_arg( array(
		'action' => 'rp',
		'key'    => $key,
		'login'  => rawurlencode( $user_login )
	), wp_login_url() );

	$email_receiver = ( ! empty( $user_fname ) ) ? $user_fname : $user_login;

	// Set email content variables
	$ms_logo   = '<div id="site-logo" style="text-align: center;"><a href="' . esc_url( home_url() ) . '"><img style="max-width: 100%; width: 100px; padding-top: 20px;" src="' . lucidlms_get_theme_mod( 'logo' ) . '"></a></div>';
	$ms_header = '<div style="width:550px; padding:0 20px 20px 20px; background:#fff; margin:0 auto; border:3px #cecece solid; -moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px; color:#454545; line-height:1.5em; " id="email_content">' . $ms_logo;
	$ms_header .= '<h1 style="padding:10px 0 10px; font-family:georgia;font-weight:500;font-size:24px;color:#000; text-align: center; border-bottom:1px solid #cecece; border-top:1px solid #cecece;">' . __( 'Password Reset', 'lucidlms-theme' ) . '</h1>';
	$ms_receiver  = '<strong>' . $email_receiver . '</strong>';
	$ms_content   = sprintf( '<p>%s, %s</p>', $ms_receiver, __( 'It looks like you (hopefully) want to reset your password for your ', 'lucidlms' ) . esc_url( home_url() ) . __( ' account. <br><br>To reset your password, simply click the link below, otherwise just ignore this email and nothing will happen.', 'lucidlms-theme' ) );
	$ms_reset_url = sprintf( '<p><a href="%s">%s</a></p>', $reset_url, $reset_url );
	$ms_footer    = '</div></div>';

	// Create and return the message
	$message = $ms_header;
	$message .= $ms_content;
	$message .= $ms_reset_url;
	$message .= $ms_footer;

	return $message;
}

add_filter( 'retrieve_password_message', 'lucidlms_modified_retrieve_password_message', 10, 2 );


if ( ! function_exists( 'wp_password_change_notification' ) ) {

	/**
	 * Notify the blog admin of a user changing password, normally via email.
	 *
	 * @param object $user User Object
	 */
	function wp_password_change_notification( $user ) {
		// send a copy of password change notification to the admin
		// but check to see if it's the admin whose password we're changing, and skip this
		if ( 0 !== strcasecmp( $user->user_email, get_option( 'admin_email' ) ) ) {

			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

			// Set admin email content variables
			$ms_logo         = '<div id="site-logo" style="text-align: center;"><a href="' . esc_url( home_url() ) . '"><img style="max-width: 100%; width: 100px; padding-top: 20px;" src="' . lucidlms_get_theme_mod( 'logo' ) . '"></a></div>';
			$ms_admin_header = '<div style="width:550px; padding:0 20px 20px 20px; background:#fff; text-align: center; margin:0 auto; border:3px #cecece solid; -moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px; color:#454545; line-height:1.5em; " id="email_content">' . $ms_logo;
			$ms_admin_header .= '<h1 style="padding:10px 0 10px; font-family:georgia;font-weight:500;font-size:24px;color:#000; text-align: center; border-bottom:1px solid #cecece; border-top:1px solid #cecece;">' . __( 'Password Lost/Changed', 'lucidlms-theme' ) . '</h1>';
			$ms_admin_content = sprintf( __( 'Heads up! User %s just changed their password on %s. <br>' ), $user->user_login, $blogname );
			$ms_admin_footer  = '</div></div>';

			// Create and return the message admin notification
			$message = $ms_admin_header;
			$message .= $ms_admin_content;
			$message .= $ms_admin_footer;

			wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] Password Lost/Changed' ), $blogname ), $message );
		}
	}
}

if ( ! function_exists( 'wp_new_user_notification' ) ) {

	/**
	 * Override new user registration notification.
	 *
	 * @param $user_id
	 * @param null $deprecated
	 * @param string $notify
	 */
	function wp_new_user_notification( $user_id, $deprecated = null, $notify = '' ) {
		if ( $deprecated !== null ) {
			_deprecated_argument( __FUNCTION__, '4.3.1' );
		}

		global $wpdb, $wp_hasher;
		$user = get_userdata( $user_id );

		// Store some info about the user
		$user_fname     = stripslashes( $user->user_firstname );
		$user_login     = stripslashes( $user->user_login );
		$user_email     = stripslashes( $user->user_email );
		$email_receiver = ! empty( $user_fname ) ? $user_fname : $user_login;

		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		// Set admin email content variables
		$ms_logo         = '<div id="site-logo" style="text-align: center;"><a href="' . esc_url( home_url() ) . '"><img style="max-width: 100%; width: 100px; padding-top: 20px;" src="' . lucidlms_get_theme_mod( 'logo' ) . '"></a></div>';
		$ms_admin_header = '<div style="width:550px; padding:0 20px 20px 20px; background:#fff; margin:0 auto; border:3px #cecece solid; -moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px; color:#454545; line-height:1.5em; " id="email_content">' . $ms_logo;
		$ms_admin_header .= '<h1 style="padding:10px 0 10px; font-family:georgia;font-weight:500;font-size:24px;color:#000; text-align: center; border-bottom:1px solid #cecece; border-top:1px solid #cecece;">' . __( 'New User Registration', 'lucidlms-theme' ) . '</h1>';
		$ms_admin_content = sprintf( __( 'New user registration on your site %s: <br>' ), $blogname );
		$ms_admin_content .= sprintf( __( 'Username: %s <br>' ), $user->user_login );
		$ms_admin_content .= sprintf( __( 'First Name: %s <br>' ), $user->user_firstname );
		$ms_admin_content .= sprintf( __( 'Last Name: %s <br>' ), $user->user_lastname );
		$ms_admin_content .= sprintf( __( 'E-mail: %s <br>' ), $user->user_email );
		$ms_admin_footer = '</div></div>';

		// Create and return the message admin notification
		$message = $ms_admin_header;
		$message .= $ms_admin_content;
		$message .= $ms_admin_footer;

		// Sent notification to admin email
		@wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] New User Registration' ), $blogname ), $message );

		if ( 'admin' === $notify || empty( $notify ) ) {
			return;
		}

		// Insert the key, hashed, into the DB.
		$key = wp_generate_password( 20, false );
		if ( empty( $wp_hasher ) ) {
			require_once ABSPATH . WPINC . '/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, true );
		}
		$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
		$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );

		// Assembled the URL for resetting the password
		$reset_url = add_query_arg( array(
			'action' => 'rp',
			'key'    => $key,
			'login'  => rawurlencode( $user_login )
		), wp_login_url() );

		// Set email content variables
		$ms_header = '<div style="width:550px; padding:0 20px 20px 20px; background:#fff; margin:0 auto; border:3px #cecece solid; -moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px; color:#454545; line-height:1.5em; " id="email_content">' . $ms_logo;
		$ms_header .= '<h1 style="padding:10px 0 10px; font-family:georgia;font-weight:500;font-size:24px;color:#000; text-align: center; border-bottom:1px solid #cecece; border-top:1px solid #cecece;">' . __( 'Registration information', 'lucidlms-theme' ) . '</h1>';
		$ms_content = sprintf( '<p>' . __( 'A very special welcome to you, %s. Thank you for joining %s! <br><br>' ), $email_receiver, $blogname );
		$ms_content .= __( 'Your account information: <br>', 'lucidlms-theme' );
		$ms_content .= sprintf( __( 'Username: %s <br>' ), $user->user_login );
		$ms_content .= sprintf( __( 'First Name: %s <br>' ), $user->user_firstname );
		$ms_content .= sprintf( __( 'Last Name: %s <br>' ), $user->user_lastname );
		$ms_content .= sprintf( __( 'E-mail: %s <br><br>' ), $user->user_email );
		$ms_content .= sprintf( __( 'We hope you enjoy your stay at %s. If you have any problems, questions, opinions, praise, comments, suggestions, please feel free to contact us at any time.' ) . '</p>', $blogname );
		$ms_reset_url = sprintf( '<p>' . __( 'To set your password, visit the following address: <br><br>', 'lucidlms-theme' ) . '<a href="%s">%s</a></p>', $reset_url, $reset_url );
		$ms_footer    = '</div></div>';

		// Create and return the message
		$message = $ms_header;
		$message .= $ms_content;
		$message .= $ms_reset_url;
		$message .= $ms_footer;

		wp_mail( $user_email, sprintf( __( '[%s] Registration information' ), $blogname ), $message );

	}
}