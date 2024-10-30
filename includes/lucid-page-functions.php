<?php
/**
 * LucidLMS Page Functions
 *
 * Functions related to pages and menus.
 *
 * @author        New Normal
 * @category      Core
 * @package       LucidLMS/Functions
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Retrieve page ids - used for courses, studentprofile. returns -1 if no page is found
 *
 * @param string $page
 *
 * @return int
 */
function lucid_get_page_id( $page ) {

	$page = apply_filters( 'lucidlms_get_' . $page . '_page_id', get_option( 'lucidlms_' . $page . '_page_id' ) );

	return $page ? $page : - 1;
}