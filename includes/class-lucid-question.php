<?php
/**
 * Abstract Course Element Class
 *
 * The question class handles individual question data.
 *
 * @class 		LU_Question
 * @version		1.0.0
 * @package		LucidLMS/Abstracts
 * @author 		New Normal
 */
class LU_Question {

    /** @var int The question (post) ID. */
    public $id = null;

    /** @var object The actual post object. */
    public $post = null;

    /**
     * @var array Metadata of the post
     */
    public $post_meta = array();

    /**
     * @var string main part of question (post title)
     */
    public $question_text = '';

    /**
     * @var string question description. Might consist embeds etc
     */
    public $question_text_extended = '';

    /**
     * Type of question: i.e. single_choice, multiple_choice, open
     * @var string Type of question
     */
    protected $question_type = '';

    /**
     *
     * Name of question type to display
     * @var string
     */
    protected $question_type_name = '';

    /**
     * The structure is
     * array( {{uniq_id}} => array( 'answer' => {{your_answer_text}}, 'answer_extended' => {{your_extended_answer_text}}, 'is_correct' => true|false,
    ); ), ... )
     * @var array All answers are stored here
     */
    public $answers = array();

    /**
     * List of categories for a question
     * @var array
     */
    protected $categories = null;

    /**
     * List of courses that the question belongs to
     * @var array|null
     */
    protected $courses = null;

    /**
     * Constructor gets the post object and sets the ID for the loaded question.
     *
     * @access public
     * @param int|LU_Question|WP_Post $question Question ID, post object, or question object
     * @param array|string $args
     */
    public function __construct( $question = null, $args = array() ) {
        if ( is_numeric( $question ) ) {
            $this->id   = absint( $question );
            $this->post = get_post( $this->id );
        } elseif ( $question instanceof LU_Course_Element ) {
            $this->id   = absint( $question->id );
            $this->post = $question;
        } elseif ( $question instanceof WP_Post || isset( $question->ID ) ) {
            $this->id   = absint( $question->ID );
            $this->post = $question;
        }

        $this->initialize($args);

    }

    /**
     * Set data by post id etc
     * @param array $args
     */
    public function initialize($args = array()){
        $this->id = empty($this->id) && !empty($this->post) ? absint($this->post->ID) : $this->id;
        $this->post = empty($this->post) && !empty($this->id) ? get_post($this->id) : $this->post;

        if ( !empty($this->post) && !empty($this->id)){
            $this->question_text = $this->post->post_title;
            $this->question_text_extended = $this->post->post_content;


            if( $terms = wp_get_post_terms($this->id, QTYPE) ) {
                $term = current( $terms );
                $this->question_type = isset( $term->slug ) ? $term->slug : 'open';
                $this->question_type_name = isset( $term->name ) ? $term->name : '';
            }

            $this->post_meta = get_post_meta($this->id);

            $this->answers = ( isset($this->post_meta['_answers'][0]) && is_array($this->post_meta['_answers'])) ? unserialize($this->post_meta['_answers'][0]) : array();

            $this->categories = $this->get_categories();
        }

        if( !empty($args) ){
            $args = wp_parse_args($args, array(
                'question_text' => '',
                'question_text_extended' => '',
                'question_type' => apply_filters('lucidlms_default_question_type', 'open'),
                'answers' => array(),
            ));


            $this->question_text = isset($args['question_text']) ? $args['question_text'] : $this->question_text;
            $this->question_text_extended = isset($args['question_text_extended']) ? $args['question_text_extended'] : $this->question_text_extended;

            if( isset($args['question_type']) ){
                $this->set_question_type( $args['question_type'] );
            }
            if( !empty($args['answers']) && is_array($args['answers'])){
                $this->answers = array();
                foreach( $args['answers'] as $answer){
                    $this->add_answer( $answer);
                }
            }
        }

    }

