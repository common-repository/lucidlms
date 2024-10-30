<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Course Class
 *
 * The default course type.
 *
 * @class          LU_Course
 * @version        1.0.0
 * @package        LucidLMS/Classes/Courses
 * @category       Class
 * @author         New Normal
 */
class LU_Course extends LU_Base_Course {

	/**
	 * means how long this will be accessable to a student.
	 * 0 is indefinite
	 * this value will define _expire_date for a user's course
	 * should be a number of days
	 * default value is stored in
	 * @var int
	 */
	public $availability_time = 0;

	/**
	 * If enabled, all lessons/quizzes will should be passed one by one,
	 * if not - they will be shown as a list and student can read/pass any lesson/quiz,
	 * even final quiz (if exist).
	 * @var bool
	 */
	public $sequential_logic = false;

	/**
	 * May be link to a png template. Will see later.
	 * @var string
	 */
	public $certificate_template = '';

	/**
	 * Custom certificate text
	 * @var string
	 */
	public $custom_certificate_text = '';

	/**
	 * Visibility option after complete course
	 * @var bool
	 */
	public $visibility = false;

	/**
	 * values may be:
	 * - none (student will receive certificate in any way, when finishes all course elements, without any score listed in certificate)
	 * - all quizzes average (require _threshold on all quizzes)
	 * - final quiz threshold (require final quiz _threshold)
	 * @var string 'none'|'average'|'final'
	 */
	public $threshold_type = 'none';

	/**
	 * Ids of all elements
	 * @var array
	 */
	public $elements_ids = array();

	/**
	 * Course elements
	 * @var LU_Lesson[]|LU_Quiz[]
	 */
	public $elements = array();


	// ==================== initialize methods ================== //

	/**
	 * __construct function.
	 *
	 * @access public
	 *
	 * @param mixed $course
	 */
	public function __construct( $course ) {
		$this->course_type = 'course';
		parent::__construct( $course );
	}

	/**
	 * Get base data from meta or set default values
	 */
	public function initialize() {
		parent::initialize();

		if ( ! empty( $this->id ) && ! empty( $this->post ) ) {
			$this->post_meta = get_post_meta( $this->id );

			if ( isset( $this->post_meta['_availability_time'][0] ) ) {
				$this->availability_time = $this->post_meta['_availability_time'][0];
			} else {
				$value                   = get_option( 'lucidlms_course_default_availability_time' );
				$this->availability_time = ! empty( $value ) ? $value : 0;
			}
			$this->sequential_logic        = isset( $this->post_meta['_sequential_logic'][0] ) && ( 'yes' == $this->post_meta['_sequential_logic'][0] ) ? true : false;
			$this->visibility              = isset( $this->post_meta['_course_visibility'][0] ) && ( 'yes' == $this->post_meta['_course_visibility'][0] ) ? true : false;
			$this->certificate_template    = isset( $this->post_meta['_certificate_template'][0] ) ? unserialize( $this->post_meta['_certificate_template'][0] ) : '';
			$this->custom_certificate_text = isset( $this->post_meta['_custom_certificate_text'][0] ) ? $this->post_meta['_custom_certificate_text'][0] : $this->custom_certificate_text;
			$this->threshold_type          = isset( $this->post_meta['_threshold_type'][0] ) ? $this->post_meta['_threshold_type'][0] : 'none';
			$this->elements_ids            = isset( $this->post_meta['_elements_ids'][0] ) ? unserialize( $this->post_meta['_elements_ids'][0] ) : array();
		}

		$this->load_elements();
	}

	/**
	 * Loads elements by their ids
	 */
	protected function load_elements() {

		if ( ! $this->elements_ids ) {
			$this->elements_ids = get_post_meta( $this->id, '_elements_ids', true );
		}

		if ( ! empty( $this->elements_ids ) ) {
			$this->elements = array();
			foreach ( $this->elements_ids as $element_id ) {
				$this->elements[ $element_id ] = get_course_element( $element_id );
			}
		}
	}

	// ===================== work with database ======================= //
	/**
	 * Update availability time in meta
	 */
	public function update_availability_time_field() {
		if ( $this->id ) {
			update_post_meta( $this->id, '_availability_time', $this->availability_time );
		}
	}

