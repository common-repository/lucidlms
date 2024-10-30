<?php
/**
 * Student profile page
 *
 * @author        New Normal
 * @package       LucidLMS/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$user_id      = get_current_user_id();
$user         = get_user_by( 'id', $user_id );
$first_name   = get_user_meta( $user_id, 'first_name', true );
$last_name    = get_user_meta( $user_id, 'last_name', true );
$user_country = get_user_meta( $user_id, 'user_country', true );
$user_state   = get_user_meta( $user_id, 'user_state', true );
$user_email   = $user->user_email;
$empty_field  = '<span class="alert">' . __( 'empty yet', 'lucidlms' ) . '</span>'; ?>

	<p>
		<?php printf(
			__( 'Hi %s! You can find here all the courses you\'ve ever started and/or completed. In order to start a course - browse <a href="%s">courses</a>. You are able to <a href="%s">edit your profile</a>, and you can always <a href="%s">log out</a> if you want.', 'lucidlms' ),
			$user->first_name ? $user->first_name : $user->display_name,
			get_permalink( lucid_get_page_id( 'courses' ) ),
			add_query_arg( array( 'action' => 'editprofile' ), get_permalink() ),
			wp_logout_url( get_permalink( lucid_get_page_id( 'courses' ) ) )
		); ?>
	</p>
	<p>
		<b>
			<?php printf(
				__( 'You profile data (<a href="%s">edit</a>):', 'lucidlms' ),
				add_query_arg( array( 'action' => 'editprofile' ), get_permalink() )
			); ?>
		</b>

		<br/>
		<?php printf(
			__( 'First name: %s', 'lucidlms' ),
			$first_name ? $first_name : $empty_field
		); ?>

		<br/>
		<?php printf(
			__( 'Last name: %s', 'lucidlms' ),
			$last_name ? $last_name : $empty_field
		); ?>

		<br/>
		<?php printf(
			__( 'Email: %s', 'lucidlms' ),
			$user_email ? $user_email : $empty_field
		); ?>

		<br/>
		<?php printf(
			__( 'Country: %s', 'lucidlms' ),
			$user_country ? $user_country : $empty_field
		); ?>

		<br/>
		<?php
		if ( $user_country == 'US' ) {
			printf(
				__( 'State: %s', 'lucidlms' ),
				$user_state ? $user_state : $empty_field
			);
		} ?>

		<?php do_action( 'lucidlms_student_profile_additional_user_meta', $user_id ); ?>

	</p>

<?php
// get user scorecards by current user variable (probably current user id)
$user_scorecards = lucidlms_get_score_card( $user_id );

// Declare empty variables
$sorted_scorecards = array();

// Divide scorecards by statuses
foreach ( $user_scorecards as $scorecard ) {
	$sorted_scorecards[ $scorecard->get_status_name() ][] = $scorecard;
}

// Loop through each status separately and show courses
foreach ( $sorted_scorecards as $status_name => $scorecards_by_status ) {
	?>
	<h2><?php echo sprintf( __( '%s courses', 'lucidlms' ), ucfirst( $status_name ) ) ?></h2>
	<ul>

		<?php foreach ( $scorecards_by_status as $scorecard_by_status ) {

			$status           = $scorecard_by_status->get_status();
			$course         = get_course( $scorecard_by_status->get_course_id() );
			$certificate_link = $scorecard_by_status->get_certificate_link();

			// Check if course post is still exist
			// (we check the case when course was deleted but scorecard is still there)
			if ( ! $course ) {
				continue;
			}
			?>

			<li>
				<a href="<?php echo $course->get_permalink(); ?>">
					<?php echo $scorecard_by_status->get_course_title(); ?>
				</a>
				<?php if ( $complete_date = $scorecard_by_status->get_complete_date() ) {
					?>
					<span class="complete_date"><?php printf( __( '[Completed on %s]', 'lucidlms' ), $complete_date->format( LUCID_DATE_FORMAT ) ) ?></span>
					<a href="<?php echo $scorecard_by_status->get_certificate_link() ?>" target="_blank"><?php _e( 'Download a certificate', 'lucidlms' ); ?></a>
				<?php } ?>
			</li>

		<?php } ?>

	</ul>
<?php
}