<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Cron jobs to execute
 *
 * @class          LU_Certificate
 * @version        1.0.0
 * @package        LucidLMS/Classes/Cron
 * @category       Class
 * @author         New Normal
 */

class LU_Cron {

    /**
     * The method to start all cron tasks
     * It runs all methods from current class except "run_all_methods"
     * and that one, whose names are started with "_"
     * ex. _check_smth()
     */
    static function run_all_methods() {
        $methods = get_class_methods(__CLASS__);
            foreach ($methods as $method_name) {
            $method = __CLASS__ . '::' .$method_name;

            // we're marking all unused methods with underscore
            if ( ($method != __METHOD__) && ('_' != substr($method_name, 0, 1) )) {
                call_user_func($method);
            }

        }
    }

    public static function check_expired_score_cards(){
        $affected_score_cards = 0;

        $args = array(
            'post_type' => 'score_card',
            'post_status' => array('published'),
            'tax_query' => array(
                array(
                    'taxonomy' => SCSTATUS,
                    'field' => 'slug',
                    'terms' => 'sc_started',
                ),
            ),
            'meta_query' => array(
                array(
                    'key' => '_expire_date_timestamp',
                    'value' => time(),
                    'compare' => '<=',
                    'type' => 'numeric'
                ),
            ),
        );

        if( $posts = get_posts($args) ) {
            foreach( $posts as $post ){
                $score_card = new LU_Score_Card($post);

                $score_card->set_status('sc_expired');
                if( $score_card->update_status() ){ // write changes to db
                    $affected_score_cards++;
                }
            }
        }

        return $affected_score_cards;
    }

}