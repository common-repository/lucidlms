<?php
/**
 * LucidLMS Admin Settings Class.
 *
 * @author 		New Normal
 * @category 	Admin
 * @package 	LucidLMS/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'LU_Admin_Settings' ) ) :

	/**
	 * LU_Admin_Settings
	 */
	class LU_Admin_Settings {

		private static $settings = array();
		private static $errors   = array();
		private static $messages = array();

		/**
		 * Include the settings page classes
		 */
		public static function get_settings_pages() {
			if ( empty( self::$settings ) ) {
				$settings = array();

				include_once( 'settings/class-lucid-settings-page.php' );

				$settings[] = include( 'settings/class-lucid-settings-general.php' );
				$settings[] = include( 'settings/class-lucid-settings-woocommerce-integration.php' );
				$settings[] = include( 'settings/class-lucid-settings-emails.php' );
				$settings[] = include( 'settings/class-lucid-settings-bbpress-integration.php' );

				self::$settings = apply_filters( 'lucidlms_get_settings_pages', $settings );
			}
			return self::$settings;
		}

		/**
		 * Save the settings
		 */
		public static function save() {
			global $current_section, $current_tab;

			if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'lucidlms-settings' ) )
				die( __( 'Action failed. Please refresh the page and retry.', 'lucidlms' ) );

			// Trigger actions
			do_action( 'lucidlms_settings_save_' . $current_tab );
			do_action( 'lucidlms_update_options' );

			self::add_message( __( 'Your settings have been saved.', 'lucidlms' ) );

			// TODO: decide whether we need this
			// Re-add endpoints and flush rules
