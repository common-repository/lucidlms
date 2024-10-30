<?php
/**
 * Post Types Admin
 *
 * @author 		New Normal
 * @category 	Admin
 * @package 	LucidLMS/Admin
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'LU_Admin_Post_Types' ) ) :

    /**
     * LU_Admin_Post_Types Class
     */
    class LU_Admin_Post_Types {

        /**
         * Constructor
         */
        public function __construct() {
            add_action( 'admin_init', array( $this, 'include_post_type_handlers' ) );
            add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
//            add_action( 'admin_print_scripts', array( $this, 'disable_autosave' ) ); // activate if we don't need autosave

            //@TODO Add this functions in future releases when we would have transients
//            add_action( 'delete_post', array( $this, 'delete_post' ) );
//            add_action( 'wp_trash_post', array( $this, 'trash_post' ) );
//            add_action( 'untrash_post', array( $this, 'untrash_post' ) );
        }

        /**
         * Conditonally load classes and functions only needed when viewing a post type.
         */
        public function include_post_type_handlers() {
            include( 'post-types/class-lucid-admin-meta-boxes.php' );
            include( 'post-types/class-lucid-admin-view-score-card.php' );

            //@TODO add this if we need duplicate some instances
//            if ( ! function_exists( 'duplicate_post_plugin_activation' ) ) //
//                include( 'class-lucid-admin-duplicate-course.php' );
        }

        /**
         * Change messages when a post type is updated.
         *
         * @param  array $messages
         * @return array
         */
        public function post_updated_messages( $messages ) {
            global $post, $post_ID;

            $messages['course'] = array(
                0 => '', // Unused. Messages start at index 1.
                1 => sprintf( __( 'Course updated. <a href="%s">View Course</a>', 'lucidlms' ), esc_url( get_permalink($post_ID) ) ),
                2 => __( 'Custom field updated.', 'lucidlms' ),
                3 => __( 'Custom field deleted.', 'lucidlms' ),
                4 => __( 'Course updated.', 'lucidlms' ),
                5 => isset($_GET['revision']) ? sprintf( __( 'Course restored to revision from %s', 'lucidlms' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
                6 => sprintf( __( 'Course published. <a href="%s">View Course</a>', 'lucidlms' ), esc_url( get_permalink($post_ID) ) ),
                7 => __( 'Course saved.', 'lucidlms' ),
                8 => sprintf( __( 'Course submitted. <a target="_blank" href="%s">Preview Course</a>', 'lucidlms' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
                9 => sprintf( __( 'Course scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Course</a>', 'lucidlms' ),
                    date_i18n( __( 'M j, Y @ G:i', 'lucidlms' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
                10 => sprintf( __( 'Course draft updated. <a target="_blank" href="%s">Preview Course</a>', 'lucidlms' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
            );

            $messages['score_card'] = array(
                0 => '', // Unused. Messages start at index 1.
                1 => __( 'Score Card updated.', 'lucidlms' ),
                2 => __( 'Custom field updated.', 'lucidlms' ),
                3 => __( 'Custom field deleted.', 'lucidlms' ),
                4 => __( 'Score Card updated.', 'lucidlms' ),
                5 => isset($_GET['revision']) ? sprintf( __( 'Score Card restored to revision from %s', 'lucidlms' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
                6 => __( 'Score Card updated.', 'lucidlms' ),
                7 => __( 'Score Card saved.', 'lucidlms' ),
                8 => __( 'Score Card submitted.', 'lucidlms' ),
                9 => sprintf( __( 'Score Card scheduled for: <strong>%1$s</strong>.', 'lucidlms' ),
                    date_i18n( __( 'M j, Y @ G:i', 'lucidlms' ), strtotime( $post->post_date ) ) ),
                10 => __( 'Score Card draft updated.', 'lucidlms' )
            );

            $messages['course_element'] = array(
                0 => '', // Unused. Messages start at index 1.
                1 => __( 'Course Element updated.', 'lucidlms' ),
                2 => __( 'Custom field updated.', 'lucidlms' ),
                3 => __( 'Custom field deleted.', 'lucidlms' ),
                4 => __( 'Course Element updated.', 'lucidlms' ),
                5 => isset($_GET['revision']) ? sprintf( __( 'Course Element restored to revision from %s', 'lucidlms' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
                6 => __( 'Course Element updated.', 'lucidlms' ),
                7 => __( 'Course Element saved.', 'lucidlms' ),
                8 => __( 'Course Element submitted.', 'lucidlms' ),
                9 => sprintf( __( 'Course Element scheduled for: <strong>%1$s</strong>.', 'lucidlms' ),
                    date_i18n( __( 'M j, Y @ G:i', 'lucidlms' ), strtotime( $post->post_date ) ) ),
                10 => __( 'Course Element draft updated.', 'lucidlms' )
            );

            $messages['question'] = array(
                0 => '', // Unused. Messages start at index 1.
                1 => __( 'Question updated.', 'lucidlms' ),
                2 => __( 'Custom field updated.', 'lucidlms' ),
                3 => __( 'Custom field deleted.', 'lucidlms' ),
                4 => __( 'Question updated.', 'lucidlms' ),
                5 => isset($_GET['revision']) ? sprintf( __( 'Question restored to revision from %s', 'lucidlms' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
                6 => __( 'Question updated.', 'lucidlms' ),
                7 => __( 'Question saved.', 'lucidlms' ),
                8 => __( 'Question submitted.', 'lucidlms' ),
                9 => sprintf( __( 'Question scheduled for: <strong>%1$s</strong>.', 'lucidlms' ),
                    date_i18n( __( 'M j, Y @ G:i', 'lucidlms' ), strtotime( $post->post_date ) ) ),
                10 => __( 'Question draft updated.', 'lucidlms' )
            );

            return apply_filters('lucid_post_updated_messages', $messages);
        }

        /**
         * Disable an auto save
         *
         * @return void
         */
        public function disable_autosave(){
            global $post;

            if ( $post && get_post_type( $post->ID ) === 'course' ) {
                wp_dequeue_script( 'autosave' );
            }
        }

    }

endif;

return new LU_Admin_Post_Types();
