<div class="description"><?php _e('Split your questions into categories. You will be able to pull entire categories of questions into your quizzes.', 'lucidlms') ?></div>
<div class="create-category">
    <a href="#" class="create"><i class="fa fa-plus"></i> <?php _e('Create new category', 'lucidlms') ?></a>
    <form class="create-category-form">
        <input type="text" class="new-category-name" name="new-category-name" id="new-category-name" placeholder="<?php _e('New category name', 'lucidlms') ?>">
        <input type="submit" class="btn btn-primary create-category" value="<?php _e('Create', 'lucidlms') ?>">
    </form>
</div>

<ul class="list-group categories-list">
    <?php foreach( $categories as $category_id => $category ):?>
        <li class="list-group-item category-row" data-category-id="<?php echo $category_id ?>">

            <input type="text" class="category category-<?php echo $category_id ?>" name="category-<?php echo $category_id ?>" id="category-<?php echo $category_id ?>" value="<?php echo $category ?>" placeholder="<?php _e('Category name', 'lucidlms') ?>" disabled="disabled">

            <div class="btn-group category-controls" role="group">
                <button type="button" class="btn btn-primary edit-category"><?php _e('Edit', 'lucidlms') ?></button>
                <button type="button" class="btn btn-default delete-category"><?php _e('Delete', 'lucidlms') ?></button>
                <button type="button" class="btn btn-success save-category"><?php _e('Save', 'lucidlms') ?></button>
            </div>

            <div class="cf"></div>
        </li>
    <?php endforeach; ?>
</ul>

