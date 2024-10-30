<?php

/**
 * Abstract Course Class
 *
 * The course class handles individual course data.
 *
 * @class        LU_Base_Course
 * @version        1.0.0
 * @package        LucidLMS/Abstracts
 * @category    Abstract Class
 * @author        New Normal
 */
class LU_Base_Course {

	/** @var int The course (post) ID. */
	public $id;

	/** @var object The actual post object. */
	public $post;

	/**
	 * @var array The posts meta, fetched once
	 */
	public $post_meta;

	/** @var string The course's type (course etc). */
	public $course_type = null;

    /** @var string Course status (publish, draft, not active)  */
    public $course_status = null;

    /** @var string Course status label to output (Publish, Draft, Not Active)  */
    public $course_status_label = '';

	/**
	 * Course title (post title)
	 * @var string
	 */
	public $title = '';

	/**
	 * Actisty content (post_content)
	 * @var string
	 */
	public $content = '';

	/**
	 * Used to identify the time post should be published.
	 * @var null|DateTime
	 */
	public $start_date = null;

	/**
	 * Id of course instructor
	 * @var int|null
	 */
	public $instructor_id = null;

	/**
	 * Id of assigned woocommerce product
	 * @var int|null
	 */
	protected $woo_product_id = null;

	/**
	 * Course custom fields
	 *
	 * @var array
	 */
	protected $custom_fields = array();

	/**
	 * Constructor gets the post object and sets the ID for the loaded course.
	 *
	 * @access public
	 *
	 * @param int|LU_Base_Course|WP_Post $course Course ID, post object, or course object
	 */
	public function __construct( $course ) {
		if ( is_numeric( $course ) ) {
			$this->id   = absint( $course );
			$this->post = get_post( $this->id );
		} elseif ( $course instanceof LU_Base_Course ) {
			$this->id   = absint( $course->id );
			$this->post = $course;
		} elseif ( $course instanceof WP_Post || isset( $course->ID ) ) {
			$this->id   = absint( $course->ID );
			$this->post = $course;
		}
		$this->initialize();
	}

	/**
	 * Get base data from meta or set default values
	 */
	public function initialize() {
		if ( ! empty( $this->id ) && ! empty( $this->post ) ) {
			$this->post_meta = get_post_meta( $this->id );

			$this->title   = isset( $this->post->post_title ) ? $this->post->post_title : $this->title;
			$this->content = isset( $this->post->post_content ) ? $this->post->post_content : $this->content;

			$this->start_date = isset( $this->post_meta['_start_date'][0] ) ? date_create( $this->post_meta['_start_date'][0], lucidlms_timezone() ) : null;

			$this->woo_product_id = isset( $this->post_meta['_woo_product_id'][0] ) ? intval( $this->post_meta['_woo_product_id'][0] ) : null;

			$this->instructor_id = isset( $this->post_meta['_instructor_id'][0] ) ? intval( $this->post_meta['_instructor_id'][0] ) : null;
			$this->custom_fields = $this->get_custom_fields();

            $this->course_status = isset( $this->post->post_status ) ? $this->post->post_status : $this->course_status;
            $this->course_status_label = $this->get_course_status_label();
		}
	}

	/**
	 * __isset function.
	 *
	 * @access public
	 *
	 * @param mixed $key
	 *
	 * @return bool
	 */
	public function __isset( $key ) {
		return metadata_exists( 'post', $this->id, '_' . $key );
	}

	/**
	 * Get course custom filed from filter
	 *
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public function __get( $key ) {
		return $this->get_custom_field( $key );
	}

	/**
	 * Set course custom filed from filter
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return mixed|null
	 */
	public function __set( $key, $value ) {
		return $this->set_custom_field( $key, $value );
	}

	/**
	 * Wrapper for get_permalink
	 * @return string
	 */
	public function get_permalink() {
		return get_permalink( $this->id );
	}

	/**
	 * Checks the course type.
	 *
	 * @access public
	 *
	 * @param mixed $type Array or string of types
	 *
	 * @return bool
	 */
	public function is_type( $type ) {
		return ( $this->course_type == $type ) ? true : false;
	}

	/**
	 * Returns string with course type
	 * @return string
	 */
	public function get_type() {
		return $this->course_type;
	}

	/**
	 * Set the type of course (e.g. course)
	 *
	 * @param $type String
	 *
	 * @return bool
	 */
	public function set_type( $type ) {

		if ( ! is_wp_error( wp_set_post_terms( $this->id, $type, ATYPE ) ) ) {
			$this->course_type = $type;

			return true;
		}

		return false;
	}

	/**
	 * Id of associated woocommerce product
	 * @return int|null
	 */
	public function get_woo_product_id() {
		return $this->woo_product_id;
	}

	/**
	 * Get product price if exist
	 *
	 * @return bool|string
	 */
	public function get_product_price() {
		if ( $woo_product_id = $this->get_woo_product_id() ) {
			$product = wc_get_product( $woo_product_id );

			return $product->get_price_html();
		}

		return false;
	}

	/**
	 * Set associated woocommerce product value
	 *
	 * @param $product_id
	 *
	 * @return bool|self
	 */
	public function set_woo_product_id( $product_id ) {
		if ( wc_get_product( $product_id ) || is_null( $product_id ) ) {
			$this->woo_product_id = $product_id;

			return $this;
		}

		return false;
	}

