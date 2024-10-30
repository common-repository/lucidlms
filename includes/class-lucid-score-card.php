<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Score Card Class
 *
 * The default lesson type.
 *
 * @class 		LU_Score_Card
 * @version		1.0.0
 * @package		LucidLMS/Classes/Course Elements
 * @category	Class
 * @author 		New Normal
 */
class LU_Score_Card {


    /** @var int The scorecard (post) ID. */
    public $id;

    /** @var object The actual post object. */
    public $post;

    /**
     * Started, completed or expired score card
     * @var string
     */
    public $status = '';

    /**
     * Here is the name of our score card type to display
     * @var string
     */
    public $status_name = '';

    /**
     * @var array The posts meta, fetched once
     */
    public $post_meta;

    /**
     * Score card title (post title)
     * @var string
     */
    public $title = '';

    /**
     * Foreign key to course id
     * @var integer
     */
    protected $course_id = null;

    /**
     * A title of connected course
     * @var string
     */
    protected $course_title = '';

    /**
     * Course type term slug
     * @var string
     */
    protected $course_type = 0;

    /**
     * ID of user that passed the test
     * @var int
     */
    protected $student_id = null;

    /**
     * Used to identify the time when user started the course
     * @var null|DateTime
     */
    public $start_date = null;

    /**
     * Marks date when the course would be expired
     * @var DateTime|null
     */
    public $expire_date = null;

	/**
     * Marks date when the course would be expired
     * @var DateTime|null
     */
    public $complete_date = null;

    /**
     * @var LU_Score_Card_Element[]
     */
    public $score_card_elements = array();

    /**
     * Stores related order id, if user bought a course
     * @var null
     */
    protected $order_id = null;

    /**
     * @var null|LU_Course
     */
    public $course = null;

    /**
     * __construct function.
     *
     * @access public
     * @param null $score_card
     * @param array $args
     * @internal param mixed $lesson
     */
	public function __construct( $score_card = null, $args = array() ) {

        if ( is_numeric( $score_card ) ) {
            $this->id   = absint( $score_card );
            $this->post = get_post( $this->id );
        } elseif ( $score_card instanceof LU_Score_Card ) {
            $this->id   = absint( $score_card->id );
            $this->post = $score_card->post;
        } elseif ( $score_card instanceof WP_Post || isset( $score_card->ID ) ) {
            $this->id   = absint( $score_card->ID );
            $this->post = $score_card;
        }

        $this->initialize($args);

    }

    /**
     * Extends base initialize method. Added duration init
     */
    public function initialize($args = array()){
        $this->id = empty($this->id) && !empty($this->post) ? absint($this->post->ID) : $this->id;
        $this->post = empty($this->post) && !empty($this->id) ? get_post($this->id) : $this->post;

        if ( !empty($this->post) && !empty($this->id)){
            $this->title = $this->post->post_title;

            if( $terms = wp_get_post_terms($this->id, SCSTATUS) ) {
                $term = current( $terms );
                $this->status = isset( $term->slug ) ? $term->slug : 'sc_started';
                $this->status_name = isset( $term->name ) ? $term->name : '';
            }

            $this->post_meta = get_post_meta($this->id);

            if( isset($this->post_meta['_course_id'][0]) ){
                $this->set_course_id($this->post_meta['_course_id'][0]);
            }

            $this->student_id = isset( $this->post_meta['_student_id'][0] ) ? $this->post_meta['_student_id'][0] : null;

            if( isset($this->post_meta['_start_date'][0]) ){
                $this->set_start_date($this->post_meta['_start_date'][0]);
            }

            if( isset($this->post_meta['_expire_date'][0]) ){
                $this->set_expire_date($this->post_meta['_expire_date'][0]);
            }

	        if( isset($this->post_meta['_complete_date'][0]) ){
                $this->set_complete_date($this->post_meta['_complete_date'][0]);
            }

            if( isset($this->post_meta['_order_id'][0]) ){
                $this->set_order_id($this->post_meta['_order_id'][0]);
            }

            $this->load_score_card_elements();

        }

        if( !empty($args) ){
            $args = wp_parse_args($args, array(
                'score_card_status' => apply_filters('lucidlms_default_score_card_status', 'sc_started'),
                'course_id' => null,
                'student_id' => null,
                'start_date' => 'now',
                'expire_date' => null,
                'complete_date' => null,
            ));


            if( isset($args['score_card_status']) ){
                $this->set_status( $args['score_card_status'] );
            }

            if( $args['course_id'] ){
                $this->set_course_id($args['course_id']);
            }

            if( $args['student_id'] ){
                $this->set_student_id( $args['student_id']);
            }

            if( $args['start_date'] ){
                $this->set_start_date( $args['start_date']);
            }

            if( $args['expire_date'] ){
                $this->set_expire_date( $args['expire_date']);
            }

	        if( $args['complete_date'] ){
                $this->set_complete_date( $args['complete_date']);
            }

            if( $args['order_id'] ){
                $this->set_order_id( $args['order_id']);
            }

        }
    }

