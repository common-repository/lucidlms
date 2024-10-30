<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>

<div id="message" class="updated lucidlms-message notice is-dismissible">
	<?php if ( is_multisite() ) : ?>
        <p><?php printf( __( '<strong>Registration is disabled, please turn it on</strong>. In order LucidLMS to work properly, you need to go to <a href="%s">Network Admin -> Settings</a> and allow new user registrations.', 'lucidlms' ), network_admin_url( 'settings.php' ) ); ?></p>
	<?php else: ?>
        <p><?php printf( __( '<strong>Registration is disabled, please turn it on</strong>. In order LucidLMS to work properly, you need to go to <a href="%s">Settings -> General -> Membership</a> field and enable <b>"Anyone can register"</b>', 'lucidlms' ), admin_url( 'options-general.php' ) ); ?></p>
	<?php endif; ?>
</div>