<?php
if ( ( 'yes' == get_option( 'lucidlms_woocommerce_integration_enabled' ) ) && in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ):

	/**
	 * @param $post_id
	 * @param WP_Post $post Post object.
	 */
	function lucidlms_save_post_course( $post_id, $post ) {

		// Dont' save meta boxes for revisions or autosaves
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}
		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}
//         Check the nonce
		if ( empty( $_POST['lucidlms_meta_nonce'] ) || ! wp_verify_nonce( $_POST['lucidlms_meta_nonce'], 'lucidlms_save_data' ) ) {
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
		}

		// Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

//         update product if exists
		if ( ( $course = get_course( $post ) ) && $course->get_woo_product_id() ) {
			lucidlms_create_update_connected_product( $course );
		}

		remove_action( 'save_post', 'lucidlms_save_post_course', 100, 2 );
	}

	add_action( 'save_post', 'lucidlms_save_post_course', 100, 2 );


	function lucidlms_delete_course_post( $post_id ) {

		if ( $post = get_post( $post_id ) ) {
			if ( ( 'course' == $post->post_type ) && ( $course = get_course( $post ) ) ) {
				lucidlms_remove_connected_product( $course );
			}
		}
	}

	add_action( 'before_delete_post', 'lucidlms_delete_course_post', 10, 1 );
	add_action( 'wp_trash_post', 'lucidlms_delete_course_post', 10, 1 );

	function lucidlms_order_paid( $order_id ) {
		if ( $order = new WC_Order( $order_id ) ) {
			$customer_id = $order->customer_user;
			if ( ! empty( $customer_id ) && ( $order_items = $order->get_items() ) ) { // don't worry if it is a guest checkout

				foreach ( $order_items as $item ) {
					$product_id = $item['product_id'];

					if ( is_course_product( $product_id ) ) {

						// 1. check if not score card exists
						// 2. create new score card for %user_id% and %course_id%
						// 3. set score card status 'started' or ???
						// 4. save related order ID to the score card
						// 5. ???
						// 6. profit!!!


						if ( $course_id = find_course_by_product( $product_id ) ) {

							$score_card = lucidlms_get_current_score_card( $customer_id, $course_id );

							if ( ! $score_card ) {
								$score_card = new LU_Score_Card();

								$score_card->set_student_id( $customer_id );
								$score_card->set_course_id( $course_id );
								$score_card->set_status( 'sc_started' );

								$course = get_course( $course_id );
								// set new expire date if there any limitations
								if ( 0 !== $course->availability_time ) {
									$score_card->set_expire_date( '+' . $course->availability_time . ' days' );
								}
							} else {
								// ok, we already have score card
								// seems that it is error
								// @todo error handling
							}

							$score_card->set_order_id( $order_id );
							$score_card->flush();
						}
					}
				}
			}
		}
	}

	add_action( 'woocommerce_payment_complete', 'lucidlms_order_paid', 10, 1 );
	add_action( 'woocommerce_order_status_completed', 'lucidlms_order_paid', 10, 1 );


	/**
	 * Change add to cart text to some other if Course was already purchased
	 * @filter woocommerce_product_add_to_cart_text
	 *
	 * @param $text string
	 * @param $product WC_Product
	 *
	 * @return string
	 */
	function lucidlms_course_product_add_to_cart_text( $text, $product ) {
		if ( lucidlms_is_course_product_allowed_to_buy( $product->get_id() ) ) {
			return __( 'Go to Course', 'lucidlms' );
		}

		return $text;
	}

//    add_filter('woocommerce_product_add_to_cart_text', 'lucidlms_course_product_add_to_cart_text', 10, 2); // enable that if you fix problems with ajax

	/**
	 * Change add to cart url to course view page when user already bought a product
	 * @filter woocommerce_product_add_to_cart_url
	 *
	 * @param $url string
	 * @param $product WC_Product
	 *
	 * @return string
	 */
	function lucidlms_course_product_add_to_cart_url( $url, $product ) {
		if ( is_course_product( $product->get_id() ) && ( $user_id = get_current_user_id() ) ) {

			if ( $related_course = find_course_by_product( $product->get_id(), true ) ) {
				if ( lucidlms_get_current_score_card( $user_id, $related_course->id ) ) {
					return $related_course->get_permalink();
				}

			}
		}

		return $url;
	}

