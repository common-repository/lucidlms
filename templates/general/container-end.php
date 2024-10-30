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
		echo '</div></div>';
		break;
	case 'twentytwelve' :
		echo '</div></div>';
		break;
	case 'twentythirteen' :
		echo '</div></div>';
		break;
	case 'twentyfourteen' :
		echo '</div></div>';
		get_sidebar();
		break;
	case 'twentyfifteen' :
		echo '</main></div>';
		break;
	case 'twentysixteen' :
		echo '</main></div>';
		break;
	case 'twentyseventeen' :
		echo '</main></div>';
		get_sidebar('lucidlms');
		echo '</div>';
		break;
	default :
		echo '</div></div>';
		break;
}