<?php
/**
 * Register form
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$return_to_course = isset( $_REQUEST['return_to_course'] ) ? $_REQUEST['return_to_course'] : false;
?>

<div id="register-form">
	<p><?php _e( 'Register to access site features.', 'lucidlms' ); ?></p>

	<form action="<?php echo site_url( 'wp-login.php?action=register', 'login_post' ) ?>" method="post">
		<p class="register-username">
			<label for="user_login"><?php _e( 'Username', 'lucidlms' ); ?><br />
				<input type="text" name="user_login" id="user_login" class="input" />
			</label>
		</p>

		<p class="register-email">
			<label for="user_email"><?php _e( 'E-Mail' ); ?><br />
				<input type="text" name="user_email" id="user_email" class="input" />
			</label>
		</p>

		<p class="register-first_name">
			<label for="first_name"><?php _e( 'First Name' ) ?><br />
				<input type="text" name="first_name" id="first_name" class="input" size="25" />
			</label>
		</p>

		<p class="register-last_name">
			<label for="last_name"><?php _e( 'Last Name' ) ?><br />
				<input type="text" name="last_name" id="last_name" class="input" size="25" />
			</label>
		</p>

		<input id="role" type="hidden" tabindex="20" size="25" value="<?php if ( isset( $_GET['role'] ) ) {
			echo $_GET['role'];
		} ?>" name="role" />
		<?php
		$redirect_to_args = array( 'action' => 'login', 'message' => 'registered' );
		if ( $return_to_course ) {
			$redirect_to_args['return_to_course'] = $return_to_course;
		} ?>
		<input type="hidden" name="redirect_to" value="<?php echo add_query_arg( $redirect_to_args, get_permalink() ); ?>" />

		<?php do_action( 'register_form' ); ?>

		<input type="submit" value="<?php _e( 'Register', 'lucidlms' ); ?>" id="register" />

		<p class="statement"><?php _e( 'A password will be e-mailed to you.', 'lucidlms' ); ?></p>
	</form>
</div>

<p id="loginnav">
	<a href="<?php echo add_query_arg( array( 'action' => 'login' ), get_permalink() ); ?>"><?php _e( 'Log in', 'lucidlms' ); ?></a> |
	<a href="<?php echo add_query_arg( array( 'action' => 'lostpassword' ), get_permalink() ); ?>" title="Password Lost and Found"><?php _e( 'Lost your password?', 'lucidlms' ); ?></a>
</p>