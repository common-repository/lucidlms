<?php
/**
 * Add extra profile fields for users in admin.
 *
 * @author 		New Normal
 * @category 	Admin
 * @package 	LucidLMS/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'LU_Admin_Profile' ) ) :

	/**
	 * LU_Admin_Profile Class
	 */
	class LU_Admin_Profile {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			add_action( 'show_user_profile', array( $this, 'add_student_meta_fields' ) );
			add_action( 'edit_user_profile', array( $this, 'add_student_meta_fields' ) );
			add_action( 'personal_options_update', array( $this, 'save_student_meta_fields' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_student_meta_fields' ) );
		}

		/**
		 * Get Student Meta Fields for the edit user pages.
		 *
		 * @return array Fields to display which are filtered through lucidlms_student_meta_fields before being returned
		 */
		public function get_student_meta_fields() {
			$show_fields = apply_filters('lucidlms_student_meta_fields', array(
				'lucidlms' => array(
					'title' => __( 'LucidLMS data', 'lucidlms' ),
					'fields' => array(
						// Add here needed student fields via filter. Example:
//						'rid_number' => array(
//							'label' => __( 'Recipient Identification (RID) number', 'lucidlms' ),
//							'description' => 'Used for issuing a certificate.'
//						)
					)
				)
			));
			return $show_fields;
		}

		/**
		 * Show Student Meta Fields on edit user pages.
		 *
		 * @param mixed $user User (object) being displayed
		 */
		public function add_student_meta_fields( $user ) {
			if ( ! current_user_can( 'manage_lucidlms' ) )
				return;

			$show_fields = $this->get_student_meta_fields();

			foreach( $show_fields as $fieldset ) :

				if ( empty( $fieldset['fields'] ) ) {
					return false;
				}

				?>
				<h3><?php echo $fieldset['title']; ?></h3>
				<table class="form-table">
					<?php
					foreach( $fieldset['fields'] as $key => $field ) :
						?>
						<tr>
							<th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( get_user_meta( $user->ID, $key, true ) ); ?>" class="regular-text" /><br/>
								<span class="description"><?php echo wp_kses_post( $field['description'] ); ?></span>
							</td>
						</tr>
					<?php
					endforeach;
					?>
				</table>
			<?php
			endforeach;
		}

		/**
		 * Save Custom Meta Fields on edit user pages
		 *
		 * @param mixed $user_id User ID of the user being saved
		 */
		public function save_student_meta_fields( $user_id ) {
			$save_fields = $this->get_student_meta_fields();

			foreach( $save_fields as $fieldset )
				foreach( $fieldset['fields'] as $key => $field )
					if ( isset( $_POST[ $key ] ) )
						update_user_meta( $user_id, $key, sanitize_text_field( $_POST[ $key ] ) );
		}
	}

endif;

return new LU_Admin_Profile();
