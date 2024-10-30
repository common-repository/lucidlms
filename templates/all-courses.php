<?php
/**
 * The Template for displaying all courses.
 *
 * Override this template by copying it to yourtheme/lucidlms/all-courses.php
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$categories = get_terms( array( 'taxonomy' => 'course_cat' ) ); ?>

<?php
/**
 * lucidlms_before_course_categories_loop hook
 */
do_action( 'lucidlms_before_course_categories_loop' ); ?>

<?php if ( ! empty( $categories ) ): ?>

	<?php foreach ( $categories as $category ): ?>

		<?php do_action( 'lucidlms_before_course_categories_loop_item' ); ?>

        <div class="course-category">

            <div class="entry-header expand" data-category-slug="<?php echo $category->slug; ?>">
				<?php $total = wp_count_terms( 'course_cat', 'hide_empty=1' ); ?>

				<?php if ( $total > 1 ) : ?>
                    <i class="arrow-icon fa fa-chevron-down"></i>
                    <h2 class="entry-title "><?php echo $category->name ?></h2>
				<?php endif; ?>
            </div>

            <div class="collapse">
                <p class="lucidlms-info"><?php _e( 'Loading courses...', 'lucidlms' ); ?></p>
            </div>
        </div>
		<?php do_action( 'lucidlms_after_course_categories_loop_item' ); ?>

	<?php endforeach; ?>

<?php else: ?>

	<?php lucid_get_template( 'loop/no-courses.php' ); ?>

<?php endif; ?>

<?php
/**
 * lucidlms_after_course_categories_loop hook
 */
do_action( 'lucidlms_after_course_categories_loop' ); ?>