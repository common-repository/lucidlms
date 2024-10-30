<div class="entry-header expand">
	<i class="arrow-icon fa fa-chevron-down"></i>

	<h2 class="entry-title <?php echo $course->post->post_status; ?>">
<!--		--><?php //echo $course->get_image(); TODO: replace with bg ?>
		<?php echo $course->title; ?>
		<?php if ($course->get_product_price()) { ?>
		<span class="amount-label"><?php _e('Price ', 'lucidlms'); ?></span><?php echo $course->get_product_price(); ?>
		<?php } ?>
		<?php echo in_array($course->course_status, array('draft', 'not_active') ) ? '<span class="status">' . $course->course_status_label . '</span>' : ''; ?>
	</h2>
</div>


<div class="collapse <?php echo $index === 0 && $cat_index === 0 ? 'shown' : ''; ?>">

	<?php do_action('lucidlms_before_dashboard_course', $course); ?>

	<div class="dashboard-course">
		<ul class="course-menu">
			<li>
				<a target="_blank" class="btn btn-link" href="<?php echo admin_url( 'edit.php' ) . '?post_type=score_card&view_course_id=' . $course->id; ?>"><?php _e( 'Students', 'lucidlms' ); ?></a>
			</li>
			<li>
				<a class="btn btn-link" href="<?php echo admin_url( 'post.php' ) . '?post=' . $course->id . '&action=edit'; ?>"><?php _e( 'Settings', 'lucidlms' ); ?></a>
			</li>
			<li>
				<a target="_blank" class="btn btn-link" href="<?php echo $course->post->post_status === 'draft' ? home_url( '?post_type=course&p=' . $course->id . '&preview=true' ) : $course->get_permalink(); ?>">
					<?php echo $course->post->post_status === 'draft' ? __( 'Preview', 'lucidlms' ) : __( 'View', 'lucidlms' ); ?>
				</a>
			</li>
			<li class="change-course-status">
                <?php if( 'publish' == $course->course_status): ?>
				    <a class="btn btn-link" href="#" data-status="draft"><?php _e( 'Unpublish', 'lucidlms' ); ?></a> or
                    <a class="btn btn-link" href="#" data-status="not_active"><?php _e( 'Deactivate', 'lucidlms' ) ?></a>
                <?php else: ?>
                    <a class="btn btn-link" href="#" data-status="publish"><?php _e( 'Publish', 'lucidlms' ) ?></a>
                <?php endif; ?>
			</li>
		</ul>

		<?php $available_course_types = LU()->taxonomies->get_terms( ATYPE, TRUE ); ?>

		<?php lucidlms_wp_hidden_input( array(
			'value' => $course->get_type(),
			'id'    => 'course_type',
			'class' => 'lucid-radios'
		) ); ?>

		<?php
		lucidlms_wp_hidden_input( array(
			'id'    => 'course_id',
			'value' => $course->id
		) );
		?>

		<div class="inside">

			<?php include 'html-admin-page-dashboard-course-meta.php' ?>

		</div>
	</div>

	<?php do_action('lucidlms_after_dashboard_course', $course->id); ?>

</div>