	/**
	 * Update sequential logic in meta
	 */
	public function update_sequential_logic_field() {
		if ( $this->id ) {
			$value = $this->sequential_logic == 'true' ? 'yes' : 'no';
			update_post_meta( $this->id, '_sequential_logic', $value );
		}
	}

	/**
	 * Update course visibility logic in meta
	 */
	public function update_course_visibility_field() {
		if ( $this->id ) {
			$value = $this->visibility == 'true' ? 'yes' : 'no';
			update_post_meta( $this->id, '_course_visibility', $value );
		}
	}

	/**
	 * Update certificate template in mete
	 */
	public function update_certificate_template_field() {
		if ( $this->id ) {
			update_post_meta( $this->id, '_certificate_template', $this->certificate_template );
		}
	}

	/**
	 * Update course instructor in meta
	 */
	public function update_custom_certificate_text_field() {
		if ( $this->id ) {
			update_post_meta( $this->id, '_custom_certificate_text', $this->custom_certificate_text );
		}
	}

	/**
	 * Update threshold type in meta
	 */
	public function update_threshold_type_field() {
		if ( $this->id ) {
			update_post_meta( $this->id, '_threshold_type', $this->threshold_type );
		}
	}

	/**
	 * Update elements ids data in meta
	 *
	 * @param array $elements
	 */
	public function update_elements_ids_field( $elements = array() ) {
		if ( $this->id ) {

			$this->elements_ids = array();

			if ( ! empty( $elements ) ) {
				foreach ( $elements as $id ) {
					$this->elements_ids[] = (int) $id;
				}
			} else if ( ! empty( $this->elements ) ) {
				foreach ( $this->elements as $element_id => $element ) {
					$this->elements_ids[] = (int) $element_id;
				}
			}

			update_post_meta( $this->id, '_elements_ids', $this->elements_ids );
		}
	}


	/**
	 * Write/update all obj in database
	 * @return int|null
	 */
	public function flush() {
		if ( $post_id = parent::flush() ) {
			$this->update_availability_time_field();
			$this->update_certificate_template_field();
			$this->update_sequential_logic_field();
			$this->update_course_visibility_field();
			$this->update_threshold_type_field();
			$this->update_custom_certificate_text_field();

			return $post_id;
		} else {
			return null;
		}
	}


	// ============= elements ================== //
	/**
	 * Get array with base courses data (usually used for templates)
	 * @return array
	 */
	public function get_elements_list() {
		$elements_list = array();
		if ( ! empty( $this->elements ) ) {
			foreach ( $this->elements as $id => $element ) {
				$elements_list[ $id ] = $element->get_data();
			}
		}

		return $elements_list;
	}

	/**
	 * Create, save to db and assign new course element to the quiz
	 *
	 * @param string $type
	 * @param array $args
	 *
	 * @return bool|mixed
	 */
	public function add_element( $type = '', $args = array() ) {
		$element = create_course_element( $type, $args );
		if ( $element_id = $element->flush() ) {
			$this->elements[ $element_id ] = $element;
			$this->update_elements_ids_field();

			return $element;
		}

		return false;
	}

	/**
	 * Remove course element and unassign it from current course
	 */
	public function remove_element( $element_id ) {
		if ( $element_id && isset( $this->elements[ $element_id ] ) ) {
			$this->elements[ $element_id ]->remove_self();
			$this->unassign_element( $element_id );

			return true;
		}

		return false;
	}

	/**
	 * Unassign current element from this course
	 *
	 * @param $element_id
	 */
	public function unassign_element( $element_id ) {
		if ( isset( $this->elements[ $element_id ] ) ) {
			unset( $this->elements[ $element_id ] );
			$this->update_elements_ids_field();
		}
	}

	/**
	 * Get final element of type
	 *
	 * @param $element_type
	 *
	 * @return bool|LU_Lesson|LU_Quiz
	 */
	public function get_final_element_of_type( $element_type ) {

		$final_element = false;
		$elements      = $this->get_elements_list();
		$elements      = array_reverse( $elements, true );

		foreach ( $elements as $id => $element ) {
			if ( $element['type'] == $element_type ) {
				$final_element = get_course_element( $id );
				break;
			}
		}

		return $final_element;
	}
}