<?php
/**
 * Course Element Meta
 *
 * Displays the course element meta box.
 *
 * @author        New Normal
 * @category      Admin
 * @package       LucidLMS/Admin/Meta Boxes
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * LU_Meta_Box_Course_Element
 */
class LU_Meta_Box_Course_Element {

	/**
	 * Output the metabox
	 *
	 * @param $post
	 */
	public static function output( $post ) {
		global $post, $wpdb, $thepostid;

		wp_nonce_field( 'lucidlms_save_data', 'lucidlms_meta_nonce' );

		$thepostid = $post->ID;

		$course_element      = get_course_element( $thepostid );
		$course_element_type = $course_element->get_type();
        $collapsible_settings = $course_element_type == 'quiz' ? true : false;

        echo '<div class="lucidlms-meta-box">';

        if( $collapsible_settings ){
            echo '<h4 class="section-title collapsible collapsed" data-toggle="collapse" data-target="#settings"><i class="fa fa-chevron-down"></i>' . __('General Settings', 'lucidlms') . '</h4>';
        } else {
            echo '<h4 class="section-title">' . __('General Settings', 'lucidlms') . '</h4>';
        }

        // Main options
        echo '<div class="lucidlms-options ' . $course_element_type . ($collapsible_settings ? ' collapse' : '') . '" id="settings">';

        $terms = LU()->taxonomies->get_terms( AETYPE, true );

        lucidlms_wp_hidden_input( array(
            'id'            => 'course_element_type',
            'value'         => $course_element_type,
            'wrapper_class' => 'taxonomy',
        ));

        // TODO: needs refactoring
        if (isset($_GET['course_id']) || isset($_POST['course_id'])) {
            global $course_id;
            if (isset ($_GET['course_id'])) {
                $course_id = $_GET['course_id'];
            }

            if (isset($_POST['course_id'])) {
                $course_id = $_POST['course_id'];
            }

            lucidlms_wp_hidden_input(array(
                'id' => 'course_id',
                'value' => $course_id,
            ));
        }

        lucidlms_wp_radio( array(
			'options'       => $terms,
			'value'         => $course_element_type,
			'label'         => __( 'Course Element Type', 'lucidlms' ),
			'id'            => 'course_element_type',
			'wrapper_class' => 'taxonomy',
			'class'         => 'one-row'
		) );

		do_action( 'lucidlms_course_element_metabox_output_general' );

		if ( $course_element_type == 'quiz' ) {

			lucidlms_wp_text_input( array(
				'id'    => '_threshold',
				'name'  => '_threshold',
				'label' => __( 'Threshold', 'lucidlms' ),
				'value' => $course_element->threshold,
			) );

			lucidlms_wp_text_input( array(
				'id'          => '_attempts',
				'name'        => '_attempts',
				'desc_tip'    => 'true',
				'description' => __( 'Choose number of attempts allowed for student to pass quiz. "0" means indefinite.', 'lucidlms' ),
				'label'       => __( 'Attempts', 'lucidlms' ),
				'value'       => $course_element->attempts,
			) );

			lucidlms_wp_text_input( array(
				'id'          => '_duration',
				'name'        => '_duration',
				'desc_tip'    => 'true',
				'description' => __( 'Optional. If student exceed given time, results will be still submitted. "0" means timer is off.', 'lucidlms' ),
				'label'       => __( 'Timer (min)', 'lucidlms' ),
				'value'       => $course_element->duration
			) );

		} elseif ( $course_element_type == 'lesson' ) {

			lucidlms_wp_text_input( array(
				'id'          => '_duration',
				'name'        => '_duration',
				'desc_tip'    => 'true',
				'description' => __( 'Optional. The time student should wait to be able to complete the lesson. "0" means timer is off.', 'lucidlms' ),
				'label'       => __( 'Duration (min)', 'lucidlms' ),
				'value'       => $course_element->duration
			) );
		}

		do_action( 'lucidlms_course_element_metabox_output_' . $course_element_type );

		echo '</div>';

		// Additional options
		if ( $course_element_type == 'quiz' ) { ?>

            <a class="insert-from-question-pool" href="#"><i class="fa fa-plus"></i> <?php _e('Insert from Question Pool', 'lucidlms')?></a>
            <h4 class="section-title"><?php _e('Questions', 'lucidlms') ?></h4>


            <?php
			$questions = $course_element->get_questions_list();

			$available_question_types = LU_Question::get_available_question_types();
			array_unshift( $available_question_types, __( 'Choose a type', 'lucidlms' ) );

			lucidlms_wp_select( array(
				'id'      => 'new_element',
				'options' => $available_question_types,
			) );
			?>

			<p class="input-group">
				<input type="text" class="form-control new_element_name" placeholder="<?php _e( 'Name', 'lucidlms' ) ?>">
		            <span class="input-group-btn">
		                <button class="btn btn-primary create-element" type="button">
			                <?php _e( 'Create', 'lucidlms' ) ?>
		                </button>
		            </span>
			</p>

			<ul class="lucidlms-options questions">

				<?php if ( ! empty( $questions ) ) {
					include 'views/html-course-elements-question.php';
				} ?>

			</ul>
			<?php

			do_action( 'lucidlms_course_element_metabox_output_questions' );
		}
        echo '</div>'; //end of lucidlms-meta-box
		do_action( 'lucidlms_course_element_metabox_output' );
	}

