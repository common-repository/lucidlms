<?php
/**
 * Instructor Meta
 *
 * Displays the instructor meta box.
 *
 * @author        New Normal
 * @category      Admin
 * @package       LucidLMS/Admin/Meta Boxes
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * LU_Meta_Box_Instructor
 */
class LU_Meta_Box_Instructors {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
		global $post;

		wp_nonce_field( 'lucidlms_save_data', 'lucidlms_meta_nonce' );

		?>
		<div class="lucidlms-meta-box">

			<div class="lucidlms-options instructors">

				<?php
				lucidlms_wp_text_input( array(
					'id'          => '_instructor_position',
					'label'       => __( 'Position', 'lucidlms' ),
					'class'       => 'short',
					'value'       => get_post_meta( $post->ID, '_instructor_position', true ),
				) );

				lucidlms_wp_text_input( array(
					'id'          => '_instructor_fb_link',
					'label'       => __( 'Facebook', 'lucidlms' ),
					'class'       => 'short',
					'value'       => get_post_meta( $post->ID, '_instructor_fb_link', true ),
				) );

				lucidlms_wp_text_input( array(
					'id'          => '_instructor_tw_link',
					'label'       => __( 'Twitter', 'lucidlms' ),
					'class'       => 'short',
					'value'       => get_post_meta( $post->ID, '_instructor_tw_link', true ),
				) );

				lucidlms_wp_text_input( array(
					'id'          => '_instructor_in_link',
					'label'       => __( 'LinkedIn', 'lucidlms' ),
					'class'       => 'short',
					'value'       => get_post_meta( $post->ID, '_instructor_in_link', true ),
				) );

				lucidlms_wp_text_input( array(
					'id'          => '_instructor_email_link',
					'label'       => __( 'Email', 'lucidlms' ),
					'class'       => 'short',
					'value'       => get_post_meta( $post->ID, '_instructor_email_link', true ),
				) );

				?>

				<?php do_action( 'lucidlms_instructors_metabox_after_general_settings' ); ?>

			</div>

			<?php do_action( 'lucidlms_instructors_metabox_output' ); ?>
		</div>
	<?php }

	/**
	 * Save meta box data
	 */
	public static function save( $post_id, $post ) {

		if ( isset( $_POST['_instructor_position'] ) ) {
			$instructor_position = sanitize_text_field( $_POST['_instructor_position'] );
			update_post_meta( $post_id, '_instructor_position', $instructor_position );
		}

		if ( isset( $_POST['_instructor_fb_link'] ) ) {
			$instructor_fb_link = sanitize_text_field( $_POST['_instructor_fb_link'] );
			update_post_meta( $post_id, '_instructor_fb_link', $instructor_fb_link );
		}

		if ( isset( $_POST['_instructor_tw_link'] ) ) {
			$instructor_tw_link = sanitize_text_field( $_POST['_instructor_tw_link'] );
			update_post_meta( $post_id, '_instructor_tw_link', $instructor_tw_link );
		}

		if ( isset( $_POST['_instructor_in_link'] ) ) {
			$instructor_in_link = sanitize_text_field( $_POST['_instructor_in_link'] );
			update_post_meta( $post_id, '_instructor_in_link', $instructor_in_link );
		}

		if ( isset( $_POST['_instructor_email_link'] ) ) {
			$instructor_email_link = sanitize_text_field( $_POST['_instructor_email_link'] );
			update_post_meta( $post_id, '_instructor_email_link', $instructor_email_link );
		}

	}
}
