<?php
/**
 * Admin functions for the score card post type
 *
 * @author        New Normal
 * @category      Admin
 * @package       LucidLMS/Admin/Post Types
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'LU_Admin_View_Score_Card' ) ) {

	/**
	 * LU_Admin_View_Score_Card Class
	 */
	class LU_Admin_View_Score_Card {

		/**
		 * Constructor
		 */
		public function __construct() {

			// Admin Columns
			add_filter( 'manage_edit-score_card_columns', array( $this, 'edit_columns' ) );
			add_action( 'manage_score_card_posts_custom_column', array( $this, 'custom_columns' ), 2 );
			add_filter( 'manage_edit-score_card_sortable_columns', array( $this, 'custom_columns_sort' ) );
			add_filter( 'posts_clauses', array( $this, 'status_clauses' ), 10, 2 );
			add_filter( 'request', array( $this, 'custom_columns_orderby' ) );

			// Score card filtering
			add_action( 'date_query_valid_columns', array( $this, 'score_card_date_columns' ) );
			add_action( 'restrict_manage_posts', array( $this, 'score_card_filters' ) );
			add_filter( 'parse_query', array( $this, 'score_card_filters_query' ) );
			add_filter( 'query_vars', array( $this, 'add_score_card_query_vars' ) );

			// Score card csv download
			add_action( 'views_edit-score_card', array( $this, 'score_cards_download_link' ) );
			add_action( 'the_posts', array( $this, 'download_score_cards_csv' ) );

		}

		/**
		 * Change the columns shown in admin.
		 */
		public function edit_columns( $existing_columns ) {

			if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
				$existing_columns = array();
			}

			unset( $existing_columns['title'], $existing_columns['date'] );

			$columns                  = array();
			$columns['cb']            = '<input type="checkbox" />';
			$columns['course_name'] = __( 'Course', 'lucidlms' );
			$columns['student']       = __( 'Student', 'lucidlms' );
			$columns['course_type'] = __( 'Type', 'lucidlms' );
			$columns['status']        = __( 'Status', 'lucidlms' );
			$columns['start_date']    = __( 'Started on', 'lucidlms' );
			$columns['expire_date']   = __( 'Expire date', 'lucidlms' );

			return array_merge( $columns, $existing_columns );
		}

		/**
		 * Define our custom columns shown in admin.
		 *
		 * @param  string $column
		 */
		public function custom_columns( $column ) {
			global $post, $the_scorecard;

			if ( empty( $the_scorecard ) || $the_scorecard->id != $post->ID ) {
				$the_scorecard = new LU_Score_Card( $post );
			}


			switch ( $column ) {
				case 'student' :

					$user = get_user_by( 'id', $the_scorecard->get_student_id() );

					// TODO [future-releases]: make a backend profile for user
					echo $user->display_name;

					break;

				case 'course_name' :

					$edit_link        = get_edit_post_link( $post->ID );
					$title            = $the_scorecard->get_course_title();
					$post_type_object = get_post_type_object( $post->post_type );
					$can_edit_post    = current_user_can( $post_type_object->cap->edit_post, $post->ID );

					echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) . '">' . $title . '</a>';

					_post_states( $post );

					echo '</strong>';

					if ( $post->post_parent > 0 ) {
						echo '&nbsp;&nbsp;&larr; <a href="' . get_edit_post_link( $post->post_parent ) . '">' . get_the_title( $post->post_parent ) . '</a>';
					}

					// Excerpt view
					if ( isset( $_GET['mode'] ) && 'excerpt' == $_GET['mode'] ) {
						echo apply_filters( 'the_excerpt', $post->post_excerpt );
					}

					// Get actions
					$actions = array();

					$actions['id'] = 'ID: ' . $post->ID;

					if ( $can_edit_post && 'trash' != $post->post_status ) {
						$actions['edit']                 = '<a href="' . get_edit_post_link( $post->ID, true ) . '" title="' . esc_attr( __( 'Edit this item', 'lucidlms' ) ) . '">' . __( 'Edit', 'lucidlms' ) . '</a>';
						$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . esc_attr( __( 'Edit this item inline', 'lucidlms' ) ) . '">' . __( 'Quick&nbsp;Edit', 'lucidlms' ) . '</a>';
					}
					if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
						if ( 'trash' == $post->post_status ) {
							$actions['untrash'] = '<a title="' . esc_attr( __( 'Restore this item from the Trash', 'lucidlms' ) ) . '" href="' . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ) . '">' . __( 'Restore', 'lucidlms' ) . '</a>';
						} elseif ( EMPTY_TRASH_DAYS ) {
							$actions['trash'] = '<a class="submitdelete" title="' . esc_attr( __( 'Move this item to the Trash', 'lucidlms' ) ) . '" href="' . get_delete_post_link( $post->ID ) . '">' . __( 'Trash', 'lucidlms' ) . '</a>';
						}

						if ( 'trash' == $post->post_status || ! EMPTY_TRASH_DAYS ) {
							$actions['delete'] = '<a class="submitdelete" title="' . esc_attr( __( 'Delete this item permanently', 'lucidlms' ) ) . '" href="' . get_delete_post_link( $post->ID, '', true ) . '">' . __( 'Delete Permanently', 'lucidlms' ) . '</a>';
						}
					}
					if ( $post_type_object->public ) {
						if ( in_array( $post->post_status, array( 'pending', 'draft', 'future', 'not_active' ) ) ) {
							if ( $can_edit_post ) {
								$actions['view'] = '<a href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) . '" title="' . esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', 'lucidlms' ), $title ) ) . '" rel="permalink">' . __( 'Preview', 'lucidlms' ) . '</a>';
							}
						} elseif ( 'trash' != $post->post_status ) {
							$actions['view'] = '<a href="' . get_permalink( $post->ID ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'lucidlms' ), $title ) ) . '" rel="permalink">' . __( 'View', 'lucidlms' ) . '</a>';
						}
					}

					$actions = apply_filters( 'post_row_actions', $actions, $post );

					echo '<div class="row-actions">';

					$i            = 0;
					$action_count = sizeof( $actions );

					foreach ( $actions as $action => $link ) {
						++ $i;
						( $i == $action_count ) ? $sep = '' : $sep = ' | ';
						echo '<span class="' . $action . '">' . $link . $sep . '</span>';
					}
					echo '</div>';

					get_inline_data( $post );

					break;

				case 'course_type' :

					$course_type = LU()->taxonomies->get_term( ATYPE, $the_scorecard->get_course_type() );
					echo $course_type['name'];

					break;

				case 'status' :

					echo $the_scorecard->get_status_name();

					if ( $the_scorecard->get_status() == 'sc_completed' ) {
						echo '<div>';
						printf( __( 'on %s', 'lucidlms' ), $the_scorecard->get_complete_date()->format( LUCID_DATE_FORMAT ) );
						echo '</div>';
					}

					break;

				case 'expire_date' :

					$expire_date = $the_scorecard->get_expire_date();
				  	$course  = $the_scorecard->id ? get_course( $the_scorecard->get_course_id() ) : false;

					if ( $expire_date !== null ) {
						echo $the_scorecard->get_expire_date()->format( LUCID_DATE_FORMAT . ' \<\b\r\/\> g:i a T' );
					} else {
						_e( 'Indefinite', 'lucidlms' );
					}

					break;

				case 'start_date' :

					echo $the_scorecard->get_start_date()->format( LUCID_DATE_FORMAT . ' \<\b\r\/\> g:i a T' );

					break;

				default :
					break;
			}
		}

		/**
		 * Make score card columns sortable
		 *
		 * https://gist.github.com/906872
		 *
		 * @access public
		 *
		 * @param mixed $columns
		 *
		 * @return array
		 */
		public function custom_columns_sort( $columns ) {

			$custom = array(
				'student'       => 'student',
				'course_name' => 'course_name',
				'course_type' => 'course_type',
				'status'        => 'status',
				'expire_date'   => 'expire_date',
				'start_date'    => 'start_date'
			);

			return wp_parse_args( $custom, $columns );
		}

		/**
		 * Score Card column orderby
		 *
		 * http://scribu.net/wordpress/custom-sortable-columns.html#comment-4732
		 *
		 * @access public
		 *
		 * @param mixed $vars
		 *
		 * @return array
		 */
		public function custom_columns_orderby( $vars ) {
			global $typenow;

			$screen = get_current_screen();
			if ( ( 'score_card' !== $typenow ) || ( $screen->base !== 'edit' ) ) {
				return $vars;
			}

			if ( isset( $vars['orderby'] ) ) {
				if ( 'student' == $vars['orderby'] ) {
					$vars = array_merge( $vars, array(
						'meta_key' => '_student_id',
						'orderby'  => 'meta_value_num'
					) );
				}
				if ( 'course_type' == $vars['orderby'] ) {
					$vars = array_merge( $vars, array(
						'meta_key' => '_course_type',
						'orderby'  => 'meta_value'
					) );
				}
				if ( 'course_name' == $vars['orderby'] ) {
					$vars = array_merge( $vars, array(
						'meta_key' => '_course_title',
						'orderby'  => 'meta_value'
					) );
				}
				if ( 'expire_date' == $vars['orderby'] ) {
					$vars = array_merge( $vars, array(
						'meta_key' => '_expire_date',
						'orderby'  => 'meta_value'
					) );
				}
				if ( 'start_date' == $vars['orderby'] ) {
					$vars = array_merge( $vars, array(
						'meta_key' => '_start_date',
						'orderby'  => 'meta_value'
					) );
				}

			}

			return $vars;
		}

		/**
		 * Status column orderby
		 *
		 * @param $clauses
		 * @param $wp_query
		 *
		 * @return mixed
		 */
		function status_clauses( $clauses, $wp_query ) {
			global $wpdb, $typenow;

			$screen = get_current_screen();
			if ( ( 'score_card' !== $typenow ) || ( $screen->base !== 'edit' ) ) {
				return $clauses;
			}

			if ( isset( $wp_query->query['orderby'] ) && 'status' == $wp_query->query['orderby'] ) {
				$clauses['join'] .= " LEFT JOIN (
						SELECT object_id, GROUP_CONCAT(name ORDER BY name ASC) AS status
						FROM $wpdb->term_relationships
						INNER JOIN $wpdb->term_taxonomy USING (term_taxonomy_id)
						INNER JOIN $wpdb->terms USING (term_id)
						WHERE taxonomy = 'score_card_status'
						GROUP BY object_id
					) AS status_terms ON ($wpdb->posts.ID = status_terms.object_id)";
				$clauses['orderby'] = 'status_terms.status ';
				$clauses['orderby'] .= ( 'ASC' == strtoupper( $wp_query->get( 'order' ) ) ) ? 'ASC' : 'DESC';
			}

			return $clauses;
		}

		/**
		 * Show a status (score_card_status) filter box
		 */
		public function score_card_filters() {
			global $typenow, $wp_query;

			$screen = get_current_screen();
			if ( ( 'score_card' !== $typenow ) || ( $screen->base !== 'edit' ) ) {
				return;
			}

			$output = '';

			// Type filtering
			$terms = get_terms( 'course_type' );
			$output .= '<select name="course_type" id="dropdown_course_type">';
			$output .= '<option value="">' . __( 'Show all course types', 'lucidlms' ) . '</option>';

			foreach ( $terms as $term ) {
				$output .= '<option value="' . sanitize_title( $term->slug ) . '" ';

				if ( isset( $wp_query->query['course_type'] ) ) {
					$output .= selected( $term->slug, $wp_query->query['course_type'], false );
				}

				$output .= '>' . $term->name . '</option>';

			}

			$output .= '</select>';

			// Course Filtering
			$courses = get_posts( array(
				'post_type'      => 'course',
				'posts_per_page' => '-1',
				'post_status'    => 'publish'
			) );
			$output .= '<select name="view_course_id" id="dropdown_course_id">';
			$output .= '<option value="">' . __( 'Show all courses', 'lucidlms' ) . '</option>';

			foreach ( $courses as $course ) {
				$output .= '<option value="' . (int) $course->ID . '" ';

				if ( isset( $wp_query->query['view_course_id'] ) ) {
					$output .= selected( (int) $course->ID, (int) $wp_query->query['view_course_id'], false );
				}

				$output .= '>' . $course->post_title . '</option>';

			}

			$output .= '</select>';

			// Student Filtering
			$query_args['fields'] = array( 'ID', 'user_login', 'display_name' );
			$users                = get_users( array( 'fields' => array( 'ID', 'user_login', 'display_name' ) ) );
			$output .= '<select name="view_student_id" id="dropdown_student_id">';
			$output .= '<option value="">' . __( 'Show all students', 'lucidlms' ) . '</option>';

			foreach ( $users as $user ) {
				$output .= '<option value="' . (int) $user->ID . '" ';

				if ( isset( $wp_query->query['view_student_id'] ) ) {
					$output .= selected( (int) $user->ID, (int) $wp_query->query['view_student_id'], false );
				}

				$output .= '>' . $user->display_name . '</option>';

			}

			$output .= '</select>';

			// Status Filtering
			$terms_sc_statuses = get_terms( 'score_card_status' );
			$output .= '<select name="view_sc_status" id="dropdown_sc_status">';
			$output .= '<option value="">' . __( 'Show all statuses', 'lucidlms' ) . '</option>';

			foreach ( $terms_sc_statuses as $status ) {
				$output .= '<option value="' . $status->slug . '" ';

				if ( isset( $wp_query->query['view_sc_status'] ) ) {
					$output .= selected( $status->slug, $wp_query->query['view_sc_status'], false );
				}

				$output .= '>' . $status->name . '</option>';

			}

			$output .= '</select>';


			echo apply_filters( 'lucidlms_score_card_filters', $output );
		}

		/**
		 * Filter the score cards in admin based on options
		 *
		 * @param mixed $query
		 */
		public function score_card_filters_query( $query ) {
			global $typenow, $wp_query;

			$screen = get_current_screen();
			if ( ( 'score_card' == $typenow ) && ( $screen->base === 'edit' ) ) {

				$query->query_vars['meta_query'] = array();
				if ( isset( $query->query_vars['course_type'] ) && ! empty( $query->query_vars['course_type'] ) ) {
					$course_type = $query->query_vars['course_type'];

					$query->query_vars['course_type'] = '';
					$query->query_vars['meta_query'][]  = array(
						'key'     => '_course_type',
						'value'   => $course_type,
						'compare' => 'IN',
					);
				}

				if ( isset( $query->query_vars['view_course_id'] ) && ! empty( $query->query_vars['view_course_id'] ) ) {
					$course_id = $query->query_vars['view_course_id'];

					$query->query_vars['view_course_id'] = '';
					$query->query_vars['meta_query'][]     = array(
						'key'     => '_course_id',
						'value'   => $course_id,
						'compare' => 'IN',
					);
				}

				if ( isset( $query->query_vars['view_student_id'] ) && ! empty( $query->query_vars['view_student_id'] ) ) {
					$student_id = $query->query_vars['view_student_id'];

					$query->query_vars['view_student_id'] = '';
					$query->query_vars['meta_query'][]    = array(
						'key'     => '_student_id',
						'value'   => $student_id,
						'compare' => 'IN',
					);
				}

				$status_for_date_filter = ''; // We declare variable here to use in 'm' query
				if ( isset( $query->query_vars['view_sc_status'] ) && ! empty( $query->query_vars['view_sc_status'] ) ) {
					$sc_status              = $query->query_vars['view_sc_status'];
					$status_for_date_filter = $query->query_vars['view_sc_status'];

					$query->query_vars['view_sc_status'] = '';
					$query->query_vars['tax_query'][]    = array(
						'taxonomy' => 'score_card_status',
						'field'    => 'slug',
						'terms'    => $sc_status,
					);
				}

				if ( isset( $query->query_vars['m'] ) && ! empty( $query->query_vars['m'] ) ) {
					global $wpdb;
					$month = $query->query_vars['m'];

					$date_filter = '_start_date';
					if ( ! empty( $status_for_date_filter ) ) {
						if ( $status_for_date_filter == 'sc_completed' ) {
							$date_filter = '_complete_date';
						}

						if ( $status_for_date_filter == 'sc_expired' ) {
							$date_filter = '_expire_date';
						}
					}

					// We add meta query to filter by meta field
					$query->query_vars['meta_query'][] = array(
						'key'     => $date_filter,
						'compare' => 'EXISTS'
					);

					$query->query_vars['m']            = '';
					$query->query_vars['date_query'][] = array(
						'column' => $wpdb->postmeta . '.meta_value',
						'year'   => (int) substr( $month, 0, 4 ),
						'month'  => (int) substr( $month, 4 )
					);
				}

				if ( isset( $_REQUEST['action3'] ) && $_REQUEST['action3'] === 'download_csv' ) {
					$query->query_vars['posts_per_page'] = -1;
				}

			}
		}

		/**
		 * Add new valid columns to filter score cards
		 * We only add meta_value because we filter by meta field
		 *
		 * @param $valid_columns
		 *
		 * @return array
		 */
		public function score_card_date_columns( $valid_columns ) {
			global $typenow;

			$screen = get_current_screen();
			if ( ( 'score_card' == $typenow ) && ( $screen->base === 'edit' ) ) {
				global $wpdb;

				return array_merge( $valid_columns, array(
					$wpdb->postmeta . '.meta_value'
				) );
			} else {
				return $valid_columns;
			}
		}

		/**
		 * Add query_vars needed for score card backend views
		 *
		 * @param $vars
		 *
		 * @return array
		 */
		public function add_score_card_query_vars( $vars ) {
			global $typenow;

			$screen = get_current_screen();
			if ( ( 'score_card' == $typenow ) && ( $screen->base === 'edit' ) ) {

				$vars[] = 'view_course_id';
				$vars[] = 'view_student_id';
				$vars[] = 'view_sc_status';

			}

			return $vars;
		}

		/**
		 * Add download score card csv link
		 *
		 * @param $views
		 *
		 * @return mixed
		 */
		public function score_cards_download_link( $views ) {

			$screen = get_current_screen();
			if ( $screen->id === 'edit-score_card' ) {
				$link = sprintf( '<a href="%s" target="_blank">', add_query_arg( array( 'action3' => 'download_csv' ) ) );
				$link .= __( 'Download CSV Report', 'lucidlms' );
				$link .= '</a>';

				$views['download_csv'] = $link;
			}

			return $views;
		}

		/**
		 * Download csv processing
		 *
		 * @param $posts
		 *
		 * @return mixed
		 */
		public function download_score_cards_csv( $posts ) {

			if ( is_ajax() ) {
				return $posts;
			}

			$screen = get_current_screen();
			if ( $screen->id === 'edit-score_card' ) {

				if ( isset( $_REQUEST['action3'] ) && $_REQUEST['action3'] === 'download_csv' ) {

					$header_row = array(
						__( 'Member ID', 'lucidlms' ),
						__( 'First name', 'lucidlms' ),
						__( 'Last name', 'lucidlms' ),
                        __( 'Course name', 'lucidlms' ),
                        __( 'Status', 'lucidlms' ),
                        __( 'Date', 'lucidlms' ),
					);

					$data[] = $header_row;
					foreach ( $posts as $post ) {
						$post_id = $post->ID;

						$score_card  = new LU_Score_Card( $post_id );
						$student     = get_user_by( 'id', $score_card->get_student_id() );
						$student_first_name = $student->first_name;
						$student_last_name = $student->last_name;

                        if( empty($student_first_name) && empty($student_last_name) && !empty($student->data->display_name)){
                            $student_first_name = $student->data->display_name;
                        }

						$course    = get_course( $score_card->get_course_id() );
                        $course_name = $course->get_title();

						$score_card_status       = $score_card->get_status_name();
						$complete_date_formatted = '';
						if ( $complete_date = $score_card->get_complete_date() ) {
							$complete_date_formatted = $complete_date->format( LUCID_DATE_FORMAT );
						}

						$row = array(
							$score_card->get_student_id(),
							$student_first_name,
							$student_last_name,
                            $course_name,
                            $score_card_status,
							$complete_date_formatted
						);

						$data[] = $row;

					}

					$csv = $this->generate_csv( $data );

                    if( ob_get_contents() ) ob_end_clean();

					header( "Pragma: public" );
					header( "Expires: 0" );
					header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
					header( "Cache-Control: private", false );
					header( "Content-Type: application/octet-stream" );
					header( "Content-Disposition: attachment; filename=\"report.csv\";" );
					header( "Content-Transfer-Encoding: binary" );

					echo $csv;

					exit;

				}
			}

			return $posts;
		}

		/**
		 * Generate csv and return
		 *
		 * @param        $data
		 * @param string $delimiter
		 * @param string $enclosure
		 *
		 * @return string
		 */
		public function generate_csv( $data, $delimiter = ',', $enclosure = '"' ) {
			$handle = fopen( 'php://temp', 'r+' );

			foreach ( $data as $line ) {
				fputcsv( $handle, $line, $delimiter, $enclosure );
			}

			rewind( $handle );

			$contents = '';
			while ( ! feof( $handle ) ) {
				$contents .= fread( $handle, 8192 );
			}
			fclose( $handle );

			return $contents;
		}

	}

}

return new LU_Admin_View_Score_Card();
