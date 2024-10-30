<?php
if( ('yes' == get_option('lucidlms_bbpress_integration_enabled')) && is_bbpress_enabled() ):

    // Create or update forum when creating new course or course status changed
    add_action( 'lucid_process_course_meta', 'lucidlms_bbpress_forum_save', 10, 2 );

    // Trash/untrash/delete forum when appropriate course action is triggered
    add_action( 'wp_trash_post', 'lucidlms_bbpress_forum_trash' );
    add_action( 'untrash_post', 'lucidlms_bbpress_forum_untrash' );
    add_action( 'before_delete_post', 'lucidlms_bbpress_forum_delete' );

    // Triggered on settings page start loading
    add_action( 'lucidlms_settings_start', 'lucidlms_bbpress_settings_start' );

    // Delete course meta info when manually delete related forum
    add_action( 'bbp_delete_forum', 'lucidlms_bbpress_delete_course_meta' );

    // Change forum breadcrumbs
    add_filter( 'bbp_breadcrumbs', 'lucidlms_bbpress_breadcrumbs' );

    // Redirect from forums page to homepage
    add_filter( 'bbp_template_include', 'lucidlms_bbpress_forum_archive_redirect' );

    // Define show or hide forums and topics for current user
    add_action('template_redirect', 'lucidlms_bbpress_security_template_rendering' );

    // Fix for editor area border
    add_filter( 'the_editor', 'lucidlms_bbpress_the_editor' );

    // Add lucidlms bbpress templates into load stack
    add_filter( 'bbp_get_template_stack', 'lucidlms_bbpress_template_stack' );

endif;

// Create forums on integration enabling. It should be loaded even if bbpress is not enabled
add_action( 'update_option_lucidlms_bbpress_integration_enabled', 'lucidlms_bbpress_update_forums', 10, 2 );

/**
 * Check if bbpress plugin is enabled
 * @return bool
 */
