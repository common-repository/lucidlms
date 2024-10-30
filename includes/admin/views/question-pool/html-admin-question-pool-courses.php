<li class="label selected all"><?php _e('All', 'lucidlms') ?></li>

<?php foreach( $courses as $course): ?>

    <li class="label" data-id="<?php echo esc_attr($course->id) ?>"><?php echo $course->title ?></li>

<?php endforeach; ?>