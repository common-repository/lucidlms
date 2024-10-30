<?php
/**
 * Score Card Meta
 *
 * Displays the score card meta box.
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
 * LU_Meta_Box_Score_Card
 */
class LU_Meta_Box_Score_Card {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
		global $post, $wpdb, $thepostid, $current_user;

		wp_nonce_field( 'lucidlms_save_data', 'lucidlms_meta_nonce' );

		$thepostid = $post->ID;

		$scorecard = new LU_Score_Card( $thepostid );

        $course  = $scorecard->id ? get_course( $scorecard->get_course_id() ) : false;
		$screen    = get_current_screen();

		?>

		<div class="lucidlms-options general"><?php

			// Show student field
            $query_args['orderby'] = 'display_name';
			$query_args['fields'] = array( 'ID', 'user_login', 'display_name' );
			$users                = get_users( $query_args );
			if ( ! empty( $users ) ) {
				$options_users = array();
				foreach ( (array) $users as $user ) {
					$options_users[ $user->ID ] = $user->display_name;
				}

				lucidlms_wp_select( array(
					'id'                => '_student_id',
					'label'             => __( 'Student', 'lucidlms' ),
					'class'             => '',
					'value'             => $scorecard->get_student_id(),
					'options'           => $options_users,
					'custom_attributes' => $screen->action !== 'add' ?
						array( // Add this for 'edit' action
							'disabled' => 'disabled'
						) :
						array() // Add this for 'add' action
				) );
			}

			// Show course field
			$courses         = get_posts( array(
					'post_type'      => 'course',
					'posts_per_page' => '-1',
					'post_status'    => array('publish', 'draft', 'not_active')
				) );
			$options_courses = array();
			foreach ( $courses as $possible_course ) {
				$options_courses[ $possible_course->ID ] = $possible_course->post_title;
			}
			lucidlms_wp_select( array(
				'id'                => '_course_id',
				'label'             => __( 'Course to be passed', 'lucidlms' ),
				'class'             => '',
				'value'             => $scorecard->get_course_id(),
				'options'           => $options_courses,
				'custom_attributes' => $screen->action !== 'add' ?
					array( // Add this for 'edit' action
						'disabled' => 'disabled'
					) :
					array() // Add this for 'add' action
			) );

			// Show status field just as text (in order to prevent manual editing status)
			?>
			<p class="form-field _status_field ">
				<label><?php _e( 'Status', 'lucidlms' ); ?></label>
				<strong><?php echo $scorecard->get_status_name() ? $scorecard->get_status_name() : __( 'Not created', 'lucidlms' ); ?></strong>
				<?php echo $scorecard->get_status() === 'sc_completed' ? '<a href="' . $scorecard->get_certificate_link() . '" target="_blank">Download certificate</a>' : ''; ?>
			</p>
			<?php

			// Show expire date field only when editing scorecard
			if ( $screen->action !== 'add' ) {

				if ($course->availability_time == 0) { ?>
					<p class="form-field _expire_date ">

						<label><?php _e( 'Expire date', 'lucidlms' ); ?></label>
						<?php _e( 'Indefinite', 'lucidlms' ); ?>

					</p><?php
				} else {
				  //get timestamp based on timezone offset
				  $expire_date = ! empty( $scorecard->expire_date ) ? $scorecard->expire_date->getTimestamp() + $scorecard->expire_date->getOffset() : time();
				  lucidlms_wp_datetimepicker( array(
					'id'    => '_expire_date',
					'label' => __( 'Expire date', 'lucidlms' ),
					'value' => $expire_date,
				  ) );
				}
			}

			do_action( 'lucidlms_score_card_metabox_output_general' ); ?>

		</div>

