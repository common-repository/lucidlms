<?php
/**
 * Lost password form
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

?>

<p><?php _e( 'Reset your password.', 'lucidlms' ); ?></p>

<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" class="wp-user-form">
	<p class="username">
		<label for="user_login" class="hide"><?php _e( 'Username or Email' ); ?>: </label>
		<input type="text" name="user_login" value="" size="20" id="user_login" tabindex="1001"/>
	</p>

	<p class="login_fields">
		<?php do_action( 'login_form', 'resetpass' ); ?>
		<input type="submit" name="user-submit" value="<?php _e( 'Reset my password' ); ?>" class="user-submit"
		       tabindex="1002"/>

		<?php
		/**
		 * TODO: move this to student handler @see LU_Student_Handler via lucidlms_init hook
		 */
		if ( isset( $_POST['reset_pass'] ) ) {
			global $wpdb;
			$username    = trim( $_POST['user_login'] );
			$user_exists = false;
			$error       = array();
			$user        = false;

			// First check by username
			if ( username_exists( $username ) ) {
				$user_exists = true;
				$user        = get_user_by( 'login', $username );
			} // Then, by e-mail address
			elseif ( email_exists( $username ) ) {
				$user_exists = true;
				$user        = get_user_by( 'email', $username );
			} else {
				$error[] = '<p>' . __( 'Username or Email was not found, try again!' ) . '</p>';
			}
			if ( $user_exists ) {
				$user_login = $user->user_login;
				$user_email = $user->user_email;

				$key = wp_generate_password( 20, false );
				do_action( 'retrieve_password_key', $user_login, $key );

				if ( empty( $wp_hasher ) ) {
					require_once ABSPATH . 'wp-includes/class-phpass.php';
					$wp_hasher = new PasswordHash( 8, true );
				}
				$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
				$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );

				//create email message
				$message = __( 'Someone has asked to reset the password for the following site and username.' ) . "\r\n\r\n";
				$message .= get_option( 'siteurl' ) . "\r\n\r\n";
				$message .= sprintf( __( 'Username: %s' ), $user_login ) . "\r\n\r\n";
				$message .= __( 'To reset your password visit the following address, otherwise just ignore this email and nothing will happen.' ) . "\r\n\r\n";
				$message .= network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . "&redirect_to=" . urlencode( add_query_arg( array( 'action' => 'login' ), get_permalink() ) ) . "\r\n";
				//send email meassage

				if ( false == wp_mail( $user_email, sprintf( __( '[%s] Password Reset' ), get_option( 'blogname' ) ), $message ) ) {
					$error[] = '<p>' . __( 'The e-mail could not be sent.' ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function...' ) . '</p>';
				}
			}
			if ( count( $error ) > 0 ) {
				foreach ( $error as $e ) {
					echo $e . "<br/>";
				}
			} else {
				echo '<p>' . __( 'A message will be sent to your email address.' ) . '</p>';
			}
		}
		?>
		<input type="hidden" name="reset_pass" value="1"/>
		<input type="hidden" name="user-cookie" value="1"/>
	</p>
</form>

<p id="loginnav">
	<a href="<?php echo add_query_arg( array( 'action' => 'login' ), get_permalink() ); ?>"><?php _e( 'Log in', 'lucidlms' ); ?></a>
	|
	<a href="<?php echo add_query_arg( array( 'action' => 'register' ), get_permalink() ); ?>"><?php _e( 'Not a member yet?', 'lucidlms' ); ?></a>
</p>