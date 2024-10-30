<?php
/**
 * Main function for returning courses, uses the LU_course_factory class.
 *
 * @param mixed $the_question Post object or post ID of the course.
 *
 * @return LU_Question
 */
function get_question( $the_question = false ) {
    return new LU_Question( $the_question );
}

/**
 * Get questions by categories
 * @param array $categories
 * @return array
 */
function get_questions($categories = array(), $exclude_questions = array()){

    $args = array(
        'post_type' => 'question',
        'posts_per_page'  => -1,
        'post_status'   => array( 'publish', 'draft', 'not_active' ),
        'post__not_in' => $exclude_questions,
    );

    if( !empty($categories) ){
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'question_cat',
                'field'    => 'id',
                'terms'    => $categories,
                'relation' => 'OR',
            ),
        );
    }
    $posts = get_posts( $args );

    $questions = array();
    foreach( $posts as $post ){
        if( $question = get_question($post) ){
            $questions[$question->id] = $question;
        }
    }
    return $questions;
}

/**
 * Fetch questions from given categories and courses list
 * @param array $categories
 * @param array $courses
 * @return array
 */
function get_questions_by_category_and_courses( $categories = array(), $courses = array() ){
    $questions = get_questions($categories);

    // filter questions by selected courses
    if( !empty($courses) ){
        foreach( $questions as $question_id => $question ){
            $question_courses = $question->get_courses();
            if( !array_intersect( array_keys($question_courses), $courses) ){
                unset($questions[$question_id]);
            }
        }
    }

    return $questions;
}

/**
 * Get array of questions, sorted by categories
 * @return array
 */
function get_all_questions_by_categories($exclude_questions = array()){
    $questions = get_questions( array(), $exclude_questions );
    $category_names = get_all_question_categories(true);
    $categories = array();

    // fill with names and ids
    foreach( $category_names as $id => $name ){
        $categories[ $id ] = array(
            'name' => $name,
            'category_id' => $id,
            'questions' => array(),
        );
    }

    $categories[0] = array(
        'name'          => __('Other Questions', 'lucidlms'),
        'category_id'   => 0,
        'questions' => array(),
    );

    /** @var $question LU_Question */
    foreach( $questions as $question ){
        // get question data as array
        $question_data = $question->get_data();
        // get categories that question belongs to
        $question_categories = $question->get_categories();

        if( !empty($question_categories) ){
            foreach($question_categories as $category_id => $category_name){
                // put question's data to proper place in categories array to return
                $categories[$category_id]['questions'][$question->id] = $question_data;
            }
        } else {
            // if there are no categories in question, put it to special "other" virtual category
            $categories[0]['questions'][$question->id] = $question_data;
        }
    }
    // double check if there are any empty categories
    foreach( $categories as $category_id => $category ){
        if( empty($category['questions']) ){
            unset($categories[ $category_id ]);
        }
    }

    return $categories;
}

// ====================== Question Categories ========================== //

/**
 * Get all available categories
 * @return array
 */
function get_all_question_categories( $hide_empty = false ){
    $categories = get_terms( 'question_cat', array('hide_empty' => $hide_empty, 'fields' => 'id=>name') );
    if( $categories && !is_wp_error($categories) ){
        return $categories;
    }

    return array();
}

/**
 * Create new category for questions
 * @param $name
 * @return bool
 */
function create_question_category($name){
    $result = wp_insert_term( $name, 'question_cat' );
    if( $result && !is_wp_error($result) ){
        return $result['term_id'];
    }
    return false;
}

/**
 * Delete a category for questions
 * @param $category_id
 * @return bool
 */
function remove_question_category($category_id){
    $result = wp_delete_term( $category_id, 'question_cat' );
    if( $result && !is_wp_error($result) ){
        return true;
    }
    return false;
}

/**
 * Change name of question's category
 * @param $category_id
 * @param $new_category_name
 * @return bool
 */
function rename_question_category($category_id, $new_category_name){
    $result = wp_update_term( $category_id, 'question_cat', array( 'name' => $new_category_name ) );
    if( $result && !is_wp_error($result) ){
        return true;
    }
    return false;
}

