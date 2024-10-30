<?php
/**
 * Main function for returning courses, uses the LU_course_factory class.
 *
 * @param mixed $the_course Post object or post ID of the course.
 *
 * @return LU_Course
 */
function get_course( $the_course = false ) {
	return LU()->course_factory->get_course( $the_course );
}

/**
 * Main function for returning course elements, uses the LU_course_Element_factory class.
 *
 * @param mixed $the_course_element
 *
 * @return LU_Lesson|LU_Quiz
 */
function get_course_element( $the_course_element = false ) {
	return LU()->course_element_factory->get_course_element( $the_course_element );
}

/**
 * Create new element (lesson, quiz etc) by given type
 *
 * @param string $type
 * @param array $args
 *
 * @return LU_Lesson|LU_Quiz
 */
function create_course_element( $type = '', $args = array() ) {
	return LU()->course_element_factory->create_course_element( $type, $args );
}

/**
 * Allow uploading different file types
 */
function update_edit_form() {
	echo ' enctype="multipart/form-data"';
} // end update_edit_form
add_action( 'post_edit_form_tag', 'update_edit_form' );


/**
 * Get the placeholder image URL for courses etc
 *
 * @access public
 * @return string
 */
function lucid_placeholder_img_src() {
	return apply_filters( 'lucidlms_placeholder_img_src', LU()->plugin_url() . '/assets/images/placeholder.png' );
}

/**
 * Get the placeholder image
 *
 * @access public
 *
 * @param string $size
 *
 * @return mixed|void
 */
function lucid_placeholder_img( $size = 'thumbnail' ) {
	$dimensions['width']  = get_option( $size . '_size_w' );
	$dimensions['height'] = get_option( $size . '_size_h' );
	$dimensions['crop']   = (bool) get_option( $size . '_crop' );

	$image_id = get_option( 'lucidlms_organization_logo' );
	$src      = lucid_placeholder_img_src();

	if ( ! empty ( $image_id ) ) {
		$attachment = wp_get_attachment_image_src( $image_id, $size );
		$src        = $attachment[0];
	}

	if ( empty( $src ) ) {
		return false;
	}

	return apply_filters( 'lucidlms_placeholder_img', '<img src="' . $src . '" alt="Placeholder" width="' . esc_attr( $dimensions['width'] ) . '" class="lucidlms-placeholder wp-post-image" height="' . esc_attr( $dimensions['height'] ) . '" />' );
}

/**
 * Get the course thumbnail image link
 *
 * @access public
 *
 * @param int $post_id
 * @param string $size
 *
 * @return bool|string
 */
function lucid_course_thumbnail_link( $post_id = null, $size = 'thumbnail' ) {
	$post_id           = ( null === $post_id ) ? get_the_ID() : $post_id;
	$post_thumbnail_id = get_post_thumbnail_id( $post_id );

	if ( $post_thumbnail_id ) {
		$attachment = wp_get_attachment_image_src( $post_thumbnail_id, $size );
		$src        = $attachment[0];

		return $src;
	} else {
		return false;
	}
}

/**
 * Get the placeholder image link
 *
 * @access public
 *
 * @param string $size
 *
 * @return mixed|void
 */
function lucid_placeholder_img_link( $size = 'thumbnail' ) {
	$image_id = get_option( 'lucidlms_organization_logo' );
	$src      = lucid_placeholder_img_src();

	if ( ! empty ( $image_id ) ) {
		$attachment = wp_get_attachment_image_src( $image_id, $size );
		$src        = $attachment[0];
	}

	if ( empty( $src ) ) {
		return false;
	}

	return apply_filters( 'lucid_placeholder_img_link', $src );
}

/**
 * Adds the explanation to Course edit page (near "Add Media" button)
 */
function lucidlms_course_edit_insert_media_explanation() {
	printf( '<span class="explanation">%s</span>',
		__( 'To embed a video, copy it\'s link and insert into text on a new line', 'lucidlms' )
	);
}

add_action( 'media_buttons', 'lucidlms_course_edit_insert_media_explanation', 30 );

/**
 * Returns list of course tags for Course custom post type
 *
 * @param $tags
 * @param $before
 * @param $sep
 * @param $after
 * @param $id
 *
 * @return bool|string|WP_Error
 */
function lucidlms_course_tags( $tags, $before, $sep, $after, $id ) {
	if ( $post = get_post( $id ) ) {
		if ( 'course' == $post->post_type ) {
			$tags = get_the_term_list( $id, 'course_tag', $before, $sep, $after );
		}
	}

	return $tags;
}

add_filter( 'the_tags', 'lucidlms_course_tags', 10, 5 );

function lucidlms_get_all_courses() {
	$args    = array(
		'post_type'      => 'course',
		'posts_per_page' => - 1,
		'post_status'    => array( 'publish', 'draft', 'not_active' ),

	);
	$posts   = get_posts( $args );
	$courses = array();
	foreach ( $posts as $post ) {
		if ( $course = get_course( $post ) ) {
			$courses[ $course->id ] = $course;
		}
	}

	return $courses;
}

/**
 * Send course name to remote server
 *
 * @param $ID
 * @param $post
 */
function lucidlms_get_publish_course_info( $ID, $post ) {
	if ( get_option( '_lucid_opt_in' ) == 'yes' ) {
		$body = array( 'domain' => get_site_url(), 'course[wp_id]' => $post->ID, 'course[name]' => $post->post_name );
		$url  = 'http://stat.lucidlms.com/api/v1/courses';

		$response = wp_remote_post( $url, array(
				'method' => 'POST',
				'body'   => $body,
			)
		);
	}
}

/**
 * Send course id to remote server to count enrolled students
 *
 * @param $id
 */
function lucidlms_get_started_course_info( $id ) {

	if ( get_option( '_lucid_opt_in' ) == 'yes' ) {
		$body = array( 'domain' => get_site_url() );
		$url  = 'http://stat.lucidlms.com/api/v1/courses/' . $id . '/enroll_student';

		$response = wp_remote_post( $url, array(
				'method' => 'POST',
				'body'   => $body,
			)
		);

		if ( ! is_wp_error( $response ) ) {

			$response_body = json_decode( $response['body'] );

			// handle errors
			if ( $response_body->wp_id ) {

				$course = get_course( $id );

				// set course if not exist
				$response = wp_remote_post(
					'http://stat.lucidlms.com/api/v1/courses', array(
						'method' => 'POST',
						'body'   => array(
							'domain'        => get_site_url(),
							'course[wp_id]' => $course->id,
							'course[name]'  => $course->post->post_title
						),
					)
				);

				// send enrolled student stats again
				$response = wp_remote_post( $url, array(
					'method' => 'POST',
					'body'   => $body,
				) );
			}
		}
	}
}