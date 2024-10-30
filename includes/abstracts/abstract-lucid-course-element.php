<?php

/**
 * Abstract Course Element Class
 *
 * The course element class handles individual course element data.
 *
 * @class          LU_Course_Element
 * @version        1.0.0
 * @package        LucidLMS/Abstracts
 * @category       Abstract Class
 * @author         New Normal
 */
abstract class LU_Course_Element {

	/** @var int The course element (post) ID. */
	public $id;

	/** @var object The actual post object. */
	public $post;

	/** @var string The course element's type (lesson, quiz etc). */
	public $course_element_type = null;

	/**
	 * @var string Name of the type for display to the user
	 */
	public $course_element_type_name = '';

	/**
	 * @var string Title of the element (usually post title)
	 */
	public $title = '';

	/**
	 * @var string element short description (excerpt)
	 */
	public $description = '';

	/**
	 * @var string course element content (if needed)
	 */
	public $content = '';

	/**
	 * @var null course element is final
	 */
	public $is_final = null;

	/**
	 * Course element custom fields
	 *
	 * @var array
	 */
	protected $custom_fields = array();

	/**
	 * Constructor gets the post object and sets the ID for the loaded course.
	 *
	 * @access public
	 *
	 * @param int|LU_Course_Element|WP_Post $course_element Course element ID, post object, or course element object
	 * @param array $args
	 */
	public function __construct( $course_element = null, $args = array() ) {
		if ( is_numeric( $course_element ) ) {
			$this->id   = absint( $course_element );
			$this->post = get_post( $this->id );
		} elseif ( $course_element instanceof LU_Course_Element ) {
			$this->id   = absint( $course_element->id );
			$this->post = $course_element;
		} elseif ( $course_element instanceof WP_Post || isset( $course_element->ID ) ) {
			$this->id   = absint( $course_element->ID );
			$this->post = $course_element;
		}

		$this->initialize( $args );
	}

	/**
	 * Set init element data
	 */
	public function initialize( $args = array() ) {
		if ( ! empty( $this->id ) && ! empty( $this->post ) ) {
			$this->title       = isset( $this->post->post_title ) ? $this->post->post_title : '';
			$this->description = isset( $this->post->post_excerpt ) ? $this->post->post_excerpt : '';
			$this->content     = isset( $this->post->post_content ) ? $this->post->post_content : '';
		}

		if ( ! empty( $args ) ) {
			$args = wp_parse_args( $args, array(
				'title'       => '',
				'description' => '',
				'content'     => '',
			) );

			$this->title       = isset( $args['title'] ) ? $args['title'] : $this->title;
			$this->description = isset( $args['description'] ) ? $args['description'] : $this->description;
			$this->content     = isset( $args['content'] ) ? $args['content'] : $this->content;
		}

		$this->set_type_name();
		$this->custom_fields = $this->get_custom_fields();
	}


	/**
	 * Wrapper for get_permalink
	 * @return string
	 */
	public function get_permalink() {
		return get_permalink( $this->id );
	}

	/**
	 * Get course element custom filed from filter
	 *
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public function __get( $key ) {
		return $this->get_custom_field( $key );
	}

	/**
	 * Set course element custom filed from filter
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
	 * Checks the course element type.
	 *
	 * @access public
	 *
	 * @param mixed $type Array or string of types
	 *
	 * @return bool
	 */
	public function is_type( $type ) {
		return ( $this->course_element_type == $type ) ? true : false;
	}

	/**
	 * Returns a string with type of course element
	 * @return string
	 */
	public function get_type() {
		return $this->course_element_type;
	}

	/**
	 * Set the type of course element (e.g. lesson or quiz)
	 *
	 * @param $type String
	 *
	 * @return bool
	 */
	public function set_type( $type ) {

		if ( ! is_wp_error( wp_set_post_terms( $this->id, $type, AETYPE ) ) ) {
			$this->course_element_type = $type;

			$this->set_type_name();

			return true;
		}

		return false;
	}

	/**
	 * Text representation of type
	 * @return string
	 */
	public function get_type_name() {
		return $this->course_element_type_name;
	}

	/**
	 * Update type name data from slug metadata
	 */
	protected function set_type_name() {
		if ( ! empty( $this->course_element_type ) ) {
			$this->course_element_type_name = LU()->taxonomies->get_term_name( AETYPE, $this->course_element_type );
		}
	}

	/**
	 * The description of an element
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Change a course element description
	 *
	 * @param $description
	 */
	public function set_description( $description ) {
		$this->description = $description;
	}