//    add_filter( 'woocommerce_product_add_to_cart_url', 'lucidlms_course_product_add_to_cart_url', 10, 2 ); //when you do smth with ajax actions when adding to cart and we can enable this


	/**
	 * Check if user has already started course that he's going to buy.
	 * Don't allow checkout with 'started' score cards
	 */
	function lucidlms_check_cart_items() {
		/** @var WC_Cart $cart */
		$cart = WC()->cart;

		if ( $cart_items = $cart->get_cart() ) {

			foreach ( $cart_items as $key => $cart_item ) {
				if ( ( $cart_item['data'] instanceof WC_Product ) && is_course_product( $cart_item['data']->get_id() ) ) {

					// we can't have more than 1 course product in the cart
					// so silently remove others
					if ( $cart_item['quantity'] > 1 ) {
						$cart->set_quantity( $key, 1 );
					}

					// returns empty array if no score cards with this course already exists
					$score_cards = lucidlms_is_course_product_allowed_to_buy( $cart_item['data']->get_id(), null, true );

					// so, we're found them
					if ( ! empty( $score_cards ) ) {

						/** @var $score_card LU_Score_Card */
						foreach ( $score_cards as $score_card ) {
							// let's show an error and go home
							wc_add_notice( sprintf( '%s (<a href="%s">"%s"</a>). Please remove this course from cart to continue checkout.',
								__( 'You\'re trying to buy course that you\'ve already started', 'lucidlms' ),
								get_permalink( $score_card->get_course_id() ),
								$cart_item['data']->get_title()
							), 'error' );
						}

					}

				} // end if WC_Product and Course Product
			} // end foreach
		}
	}

	add_action( 'woocommerce_check_cart_items', 'lucidlms_check_cart_items' );

	function lucidlms_create_update_connected_product( LU_Base_Course &$course ) {
		// 1. create product if not exist
		// we have the product, let's just update the data
		// add new product
		// 2. fill product with the data: post title, description,...
		// 3. assign product id to course id
		// 4. assign custom category to the woo product

		$product_title = $course->get_title();
		$product_title = empty( $product_title ) ? __( 'Course #', 'lucidlms' ) . $course->id : $product_title;

		$post           = array(
			'post_author'  => 1,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_title'   => $product_title,
			'post_parent'  => '',
			'post_type'    => 'product',
		);
		$woo_product_id = $course->get_woo_product_id();
		if ( empty( $woo_product_id ) && ( $post_id = wp_insert_post( $post ) ) ) {
			update_post_meta( $course->id, '_woo_product_id', $post_id );
			$course->set_woo_product_id( $post_id )->update_woo_product_id();

			wp_set_object_terms( $post_id, 'Course', 'product_cat' );
			wp_set_object_terms( $post_id, 'simple', 'product_type' );

			update_post_meta( $post_id, '_visibility', 'visible' );
			update_post_meta( $post_id, '_stock_status', 'instock' );
			update_post_meta( $post_id, 'total_sales', '0' );
			update_post_meta( $post_id, '_downloadable', 'yes' );
			update_post_meta( $post_id, '_virtual', 'yes' );

			update_post_meta( $post_id, '_purchase_note', '' );
			update_post_meta( $post_id, '_featured', 'no' );
			update_post_meta( $post_id, '_weight', '' );
			update_post_meta( $post_id, '_length', '' );
			update_post_meta( $post_id, '_width', '' );
			update_post_meta( $post_id, '_height', '' );
			update_post_meta( $post_id, '_sku', '' );
			update_post_meta( $post_id, '_product_attributes', array() );
			update_post_meta( $post_id, '_sale_price_dates_from', '' );
			update_post_meta( $post_id, '_sale_price_dates_to', '' );
			update_post_meta( $post_id, '_sold_individually', '' );
			update_post_meta( $post_id, '_manage_stock', 'no' );
			update_post_meta( $post_id, '_backorders', 'no' );
			update_post_meta( $post_id, '_stock', '' );

			update_post_meta( $post_id, '_download_limit', '' );
			update_post_meta( $post_id, '_download_expiry', '' );
			update_post_meta( $post_id, '_download_type', '' );

			update_post_meta( $post_id, '_lucidlms_product', 'yes' );

		} else {
			$post['ID'] = $woo_product_id;
			if ( ! $post_id = wp_update_post( $post ) ) {
				// @todo: handle an error
				return false;
			}

		}
		$thumbnail_id = get_post_meta( $course->id, '_thumbnail_id', true );
		if ( ! empty( $thumbnail_id ) ) {
			update_post_meta( $post_id, '_thumbnail_id', $thumbnail_id );
			update_post_meta( $post_id, '_product_image_gallery', $thumbnail_id );

		}


		if ( $post_id ) {
			return true;
		}

		return false;

	}

	function lucidlms_remove_connected_product( LU_Base_Course &$course ) {
		if ( $woo_product_id = $course->get_woo_product_id() ) {
			wp_trash_post( $woo_product_id );

			$course->set_woo_product_id( null )->update_woo_product_id();

			return true;
		}

		return false;
	}

	function find_course_by_product( $product_id, $return_obj = false ) {
		$args = array(
			'posts_per_page' => - 1,
			'post_type'      => 'course',
			'meta_key'       => '_woo_product_id',
			'meta_value'     => $product_id,
			'post_status'    => array( 'publish', 'draft', 'not_active' ),
		);
		if ( $course = get_posts( $args ) ) {
			if ( $return_obj ) {
				return get_course( $course[0] );
			} else {
				return $course[0]->ID;
			}
		}

		return null;
	}


	/**
	 * Checks if the product was created from Course
	 *
	 * @param $id int
	 *
	 * @return bool
	 */
	function is_course_product( $id ) {
		if ( 'yes' == get_post_meta( $id, '_lucidlms_product', true ) ) {
			return true;
		}

		return false;
	}


	/**
	 * A method that checks if this user can buy this actuvity
	 *
	 * @param int $product_id product that we're going to check
	 * @param null $user_id leave empty to use current user ID
	 * @param bool $return_arr return array of found score cards for this product and user
	 *
	 * @return array|bool
	 */
	function lucidlms_is_course_product_allowed_to_buy( $product_id, $user_id = null, $return_arr = false ) {
		if ( ! is_user_logged_in() ) {
			return $return_arr ? array() : true;
		}

		$user_id = empty( $user_id ) ? get_current_user_id() : $user_id;

		if ( $product_id && $user_id && is_course_product( $product_id ) ) {

			$score_cards = lucidlms_get_score_card_by(
				array(
					'user_id'    => $user_id,
					'status'     => apply_filters( 'lucidlms_score_card_status_active', 'sc_started' ),
					'product_id' => $product_id,
				),
				$return_arr ? true : false //dont' return objects to work more quickly
			);
			if ( ! empty( $score_cards ) ) {
				return $return_arr ? $score_cards : false;
			}
		}

		return $return_arr ? array() : true;
	}

	/**
	 * Disable guest checkout when there are course products in the cart
	 *
	 * @param $guest_checkout
	 *
	 * @return string
	 */
	function lucidlms_disable_guest_checkout( $guest_checkout ) {
		$cart = WC()->cart;

		if ( $cart && ( $cart_contents = $cart->cart_contents ) ) {
			foreach ( $cart_contents as $item ) {
				if ( is_course_product( $item['product_id'] ) ) {
					$guest_checkout = 'no';
					break;
				}
			}
		}

		return $guest_checkout;
	}

	add_filter( 'pre_option_woocommerce_enable_guest_checkout', 'lucidlms_disable_guest_checkout', 10, 1 );


	/**
	 * Disable ability to change guest checkout settings for user
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	function lucidlms_disable_quest_checkout_option( $settings ) {
		// custom_attributes
		if ( ! empty( $settings ) && is_array( $settings ) ) {
			foreach ( $settings as &$setting ) {
				if ( 'woocommerce_enable_guest_checkout' == $setting['id'] ) {
					$setting['custom_attributes'] = array( 'disabled' => 'disabled' );
					break;
				}
			}
		}

		return $settings;
	}

//    add_filter('woocommerce_payment_gateways_settings', 'lucidlms_disable_quest_checkout_option', 10, 1);

	// --------------- Templates Customization -----------------------
	/**
	 * @param $text
	 * @param $course
	 * @param $is_form
	 *
	 * @return string|void
	 */
	function lucidlms_add_to_cart_button_text( $text, $course, $is_form ) {

		if ( $woo_product_id = $course->get_woo_product_id() ) {
			$product = wc_get_product( $woo_product_id );

			if ( $product->get_sale_price() !== '' ) {
				if ( $is_form == true ) {
					return sprintf( __( 'Buy $%s.00', 'lucidlms' ), $product->get_sale_price() );
				}

				return sprintf( __( 'Buy %s', 'lucidlms' ), $product->get_price_html() );
			} else {
				return sprintf( __( 'Buy %s', 'lucidlms' ), strip_tags( $product->get_price_html() ) );
			}
		}

		return __( 'Free', 'lucidlms' );
	}

	/**
	 * Display button depend on item count
	 *
	 * @param $order
	 */
	function lucidlms_go_course_button( $order ) {

		$total_items = $order->get_items();

		if ( count( $total_items ) == 1 ) {

			$item       = current( $total_items );
			$product_id = isset( $item['product_id'] ) ? $item['product_id'] : '';

			$course = get_posts( array(
				'post_type'  => 'course',
				'meta_query' => array(
					array(
						'key'   => '_woo_product_id',
						'value' => $product_id
					)
				),
			) );

			printf( '<p class="goto-course"><a class="button" href="%s">%s</a></p>', get_permalink( current( $course )->ID ), __( 'Go to course' ) );
		} else {
			printf( '<p class="goto-course"><a class="button" href="%s">%s</a></p>', get_permalink( lucid_get_page_id( 'studentprofile' ) ), __( 'Go to my courses' ) );

		}

	}

	add_action( 'woocommerce_order_details_after_order_table', 'lucidlms_go_course_button' );

	/**
	 * @param $url String
	 * @param $course LU_Course
	 *
	 * @return string
	 */
	function lucidlms_add_to_cart_button_url( $url, $course ) {
		if ( $woo_product_id = $course->get_woo_product_id() ) {
			return add_query_arg( 'add-to-cart', $woo_product_id, WC()->cart->get_checkout_url() );
		}

		return $url;
	}

	add_filter( 'lucidlms_start_course_text', 'lucidlms_add_to_cart_button_text', 10, 3 );
	add_filter( 'lucidlms_start_course_url', 'lucidlms_add_to_cart_button_url', 10, 2 );

	/**
	 * Set user role to Student after created user with Woo Checkout
	 *
	 * @param $userdata
	 * @param $checkout
	 *
	 * @return mixed
	 */
	function lucidlms_user_role_after_checkout( $userdata, WC_Checkout $checkout ) {
		if ( $cart_items = WC()->cart->get_cart() ) {
			foreach ( $cart_items as $item ) {
				if ( isset( $item['product_id'] ) && is_course_product( $item['product_id'] ) ) {
					$userdata['role'] = 'student';
				}
			}
		}

		return $userdata;
	}

	add_filter( 'woocommerce_checkout_customer_userdata', 'lucidlms_user_role_after_checkout', 100, 2 );

endif;

/**
 * These functions should be working even if WooCommerce is installed but not activated in LucidLMS settings
 */

/**
 * Link to student profile lost password
 */
function student_profile_lost_password_page( $lostpassword_url, $redirect ) {
	$args['action'] = 'lostpassword';
	if ( ! empty( $redirect ) ) {
		$args['redirect_to'] = $redirect;
	}

	return add_query_arg( $args, network_site_url( 'wp-login.php', 'login' ) );
}

/**
 * Add custom link to lost password on student profile page
 */
function disable_wc_lostpassword_url() {
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		remove_filter( 'lostpassword_url', 'wc_lostpassword_url', 10 );
	}
	add_filter( 'lostpassword_url', 'student_profile_lost_password_page', 10, 2 );
}

add_action( 'lostpassword_url', 'disable_wc_lostpassword_url', 100, 0 );
