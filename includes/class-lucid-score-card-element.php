<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Score Card Element Class
 *
 * The default lesson type.
 *
 * @class 		LU_Score_Card_Element
 * @version		1.0.0
 * @package		LucidLMS/Classes
 * @category	Class
 * @author 		New Normal
 */
class LU_Score_Card_Element {

    /**
     * @var int Course element id
     */
    public $id;

    /**
     * Related Score Card id
     * @var null
     */
    public $score_card_id;

    /**
     * @var string
     */
    public $type;

    /**
     * @var int
     */
    public $time_spent = 0;

    /**
     * From 0 to 100
     * @var int
     */
    public $score;

    /**
     * Started or completed etc
     * @var String
     */
    public $status = 'started';

    /**
     * @var int
     */
    public $passed_attempts = 0;

    /**
     * @var string
     */
    public $course_element_title = '';

    /**
     * @var array
     */
    public $questions = array();

    protected $course_element = null;

    /**
     * __construct function.
     *
     * @access public
     * @param $course_element_id
     * @param $score_card_id
     * @param array $args score card elements meta data array
     */
	public function __construct( $course_element_id, $score_card_id, $args = array()) {

        if( $course_element_id && $score_card_id) {
            if( $course_element = get_course_element($course_element_id) ){
                $this->id = $course_element_id;
                $this->score_card_id = $score_card_id;

                $this->course_element = $course_element;
                $this->initialize($args);
            }

        }
    }

    public function initialize($args = array()){
        $defaults = array(
            'type' => '',
            'time_spent' => 0,
            'score' => null,
            'status' => 'started',
            'passed_attempts' => 0,
            'course_element_title' => '',
            'questions' => array(),
        );

        // load previously stored data in score card meta
        foreach( $args as $key => $value){
            switch( $key ){
                case 'type':
                    $this->type = $value;
                    break;
                case 'time_spent':
                    $this->time_spent = $value;
                    break;
                case 'score':
                    $this->score = $value;
                    break;
                case 'status':
                    $this->status = $value;
                    break;
                case 'passed_attempts':
                    $this->passed_attempts = $value;
                    break;
                case 'course_element_title':
                    $this->course_element_title = $value;
                    break;
                case 'questions':
                    $this->questions = $value;
                    break;

            }
        }

        if( $this->course_element ) { // if no data found, load initial data from course
            $this->type = empty($this->type) ? $this->course_element->get_type() : $this->type;
            $this->course_element_title = empty($this->course_element_title) ? $this->course_element->get_title() : $this->course_element_title;
        }

    }

    public function add_question( $question_id, $args = array() ){
        if( $this->questions_available() && $question_id ){
            /** @var LU_Question[] $available_questions */
            $available_questions = $this->course_element->get_questions_list(true);

            if( in_array($question_id, array_keys($available_questions)) && !isset($this->questions[$question_id]) ) {

                $defaults = array(
                    'question_title'     => '',
                    'answers'            => array(),
                    'is_answers_correct' => null,
                );
                $args = wp_parse_args($args, $defaults);

                if( empty($args['question_title']) && isset($available_questions[$question_id]['question_text']) ){
                    $args['question_title'] = $available_questions[$question_id]['question_text'];
                }

                $this->questions[$question_id] = $args;

                return true;
            }

        }
        return false;
    }

    public function edit_question( $question_id, $args = array()){
        if( $this->questions_available() ){
            if ( !isset($this->questions[$question_id]) ){
                return $this->add_question($question_id, $args);
            } else {
                if( !empty($args) ){
                    foreach( $args as $key => $value){
                        $this->questions[$question_id][$key] = $value;
                    }
                }
                return true;
            }
        }
        return false;
    }

    /**
     * 'answers'            => array(
     *  'q_53fdc4da85a0f' => 'Lola'
     * ),
     * @param $question_id
     * @param array $answers
     * @return bool
     */
    public function add_answers($question_id, $answers = array()){
        if( !$this->questions_available() ) return false;

	    if( isset($answers[0]) && (false === $answers[0]) ){
		    $this->remove_answer($question_id);
		    return;
	    }

		if( ! isset($this->questions[$question_id]) ){
			$this->add_question($question_id);
		}

	    $this->questions[$question_id]['answers'] = $answers;
	    $this->validate_answers($question_id);
    }

