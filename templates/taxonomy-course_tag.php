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

lucid_get_template( 'taxonomy-course_cat.php' );
