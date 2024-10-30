<?php
foreach ( $courses as $index => $post ) {
	$course           = get_course( $post );
	$excluded_posts[] = $post->ID;
	?>
	<div class="collapsible-course">
		<?php include 'html-admin-page-dashboard-course.php'; ?>
	</div>
	<?php
}