    /**
     * Loads elements from metadata
     */
    public function load_score_card_elements(){
        if( $this->id ){
            $meta_data = get_post_meta($this->id, '_score_card_elements', true);
            $this->score_card_elements = array();
            if( !empty($meta_data) && is_array($meta_data) ){
                foreach($meta_data as $id => $element){
                    $this->score_card_elements[$id] = new LU_Score_Card_Element($id, $this->id, $element, $this);
                }
            }
        }
    }

    /**
     * Upd elements array in score card post meta
     * @return bool
     */
    public function update_score_card_elements() {
        if( $this->id) {
            $score_card_elements_data = array();
            if( !empty($this->score_card_elements) ){

                /** @var $element LU_Score_Card_Element */
                foreach( $this->score_card_elements as $course_id => $element){
                    $score_card_elements_data[$course_id] = $element->get_data_array();
                }
            }
            update_post_meta($this->id, '_score_card_elements', $score_card_elements_data);

            return true;
        }
        return false;
    }

    /**
     * Create and initialize new course element using course element id
     * @param $course_element_id
     * @param array $args
     */
    public function add_score_card_element($course_element_id, $args = array()){
        if( !isset($this->score_card_elements[$course_element_id]) ){
            $this->score_card_elements[$course_element_id] = new LU_Score_Card_Element($course_element_id, $this->id, $args);
        }
    }

    /**
     * Delete course element
     * @param $course_element_id
     */
    public function remove_score_card_element($course_element_id){
        unset($this->score_card_elements[$course_element_id]);
    }

    /**
     * Pass here some data as arguments to change them in course element
     *
     * Defaults are:
     * array(
     *   'type' => '',
     *   'time_spent' => 0,
     *   'score' => null,
     *   'status' => 'started',
     *   'passed_attempts' => 0,
     *   'course_element_title' => '',
     *   'questions' => array(),
     * )
     *
     * @param $course_element_id
     * @param $args
     */
    public function edit_score_card_element($course_element_id, $args){
        if( !isset($this->score_card_elements[$course_element_id]) ){
            $this->score_card_elements[$course_element_id] = new LU_Score_Card_Element($course_element_id, $this->id);
        }

        $this->score_card_elements[$course_element_id]->initialize($args);
    }

    /**
     * Returns object with LU_Score_Card_Element data
     * @param $course_element_id
     * @return LU_Score_Card_Element|null
     */
    public function get_score_card_element( $course_element_id ){
        if( isset($this->score_card_elements[$course_element_id]) )
            return $this->score_card_elements[$course_element_id];
        return null;
    }

    /**
     * @param LU_Score_Card_Element $score_card_element
     */
    public function set_score_card_element( LU_Score_Card_Element $score_card_element ){
        $this->score_card_elements[$score_card_element->id] = $score_card_element;
    }

    protected function generate_title(){
        $title = __('Score card', 'lucidlms');

        if( !empty($this->student_id) ){
            if( $user_data = get_userdata($this->student_id) ){
                $title .= ' for ' . $user_data->data->user_nicename;
            }
        }

        $title .= sprintf(' [%s]', uniqid('sc_'));
        return $title;
    }

    /**
     * Associated course id
     * @return int
     */
    public function get_course_id(){
        return $this->course_id;
    }

    /**
     * Set associated course id and updates all relative course data
     * @param $course_id
     * @return bool
     */
    public function set_course_id($course_id){
        if( $course = get_course($course_id) ){
            $this->course_id = $course->id;
            $this->course_title = $course->title;
            $this->course_type = $course->get_type();

            $this->course = $course;

            return true;
        }
        return false;
    }

