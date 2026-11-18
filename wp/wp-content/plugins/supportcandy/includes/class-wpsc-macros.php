<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_Macros' ) ) :

	final class WPSC_Macros {

		/**
		 * All macros
		 *
		 * @var array
		 */
		public static $macros;

		/**
		 * Custom field type attachments macros
		 *
		 * @var array
		 */
		public static $attachments = array();

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// Load macros.
			add_action( 'init', array( __CLASS__, 'load_macros' ), 12 );

			// get macros modal.
			add_action( 'wp_ajax_wpsc_get_macros', array( __CLASS__, 'get_macros' ) );
			add_action( 'wp_ajax_nopriv_wpsc_get_macros', array( __CLASS__, 'get_macros' ) );
		}

		/**
		 * Load macros on init wp event
		 *
		 * @return void
		 */
		public static function load_macros() {

			$macros = array(

				array(
					'tag'   => '{{ticket_id}}',
					'title' => esc_attr__( 'Ticket id', 'supportcandy' ),
				),
				array(
					'tag'   => '{{customer_name}}',
					'title' => esc_attr__( 'Customer name', 'supportcandy' ),
				),
				array(
					'tag'   => '{{customer_first_name}}',
					'title' => esc_attr__( 'Customer first name', 'supportcandy' ),
				),
				array(
					'tag'   => '{{customer_email}}',
					'title' => esc_attr__( 'Customer email address', 'supportcandy' ),
				),
				array(
					'tag'   => '{{last_reply}}',
					'title' => esc_attr__( 'Last reply', 'supportcandy' ),
				),
				array(
					'tag'   => '{{last_reply_user_name}}',
					'title' => esc_attr__( 'Last reply user name', 'supportcandy' ),
				),
				array(
					'tag'   => '{{last_reply_user_email}}',
					'title' => esc_attr__( 'Last reply user email', 'supportcandy' ),
				),
				array(
					'tag'   => '{{last_reply_user_first_name}}',
					'title' => esc_attr__( 'Last reply user first name', 'supportcandy' ),
				),
				array(
					'tag'   => '{{last_note}}',
					'title' => esc_attr__( 'Last note', 'supportcandy' ),
				),
				array(
					'tag'   => '{{last_note_user_name}}',
					'title' => esc_attr__( 'Last note user name', 'supportcandy' ),
				),
				array(
					'tag'   => '{{last_note_user_email}}',
					'title' => esc_attr__( 'Last note user email', 'supportcandy' ),
				),
				array(
					'tag'   => '{{last_note_user_first_name}}',
					'title' => esc_attr__( 'Last note user first name', 'supportcandy' ),
				),
				array(
					'tag'   => '{{current_user_name}}',
					'title' => esc_attr__( 'Current user name', 'supportcandy' ),
				),
				array(
					'tag'   => '{{current_user_email}}',
					'title' => esc_attr__( 'Current user email', 'supportcandy' ),
				),
				array(
					'tag'   => '{{current_user_first_name}}',
					'title' => esc_attr__( 'Current user first name', 'supportcandy' ),
				),
				array(
					'tag'   => '{{ticket_history}}',
					'title' => esc_attr__( 'Ticket history (last few report & reply excluding last reply)', 'supportcandy' ),
				),
				array(
					'tag'   => '{{ticket_history_all}}',
					'title' => esc_attr__( 'Ticket history (report & reply)', 'supportcandy' ),
				),
				array(
					'tag'   => '{{ticket_notes_history}}',
					'title' => esc_attr__( 'Ticket history (note)', 'supportcandy' ),
				),
				array(
					'tag'   => '{{ticket_history_all_with_notes}}',
					'title' => esc_attr__( 'Ticket history (report, reply & note)', 'supportcandy' ),
				),
				array(
					'tag'   => '{{ticket_history_all_with_logs}}',
					'title' => esc_attr__( 'Ticket history (report, reply & log)', 'supportcandy' ),
				),
				array(
					'tag'   => '{{ticket_history_all_with_notes_and_logs}}',
					'title' => esc_attr__( 'Ticket history (report, reply, note & log)', 'supportcandy' ),
				),
				array(
					'tag'   => '{{ticket_notes_history_with_logs}}',
					'title' => esc_attr__( 'Ticket history (note & log)', 'supportcandy' ),
				),
				array(
					'tag'         => '{{ticket_url}}',
					'title'       => esc_attr__( 'Ticket URL', 'supportcandy' ),
					'description' => esc_attr__( 'Clickable link to open the ticket.', 'supportcandy' ),
				),
				array(
					'tag'         => '{{ticket_url_plain}}',
					'title'       => esc_attr__( 'Ticket URL (plain)', 'supportcandy' ),
					'description' => esc_attr__( 'Returns the ticket URL without any HTML formatting. Use it inside href="".', 'supportcandy' ),
				),
			);

			// Add custom fields.
			foreach ( WPSC_Custom_Field::$custom_fields as $cf ) {

				if ( ! (
					class_exists( $cf->type ) &&
					in_array( $cf->field, WPSC_CF_Settings::$allowed_modules['ticket-macro'] ) &&
					$cf->type::$has_macro
				) ) {
					continue;
				}

				$macros[] = array(
					'tag'   => '{{' . $cf->slug . '}}',
					'title' => $cf->name,
				);
			}

			self::$macros = apply_filters( 'wpsc_macros', $macros );
		}

		/**
		 * Get macros
		 *
		 * @return void
		 */
		public static function get_macros() {

			$current_user = WPSC_Current_User::$current_user;
			if ( ! $current_user->is_agent ) {
				wp_send_json_error( new WP_Error( '001', 'Bad request!' ), 400 );
			}

			$gs    = get_option( 'wpsc-gs-general' );
			$title = esc_attr__( 'Insert macro', 'supportcandy' );

			// Sort macros.
			$macros = self::$macros;
			$macros = WPSC_Functions::array_sort( $macros, 'title', SORT_ASC );

			// Unique ID.
			$unique_id = uniqid( 'wpsc_' );

			ob_start();?>

			<div style="width: 100%;">
				<table class="wpsc-setting-tbl wpscMacros">
					<thead>
						<tr>
							<th><?php esc_attr_e( 'Title', 'supportcandy' ); ?></th>
							<th><?php esc_attr_e( 'Tag', 'supportcandy' ); ?></th>
							<th><?php esc_attr_e( 'Actions', 'supportcandy' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $macros as $macro ) {

							if ( $macro['tag'] == '{{customer_email}}' && ! in_array( $current_user->agent->role, $gs['allow-ar-thread-email'] ) ) {
								continue;
							}
							?>
							<tr>
								<td class="insert-tag lable" data-label="<?php echo esc_attr( $macro['title'] ); ?>" data-tag="<?php echo esc_attr( $macro['tag'] ); ?>"><?php echo esc_html( $macro['title'] ); ?><?php echo isset( $macro['description'] ) ? '<br><small>' . esc_html( $macro['description'] ) . '</small>' : ''; ?></td>
								<td><code><?php echo esc_html( $macro['tag'] ); ?></code></td>
								<td style="white-space: nowrap;">
									<a class="copy-tag wpsc-link" title="<?php echo esc_attr_e( 'Copy Tag', 'supportcandy' ); ?>"><?php esc_attr_e( 'Copy', 'supportcandy' ); ?></a> |
									<a class="insert-tag wpsc-link" title="<?php echo esc_attr_e( 'Insert Tag', 'supportcandy' ); ?>"><?php esc_attr_e( 'Insert', 'supportcandy' ); ?></a>
								</td>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
			</div>

			<script>
				var macroTable = jQuery('table.wpscMacros').DataTable({
					ordering: false,
					paging: false, 
					info: false,
					order: [[1, "desc"]],
					columnDefs: [ 
						{ targets: -1, searchable: false, orderable: false },
						{ targets: '_all', className: 'dt-left' }
					],
					language: supportcandy.translations.datatables
				});

				jQuery(document).ready(function() {
					jQuery('div.dt-search input', macroTable.table().container()).focus();
				})

				// Insert tag directly into editor
				jQuery('.insert-tag').click(function(){

					var text_to_insert = jQuery(this).closest('tr').find('td:first-child').data('tag');
					var is_tinymce = (typeof tinyMCE != "undefined") && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden();
					if (is_tinymce) {
						tinymce.activeEditor.execCommand('mceInsertContent', false, text_to_insert);
					} else {
						var $txt = jQuery(".wpsc_textarea");
						var caretPos = $txt[0].selectionStart;
						var textAreaTxt = $txt.val();
						$txt.val(textAreaTxt.substring(0, caretPos) + text_to_insert + textAreaTxt.substring(caretPos));
					}
					wpsc_close_modal();
				});
				// Copy tag to clipboard
				jQuery('.copy-tag').click(function(){

					var text_to_copy = jQuery(this).closest('tr').find('td:first-child').data('tag');
					var temp = jQuery("<input>");
					jQuery("body").append(temp);
					temp.val(text_to_copy).select();
					document.execCommand("copy");
					temp.remove();
					wpsc_close_modal();
				});
			</script>
			<?php
			$body = ob_get_clean();

			ob_start();
			?>
			<button class="wpsc-button small secondary" onclick="wpsc_close_modal();">
				<?php esc_attr_e( 'Close', 'supportcandy' ); ?>
			</button>
			<?php
			do_action( 'wpsc_get_after_macro' );
			$footer = ob_get_clean();

			$response = array(
				'title'  => $title,
				'body'   => $body,
				'footer' => $footer,
			);
			wp_send_json( $response );
		}

		/**
		 * Replace macros in string with sample values (used for email template preview)
		 *
		 * @param string $str - string to replace macros in.
		 * @return string
		 */
		public static function replace_sample( $str ) {

			$ad_settings = get_option( 'wpsc-ms-advanced-settings' );
			$general     = get_option( 'wpsc-gs-general' );
			$page        = get_option( 'wpsc-gs-page-settings' );
			$user        = wp_get_current_user();

			$id = $ad_settings['ticket-id-format'] === 'random' ?
				substr( '48215937482159374821', 0, max( 1, intval( $ad_settings['random-id-length'] ) ) ) :
				intval( $ad_settings['starting-ticket-id'] );

			$url = add_query_arg(
				array(
					'wpsc-section' => 'ticket-list',
					'ticket-id'    => $id,
					'auth-code'    => 'SAMPLE',
				),
				! empty( $page['support-page'] ) ? get_permalink( $page['support-page'] ) : home_url( '/' )
			);

			$reply   = '<p>' . esc_html__( 'This is a sample reply. It shows how the latest reply will appear in the email.', 'supportcandy' ) . '</p>';
			$history = '<p><strong>Jane Doe</strong> <em>' . esc_html__( 'reported', 'supportcandy' ) . '</em></p><p>' . esc_html__( 'Sample ticket description.', 'supportcandy' ) . '</p><p><strong>John Smith</strong> <em>' . esc_html__( 'replied', 'supportcandy' ) . '</em></p>' . $reply;

			$values = array(
				'{{ticket_id}}'                  => $id,
				'{{id}}'                         => $id,
				'{{customer}}'                   => 'Jane Doe',
				'{{customer_name}}'              => 'Jane Doe',
				'{{customer_first_name}}'        => 'Jane',
				'{{customer_email}}'             => 'jane.doe@example.com',
				'{{last_reply}}'                 => $reply,
				'{{last_reply_user_name}}'       => 'John Smith',
				'{{last_reply_user_email}}'      => 'john.smith@example.com',
				'{{last_reply_user_first_name}}' => 'John',
				'{{last_note}}'                  => '<p>' . esc_html__( 'This is a sample private note.', 'supportcandy' ) . '</p>',
				'{{last_note_user_name}}'        => 'John Smith',
				'{{last_note_user_email}}'       => 'john.smith@example.com',
				'{{last_note_user_first_name}}'  => 'John',
				'{{current_user_name}}'          => $user->display_name,
				'{{current_user_email}}'         => $user->user_email,
				'{{current_user_first_name}}'    => $user->first_name ? $user->first_name : $user->display_name,
				'{{ticket_url}}'                 => '<a class="wpsc_link" href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $url ) . '</a>',
				'{{ticket_url_plain}}'           => esc_url( $url ),
				'{{subject}}'                    => esc_html__( 'Sample ticket subject', 'supportcandy' ),
				'{{description}}'                => '<p>' . esc_html__( 'Sample ticket description.', 'supportcandy' ) . '</p>',
			);

			// add-ons can give sample values of their macros (same signature as wpsc_replace_macros gets a ticket with this id).
			$values = apply_filters( 'wpsc_macros_sample_values', $values, $id );

			// macros that are aliases of a custom field.
			$aliases = array(
				'ticket_status'             => 'status',
				'ticket_category'           => 'category',
				'ticket_priority'           => 'priority',
				'previously_assigned_agent' => 'prev_assignee',
			);

			foreach ( self::$macros as $macro ) {

				$tag = $macro['tag'];
				if ( isset( $values[ $tag ] ) ) {
					continue;
				}

				if ( strpos( $tag, 'history' ) !== false ) {
					$values[ $tag ] = $history;
					continue;
				}

				$slug  = trim( $tag, '{}' );
				$slug  = isset( $aliases[ $slug ] ) ? $aliases[ $slug ] : $slug;
				$cf    = WPSC_Custom_Field::get_cf_by_slug( $slug );
				$value = $cf ? self::get_sample_cf_value( $cf ) : '';

				$values[ $tag ] = $value !== '' ? $value : sprintf(
					/* translators: %s: macro title */
					esc_html__( 'Sample %s', 'supportcandy' ),
					esc_html( $macro['title'] )
				);
			}

			// legacy macros that replace() still supports but are not in the macro list.
			$legacy = array(
				'ticket_subject'     => 'subject',
				'ticket_description' => 'description',
				'ticket_status'      => 'status',
				'ticket_category'    => 'category',
				'ticket_priority'    => 'priority',
			);
			foreach ( $legacy as $old => $new ) {
				if ( ! isset( $values[ '{{' . $old . '}}' ] ) && isset( $values[ '{{' . $new . '}}' ] ) ) {
					$values[ '{{' . $old . '}}' ] = $values[ '{{' . $new . '}}' ];
				}
			}

			$values['{{alias}}'] = $general['ticket-alice'] . $id;
			return str_replace( array_keys( $values ), array_values( $values ), $str );
		}

		/**
		 * Sample value of a custom field for email template preview, formatted like the real value
		 *
		 * @param WPSC_Custom_Field $cf - custom field object.
		 * @return string - empty string if there is no type specific sample.
		 */
		private static function get_sample_cf_value( $cf ) {

			if ( ! class_exists( $cf->type ) ) {
				return '';
			}

			$gs  = get_option( 'wpsc-gs-general' );
			$now = time();

			switch ( $cf->type::$slug ) {

				case 'df_status':
					$results = WPSC_Status::find()['results'];
					return $results ? esc_html( $results[0]->name ) : '';

				case 'df_priority':
					$results = WPSC_Priority::find()['results'];
					return $results ? esc_html( $results[0]->name ) : '';

				case 'df_category':
					$results = WPSC_Category::find()['results'];
					return $results ? esc_html( $results[0]->name ) : '';

				case 'cf_single_select':
				case 'cf_radio_button':
					$options = $cf->get_options();
					return $options ? esc_html( $options[0]->name ) : '';

				case 'cf_multi_select':
				case 'cf_checkbox':
					$options = array_slice( $cf->get_options(), 0, 2 );
					return esc_html( implode( ', ', array_map( fn( $option ) => $option->name, $options ) ) );

				case 'cf_date':
				case 'cf_datetime':
				case 'df_date_created':
				case 'df_date_updated':
				case 'df_date_closed':
				case 'df_last_reply_on':
				case 'df_sf_date':
					return esc_html( wp_date( $cf->date_format ? $cf->date_format : $gs['default-date-format'], $now ) );

				case 'cf_time':
					return esc_html( wp_date( $cf->time_format == 12 ? 'h:i A' : 'H:i', $now ) );

				case 'cf_number':
					return '10';

				case 'cf_email':
				case 'df_customer_email':
				case 'df_add_recipients':
					return 'jane.doe@example.com';

				case 'cf_url':
					return 'https://example.com';

				case 'cf_file_attachment_single':
				case 'cf_file_attachment_multiple':
					return '<a href="#">sample-file.pdf</a>';

				case 'df_customer_name':
					return 'Jane Doe';

				case 'df_assigned_agent':
				case 'df_prev_assignee':
				case 'df_agent_created':
				case 'df_last_reply_by':
					return 'John Smith';

				case 'df_ip_address':
					return '192.0.2.10';

				case 'df_browser':
					return 'Chrome';

				case 'df_os':
					return 'Windows';

				case 'cf_textarea':
					return esc_html__( 'Sample text entered in this field.', 'supportcandy' );
			}

			return '';
		}

		/**
		 * Replace macros in string
		 *
		 * @param string      $str - String to replace macros in.
		 * @param WPSC_ticket $ticket - ticket object.
		 * @param string      $module - module name.
		 * @return string
		 */
		public static function replace( $str, $ticket, $module = '' ) {

			// validate ticket object.
			if ( is_object( $ticket ) && ! intval( $ticket->id ) ) {
				return $str;
			}

			// get all macros within string so that will replace only matched.
			preg_match_all( '/{(\w*)}/', $str, $matches );
			$matches = isset( $matches[1] ) ? array_unique( $matches[1] ) : array();

			// replace matched tags.
			foreach ( $matches as $macro ) {

				switch ( $macro ) {

					case 'ticket_id':
						$cf  = WPSC_Custom_Field::get_cf_by_slug( 'id' );
						$str = str_replace( '{{ticket_id}}', $cf->type::get_ticket_field_val( $cf, $ticket, $module ), $str );
						break;

					case 'ticket_status':
						$cf  = WPSC_Custom_Field::get_cf_by_slug( 'status' );
						$str = str_replace( '{{ticket_status}}', $cf->type::get_ticket_field_val( $cf, $ticket, $module ), $str );
						break;

					case 'ticket_category':
						$cf  = WPSC_Custom_Field::get_cf_by_slug( 'category' );
						$str = str_replace( '{{ticket_category}}', $cf->type::get_ticket_field_val( $cf, $ticket, $module ), $str );
						break;

					case 'ticket_priority':
						$cf  = WPSC_Custom_Field::get_cf_by_slug( 'priority' );
						$str = str_replace( '{{ticket_priority}}', $cf->type::get_ticket_field_val( $cf, $ticket, $module ), $str );
						break;

					case 'ticket_subject':
						$cf  = WPSC_Custom_Field::get_cf_by_slug( 'subject' );
						$str = str_replace( '{{ticket_subject}}', $cf->type::get_ticket_field_val( $cf, $ticket, $module ), $str );
						break;

					case 'customer_name':
						$cf  = WPSC_Custom_Field::get_cf_by_slug( 'customer' );
						$str = str_replace( '{{customer_name}}', $cf->type::get_ticket_field_val( $cf, $ticket, $module ), $str );
						break;

					case 'customer_email':
						$cf  = WPSC_Custom_Field::get_cf_by_slug( 'email' );
						$str = str_replace( '{{customer_email}}', $cf->type::get_customer_field_val( $cf, $ticket->customer ), $str );
						break;

					case 'customer_first_name':
						$str = self::replace_customer_first_name( $str, $ticket );
						break;

					case 'ticket_description':
						$cf  = WPSC_Custom_Field::get_cf_by_slug( 'description' );
						$str = str_replace( '{{ticket_description}}', $cf->type::get_ticket_field_val( $cf, $ticket, $module ), $str );
						break;

					case 'last_reply':
						$str = self::replace_last_reply( $str, $ticket );
						break;

					case 'last_reply_user_name':
						$str = self::replace_last_reply_user_name( $str, $ticket );
						break;

					case 'last_reply_user_email':
						$str = self::replace_last_reply_user_email( $str, $ticket );
						break;

					case 'last_reply_user_first_name':
						$str = self::replace_last_reply_user_first_name( $str, $ticket );
						break;

					case 'last_note':
						$str = self::replace_last_note( $str, $ticket );
						break;

					case 'last_note_user_name':
						$str = self::replace_last_note_user_name( $str, $ticket );
						break;

					case 'last_note_user_email':
						$str = self::replace_last_note_user_email( $str, $ticket );
						break;

					case 'last_note_user_first_name':
						$str = self::replace_last_note_user_first_name( $str, $ticket );
						break;

					case 'current_user_name':
						$str = self::replace_current_user_name( $str, $ticket );
						break;

					case 'current_user_email':
						$str = self::replace_current_user_email( $str, $ticket );
						break;

					case 'current_user_first_name':
						$str = self::replace_current_user_first_name( $str, $ticket );
						break;

					case 'previously_assigned_agent':
						$cf  = WPSC_Custom_Field::get_cf_by_slug( 'prev_assignee' );
						$str = str_replace( '{{' . $cf->slug . '}}', $cf->type::get_ticket_field_val( $cf, $ticket, $module ), $str );
						break;

					case 'ticket_history':
						$str = self::replace_ticket_history( $str, $ticket );
						break;

					case 'ticket_history_all':
						$str = self::replace_ticket_history_all( $str, $ticket );
						break;

					case 'ticket_notes_history':
						$str = self::replace_ticket_notes_history( $str, $ticket );
						break;

					case 'ticket_history_all_with_notes':
						$str = self::replace_ticket_history_all_with_notes( $str, $ticket );
						break;

					case 'ticket_history_all_with_logs':
						$str = self::replace_ticket_history_all_with_logs( $str, $ticket );
						break;

					case 'ticket_history_all_with_notes_and_logs':
						$str = self::replace_ticket_history_all_with_notes_and_logs( $str, $ticket );
						break;

					case 'ticket_notes_history_with_logs':
						$str = self::replace_ticket_notes_history_with_logs( $str, $ticket );
						break;

					case 'ticket_url':
						$str = self::replace_ticket_url( $str, $ticket );
						break;

					case 'ticket_url_plain':
						$str = self::replace_ticket_url_plain( $str, $ticket );
						break;

					default:
						// custom fields.
						foreach ( WPSC_Custom_Field::$custom_fields as $cf ) {

							if (
								$cf->type::$slug == 'cf_html' ||
								$cf->slug != $macro
							) {
								continue;
							}

							$val = in_array( $cf->field, array( 'ticket', 'agentonly' ) ) ? $cf->type::get_ticket_field_val( $cf, $ticket, $module ) : $cf->type::get_customer_field_val( $cf, $ticket->customer );
							$str = str_replace( '{{' . $cf->slug . '}}', $val, $str );
						}

						// filter tags.
						$str = apply_filters( 'wpsc_replace_macros', $str, $ticket, $macro );
				}
			}

			// return string after replacing found tags.
			return $str;
		}

		/**
		 * Replace ticket customer first name
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_customer_first_name( $str, $ticket ) {

			$first_name = $ticket->customer->name;
			if ( $ticket->customer->user ) {
				$first_name = get_user_meta( $ticket->customer->user->ID, 'first_name', true );
				if ( ! $first_name ) {
					$first_name = $ticket->customer->name;
				}
			}

			$str = str_replace( '{{customer_first_name}}', $first_name, $str );
			return $str;
		}

		/**
		 * Replace ticket history
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_ticket_history( $str, $ticket ) {

			$advanced = get_option( 'wpsc-ms-advanced-settings' );
			$filters  = array(
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'slug'    => 'ticket',
						'compare' => '=',
						'val'     => $ticket->id,
					),
					array(
						'slug'    => 'type',
						'compare' => 'IN',
						'val'     => array( 'report', 'reply' ),
					),
					array(
						'slug'    => 'is_active',
						'compare' => '=',
						'val'     => 1,
					),
				),
				'orderby'        => 'id',
				'order'          => 'DESC',
				'items_per_page' => intval( $advanced['ticket-history-macro-threads'] ) + 1,
			);

			$threads = WPSC_Thread::find( $filters );

			if ( $threads['total_items'] ) {

				array_shift( $threads['results'] );
				$history = array_filter(
					array_map(
						fn ( $thread ) => $thread->get_history_macro(),
						$threads['results']
					)
				);
				$history = implode( '<br><hr><br>', $history );
				return str_replace( '{{ticket_history}}', $history, $str );

			} else {

				$history = esc_attr__( 'Not Applicable', 'supportcandy' );
				return str_replace( '{{ticket_history}}', $history, $str );
			}
		}

		/**
		 * Replace ticket history all
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_ticket_history_all( $str, $ticket ) {

			$filters = array(
				'items_per_page' => 0,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'slug'    => 'ticket',
						'compare' => '=',
						'val'     => $ticket->id,
					),
					array(
						'slug'    => 'type',
						'compare' => 'IN',
						'val'     => array( 'report', 'reply' ),
					),
					array(
						'slug'    => 'is_active',
						'compare' => '=',
						'val'     => 1,
					),
				),
				'orderby'        => 'id',
				'order'          => 'DESC',
			);

			$threads = WPSC_Thread::find( $filters );

			if ( $threads['total_items'] ) {

				$history = array_filter(
					array_map(
						fn ( $thread ) => $thread->get_history_macro(),
						$threads['results']
					)
				);
				$history = implode( '<br><hr><br>', $history );
				return str_replace( '{{ticket_history_all}}', $history, $str );

			} else {

				$history = esc_attr__( 'Not Applicable', 'supportcandy' );
				return str_replace( '{{ticket_history_all}}', $history, $str );
			}
		}

		/**
		 * Replace ticket notes history
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_ticket_notes_history( $str, $ticket ) {

			$filters = array(
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'slug'    => 'ticket',
						'compare' => '=',
						'val'     => $ticket->id,
					),
					array(
						'slug'    => 'type',
						'compare' => '=',
						'val'     => 'note',
					),
					array(
						'slug'    => 'is_active',
						'compare' => '=',
						'val'     => 1,
					),
				),
				'orderby'    => 'id',
				'order'      => 'DESC',
			);

			$threads = WPSC_Thread::find( $filters );

			if ( $threads['total_items'] ) {

				$history = array_filter(
					array_map(
						fn ( $thread ) => $thread->get_history_macro(),
						$threads['results']
					)
				);
				$history = implode( '<br><hr><br>', $history );
				return str_replace( '{{ticket_notes_history}}', $history, $str );

			} else {

				$history = esc_attr__( 'Not Applicable', 'supportcandy' );
				return str_replace( '{{ticket_notes_history}}', $history, $str );
			}
		}

		/**
		 * Replace ticket history all with notes
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_ticket_history_all_with_notes( $str, $ticket ) {

			$filters = array(
				'items_per_page' => 0,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'slug'    => 'ticket',
						'compare' => '=',
						'val'     => $ticket->id,
					),
					array(
						'slug'    => 'type',
						'compare' => 'IN',
						'val'     => array( 'report', 'reply', 'note' ),
					),
					array(
						'slug'    => 'is_active',
						'compare' => '=',
						'val'     => 1,
					),
				),
				'orderby'        => 'id',
				'order'          => 'DESC',
			);

			$threads = WPSC_Thread::find( $filters );

			if ( $threads['total_items'] ) {

				$history = array_filter(
					array_map(
						fn ( $thread ) => $thread->get_history_macro(),
						$threads['results']
					)
				);
				$history = implode( '<div></div><br><hr><br><div></div>', $history );
				return str_replace( '{{ticket_history_all_with_notes}}', $history, $str );

			} else {

				$history = esc_attr__( 'Not Applicable', 'supportcandy' );
				return str_replace( '{{ticket_history_all_with_notes}}', $history, $str );
			}
		}

		/**
		 * Replace ticket history all with logs
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_ticket_history_all_with_logs( $str, $ticket ) {

			$filters = array(
				'items_per_page' => 0,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'slug'    => 'ticket',
						'compare' => '=',
						'val'     => $ticket->id,
					),
					array(
						'slug'    => 'type',
						'compare' => 'IN',
						'val'     => array( 'report', 'reply', 'log' ),
					),
					array(
						'slug'    => 'is_active',
						'compare' => '=',
						'val'     => 1,
					),
				),
				'orderby'        => 'id',
				'order'          => 'DESC',
			);

			$threads = WPSC_Thread::find( $filters );

			if ( $threads['total_items'] ) {

				$history = array_filter(
					array_map(
						fn ( $thread ) => $thread->get_history_macro(),
						$threads['results']
					)
				);
				$history = implode( '<br><hr><br>', $history );
				return str_replace( '{{ticket_history_all_with_logs}}', $history, $str );

			} else {

				$history = esc_attr__( 'Not Applicable', 'supportcandy' );
				return str_replace( '{{ticket_history_all_with_logs}}', $history, $str );
			}
		}

		/**
		 * Replace ticket history all with logs and notes
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_ticket_history_all_with_notes_and_logs( $str, $ticket ) {

			$filters = array(
				'items_per_page' => 0,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'slug'    => 'ticket',
						'compare' => '=',
						'val'     => $ticket->id,
					),
					array(
						'slug'    => 'type',
						'compare' => 'IN',
						'val'     => array( 'report', 'reply', 'note', 'log' ),
					),
					array(
						'slug'    => 'is_active',
						'compare' => '=',
						'val'     => 1,
					),
				),
				'orderby'        => 'id',
				'order'          => 'DESC',
			);

			$threads = WPSC_Thread::find( $filters );

			if ( $threads['total_items'] ) {

				$history = array_filter(
					array_map(
						fn ( $thread ) => $thread->get_history_macro(),
						$threads['results']
					)
				);
				$history = implode( '<br><hr><br>', $history );
				return str_replace( '{{ticket_history_all_with_notes_and_logs}}', $history, $str );

			} else {

				$history = esc_attr__( 'Not Applicable', 'supportcandy' );
				return str_replace( '{{ticket_history_all_with_notes_and_logs}}', $history, $str );
			}
		}

		/**
		 * Replace ticket notes history with logs
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_ticket_notes_history_with_logs( $str, $ticket ) {

			$filters = array(
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'slug'    => 'ticket',
						'compare' => '=',
						'val'     => $ticket->id,
					),
					array(
						'slug'    => 'type',
						'compare' => 'IN',
						'val'     => array( 'note', 'log' ),
					),
					array(
						'slug'    => 'is_active',
						'compare' => '=',
						'val'     => 1,
					),
				),
				'orderby'    => 'id',
				'order'      => 'DESC',
			);

			$threads = WPSC_Thread::find( $filters );

			if ( $threads['total_items'] ) {

				$history = array_filter(
					array_map(
						fn ( $thread ) => $thread->get_history_macro(),
						$threads['results']
					)
				);
				$history = implode( '<br><hr><br>', $history );
				return str_replace( '{{ticket_notes_history_with_logs}}', $history, $str );

			} else {

				$history = esc_attr__( 'Not Applicable', 'supportcandy' );
				return str_replace( '{{ticket_notes_history_with_logs}}', $history, $str );
			}
		}

		/**
		 * Replace ticket URL
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_ticket_url( $str, $ticket ) {

			$ticket_url = '<a class="wpsc_link" href="' . $ticket->get_url() . '" target="_blank">' . $ticket->get_url() . '</a>';
			$str        = str_replace( '{{ticket_url}}', $ticket_url, $str );
			return $str;
		}

		/**
		 * Replace ticket URL
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_ticket_url_plain( $str, $ticket ) {

			$str = str_replace( '{{ticket_url_plain}}', $ticket->get_url(), $str );
			return $str;
		}

		/**
		 * Replace ticket last reply
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_last_reply( $str, $ticket ) {

			$thread = $ticket->get_last_reply();
			if ( $thread ) {
				$str = str_replace( '{{last_reply}}', $thread->get_printable_string(), $str );
			} else {
				$str = str_replace(
					'{{last_reply}}',
					esc_attr__( 'Not Applicable', 'supportcandy' ),
					$str
				);
			}

			return $str;
		}

		/**
		 * Replace ticket last reply user name
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_last_reply_user_name( $str, $ticket ) {

			$thread = $ticket->get_last_reply();
			if ( ! $thread ) {
				return str_replace( '{{last_reply_user_name}}', '', $str );
			}

			$str = str_replace( '{{last_reply_user_name}}', $thread->customer->name, $str );
			return $str;
		}

		/**
		 * Replace ticket last reply user email
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_last_reply_user_email( $str, $ticket ) {

			$thread = $ticket->get_last_reply();
			if ( ! $thread ) {
				return str_replace( '{{last_reply_user_email}}', '', $str );
			}

			$str = str_replace( '{{last_reply_user_email}}', $thread->customer->email, $str );
			return $str;
		}

		/**
		 * Replace ticket last reply user first name
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_last_reply_user_first_name( $str, $ticket ) {

			$thread = $ticket->get_last_reply();
			if ( ! $thread ) {
				return str_replace( '{{last_reply_user_first_name}}', '', $str );
			}

			$first_name = $thread->customer->name;
			if ( $thread->customer->user ) {
				$first_name = get_user_meta( $thread->customer->user->ID, 'first_name', true );
				if ( ! $first_name ) {
					$first_name = $thread->customer->name;
				}
			}

			$str = str_replace( '{{last_reply_user_first_name}}', $first_name, $str );
			return $str;
		}

		/**
		 * Replace ticket last note
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_last_note( $str, $ticket ) {

			$thread = $ticket->get_last_note();
			if ( $thread ) {
				$str = str_replace( '{{last_note}}', $thread->get_printable_string(), $str );
			} else {
				$str = str_replace(
					'{{last_note}}',
					esc_attr__( 'Not Applicable', 'supportcandy' ),
					$str
				);
			}

			return $str;
		}

		/**
		 * Replace ticket last note user name
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_last_note_user_name( $str, $ticket ) {

			$thread = $ticket->get_last_note();
			if ( ! $thread ) {
				return str_replace( '{{last_note_user_name}}', '', $str );
			}

			$str = str_replace( '{{last_note_user_name}}', $thread->customer->name, $str );
			return $str;
		}

		/**
		 * Replace ticket last note user email
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_last_note_user_email( $str, $ticket ) {

			$thread = $ticket->get_last_note();
			if ( ! $thread ) {
				return str_replace( '{{last_note_user_email}}', '', $str );
			}

			$str = str_replace( '{{last_note_user_email}}', $thread->customer->email, $str );
			return $str;
		}

		/**
		 * Replace ticket last note user first name
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_last_note_user_first_name( $str, $ticket ) {

			$thread = $ticket->get_last_note();
			if ( ! $thread ) {
				return str_replace( '{{last_note_user_first_name}}', '', $str );
			}

			$first_name = $thread->customer->name;
			if ( $thread->customer->user ) {
				$first_name = get_user_meta( $thread->customer->user->ID, 'first_name', true );
				if ( ! $first_name ) {
					$first_name = $thread->customer->name;
				}
			}

			$str = str_replace( '{{last_note_user_first_name}}', $first_name, $str );
			return $str;
		}

		/**
		 * Replace current user name
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_current_user_name( $str, $ticket ) {

			$current_user = WPSC_Current_User::$current_user;
			if ( $current_user->is_customer ) {
				$str = str_replace( '{{current_user_name}}', $current_user->customer->name, $str );
			} else {
				$str = str_replace( '{{current_user_name}}', esc_attr__( 'Not Applicable', 'supportcandy' ), $str );
			}

			return $str;
		}

		/**
		 * Replace current user email
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_current_user_email( $str, $ticket ) {

			$current_user = WPSC_Current_User::$current_user;
			if ( $current_user->is_customer ) {
				$str = str_replace( '{{current_user_email}}', $current_user->customer->email, $str );
			} else {
				$str = str_replace( '{{current_user_email}}', esc_attr__( 'Not Applicable', 'supportcandy' ), $str );
			}

			return $str;
		}

		/**
		 * Replace current user first name
		 *
		 * @param string      $str - string to replace tags in.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function replace_current_user_first_name( $str, $ticket ) {

			$current_user = WPSC_Current_User::$current_user;

			if ( $current_user->is_customer ) {
				$first_name = $current_user->customer->name;
				if ( $current_user->customer->user ) {
					$first_name = get_user_meta( $current_user->user->ID, 'first_name', true );
					if ( ! $first_name ) {
						$first_name = $current_user->customer->name;
					}
				}
				$str = str_replace( '{{current_user_first_name}}', $first_name, $str );
			} else {
				$str = str_replace( '{{current_user_first_name}}', esc_attr__( 'Not Applicable', 'supportcandy' ), $str );
			}

			return $str;
		}
	}
endif;

WPSC_Macros::init();
