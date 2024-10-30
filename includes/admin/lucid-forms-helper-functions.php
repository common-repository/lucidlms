<?php
/**
 * LucidLMS Meta Box Functions
 *
 * @author      New Normal
 * @category    Core
 * @package     LucidLMS/Admin/Functions
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Output a text input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function lucidlms_wp_text_input( $field ) {
	global $thepostid, $post, $lucidlms;

    $thepostid              = empty( $thepostid ) && isset($post->ID) ? $post->ID : $thepostid;
	$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$post_meta_value        = !empty($thepostid) ? get_post_meta( $thepostid, $field['id'], true ) : false;
	$field['value']         = isset( $field['value'] ) ? $field['value'] : ( $post_meta_value ? $post_meta_value : ( isset( $field['default'] ) ? $field['default'] : '' ) );
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
	$field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';

	// Custom attribute handling
	$custom_attributes = array();

	if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) )
		foreach ( $field['custom_attributes'] as $attribute => $value )
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';

	echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><input type="' . esc_attr( $field['type'] ) . '" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . implode( ' ', $custom_attributes ) . ' /> ';

	if ( ! empty( $field['description'] ) ) {

		if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
			echo '<img class="help_tip" title="' . esc_attr( $field['description'] ) . '" src="' . esc_url( LU()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
		} else {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		}

	}

	if ( ! empty($field['extra-description'] ) ) {
		echo '<span class="extra-description">' . wp_kses_post( $field['extra-description'] ) . '</span>';
	}

	echo '</p>';
}

/**
 * Output a hidden input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function lucidlms_wp_hidden_input( $field ) {

	$field['value'] = isset( $field['value'] ) ? $field['value'] : '';
	$field['class'] = isset( $field['class'] ) ? $field['class'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';

	echo '<input type="hidden" class="' . esc_attr( $field['class'] ) . esc_attr( $field['wrapper_class'] ) .'" name="' . esc_attr( $field['id'] ) .'" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) .  '" /> ';
}

/**
 * Output a textarea input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function lucidlms_wp_textarea_input( $field ) {

	$field['placeholder'] 	= isset( $field['placeholder'] ) ? $field['placeholder'] : '';
	$field['class'] 		= isset( $field['class'] ) ? $field['class'] : 'short';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value'] 		= isset( $field['value'] ) ? $field['value'] : '';

	echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><textarea class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" rows="2" cols="20">' . esc_textarea( $field['value'] ) . '</textarea> ';

	if ( ! empty( $field['description'] ) ) {

		if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
			echo '<img class="help_tip" title="' . esc_attr( $field['description'] ) . '" src="' . esc_url( LU()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
		} else {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		}

	}

	if ( ! empty($field['extra-description'] ) ) {
		echo '<span class="extra-description">' . wp_kses_post( $field['extra-description'] ) . '</span>';
	}

	echo '</p>';
}

/**
 * Output a checkbox input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function lucidlms_wp_checkbox( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) && isset($post->ID) ? $post->ID : $thepostid;
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'checkbox';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['cbvalue']       = isset( $field['cbvalue'] ) ? $field['cbvalue'] : 'yes';
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

    if( !isset($field['value']) ){
        $field['value'] = !empty($thepostid) ? get_post_meta( $thepostid, $field['id'], true ) : '';
    }

	echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><input type="checkbox" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['cbvalue'] ) . '" ' . checked( $field['value'], $field['cbvalue'], false ) . ' /> ';

	if ( ! empty( $field['description'] ) ) echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';

	echo '</p>';
}

/**
 * Output a select input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function lucidlms_wp_select( $field ) {
	global $thepostid, $post, $lucidlms;

	$thepostid 				= empty( $thepostid ) && isset($post->ID) ? $post->ID : $thepostid;
	$field['class'] 		= isset( $field['class'] ) ? $field['class'] : 'select short';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
//	$field['value'] 		= isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
    if( !isset($field['value']) ){
        $field['value'] = !empty($thepostid) ? get_post_meta( $thepostid, $field['id'], true ) : '';
    }

	// Custom attribute handling
	$custom_attributes = array();

    if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) )
		foreach ( $field['custom_attributes'] as $attribute => $value )
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';

	echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">' . ( isset($field['label']) ? '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>' : '' ) . '<select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['id'] ) . '" class="' . esc_attr( $field['class'] ) . '" ' . implode( ' ', $custom_attributes ) . '>';
	foreach ( $field['options'] as $key => $value ) {

		echo '<option value="' . esc_attr( $key ) . '" ' . selected( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';

	}

	echo '</select> ';

	if ( ! empty( $field['description'] ) ) {

		if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
			echo '<img class="help_tip" title="' . esc_attr( $field['description'] ) . '" src="' . esc_url( LU()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
		} else {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		}

	}

	if ( ! empty($field['extra-description'] ) ) {
		echo '<span class="extra-description">' . wp_kses_post( $field['extra-description'] ) . '</span>';
	}

	echo '</p>';
}

/**
 * Output a radio input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function lucidlms_wp_radio( $field ) {
	global $thepostid, $post, $lucidlms;

	$thepostid              = empty( $thepostid ) && isset($post->ID) ? $post->ID : $thepostid;
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'select short';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$post_meta_value        = !empty($thepostid) ? get_post_meta( $thepostid, $field['id'], true ) : false;
	$field['value']         = isset( $field['value'] ) ? $field['value'] : ( $post_meta_value ? $post_meta_value : ( isset( $field['default'] ) ? $field['default'] : '' ) );
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

	echo '<fieldset class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><legend>' . wp_kses_post( $field['label'] ) . '</legend>';

	if ( ! empty( $field['sub-heading'] ) ) echo '<span class="sub-heading">' . wp_kses_post( $field['sub-heading'] ) . '</span>';

	echo '<ul class="lucid-radios ' . $field['class'] . '">';

	foreach ( $field['options'] as $key => $value ) {

		if ( is_array( $value ) ) {
			echo '<li class="row-tip"><label><input
        		name="' . esc_attr( $field['name'] ) . '"
        		value="' . esc_attr( $key ) . '"
        		type="radio"
        		class="' . esc_attr( $field['class'] ) . '"
        		' . checked( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '
        		/> ' . esc_html( $value[0] ) . '
                <img class="help_tip" title="' . esc_attr( $value[1] ) . '" src="' . esc_url( LU()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />
        		</label>

    	</li>';

		} else {
			echo '<li><label><input
        		name="' . esc_attr( $field['name'] ) . '"
        		value="' . esc_attr( $key ) . '"
        		type="radio"
        		class="' . esc_attr( $field['class'] ) . '"
        		' . checked( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '
        		/> ' . esc_html( $value ) . '</label>
    	</li>';
		}

	}
	echo '</ul>';

	if ( ! empty( $field['description'] ) ) {

		if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
			echo '<img class="help_tip" title="' . esc_attr( $field['description'] ) . '" src="' . esc_url( LU()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
		} else {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		}

	}

	if ( ! empty($field['extra-description'] ) ) {
		echo '<span class="extra-description">' . wp_kses_post( $field['extra-description'] ) . '</span>';
	}

	echo '</fieldset>';
}

/**
 * Output a bootstrap datetime picker input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function lucidlms_wp_datetimepicker( $field ) {
	global $thepostid, $post, $lucidlms;

	$thepostid              = empty( $thepostid ) && isset($post->ID) ? $post->ID : $thepostid;
	$field['class']         = isset( $field['class'] ) ? $field['class'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$post_meta_value        = !empty($thepostid) ? get_post_meta( $thepostid, $field['id'], true ) : false;
	$field['value']         = isset( $field['value'] ) ? $field['value'] : ( $post_meta_value ? $post_meta_value : ( isset( $field['default'] ) ? $field['default'] : '' ) );
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

	// Custom attribute handling
	$custom_attributes = array();

	if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) )
		foreach ( $field['custom_attributes'] as $attribute => $value )
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';

	echo '<p class="form-field input-group date ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';
	echo '<input type="text" class="form-control ' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" ' . implode( ' ', $custom_attributes ) . ' /> ';
	echo '<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>';
	echo '</p>';

}

/**
 * Output pdf upload field
 *
 * @access public
 * @param array $field
 * @return void
 */
