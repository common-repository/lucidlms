<?php if ( ! empty( $questions ) ): ?>
	<?php foreach ( $questions as $question_id => $question ): ?>
		<?php
		$question          = $question->get_data();
		$unused_categories = array_diff( $categories, $question['categories'] ); ?>

		<li class="question <?php echo $question['question_type'] ?> panel panel-default"
		    data-id="<?php echo esc_attr( $question_id ); ?>" id="question-<?php echo esc_attr( $question_id ); ?>">
			<div class="panel-body">
				<div class="panel-body-inner">
					<div class="question-info-wrap">

						<?php if ( $question['has_content'] ): ?>
							<i class="content-icon fa fa-file-text"
							   title="<?php _e( 'Content filled in', 'lucidlms' ) ?>"></i>
						<?php else: ?>
							<i class="content-icon fa fa-file-o"
							   title="<?php _e( 'Content missing', 'lucidlms' ) ?>"></i>
						<?php endif; ?>

						<div class="type"><?php echo $question['question_type_name'] ?></div>
						<div class="title"><?php echo $question['question_text'] ?></div>
					</div>

					<div class="taxonomies">
						<ul class="categories">
							<?php if ( ! empty( $question['categories'] ) ): ?>
								<?php foreach ( $question['categories'] as $category_id => $category ): ?>
									<li class="label label-success" data-id="<?php echo $category_id ?>">
										<?php echo $category ?>
										<i class="fa fa-times delete"></i>
									</li>
								<?php endforeach; ?>
							<?php else: ?>
								<li class="label label-success empty">
									<?php _e( 'No categories' ) ?>
								</li>
							<?php endif; ?>
							<li class="label add-category <?php echo empty( $unused_categories ) ? 'no-categories' : '' ?>">
								<a class="add" href="#"><i class="fa fa-plus-circle"></i>
									<span><?php _e( 'Add category', 'lucidlms' ) ?></span></a>

								<div class="categories-select-wrapper">
									<select class="categories-select">
										<option
												selected="selected"><?php _e( 'Choose a category', 'lucidlms' ) ?></option>
										<?php foreach ( $unused_categories as $category_id => $category ): ?>
											<option value="<?php echo $category_id ?>"><?php echo $category ?></option>
										<?php endforeach; ?>
									</select>
									<a class="cancel" href="#"><?php _e( 'Cancel', 'lucidlms' ) ?></a>
								</div>
							</li>
						</ul>
						<div class="cf"></div>
						<ul class="courses">
							<?php if ( ! empty( $question['courses'] ) ): ?>
								<?php foreach ( $question['courses'] as $course ): ?>
									<li class="label label-primary">
										<?php echo $course['title'] ?>
									</li>
								<?php endforeach; ?>
							<?php else: ?>
								<li class="label label-primary empty">
									<?php _e( 'No courses' ) ?>
								</li>
							<?php endif; ?>
						</ul>
						<div class="cf"></div>

					</div>

					<div class="btn-wrap">
						<button type="button" class="btn btn-default edit-element">
							<span class="glyphicon glyphicon-edit"></span>
						</button>
						<button type="button" class="btn btn-default remove-element">
							<span class="glyphicon glyphicon-remove"></span>
						</button>
					</div>
				</div>
			</div>
		</li>
	<?php endforeach; ?>
<?php else: ?>
	<div class="no-questions-found">
		<?php _e( 'Sorry, no questions found...', 'lucidlms' ) ?>
	</div>
<?php endif; ?>