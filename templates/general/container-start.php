<?php
/**
 * Content containers
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$template = get_option( 'template' );

switch ( $template ) {
	case 'twentyeleven' :
		echo '<div id="primary"><div id="content" role="main" class="lucidlms">';
		break;
	case 'twentytwelve' :
		echo '<div id="primary" class="site-content"><div id="content" role="main" class="lucidlms">';
		break;
	case 'twentythirteen' :
		echo '<div id="primary" class="site-content"><div id="content" role="main" class="entry-content lucidlms">';
		break;
	case 'twentyfourteen' :
		echo '<div id="primary" class="content-area"><div id="content" role="main" class="site-content lucidlms">';
		break;
	case 'twentyfifteen' :
		echo '<div id="primary" class="content-area"><main id="main" role="main" class="site-main lucidlms">';
		break;
	case 'twentysixteen' :
		echo '<div id="primary" class="content-area"><main id="main" role="main" class="site-main lucidlms">';
		break;
	case 'twentyseventeen' :
		echo '<div class="wrap"><div id="primary" class="content-area"><main id="main" role="main" class="site-main lucidlms">';
		break;
	default :
		echo '<div id="container"><div id="content" role="main" class="site-main lucidlms">';
		break;
}