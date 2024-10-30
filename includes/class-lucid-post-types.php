<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Post types
 *
 * Registers post types and taxonomies
 *
 * @class          LU_Post_types
 * @version        1.0.2
 * @package        LucidLMS/Classes/Courses
 * @category       Class
 * @author         New Normal
 */
class LU_Post_types {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
		add_action( 'save_post', array( __CLASS__, 'course_set_default_object_terms' ), 100, 2 );
	}

	/**
	 * Register Lucid LMS taxonomies.
	 */
	public static function register_taxonomies() {
		if ( taxonomy_exists( 'course_type' ) ) {
			return;
		}

		do_action( 'lucid_register_taxonomy' );

		$permalinks = get_option( 'lucid_permalinks' );

		register_taxonomy( 'course_type',
			apply_filters( 'lucid_taxonomy_objects_course_type', array( 'course' ) ),
			apply_filters( 'lucid_taxonomy_args_course_type', array(
				'hierarchical'      => false,
				'show_ui'           => false,
				'show_in_nav_menus' => false,
				'query_var'         => is_admin(),
				'rewrite'           => false,
				'public'            => false
			) )
		);

		register_taxonomy( 'course_cat',
			apply_filters( 'lucid_taxonomy_objects_course_cat', array( 'course' ) ),
			apply_filters( 'lucid_taxonomy_args_course_cat', array(
				'hierarchical' => true,
				'label'        => __( 'Course Categories', 'lucidlms' ),
				'labels'       => array(
					'name'              => __( 'Course Categories', 'lucidlms' ),
					'singular_name'     => __( 'Course Category', 'lucidlms' ),
					'menu_name'         => _x( 'Categories', 'Admin menu name', 'lucidlms' ),
					'search_items'      => __( 'Search Course Categories', 'lucidlms' ),
					'all_items'         => __( 'All Course Categories', 'lucidlms' ),
					'parent_item'       => __( 'Parent Course Category', 'lucidlms' ),
					'parent_item_colon' => __( 'Parent Course Category:', 'lucidlms' ),
					'edit_item'         => __( 'Edit Course Category', 'lucidlms' ),
					'update_item'       => __( 'Update Course Category', 'lucidlms' ),
					'add_new_item'      => __( 'Add New Course Category', 'lucidlms' ),
					'new_item_name'     => __( 'New Course Category Name', 'lucidlms' )
				),
				'show_ui'      => true,
				'query_var'    => true,
				'rewrite'      => array(
					'slug'         => empty( $permalinks['category_base'] ) ? _x( 'course-category', 'slug', 'lucidlms' ) : $permalinks['category_base'],
					'with_front'   => false,
					'hierarchical' => true,
				),
			) )
		);

        register_taxonomy('course_tag', 'course', array(
            'hierarchical' => false,
            'labels' => apply_filters('lucidlms_course_tags_labels', array(
                'name' => _x( 'Tags', 'taxonomy general name', 'lucidlms' ),
                'singular_name' => _x( 'Tag', 'taxonomy singular name', 'lucidlms' ),
                'search_items' =>  __( 'Search Tags', 'lucidlms' ),
                'popular_items' => __( 'Popular Tags', 'lucidlms' ),
                'all_items' => __( 'All Tags', 'lucidlms' ),
                'parent_item' => null,
                'parent_item_colon' => null,
                'edit_item' => __( 'Edit Tag', 'lucidlms' ),
                'update_item' => __( 'Update Tag', 'lucidlms' ),
                'add_new_item' => __( 'Add New Tag', 'lucidlms' ),
                'new_item_name' => __( 'New Tag Name', 'lucidlms' ),
                'separate_items_with_commas' => __( 'Separate tags with commas', 'lucidlms' ),
                'add_or_remove_items' => __( 'Add or remove tags', 'lucidlms' ),
                'choose_from_most_used' => __( 'Choose from the most used tags', 'lucidlms' ),
                'menu_name' => __( 'Tags', 'lucidlms' ),
            )),
            'show_ui' => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var' => true,
            'rewrite' => array( 'slug' => 'tag' ),
        ));

		register_taxonomy( 'course_element_type',
			apply_filters( 'lucid_taxonomy_objects_course_element_type', array( 'course_element' ) ),
			apply_filters( 'lucid_taxonomy_args_course_element_type', array(
				'hierarchical'      => false,
				'show_ui'           => false,
				'show_in_nav_menus' => false,
				'query_var'         => is_admin(),
				'rewrite'           => false,
				'public'            => false
			) )
		);

		register_taxonomy( 'score_card_status',
			apply_filters( 'lucid_taxonomy_objects_score_card_status', array( 'score_card' ) ),
			apply_filters( 'lucid_taxonomy_args_score_card_status', array(
				'hierarchical'      => false,
				'show_ui'           => false,
				'show_in_nav_menus' => false,
				'query_var'         => is_admin(),
				'rewrite'           => false,
				'public'            => false
			) )
		);

		register_taxonomy( 'question_type',
			apply_filters( 'lucid_taxonomy_objects_question_type', array( 'question' ) ),
			apply_filters( 'lucid_taxonomy_args_question_type', array(
				'hierarchical'      => false,
				'show_ui'           => false,
				'show_in_nav_menus' => false,
				'query_var'         => is_admin(),
				'rewrite'           => false,
				'public'            => false
			) )
		);

        register_taxonomy( 'question_cat',
            apply_filters( 'lucid_taxonomy_objects_question_cat', array( 'course' ) ),
            apply_filters( 'lucid_taxonomy_args_question_cat', array(
                'hierarchical'      => false,
                'show_ui'           => false,
                'show_in_nav_menus' => false,
                'query_var'         => is_admin(),
                'rewrite'           => false,
                'public'            => false,
            ) )
        );

		do_action( 'lucid_after_register_taxonomy' );
	}

	/**
	 * Register core post types
	 */
	public static function register_post_types() {
		if ( post_type_exists( 'course' ) ) {
//			return;
		}

		do_action( 'lucid_register_post_type' );


		$permalinks         = get_option( 'lucid_permalinks' );
		$course_permalink = empty( $permalinks['course_base'] ) ? _x( 'course', 'slug', 'lucidlms' ) : $permalinks['course_base'];

		register_post_type( 'course',
			apply_filters( 'lucid_register_post_type_course',
				array(
					'labels'              => array(
						'name'               => __( 'Courses', 'lucidlms' ),
						'singular_name'      => __( 'Course', 'lucidlms' ),
						'menu_name'          => _x( 'Courses', 'Admin menu name', 'lucidlms' ),
						'add_new'            => __( 'Add Course', 'lucidlms' ),
						'add_new_item'       => __( 'Add New Course', 'lucidlms' ),
						'edit'               => __( 'Edit', 'lucidlms' ),
						'edit_item'          => __( 'Edit Course', 'lucidlms' ),
						'new_item'           => __( 'New Course', 'lucidlms' ),
						'view'               => __( 'View Course', 'lucidlms' ),
						'view_item'          => __( 'View Course', 'lucidlms' ),
						'search_items'       => __( 'Search Courses', 'lucidlms' ),
						'not_found'          => __( 'No Courses found', 'lucidlms' ),
						'not_found_in_trash' => __( 'No Courses found in trash', 'lucidlms' ),
						'parent'             => __( 'Parent Course', 'lucidlms' )
					),
					'description'         => __( 'This is where you can add new courses to your system.', 'lucidlms' ),
					'public'              => true,
					'show_ui'             => true,
					'menu_icon'           => plugins_url('lucidlms/assets/images/lucid_logo16x16.png'),
					'capability_type'     => 'course',
					'map_meta_cap'        => true,
					'publicly_queryable'  => true,
					'exclude_from_search' => false,
					'hierarchical'        => false,
					'rewrite'             => $course_permalink ? array( 'slug'       => untrailingslashit( $course_permalink ),
					                                                      'with_front' => false,
					                                                      'feeds'      => true
					) : false,
					'query_var'           => true,
					'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
					'has_archive'         => ( $courses_page_id = lucid_get_page_id( 'courses' ) ) && get_post( $courses_page_id ) ? get_page_uri( $courses_page_id ) : 'courses',
					'show_in_nav_menus'   => true,
				)
			)
		);


		register_post_type( "score_card",
			apply_filters( 'lucid_register_post_type_score_card',
				array(
					'labels'              => array(
						'name'               => __( 'Score Cards', 'lucidlms' ),
						'singular_name'      => __( 'Score Card', 'lucidlms' ),
						'add_new'            => __( 'Add Score Card', 'lucidlms' ),
						'add_new_item'       => __( 'Add New Score Card', 'lucidlms' ),
						'edit'               => __( 'Edit', 'lucidlms' ),
						'edit_item'          => __( 'Edit Score Card', 'lucidlms' ),
						'new_item'           => __( 'New Score Card', 'lucidlms' ),
						'view'               => __( 'View Score Card', 'lucidlms' ),
						'view_item'          => __( 'View Score Card', 'lucidlms' ),
						'search_items'       => __( 'Search Score Cards', 'lucidlms' ),
						'not_found'          => __( 'No Score Cards found', 'lucidlms' ),
						'not_found_in_trash' => __( 'No Score Cards found in trash', 'lucidlms' ),
						'parent'             => __( 'Parent Score Cards', 'lucidlms' ),
						'menu_name'          => _x( 'Score Cards', 'Admin menu name', 'lucidlms' ),
					),
					'description'         => __( 'This is where score cards are stored.', 'lucidlms' ),
					'public'              => false,
					'show_ui'             => true,
					'menu_icon'           => plugins_url('lucidlms/assets/images/lucid_logo16x16.png'),
					'capability_type'     => 'score_card',
					'map_meta_cap'        => true,
					'publicly_queryable'  => false,
					'exclude_from_search' => true,
					'show_in_menu'        => current_user_can( 'manage_lucidlms' ) ? 'lucidlms' : true,
					'menu_position'       => 70,
					'hierarchical'        => false,
					'show_in_nav_menus'   => false,
					'rewrite'             => false,
					'query_var'           => false,
					'supports'            => false,
					'has_archive'         => false,
				)
			)
		);


		register_post_type( "course_element",
			apply_filters( 'lucid_register_post_type_course_element',
				array(
					'labels'              => array(
						'name'               => __( 'Course Elements', 'lucidlms' ),
						'singular_name'      => __( 'Course Element', 'lucidlms' ),
						'menu_name'          => _x( 'Course Elements', 'Admin menu name', 'lucidlms' ),
						'add_new'            => __( 'Add Course Element', 'lucidlms' ),
						'add_new_item'       => __( 'Add New Course Element', 'lucidlms' ),
						'edit'               => __( 'Edit', 'lucidlms' ),
						'edit_item'          => __( 'Edit Course Element', 'lucidlms' ),
						'new_item'           => __( 'New Course Element', 'lucidlms' ),
						'view'               => __( 'View Course Elements', 'lucidlms' ),
						'view_item'          => __( 'View Course Element', 'lucidlms' ),
						'search_items'       => __( 'Search Course Elements', 'lucidlms' ),
						'not_found'          => __( 'No Course Elements found', 'lucidlms' ),
						'not_found_in_trash' => __( 'No Course Elements found in trash', 'lucidlms' ),
						'parent'             => __( 'Parent Course Element', 'lucidlms' )
					),
					'description'         => __( 'This is where you can add new elements for your courses.', 'lucidlms' ),
					'public'              => false,
					'show_ui'             => true,
					'capability_type'     => 'course_element',
					'map_meta_cap'        => true,
					'publicly_queryable'  => true,
					'exclude_from_search' => true,
					'hierarchical'        => false,
					'rewrite'             => false,
					'query_var'           => false,
					'supports'            => array( 'title', 'editor', 'page-attributes' ),
					'show_in_nav_menus'   => false,
					'show_in_menu'        => false,
					'show_in_admin_bar'   => false,
				)
			)
		);
		register_post_type( "question",
			apply_filters( 'lucid_register_post_type_question',
				array(
					'labels'              => array(
						'name'               => __( 'Questions', 'lucidlms' ),
						'singular_name'      => __( 'Question', 'lucidlms' ),
						'menu_name'          => _x( 'Questions', 'Admin menu name', 'lucidlms' ),
						'add_new'            => __( 'Add Question', 'lucidlms' ),
						'add_new_item'       => __( 'Add New Question', 'lucidlms' ),
						'edit'               => __( 'Edit', 'lucidlms' ),
						'edit_item'          => __( 'Edit Question', 'lucidlms' ),
						'new_item'           => __( 'New Question', 'lucidlms' ),
						'view'               => __( 'View Questions', 'lucidlms' ),
						'view_item'          => __( 'View Question', 'lucidlms' ),
						'search_items'       => __( 'Search Questions', 'lucidlms' ),
						'not_found'          => __( 'No Questions found', 'lucidlms' ),
						'not_found_in_trash' => __( 'No Questions found in trash', 'lucidlms' ),
						'parent'             => __( 'Parent Question', 'lucidlms' )
					),
					'description'         => __( 'This is where you can add new questions that you can use in your quizzes.', 'lucidlms' ),
					'public'              => false,
					'show_ui'             => false,
					'capability_type'     => 'question',
					'map_meta_cap'        => true,
					'publicly_queryable'  => false,
					'exclude_from_search' => true,
					'hierarchical'        => false,
					'rewrite'             => false,
					'query_var'           => false,
					'supports'            => array( 'title', 'editor' ),
					'show_in_nav_menus'   => false,
					'show_in_admin_bar'   => true,
                    'taxonomies'          => array( 'question_cat' ),
				)
			)
		);


        register_post_type( "instructors",
            apply_filters( 'lucid_register_post_type_instructors',
                array(
                    'labels'              => array(
                        'name'               => __( 'Instructors', 'lucidlms' ),
                        'singular_name'      => __( 'Instructor', 'lucidlms' ),
                        'add_new'            => __( 'Add Instructor', 'lucidlms' ),
                        'add_new_item'       => __( 'Add New Instructor', 'lucidlms' ),
                        'edit'               => __( 'Edit', 'lucidlms' ),
                        'edit_item'          => __( 'Edit Instructor', 'lucidlms' ),
                        'new_item'           => __( 'New Instructor', 'lucidlms' ),
                        'view'               => __( 'View Instructors', 'lucidlms' ),
                        'view_item'          => __( 'View Instructor', 'lucidlms' ),
                        'search_items'       => __( 'Search Instructors', 'lucidlms' ),
                        'not_found'          => __( 'No Instructors found', 'lucidlms' ),
                        'not_found_in_trash' => __( 'No Instructors found in trash', 'lucidlms' ),
                        'parent'             => __( 'Parent Instructors', 'lucidlms' ),
                        'menu_name'          => _x( 'Instructors', 'Admin menu name', 'lucidlms' ),
                    ),
                    'description'         => __( 'This is where course instructors are stored.', 'lucidlms' ),
                    'public'              => true,
                    'show_ui'             => true,
                    'capability_type'     => 'post',
                    'map_meta_cap'        => true,
                    'publicly_queryable'  => true,
                    'exclude_from_search' => true,
                    'show_in_menu'        => true,
                    'hierarchical'        => false,
                    'rewrite'             => true,
                    'menu_icon'          =>  plugins_url('lucidlms/assets/images/lucid_logo16x16.png'),
                    'query_var'           => true,
                    'supports'            => array('title', 'editor', 'thumbnail'),
                    'has_archive'         => false,
                )
            )
        );
	}

	/**
	 * Set a default term for course_cat taxonomy if empty
	 */
	public static function  course_set_default_object_terms( $post_id, $post ) {
		if ( ( 'publish' === $post->post_status ) && ( $post->post_type == 'course' ) ) {

			$defaults = array(
				'course_cat' => array( 'other' => 'Other' ),
			);

			$taxonomies = get_object_taxonomies( $post->post_type );
			foreach ( (array) $taxonomies as $taxonomy ) {
				$terms = wp_get_post_terms( $post_id, $taxonomy );
				if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
					wp_set_object_terms( $post_id, $defaults[ $taxonomy ], $taxonomy );
				}
			}
		}
	}

}

new LU_Post_types();