    /**
     * Course title
     * @return string
     */
    public function get_course_title(){
        return $this->course_title;
    }

    /**
     * Course type
     * @return string
     */
    public function get_course_type(){
        return $this->course_type;
    }

    /**
     * Update course id in the database
     * @return bool
     */
    public function update_course_id(){
        if( $this->id ){
            update_post_meta($this->id, '_course_id', $this->course_id);
            update_post_meta($this->id, '_course_title', $this->course_title);
            update_post_meta($this->id, '_course_type', $this->course_type);
            return true;
        }
        return false;
    }


    /**
     * Set score card taxonomy data
     * @param $new_status
     * @return bool
     */
    public function set_status($new_status){

	    $this->status = $new_status;

        switch( $new_status ){
            case 'sc_completed':
                $this->set_complete_date();
                break;
            case 'sc_started': // here we need to set started and expired date
                $this->set_start_date();
                $this->set_expire_date();
                break;
        }

	    // Made this via hook since taxonomies are loaded too late
        $this->set_status_name();
    }

	/**
	 * Set score card taxonomy data name
	 * @return bool
	 */
	public function set_status_name() {

		$available_score_types = $this->get_available_score_types();

		if( !isset($available_score_types[$this->status]) ){
			// @todo handle wrong type error
			return false;
		}

		$this->status_name = $available_score_types[$this->status];

		return true;
	}