function is_bbpress_enabled(){
    return function_exists('bbpress') && in_array( 'bbpress/bbpress.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
}

/**
 * Create new forum
 *
 * @param $course
 *
 * @return bool|int|WP_Error
 */
function create_new_forum($course) {
    // get bbPress instance
    bbpress();

    $forum_title = __( 'Discuss', 'lucidlms' ) . ' ' . $course->post_title;

    // Create new forum
    $forum_id = bbp_insert_forum( array(
        'post_parent'  => 0,
        'post_status'  => $course->post_status,
        'post_title'   => $forum_title,
    ) );

    // Add course meta
    add_post_meta( $course->ID, '_bbp_forum_id', $forum_id );

    return $forum_id;
}

/**
 * Create or update forum when creating new course or course status changed
 *
 * @param $post_id
 * @param $post
 */
function lucidlms_bbpress_forum_save( $post_id, $post ) {

    if ( ('course' == $post->post_type) ) {

        // Check forum id meta
        $forum_id = get_post_meta($post_id, '_bbp_forum_id', true);

        // Create new forum if not exists
        if ( empty($forum_id) ) {
            $forum_id = create_new_forum($post);
        }

        // Get forum current visibility
        $visibility = bbp_get_forum_visibility( $forum_id );

        // Make new forum hidden
        bbp_hide_forum( $forum_id, $visibility );
    }
}

/**
 * Trash/untrash/delete forum actions
 *
 * @param $post_id
 * @param $action
 */
function lucidlms_bbpress_forum_action($post_id, $action) {

    if ($post = get_post($post_id)) {

        if ( ('course' == $post->post_type) ) {

            $forum_id = get_post_meta($post_id, '_bbp_forum_id', true);
            if ( !empty($forum_id) ) {
                switch ($action) {
                    case 'trash':
                        wp_trash_post($forum_id);
                        break;

                    case 'untrash':
                        wp_untrash_post($forum_id);
                        break;

                    case 'delete':
                        wp_delete_post($forum_id);
                        break;

                    default :
                        break;
                }
            }
        }
    }
}

/**
 * Trash forum wrapper
 *
 * @param $post_id
 */
function lucidlms_bbpress_forum_trash($post_id) {
    lucidlms_bbpress_forum_action($post_id, 'trash');
}

/**
 * Untrash forum wrapper
 *
 * @param $post_id
 */
function lucidlms_bbpress_forum_untrash($post_id) {
    lucidlms_bbpress_forum_action($post_id, 'untrash');
}

/**
 * Delete forum wrapper
 *
 * @param $post_id
 */
function lucidlms_bbpress_forum_delete($post_id) {
    lucidlms_bbpress_forum_action($post_id, 'delete');
}

/**
* Fetch courses that still are not created
* @return array
*/
function lucidlms_bbpress_get_uncreated_forums(){
    $courses = get_posts( array(
        'post_type' => 'course',
        'posts_per_page' => -1,
        'post_status' => array('publish', 'draft', 'not_active'),
        'meta_query' => array(
            array(
                'key' => '_bbp_forum_id',
                'compare' => 'NOT EXISTS',
                'value' => 'completely:)', // due to bug #23268 @link https://core.trac.wordpress.org/ticket/23268
            )

        ),
    ));

    return $courses;
}

/**
* Fetch courses that still exists
* @return array
*/
function lucidlms_bbpress_get_undeleted_forums(){
    $courses = get_posts( array(
        'post_type' => 'course',
        'posts_per_page' => -1,
        'post_status' => array('publish', 'draft', 'not_active'),
        'meta_query' => array(
            array(
                'key' => '_bbp_forum_id',
                'compare' => 'EXISTS',
            )
        ),
    ));

    return $courses;
}

/**
 * Create forums for courses that are exist when user turns on bbPress integration
 * @param $old_value
 * @param $new_value
 */
function lucidlms_bbpress_update_forums($old_value, $new_value){
    if( !is_bbpress_enabled() ) return;

    if( $new_value == "yes" ){
        //create forums
        if( $courses = lucidlms_bbpress_get_uncreated_forums() ){
            foreach( $courses as $course ){
                // Create new forums for courses
                $forum_id = create_new_forum($course);
                bbp_hide_forum($forum_id);
            }
        }

    }
}
/**
 * Delete/create courses processing
 */
function lucidlms_bbpress_settings_start() {
    global $current_tab;

    // Check for current tab
    $current_tab = isset($_GET['tab']) ? sanitize_title( $_GET['tab'] ) : '';
    if ( 'bbpress_integration' == $current_tab ) {

        if( isset($_POST['lucidlms_bbpress_delete_forums']) ){
            // Get all courses which have forums
            $courses = lucidlms_bbpress_get_undeleted_forums();

            if ( !empty($courses) ) {

                // Delete courses meta info and all related forums
                foreach ($courses as $course) {
                    delete_post_meta( $course->ID, '_bbp_forum_id' );
                    wp_delete_post($course->forum_id);
                }

                LU_Admin_Settings::add_message( __('All forums deleted', 'lucidlms') );

            } else {
                LU_Admin_Settings::add_message( __('All forums already deleted', 'lucidlms') );
            }

        } elseif( isset($_POST['lucidlms_bbpress_create_forums']) ){
            if( !is_bbpress_enabled() ){
                LU_Admin_Settings::add_error( __('bbPress plugin is not activated', 'lucidlms') );
                return;
            }
            // Get all courses except those that already have forums
            $courses = lucidlms_bbpress_get_uncreated_forums();

            if ( !empty($courses) ) {
                // Create new forums for courses
                foreach( $courses as $course ){
                    $forum_id = create_new_forum($course);
                    bbp_hide_forum($forum_id);
                }

                LU_Admin_Settings::add_message( __('New forums created', 'lucidlms') );

            } else {
                LU_Admin_Settings::add_message( __('All forums already exist', 'lucidlms') );
            }
        }
    }
}

/**
 * Get course id by forum id
 *
 * @param string $key
 * @param mixed $value
 * @return int|bool
 */
function get_course_id_by_forum_id($forum_id) {
    global $wpdb;

    // Get course id depending on forum id
    $course_id = $wpdb->get_var( $wpdb->prepare("
        SELECT p.`ID`
        FROM $wpdb->posts AS p
        LEFT JOIN $wpdb->postmeta AS pm
            ON p.`ID`= pm.`post_id`
        WHERE p.`post_type` = 'course'
            AND pm.`meta_key` = '_bbp_forum_id'
            AND pm.`meta_value` = %d
    ", $forum_id ) );

    if ( null !== $course_id )
        $course_id = (int) $course_id;

    return $course_id;
}

/**
 * Get post id by post name
 *
 * @param $post_type
 * @param $post_name
 *
 * @return int|null|string
 */
function get_post_id_by_post_name($post_type, $post_name) {
    global $wpdb;

    // Get post id depending on post name
    $post_id = $wpdb->get_var( $wpdb->prepare("
        SELECT p.`ID`
        FROM $wpdb->posts AS p
        WHERE p.`post_type` = %s
            AND p.`post_name` = %s
    ", $post_type, $post_name ) );

    if ( null !== $post_id )
        $post_id = (int) $post_id;

    return $post_id;
}

/**
 * Get forum id by topic id
 *
 * @param $topic_id
 *
 * @return int
 */
function get_forum_id_by_topic_id($topic_id) {
    global $wpdb;

    // Get forum id depending on topic id
    $post_id = $wpdb->get_var( $wpdb->prepare("
        SELECT p.`post_parent`
        FROM $wpdb->posts AS p
        WHERE p.`ID` = %d
            AND p.`post_type` = 'topic'
    ", $topic_id ) );

    return (int) $post_id;
}

/**
 * Delete course meta info when manually delete related forum
 *
 * @param $forum_id
 */
function lucidlms_bbpress_delete_course_meta($forum_id) {

    $course_id = get_course_id_by_forum_id($forum_id);

    if ( null !== $course_id ) {
        // Delete course meta info
        delete_post_meta( $course_id, '_bbp_forum_id' );
    }
}

/**
 * Redirect to the homepage on forums archive page
 *
 * @param $template
 *
 * @return mixed
 */
function lucidlms_bbpress_forum_archive_redirect($template) {
    if ( bbp_is_forum_archive() ) {
        wp_redirect( home_url() );
        exit;
    }

    return $template;
}

/**
 * Remove forums archive link from breadcrumbs
 *
 * @param $crumbs
 *
 * @return mixed
 */
function lucidlms_bbpress_breadcrumbs($crumbs) {
    global $post;

    if ( !empty($crumbs) ) {
        foreach ($crumbs as $key => $crumb) {

            // Remove home page link
            if ( strpos($crumb, 'bbp-breadcrumb-home') ) {
                unset($crumbs[$key]);
            }

            // Replace root forum link to course link
            if ( strpos($crumb, 'bbp-breadcrumb-root') ) {

                // Get forum id depending on post type
                if ('forum' == $post->post_type) {
                    $forum_id = $post->ID;
                } elseif ('topic' == $post->post_type) {
                    $forum_id = get_forum_id_by_topic_id($post->ID);
                }

                if ( !empty($forum_id) )
                    $course_id = get_course_id_by_forum_id($forum_id);

                if ( !empty($course_id) )
                    $crumbs[$key] = '<a href="' . get_permalink($course_id) . '" class="bbp-breadcrumb-root">' . get_the_title($course_id) . '</a>';
            }
        }
    }

    return $crumbs;
}

/**
 * Define show or hide forums and topics for current user depending on user's score card
 */
function lucidlms_bbpress_security_template_rendering() {
    global $wp_query, $post, $current_user;

    if ( ! ( $wp_query->query_vars['post_type'] === 'forum' || $wp_query->query_vars['post_type'] === 'topic' ) ) {
        return;
    }

    // bbPress instance
    $bbp = bbpress();

    // Get main vars
    $user_id   = $current_user->ID;
    $post_name = $wp_query->query['name'];
    $post_type = $wp_query->query['post_type'];

    // Secondary vars for default
    $course_id = null;
    $forum_id    = null;
    $topic_id    = null;

    // Get course id depending on post type
    switch ($post_type) {
        case 'course':
            $course_id = $post->ID;
            break;

        case 'forum':
            $forum_id = get_post_id_by_post_name($post_type, $post_name);
            if ( !empty($forum_id) )
                $course_id = get_course_id_by_forum_id($forum_id);
            break;

        case 'topic':
            $topic_id = get_post_id_by_post_name($post_type, $post_name);
            $forum_id = get_forum_id_by_topic_id($topic_id);
            if ( !empty($topic_id) && !empty($forum_id) )
                $course_id = get_course_id_by_forum_id($forum_id);
            break;

        default:
            return;
    }

    // Try to get started or completed user score card
    $sc_started = lucidlms_get_current_score_card($user_id, $course_id, 'sc_started');
    $sc_completed = lucidlms_get_current_score_card($user_id, $course_id, 'sc_completed');

    // If user has active score card
    if ( null !== $sc_started || null !== $sc_completed ) {

        // Check for already exists caps
        if ( ! $current_user->has_cap( 'read_hidden_forums' ) ) {

            // Add read hidden forums user caps and rewrite current user object
            $current_user->add_cap( 'read_hidden_forums' );
            $current_user = new WP_User($user_id);
            $bbp->current_user = $current_user;

            // Add additional actions for forums and topics
            switch ($post_type) {
                case 'forum':
                    add_filter( 'bbp_is_single_forum', '__return_true');
                    $bbp->current_forum_id = $forum_id;
                    $wp_query->post->ID = $forum_id;
                    break;

                case 'topic':
                    add_filter( 'bbp_is_single_topic', '__return_true');
                    $bbp->current_topic_id = $topic_id;
                    $wp_query->post->ID = $topic_id;
                    break;

                default:
                    return;
            }
        }

    } else {

        // Check for already exists caps
        if ( $current_user->has_cap( 'read_hidden_forums' ) ) {

            // Remove read hidden forums user caps and rewrite current user object
            $current_user->remove_cap( 'read_hidden_forums' );
            $current_user = new WP_User($user_id);
            $bbp->current_user = $current_user;

            // Remove additional actions for forums and topics
            switch ($post_type) {
                case 'forum':
                    remove_filter( 'bbp_is_single_forum', '__return_true');
                    $bbp->current_forum_id = 0;
                    $wp_query->post->ID = 0;
                    break;

                case 'topic':
                    remove_filter( 'bbp_is_single_topic', '__return_true');
                    $bbp->current_topic_id = 0;
                    $wp_query->post->ID = 0;
                    break;

                default:
                    return;
            }
        }
    }
}

/**
 * Fix for editor area border
 *
 * @param $output
 *
 * @return mixed
 */
function lucidlms_bbpress_the_editor($output) {
    $output = str_replace(' wp-editor-area', '', $output);

    return $output;
}

/**
 * Add lucidlms bbpress templates into load stack
 *
 * @param $stack
 *
 * @return mixed
 */
function lucidlms_bbpress_template_stack($stack) {

    foreach ($stack as $key => $location) {
        if ( strpos($location, 'plugins/bbpress') ) {
            array_splice( $stack, $key, 0, LU()->plugin_path() . '/templates/bbpress' );
            break;
        }

    }

    return $stack;
}

