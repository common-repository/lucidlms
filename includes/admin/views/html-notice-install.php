<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>
<div id="message" class="updated lucidlms-message">
	<p><?php _e( '<strong>Welcome to LucidLMS</strong>. You need to install some pages for the plugin.', 'lucidlms' ); ?></p>

	<p class="submit">
		<a href="<?php echo add_query_arg( 'install_lucidlms_pages', 'true', admin_url( 'admin.php?page=lucid-settings' ) ); ?>" class="button-primary"><?php _e( 'Install LucidLMS Pages', 'lucidlms' ); ?></a>
		<a class="skip button-primary" href="<?php echo add_query_arg( 'skip_install_lucidlms_pages', 'true', admin_url( 'admin.php?page=lucid-settings' ) ); ?>"><?php _e( 'Skip setup', 'lucidlms' ); ?></a>
	</p>
</div>

