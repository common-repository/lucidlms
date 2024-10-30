<?php
/**
 * course bbpress forums
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $course;

// Try to get current forum id
$forum_id = get_post_meta( $course->id, '_bbp_forum_id', true );

if ( !empty($forum_id) && ('yes' == get_option('lucidlms_bbpress_integration_enabled')) && function_exists('bbpress') && in_array( 'bbpress/bbpress.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	add_filter( 'bbp_get_topics_per_page', 'lucidlms_bbpress_course_topics_per_page', 10, 2 );
	add_filter( 'bbp_is_single_forum', '__return_true' );

	/**
	 * Set bbpress topics per page limit
	 *
	 * @param $retval
	 * @param $default
	 *
	 * @return int
	 */
	function lucidlms_bbpress_course_topics_per_page( $retval, $default ) {
		return $retval = 5;
	}

	// Set query args for current forum
	$args = array(
		'p' => $forum_id,
		'post_type' => 'forum',
	);

	// Get current forum data
	query_posts($args);

	while ( have_posts() ) : the_post();

		// Check forum caps
		if ( bbp_user_can_view_forum() ) {
			?>

			<div id="bbpress-forums">

				<div class="lucidlms-bbp-course-top">
					<h2 class="lucidlms-bbp-forum-title"><?php echo '<a href="' . esc_url( bbp_get_forum_permalink() ) . '" class="bbp-breadcrumb-forum">' . bbp_get_forum_title() . '</a>' ?></h2>
					<div class="lucidlms-bbp-new-topic">
						<?php echo '<a href="' . esc_url( bbp_get_forum_permalink() ) . '#new-post" rel="nofollow"><button>' . __( 'New Topic', 'lucidlms' ) . '</button></a>'; ?>
					</div>
				</div>

				<?php if ( ! bbp_is_forum_category() && bbp_has_topics() ) : ?>

					<ul id="bbp-forum-<?php bbp_forum_id(); ?>" class="bbp-topics">

						<li class="bbp-header">

							<ul class="forum-titles">
								<li class="bbp-topic-title"><?php _e( 'Topic', 'lucidlms' ); ?></li>
								<li class="bbp-topic-voice-count"><?php _e( 'Voices', 'lucidlms' ); ?></li>
								<li class="bbp-topic-reply-count"><?php bbp_show_lead_topic() ? _e( 'Replies', 'lucidlms' ) : _e( 'Posts', 'lucidlms' ); ?></li>
								<li class="bbp-topic-freshness"><?php _e( 'Freshness', 'lucidlms' ); ?></li>
							</ul>

						</li>

						<li class="bbp-body">

							<?php while ( bbp_topics() ) : bbp_the_topic(); ?>

								<?php bbp_get_template_part( 'loop', 'single-topic' ); ?>

							<?php endwhile; ?>

						</li>

						<li class="bbp-footer">

							<div class="tr">
								<p>
									<span
										class="td colspan<?php echo ( bbp_is_user_home() && ( bbp_is_favorites() || bbp_is_subscriptions() ) ) ? '5' : '4'; ?>">&nbsp;</span>
								</p>
							</div>
							<!-- .tr -->

						</li>

					</ul><!-- #bbp-forum-<?php bbp_forum_id(); ?> -->

				<?php
					// Show new topic form if forum doesn't have topics
					elseif ( ! bbp_is_forum_category() ) : ?>

					<?php bbp_get_template_part( 'feedback',   'no-topics' ); ?>

					<?php bbp_get_template_part( 'form', 'topic' ); ?>

				<?php endif; ?>

			</div>

			<?php

			// Forum is private and user does not have caps
		} elseif ( bbp_is_forum_private( $forum_id, false ) ) {

			bbp_get_template_part( 'feedback', 'no-access' );

		}

	endwhile;

	/* Restore original course Data */
	wp_reset_query();

}