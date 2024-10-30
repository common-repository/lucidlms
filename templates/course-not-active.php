<?php
/**
 * The Template for displaying all single courses.
 *
 * Override this template by copying it to yourtheme/lucidlms/single-course.php
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
 * @see lucidlms_output_content_container - 10
 */
do_action( 'lucidlms_before_main_content' );
?>

    <div class="entry-content">
        <p>
            <?php printf( __('This course is no longer available. Please choose other course <a href="%s">here</a>', 'lucidlms'), get_permalink( lucid_get_page_id( 'courses' ) ) ) ?>
        </p>
    </div>
<?php
/**
 * lucidlms_after_main_content hook
 *
 * @see lucidlms_output_content_container_end - 10
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