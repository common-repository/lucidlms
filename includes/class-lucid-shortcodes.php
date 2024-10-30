<?php

/**
 * LU_Shortcodes class.
 *
 * @class          LU_Shortcodes
 * @version        1.0.0
 * @package        LucidLMS/Classes
 * @category       Class
 * @author         New Normal
 */
class LU_Shortcodes {

	/**
	 * Init shortcodes
	 */
	public static function init() {
		// Define shortcodes
		$shortcodes = array(
			'lucidlms_student_profile' => __CLASS__ . '::student_profile'
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}

	}

	/**
	 * Shortcode Wrapper
	 *
	 * @access public
	 *
	 * @param       $function
	 * @param array $atts
	 * @param array $wrapper
	 *
	 * @return string
	 */
	public static function shortcode_wrapper(
		$function,
		$atts = array(),
		$wrapper = array(
			'class'  => 'lucidlms',
			'before' => null,
			'after'  => null
		)
	) {
		ob_start();

		$before = empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
		$after  = empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];

		echo $before;
		call_user_func( $function, $atts );
		echo $after;

		return ob_get_clean();
	}

	/**
	 * Student profile shortcode.
	 *
	 * @access public
	 *
	 * @param mixed $atts
	 *
	 * @return string
	 */
	public static function student_profile( $atts ) {
		return self::shortcode_wrapper( array( 'LU_Shortcode_Student_Profile', 'output' ), $atts );
	}

}
