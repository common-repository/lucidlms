<?php
/**
 * The Template for displaying course archives, including the main courses page which is a post type archive.
 *
 * Override this template by copying it to yourtheme/lucidlms/archive-course.php
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
do_action( 'lucidlms_before_main_content' ); ?>

		<?php if ( apply_filters( 'lucidlms_show_page_title', true ) ) : ?>

            <div class="entry-header">
                <h1 class="entry-title"><?php lucidlms_page_title(); ?></h1>
            </div>

		<?php endif; ?>

        <div class="lucidlms-info loading-categories"><?php _e( 'Loading categories...', 'lucidlms' ); ?></div>

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