function lucidlms_wp_image_upload( $field ) {
	global $thepostid, $post, $lucidlms;

	$thepostid              = empty( $thepostid ) && isset($post->ID) ? $post->ID : $thepostid;
	$field['class']         = isset( $field['class'] ) ? $field['class'] : '';
	$field['size']         = isset( $field['size'] ) ? $field['size'] : 25;
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = isset( $field['value'] ) && ! empty( $field['value'] ) ? '<span class="description">' . __( 'You have already uploaded a file. ', 'lucidlms' ) . '<a href="' . $field['value']['url'] . '" target="_blank">' . __( 'Click to see it.' ) . '</a></span>' : '' ;
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

	// Custom attribute handling
	$custom_attributes = array();

	if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) )
		foreach ( $field['custom_attributes'] as $attribute => $value )
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';

	echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>' . $field['value'] . '<input type="file" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="" size="' . esc_attr( $field['size'] ) . '" ' . implode( ' ', $custom_attributes ) . ' /> ';

	if ( ! empty( $field['description'] ) ) {

		if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
			echo '<img class="help_tip" title="' . esc_attr( $field['description'] ) . '" src="' . esc_url( LU()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
		} else {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		}

	}

	if ( ! empty($field['extra-description'] ) ) {
		echo '<span class="extra-description">' . wp_kses_post( $field['extra-description'] ) . '</span>';
	}

	echo '</p>';
}