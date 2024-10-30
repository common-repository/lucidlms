<?php
/**
 * Course Meta
 *
 * Displays the course meta box.
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
 * LU_Meta_Box_Course
 */
class LU_Meta_Box_Course {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
		global $post, $wpdb, $thepostid, $current_user;

		wp_nonce_field( 'lucidlms_save_data', 'lucidlms_meta_nonce' );

		$thepostid = $post->ID;

		$course         = get_course( $thepostid );
		$course_type    = $course->get_type();
		$woo_product_id = $course->get_woo_product_id();

		if ( ! $post = get_post( $woo_product_id ) ) { // if no product found show error and remove dependency
			//@todo show an error
			$woo_product_id = null;
			$course->set_woo_product_id( null )->update_woo_product_id();
		}
		?>

		<div class="lucidlms-meta-box">

			<h4 class="section-title collapsible collapsed" data-toggle="collapse" data-target="#settings">
                <i class="fa fa-chevron-down"></i> <?php _e( 'General Settings', 'lucidlms' ) ?>
            </h4>

			<div class="lucidlms-options general <?php echo $course_type ?> collapse" id="settings">

				<?php lucidlms_wp_hidden_input( array(
					'id'            => 'course_type',
					'value'         => $course_type,
					'wrapper_class' => 'taxonomy',
				));

				lucidlms_wp_text_input( array(
					'id'                => '_availability_time',
					'label'             => __( 'Availability Time (days)', 'lucidlms' ),
					'desc_tip'          => 'true',
					'description'       => __( 'Defines how long the course will be available to a student once he started it. Enter "0" if you want it to be indefinite.', 'lucidlms' ),
					'value'             => $course->availability_time,
					'type'              => 'number',
					'custom_attributes' => array(
						'min'  => '0',
						'step' => '1'
					)
				) );

				lucidlms_wp_select( array(
					'id'          => '_sequential_logic',
					'label'       => __( 'Enable Sequential Logic', 'lucidlms' ),
					'desc_tip'    => 'true',
					'description' => __( 'If enabled, student will have to pass all course elements one by one in the defined order.', 'lucidlms' ),
					'value'       => $course->sequential_logic ? 'true' : 'false',
					'options'     => array(
						'false' => __( 'False', 'lucidlms' ),
						'true'  => __( 'True', 'lucidlms' )
					)
				) );

				lucidlms_wp_select( array(
					'id'          => '_course_visibility',
					'label'       => __( 'Course Visibility', 'lucidlms' ),
					'desc_tip'    => 'true',
					'description' => __( 'If enabled, course elements will be visible after complete course.', 'lucidlms' ),
					'value'       => $course->visibility ? 'true' : 'false',
					'options'     => array(
						'false' => __( 'False', 'lucidlms' ),
						'true'  => __( 'True', 'lucidlms' )
					)
				) );

				lucidlms_wp_radio( array(
					'id'          => '_threshold_type',
					'label'       => __( 'Threshold Type', 'lucidlms' ),
					'sub-heading' => 'Describes how student will receive completion certificate upon successfully finishing the course.',
					'class'       => '',
					'default'     => 'none',
					'value'       => $course->threshold_type,
					'options'     => apply_filters( 'lucidlms_course_threshold_type_options', array(
						'none'    => array(
							__( "None.", 'lucidlms' ),
							__( "The certificate will not list student's score.", 'lucidlms' )
						),
						'average' => array(
							__( "Each quiz individually.", 'lucidlms' ),
							__( "The certificate will list student's average score on all quizzes.", 'lucidlms' )
						),
						'final'   => array(
							__( "Final quiz.", 'lucidlms' ),
							__( "The certificate will list student's final quiz score. Threshold field is required in final quiz.", 'lucidlms' )
						)
					) )
				) );

				lucidlms_wp_image_upload( array(
					'id'                => '_certificate_template',
					'label'             => __( 'Certificate Template image', 'lucidlms' ),
					'desc_tip'          => 'true',
					'class'             => 'short',
					'description'       => __( 'Before uploading an image, you should prepare it as on the example (everything that is text should be removed). Supported formats for upload are: png, jpg, jpeg. PNG example is used by default if not specified.', 'lucidlms' ),
					'extra-description' => sprintf( __( '<a href="%s" target="_blank">Download PDF example</a>. <a href="%s" target="_blank">Download PNG example</a>', 'lucidlms' ), esc_url( LU()->plugin_url() . '/assets/images/certificate_example.pdf' ), esc_url( LU()->plugin_url() . '/assets/images/certificate-default.png' ) ),
					'value'             => $course->certificate_template,
				) );

