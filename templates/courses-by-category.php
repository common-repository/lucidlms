<?php
/**
 * The Template for displaying specific category courses.
 *
 * Override this template by copying it to yourtheme/lucidlms/courses-by-category.php
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * We use extracted variables in template
 *
 * @var $category_slug
 */

query_posts(array(
    'course_cat' => $category_slug,
    'order' => 'ASC',
	'post_status' => 'publish',
    'orderby' => 'name',
    'posts_per_page' => -1
) );

if ( have_posts() ) : ?>

    <?php
    /**
     * lucidlms_before_courses_loop hook
     */
    do_action( 'lucidlms_before_courses_loop' );
    ?>

    <?php while ( have_posts() ) : the_post(); ?>

        <?php lucid_get_template_part( 'content', 'course' ); ?>

    <?php endwhile; // end of the loop. ?>

    <?php
    /**
     * lucidlms_after_courses_loop hook
     *
     * @see lucidlms_pagination - 10
     */
    do_action( 'lucidlms_after_courses_loop' );
    ?>
<?php else : ?>

    <?php lucid_get_template( 'loop/no-courses.php' ); ?>

<?php endif;

wp_reset_query();