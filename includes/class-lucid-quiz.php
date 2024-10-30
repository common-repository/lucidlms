<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Quiz Class
 *
 * The default quiz type.
 *
 * @class 		LU_Quiz
 * @version		1.0.0
 * @package		LucidLMS/Classes/Course Elements
 * @category	Class
 * @author 		New Normal
 */
class LU_Quiz extends LU_Course_Element {

    /**
     * @var int quiz threshold
     */
    public $threshold = NULL;

    /**
     * @var int number of passed attempts
     */
    public $attempts = 0;

	/**
	 * @var float time that was spent for the lesson
	 */
	public $duration = 0;

    /**
     * @var array List of questions post id, stores in the meta of a quiz
     */
    public $question_ids = array();

    /**
     * Array with all question objects
     * @var LU_Question[]
     */
    protected $questions = array();

    /**
     * __construct function.
     *
     * @access public
     * @param mixed $quiz
     * @param array $args
     */
	public function __construct( $quiz = null, $args = array() ) {
		$this->course_element_type = 'quiz';
		parent::__construct( $quiz, $args );

        $this->load_questions();
	}

    /**
     * Initialize basic properties
     */
    public function initialize( $args = array() ){
        parent::initialize( $args );
        if( !empty($this->id) && !empty($this->post)) {
            $threshold = get_post_meta($this->id, '_threshold', true);
            if ( !empty($threshold) ) {
                $this->threshold = $threshold;
            } else {
                $value                   = get_option( 'lucidlms_quiz_default_threshold' );
                $this->threshold = ! empty( $value ) ? $value : 0;
            }
            if( $attempts = get_post_meta($this->id, '_attempts', true))  $this->attempts = $attempts;
	        $duration = get_post_meta($this->id, '_duration', true);
	        $this->duration = !empty($duration) ? $duration : 0 ;
        }

        if( !empty($args) ){
            $this->threshold = isset($args['threshold']) ? $args['threshold'] : $this->threshold;
            $this->attempts = isset($args['attempts']) ? $args['attempts'] : $this->attempts;
	        $this->duration = isset($args['duration']) ? $args['duration'] : $this->duration;
        }
    }

    /**
     * Loads questions by their ids
     */
    protected function load_questions(){

        if( ! $this->question_ids ){
            $this->question_ids = get_post_meta($this->id, '_question_ids', true);
        }

        if( !empty($this->question_ids) ){
            $this->questions = array();
            foreach($this->question_ids as $question_id){
                $this->questions[$question_id] = new LU_Question($question_id);
            }
        }
    }

    /**
     * @return LU_Question[]
     */
    public function get_questions(){
        return $this->questions;
    }

    /**
     * @param $question_id
     * @return LU_Question|null
     */
    public function get_question($question_id){
        if( isset($this->questions[$question_id]) )
            return $this->questions[$question_id];
        return null;
    }

	/**
	 * Synchronize question_ids with question objects that are in questions property
	 *
	 * @param array $questions
	 */
	public function update_question_ids_field( $questions = array() ){
	    if( $this->id ) {

	        $this->question_ids = array();

		    if ( ! empty( $questions ) ) {
			    foreach ( $questions as $id ) {
				    $this->question_ids[] = $id;
			    }
		    } else if( !empty($this->questions) ){
	            foreach($this->questions as $question_id => $question){
	                $this->question_ids[] = $question_id;
	            }
	        }

	        update_post_meta($this->id, '_question_ids', $this->question_ids);
	    }
    }

    /**
     * Returns threshold value
     * @return int
     */
    public function get_threshold(){
        return $this->threshold;
    }

    /**
     * Set threshold as integer number
     */
    public function set_threshold($threshold){
        $this->threshold = intval($threshold);
    }
    /**
     * Update threshold in database
     */
    public function update_threshold_field(){
        if( !empty($this->id) ){
            update_post_meta($this->id, '_threshold', $this->threshold);
        }
    }

    /**
     * Returns attempts value
     * @return int
     */
    public function get_attempts(){
        return $this->attempts;
    }

    /**
     * Set attempts as integer number
     */
    public function set_attempts($attempts){
        $this->attempts = $attempts;
    }

    /**
     * Add a passed attempt
     * @param int $num
     */
    public function add_attempt( $num = 1 ){
        $this->attempts += $num;
    }

    /**
     * Update attempts meta in database
     */
    public function update_attempts_field(){
        if( !empty($this->id) ){
            update_post_meta($this->id, '_attempts', $this->attempts);
        }
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
     * Create a new question with answers
     * @param array $args
     * @return LU_Question
     */
    public function add_question($args = array()){
        $question = new LU_Question(null, $args);

        if ( $question_id = $question->flush() ) {
            if( $this->add_questions($question_id) ){

                return $question;
            }

        }
        return false;
    }

    /**
     * Add bunch of questions in one time
     * @param $questions_list array|int|LU_Question
     * @return bool
     */
    public function add_questions( $questions_list ){

        // put single obj to array (for unification)
        $questions_list = is_int($questions_list) || is_a($questions_list, 'LU_Question') ? array( $questions_list ) : $questions_list;
        $questions_list = is_string($questions_list) ? array( intval($questions_list) ) : $questions_list;

        if( is_array($questions_list) && !empty($questions_list) ){
            foreach( $questions_list as $question_id ){
                if( !isset($this->questions[$question_id]) ){
                    $question = get_question($question_id);
                    $this->questions[$question_id] = $question;
                }
            }
            $this->update_question_ids_field();
            return true;
        }
        return false;
    }

    /**
     * Delete question from database and unassign it
     * @param $question_id
     * @return bool
     */
    public function remove_question( $question_id ){
        if( $question_id && isset($this->questions[$question_id]) ){
            $this->questions[$question_id]->remove_self();
            $this->unassign_question($question_id);
            return true;
        }

        return false;
    }

    public function get_questions_list( $exclude_empty = false ){
        $questions_list = array();
        if( !empty($this->questions) ){
            foreach($this->questions as $id => $question){
                if( !$exclude_empty || $question->has_content() ){
                    $questions_list[$id] = $question->get_data();
                }
            }
        }
        return $questions_list;
    }

    /**
     * Just unassign question from current quiz
     * @param $question_id
     */
    public function unassign_question( $question_id ){
        if( isset($this->questions[$question_id]) ){
            unset($this->questions[$question_id]);
            $this->update_question_ids_field();
            return true;
        }
        return false;
    }
	/**
	 * Get the start course element url used mainly in loops.
	 *
	 * @access public
	 * @return string
	 */
	public function start_course_element_url() {
		$url = remove_query_arg( 'started-quiz', add_query_arg( 'start-quiz', $this->id ) );

		return apply_filters( 'lucidlms_start_quiz_url', $url, $this );
	}

	/**
	 * Get the start course element button text for the single page
	 *
	 * @access public
	 * @return string
	 */
	public function single_start_course_element_text() {
		return apply_filters( 'lucidlms_single_start_quiz_text', __( 'Start Quiz', 'lucidlms' ), $this );
	}

    /**
     * Updates all data as default course element, also the meta is updated
     * @return int|null
     */
    public function flush(){
        if( $post_id = parent::flush() ){
            $this->update_threshold_field();
            $this->update_attempts_field();
	        $this->update_duration_field();
	        $this->update_question_ids_field();

            return $post_id;
        }
        return null;
    }
}