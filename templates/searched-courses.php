<?php
global $wpdb;
/**
 * The Template for displaying searched courses.
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * We use extracted variables in template
 *
 * @var $search_query
 */

$prefix       = $wpdb->prefix;
$search_query = $wpdb->esc_like( $search_query );
$search_query = esc_sql( $search_query );
$search_query = '%' . $search_query . '%';

$db_query = "
    SELECT id
    FROM " . $prefix . "posts
    INNER JOIN " . $prefix . "term_relationships ON
      (" . $prefix . "posts.ID = " . $prefix . "term_relationships.object_id)
    WHERE(
      " . $prefix . "term_relationships.term_taxonomy_id IN (
    SELECT " . $prefix . "term_taxonomy.term_taxonomy_id
					FROM " . $prefix . "term_taxonomy
					INNER JOIN " . $prefix . "terms USING (term_id)
					WHERE taxonomy = 'course_tag'
    AND " . $prefix . "terms.name LIKE '%$search_query%'
                        )
    OR " . $prefix . "posts.post_title LIKE '%$search_query%'
    OR " . $prefix . "posts.post_content LIKE '%$search_query%')
    AND " . $prefix . "posts.post_type = 'course'
    AND (" . $prefix . "posts.post_status = 'publish')
    GROUP BY " . $prefix . "posts.ID";

$query_args = array(); // Ugly fix, but will work fine until we completely rewrite query :)
$post_ids   = $wpdb->get_col( $wpdb->prepare( $db_query, $query_args ) );

if ( ! empty( $post_ids ) ) {
	$args  = array(
		'post__in'       => $post_ids,
		'post_type'      => 'course',
		'order'          => 'ASC',
		'post_status'    => 'publish',
		'orderby'        => 'name',
		'posts_per_page' => - 1
	);
	$query = new WP_Query( $args );
}


if ( $query->have_posts() ) : ?>

	<?php
	/**
	 * lucidlms_before_courses_loop hook
	 */
	do_action( 'lucidlms_before_courses_loop' );
	?>

	<?php while ( $query->have_posts() ) : $query->the_post(); ?>

		<?php lucid_get_template_part( 'content', 'course' ); ?>

	<?php endwhile; ?>

	<?php
	/**
	 * lucidlms_after_courses_loop hook
	 *
	 * @see lucidlms_pagination - 10
	 */
	do_action( 'lucidlms_after_courses_loop' );
	?>
<?php else : ?>

	<?php lucid_get_template( 'loop/no-courses.php' ); ?>

<?php endif;

wp_reset_postdata();