				$query                 = new WP_Query( array(
					'post_type'      => 'instructors',
					'orderby'        => 'ID',
					'order'          => 'ASC',
					'posts_per_page' => '-1'
				) );
				$available_instructors = array();

				array_unshift( $available_instructors, __( 'Choose a instructor', 'lucidlms' ) );

				while ( $query->have_posts() ) {
					$query->the_post();

					$available_instructors[ $query->post->ID ] = $post->post_title;
				}
				wp_reset_postdata();

				lucidlms_wp_select( array(
					'id'          => '_instructor_id',
					'value'       => $course->instructor_id,
					'label'       => __( 'Instructor', 'lucidlms' ),
					'desc_tip'    => 'true',
					'description' => __( 'You can choose a course instructor', 'lucidlms' ),
					'options'     => $available_instructors,
				) );

				lucidlms_wp_text_input( array(
					'id'          => '_custom_certificate_text',
					'label'       => __( 'Custom certificate text', 'lucidlms' ),
					'class'       => '',
					'desc_tip'    => 'true',
					'description' => __( 'You may specify custom text displayed under Instructor field.', 'lucidlms' ),
					'value'       => $course->custom_certificate_text
				) );

				if ( 'yes' == get_option( 'lucidlms_woocommerce_integration_enabled' ) ): ?>

					<h4 class="section-title"><?php _e( 'Sell online', 'lucidlms' ) ?></h4>
					<div class="lucidlms-options woocommerce-integration">

						<fieldset class="form-field woocommerce-connected-product">
							<legend><?php _e( 'Connected Product', 'lucidlms' ) ?></legend>
							<?php if ( $woo_product_id ): ?>
								<?php edit_post_link( __( 'WooCommerce Product #', 'lucidlms' ) . $woo_product_id, '', '', $woo_product_id ) ?>

								<?php /**
								 * @TODO [future-releases] maybe add here some js confirm when trying to remove this product
								 * this product is removing to the trash, but we need to let user know what is he doing :)
								 */
								?>
								<input type="submit" class="btn btn-default remove-connected-product"
									   name="remove_connected_product" value="<?php _e( 'remove', 'lucidlms' ) ?>"/>

							<?php else: ?>
								<div class="no-product-found"><?php _e( 'No product found.', 'lucidlms' ) ?></div>
								<input type="submit" class="btn btn-default create-connected-product"
									   name="create_connected_product" value="<?php _e( 'create', 'lucidlms' ) ?>"/>
							<?php endif; ?>
						</fieldset>


						<?php if ( $woo_product_id ):
							lucidlms_wp_text_input( array(
								'id'          => '_woocommerce_course_price',
								'label'       => __( 'Course Price', 'lucidlms' ),
								'placeholder' => '0.00',
								'class'       => '',
								'desc_tip'    => 'true',
								'description' => __( 'Price of associated course product', 'lucidlms' ),
								'value'       => number_format( floatval( get_post_meta( $woo_product_id, '_price', true ) ), 2, '.', '' ),
							) );
						endif; ?>
					</div>

				<?php endif;

				do_action( 'lucidlms_course_metabox_output_' . $course_type ); ?>

				<?php do_action( 'lucidlms_course_metabox_after_general_settings', $course ); ?>

			</div>

			<?php if ( $course_type == 'course' ):
				$course_elements                = $course->get_elements_list();
				$available_course_element_types = LU()->taxonomies->get_terms( AETYPE, true );

				array_unshift( $available_course_element_types, __( 'Choose a type', 'lucidlms' ) );
				?>
				<h4 class="section-title"><?php _e( 'Course elements', 'lucidlms' ) ?></h4>

				<?php lucidlms_wp_select( array(
				'id'      => 'new_element',
				'options' => $available_course_element_types,
			) ); ?>

