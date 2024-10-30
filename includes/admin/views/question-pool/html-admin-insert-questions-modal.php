<div class="description"><a href="<?php echo admin_url('admin.php?page=lucid-question-pool') ?>" target="_blank"><?php _e('Browse Question Pool &rarr;', 'lucidlms') ?></a></div>
<?php foreach( $categories as $category_id => $category ): ?>
    <div class="question-group">

        <?php lucidlms_wp_checkbox( array(
            'label'         => $category['name'],
            'id'            => 'category-' . $category_id,
            'cbvalue'       => $category_id,
            'class'         => 'category category-' . $category_id,
            'wrapper_class'       => 'category-wrapper',
        )) ?>

        <?php foreach($category['questions'] as $question_id => $question ): ?>
            <?php lucidlms_wp_checkbox( array(
                'label'         => $question['question_text'],
                'id'            => 'question-' . $question_id,
                'cbvalue'       => $question_id,
                'class'         => 'question question-' . $question_id,
                'wrapper_class'       => 'question-wrapper',
            )) ?>
        <?php endforeach; ?>
    </div>
<?php endforeach;