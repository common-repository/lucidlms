<?php foreach ( $course_elements as $id => $element ): ?>
	<li class="course-element <?php echo $element['type'] ?> panel panel-default" id="element-<?php echo $id ?>">
		<div class="panel-body">
			<div class="panel-body-inner">
				<div class="course-element-info-wrap">
					<i class="content-icon fa <?php echo ( 'quiz' == $element['type'] ) ? 'fa-question' : 'fa-book' ?>"
					   title="<?php echo $element['type_name'] ?>"></i>

					<div class="type"><?php echo $element['type_name'] ?></div>
					<div class="title"><?php echo stripslashes($element['title']) ?></div>
				</div>

				<?php do_action( 'lucidlms_course_element_content', $id ); ?>

				<div class="btn-wrap">
					<button type="button" class="btn btn-default edit-element">
						<span class="glyphicon glyphicon-edit"></span>
					</button>
					<button type="button" class="btn btn-default remove-element">
						<span class="glyphicon glyphicon-remove"></span>
					</button>
				</div>
			</div>
			<?php do_action( 'lucidlms_after_course_element_content', $id ); ?>
		</div>
	</li>
<?php endforeach; ?>