<?php
/**
 * Email Header
 *
 * @author        New Normal
 * @package       LucidLMS/Templates/Emails
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * @var $email_heading
 */

$base_img_path = LU()->plugin_url() . '/assets/images/email/';

$logo = '';

if ( is_numeric( get_option( 'lucidlms_organization_logo' ) ) ) {
	$arr_existing_image = wp_get_attachment_image_src( get_option( 'lucidlms_organization_logo' ), 'thumbnail' );
	$logo               = $arr_existing_image[0];
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $email_heading; ?></title>
</head>

<body bgcolor="#FFFFFF">
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
	<tr>
		<td>
			<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
				<tr>
					<td>
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td width="144" align="center">
									<a href="<?php bloginfo('url'); ?>" target="_blank"><img src="<?php echo $logo ? $logo : $base_img_path . 'PROMO-GREEN2_01_02.jpg'; ?>" height="76" border="0" alt="" /></a>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center">&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr>