<?php
/**
 * The Template for displaying courses in a course category. Simply includes the archive template.
 *
 * Override this template by copying it to yourtheme/lucidlms/taxonomy-course_cat.php
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


get_header( 'lucidlms' ); ?>

<?php
/**
 * lucidlms_before_main_content hook
 *
 * @see lucidlms_output_content_wrapper - 10 (outputs opening divs for the content)
 */
do_action( 'lucidlms_before_main_content' );
?>

<?php if ( apply_filters( 'lucidlms_show_page_title', true ) ) : ?>

    <div class="entry-header">
        <h1 class="entry-title"><?php lucidlms_page_title(); ?></h1>
    </div>

<?php endif; ?>

<?php if ( have_posts() ) : ?>

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

<?php endif; ?>

<?php
/**
 * lucidlms_after_main_content hook
 *
 * @see lucidlms_output_content_container_end - 10 (outputs closing divs for the content)
 */
do_action( 'lucidlms_after_main_content' );
?>

<?php
/**
 * lucidlms_sidebar hook
 *
 * @see lucidlms_get_sidebar - 10
 */
do_action( 'lucidlms_sidebar' );
?>

<?php get_footer( 'lucidlms' ); ?>