    /**
     * Flush score card type to the database
     * @return bool
     */
    public function update_status(){
        if( $this->id ){
            if( !is_wp_error( wp_set_post_terms($this->id, $this->status, SCSTATUS) ) ){
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the type of SC
     * @return string
     */
    public function get_status(){
        return $this->status;
    }

    /**
     * Name of score card's type to display
     * @return string
     */
    public function get_status_name(){
        return $this->status_name;
    }

    /**
     * To fetch array with available score types
     * @return array
     */
    public function get_available_score_types(){
        /**
         * @var $taxonomies_controller LU_Core_Taxonomies
         */
        $taxonomies_controller = LU()->taxonomies;

        return $taxonomies_controller->get_terms(SCSTATUS, true);
    }

    /**
     * Wordpress user id that passed a course
     * @return int
     */
    public function get_student_id(){
        return $this->student_id;
    }

    /**
     * Assign user to score card
     * @param $id
     * @return bool
     */
    public function set_student_id($id){
        if( get_userdata($id) ){
            $this->student_id = $id;
            return true;
        }
        return false;
    }

    /**
     * Update student id in the post meta
     */
    public function update_student_id(){
        if( $this->id ){
            update_post_meta($this->id, '_student_id', $this->student_id);
        }
    }

    /**
     * @return DateTime|null
     */
    public function get_start_date(){
        return $this->start_date;
    }

    public function update_start_date(){
        if(  $this->id && ($this->start_date instanceof DateTime) ){
            update_post_meta($this->id, '_start_date', $this->start_date->format('Y-m-d H:i:s'));
            return true;
        }
        return false;
    }

    /**
     * When the user started a course
     * @param $start_date string
     * @return bool
     */
    public function set_start_date($start_date = 'now'){
        $start_date = empty($start_date) ? 'now' : $start_date;

        if( $date = new DateTime($start_date, lucidlms_timezone()) ){
            $this->start_date = $date;
            return true;
        }
        return false;
    }

	/**
     * @return DateTime|null
     */
    public function get_complete_date(){
        return $this->complete_date;
    }

    public function update_complete_date(){
        if(  $this->id && ($this->complete_date instanceof DateTime) ){
            update_post_meta($this->id, '_complete_date', $this->complete_date->format('Y-m-d H:i:s'));
            return true;
        }
        return false;
    }

    /**
     * When the user completed a course
     * @param $complete_date string
     * @return bool
     */
    public function set_complete_date($complete_date = 'now'){
        if( $this->get_status() == 'sc_completed' && $date = new DateTime($complete_date, lucidlms_timezone()) ){
            $this->complete_date = $date;
            return true;
        }
        return false;
    }

    /**
     * @return DateTime|null
     */
    public function get_expire_date(){
        return $this->expire_date;
    }

    /**
     * Update expire date in the database
     * @return bool
     */
    public function update_expire_date(){
        if(  $this->id && ( $this->expire_date instanceof DateTime) ){
            update_post_meta($this->id, '_expire_date', $this->expire_date->format('Y-m-d H:i:s'));
            update_post_meta($this->id, '_expire_date_timestamp', $this->expire_date->getTimestamp() + $this->expire_date->getOffset()); // we need it for queries
            return true;
        }
        return false;
    }

    /**
     * Date when the score card would automatically get expired status
     * @param $date_str string|null
     * @return bool
     */
    public function set_expire_date($date_str = null){
        $new_expire_date = null;

        if( is_null($date_str) ){ //count date that depends on course duration, expiration dates etc

            // set new expire date if there any limitations
            if ( $this->course->availability_time ) {
                $new_expire_date = date_create('+' . $this->course->availability_time . ' days', lucidlms_timezone());
            }
        } else {
		  	if ( $this->course->availability_time ) {
			  	$new_expire_date = date_create($date_str, lucidlms_timezone());
			} else {
			  	$new_expire_date = null;
			}
        }

        if( $new_expire_date ) {
            $this->expire_date = $new_expire_date;
            return true;
        }
        return false;
    }


    /**
     * ID of related order that was bought by student
     * @return null|int
     */
    public function get_order_id(){
        return $this->order_id;
    }

    public function set_order_id($order_id){
        if( get_post($order_id) ){
            $this->order_id = $order_id;
            return true;
        }
        return false;
    }

    public function update_order_id(){
        if( $this->id ){
            update_post_meta($this->id, '_order_id', $this->order_id);
            return true;
        }
        return false;
    }


    public function get_certificate_link(){
        if( $this->id ){
            return sprintf('%s/?scorecard_id=%s&n=%s', get_bloginfo( 'url' ), $this->id, wp_create_nonce( 'certificate_download' ) );
        }
        return '#';
    }

    public function is_ready_to_complete(){

        $complete_course = true;
        $threshold_type    = $this->course->threshold_type;

        switch ( $threshold_type ) {

            case 'final':

                // check if final quiz is scorecard is completed
                $final_quiz = $this->course->get_final_element_of_type( 'quiz' );
                if ( ! isset( $this->score_card_elements[ $final_quiz->id ] ) || $this->score_card_elements[ $final_quiz->id ]->status !== 'completed' ) {
                    $complete_course = false;
                }
                break;

            case 'average':
            case 'none':
            default:

                // Check if all elements exist in scorecard and are completed
                if ( ! $this->course->get_elements_list() ) {
                    $complete_course = false;
                    break;
                }

                foreach ( $this->course->get_elements_list() as $id => $element ) {
                    if ( ! isset( $this->score_card_elements[ $id ] ) || $this->score_card_elements[ $id ]->status !== 'completed' ) {
                        $complete_course = false;
                        break;
                    }
                }
                break;
        }

        return $complete_course;

    }
    /**
     * Updates all data as default course element, also the meta is updated
     * @return int|null
     */
    public function flush(){

        // update or create the post title
        // for ex. if the customer was changed to change his name in the post title
        if( !$this->title) {
            $this->title = $this->generate_title();
        }

        if( !$this->start_date ){
            $this->set_start_date('now');
        }

        if( !$this->expire_date ){
            $this->set_expire_date();
        }

        $post = array(
            'post_title'     => $this->title, // The title of your post.
            'post_status'    => 'publish',
            'post_type'      => 'score_card',
            'tax_input'      => array(SCSTATUS => $this->status),
        );

        if( $this->id ){ // to update the existing post
            $post['ID'] = $this->id;
        }

        if( $post_id = wp_insert_post($post, false) ){

            $this->id = $post_id;
            $this->post = get_post($post_id);

            if( ('course' == $this->course->course_type) && ('sc_completed' != $this->get_status()) && $this->is_ready_to_complete() ){
                $this->set_status( 'sc_completed' );

                // Send email with certificate once completed
                do_action( 'lucidlms_course_completed', $this );
            }

            $this->update_status();
            $this->update_course_id();
            $this->update_student_id();

            $this->update_start_date();
            $this->update_expire_date();
            $this->update_complete_date();

            $this->update_order_id();

            $this->update_score_card_elements();

            return $post_id;
        } else {
            return null;
        }

    }
}