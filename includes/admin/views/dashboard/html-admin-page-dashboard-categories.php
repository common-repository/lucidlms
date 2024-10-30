<?php

$excluded_posts = array();
$cat_index      = 0;
$categories     = get_terms( 'course_cat', array( 'hide_empty' => false ) ); ?>

<?php if ( ! empty( $categories ) ): ?>

	<?php foreach ( $categories as $category ): ?>

		<?php
		$courses = get_posts( array(
			'course_cat'     => $category->slug,
			'post__not_in'   => $excluded_posts,
			'post_type'      => 'course',
			'order'          => 'ASC',
			'post_status'    => array('publish', 'draft', 'not_active'),
			'orderby'        => 'name',
			'posts_per_page' => -1
		) );

		if ( empty( $courses ) ) {
			continue;
		} ?>

		<div class="course-category">

			<h4 class="course-category-title"><?php echo $category->name; ?></h4>

			<?php
			if ( ! empty ( $courses ) ) {
				include "html-admin-page-dashboard-courses.php";
			}
			$cat_index++; ?>

		</div>

	<?php endforeach; ?>

<?php endif;