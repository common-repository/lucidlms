<?php
/**
 * Email Footer
 *
 * @author        New Normal
 * @package       LucidLMS/Templates/Emails
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $scorecard;

$base_img_path = LU()->plugin_url() . '/assets/images/email/';
?>

</tr>
<tr>
	<td align="center" valign="top">
		<table width="40%" border="0" cellspacing="0" cellpadding="0">
            <tr><td>&nbsp;</td></tr>
            <tr>
                <td height="10" align="center" valign="middle"></td>
            </tr>
			<tr>
				<td align="center" valign="middle">
					<font style="font-family: Georgia, 'Times New Roman', Times, serif; color:#06C; font-size:15px">
                        <strong>
                            <a href="<?php echo $scorecard->get_certificate_link(); ?>" target="_blank" style="color:#06C; text-decoration:underline">
                                <?php _e('Download Completion Certificate', 'lucidlms'); ?>
                            </a>
                        </strong>
                    </font>
				</td>
			</tr>
			<tr>
				<td height="10" align="center" valign="middle"></td>
			</tr>
		</table>
	</td>
</tr>
</table></td>
</tr>
</table></td>
</tr>
</table>
</body>
</html>
