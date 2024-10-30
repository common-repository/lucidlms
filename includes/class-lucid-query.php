<?php
/**
 * Contains the query functions for LucidLMS which alter the front-end post queries and loops.
 *
 * @class          LU_Query
 * @version        1.0.0
 * @package        LucidLMS/Classes
 * @category       Class
 * @author         New Normal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'LU_Query' ) ) : /**
 * LU_Query Class
 */ {
	class LU_Query {

		/** @public array Query vars to add to wp */
		public $query_vars = array();

		/** @public array The meta query for the page */
		public $meta_query = '';

		/**
		 * Constructor for the query class. Hooks in methods.
		 *
		 * @access public
		 */
		public function __construct() {
			add_action( 'lucidlms_init', array( $this, 'add_endpoints' ) );

			if ( ! is_admin() ) {
				add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
				add_action( 'parse_request', array( $this, 'parse_request' ), 0 );
				add_filter( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
				add_filter( 'the_posts', array( $this, 'the_posts' ), 11, 2 );
				add_action( 'wp', array( $this, 'remove_course_query' ) );
			}

			$this->init_query_vars();
		}

		/**
		 * Init query vars by loading options.
		 */
		public function init_query_vars() {
			// TODO[future-releases]: Query vars to add to WP
			$this->query_vars = array(
			);
		}

		/**
		 * Add endpoints for query vars
		 */
		public function add_endpoints() {
			foreach ( $this->query_vars as $key => $var ) {
				add_rewrite_endpoint( $var, EP_PAGES );
			}
		}

		/**
		 * add_query_vars function.
		 *
		 * @access public
		 *
		 * @param array $vars
		 *
		 * @return array
		 */
		public function add_query_vars( $vars ) {
			foreach ( $this->query_vars as $key => $var ) {
				$vars[] = $key;
			}

			return $vars;
		}

		/**
		 * Get query vars
		 * @return array()
		 */
		public function get_query_vars() {
			return $this->query_vars;
		}

		/**
		 * Parse the request and look for query vars - endpoints may not be supported
		 */
		public function parse_request() {
			global $wp;

			// Map query vars to their keys, or get them if endpoints are not supported
			foreach ( $this->query_vars as $key => $var ) {
				if ( isset( $_GET[ $var ] ) ) {
					$wp->query_vars[ $key ] = $_GET[ $var ];
				} elseif ( isset( $wp->query_vars[ $var ] ) ) {
					$wp->query_vars[ $key ] = $wp->query_vars[ $var ];
				}
			}
		}

		/**
		 * Hook into pre_get_posts to do the main course query
		 *
		 * @access public
		 *
		 * @param mixed $q query object
		 *
		 * @return void
		 */
		public function pre_get_posts( $q ) {
			// We only want to affect the main query
			if ( ! $q->is_main_query() ) {
				return;
			}

			// When orderby is set, WordPress shows posts. Get around that here.
			if ( $q->is_home() && 'page' == get_option( 'show_on_front' ) && get_option( 'page_on_front' ) == lucid_get_page_id( 'courses' ) ) {
				$_query = wp_parse_args( $q->query );
				if ( empty( $_query ) || ! array_diff( array_keys( $_query ), array(
							'preview',
							'page',
							'paged',
							'cpage',
							'orderby'
						) )
				) {
					$q->is_page = true;
					$q->is_home = false;
					$q->set( 'page_id', get_option( 'page_on_front' ) );
					$q->set( 'post_type', 'course' );
				}
			}

			// Special check for courses page with the course archive on front
			if ( $q->is_page() && 'page' == get_option( 'show_on_front' ) && $q->get( 'page_id' ) == lucid_get_page_id( 'courses' ) ) {

				// This is a front-page courses
				$q->set( 'post_type', 'course' );
				$q->set( 'page_id', '' );
				if ( isset( $q->query['paged'] ) ) {
					$q->set( 'paged', $q->query['paged'] );
				}

				// Define a variable so we know this is the front page shop later on
				define( 'SHOP_IS_ON_FRONT', true );

				// Get the actual WP page to avoid errors and let us use is_front_page()
				// This is hacky but works. Awaiting http://core.trac.wordpress.org/ticket/21096
				global $wp_post_types;

				$courses_page = get_post( lucid_get_page_id( 'courses' ) );
				$q->is_page      = true;

				$wp_post_types['course']->ID         = $courses_page->ID;
				$wp_post_types['course']->post_title = $courses_page->post_title;
				$wp_post_types['course']->post_name  = $courses_page->post_name;
				$wp_post_types['course']->post_type  = $courses_page->post_type;
				$wp_post_types['course']->ancestors  = get_ancestors( $courses_page->ID, $courses_page->post_type );

				// Fix conditional Functions like is_front_page
				$q->is_singular          = false;
				$q->is_post_type_archive = true;
				$q->is_archive           = true;

			} else {

				// Only apply to course categories, the course post archive, the courses page, course tags, and course attribute taxonomies
				if ( ! $q->is_post_type_archive( 'course' ) && ! $q->is_tax( get_object_taxonomies( 'course' ) ) ) {
					return;
				}

			}

			$this->course_query( $q );

			if ( is_search() ) {
				add_filter( 'posts_where', array( $this, 'search_post_excerpt' ) );
				add_filter( 'wp', array( $this, 'remove_posts_where' ) );
			}

			add_filter( 'posts_where', array( $this, 'exclude_protected_courses' ) );

			// We're on a courses page so queue the lucidlms_get_courses_in_view function
			add_action( 'wp', array( $this, 'get_courses_in_view' ), 2 );

			// And remove the pre_get_posts hook
			$this->remove_course_query();
		}

		/**
		 * search_post_excerpt function.
		 *
		 * @access public
		 *
		 * @param string $where (default: '')
		 *
		 * @return string (modified where clause)
		 */
		public function search_post_excerpt( $where = '' ) {
			global $wp_the_query;

			// If this is not a LU Query, do not modify the query
			if ( empty( $wp_the_query->query_vars['lu_query'] ) || empty( $wp_the_query->query_vars['s'] ) ) {
				return $where;
			}

			$where = preg_replace(
				"/post_title\s+LIKE\s*(\'\%[^\%]+\%\')/",
				"post_title LIKE $1) OR (post_excerpt LIKE $1", $where );

			return $where;
		}

		/**
		 * Prevent password protected courses appearing in the loops
		 *
		 * @param  string $where
		 *
		 * @return string
		 */
		public function exclude_protected_courses( $where ) {
			global $wpdb;
			$where .= " AND {$wpdb->posts}.post_password = ''";

			return $where;
		}

		/**
		 * Hook into the_posts to do the main course query if needed - relevanssi compatibility
		 *
		 * @access public
		 *
		 * @param array         $posts
		 * @param WP_Query|bool $query (default: false)
		 *
		 * @return array
		 */
		public function the_posts( $posts, $query = false ) {
			// Abort if there's no query
			if ( ! $query ) {
				return $posts;
			}

			// Abort if we're not filtering posts
			if ( empty( $this->post__in ) ) {
				return $posts;
			}

			// Abort if this query has already been done
			if ( ! empty( $query->lu_query ) ) {
				return $posts;
			}

			// Abort if this isn't a search query
			if ( empty( $query->query_vars["s"] ) ) {
				return $posts;
			}

			// Abort if we're not on a post type archive/course taxonomy
			if ( ! $query->is_post_type_archive( 'course' ) && ! $query->is_tax( get_object_taxonomies( 'course' ) ) ) {
				return $posts;
			}

			$filtered_posts   = array();
			$queried_post_ids = array();

			foreach ( $posts as $post ) {
				if ( in_array( $post->ID, $this->post__in ) ) {
					$filtered_posts[]   = $post;
					$queried_post_ids[] = $post->ID;
				}
			}

			$query->posts      = $filtered_posts;
			$query->post_count = count( $filtered_posts );

			return $filtered_posts;
		}


		/**
		 * Query the courses. This applies to the main wordpress loop
		 *
		 * @access public
		 *
		 * @param mixed $q
		 *
		 * @return void
		 */
		public function course_query( $q ) {

			// Meta query
			$meta_query = $this->get_meta_query( $q->get( 'meta_query' ) );

			// Query vars that affect posts shown
			// TODO: decide with meta query, whether to leave or remove
//			$q->set( 'meta_query', $meta_query );
			$q->set( 'posts_per_page', $q->get( 'posts_per_page' ) ? $q->get( 'posts_per_page' ) : apply_filters( 'loop_courses_per_page', get_option( 'posts_per_page' ) ) );

			// Set a special variable
			$q->set( 'lu_query', true );

			// Store variables
			$this->meta_query = $meta_query;

			do_action( 'lucidlms_course_query', $q, $this );
		}


		/**
		 * Remove the query
		 *
		 * @access public
		 * @return void
		 */
		public function remove_course_query() {
			remove_filter( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
		}

		/**
		 * Remove the posts_where filter
		 *
		 * @access public
		 * @return void
		 */
		public function remove_posts_where() {
			remove_filter( 'posts_where', array( $this, 'search_post_excerpt' ) );
		}


		/**
		 * Get an unpaginated list all course ID's. Makes use of transients.
		 *
		 * @access public
		 * @return void
		 */
		public function get_courses_in_view() {
			global $wp_the_query;

			$unfiltered_course_ids = array();

			// Get main query
			$current_wp_query = $wp_the_query->query;

			// Get WP Query for current page (without 'paged')
			unset( $current_wp_query['paged'] );

			// Generate a transient name based on current query
			$transient_name = 'lucid_uf_pid_' . md5( http_build_query( $current_wp_query ) );
			$transient_name = ( is_search() ) ? $transient_name . '_s' : $transient_name;

			if ( false === ( $unfiltered_course_ids = get_transient( $transient_name ) ) ) {

				// Get all visible posts, regardless of filters
				$unfiltered_course_ids = get_posts(
					array_merge(
						$current_wp_query,
						array(
							'post_type'              => 'course',
							'numberposts'            => - 1,
							'post_status'            => 'publish',
							'meta_query'             => $this->meta_query,
							'fields'                 => 'ids',
							'no_found_rows'          => true,
							'update_post_meta_cache' => false,
							'update_post_term_cache' => false
						)
					)
				);

				set_transient( $transient_name, $unfiltered_course_ids, YEAR_IN_SECONDS );
			}

		}

		/**
		 * Appends meta queries to an array.
		 * @access public
		 *
		 * @param array $meta_query
		 *
		 * @return array
		 */
		public function get_meta_query( $meta_query = array() ) {
			if ( ! is_array( $meta_query ) ) {
				$meta_query = array();
			}

			$meta_query[] = $this->visibility_meta_query();

			return array_filter( apply_filters( 'lucidlms_course_get_meta_query', $meta_query ) );
		}

		/**
		 * Returns a meta query to handle course visibility
		 *
		 * @access public
		 *
		 * @param string $compare (default: 'IN')
		 *
		 * @return array
		 */
		public function visibility_meta_query( $compare = 'IN' ) {
			$in = apply_filters( 'lucidlms_course_visibility_meta_query', array( 'public' ) );

			$meta_query = array(
				'key'     => '_visibility',
				'value'   => $in,
				'compare' => $compare
			);

			return $meta_query;
		}

	}
}

endif;

return new LU_Query();
