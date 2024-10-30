<?php
/**
 * Course completed email
 *
 * @author        New Normal
 * @package       LucidLMS/Templates/Emails
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Available variables
 *
 * @var string $email_heading
 * @var string $course_name
 * @var string $blogname
 * @var object $user
 */
do_action( 'lucidlms_email_header', $email_heading ); ?>

<td width="100%" align="center" valign="top">
	<font style="font-family: Helvetica, Arial, sans-serif; color:#010101; font-size:24px"><strong><?php printf( __( 'Dear %s', 'lucidlms' ), $user->first_name ? $user->first_name : $user->display_name ) ?>,</strong></font><br /><br />
	<font style="font-family: Helvetica, Arial, sans-serif; color:#010101; font-size:13px; line-height:21px">
		<?php printf( __( 'Congratulations you have completed &ldquo;%s&rdquo;! <br /> You can download your certificate in your dashboard on our website or by clicking the link below.', 'lucidlms' ), $course_name ); ?>
	</font>
</td>

<?php do_action( 'lucidlms_email_footer' ); ?>

