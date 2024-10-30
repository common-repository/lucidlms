<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * LucidLMS Admin.
 *
 * @class 		LU_Admin
 * @author 		New Normal
 * @category 	Admin
 * @package 	LucidLMS/Admin
 * @version     1.0.0
 */
class LU_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'current_screen', array( $this, 'conditional_includes' ) );
		add_action( 'admin_init', array( $this, 'prevent_admin_access' ) );
		add_action( 'admin_footer', 'lucid_print_js', 25 );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		// Functions
		include_once( 'lucid-admin-functions.php' );
		include_once( 'lucid-forms-helper-functions.php' );

		// Classes
		include_once( 'class-lucid-admin-post-types.php' );

		// Classes we only need if the ajax is not-ajax
		if ( ! is_ajax() ) {
			include( 'class-lucid-admin-menus.php' );
			include( 'class-lucid-admin-notices.php' );
			include( 'class-lucid-admin-assets.php' );
			// TODO in current release (when we have courses/lessons/quizzes structure)
//			include( 'class-lucid-admin-permalink-settings.php' );
		}
	}

	/**
	 * Include admin files conditionally
	 */
	public function conditional_includes() {
		$screen = get_current_screen();

		switch ( $screen->id ) {
			case 'users' :
			case 'user' :
			case 'profile' :
			case 'user-edit' :
				include( 'class-lucid-admin-profile.php' );
				break;
		}
	}

	/**
	 * Prevent any user who cannot 'edit_posts' (students) from accessing admin
	 */
	public function prevent_admin_access() {
		$prevent_access = false;

		if ( ! is_ajax() && ! ( current_user_can( 'edit_posts' ) || current_user_can( 'manage_lucidlms' ) ) && basename( $_SERVER["SCRIPT_FILENAME"] ) !== 'admin-post.php' ) {
			$prevent_access = true;
		}

		$prevent_access = apply_filters( 'lucidlms_prevent_admin_access', $prevent_access );

		if ( $prevent_access ) {
			wp_safe_redirect( get_permalink( lucid_get_page_id( 'studentprofile' ) ) );
			exit;
		}
	}

}

return new LU_Admin();