		<?php if ( $screen->action !== 'add' && $scorecard->get_course_type() === 'course' ) { ?>

			<p><strong><?php _e( 'Score Card Progress', 'lucidlms' ); ?></strong></p>
			<div class="lucidlms-options score-card-progress"><?php

				if ( ! empty( $scorecard->score_card_elements ) ) {
					foreach ( $scorecard->score_card_elements as $element_id => $score_card_element ) {

						$course_element = get_course_element( $score_card_element->id );
						$title            = $score_card_element->course_element_title;

						$type_slug = $score_card_element->type;
						$type_name = LU()->taxonomies->get_term_name( AETYPE, $type_slug );

						$status_name = $score_card_element->get_status_name();

						?>

						<hr>

						<p>
							<strong><?php echo $type_name . ': ' . $title . ' (' . $status_name . ')' ?></strong>
						</p>

						<?php

						if ( $type_slug === 'lesson' ) {
							$timespent = $score_card_element->time_spent; // these are seconds
							$duration  = $course_element->duration; // these are minutes
							?>

							<p>
								<?php printf( __( 'Time spent: %s out of %s', 'lucidlms' ), $timespent, $duration * 60 ); ?>
							</p>

						<?php
						}

						if ( $type_slug === 'quiz' ) {

							// TODO: move handling request to somewhere else
							if ( isset( $_REQUEST['reset-attempts'] ) && ( ! empty( $_REQUEST['reset-attempts'] ) && ( is_numeric( $_REQUEST['reset-attempts'] ) ) ) ) {
								$score_card_element->reset_attempts( $_REQUEST['reset-attempts'] );
								$scorecard->flush();
							}

							$score            = $score_card_element->score;
							$threshold        = $course_element->threshold;
							$threshold_type   = $course->threshold_type;
							$passed_attempts  = $score_card_element->passed_attempts;
							$allowed_attempts = $course_element->attempts;
							?>

							<p>
								<?php
								if ( $threshold_type === 'none' ) {
									printf( __( 'Score: %s', 'lucidlms' ), $score );
								} else {
									printf( __( 'Score: %s (threshold is %s)', 'lucidlms' ), $score, $threshold );
								}
								?>
							</p>

							<p>
								<?php
								if ( $allowed_attempts !== 0 ) {

									printf( __( 'Passed attempts: %s out of %s allowed', 'lucidlms' ), $passed_attempts, $allowed_attempts );
									printf( ' <a href="%s">%s</a>', $score_card_element->get_reset_attempts_link(), __( 'Reset one attempt', 'lucidlms' ) );
								}
								?>
							</p>

							<a href="#" class="show-results"><?php _e('Show results', 'lucidlms'); ?></a>
							<ul class="quiz-results">
								<?php foreach ( $course_element->get_questions_list(true) as $id => $question ) { ?>
									<li>
										<span class="question-title"><?php echo sprintf( __( 'Q: %s', 'lucidlms' ), $question['question_text'] ); ?></span>
										<?php if ( isset( $score_card_element->questions[ $id ] ) ) { ?>
											<span class="question-user-answer"><?php echo sprintf( __( 'A: %s', 'lucidlms' ), implode( ', ', $score_card_element->questions[ $id ]['answers'] ) ) ?></span>
											<span class="question-is-correct <?php echo $score_card_element->questions[ $id ]['is_answers_correct'] ? 'correct' : 'incorrect'; ?>">
												<?php echo $score_card_element->questions[ $id ]['is_answers_correct'] ? __( 'Correct', 'lucidlms' ) : __( 'Incorrect', 'lucidlms' ); ?>
											</span>
										<?php } else { ?>
											<span class="question-user-answer"><?php _e( 'A: missing', 'lucidlms' ) ?></span>
										<?php } ?>
									</li>
								<?php } ?>
							</ul>

						<?php
						}

					}
				} else {
					_e( 'There\'s no progress yet', 'lucidlms' );
				}

				do_action( 'lucidlms_score_card_metabox_output_progress' ); ?>

			</div>

		<?php
		}

		do_action( 'lucidlms_score_card_metabox_output' );
	}

	/**
	 * Save meta box data
	 */
	public static function save( $post_id, $post ) {

		remove_action( 'lucid_process_score_card_meta', 'LU_Meta_Box_Score_Card::save', 10, 2 ); // to prevent recursion

        $score_card = new LU_Score_Card( $post_id );

        if( isset($_POST['_expire_date']) ){
            $expire_date_str = sanitize_text_field( $_POST['_expire_date'] );

            // date string is valid
            if( $expire_date = date_create( $expire_date_str, lucidlms_timezone() ) ){

                // set new expire date
                $score_card->set_expire_date( $expire_date_str );

                // don't change a status if it is already completed
                if( 'sc_completed' != $score_card->get_status() ) {
                    // if date expire date is in the past
                    if( $expire_date < date_create('now', lucidlms_timezone() ) ){
                        // set card as expired
                        $score_card->set_status('sc_expired');
                    } else {
                        // otherwise set it as started
                        $score_card->set_status('sc_started');
                    }
                }
            }
        }

        if ( isset( $_POST['_student_id'] ) && isset( $_POST['_course_id'] ) ) {

            $student_id = intval($_POST['_student_id']);
            $course_id = intval($_POST['_course_id']);

            if ( $course = get_course( $course_id ) ) {

                $started_score_card = lucidlms_get_current_score_card($student_id, $course_id);
                // and there are no already started score cards
                if( !$started_score_card ){

                    //on-create part
                    $score_card->set_course_id( $course_id );
                    $score_card->set_student_id( $student_id );
                    $score_card->set_status( 'sc_started' );

                }
            }
        }
        $score_card->flush();
    }
}
