<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Core Taxonomies Class
 *
 * The default course type.
 *
 * @class 		LU_Core_Taxonomies
 * @version		1.0.0
 * @package		LucidLMS/Classes/Courses
 * @category	Class
 * @author 		New Normal
 */
class LU_Core_Taxonomies {

    /**
     * Slugs of all available for plugin taxonomies
     * @var array
     */
    public static $taxonomy_names = array(
        'atype' => 'course_type',
        'aetype' => 'course_element_type',
        'scstatus' => 'score_card_status',
        'qtype' => 'question_type',
    );

    /**
     * Array of taxonomies for internal use
     * @var array
     */
    protected $taxonomies = array();

    /**
     * Fetch from database all taxonomies at once, initialize their terms
     */
    public function __construct(){

        foreach( self::$taxonomy_names as $taxonomy) {

            $terms = get_terms( $taxonomy, 'hide_empty=0' );

            if(  !is_wp_error($terms)  ){
                foreach($terms as $term){
                    $this->taxonomies[$taxonomy][$term->slug] = array(
                        'term_id' => $term->term_id,
                        'name' => $term->name,
                        'slug' => $term->slug,
                    );
                }
            }
        }

    }


    /**
     * Get all available terms by taxonomy name
     * @param $taxonomy_name
     * @param bool $simplify
     * @return array
     */
    public function get_terms($taxonomy_name, $simplify = false){
        $result = array();
        if( isset($this->taxonomies[$taxonomy_name]) ){
            $result = $this->taxonomies[$taxonomy_name];
        }

        if( $simplify && !empty($result) ){
            $result_simplified = array();
            foreach($result as $term){
                $result_simplified[ $term['slug'] ] = $term['name'];
            }
            $result = $result_simplified;
        }
        return $result;
    }

    /**
     * Get array with terms id as key and slug as value by taxonomy name
     *
     * @param $taxonomy_name
     * @return array
     */
    public function get_terms_slugs($taxonomy_name){
        $result = array();
        if( $terms = $this->get_terms($taxonomy_name) ){
            foreach( $terms as $term ){
                $result[$term['term_id']] = $term['slug'];
            }
        }
        return $result;
    }


    /**
     * Get term by it's id or slug
     * @param $taxonomy_name
     * @param $term int|string
     * @return null
     */
    public function get_term($taxonomy_name, $term) {

        $result = null;

        if( $this->get_terms($taxonomy_name) ){
            if( is_int($term) ){
                foreach($this->taxonomies[$taxonomy_name] as $current_term){
                    if( $term == $current_term['term_id'] ){
                        $result = $current_term;
                        break;
                    }
                }
            } elseif( is_string($term) ){ //process like
                $result = isset($this->taxonomies[$taxonomy_name][$term]) ? $this->taxonomies[$taxonomy_name][$term] : null;
            }
        }
        return $result;
    }

    /**
     * Get printable name of a term
     * @param $taxonomy_name
     * @param $term
     * @return string
     */
    public function get_term_name($taxonomy_name, $term){
        if( $term_data = $this->get_term($taxonomy_name, $term) ){
            return $term_data['name'];
        }
        return '';
    }
    /**
     * Get term id by slug
     * @param $taxonomy_name
     * @param $term int|string
     * @return null
     */
    public function get_term_id($taxonomy_name, $term){
        if( $term = $this->get_term($taxonomy_name, $term) ){
            return $term['id'];
        }
        return null;
    }

}