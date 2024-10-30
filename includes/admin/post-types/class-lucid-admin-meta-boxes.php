<?php
/**
 * LucidLMS Meta Boxes
 *
 * Sets up the write panels used by courses and course elements (custom post types)
 *
 * @author 		New Normal
 * @category 	Admin
 * @package 	LucidLMS/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * LU_Admin_Meta_Boxes
 */
class LU_Admin_Meta_Boxes {

    private static $meta_box_errors = array();

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 10 );
        add_action( 'add_meta_boxes', array( $this, 'rename_meta_boxes' ), 20 );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );
        add_action( 'save_post', array( $this, 'save_meta_boxes' ), 1, 2 );

        /**
         * Save Meta Boxes
         */
	    add_action( 'lucid_process_course_meta', 'LU_Meta_Box_Course::save', 10, 2 );
	    add_action( 'lucid_process_course_element_meta', 'LU_Meta_Box_Course_Element::save', 10, 2 );
	    add_action( 'lucid_process_score_card_meta', 'LU_Meta_Box_Score_Card::save', 10, 2 );
	    add_action( 'lucid_process_instructors_meta', 'LU_Meta_Box_Instructors::save', 10, 2 );

	    // Error handling (for showing errors from meta boxes on next page load)
        add_action( 'admin_notices', array( $this, 'output_errors' ) );
        add_action( 'shutdown', array( $this, 'save_errors' ) );


    }

    /**
     * Add an error message
     * @param string $text
     */
    public static function add_error( $text ) {
        self::$meta_box_errors[] = $text;
    }

    /**
     * Save errors to an option
     */
    public function save_errors() {
        update_option( 'lucid_meta_box_errors', self::$meta_box_errors );
    }

    /**
     * Show any stored error messages.
     */
    public function output_errors() {
        $errors = maybe_unserialize( get_option( 'lucid_meta_box_errors' ) );

        if ( ! empty( $errors ) ) {

            echo '<div id="lucid_errors" class="error fade">';
            foreach ( $errors as $error ) {
                echo '<p>' . esc_html( $error ) . '</p>';
            }
            echo '</div>';

            // Clear
            delete_option( 'lucid_meta_box_errors' );
        }
    }

    /**
     * Add LU Meta boxes
     */
    public function add_meta_boxes() {
	    // Courses
	    add_meta_box( 'lucidlms-course-meta', __( 'Course Settings', 'lucidlms' ), 'LU_Meta_Box_Course::output', 'course', 'normal', 'high' );
	    add_meta_box( 'lucidlms-course-element-meta', __( 'Settings', 'lucidlms' ), 'LU_Meta_Box_Course_Element::output', 'course_element', 'normal', 'high' );
	    add_meta_box( 'lucidlms-score-card-meta', __( 'General', 'lucidlms' ), 'LU_Meta_Box_Score_Card::output', 'score_card', 'normal', 'high' );
	    add_meta_box( 'lucidlms-instructors-meta', __( 'Instructor Information', 'lucidlms' ), 'LU_Meta_Box_Instructors::output', 'instructors', 'normal', 'high' );
    }

    /**
     * Remove metaboxes we don't need here like in example
     */
    public function remove_meta_boxes() {
//        remove_meta_box( 'postexcerpt', 'course', 'normal' );
    }

    /**
     * Rename core meta boxes
     */
    public function rename_meta_boxes() {
        global $post;

        // Comments/Reviews
        if ( isset( $post ) && ( 'publish' == $post->post_status || 'private' == $post->post_status ) ) {
            remove_meta_box( 'postexcerpt', 'course', 'normal' );
//            add_meta_box( 'postexcerpt', __( 'Description', 'lucidlms' ), 'post_excerpt_meta_box', 'course', 'normal' );
        }
    }

    /**
     * Check if we're saving, the trigger an action based on the post type
     *
     * @param  int $post_id
     * @param  object $post
     */
    public function save_meta_boxes( $post_id, $post ) {
        // $post_id and $post are required
        if ( empty( $post_id ) || empty( $post ) ) {
            return;
        }

        // Dont' save meta boxes for revisions or autosaves
        if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
            return;
        }

        // Check the nonce
        if ( empty( $_POST['lucidlms_meta_nonce'] ) || ! wp_verify_nonce( $_POST['lucidlms_meta_nonce'], 'lucidlms_save_data' ) ) {
            return;
        }

        // Check the post being saved == the $post_id to prevent triggering this call for other save_post events
        if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
            return;
        }

        // Check user has permission to edit
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Check the post type
        if ( ! in_array( $post->post_type, array( 'course', 'score_card', 'course_element', 'question', 'instructors' ) ) ) {
            return;
        }

        do_action( 'lucid_process_' . $post->post_type . '_meta', $post_id, $post );
    }

}

new LU_Admin_Meta_Boxes();
