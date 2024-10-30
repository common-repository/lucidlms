<?php
/**
 * LucidLMS Score Card Functions
 *
 * @author        New Normal
 * @category      Core
 * @package       LucidLMS/Functions
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Get score cards query
 *
 * @param array $data
 * @param bool  $return_obj
 *
 * @return array
 */
function lucidlms_get_score_card_by(array $data, $return_obj = true){
    $args = array (
        'posts_per_page' => -1,
        'post_type' => 'score_card',
        'meta_query' => array(),
        'tax_query' => array(),
        'orderby'   => 'date',
    );
    if( !empty($data) && is_array($data) ){
        foreach( $data as $key => $value){
            switch( $key ){
                case 'user_id':
                    $args['meta_query'][] = array(
                        'key' => '_student_id',
                        'value' => $value,
                    );
                    break;
                case 'course_id':
                    $args['meta_query'][] = array(
                        'key' => '_course_id',
                        'value' => $value,
                    );
                    break;
                case 'course_type':
                    $args['meta_query'][] = array(
                        'key' => '_course_type',
                        'value' => $value,
                    );
                    break;
                case 'status':
                    $args['tax_query'][] = array(
                        'taxonomy' => SCSTATUS,
                        'field' => 'slug',
                        'terms' => $value,
                    );
                    break;
                case 'product_id':
                    if( function_exists('find_course_by_product') ){
                        $course_id = find_course_by_product($value);
                        if( $course_id ){
                            $args['meta_query'][] = array(
                                'key' => '_course_id',
                                'value' => $course_id,
                            );
                        } else {
                            return array(); // no course found, so query should retrieve empty result
                        }
                    }
                    break;
                default:
                    // smth
                    break;
            }
        }
    }

    $score_cards = array();
    if( $res = get_posts( $args ) ){
        foreach( $res as $score_card_post){
            $score_cards[] = $return_obj ? new LU_Score_Card($score_card_post->ID) : $score_card_post->ID;
        }
    }

    return $score_cards;
}

/**
 * @param $user_id
 * @param array $args
 * @param bool $return_obj
 * @return array|LU_Score_Card[]
 */
function lucidlms_get_score_card($user_id, $args = array(), $return_obj = true){
    $args['user_id'] = $user_id;

    return lucidlms_get_score_card_by( $args, $return_obj);
}
/**
 * Get current (started by default) score card object or null if not exists
 * @param $user_id
 * @param $course_id
 * @param $status
 * @param null $product_id
 * @return LU_Score_Card|null
 */
function lucidlms_get_current_score_card($user_id, $course_id, $status = 'sc_started', $product_id = null){
    $args = array(
        'user_id' => $user_id,
        'status' => $status,
    );

    if( !is_null($product_id) ){
        $args['product_id'] = $product_id;
    }

    if( !isset($args['product_id']) ){ //if product id was not set, use course id
        $args['course_id'] = $course_id;
    }

    if( $started_score_cards = lucidlms_get_score_card_by( $args ) ){
        return current($started_score_cards);
    }

    return null;
}