	/**
	 * Add an answer to answers array (assign an unique key)
	 *
	 * @param array $answer
	 * @param       $key
	 *
	 * @return string
	 */
	public function add_answer( $answer = array(), $key = false ) {

		if ( ! $key ) {
			do {

				$key = uniqid( 'q_' );

			} while ( isset( $this->answers[ $key ] ) );
		}

		$defaults = array(
			'answer'          => '',
			'answer_extended' => '',
			'is_correct'      => false,
		);

		$this->answers[ $key ] = wp_parse_args( $answer, $defaults );

		return $key;
	}

    /**
     * Remove answer by id
     * @param $key
     * @return bool
     */
    public function remove_answer($key){
        if( isset($this->answers[$key]) ){
            unset($this->answers[$key]);
            return true;
        }
        return false;
    }

    public function update_answers_meta(){
        update_post_meta($this->id, '_answers', $this->answers);
    }

    public function get_correct_answers(){
        $correct = array();
        if( !empty($this->answers) ){
            foreach( $this->answers as $key => $value ){
                if( $value['is_correct'] ){
                    $correct[$key] = $value;
                }
            }
        }
        return $correct;
    }

    /**
     * Mark question keys as correct
     * @param array $correct_keys
     * @internal param array $keys
     */
    public function set_correct_answers( $correct_keys = array() ){
        if( !empty($this->answers) ){
            foreach( $this->answers as $key => &$answer){
                $answer['is_correct'] = in_array($key, $correct_keys) ? true : false;
            }
        }
    }

    /**
     * Set one answer as correct or not. Correct by default
     * @param $key
     * @param bool $is_correct
     */
    public function set_answer_correctness($key, $is_correct = true){
        if ( !empty($key) && isset($this->answers[$key]) ){
            $this->answers[$key]['is_correct'] = $is_correct;
        }
    }

	/**
	 * Checks the question type.
	 *
	 * @access public
	 * @param mixed $type Array or string of types
	 * @return bool
	 */
	public function is_type( $type ) {
		return ( $this->question_type == $type ) ? true : false;
	}

	/**
	 * Returns string with question type
	 * @return string
	 */
	public function get_type() {
		return $this->question_type;
	}

    /**
     * Set question type and affect question type name
     * @param $new_type
     */
    public function set_question_type( $new_type ){

        $this->question_type = $new_type;
        $this->question_type_name = LU()->taxonomies->get_term_name(QTYPE, $this->question_type);

    }

    /**
     * Fetch list of courses from database that the question belongs to
     * @return array
     */
    public function set_courses(){
        global $wpdb;

        // @todo: redo structure of questions and course element in meta to fetch parent course w/o this query
        $sql = '
            SELECT pm.post_id as course_element_id , pm1.post_id as course_id, course.post_title as course_title
            FROM ' . $wpdb->postmeta . ' pm
                JOIN ' . $wpdb->postmeta . ' pm1 ON pm1.meta_key = "_elements_ids" AND pm1.meta_value LIKE CONCAT("%i:", pm.post_id, "%")
                JOIN ' . $wpdb->posts . ' course ON pm1.post_id = course.ID AND course.post_status IN ("publish", "draft", "not_active")
            WHERE
                pm.meta_key = "_question_ids"
                AND pm.meta_value LIKE "%i:' .$this->id . '%"
        ';

        $this->courses = array();

        if( $results = $wpdb->get_results($sql) ){
            foreach( $results as $row ){
                    $this->courses[$row->course_id]['title'] = $row->course_title;
                    $this->courses[$row->course_id]['course_elements'][] = $row->course_element_id;
            }
        }
    }

    /**
     * Get array of courses with ids as keys and names as values
     * @return array|null
     */
    public function get_courses(){
        if( is_null($this->courses) ){
            $this->set_courses();
        }
        return $this->courses;
    }

    /**
     * Fetch question's categories list and pass it to the categories property
     */
    public function set_categories(){
        $cat_terms = wp_get_post_terms($this->id, 'question_cat');
        $this->categories = array();
        if( $cat_terms && !is_wp_error($cat_terms) ){
            foreach($cat_terms as $term){
                $this->categories[ $term->term_id ] = $term->name;
            }
        }
    }

