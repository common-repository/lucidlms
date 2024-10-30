<li class="label selected all"><?php _e('All', 'lucidlms') ?></li>

<?php foreach( $categories as $category_id => $category): ?>

    <li class="label" data-id="<?php echo esc_attr($category_id) ?>">
        <?php echo $category ?>
    </li>

<?php endforeach; ?>
