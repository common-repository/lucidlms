<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Certificate
 *
 * Used when user completes a course
 *
 * @class          LU_Certificate
 * @version        1.0.0
 * @package        LucidLMS/Classes/Certificate
 * @category       Class
 * @author         New Normal
 */
class LU_Certificate {

	public static function generate_certificate() {
		if ( isset( $_GET['scorecard_id'] ) && $scorecard = new LU_Score_Card( $_GET['scorecard_id'] ) ) {

			if ( ( 'sc_completed' !== $scorecard->get_status() ) || empty( $_GET['n'] ) || ! wp_verify_nonce( $_GET['n'], 'certificate_download' ) ) {
				return;
			}

			if ( $course = get_course( $scorecard->get_course_id() ) ) {

				if ( ! current_user_can( 'manage_lucidlms' ) ) {
					if ( ( $scorecard->get_student_id() != get_current_user_id() ) ) {
						return;
					}
				}

				if ( $user = get_user_by( 'id', $scorecard->get_student_id() ) ) {

					$mid_x = 148.5;

                    $name = $user->display_name;
                    $name = empty($name) ? $user->user_login : $name;

                    $time = $scorecard->get_complete_date()->format('U');
					$server_offset = $scorecard->get_complete_date()->format('O');
					$client_offset = get_user_meta( $user->ID, 'utc', true );
					if($client_offset == null ){
						$client_offset =-21600;
					}
					$fixed_time = $time - (intval ($server_offset)/100 * 3600) + $client_offset;

                    $name                    = empty($name) && !empty($user->data->display_name) ? $user->data->display_name : $name;
					$date                    = date("F j, Y", $fixed_time);

                    $course_title            = iconv("utf-8", "windows-1251", $scorecard->get_course_title());
					$instructor              = iconv("utf-8", "windows-1251", get_the_title($course->instructor_id));
					$custom_certificate_text = iconv("utf-8", "windows-1251",$course->custom_certificate_text);
					$instructor_by              = sprintf( __( 'By: %s', 'lucidlms' ), $instructor );

					$pdf = new FPDF();
					//set document properties
					$pdf->SetAuthor( $instructor );
					$pdf->SetTitle( $course_title );

					//set font for the entire document
					$pdf->SetFont( 'Helvetica', 'B', 18 );

					$pdf->SetTextColor( 71, 71, 71 );

					//set up a page
					$pdf->AddPage( 'L', 'A4' );

					//insert an image and make it a link
					$template_background = isset( $course->certificate_template['file'] ) ? $course->certificate_template['file'] : LU()->plugin_path() . '/assets/images/certificate-default.png';
					if ( file_exists( $template_background ) ) {
						$pdf->Image( $template_background, 0, 0, 297, 210, '' );
					}

					$pdf->Text( $mid_x - $pdf->GetStringWidth( $name ) / 2, 113, $name );
					$pdf->SetFont( 'Helvetica', '', 10 );
					$pdf->Text( $mid_x - $pdf->GetStringWidth( $date ) / 2, 130, $date );

					$pdf->SetFont( 'Helvetica', 'B', 16 );
					$pdf->Text( $mid_x - $pdf->GetStringWidth( $course_title ) / 2, 152, $course_title );


					$pdf->SetFont( 'Helvetica', '', 11 );

					$pdf->Text( $mid_x - $pdf->GetStringWidth( $instructor_by ) / 2, 168, $instructor_by );
					$pdf->Text( $mid_x - $pdf->GetStringWidth( $custom_certificate_text ) / 2, 173, $custom_certificate_text );

					//Output the document
					$pdf->Output( 'certificate_' . preg_replace( '@[^a-z0-9_]+@', '_', strtolower( $course_title ) ) . '.pdf', 'I' );
				} else {
					// handle no user found error
				}
			} else {
				// @todo handle error if no course exist
			}
			die();
		}
	}

}