    /**
     * Get list of categories where the question belongs to
     * @return array
     */
    public function get_categories(){
        if( is_null($this->categories) ){
            $this->set_categories();
        }
        return $this->categories;
    }

    /**
     * Add current question to the categories
     * @param $category_ids array
     * @return bool
     */
    public function add_to_categories( Array $category_ids ){
        // string values to int to prevent adding '20', '27' as terms name
        $category_ids = array_map('intval', $category_ids);

        $res = wp_set_object_terms( $this->id, $category_ids, 'question_cat', true );

        if( $res && !is_wp_error($res) ){
            $this->set_categories();
            return true;
        }
        return false;
    }

    /**
     * Remove question from a category
     * @param $category_id
     * @return bool
     */
    public function remove_category( $category_id ){
        $result = wp_remove_object_terms( $this->id, $category_id, 'question_cat' );
        if( $result && !is_wp_error($result) ){
            unset( $this->categories[ $category_id ] );
            return true;
        }
        return false;
    }

    /**
     * Add question to a category
     * @param $category_id
     * @return bool
     */
    public function add_category( $category_id ){
        return $this->add_to_categories( array($category_id) );
    }

    /**
     * Returns slug of question type
     * @return string
     */
    public function get_question_type(){
        return $this->question_type;
    }

    /**
     * Returns nice question type for displaying
     * @return string
     */
    public function get_question_type_name(){
        return $this->question_type_name;
    }
    /**
     * Update question type in the database
     * @internal param string $new_question_type apply new type before update
     * @return bool
     */
    public function update_question_type(){
        if( $this->id ){
            if( !is_wp_error( wp_set_post_terms($this->id, $this->question_type, QTYPE) ) ){
                return true;
            }
        }

        return false;
    }

    /**
     * Remove the question from anywhere
     */
    public function remove_self(){
        $courses = $this->get_courses();

        if( $this->id && wp_delete_post($this->id) ){

            // if there are connected courses, remove question from them
            if( !empty($courses) ){
                foreach( $courses as $course ){
                    foreach( $course['course_elements'] as $course_element_id ){
                        if( $course_element = get_course_element( $course_element_id ) ) {
                            // do not remove question, because we just removed it
                            // but remove link to the question from a quiz
                            $course_element->unassign_question($this->id);
                        }

                    }
                }
            }

            $this->id = null;
            $this->post = null;
            $this->initialize();
        }
    }
    /**
     * Create new question in the database or update existing
     * @return int|null inserted post id
     */
    public function flush(){
        $post = array(
            'post_content'   => $this->question_text_extended, // The full text of the post.
            'post_title'     => $this->question_text, // The title of your post.
            'post_status'    => 'publish',
            'post_type'      => 'question',
            'tax_input'      => array('question_type' => $this->question_type),
        );
        if( $this->id ){ // to update the existing post
            $post['ID'] = $this->id;
        }
        if( $post_id = wp_insert_post($post, false) ){
            $this->id = $post_id;
            $this->update_answers_meta();
            $this->update_question_type();
            return $post_id;
        } else {
            return null;
        }

    }

    public static function get_available_question_types(){

        return LU()->taxonomies->get_terms( QTYPE, true );
    }

    /**
     * Checks if all needed data (question text, answers) exist
     * @return bool
     */
    public function has_content(){
        if( !empty($this->question_text) ){
            if( !empty($this->answers) || ('open' == $this->question_type) ){
                return true;
            }
        }
        return false;
    }

    /**
     * Returns base object data in simple array to use for templates etc
     * @internal param bool $with_courses
     * @return array
     */
    public function get_data(){
        return array(
            'question_type' => $this->question_type,
            'question_type_name' => $this->question_type_name,
            'question_text' => $this->question_text,
            'question_text_extended' => $this->question_text_extended,
            'has_content' => $this->has_content(),
            'answers' => $this->answers,
            'courses' => $this->get_courses(),
            'categories' => $this->get_categories(),
        );
    }
}
