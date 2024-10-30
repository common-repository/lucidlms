<div class="wrap lucidlms">
	<form method="post" id="mainform" action="" enctype="multipart/form-data">

		<?php if (count($tabs) > 1) : ?>
			<h2 class="nav-tab-wrapper lucid-nav-tab-wrapper">
				<?php
				foreach ( $tabs as $name => $label )
					echo '<a href="' . admin_url( 'admin.php?page=lucid-settings&tab=' . $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';

				do_action( 'lucidlms_settings_tabs' );
				?>
			</h2>
		<?php endif; ?>

		<?php
		do_action( 'lucidlms_sections_' . $current_tab );
		do_action( 'lucidlms_settings_' . $current_tab );
		?>

		<p class="submit">
			<?php if ( ! isset( $GLOBALS['hide_save_button'] ) ) : ?>
				<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', 'lucidlms' ); ?>" />
			<?php endif; ?>
			<input type="hidden" name="subtab" id="last_tab" />
			<?php wp_nonce_field( 'lucidlms-settings' ); ?>
		</p>
	</form>
</div>