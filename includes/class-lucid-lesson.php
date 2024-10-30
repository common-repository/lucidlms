<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Lesson Class
 *
 * The default lesson type.
 *
 * @class 		LU_Lesson
 * @version		1.0.0
 * @package		LucidLMS/Classes/Course Elements
 * @category	Class
 * @author 		New Normal
 */
class LU_Lesson extends LU_Course_Element {

    /**
     * @var float time that was spent for the lesson
     */
    public $duration = 0;

    /**
     * __construct function.
     *
     * @access public
     * @param mixed $lesson
     * @param array $args
     */
	public function __construct( $lesson = null, $args = array() ) {
        $this->course_element_type = 'lesson';
        parent::__construct( $lesson, $args );
    }

    /**
     * Extends base initialize method. Added duration init
     */
    public function initialize($args = array()){
        parent::initialize( $args );
        if( !empty($this->id) && !empty($this->post)) {
            $duration = get_post_meta($this->id, '_duration', true);
            $this->duration = !empty($duration) ? $duration : 0 ;
        }

        if( !empty($args) ){
            $this->duration = isset($args['duration']) ? $args['duration'] : $this->duration;
        }
    }

	/**
	 * Get the start course element url used mainly in loops.
	 *
	 * @access public
	 * @return string
	 */
	public function start_course_element_url() {
		$url = remove_query_arg( 'started-lesson', add_query_arg( 'start-lesson', $this->id ) );

		return apply_filters( 'lucidlms_start_lesson_url', $url, $this );
	}

	/**
	 * Get the start course element button text for the single page
	 *
	 * @access public
	 * @return string
	 */
	public function single_start_course_element_text() {
		return apply_filters( 'lucidlms_single_start_lesson_text', __( 'Start Lesson', 'lucidlms' ), $this );
	}

    /**
     * Update duration in database
     */
    public function update_duration_field(){
        if( !empty($this->id) ){
            update_post_meta($this->id, '_duration', floatval($this->duration));
        }
    }

    /**
     * Updates all data as default course element, also the meta is updated
     * @return int|null
     */
    public function flush(){
        if( $post_id = parent::flush() ){
            $this->update_duration_field();
            return $post_id;
        }
        return null;
    }
}