    /**
     * Checks answers and change the data in questions list array
     * @param $question_id
     * @return bool
     */
    public function validate_answers($question_id){
        if( !$this->questions_available() ) return false;

        if( isset($this->questions[$question_id]['answers']) && !empty($this->questions[$question_id]['answers']) ){
            if( $question = $this->course_element->get_question($question_id)) {
                $question_type = $question->get_question_type();

                if( 'open' == $question_type){
                    $answer = current($this->questions[$question_id]['answers']); // get first element from answers array
                    $answer_correct = !empty($answer) ? true : false; // set answer correct if it is not empty
                    $this->set_answers_correct($question_id, $answer_correct);

                } else {
                    $correct_answers = $question->get_correct_answers();

                    foreach( $this->questions[$question_id]['answers'] as $key => $answer){

                        if( isset($correct_answers[$key]) ){
                            if( ($answer == $correct_answers[$key]['answer']) ){
                                unset( $correct_answers[$key] ); // count as passed answer
                            }
                        } else {
                            $correct_answers = false; // set answers as incorrect if we have at least one wrong
                            break;
                        }

                        if( 'single_choice' == $question_type ) break; // break after first result for single choice question
                    }

                    // if all correct answers were passed mark question as correct
                    if( (false === $correct_answers) || !empty($correct_answers) ){
                        $this->set_answers_correct($question_id, false);
                    } else {
                        $this->set_answers_correct($question_id);
                    }

                }
            }
        } else {
            $this->set_answers_correct($question_id, false);
        }

    }

    public function set_answers_correct( $question_id, $is_correct = true){
        if( isset($this->questions[$question_id]) ){
            $this->questions[$question_id]['is_answers_correct'] = (bool) $is_correct;
        }
    }


    public function edit_answers($question_id, $answers){
        if( !$this->questions_available() ) return false;

        if( isset($this->questions[$question_id]) ){
            foreach( $answers as $key => $value){
                $this->questions[$question_id]['answers'][$key] = $value;
            }
        } else {
            $this->add_answers($question_id, $answers);
        }

    }

    /**
     * @param $question_id
     */
    public function remove_question( $question_id ){
        unset( $this->questions[$question_id] );
    }

    /**
     *
     * @param $question_id
     */
    public function remove_answer( $question_id ){
        unset( $this->questions[$question_id] );
    }

    /**
     * Reset questions data
     */
    public function remove_all_questions(){
        $this->questions = array();
    }

    /**
     * If questions might be added to this score card element
     * @return bool
     */
    public function questions_available(){
        if( $this->course_element && ('quiz' == $this->course_element->get_type()) ){
            return true;
        }
        return false;
    }
    /**
     * @return LU_Lesson|LU_Quiz|null
     */
    public function get_course_element(){
        return $this->course_element;
    }
	/**
	 * Returns the type of SC
	 * @return string
	 */
	public function get_status(){
		return $this->status;
	}

    public function get_status_name(){
        $available_statuses = apply_filters('lucidlms_score_card_element_statuses', array(
            'started'   => __('started', 'lucidlms'),
            'completed' => __('completed', 'lucidlms'),
            'expired'   => __('expired', 'lucidlms'),
        ));

        if( isset($available_statuses[$this->status]) ){
            return $available_statuses[$this->status];
        }

        return $this->status; // return plain status if not translation available
    }

    public function recount_score(){
        if( ! $this->questions_available() ) return false;

	    $questions = $this->course_element->get_questions_list(true);
        $num = count( $questions );
        $correct_answers = 0;
        if( $num > 0 ) {
	        foreach ( $questions as $id => $question ) {
		        if ( isset($this->questions[$id]) && $this->questions[$id]['is_answers_correct'] ) {
			        $correct_answers++;
		        }
	        }
        }
        $this->score = round( (100 * $correct_answers) / $num );

        return $this->score;
    }

    /**
     * A method that tried to complete the quiz,
     * Use that when user finished it
     * set quiz in scorecard as 'completed'. Set 'failed' if score is less than threshold and course threshold type is 'average'; add +1 to passed_attempts
     */
    public function complete_element(){

        if( $this->course_element ) {

            if( $this->questions_available()) {
                $this->recount_score();

                if ( $parent_course = $this->course_element->get_parent_course() ) {

                    if( ($this->score < $this->course_element->threshold) && ('none' != $parent_course->threshold_type) ){
                        $this->status = 'failed';
                        $this->passed_attempts++;
	                    return;
                    }

                }

            }

            $this->status = 'completed';
            $this->passed_attempts++;
        }

    }
    /**
     * Convert an object to array
     * @return array
     */
    public function get_data_array(){
        return (array) $this;
    }

	/**
	 * Get reset passed attempts link
	 *
	 * @param int $attempts_to_reset
	 *
	 * @return string
	 */
	public function get_reset_attempts_link( $attempts_to_reset = 1 ) {
		if ( $this->id && $this->score_card_id ) {
			return add_query_arg( array(
				'reset-attempts' => $attempts_to_reset
			), get_edit_post_link( $this->score_card_id ) );
		}

		return '#';
	}

	/**
	 * Reset needed number of attempts
	 *
	 * @param int $attempts_to_reset
	 *
	 * @return bool
	 */
	public function reset_attempts( $attempts_to_reset = 1 ) {
		if ( $this->id ) {
			if ( (int) $this->passed_attempts ) {
				$this->passed_attempts = $this->passed_attempts - $attempts_to_reset;
				$this->passed_attempts = $this->passed_attempts < 0 ? 0 : $this->passed_attempts;
				return true;
			}
		}

		return false;
	}

}