	/**
	 * Update description of the element in database (this is excerpt)
	 * @return bool
	 */
	public function update_description() {
		if ( $this->id ) {
			$post = array(
				'post_excerpt' => $this->description,
				'ID'           => $this->id,
			);
			if ( $post_id = wp_insert_post( $post, false ) ) {
				return true;
			}
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
	 * Get parent course class
	 *
	 * @return LU_Course
	 */
	public function get_parent_course() {
		global $wpdb;

		$course_id = $wpdb->get_results( "SELECT `post_id` as `ID` FROM `" . $wpdb->postmeta . "` WHERE `meta_key` = '_elements_ids' AND `meta_value` REGEXP '.*i:" . $this->id . ".*'" );

		if ( isset( $course_id[0]->ID ) ) {
			return get_course( $course_id[0]->ID );
		}

		return false;
	}

	/**
	 * Check if course element is final
	 *
	 * @return bool|null
	 */
	public function is_final() {

		if ( $this->is_final !== null ) {
			return $this->is_final;
		}
		$parent_course = $this->get_parent_course();
		$elements      = $parent_course->get_elements_list();
		$elements      = array_reverse( $elements, true );

		foreach ( $elements as $id => $element ) {
			if ( $element['type'] == $this->course_element_type ) {
				if ( $id == $this->id ) {
					$this->is_final = true;
				} else {
					$this->is_final = false;
				}
				break;
			}
		}

		return $this->is_final;
	}

	/**
	 * Get course element custom fields from course
	 *
	 * @return mixed|void
	 */
	public function get_custom_fields() {
		return apply_filters( 'lucidlms_course_element_custom_fields', array(), $this );
	}

	/**
	 * Get course element custom field based on incoming name
	 *
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public function get_custom_field( $key ) {
		if ( array_key_exists( $key, $this->custom_fields ) ) {
			$meta_value = get_post_meta( $this->id, $key, true );

			return ! empty( $meta_value ) ? $meta_value : $this->custom_fields[ $key ];

		}

		return null;
	}

	/**
	 * Set course element custom field based on incoming name / value
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return bool|int|null
	 */
	public function set_custom_field( $key, $value ) {

		if ( array_key_exists( $key, $this->custom_fields ) ) {
			return update_post_meta( $this->id, $key, $value );
		}

		return null;
	}

	/**
	 * Get the title of the post.
	 *
	 * @access public
	 * @return string
	 */
	public function get_title() {
		return apply_filters( 'lucidlms_course_element_title', $this->post->post_title, $this );
	}

	/**
	 * Change the title
	 *
	 * @param $title
	 */
	public function set_title( $title ) {
		$this->title = $title;
	}

	/**
	 * Update the title in the post data
	 */
	public function update_title() {
		if ( $this->id ) {
			$post = array(
				'post_title' => $this->title, // The title of your post.
				'ID'         => $this->id,
			);
			if ( $post_id = wp_insert_post( $post, false ) ) {
				return true;
			}
		}

		return false;
	}

	public function get_content() {
		return $this->content;
	}

	/**
	 * @param $content
	 */
	public function set_content( $content ) {
		$this->content = $content;
	}

	/**
	 * Updates content in database
	 * @return bool
	 */
	public function update_content() {
		if ( $this->id ) {
			$post = array(
				'post_content' => $this->content, // The title of your post.
				'ID'           => $this->id,
			);
			if ( $post_id = wp_insert_post( $post, false ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns whether or not the course element is featured.
	 *
	 * @access public
	 * @return bool
	 */
	public function is_featured() {
		return $this->featured === 'yes' ? true : false;
	}

	/**
	 * Get the complete course element url.
	 *
	 * @access public
	 * @return string
	 */
	public function complete_course_element_url() {
		$url = remove_query_arg( 'completed-course-element', add_query_arg( 'complete-course-element', $this->id ) );

		return apply_filters( 'lucidlms_complete_course_element_url', $url, $this );
	}

	/**
	 * Get the start course element button text for the single page
	 *
	 * @access public
	 * @return string
	 */
	public function complete_course_element_text() {
		return apply_filters( 'lucidlms_complete_course_element_text', __( 'Complete', 'lucidlms' ), $this );
	}

	/**
	 * Get the try again course element url.
	 *
	 * @access public
	 * @return string
	 */
	public function try_again_course_element_url() {
		$url = add_query_arg( 'try-again-course-element', $this->id, get_permalink( $this->id ) );

		return apply_filters( 'lucidlms_try_again_course_element_url', $url, $this );
	}

	/**
	 * Get the try again course element button text for the single page
	 *
	 * @access public
	 * @return string
	 */
	public function try_again_course_element_text() {
		return apply_filters( 'lucidlms_try_again_course_element_text', __( 'Try Again', 'lucidlms' ), $this );
	}

	public function get_data() {
		return array(
			'type'        => $this->course_element_type,
			'type_name'   => $this->course_element_type_name,
			'title'       => $this->title,
			'content'     => $this->content,
			'description' => $this->description,
		);
	}

	/**
	 * Remove the element from database!
	 * Please use carefully!
	 *
	 */
	public function remove_self() {
		if ( $this->id && wp_delete_post( $this->id ) ) {
			$this->id   = null;
			$this->post = null;
			$this->initialize();
		}
	}

	public function flush() {
		$post = array(
			'post_title'   => $this->title, // The title of your post.
			'post_excerpt' => $this->description, // we store descriptions in excerpts
			'post_content' => $this->content,
			'post_status'  => 'publish',
			'post_type'    => 'course_element',
			'tax_input'    => array( AETYPE => $this->course_element_type ),
		);
		if ( $this->id ) { // to update the existing post
			$post['ID'] = $this->id;
		}
		if ( $post_id = wp_insert_post( $post, false ) ) {
			$this->id   = $post_id;
			$this->post = get_post( $post_id );

			return $post_id;
		} else {
			return null;
		}
	}

	/**
	 * Find the next element
	 * @return LU_Course_Element|bool
	 */
	public function get_next_course_element() {
		$parent_course = $this->get_parent_course();
		$key           = array_search( $this->id, $parent_course->elements_ids );

		if ( isset( $parent_course->elements_ids[ ++ $key ] ) ) {
			$id = $parent_course->elements_ids[ $key ];

			return $parent_course->elements[ $id ];
		}

		return false;
	}

	/**
	 * Find previous element
	 * @return LU_Course_Element|bool
	 */
	public function get_previous_course_element() {
		$parent_course = $this->get_parent_course();
		$key           = array_search( $this->id, $parent_course->elements_ids );

		if ( isset( $parent_course->elements_ids[ -- $key ] ) ) {
			$id = $parent_course->elements_ids[ $key ];

			return $parent_course->elements[ $id ];
		}

		return false;
	}
}
