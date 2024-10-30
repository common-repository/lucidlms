<?php
/**
 * Login form
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( isset( $_REQUEST['message'] ) ) {
	?>

    <p class="lucidlms-info">
		<?php if ( $_REQUEST['message'] === 'registered' ) {
			_e( 'You have successfully registered for a membership. Please check your e-mail for a password.', 'lucidlms' );
		} ?>
    </p>

	<?php
}

$login_args = array();
if ( isset( $_REQUEST['return_to_course'] ) ) {
	$course_id              = $_REQUEST['return_to_course'];
	$login_args['redirect'] = add_query_arg( array( 'start_course' => $course_id ), get_permalink( $course_id ) );
}

?>
<p><?php _e( 'Please log in to access your profile.', 'lucidlms' ); ?></p>
<?php wp_login_form( $login_args ); ?>

<p id="loginnav">

	<?php if ( is_multisite() ) : ?>
        <a href="<?php echo wp_registration_url(); ?>"><?php _e( 'Not a member yet?', 'lucidlms' ); ?></a> |
	<?php else : ?>
        <a href="<?php echo add_query_arg( array(
			'action' => 'register',
			'role'   => 'student'
		), get_permalink() ); ?>"><?php _e( 'Not a member yet?', 'lucidlms' ); ?></a> |
	<?php endif; ?>
    <a href="<?php echo add_query_arg( array( 'action' => 'lostpassword' ), get_permalink() ); ?>" title="Password Lost and Found"><?php _e( 'Lost your password?', 'lucidlms' ); ?></a>
</p>