	/**
	 * Update field value in the database
	 * @return bool
	 */
	public function update_woo_product_id() {
		if ( $this->id ) {
			update_post_meta( $this->id, '_woo_product_id', $this->woo_product_id );

			return true;
		}

		return false;
	}

	/**
	 * Update field value in the database
	 * @return bool
	 */
	public function update_instructor_id() {
		if ( $this->id ) {
			update_post_meta( $this->id, '_instructor_id', $this->instructor_id );

			return true;
		}

		return false;
	}

	/**
	 * Returns whether or not the course post exists.
	 *
	 * @access public
	 * @return bool
	 */
	public function exists() {
		return empty( $this->post ) ? false : true;
	}

	/**
	 * Get the title of the post.
	 *
	 * @access public
	 * @return string
	 */
	public function get_title() {
		return apply_filters( 'lucidlms_course_title', $this->post->post_title, $this );
	}

	/**
	 * Get the post date of the post.
	 *
	 * @access public
	 * @return string
	 */
	public function get_post_date() {
		return apply_filters( 'lucidlms_course_post_date', $this->post->post_date, $this );
	}

	/**
	 * Get the start course url.
	 *
	 * @access public
	 * @return string
	 */
	public function start_course_url() {
		$url = remove_query_arg( 'started-course', add_query_arg( 'start-course', $this->id, get_permalink( $this->id ) ) );

		return apply_filters( 'lucidlms_start_course_url', $url, $this );
	}

	/**
	 * Get the start course button text
	 *
	 * @param bool|false $text
	 * @param bool|false $is_form
	 *
	 * @return mixed
	 */
	public function start_course_text( $text = false, $is_form = false ) {

		if ( ! $text ) {
			$text = __( 'Start Course', 'lucidlms' );
		}

		return apply_filters( 'lucidlms_start_course_text', $text, $this, $is_form );
	}

	/**
	 * Returns the course categories.
	 *
	 * @access public
	 *
	 * @param string $sep (default: ', ')
	 * @param string $before (default: '')
	 * @param string $after (default: '')
	 *
	 * @return string
	 */
	public function get_categories( $sep = ', ', $before = '', $after = '' ) {
		return get_the_term_list( $this->id, 'course_cat', $before, $sep, $after );
	}

	/**
	 * Gets the main course image ID.
	 * @return int
	 */
	public function get_image_id() {
		if ( has_post_thumbnail( $this->id ) ) {
			$image_id = get_post_thumbnail_id( $this->id );
		} elseif ( ( $parent_id = wp_get_post_parent_id( $this->id ) ) && has_post_thumbnail( $parent_id ) ) {
			$image_id = get_post_thumbnail_id( $parent_id );
		} else {
			$image_id = 0;
		}

		return $image_id;
	}

	/**
	 * Get course custom fields from course
	 *
	 * @return mixed|void
	 */
	protected function get_custom_fields() {
		return apply_filters( 'lucidlms_course_custom_fields', array(), $this );
	}

	/**
	 * Get course custom field based on incoming name
	 *
	 * @param $key
	 *
	 * @return mixed|null
	 */
	protected function get_custom_field( $key ) {

		if ( array_key_exists( $key, $this->custom_fields ) ) {
			$meta_value = get_post_meta( $this->id, $key, true );

			return ! empty( $meta_value ) ? $meta_value : $this->custom_fields[ $key ];
		}

		return null;
	}

	/**
	 * Set course custom field based on incoming name / value
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return bool|int|null
	 */
	protected function set_custom_field( $key, $value ) {

		if ( array_key_exists( $key, $this->custom_fields ) ) {
			return update_post_meta( $this->id, $key, $value );
		}

		return null;
	}

	/**
	 * Returns the main course image
	 *
	 * @access public
	 *
	 * @param string $size (default: 'thumbnail')
	 * @param array $attr
	 *
	 * @return string
	 */
	public function get_image( $size = 'thumbnail', $attr = array() ) {
		$image = '';

		if ( has_post_thumbnail( $this->id ) ) {
			$image = get_the_post_thumbnail( $this->id, $size, $attr );
		} elseif ( ( $parent_id = wp_get_post_parent_id( $this->id ) ) && has_post_thumbnail( $parent_id ) ) {
			$image = get_the_post_thumbnail( $parent_id, $size, $attr );
		}

		return $image;
	}

	/**
	 * Update start date in meta
	 */
	public function update_start_date_field() {
		if ( $this->id && $this->start_date ) {
			update_post_meta( $this->id, '_start_date', $this->start_date->format( 'Y-m-d' ) );
		}
	}

    public function get_course_status_label(){
        $label = '';
        if( $this->course_status ){
            $label = join( ' ', explode('_', $this->course_status));
            $label = ucwords($label);
        }
        return $label;
    }

	/**
	 * Write/update all obj in database
	 * @return int|null
	 */
	public function flush() {
		$post = array(
			'post_title'   => $this->title, // The title of your post.
			'post_content' => $this->content,
			'post_status'  => 'publish',
			'post_type'    => 'course',
			'tax_input'    => array( ATYPE => $this->course_type ),
		);
		if ( $this->id ) { // to update the existing post
			$post['ID'] = $this->id;
		}

		if ( ! $this->id && $post_id = wp_insert_post( $post, false ) ) {

			$this->id   = $post_id;
			$this->post = get_post( $post_id );

		}

		if ( $this->id ) {

			$this->update_start_date_field();
			$this->update_woo_product_id();
			$this->update_instructor_id();

			return $this->id;
		}

		return false;

	}
}
