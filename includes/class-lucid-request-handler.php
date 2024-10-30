<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Handle frontend requests
 *
 * @class          LU_Request_Handler
 * @version        1.0.0
 * @package        LucidLMS/Classes/
 * @category       Class
 * @author         New Normal
 */
class LU_Request_Handler {

	/**
	 * Constructor
	 */
	public function __construct() {

		add_action( 'lucidlms_init', array( $this, 'start_course_action' ) );
		add_action( 'lucidlms_init', array( $this, 'complete_course_element_action' ) );
		add_action( 'lucidlms_init', array( $this, 'try_again_course_element_action' ) );

	}

	/**
	 * Start course action
	 *
	 * Checks for a valid request, does validation and then redirects if valid.
	 *
	 * @param bool $url (default: false)
	 */
	public function start_course_action( $url = false ) {
		if ( empty( $_REQUEST['start-course'] ) || ! is_numeric( $_REQUEST['start-course'] ) ) {
			return;
		}

		$course_id = apply_filters( 'lucidlms_start_course_course_id', absint( $_REQUEST['start-course'] ) );
		$course    = get_course( $course_id );
		$success     = false;

        if( 'publish' == $course->course_status ){ // prevent joining of not active courses
            if ( $user_id = get_current_user_id() ) {
                if ( $course ) {
                    if ( ! lucidlms_get_current_score_card( $user_id, $course_id ) ) {
                        $score_card = new LU_Score_Card();

                        $score_card->set_course_id( $course_id );
                        $score_card->set_student_id( $user_id );
                        $score_card->set_status( 'sc_started' );

                        if ( $score_card->flush() ) {
                            $success = true;
                        }
                    }
                }
                // Redirect user to login form if he isn't logged in and redirect back to start a course
            } else {
                $url     = add_query_arg( array(
                    'action'             => 'login',
                    'role'               => 'student',
                    'return_to_course' => $course_id
                ), wp_login_url() );
                $success = true;
            }
        }


		// If we started the course we can now optionally do a redirect.
		if ( $success ) {


			$url = apply_filters( 'success_start_course_redirect', $url );
			do_action('lucidlms_started_course', $course_id);

			// If has custom URL redirect there
			if ( $url ) {
				wp_safe_redirect( $url );
				exit;
			}

		}
	}

	/**
	 * Complete course element action
	 *
	 * Checks for a valid request, does validation and then redirects if valid.
	 *
	 * @param bool $url (default: false)
	 */
	public function complete_course_element_action( $url = false ) {
		if ( empty( $_REQUEST['complete-course-element'] ) || ! is_numeric( $_REQUEST['complete-course-element'] ) ) {
			return;
		}

		$course_element_id = apply_filters( 'lucidlms_complete_course_element_course_id', absint( $_REQUEST['complete-course-element'] ) );
		$course_element    = get_course_element( $course_element_id );
        $course = $course_element->get_parent_course();
		$success             = false;

        $url = remove_query_arg('complete-course-element', $_SERVER["REQUEST_URI"]) . '#element-completed';
        $url = 'lesson' == $course_element->get_type() && isset($course->id) ? get_permalink($course->id) : $url;

		if ( $user_id = get_current_user_id() ) {
			// mark current course element as completed (if not quiz), make var $success = true.
			if ( $course_element && ( 'lesson' == $course_element->get_type() ) ) {
				if ( $course = $course_element->get_parent_course() ) {
                    $score_card = lucidlms_get_current_score_card( $user_id, $course->id );
					if ( $score_card ) {

						// create new course element to use it
						if ( ! isset( $score_card->score_card_elements[ $course_element_id ] ) ) {
							$score_card->add_score_card_element( $course_element_id );
						}

						/** @var LU_Score_Card $score_card */
						if ( isset( $score_card->score_card_elements[ $course_element_id ] ) ) {
							if ( $score_card->score_card_elements[ $course_element_id ]->time_spent >= $course_element->duration ) {
								$score_card->score_card_elements[ $course_element_id ]->complete_element();

								if ( $score_card->flush() ) {
									$success = true;
								}
							}
						}


                        // let's check here if the course is completed
					}


				}
			}
		}
		// If we completed the course element we can now optionally do a redirect.
		if ( $success ) {

			$url = apply_filters( 'success_complete_course_element_redirect', $url );

            // If has custom URL redirect there
            if ( $url ) {
				wp_safe_redirect( $url );
				exit;
			}

		}
	}

	/**
	 * Try again course element action
	 *
	 * Checks for a valid request, does validation and then redirects if valid.
	 *
	 * @param bool $url (default: false)
	 */
	public function try_again_course_element_action( $url = false ) {
		if ( empty( $_REQUEST['try-again-course-element'] ) || ! is_numeric( $_REQUEST['try-again-course-element'] ) ) {
			return;
		}

		$course_element_id = apply_filters( 'lucidlms_try_again_course_element_course_id', absint( $_REQUEST['try-again-course-element'] ) );
		$course_element    = get_course_element( $course_element_id );
		$success             = false;

        $url = remove_query_arg('try-again-course-element', $_SERVER["REQUEST_URI"]);

        // if quiz - clear previous questions progress (questions array in scorecard's course element)
		// set status to 'blank' (no matter what type is), make var $success = true.
		if ( $user_id = get_current_user_id() ) {
			if ( $course_element ) {
				if ( $course = $course_element->get_parent_course() ) {
                    /** @var LU_Score_Card $score_card */
                    $score_card = lucidlms_get_current_score_card( $user_id, $course->id );
                    if ( $score_card ) {

						if ( $score_card_element = $score_card->get_score_card_element( $course_element_id ) ) {
                            if ( 'quiz' == $score_card_element->type ) {
                                // lets check if quiz might be re-tried
                                $passed_attempts  = !is_null($score_card_element) && !empty($score_card_element->passed_attempts) ? $score_card_element->passed_attempts : 0;
                                $allowed_attempts = $course_element->attempts;

                                // if $allowed_attempts equals false or zero, then user is allowed to pass this quiz as much as he/she wants.
                                if( !$allowed_attempts || ($allowed_attempts > $passed_attempts) ){
                                    // reset quiz data
                                    $score_card_element->remove_all_questions();

                                    $score_card_element->initialize( array(
                                        'time_spent' => 0,
                                        'score'      => 0,
                                        'status'     => 'blank',
                                    ) );

                                    if ( $score_card->flush() ) {
                                        $success = true;
                                    }

                                }
                            } else {

                                // reset other types
                                $score_card_element->initialize( array(
                                    'time_spent' => 0,
                                    'score'      => 0,
                                    'status'     => 'blank',
                                ) );

                                if ( $score_card->flush() ) {
                                    $success = true;
                                }
                            }
						}
					}
				}
			}
		}

		// If we cleared previous progress we can now optionally do a redirect.
		if ( $success ) {

			$url = apply_filters( 'success_try_again_course_element_redirect', $url );

			// If has custom URL redirect there
			if ( $url ) {
				wp_safe_redirect( $url );
				exit;
			}

		}
	}

}

new LU_Request_Handler();
