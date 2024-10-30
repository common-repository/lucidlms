<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * LucidLMS LU_AJAX
 *
 * AJAX Event Handler
 *
 * @class          LU_AJAX
 * @version        1.0.0
 * @package        LucidLMS/Classes
 * @category       Class
 * @author         New Normal
 */
class LU_AJAX {

	/**
	 * Hook into ajax events
	 */
	public function __construct() {

		// lucidlms_EVENT => nopriv (for frontend)
		$ajax_events = array(
			// Backend
			'create_course'              => FALSE,
			'create_course_element'      => FALSE,
			'remove_course_element'      => FALSE,
			'get_course_elements'        => FALSE,
			'create_question'            => FALSE,
			'edit_question'              => FALSE,
            'insert_questions_modal'     => FALSE,
            'insert_questions'           => FALSE,
            'question_remove_category'   => FALSE,
            'question_add_category'      => FALSE,
			'save_edit_question'         => FALSE,
			'remove_question'            => FALSE,
			'get_questions'              => FALSE,
			'change_course_type'         => FALSE,
			'filter_questions'           => FALSE,
			'change_course_element_type' => FALSE,
			'reorder_course_elements'    => FALSE,
			'reorder_questions'          => FALSE,
			'get_available_courses'      => FALSE,
			'change_course_status'       => FALSE,
            'edit_question_category'     => FALSE,
            'remove_question_category'   => FALSE,
            'create_new_question_category'      => FALSE,
            'manage_questions_categories_modal' => FALSE,
			// Frontend
			'save_time_spent_lesson'     => TRUE,
			'save_answer'                => TRUE,
			'update_review_screen'       => TRUE,
			'complete_quiz'              => TRUE,
			'get_all_courses'            => TRUE,
			'get_category_courses'       => TRUE,
			'get_searched_courses'       => TRUE

		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_' . $ajax_event, array( $this, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_' . $ajax_event, array(
					$this,
					$ajax_event
				) );
			}
		}
	}

	/**
	 * Output headers for JSON requests
	 */
	private function json_headers() {
		header( 'Content-Type: application/json; charset=utf-8' );
	}

	/**
	 * Method to easily throw an error and stop a script
	 *
	 * @param      $error_id string unique (text) id to define an error type, might be used to handle an error on frontend
	 * @param      $message  string message for user
	 * @param bool $die      to stop ajax executing or not
	 */
	protected function output_error_data( $error_id, $message, $die = TRUE ) {
		$this->json_headers();
		echo serialize( array( $error_id => $message ) );

		if ( $die ) {
			die();
		}
	}


	/****************************************************
	 * Backend ajax calls
	 ***************************************************/

	/**
	 * Create course via ajax
	 */
	public function create_course() {

		// Add nonce security to the request
		$nonce = isset( $_POST[ 'create_course_nonce' ] ) ? esc_html( $_POST[ 'create_course_nonce' ] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'create-course' ) ) {
			die( '' );
		}

		$course_type = isset( $_POST[ 'type' ] ) ? sanitize_text_field( $_POST[ 'type' ] ) : '';
		$course_name = isset( $_POST[ 'name' ] ) ? sanitize_text_field( $_POST[ 'name' ] ) : '';

		$result = array();

		if ( ! $course_type ) { // handle empty question type

			$result[ 'course_type_empty' ] = __( 'The course type is empty', 'lucidlms' );

		}
		elseif ( ! $course_name ) { // handle empty question_text

			$result[ 'course_name_empty' ] = __( 'The course name is empty', 'lucidlms' );

		}
		else { // no errors

			$my_post = array(
				'post_title'  => $course_name,
				'post_name'   => sanitize_title( $course_name ),
				'post_status' => 'draft',
				'post_type'   => 'course'
			);

			// Insert the post into the database
			$post_id = wp_insert_post( $my_post );

			// Get newly create course
			$course = get_course( $post_id );

			// Set it's type based on provided value
			$course->set_type( $course_type );

			// Set default category
			wp_set_object_terms( $post_id, 'other', 'course_cat' );

			// response html
			include 'admin/views/dashboard/html-admin-page-dashboard-categories.php';
		}

		if ( ! empty( $result ) ) {
			$this->json_headers();
			echo json_encode( $result );
		}

		die();
	}

	/**
	 * Create course element via ajax
	 */
	public function create_course_element() {

		// Add nonce security to the request
		$nonce = isset( $_POST[ 'create_course_element_nonce' ] ) ? esc_html( $_POST[ 'create_course_element_nonce' ] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'create-course-element' ) ) {
			die( '' );
		}


		// Check for current course id
		if ( isset( $_POST[ 'post_id' ] ) ) {

			$post_id     = intval( $_POST[ 'post_id' ] );
			$post        = get_post( $post_id );
			$course_type = isset( $_POST[ 'type' ] ) ? sanitize_text_field( $_POST[ 'type' ] ) : '';
			$course_name = isset( $_POST[ 'name' ] ) ? sanitize_text_field( $_POST[ 'name' ] ) : '';

			$result = array();

			if ( ! $post ) { // handle wrong post id error

				$result[ 'wrong_post_id' ] = __( 'Wrong post id passed', 'lucidlms' );

			}
			elseif ( ! $course_type ) { // handle empty question type

				$result[ 'empty_post_id' ] = __( 'The course type is empty', 'lucidlms' );

			}
			elseif ( ! $course_name ) { // handle empty question_text

				$result[ 'course_name_empty' ] = __( 'The course name is empty', 'lucidlms' );

			}
			else { // no errors
				if ( $course = get_course( $post_id ) ) {

					if ( 'course' != $course->course_type ) { // handle when course can't have elements in it
						$result[ 'wrong_course_type' ] = __( 'This course type doesn\'t support elements', 'lucidlms' );
					}

					$args = array(
						'title' => $course_name,
					);

					if ( $course->add_element( $course_type, $args ) ) { // yeaaaah!! We did it!
						if ( $course_elements = $course->get_elements_list() ) { // lets show the template
							include 'admin/post-types/meta-boxes/views/html-course-elements.php';
						}

					}
					else { //wazzup??
						$result[ 'cant_add_question' ] = __( 'An error has been occurred, please check the data and try again', 'lucidlms' );
					}
				}
				else { // no course element found
					$result[ 'no_course_element_found' ] = __( 'No course element found. Are you sure that the post still exists?', 'lucidlms' );
				}
			}

			if ( ! empty( $result ) ) {
				$this->json_headers();
				echo json_encode( $result );
			}


		}

		die();
	}

	/**
	 * Remove course element
	 */
	public function remove_course_element() {

		// Add nonce security to the request
		$nonce = isset( $_POST[ 'remove_course_element_nonce' ] ) ? esc_html( $_POST[ 'remove_course_element_nonce' ] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'remove-course-element' ) ) {
			die( '' );
		}


		// Check for current course id
		if ( isset( $_POST[ 'post_id' ] ) && isset( $_POST[ 'course_element_id' ] ) ) {
			$post_id    = intval( $_POST[ 'post_id' ] );
			$element_id = intval( $_POST[ 'course_element_id' ] );

			$result = array();

			if ( ! get_post( $post_id ) ) { // handle wrong post id error

				$result[ 'wrong_post_id' ] = __( 'Wrong post id passed', 'lucidlms' );

			}
			else { // no errors

				if ( $course = get_course( $post_id ) ) {

					if ( 'course' != $course->course_type ) { // handle when course can't have question in it
						$result[ 'wrong_course_type' ] = __( 'This course type doesn\'t support elements', 'lucidlms' );
					}

					if ( $course->remove_element( $element_id ) ) { // yeaaaah!! We did it!

						if ( $course_elements = $course->get_elements_list() ) { // lets show the template
							include 'admin/post-types/meta-boxes/views/html-course-elements.php';
						}

					}
					else { //wazzup??
						$error[ 'cant_remove_course_element' ] = __( 'An error has been occurred, please check the data and try again', 'lucidlms' );
					}
				}
				else { // no course found
					$error[ 'no_course_element_found' ] = __( 'No course element found. Are you sure that the post still exists?', 'lucidlms' );
				}

			}

			if ( ! empty( $result ) ) {
				$this->json_headers();
				echo json_encode( $result );
			}
		}
		die();
	}

	/**
	 * Get course elements
	 */
	public function get_course_elements() {

		// Add nonce security to the request
		$nonce = isset( $_POST[ 'get_course_elements_nonce' ] ) ? esc_html( $_POST[ 'get_course_elements_nonce' ] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'get-course-elements' ) ) {
			die( '' );
		}

		// Check for current course id
		if ( isset( $_POST[ 'post_id' ] ) ) {

			if ( $course = get_course( intval( $_POST[ 'post_id' ] ) ) ) {
				if ( 'course' == $course->course_type ) { // handle when course can't have elements in it
					if ( $course_elements = $course->get_elements_list() ) {
						include 'admin/post-types/meta-boxes/views/html-course-elements.php';
					}
				}
			}

		}

		die();
	}

	/**
	 * Create a question
     * @TODO: handle errors properly. Get rid of this bunch of ifs
	 */
	public function create_question() {
		global $post;

		// Add nonce security to the request
		$nonce = isset( $_POST[ 'create_question_nonce' ] ) ? esc_html( $_POST[ 'create_question_nonce' ] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'create-question' ) ) {
			die( '' );
		}

		// Check for current course element id
		if ( isset( $_POST[ 'name' ] ) && isset( $_POST[ 'type' ] ) ) {
			$post_id       = intval( $_POST[ 'post_id' ] );
			$post          = get_post( $post_id );
			$question_type = isset( $_POST[ 'type' ] ) ? sanitize_text_field( $_POST[ 'type' ] ) : '';
			$question_text = isset( $_POST[ 'name' ] ) ? sanitize_text_field( $_POST[ 'name' ] ) : '';
            $is_question_pool = isset( $_POST[ 'is_question_pool' ] ) && ( 'true' == $_POST[ 'is_question_pool' ]) ? true : false;

            // sanitize categories input array
            $categories = isset($_POST['selected_categories']) ? (array) $_POST['selected_categories'] : array();
            $categories = array_map( 'sanitize_text_field', $categories);

            // sanitize courses input array
            $courses = isset($_POST['selected_courses']) ? (array) $_POST['selected_courses'] : array();
            $courses = array_map( 'sanitize_text_field', $courses);

            $error = array();

			if ( ! $question_type ) { // handle empty question type

                $error[ 'empty_post_id' ] = __( 'The question type is empty', 'lucidlms' );

			}
			elseif ( ! $question_text ) { // handle empty question_text

                $error[ 'question_body_empty' ] = __( 'The question is empty', 'lucidlms' );

			} else { // no errors
                $args = array(
                    'question_text' => $question_text,
                    'question_type' => $question_type,
                );
                if( !$is_question_pool ) {
                    if ( $course_element = get_course_element( $post_id ) ) {
                            if ( 'quiz' != $course_element->course_element_type ) { // handle when course can't have question in it
                                $error[ 'wrong_course_element_type' ] = __( 'This course element type doesn\'t support questions', 'lucidlms' );
                            }

                            if( $course_element->add_question( $args ) ){
                                $questions = $course_element->get_questions_list(); // lets show the template
                                include 'admin/post-types/meta-boxes/views/html-course-elements-question.php';
                            } else { //wazzup??
                                $error[ 'cant_add_question' ] = __( 'An error has been occurred, please check the data and try again', 'lucidlms' );
                            }
                    } else { // no course element found
                        $error[ 'no_course_element_found' ] = __( 'No course element found. Are you sure that the post still exists?', 'lucidlms' );
                    }

                } else {
                    // add question at question pool page without assigned category
                    $question = new LU_Question(null, $args);
                    if( $question->flush() ){
                        // set current filter's categories to question
                        $question->add_to_categories( $categories );

                        $questions = get_questions_by_category_and_courses($categories, $courses);
                        $categories = get_all_question_categories();

                        include 'admin/views/question-pool/html-admin-question-pool-questions.php';

                    } else {
                        $error[ 'cant_create_question' ] = __( 'Can\'t persist the question to the database', 'lucidlms' );
                    }
                }
            }

			if ( ! empty( $error ) ) {
				$this->json_headers();
				echo json_encode( $error );
			}

		}

		die();
	}

	/**
	 * Output edit question html
	 */
	public function edit_question() {

		// Add nonce security to the request
		$nonce = isset( $_POST[ 'edit_question_nonce' ] ) ? esc_html( $_POST[ 'edit_question_nonce' ] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'edit-question' ) ) {
			die( '' );
		}

		// Check for current course id
		if ( isset( $_POST[ 'question_id' ] ) ) {

			// response html
			LU_Meta_Box_Course_Element::output_question( $_POST[ 'question_id' ] );

		}

		die();
	}

    /**
     * Output "Insert from Question Pool modal"
     */
    public function insert_questions_modal(){
        // Add nonce security to the request
        $nonce = isset( $_POST[ 'insert_questions_modal_nonce' ] ) ? esc_html( $_POST[ 'insert_questions_modal_nonce' ] ) : '';
        if ( ! wp_verify_nonce( $nonce, 'insert-questions-modal' ) ) {
            die( '' );
        }

        $exclude_questions = isset($_POST['exclude_questions']) ? (array) $_POST['exclude_questions'] : array();
        $exclude_questions = array_map( 'intval', $exclude_questions );

        $categories = get_all_questions_by_categories($exclude_questions);
        include 'admin/views/question-pool/html-admin-insert-questions-modal.php';

        die();
    }

    /**
     * Question Pool / Manage Categories
     * create new category
     */
    public function create_new_question_category(){
        // Add nonce security to the request
        $nonce = isset( $_POST[ 'create_new_question_category_nonce' ] ) ? esc_html( $_POST[ 'create_new_question_category_nonce' ] ) : '';
        if ( ! wp_verify_nonce( $nonce, 'create-new-question-category' ) ) {
            die( '' );
        }
        $result = array();
        if( isset( $_POST['category_name'] ) ){
            $category_name = sanitize_text_field( $_POST['category_name'] );

            if( $category_id = create_question_category($category_name) ){
                $result['category_id'] = $category_id;
            }
        }

        if( !isset($result['category_id']) ){
            $result['error'] = __('Cannot create new category. Please, try again', 'lucidlms');
        }

        $this->json_headers();
        echo json_encode( $result );

        die();
    }

    /**
     * Question Pool / Manage Categories
     * remove category
     */
    public function remove_question_category(){
        // Add nonce security to the request
        $nonce = isset( $_POST[ 'remove_question_category_nonce' ] ) ? esc_html( $_POST[ 'remove_question_category_nonce' ] ) : '';
        if ( ! wp_verify_nonce( $nonce, 'remove-question-category' ) ) {
            die( '' );
        }

        $result = array();

        if( isset( $_POST['category_id'] ) ){
            $category_id = intval( $_POST['category_id'] );

            if( remove_question_category($category_id) ){
                $result['success'] = true;
            }
        }

        if( !isset($result['success']) ){
            $result['success'] = false;
            $result['error'] = __('Cannot delete category. Please, try again', 'lucidlms');
        }

        $this->json_headers();
        echo json_encode( $result );

        die();
    }

    /**
     * Question Pool / Manage Categories
     * edit category name
     */
    public function edit_question_category(){
        // Add nonce security to the request
        $nonce = isset( $_POST[ 'edit_question_category_nonce' ] ) ? esc_html( $_POST[ 'edit_question_category_nonce' ] ) : '';
        if ( ! wp_verify_nonce( $nonce, 'edit-question-category' ) ) {
            die( '' );
        }

        $result = array();

        if( isset( $_POST['category_id'] ) && isset($_POST['new_category_name']) ){
            $category_id = intval( $_POST['category_id'] );
            $new_category_name = sanitize_text_field( $_POST['new_category_name'] );

            if( !empty($category_id) && !empty($new_category_name) ){

                if( rename_question_category($category_id, $new_category_name) ){
                    $result['success'] = true;
                }
            }

        }

        if( !isset($result['success']) ){
            $result['success'] = false;
            $result['error'] = __('Cannot delete category. Please, try again', 'lucidlms');
        }

        $this->json_headers();
        echo json_encode( $result );

        die();
    }

    /**
     * Output "Insert from Question Pool modal"
     */
    public function manage_questions_categories_modal(){
        // Add nonce security to the request
        $nonce = isset( $_POST[ 'manage_questions_categories_modal_nonce' ] ) ? esc_html( $_POST[ 'manage_questions_categories_modal_nonce' ] ) : '';
        if ( ! wp_verify_nonce( $nonce, 'manage-questions-categories-modal' ) ) {
            die( '' );
        }

        $categories = get_all_question_categories();
        include 'admin/views/question-pool/html-admin-manage-questions-categories-modal.php';

        die();
    }

    /**
     * Insert selected questions from question pool to the quiz
     */
    public function insert_questions(){
        // Add nonce security to the request
        $nonce = isset( $_POST[ 'insert_questions_nonce' ] ) ? esc_html( $_POST[ 'insert_questions_nonce' ] ) : '';
        if ( ! wp_verify_nonce( $nonce, 'insert-questions' ) ) {
            die( '' );
        }

        if( isset($_POST['post_id']) && isset($_POST['questions']) ){
            $post_id = intval( $_POST['post_id'] );

            $questions = isset($_POST['questions']) ? (array) $_POST['questions'] : array();
            $questions = array_map('sanitize_text_field', $questions);

            if( $course_element = get_course_element( $post_id ) ){
                $course_element->add_questions($questions);

                $questions = $course_element->get_questions_list(); // lets show the template
                include 'admin/post-types/meta-boxes/views/html-course-elements-question.php';
            }
        }

        die();
    }

    /**
     * Question Pool
     * Remove question from a category
     */
    public function question_remove_category(){
        // Add nonce security to the request
        $nonce = isset( $_POST[ 'question_remove_category_nonce' ] ) ? esc_html( $_POST[ 'question_remove_category_nonce' ] ) : '';
        if ( ! wp_verify_nonce( $nonce, 'question-remove-category' ) ) {
            die( '' );
        }

	    $result = array();
        if( isset($_POST['question_id']) && isset($_POST['category_id']) ){
            $question_id = intval( $_POST['question_id'] );
            $category_id = intval( $_POST['category_id'] );

            if( $question = get_question($question_id) ){
                if( $question->remove_category( $category_id ) ){
	                $result['success'] = true;
                } else {
	                $result['success'] = false;
	                $result['error'] = __('Cannot remove a category. Please, try again', 'lucidlms');
                }
            } else {
	            $result['success'] = false;
	            $result['error'] = __('Question not found', 'lucidlms');
            }
        }
	    if( !isset($result['success']) ){
		    $result['success'] = false;
		    $result['error'] = __('Unable to remove category for questions', 'lucidlms');
        }

	    $this->json_headers();
	    echo json_encode( $result );

	    die();
    }

    /**
     * Question Pool
     * Add question to a category
     */
    public function question_add_category(){
        // Add nonce security to the request
        $nonce = isset( $_POST[ 'question_add_category_nonce' ] ) ? esc_html( $_POST[ 'question_add_category_nonce' ] ) : '';
        if ( ! wp_verify_nonce( $nonce, 'question-add-category' ) ) {
            die( '' );
        }
		$result = array();
        if( isset($_POST['question_id']) && isset($_POST['category_id']) ){
            $question_id = intval( $_POST['question_id'] );
            $category_id = intval( $_POST['category_id'] );
	        if( $question = get_question($question_id) ){

		        if( $question->add_category( $category_id ) ){
			        $result['success'] = true;
                } else {
			        $result['success'] = false;
			        $result['error'] = __( 'Unable to add question to the category. Please, try again', 'lucidlms' );
                }
            } else {
		        $result['success'] = false;
		        $result['error'] = __('Question not found', 'lucidlms');
            }
        }
	    if( !isset($result['success']) ){
		    $result['success'] = false;
		    $result['error'] = __('Unable to add category for the question', 'lucidlms');
        }

	    $this->json_headers();
	    echo json_encode( $result );

        die();
    }

	/**
	 * Save question via ajax
	 */
	public function save_edit_question() {

		// Add nonce security to the request
		$nonce = isset( $_POST[ 'save_edit_question_nonce' ] ) ? esc_html( $_POST[ 'save_edit_question_nonce' ] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'save-edit-question' ) ) {
			die( '' );
		}

		/**
		 * Get variables from $_POST['question']
		 *
		 * @var int    $question_id
		 * @var array  $_answers
		 * @var string $_question_text_extended
		 */
		parse_str( $_POST[ 'question' ] );

		// Check for current course id
		if ( isset( $question_id ) ) {

			$question          = new LU_Question( intval( $question_id ) );
			$question->answers = array(); // We clear answer even user did not added new ones (handles remove functionality)
			if ( is_array( $_answers ) && ! empty( $_answers ) ) {
				foreach ( $_answers as $id => $answer_data ) {
					$answer = array(
						'answer'          => isset( $answer_data[ 'answer' ] ) ? sanitize_text_field( $answer_data[ 'answer' ] ) : '',
						'answer_extended' => isset( $answer_data[ 'answer_extended' ] ) ? sanitize_text_field( $answer_data[ 'answer_extended' ] ) : '',
						'is_correct'      => isset( $answer_data[ 'is_correct' ] ) ? (bool) $answer_data[ 'is_correct' ] : NULL,
					);
					$question->add_answer( $answer, $id );
				}
			}

			if ( ! empty( $_question_text_extended ) ) {
				$question->question_text_extended = wp_unslash( $_question_text_extended );
			}

			if ( ! $question->flush() ) {
				$this->json_headers();
				echo serialize( array( 'error_on_flush' => __( 'An error has been occurred, please check data and try again', 'lucidlms' ) ) );
			}
		}

		die();
	}

	/**
	 * Remove questions
	 */
	public function remove_question() {

		// Add nonce security to the request
		if ( isset( $_POST[ 'remove_question_nonce' ] ) ) {
			$nonce = esc_html( $_POST[ 'remove_question_nonce' ] );
		} // End If Statement
		if ( ! wp_verify_nonce( $nonce, 'remove-question' ) ) {
			die( '' );
		} // End If Statement

		// Check for current course element id
		if ( isset( $_POST[ 'question_id' ] ) ) {

			$post_id     = isset( $_POST[ 'post_id' ] ) ? intval( $_POST[ 'post_id' ] ) : null;
			$question_id = intval( $_POST[ 'question_id' ] );

			$result = array();

            if( $post_id != null ){
                if ( $course_element = get_course_element( $post_id ) ) {

                    if ( 'quiz' != $course_element->course_element_type ) { // handle when course can't have question in it
                        $result[ 'wrong_course_element_type' ] = __( 'This course element type doesn\'t support questions', 'lucidlms' );
                    }

                    if ( $course_element->unassign_question( $question_id ) ) { // yeaaaah!! We did it!

                        if ( $questions = $course_element->get_questions_list() ) { // lets show the template
                            include 'admin/post-types/meta-boxes/views/html-course-elements-question.php';
                        }

                    }
                    else { //wazzup??
                        $result[ 'cant_remove_question' ] = __( 'An error has been occurred, please check the data and try again', 'lucidlms' );
                    }
                }
                else { // no course element found
                    $result[ 'no_course_element_found' ] = __( 'No course element found. Are you sure that the post still exists?', 'lucidlms' );
                }
            } else {
                $question = new LU_Question( $question_id );
                $question->remove_self();
            }


			if ( ! empty( $result ) ) {
				$this->json_headers();
				echo json_encode( $result );
			}


		}

		die();
	}

	/**
	 * Get questions
	 */
	public function get_questions() {

		// Add nonce security to the request
		$nonce = isset( $_POST[ 'get_questions_nonce' ] ) ? esc_html( $_POST[ 'get_questions_nonce' ] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'get-questions' ) ) {
			die( '' );
		}


		// Check for current course element id
		if ( isset( $_POST[ 'post_id' ] ) ) {

			if ( $course_element = get_course_element( intval( $_POST[ 'post_id' ] ) ) ) {
				if ( 'quiz' == $course_element->course_element_type ) {
					if ( $questions = $course_element->get_questions_list() ) { // lets show the template
						include 'admin/post-types/meta-boxes/views/html-course-elements-question.php';
					}
				}

			}
		}

		die();
	}

	/**
	 * Switch selectbox on course edit page
	 */
	public function change_course_type() {

		// Add nonce security to the request
		$nonce = isset( $_POST[ 'change_course_type_nonce' ] ) ? esc_html( $_POST[ 'change_course_type_nonce' ] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'change_course_type' ) ) {
			die( '' );
		}

		// Check for current course id
		if ( isset( $_POST[ 'post_id' ] ) && isset( $_POST[ 'type' ] ) ) {

			if ( $course = get_course( absint( $_POST[ 'post_id' ] ) ) ) {
				global $post;
				$post = $course->post;

				$course->set_type( sanitize_text_field( $_POST[ 'type' ] ) );

				// response html
				if ( isset( $_POST[ 'is_dashboard' ] ) && $_POST[ 'is_dashboard' ] === 'true' ) {
					$course = get_course( absint( $_POST[ 'post_id' ] ) );
					include dirname( LU_PLUGIN_FILE ) . '/includes/admin/views/dashboard/html-admin-page-dashboard-course-meta.php';
				}
				else {
					LU_Meta_Box_Course::output( $course->post );
				}
			}

		}

		die();
	}

	/**
	 * Questions filtered by category and course
	 */
	public function filter_questions() {

        // Add nonce security to the request
		$nonce = isset( $_POST[ 'filter_questions_nonce' ] ) ? esc_html( $_POST[ 'filter_questions_nonce' ] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'filter-questions' ) ) {
			die( '' );
		}

        // sanitize categories input array
        $categories = isset($_POST['selected_categories']) ? (array) $_POST['selected_categories'] : array();
        $categories = array_map( 'sanitize_text_field', $categories);

        // sanitize courses input array
        $courses = isset($_POST['selected_courses']) ? (array) $_POST['selected_courses'] : array();
        $courses = array_map( 'sanitize_text_field', $courses);

        $questions = get_questions_by_category_and_courses($categories, $courses);
        $categories = get_all_question_categories();

        include 'admin/views/question-pool/html-admin-question-pool-questions.php';

		die();
	}

    public function remove_question_from_category() {

        // Add nonce security to the request
        $nonce = isset( $_POST[ 'remove_question_from_category_nonce' ] ) ? esc_html( $_POST[ 'remove_question_from_category_nonce' ] ) : '';
        if ( ! wp_verify_nonce( $nonce, 'remove-question-from-category' ) ) {
            die( '' );
        }

        // sanitize categories input array
        $categories = isset($_POST['selected_categories']) ? (array) $_POST['selected_categories'] : array();
        $categories = array_map( 'sanitize_text_field', $categories);

        // sanitize courses input array
        $courses = isset($_POST['selected_courses']) ? (array) $_POST['selected_courses'] : array();
        $courses = array_map( 'sanitize_text_field', $courses);

        $questions = get_questions_by_category_and_courses($categories, $courses);
        $categories = get_all_question_categories();

        include 'admin/views/question-pool/html-admin-question-pool-questions.php';

        die();
    }

	/**
	 * Switch selectbox on course element edit page
	 */
	public function change_course_element_type() {

		// Add nonce security to the request
		$nonce = isset( $_POST[ 'change_course_element_type_nonce' ] ) ? esc_html( $_POST[ 'change_course_element_type_nonce' ] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'change_course_element_type' ) ) {
			die( '' );
		}


		// Check for current course id
		if ( isset( $_POST[ 'post_id' ] ) && isset( $_POST[ 'type' ] ) ) {

			if ( $course_element = get_course_element( absint( $_POST[ 'post_id' ] ) ) ) {
				global $post;
				$post = $course_element->post;

				$course_element->set_type( sanitize_text_field( $_POST[ 'type' ] ) );

				// response html
				LU_Meta_Box_Course_Element::output( $course_element->post );
			}

		}

		die();
	}

	/**
	 * Reorder course elements (just update ids meta order)
	 */
	public function reorder_course_elements() {

		// Add nonce security to the request
		$nonce = isset( $_POST[ 'reorder_course_elements_nonce' ] ) ? esc_html( $_POST[ 'reorder_course_elements_nonce' ] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'reorder-course-elements' ) ) {
			die( '' );
		}

		if ( isset( $_POST[ 'post_id' ] ) && isset( $_POST[ 'sorted_data' ] ) ) {

			if ( $course = get_course( intval( $_POST[ 'post_id' ] ) ) ) {
				if ( 'course' == $course->course_type ) { // handle when course can't have elements in it
					if ( $course_elements = $course->get_elements_list() ) {

						parse_str( $_POST[ 'sorted_data' ], $sorted_data );

						$course->update_elements_ids_field( $sorted_data[ 'element' ] );

					}
				}
			}
		}

		die();
	}

	/**
	 * Reorder questions (just update ids meta order)
	 */
	public function reorder_questions() {

		// Add nonce security to the request
		$nonce = isset( $_POST[ 'reorder_questions_nonce' ] ) ? esc_html( $_POST[ 'reorder_questions_nonce' ] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'reorder-questions' ) ) {
			die( '' );
		}

		if ( isset( $_POST[ 'post_id' ] ) && isset( $_POST[ 'sorted_data' ] ) ) {

			if ( $course_element = get_course_element( intval( $_POST[ 'post_id' ] ) ) ) {
				if ( 'quiz' == $course_element->course_element_type ) { // handle when course can't have elements in it
					if ( $questions = $course_element->get_questions_list() ) {

						parse_str( $_POST[ 'sorted_data' ], $sorted_data );

						$course_element->update_question_ids_field( $sorted_data[ 'question' ] );

					}
				}
			}
		}

		die();
	}

	/**
	 * Get available (for score card) courses based on student id
	 */
	public function get_available_courses() {

		// Add nonce security to the request
		$nonce = isset( $_POST[ 'get_available_courses_nonce' ] ) ? esc_html( $_POST[ 'get_available_courses_nonce' ] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'get-available-courses' ) ) {
			die( '' );
		}

		if ( isset( $_POST[ 'student_id' ] ) ) {
			$student_id = $_POST[ 'student_id' ];

			$started_scorecards = lucidlms_get_score_card( $student_id, array( 'status' => 'sc_started' ) );

			$exclude_courses = array();
			foreach ( $started_scorecards as $scorecard ) {
				$exclude_courses[] = $scorecard->get_course_id();
			}

			$courses = get_posts( array(
				'post_type'      => 'course',
				'post_status'    => 'publish',
				'posts_per_page' => '-1',
				'post__not_in'   => $exclude_courses
			) );
			$output  = '';
			foreach ( $courses as $possible_course ) {
				$output .= '<option value="' . $possible_course->ID . '">' . $possible_course->post_title . '</option>';
			}
			echo $output;

		}

		die();
	}

	/**
	 * Change course status based on post id
	 */
	public function change_course_status() {

		// Add nonce security to the request
		$nonce = isset( $_POST[ 'change_course_status_nonce' ] ) ? esc_html( $_POST[ 'change_course_status_nonce' ] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'change-course-status' ) ) {
			die( '' );
		}

		if ( isset( $_POST[ 'post_id' ] ) && isset( $_POST['new_status']) ) {
			$post_id = intval($_POST[ 'post_id' ]);
            $new_post_status = sanitize_text_field($_POST['new_status'] );

			$current_status = get_post_status( $post_id );
            if( $current_status != $new_post_status ){

                if ( $new_post_status === 'publish' ) {

                    // Publish post
                    wp_publish_post( $post_id );

                }elseif ( in_array($new_post_status, array('draft', 'not_active')) ) {

                    $args = array(
                        'ID'          => $post_id,
                        'post_status' => $new_post_status
                    );

                    // Update the post into the database
                    wp_update_post( $args );
                }
            }

			// response html
			$course = get_course( $post_id );
			$index = 0;
			$cat_index = 0;
			include 'admin/views/dashboard/html-admin-page-dashboard-course.php';
		}

		die();
	}


	/****************************************************
	 * Frontend ajax calls
	 ***************************************************/

	/**
	 * Save time_spent value into score card from lesson page active timer
	 */
	public function save_time_spent_lesson() {

		check_ajax_referer( 'save-time-spent-lesson', 'security' );

		if ( isset( $_POST[ 'lessonID' ] ) && isset( $_POST[ 'timeSpent' ] ) ) {

			$lesson_id  = intval( $_POST[ 'lessonID' ] );
			$time_spent = floatval( $_POST[ 'timeSpent' ] );

			if ( $user_id = get_current_user_id() ) {
				$course_element = get_course_element( $lesson_id );
				if ( $course_element && ( $course = $course_element->get_parent_course() ) ) {
					$score_card = lucidlms_get_current_score_card( $user_id, $course->id );
					if ( ! $score_card ) {
						// create new Score Card
						$score_card = new LU_Score_Card(
							NULL,
							array(
								'course_id'  => $course->id,
								'student_id' => $user_id,
								'start_date' => 'now',
							)
						);
						$score_card->flush();
					}

					// create new course element to use it
					if ( ! isset( $score_card->score_card_elements[ $lesson_id ] ) ) {
						$score_card->add_score_card_element( $lesson_id );
					}

					$score_card->score_card_elements[ $lesson_id ]->time_spent = $time_spent;

					// submit changes
					$score_card->flush();
				}
			}

		}

		die();
	}

	/**
	 * Save answer before going to another one
	 */
	public function save_answer() {
		check_ajax_referer( 'save-answer', 'security' );

		// get 'started' scorecard using id from $current_user and $course_element->get_parent_course_id() method +

		// Create quiz progress in in scorecard if doesn't exists +
		// if answers == false, delete the whole question from quiz scorecard (if question progress exists) - then die +
		// Create question progress in scorecard if doesn't exists, or override answers in scorecard if it exists in DB(also set is_answers_correct) - then die +

		if ( $user_id = get_current_user_id() ) {
			$quiz_id      = intval( $_POST[ 'quizId' ] );
			$question_id  = intval( $_POST[ 'questionId' ] );
			$answers_json = stripslashes( htmlspecialchars_decode( strip_tags( $_POST[ 'answers' ] ) ) );

			if ( ( $course_element = get_course_element( $quiz_id ) ) && ! empty( $question_id ) ) {

				if ( $course = $course_element->get_parent_course() ) {
					$score_card = lucidlms_get_current_score_card( $user_id, $course->id );
					/** @var LU_Score_Card $score_card */
					if ( $score_card ) {

						// create quiz element in score card results if not exist
						if ( ! isset( $score_card->score_card_elements[ $quiz_id ] ) ) {
							$score_card->add_score_card_element( $quiz_id );
						}
						$quiz_results = $score_card->score_card_elements[ $quiz_id ];

						if ( 'blank' == $quiz_results->status ) {
							$quiz_results->status = 'started';
						}

						if ( FALSE === $answers_json ) {
							$quiz_results->remove_question( $question_id );
						}
						else {
							$answers = json_decode( $answers_json );
							if ( NULL === $answers ) {
								$this->output_error_data( 'wrong_answers', __( 'Wrong answers array passed', 'lucidlms' ) );
							}

							$quiz_results->add_answers( $question_id, (array) $answers );

						}

						/////// FLUSH to DB ////////
						if ( ! $score_card->flush() ) {
							$this->output_error_data( 'db_error', __( 'Database processing error on Score Card saving', 'lucidlms' ) );
						}

					}
					else {
						$this->output_error_data( 'no_score_card_found', __( 'No Score Card found for this Quiz', 'lucidlms' ) );
					}

				}
				else {
					$this->output_error_data( 'no_course', __( 'Wrong quiz id passed', 'lucidlms' ) );
				}
			}
			else {
				$this->output_error_data( 'no_course_element', __( 'Wrong quiz id passed', 'lucidlms' ) );
			}
		}
		else {
			$this->output_error_data( 'not_logged_in', __( 'You\'re not authorize to do this', 'lucidlms' ) );
		}

		die();
	}

	/**
	 * Update review screen before completing the quiz
	 */
	public function update_review_screen() {
		check_ajax_referer( 'update-review-screen', 'security' );

		global $course_element, $course_element_results;

		// get 'started' scorecard using id from $current_user and $course_element->get_parent_course_id() method
		// set global variable $course_element_results (from 'started' scorecard) of the same structure if it exists in DB (needed for template echoed using hook below)

		if ( $user_id = get_current_user_id() ) {
			$course_element_id = intval( $_POST[ 'quizId' ] );

			if ( $course_element = get_course_element( $course_element_id ) ) { // needed for template echoed using hook below

				if ( $course = $course_element->get_parent_course() ) {

					/** @var LU_Score_Card $score_card */
					$score_card = lucidlms_get_current_score_card( $user_id, $course->id );
					if ( $score_card ) {
						$course_element_results = $score_card->get_score_card_element( $course_element_id );

						$GLOBALS[ 'course_element_results' ] = $course_element_results; // just to ensure

						// Return single/quiz/content-questions-review.php template
						do_action( 'lucidlms_template_quiz_review' );

					}
					else {
						$this->output_error_data( 'no_score_card_found', __( 'No Score Card found for this Quiz', 'lucidlms' ) );
					}
				}
				else {
					$this->output_error_data( 'no_course', __( 'No Course found', 'lucidlms' ) );
				}

			}
			else {
				$this->output_error_data( 'no_course_element', __( 'Wrong course element id passed', 'lucidlms' ) );
			}
		}
		else {
			$this->output_error_data( 'not_logged_in', __( 'You\'re not authorize to do this', 'lucidlms' ) );
		}

		die();
	}

	/**
	 * Complete quiz and show quiz results screen
	 */
	public function complete_quiz() {
		check_ajax_referer( 'complete-quiz', 'security' );

		global $course, $course_element, $course_element_results, $current_user;

		if ( $user_id = get_current_user_id() ) {
			$course_element_id = intval( $_POST[ 'quizId' ] );

			if ( $course_element = get_course_element( $course_element_id ) ) { // needed for template echoed using hook below

				if ( $course = $course_element->get_parent_course() ) {

					// get 'started' scorecard using id from $current_user and $course_element->get_parent_course_id() method
					/** @var LU_Score_Card $score_card */
					$score_card = lucidlms_get_current_score_card( $user_id, $course->id );
					if ( $score_card ) {

						// set global variable $course_element_results (from 'started' scorecard) of the same structure if it exists in DB (needed for template echoed using hook below)
						$course_element_results = $score_card->get_score_card_element( $course_element_id );

						$course_element_results->complete_element();

						if ( $score_card->flush() ) {

							$GLOBALS[ 'course' ]                 = $course;
							$GLOBALS[ 'course_element_results' ] = $course_element_results; // just to ensure
							$GLOBALS[ 'scorecard' ]              = $score_card;

							// Return single/quiz/results.php template
							remove_action( 'before_quiz_completed_notices', 'quiz_status_notice', 10, 2 );

							do_action( 'lucidlms_completed_quiz_results' );
							do_action( 'lucidlms_quiz_notices' );

						}
						else {
							$this->output_error_data( 'db_error', __( 'Database processing error on Score Card saving', 'lucidlms' ) );
						}

					}
					else {
						$this->output_error_data( 'no_score_card_found', __( 'No Score Card found for this Quiz', 'lucidlms' ) );
					}
				}
				else {
					$this->output_error_data( 'no_course', __( 'No Course found', 'lucidlms' ) );
				}

			}
			else {
				$this->output_error_data( 'no_course_element', __( 'Wrong course element id passed', 'lucidlms' ) );
			}
		}
		else {
			$this->output_error_data( 'not_logged_in', __( 'You\'re not authorize to do this', 'lucidlms' ) );
		}


		die();
	}

	/**
	 * Get all courses
	 */
	public function get_all_courses() {
		check_ajax_referer( 'get-all-courses', 'security' );

		do_action( 'lucidlms_get_all_courses' );

		die();
	}

	/**
	 * Get category courses
	 */
	public function get_category_courses() {
		check_ajax_referer( 'get-category-courses', 'security' );

		do_action( 'lucidlms_get_category_courses', $_POST[ 'categorySlug' ] );

		die();
	}

	/**
	 * Get searched courses
	 */
	public function get_searched_courses() {
		check_ajax_referer( 'get-searched-courses', 'security' );

		do_action( 'lucidlms_get_searched_courses', $_POST[ 'query' ] );

		die();
	}

}

new LU_AJAX();
