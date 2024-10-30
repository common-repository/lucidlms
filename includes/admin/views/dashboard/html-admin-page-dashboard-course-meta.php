<?php if ( $course->get_type() == 'course' ) { ?>
	<?php
	$course_elements = $course->get_elements_list();

	$available_course_element_types = LU()->taxonomies->get_terms(AETYPE, true);
	array_unshift( $available_course_element_types, __( 'Choose a type', 'lucidlms' ) );
	?>

	<?php lucidlms_wp_select( array(
		'id'      => 'new_element',
		'options' => $available_course_element_types,
	) ); ?>

	<p class="input-group">
		<input type="text" class="form-control new_element_name"
			   placeholder="<?php _e( 'Name', 'lucidlms' ) ?>"><span class="input-group-btn"><button
				class="btn btn-primary create-element"
				type="button"><?php _e( 'Create', 'lucidlms' ) ?></button></span>
	</p>

	<ul class="lucidlms-options course-elements">
		<?php if ( ! empty( $course_elements ) ) include dirname( LU_PLUGIN_FILE ) . '/includes/admin/post-types/meta-boxes/views/html-course-elements.php' ?>
	</ul>
<?php } ?>

<?php //TODO: Add ability for developers to customize course meta view ?>