<?php
/**
 * LucidLMS Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @author        New Normal
 * @category    Core
 * @package    LucidLMS/Functions
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

// Include core functions
include( 'lucid-conditional-functions.php' );
include( 'lucid-page-functions.php' );
include( 'lucid-course-functions.php' );
include( 'lucid-question-functions.php' );
include( 'lucid-score-card-functions.php' );
include( 'class-lucid-student-handler.php' );
include( 'lucid-woocommerce-functions.php' );
include( 'lucid-bbpress-functions.php' );

/**
 * Post excerpt
 */
add_filter( 'lucidlms_excerpt', 'wptexturize' );
add_filter( 'lucidlms_excerpt', 'convert_smilies' );
add_filter( 'lucidlms_excerpt', 'convert_chars' );
add_filter( 'lucidlms_excerpt', 'wpautop' );
add_filter( 'lucidlms_excerpt', 'shortcode_unautop' );
add_filter( 'lucidlms_excerpt', 'prepend_attachment' );
add_filter( 'lucidlms_excerpt', 'do_shortcode', 11 ); // AFTER wpautop()

/**
 * Get template part (for templates like the courses-loop).
 *
 * @access public
 *
 * @param mixed $slug
 * @param string $name (default: '')
 *
 * @return void
 */
function lucid_get_template_part( $slug, $name = '' ) {
	$template = '';

	// Look in yourtheme/slug-name.php and yourtheme/lucidlms/slug-name.php
	if ( $name ) {
		$template = locate_template( array( "{$slug}-{$name}.php", LU()->template_path() . "{$slug}-{$name}.php" ) );
	}

	// Get default slug-name.php
	if ( ! $template && $name && file_exists( LU()->plugin_path() . "/templates/{$slug}-{$name}.php" ) ) {
		$template = LU()->plugin_path() . "/templates/{$slug}-{$name}.php";
	}

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/lucidlms/slug.php
	if ( ! $template ) {
		$template = locate_template( array( "{$slug}.php", LU()->template_path() . "{$slug}.php" ) );
	}

	// Allow 3rd party plugin filter template file from their plugin
	$template = apply_filters( 'lucid_get_template_part', $template, $slug, $name );

	if ( $template ) {
		load_template( $template, false );
	}
}

/**
 * Get other templates passing attributes and including the file.
 *
 * @access public
 *
 * @param string $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 *
 * @return void
 */
function lucid_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( $args && is_array( $args ) ) {
		extract( $args );
	}

	$located = lucid_locate_template( $template_name, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), LU_VERSION );

		return;
	}

	do_action( 'lucidlms_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'lucidlms_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *        yourtheme        /    $template_path    /    $template_name
 *        yourtheme        /    $template_name
 *        $default_path    /    $template_name
 *
 * @access public
 *
 * @param string $template_name
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 *
 * @return string
 */
function lucid_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = LU()->template_path();
	}

	if ( ! $default_path ) {
		$default_path = LU()->plugin_path() . '/templates/';
	}

	// Look within passed path within the theme - this is priority
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name
		)
	);

	// Get default template
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}

	// Return what we found
	return apply_filters( 'lucidlms_locate_template', $template, $template_name, $template_path );
}

/**
 * Send HTML emails from LucidLMS
 *
 * @param mixed $to
 * @param mixed $subject
 * @param mixed $message
 * @param string $headers (default: "Content-Type: text/html\r\n")
 * @param string $attachments (default: "")
 */
function lucid_mail( $to, $subject, $message, $headers = "Content-Type: text/html\r\n", $attachments = "" ) {
	global $lucidlms;

	$mailer = LU()->mailer();

	$mailer->send( $to, $subject, $message, $headers, $attachments );
}

/**
 * Queue some JavaScript code to be output in the footer.
 *
 * @param string $code
 */
function lucid_enqueue_js( $code ) {
	global $lucid_queued_js;

	if ( empty( $lucid_queued_js ) ) {
		$lucid_queued_js = '';
	}

	$lucid_queued_js .= "\n" . $code . "\n";
}

/**
 * Output any queued javascript code in the footer.
 */
function lucid_print_js() {
	global $lucid_queued_js;

	if ( ! empty( $lucid_queued_js ) ) {

		echo "<!-- LucidLMS JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) {";

		// Sanitize
		$lucid_queued_js = wp_check_invalid_utf8( $lucid_queued_js );
		$lucid_queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $lucid_queued_js );
		$lucid_queued_js = str_replace( "\r", '', $lucid_queued_js );

		echo $lucid_queued_js . "});\n</script>\n";

		unset( $lucid_queued_js );
	}
}

/**
 * Returns the timezone string for a site, even if it's set to a UTC offset
 *
 * Adapted from http://www.php.net/manual/en/function.timezone-name-from-abbr.php#89155
 *
 * @return string valid PHP timezone string
 */
function lucidlms_get_timezone_string() {

	// if site timezone string exists, return it
	if ( $timezone = get_option( 'timezone_string' ) ) {
		return $timezone;
	}

	// get UTC offset, if it isn't set then return UTC
	if ( 0 === ( $utc_offset = get_option( 'gmt_offset', 1 ) ) ) {
		return 'UTC';
	}

	// adjust UTC offset from hours to seconds
	$utc_offset *= 3600;

	// attempt to guess the timezone string from the UTC offset
	$timezone = timezone_name_from_abbr( '', $utc_offset, 0 );

	// last try, guess timezone string manually
	if ( false === $timezone ) {
		$is_dst = date( 'I' );

		foreach ( timezone_abbreviations_list() as $abbr ) {
			foreach ( $abbr as $city ) {
				if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset ) {
					return $city['timezone_id'];
				}
			}
		}
	} else {
		return $timezone;
	}

	// fallback to UTC
	return 'UTC';
}

/**
 * Returns TimeZone object based on wp timezone settings offset
 * @return DateTimeZone
 */
function lucidlms_timezone() {
	return new DateTimeZone( lucidlms_get_timezone_string() );
}