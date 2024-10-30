<?php

/**
 * Course Factory Class
 *
 * Course factory creating the right course object
 *
 * @class 		LU_Course_Factory
 * @package		LucidLMS/Classes
 * @category	Class
 * @author 		New Normal
 */
class LU_Course_Factory {

    /**
     * get_course function.
     *
     * @access public
     * @param bool $the_course (default: false)
     * @internal param array $args (default: array())
     * @return LU_Course
     */
    public function get_course( $the_course = false ) {
        global $post;

        if ( false === $the_course ) {
            $the_course = $post;
        } elseif ( is_numeric( $the_course ) ) {
            $the_course = get_post( $the_course );
            $course_id = $the_course;
        }

        if ( ! $the_course )
            return false;

        if ( is_object ( $the_course ) ) {
            $course_id = absint( $the_course->ID );
            $post_type  = $the_course->post_type;
        }

        if ( 'course' == $post_type ) {

            $terms        = get_the_terms( $course_id, ATYPE );
            $course_type = ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'course';

            $classname = 'LU_' . implode( '_', array_map( 'ucfirst', explode( '-', $course_type ) ) );

        } else {
            $classname = false;
            $course_type = false;
        }

        // Filter classname so that the class can be overridden if extended.
        $classname = apply_filters( 'lucid_course_class', $classname, $course_type, $post_type, $course_id );

        if ( ! class_exists( $classname ) )
            $classname = 'LU_Course';

        return new $classname( $the_course );
    }
}
