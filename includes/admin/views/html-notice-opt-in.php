<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>
<div id="message" class="updated lucidlms-message notice is-dismissible">
    <p><?php printf( __( 'Help us support your installation: opt-in to send us initial meta-data about your copy of LucidLMS.', 'lucidlms' ) ); ?></p>
    <p class="submit">
        <a href="<?php echo add_query_arg( 'allow_opt_in', 'true', admin_url( 'admin.php?page=lucid-settings' ) ); ?>"
           class="button button-primary"><?php _e( 'Allow sending data', 'lucidlms' ); ?></a>
    </p>
</div>