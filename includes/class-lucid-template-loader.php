<?php
/**
 * Template Loader
 *
 * @class 		LU_Template_Loader
 * @version		1.0.0
 * @package		LucidLMS/Classes
 * @category	Class
 * @author 		New Normal
 */
class LU_Template_Loader {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'template_include', array( $this, 'template_loader' ) );
	}

	/**
	 * Load a template.
	 *
	 * Handles template usage so that we can use our own templates instead of the themes.
	 *
	 * Templates are in the 'templates' folder. lucidlms looks for theme
	 * overrides in /theme/lucidlms/ by default
	 *
	 * For beginners, it also looks for a lucidlms.php template first. If the user adds
	 * this to the theme (containing a lucidlms() inside) this will be used for all
	 * lucidlms templates.
	 *
	 * @param mixed $template
	 * @return string
	 */
	public function template_loader( $template ) {
		$find = array( 'lucidlms.php' );
		$file = '';

		if ( is_single() && get_post_type() == 'course' ) {
            // load not active courses only for students that already started this course
            if( 'not_active' == get_post_status() && ! current_user_can('edit_courses') ){
                $file = 'course-not-active.php';

                if( $user = get_current_user_id() ){
                    $score_cards = lucidlms_get_score_card_by( array('user_id' => $user, 'course_id' => get_the_ID()) );
                    if( $score_cards ){
                        $file = 'single-course.php';
                    }
                }
            } else {
                $file = 'single-course.php';
            }

            $find[] = $file;
			$find[] = LU_TEMPLATE_PATH . $file;

		} elseif ( is_single() && get_post_type() == 'course_element' ) {

			$file 	= 'single-course-element.php';
			$find[] = $file;
			$find[] = LU_TEMPLATE_PATH . $file;

		} elseif ( is_tax( 'course_cat' ) || is_tax( 'course_tag' ) ) {

			$term = get_queried_object();

			$file 		= 'taxonomy-' . $term->taxonomy . '.php';
			$find[] 	= 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
			$find[] 	= LU_TEMPLATE_PATH . 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
			$find[] 	= $file;
			$find[] 	= LU_TEMPLATE_PATH . $file;

		} elseif ( is_post_type_archive( 'course' ) || is_page( lucid_get_page_id( 'courses' ) ) ) {

			$file 	= 'archive-course.php';
			$find[] = $file;
			$find[] = LU_TEMPLATE_PATH . $file;

		}

		if ( $file ) {
			$template       = locate_template( $find );
			if ( ! $template )
				$template = LU()->plugin_path() . '/templates/' . $file;
		}

		return $template;
	}
}

new LU_Template_Loader();