				<p class="input-group"><input type="text" class="form-control new_element_name"
				                              placeholder="<?php _e( 'Name', 'lucidlms' ) ?>"><span
						class="input-group-btn"><button
							class="btn btn-primary create-element"
							type="button"><?php _e( 'Create', 'lucidlms' ) ?></button></span></p>

				<ul class="lucidlms-options course-elements">
					<?php if ( ! empty( $course_elements ) ) include 'views/html-course-elements.php' ?>
				</ul>

				<?php do_action( 'lucidlms_course_metabox_output_course_elements' ); ?>

			<?php endif;

			do_action( 'lucidlms_course_metabox_output' ); ?>
		</div>
	<?php }

	/**
	 * Save meta box data
	 */
	public static function save( $post_id, $post ) {
		$course      = get_course( $post_id );
		$course_type = $course->get_type();

		if ( isset( $_POST['course_type'] ) ) {
			$course->set_type( stripslashes( $_POST['course_type'] ) ); //@todo: add errors processing!
		}

		if ( $course = get_course( $post_id ) ):

			if ( $course_type == 'course' ) {
				if ( isset( $_POST['_availability_time'] ) ) {
					$course->availability_time = sanitize_text_field( $_POST['_availability_time'] );
				}
				if ( isset( $_POST['_sequential_logic'] ) ) {
					$course->sequential_logic = sanitize_text_field( $_POST['_sequential_logic'] );
				}
				if ( isset( $_POST['_course_visibility'] ) ) {
					$course->visibility = sanitize_text_field( $_POST['_course_visibility'] );
				}
				if ( isset( $_POST['_threshold_type'] ) ) {
					$course->threshold_type = sanitize_text_field( $_POST['_threshold_type'] );
				}

				if ( isset( $_POST['_instructor_id'] ) ) {
					$course->instructor_id = sanitize_text_field( $_POST['_instructor_id'] );
				}

				if ( isset( $_POST['_custom_certificate_text'] ) ) {
					$course->custom_certificate_text = sanitize_text_field( $_POST['_custom_certificate_text'] );
				}

				// Make sure the file array isn't empty
				if ( ! empty( $_FILES['_certificate_template']['name'] ) ) {

					// Setup the array of supported file types.
					$supported_types = array( 'image/png', 'image/jpeg' );

					// Get the file type of the upload
					$arr_file_type = wp_check_filetype( basename( $_FILES['_certificate_template']['name'] ) );
					$uploaded_type = $arr_file_type['type'];

					// Check if the type is supported. If not, throw an error.
					if ( in_array( $uploaded_type, $supported_types ) ) {

						// Use the WordPress API to upload the file
						$upload = wp_upload_bits( $_FILES['_certificate_template']['name'], null, file_get_contents( $_FILES['_certificate_template']['tmp_name'] ) );

						if ( isset( $upload['error'] ) && $upload['error'] != 0 ) {
							wp_die( 'There was an error uploading your file. The error is: ' . $upload['error'] );
						} else {
							$course->certificate_template = $upload;
						} // end if/else

					} else {
						wp_die( "This file type is not allowed." );
					} // end if/else

				} // end if

			}

			// --------- WooCommerce Integration Processing ------------

			if ( isset( $_POST['_woocommerce_course_price'] ) ) {

				if ( $woo_product_id = $course->get_woo_product_id() ) {
					$price = floatval( sanitize_text_field( $_POST['_woocommerce_course_price'] ) );
					update_post_meta( $woo_product_id, '_price', $price );
					update_post_meta( $woo_product_id, '_regular_price', $price );
				}

			}

			if ( isset( $_POST['create_connected_product'] ) ) {
				lucidlms_create_update_connected_product( $course );
			}

			if ( isset( $_POST['remove_connected_product'] ) ) {
				if ( ! lucidlms_remove_connected_product( $course ) ) {
					//@todo add dashboard notices with errors
				}
			}

			// ------ End of WooCommerce Integration Processing ---------


			$course->flush();

		endif;

		remove_action( 'lucid_process_course_meta', 'LU_Meta_Box_Course::save', 10, 2 ); // to prevent recursion

		// Do action for course type
		do_action( 'lucid_process_course_meta_' . $course_type, $post_id );

	}
}