	/**
	 * Output question to ajax
	 *
	 * @param $thepostid
	 */
	public static function output_question( $thepostid ) {

		$question      = new LU_Question( $thepostid );
		$question_type = $question->get_type();

		// Main options
		echo '<form id="question-form">';
		echo '<div class="lucidlms-options" data-question-type="' . $question_type . '">';

		lucidlms_wp_hidden_input( array(
			'id'    => 'question_id',
			'value' => $thepostid
		) );

		echo '<textarea id="_question_text_extended" name="_question_text_extended" placeholder="' . __( 'Type a question or insert media...', '' ) . '">' . $question->question_text_extended . '</textarea>';

		if ( $question_type == 'multiple_choice' || $question_type == 'single_choice' ) {

			$answers = $question->answers;

			echo '<ul class="answers">';
			if ( ! empty( $answers ) ) {

				foreach ( $answers as $key => $answer ) {
					echo '<li class="panel panel-default"><div class="panel-body">';
					echo '<input type="text" name="_answers[' . $key . '][answer]" placeholder="' . __( 'Type an answer', 'lucidlms' ) . '" value="' . esc_attr( $answer['answer'] ) . '" />';
					echo '<input type="checkbox" class="input-switch" name="_answers[' . $key . '][is_correct]" ' . ( $answer['is_correct'] ? 'checked="true"' : '' ) . ' data-on-color="success" data-off-color="danger" value="true" data-on-text="' . __( 'Correct', 'lucidlms' ) . '" data-off-text="' . __( 'Wrong', 'lucidlms' ) . '">';
					echo '<a href="#" class="btn btn-default remove-answer"><span class="glyphicon glyphicon-remove"></span></a>';
					echo '</div></li>';
				}

			}
			echo '</ul>';

			echo '<a href="#" class="add-answer">' . __( 'Add answer', 'lucidlms' ) . '</a>';

		} elseif ( $question_type == 'open' ) {

			// No need to print anything for open questions

		}

		do_action( 'lucidlms_course_element_metabox_output_question_' . $question_type );

		echo '</div>';

		do_action( 'lucidlms_course_element_metabox_output_question' );

		echo '</form>';

	}

	/**
	 * Save meta box data
	 */
	public static function save( $post_id, $post ) {

		$course_element      = get_course_element( $post_id );
		$course_element_type = $course_element->get_type();

        if ( isset( $_POST['course_id'] )) {
            $course = get_course($_POST['course_id']);

            $course->elements[ $post_id ] = $course_element;
            $course->update_elements_ids_field();
        }

		if ( isset( $_POST['course_element_type'] ) && ( $_POST['course_element_type'] != $course_element_type ) ) {
			$course_element->set_type( stripslashes( $_POST['course_element_type'] ) ); //@todo: add errors processing!
		}

		if ( 'quiz' == $course_element_type ) {

			if ( isset( $_POST['_threshold'] ) ) {
				$course_element->threshold = sanitize_text_field( $_POST['_threshold'] );
			}
			if ( isset( $_POST['_attempts'] ) ) {
				$course_element->attempts = sanitize_text_field( $_POST['_attempts'] );
			}

		}

		if ( 'lesson' == $course_element_type || 'quiz' == $course_element_type ) {

			if ( isset( $_POST['_duration'] ) ) {
				$course_element->duration = sanitize_text_field( $_POST['_duration'] );
			}

		}

		remove_action( 'lucid_process_course_element_meta', 'LU_Meta_Box_Course_Element::save', 10, 2 ); // to prevent recursion

		$course_element->flush();

		// Do action for course element type
		do_action( 'lucid_process_course_element_meta_' . $course_element_type, $post_id );

	}
}
