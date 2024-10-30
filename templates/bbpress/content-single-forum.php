<?php

/**
 * Single Forum Content Part
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<div id="bbpress-forums">

	<div class="lucidlms-bbp-forum-top">
		<?php
		global $post;

		$course_id = get_course_id_by_forum_id($post->ID);

		if ( !empty($course_id) ) : ?>
		<div class="lucidlms-bbp-back-to-course">
			<?php echo '<a href="' . get_permalink($course_id) . '">&laquo; ' . __( 'Back to Course', 'lucidlms' ) . '</a>'; ?>
		</div>
		<?php endif; ?>

		<div class="lucidlms-bbp-new-topic">
			<?php echo '<a href="' . esc_url( bbp_get_forum_permalink() ) . '#new-post" rel="nofollow"><button>' . __( 'New Topic', 'lucidlms' ) . '</button></a>'; ?>
		</div>

	</div>

	<?php bbp_breadcrumb(); ?>

	<?php bbp_forum_subscription_link(); ?>

	<?php do_action( 'bbp_template_before_single_forum' ); ?>

	<?php if ( post_password_required() ) : ?>

		<?php bbp_get_template_part( 'form', 'protected' ); ?>

	<?php else : ?>

		<?php if ( bbp_has_forums() ) : ?>

			<?php bbp_get_template_part( 'loop', 'forums' ); ?>

		<?php endif; ?>

		<?php if ( !bbp_is_forum_category() && bbp_has_topics() ) : ?>

			<?php bbp_get_template_part( 'loop',       'topics'    ); ?>

			<?php bbp_get_template_part( 'pagination', 'topics'    ); ?>

			<?php bbp_get_template_part( 'form',       'topic'     ); ?>

		<?php elseif ( !bbp_is_forum_category() ) : ?>

			<?php bbp_get_template_part( 'feedback',   'no-topics' ); ?>

			<?php bbp_get_template_part( 'form',       'topic'     ); ?>

		<?php endif; ?>

	<?php endif; ?>

	<?php do_action( 'bbp_template_after_single_forum' ); ?>

</div>
