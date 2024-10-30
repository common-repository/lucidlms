<div class="wrap lucidlms lucidlms-dashboard">

	<h2>
		<?php _e( 'Dashboard', 'lucidlms' ); ?>
		<input type="text" class="new_course_name"
			   placeholder="<?php _e( 'Give it a title', 'lucidlms' ) ?>">
		<a href="#" class="add-new-h2 add-course"><?php _e( 'Add course', 'lucidlms' ); ?></a>
		<a href="#" class="add-new-h2 confirm-course-title"><?php _e( 'Create', 'lucidlms' ); ?></a>
	</h2>


	<?php lucidlms_wp_hidden_input( array(
		'id'    => 'is_dashboard',
		'value' => TRUE
	) ); ?>

	<div class="dashboard-courses">
		<?php include 'dashboard/html-admin-page-dashboard-categories.php'; ?>
	</div>

</div>