//			LU()->query->init_query_vars();
//			LU()->query->add_endpoints();
//			flush_rewrite_rules();

			do_action( 'lucidlms_settings_saved' );
		}

		/**
		 * Add a message
		 * @param string $text
		 */
		public static function add_message( $text ) {
			self::$messages[] = $text;
		}

		/**
		 * Add an error
		 * @param string $text
		 */
		public static function add_error( $text ) {
			self::$errors[] = $text;
		}

		/**
		 * Output messages + errors
		 */
		public static function show_messages() {
			if ( sizeof( self::$errors ) > 0 ) {
				foreach ( self::$errors as $error )
					echo '<div id="message" class="error fade"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
			} elseif ( sizeof( self::$messages ) > 0 ) {
				foreach ( self::$messages as $message )
					echo '<div id="message" class="updated fade"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
			}
		}

		/**
		 * Settings page.
		 *
		 * Handles the display of the main lucidlms settings page in admin.
		 *
		 * @access public
		 * @return void
		 */
		public static function output() {
			global $current_section, $current_tab;

			do_action( 'lucidlms_settings_start' );

			// Include settings pages
			self::get_settings_pages();

			// Get current tab/section
			$current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] );
			$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );

			// Save settings if data has been posted
			if ( ! empty( $_POST ) )
				self::save();

			// Add any posted messages
			if ( ! empty( $_GET['lucid_error'] ) )
				self::add_error( stripslashes( $_GET['lucid_error'] ) );

			if ( ! empty( $_GET['lucid_message'] ) )
				self::add_message( stripslashes( $_GET['lucid_message'] ) );

			self::show_messages();

			// Get tabs for the settings page
			$tabs = apply_filters( 'lucidlms_settings_tabs_array', array() );

			include 'views/html-admin-settings.php';
		}

		/**
		 * 
		 * Get a setting from the settings API.
		 * @param $option_name
		 * @param string $default
		 * 
		 * @return array|mixed|null|string|void
		 */
		public static function get_option( $option_name, $default = '' ) {
			// Array value
			if ( strstr( $option_name, '[' ) ) {

				parse_str( $option_name, $option_array );

				// Option name is first key
				$option_name = current( array_keys( $option_array ) );

				// Get value
				$option_values = get_option( $option_name, '' );

				$key = key( $option_array[ $option_name ] );

				if ( isset( $option_values[ $key ] ) )
					$option_value = $option_values[ $key ];
				else
					$option_value = null;

				// Single value
			} else {
				$option_value = get_option( $option_name, null );
			}

			if ( is_array( $option_value ) )
				$option_value = array_map( 'stripslashes', $option_value );
			elseif ( ! is_null( $option_value ) )
				$option_value = stripslashes( $option_value );

			return $option_value === null ? $default : $option_value;
		}

		/**
		 * Output admin fields.
		 *
		 * Loops though the lucidlms options array and outputs each field.
		 *
		 * @access public
		 * @param array $options Opens array to output
		 */
		public static function output_fields( $options ) {
			foreach ( $options as $value ) {
				if ( ! isset( $value['type'] ) ) continue;
				if ( ! isset( $value['id'] ) ) $value['id'] = '';
				if ( ! isset( $value['title'] ) ) $value['title'] = isset( $value['name'] ) ? $value['name'] : '';
				if ( ! isset( $value['class'] ) ) $value['class'] = '';
				if ( ! isset( $value['css'] ) ) $value['css'] = '';
				if ( ! isset( $value['default'] ) ) $value['default'] = '';
				if ( ! isset( $value['desc'] ) ) $value['desc'] = '';
				if ( ! isset( $value['desc_tip'] ) ) $value['desc_tip'] = false;
				if ( ! isset( $value['single'] ) ) $value['single'] = false;

				// Custom attribute handling
				$custom_attributes = array();

				if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) )
					foreach ( $value['custom_attributes'] as $attribute => $attribute_value )
						$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';

				// Description handling
				if ( $value['desc_tip'] === true ) {
					$description = '';
					$tip = $value['desc'];
				} elseif ( ! empty( $value['desc_tip'] ) ) {
					$description = $value['desc'];
					$tip = $value['desc_tip'];
				} elseif ( ! empty( $value['desc'] ) ) {
					$description = $value['desc'];
					$tip = '';
				} else {
					$description = $tip = '';
				}

				if ( $description && in_array( $value['type'], array( 'textarea', 'radio' ) ) ) {
					$description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';
				} elseif ( $description && in_array( $value['type'], array( 'checkbox' ) ) ) {
					$description =  wp_kses_post( $description );
				} elseif ( $description ) {
					$description = '<span class="description">' . wp_kses_post( $description ) . '</span>';
				}

				if ( $tip && !$value['single'] && in_array( $value['type'], array( 'checkbox' ) ) ) {

					$tip = '<p class="description">' . $tip . '</p>';

				} elseif ( $tip ) {

					$tip = '<img class="help_tip" title="' . esc_attr( $tip ) . '" src="' . LU()->plugin_url() . '/assets/images/help.png" height="16" width="16" />';

				}

				// Switch based on type
				switch( $value['type'] ) {

					// Section Titles
					case 'title':
						if ( ! empty( $value['title'] ) ) {
							echo '<h3>' . esc_html( $value['title'] ) . '</h3>';
						}
						if ( ! empty( $value['desc'] ) ) {
							echo wpautop( wptexturize( wp_kses_post( $value['desc'] ) ) );
						}
						echo '<table class="form-table">'. "\n\n";
						if ( ! empty( $value['id'] ) ) {
							do_action( 'lucidlms_settings_' . sanitize_title( $value['id'] ) );
						}
						break;

					// Section Ends
					case 'sectionend':
						if ( ! empty( $value['id'] ) ) {
							do_action( 'lucidlms_settings_' . sanitize_title( $value['id'] ) . '_end' );
						}
						echo '</table>';
						if ( ! empty( $value['id'] ) ) {
							do_action( 'lucidlms_settings_' . sanitize_title( $value['id'] ) . '_after' );
						}
						break;

					// Standard text inputs and subtypes like 'number'
					case 'text':
					case 'email':
					case 'number':
					case 'password' :

						$type 			= $value['type'];
						$class 			= '';
						$option_value 	= self::get_option( $value['id'], $value['default'] );

						?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tip; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="<?php echo esc_attr( $type ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								value="<?php echo esc_attr( $option_value ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								/> <?php echo $description; ?>
						</td>
						</tr><?php
						break;

					// Textarea
					case 'textarea':

						$option_value 	= self::get_option( $value['id'], $value['default'] );

						?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tip; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<?php echo $description; ?>

							<textarea
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								><?php echo esc_textarea( $option_value );  ?></textarea>
						</td>
						</tr><?php
						break;

					// Select boxes
					case 'select' :
					case 'multiselect' :

						$option_value 	= self::get_option( $value['id'], $value['default'] );

						?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tip; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<select
								name="<?php echo esc_attr( $value['id'] ); ?><?php if ( $value['type'] == 'multiselect' ) echo '[]'; ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								<?php if ( $value['type'] == 'multiselect' ) echo 'multiple="multiple"'; ?>
								>
								<?php
								foreach ( $value['options'] as $key => $val ) {
									?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php

									if ( is_array( $option_value ) )
										selected( in_array( $key, $option_value ), true );
									else
										selected( $option_value, $key );

									?>><?php echo $val ?></option>
								<?php
								}
								?>
							</select> <?php echo $description; ?>
						</td>
						</tr><?php
						break;

					// Radio inputs
					case 'radio' :

						$option_value 	= self::get_option( $value['id'], $value['default'] );

						?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tip; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<fieldset>
								<?php echo $description; ?>
								<ul>
									<?php
									foreach ( $value['options'] as $key => $val ) {
										?>
										<li>
											<label><input
													name="<?php echo esc_attr( $value['id'] ); ?>"
													value="<?php echo $key; ?>"
													type="radio"
													style="<?php echo esc_attr( $value['css'] ); ?>"
													class="<?php echo esc_attr( $value['class'] ); ?>"
													<?php echo implode( ' ', $custom_attributes ); ?>
													<?php checked( $key, $option_value ); ?>
													/> <?php echo $val ?></label>
										</li>
									<?php
									}
									?>
								</ul>
							</fieldset>
						</td>
						</tr><?php
						break;

					// Checkbox input
					case 'checkbox' :

						$option_value    = self::get_option( $value['id'], $value['default'] );
						$visbility_class = array();

						if ( ! isset( $value['hide_if_checked'] ) ) {
							$value['hide_if_checked'] = false;
						}
						if ( ! isset( $value['show_if_checked'] ) ) {
							$value['show_if_checked'] = false;
						}
						if ( $value['hide_if_checked'] == 'yes' || $value['show_if_checked'] == 'yes' ) {
							$visbility_class[] = 'hidden_option';
						}
						if ( $value['hide_if_checked'] == 'option' ) {
							$visbility_class[] = 'hide_options_if_checked';
						}
						if ( $value['show_if_checked'] == 'option' ) {
							$visbility_class[] = 'show_options_if_checked';
						}

						if ( $value['single'] ) { ?>
							<tr valign="top" class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
							<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?> <?php echo $tip; ?></th>
							<td class="forminp forminp-checkbox-single">
							<fieldset>
						<?php
						} elseif ( ! isset( $value['checkboxgroup'] ) || 'start' == $value['checkboxgroup'] ) {
							?>
							<tr valign="top" class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
							<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?></th>
							<td class="forminp forminp-checkbox">
							<fieldset>
						<?php
						} else {
							?>
							<fieldset class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
						<?php
						}

						if ( ! empty( $value['title'] ) ) {
							?>
							<legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ) ?></span></legend>
						<?php
						}
						?>
						<label for="<?php echo $value['id'] ?>">
							<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="checkbox"
								value="1"
								<?php checked( $option_value, 'yes'); ?>
								<?php echo implode( ' ', $custom_attributes ); ?>
								/> <?php echo $value['single'] ? '' : $description; ?>
						</label> <?php echo $value['single'] ? '' : $tip; ?>

						<?php
						if ( ! isset( $value['checkboxgroup'] ) || 'end' == $value['checkboxgroup'] ) {
							?>
							</fieldset>
							</td>
							</tr>
						<?php
						} else {
							?>
							</fieldset>
						<?php
						}
						break;

					// Single page selects
					case 'single_select_page' :

						$args = array( 'name'				=> $value['id'],
						               'id'					=> $value['id'],
						               'sort_column' 		=> 'menu_order',
						               'sort_order'			=> 'ASC',
						               'show_option_none' 	=> ' ',
						               'class'				=> $value['class'],
						               'echo' 				=> false,
						               'selected'			=> absint( self::get_option( $value['id'] ) )
						);

						if( isset( $value['args'] ) )
							$args = wp_parse_args( $value['args'], $args );

						?><tr valign="top" class="single_select_page">
						<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?> <?php echo $tip; ?></th>
						<td class="forminp">
							<?php echo str_replace(' id=', " data-placeholder='" . __( 'Select a page&hellip;', 'lucidlms' ) .  "' style='" . $value['css'] . "' class='" . $value['class'] . "' id=", wp_dropdown_pages( $args ) ); ?> <?php echo $description; ?>
						</td>
						</tr><?php
						break;

					// File input
					case 'file' :

						$option_value 	= self::get_option( $value['id'], $value['default'] );

						// See if there's an existing image.
					    // Incidentally, this is also how you'd find any uploaded files for display on the frontend.
					    if(is_numeric($option_value)) {
						    $arr_existing_image = wp_get_attachment_image_src($option_value, 'thumbnail');
						    $existing_image_url = $arr_existing_image[0];
						    $existing_image = '<img src="' . $existing_image_url . '" />';
					    } else {
						    $existing_image = '';
					    }

						?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tip; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<?php echo $existing_image; ?>
							<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="<?php echo esc_attr( $value['type'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								/> <?php echo $description; ?>
						</td>
						</tr><?php
						break;

                    case 'link':
                        // Check for description
                        if ( !empty($value['desc']) ) {
                            $tip = '<img class="help_tip" title="' . esc_attr( $value['desc'] ) . '" src="' . LU()->plugin_url() . '/assets/images/help.png" height="16" width="16" />';
                        } else {
                            $tip = '';
                        }
                        ?>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                                <?php echo $tip; ?>
                            </th>
                            <td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
                                <a
                                    href="<?php echo add_query_arg('forums', $value['action']); ?>"
                                    id="<?php echo esc_attr( $value['id'] ); ?>"
                                    class="button <?php echo esc_attr( $value['class'] ); ?>"
                                    style="<?php echo esc_attr( $value['css'] ); ?>"
                                    ><?php echo esc_attr( $value['text'] ); ?></a>
                            </td>
                        </tr>
                        <?php
                        break;
					// Default: run an action
					default:
						do_action( 'lucidlms_admin_field_' . $value['type'], $value );
						break;

					case 'submit':

						$type 			= $value['type'];
						$class          = ( ! empty( $value['class'] ) ? $value['class'] : 'button-secondary' );
						$option_value   = isset( $value['value'] ) ? $value['value'] : self::get_option( $value['id'], $value['default'] );

						?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tip; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="<?php echo esc_attr( $type ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								value="<?php echo esc_attr( $option_value ); ?>"
								class="<?php echo esc_attr( $class ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								/> <?php echo $description; ?>
						</td>
						</tr><?php
						break;
				}
			}
		}

		/**
		 * Save admin fields.
		 *
		 * Loops though the lucidlms options array and outputs each field.
		 *
		 * @access public
		 * @param array $options Opens array to output
		 * @return bool
		 */
		public static function save_fields( $options ) {
			if ( empty( $_POST ) )
				return false;

			// Options to update will be stored here
			$update_options = array();

			// Loop options and get values to save
			foreach ( $options as $value ) {

				if ( ! isset( $value['id'] ) )
					continue;

				$type = isset( $value['type'] ) ? sanitize_title( $value['type'] ) : '';

				// Get the option name
				$option_value = null;

				switch ( $type ) {

					// Standard types
					case "checkbox" :

						if ( isset( $_POST[ $value['id'] ] ) ) {
							$option_value = 'yes';
						} else {
							$option_value = 'no';
						}

						break;

					case "textarea" :

						if ( isset( $_POST[$value['id']] ) ) {
							$option_value = wp_kses_post( trim( stripslashes( $_POST[ $value['id'] ] ) ) );
						} else {
							$option_value = '';
						}

						break;

					case "text" :
					case 'email':
					case 'number':
					case "select" :
					case 'password' :
					case "single_select_page" :
					case 'radio' :
					case 'submit' :

						if ( isset( $_POST[$value['id']] ) ) {
							$option_value = sanitize_text_field( stripslashes( $_POST[ $value['id'] ] ) );
						} else {
							$option_value = '';
						}

						break;

					// Special types
					case "multiselect" :

						// Get values array
						if ( isset( $_POST[ $value['id'] ] ) )
							$selected_values = array_map( 'sanitize_text_field', array_map( 'stripslashes', (array) $_POST[ $value['id'] ] ) );
						else
							$selected_values = array();

						$option_value = $selected_values;

						break;

					// File type
					case 'file' :

						$file = $_FILES[$value['id']];
						$upload = true;
						$uploaded = false;

						if ( isset($file) && ($file['size'] > 0) ) {

							// Get the type of the uploaded file. This is returned as "type/extension"
							$arr_file_type = wp_check_filetype(basename($file['name']));
							$uploaded_file_type = $arr_file_type['type'];

							if ( $value['id'] == 'lucidlms_organization_logo' ) {

								// Set an array containing a list of acceptable formats
								$allowed_file_types = array('image/jpg','image/jpeg','image/gif','image/png');

								// If the uploaded file is the right format
								if ( ! in_array( $uploaded_file_type, $allowed_file_types ) ) {
									$upload = false;
								}
							}

							if ($upload) {

								// Options array for the wp_handle_upload function. 'test_upload' => false
								$upload_overrides = array( 'test_form' => false );

								// Handle the upload using WP's wp_handle_upload function. Takes the posted file and an options array
								$uploaded_file = wp_handle_upload($file, $upload_overrides);

								// If the wp_handle_upload call returned a local path for the image
								if(isset($uploaded_file['file'])) {

									// The wp_insert_attachment function needs the literal system path, which was passed back from wp_handle_upload
									$file_name_and_location = $uploaded_file['file'];

									// Generate a title for the image that'll be used in the media library
									$file_title_for_media_library = 'Organization Logo';

									// Set up options array to add this file as an attachment
									$attachment = array(
										'post_mime_type' => $uploaded_file_type,
										'post_title' => 'Uploaded image ' . addslashes($file_title_for_media_library),
										'post_content' => '',
										'post_status' => 'inherit'
									);

									// Run the wp_insert_attachment function. This adds the file to the media library and generates the thumbnails.
									$attach_id = wp_insert_attachment( $attachment, $file_name_and_location );
									require_once(ABSPATH . "wp-admin" . '/includes/image.php');
									$attach_data = wp_generate_attachment_metadata( $attach_id, $file_name_and_location );
									wp_update_attachment_metadata($attach_id,  $attach_data);

									// Before we update the post meta, trash any previously uploaded image for this post.
									// You might not want this behavior, depending on how you're using the uploaded images.
									$old_option_value 	= self::get_option( $value['id'] );
									if(is_numeric($old_option_value)) {
										wp_delete_attachment($old_option_value);
									}

									$option_value = $attach_id;
									$uploaded = true;

								}
							}
						}

						if ( ! $uploaded ) {
							$option_value = null;
						}

						break;

					// Custom handling
					default :

						do_action( 'lucidlms_update_option_' . $type, $value );

						break;

				}

				if ( ! is_null( $option_value ) ) {
					// Check if option is an array
					if ( strstr( $value['id'], '[' ) ) {

						parse_str( $value['id'], $option_array );

						// Option name is first key
						$option_name = current( array_keys( $option_array ) );

						// Get old option value
						if ( ! isset( $update_options[ $option_name ] ) )
							$update_options[ $option_name ] = get_option( $option_name, array() );

						if ( ! is_array( $update_options[ $option_name ] ) )
							$update_options[ $option_name ] = array();

						// Set keys and value
						$key = key( $option_array[ $option_name ] );

						$update_options[ $option_name ][ $key ] = $option_value;

						// Single value
					} else {
						$update_options[ $value['id'] ] = $option_value;
					}
				}

				// Custom handling
				do_action( 'lucidlms_update_option', $value );
			}

			// Now save the options
			foreach( $update_options as $name => $value )
				update_option( $name, $value );

			return true;
		}
	}

endif;
