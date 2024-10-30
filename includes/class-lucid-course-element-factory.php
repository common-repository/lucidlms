<?php

/**
 * Course Element Factory Class
 *
 * Course Element factory creating the right course object
 *
 * @class 		LU_Course_Element_Factory
 * @package		LucidLMS/Classes
 * @category	Class
 * @author 		New Normal
 */
class LU_Course_Element_Factory {

    /**
     * get_course function.
     *
     * @access public
     * @param bool $the_course_element (default: false)
     * @internal param array $args (default: array())
     * @return LU_Course_Element
     */
    public function get_course_element( $the_course_element = false ) {
        global $post;

        if ( false === $the_course_element ) {
            $the_course_element = $post;
        } elseif ( is_numeric( $the_course_element ) ) {
            $the_course_element = get_post( $the_course_element );
            $course_id = $the_course_element;
        }

        if ( ! $the_course_element )
            return false;

        if ( is_object ( $the_course_element ) ) {
            $course_id = absint( $the_course_element->ID );
            $post_type  = $the_course_element->post_type;
        }

        if ( 'course_element' == $post_type ) {

            $terms        = get_the_terms( $course_id, AETYPE );
            $course_type = ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'lesson';

            $class_name = 'LU_' . implode( '_', array_map( 'ucfirst', explode( '-', $course_type ) ) );

        } else {
            $class_name = false;
            $course_type = false;
        }

        // Filter classname so that the class can be overridden if extended.
        $class_name = apply_filters( 'lucid_course_element_class', $class_name, $course_type, $post_type, $course_id );

        if ( ! class_exists( $class_name ) )
            $class_name = 'LU_Lesson';

        return new $class_name( $the_course_element );
    }

    /**
     * Create new object having only it's type
     * @param string $type
     * @param array $args
     * @return LU_Quiz|LU_Lesson
     */
    public function create_course_element( $type = '', $args = array()){
        switch( $type ){
            case 'quiz':
                $class_name = 'LU_Quiz';
                break;
            case 'lesson':
            default:
                $class_name = 'LU_Lesson';
                break;
        }

        return new $class_name ( null, $args );
    }
}
