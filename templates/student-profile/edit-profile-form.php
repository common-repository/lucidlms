<?php
/**
 * Edit profile form
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/* Get user info. */
global $current_user, $wp_roles;
wp_get_current_user();

/**
 * TODO: move this to student handler @see LU_Student_Handler via lucidlms_init hook
 */
/* If profile was saved, update profile. */
if ( 'POST' == $_SERVER['REQUEST_METHOD'] && ! empty( $_POST['action'] ) && $_POST['action'] == 'update-user' ) {

	if ( wp_verify_nonce( $_POST['_wpnonce'], 'update-user' ) ) {

		/* Update data*/
		if ( ! empty( $_POST['first_name'] ) ) {
			update_user_meta( $current_user->ID, 'first_name', sanitize_text_field( $_POST['first_name'] ) );
		}
		if ( ! empty( $_POST['last_name'] ) ) {
			update_user_meta( $current_user->ID, 'last_name', sanitize_text_field( $_POST['last_name'] ) );
		}
		if ( ! empty( $_POST['user_utc'] ) ) {
			update_user_meta( $current_user->ID, 'utc', sanitize_text_field( $_POST['user_utc'] ) );
		}

        if( ! empty( $_POST['user_email'] ) ){
            wp_update_user( array(
                'ID'    => $current_user->ID,
                'user_email' => sanitize_text_field( $_POST['user_email'] ),
            ) );
        }

		if( !empty($_POST['user_pass']) && !empty($_POST['user_pass2'])){
			if(($_POST['user_pass']==$_POST['user_pass2'])) {

				$pass = sanitize_text_field( $_POST['user_pass'] );
				wp_set_password( $pass, $current_user->ID );
                echo '<p class="lucidlms-info">' . __('Password was changed', 'lucidlms') . '</p>';
			} else {
                echo '<p class="lucidlms-info">' . __('Passwords does not match', 'lucidlms') . '</p>';
			}
		}


		if ( ! empty( $_POST['user_country'] ) ) {
            update_user_meta( $current_user->ID, 'user_country', sanitize_text_field( $_POST['user_country'] ) );
	        if ( $_POST['user_country'] !== 'US' ) {
		        delete_user_meta( $current_user->ID, 'user_state' );
	        } else {
		        if ( ! empty( $_POST['user_state'] ) ) {
			        update_user_meta( $current_user->ID, 'user_state', sanitize_text_field( $_POST['user_state'] ) );
		        }
	        }
        }


		// action hook for plugins and extra fields saving
		do_action( 'edit_user_profile_update', $current_user->ID );

	}

}

?>

<div id="edit-profile-form">
	<p><?php _e( 'Edit profile data.', 'lucidlms' ); ?></p>

	<form action="" method="post">

		<p class="register-first_name">
			<label for="first_name"><?php _e( 'First Name' ) ?><br />
				<input type="text" name="first_name" id="first_name" class="input" size="25" value="<?php the_author_meta( 'first_name', $current_user->ID ); ?>" />
			</label>
		</p>

		<p class="register-last_name">
			<label for="last_name"><?php _e( 'Last Name' ) ?><br />
				<input type="text" name="last_name" id="last_name" class="input" size="25" value="<?php the_author_meta( 'last_name', $current_user->ID ); ?>" />
			</label>
		</p>

        <p class="register-user_email">
            <label for="user_email"><?php _e( 'User email' ) ?><br />
                <input type="text" name="user_email" id="user_email" class="input" size="25" value="<?php the_author_meta( 'user_email', $current_user->ID ); ?>" />
            </label>
        </p>

        <p class="register-user_country">
            <label for="user_country"><?php _e( 'Country', 'lucidlms' ) ?><br />
                <?php lucidlms_countries_selectbox(); ?>
            </label>
        </p>

		<p class="register-user_state">
            <label for="user_state"><?php _e( 'State', 'lucidlms' ) ?><br />
                <?php lucidlms_states_selectbox(); ?>
            </label>
        </p>

		<p class="register-user_utc">
			<label for="user_utc"><?php _e( 'Choose time zone', 'lucidlms' ) ?><br />
				<select name="user_utc" id="user_utc" class="input">
					<option><?php _e('Select your time-zone', 'lucidlms'); ?></option>
					<?php
					$selected = get_user_meta( $current_user->ID, 'utc', true );
					$x = -11;

					$zones_array = array();
					$timestamp = time();
					foreach(timezone_identifiers_list() as $key => $zone) {
						date_default_timezone_set($zone);
						$zones_array[$key]['zone'] = $zone;
						$zones_array[$key]['offset'] = date('Z', $timestamp);
						$zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
					}


					foreach($zones_array as $t) {
						echo '<option value ="'.$t['offset'].'"';
						if ($t['offset'] == $selected && $selected!=null){
							echo 'selected';
						}
						echo'>'.$t['diff_from_GMT'] . ' - ' . $t['zone'].'</option>';
					 }
					
					?>
				</select>
			</label>
		</p>

        <h3><?php _e( 'Change password', 'lucidlms' ); ?></h3>
        <p class="register-user_pass">
			<label for="user_pass"><?php _e( 'New password', 'lucidlms' ) ?><br />
				<input type="password" name="user_pass" id="user_pass" class="input" size="35" />
			</label>
			<label for="user_pass2"><?php _e( 'New password confirmation', 'lucidlms' ) ?><br />
				<input type="password" name="user_pass2" id="user_pass2" class="input" size="35" />
			</label>
		</p>

		<?php do_action( 'edit_user_profile', $current_user ); ?>

		<p class="form-submit">
			<input type="submit" value="<?php _e( 'Update', 'lucidlms' ); ?>" name="updateuser" id="updateuser" />
			<?php wp_nonce_field( 'update-user' ) ?>
			<input name="action" type="hidden" id="action" value="update-user" />
		</p>

	</form>

	<p><?php echo '<a href="' . get_permalink() . '">' . __( 'Back to the profile', 'lucidlms' ) . '</a>'; ?></p>
</div>