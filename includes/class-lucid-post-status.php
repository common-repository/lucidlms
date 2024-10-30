<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Post types
 *
 * Registers post types and taxonomies
 *
 * @class          LU_Post_Status
 * @version        1.0.2
 * @package        LucidLMS/Classes/
 * @category       Class
 * @author         New Normal
 */
class LU_Post_Status {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'register_post_statuses' ), 10 );
        add_action( 'post_submitbox_misc_actions', array( __CLASS__, 'my_post_submitbox_misc_actions') );
//        add_action( 'edited_term_taxonomy', array( __CLASS__, 'fix_custom_post_status_count'), 10, 2 );

        add_filter( 'display_post_states', array(__CLASS__, 'admin_display_custom_post_status'), 10, 2 );

    }

    public static function get_custom_post_statuses_list(){
        return apply_filters('lucidlms_custom_post_statuses',
            array(
                'course' => array(
                    'not_active' => array(
                        'label' => _x( 'Not Active', 'Status General Name', 'lucidlms' ),
                        'label_count' => _n_noop( 'Not Active (%s)',  'Not Active (%s)', 'lucidlms' ),
                        'public'                    => true,
                        'show_in_admin_all_list'    => true,
                        'show_in_admin_status_list' => true,
                        'exclude_from_search'       => false,
                    )
                ),
            )
        );
    }

	/**
	 * Register Lucid LMS post statuses.
	 */
	public static function register_post_statuses() {

		do_action( 'lucid_register_post_status' );

        foreach( self::get_custom_post_statuses_list() as $post_type => $custom_statuses ){
            foreach( $custom_statuses as $post_status => $args ){
                $defaults = array(
                    'label'                     => false,
                    'label_count'               => false,
                    'public'                    => true,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'exclude_from_search'       => false,
                );
                register_post_status( $post_status, wp_parse_args($args, $defaults));
            }
        }

		do_action( 'lucid_after_register_post_status' );
	}

    public static function my_post_submitbox_misc_actions(){

        global $post;

        foreach( self::get_custom_post_statuses_list() as $post_type => $custom_statuses ){
            if( $post->post_type == $post_type ): ?>

                <script>
                    jQuery(document).ready(function($){
                        var $post_status_select = $("select#post_status");

                        <?php foreach( $custom_statuses as $post_status => $args ): $label = isset($args['label']) ? $args['label'] : $post_status; ?>
                            $post_status_select.append(
                                "<option value=\"<?php echo $post_status ?>\" <?php echo $post->post_status == $post_status ? 'selected=\"selected\"' : ''?>><?php echo $label ?></option>"
                            );
                            <?php if($post->post_status == $post_status): ?>
                                $(".misc-pub-section label").append(" <span id=\"post-status-display\"><?php echo $label ?></span>");
                            <?php endif; ?>
                        <?php endforeach; ?>

                    });
                </script>

            <?php endif;

        }
    }

    /**
     * Force Wordpress count our custom post statuses in the term count
     * @param $term_id
     * @param $taxonomy_name
     */
    public static function fix_custom_post_status_count($term_id, $taxonomy_name){
        global $wpdb;

        $taxonomy = get_taxonomy($taxonomy_name);
        $object_types = (array) $taxonomy->object_type;
        foreach ( $object_types as &$object_type )
            list( $object_type ) = explode( ':', $object_type );

        $object_types = array_unique( $object_types );
        if ( $object_types )
            $object_types = esc_sql( array_filter( $object_types, 'post_type_exists' ) );

        $custom_post_statuses = self::get_custom_post_statuses_list();
        if( $custom_post_statuses && $object_types ){

            $term = get_term($term_id, $taxonomy->taxonomy_name);
            $count = $term->count;

            foreach( $object_types as $type ){
                if( isset($custom_post_statuses[$type]) ){
                    $custom_statuses = array_keys($custom_post_statuses[$type]);

                    $count += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id AND post_status IN ('" . implode("', '", $custom_statuses ) . "') AND post_type IN ('" . implode("', '", $object_types ) . "') AND term_taxonomy_id = %d", $term_id ) );
                }
            }
            if( $count != $term->count ){
                $wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term_id ) );
            }

        }
    }

    /**
     * Show labels for custom post types in admin post type list
     * @param $post_states
     * @param $post
     * @return mixed
     */

    public static function admin_display_custom_post_status($post_states, $post){
        $all_custom_statuses = self::get_custom_post_statuses_list();

        if( isset($all_custom_statuses[$post->post_type]) ){
            $custom_post_statuses = $all_custom_statuses[$post->post_type];

            if( isset($custom_post_statuses[$post->post_status]) ){
                $post_states[$post->post_status] = $custom_post_statuses[$post->post_status]['label'];
            }
        }
        return $post_states;
    }

}

new LU_Post_Status();

