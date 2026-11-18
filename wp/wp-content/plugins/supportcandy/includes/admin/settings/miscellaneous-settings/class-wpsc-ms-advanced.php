<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_MS_Advanced' ) ) :

	final class WPSC_MS_Advanced {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// User interface.
			add_action( 'wp_ajax_wpsc_get_ms_advanced', array( __CLASS__, 'load_settings_ui' ) );
			add_action( 'wp_ajax_wpsc_set_ms_advanced', array( __CLASS__, 'save_settings' ) );
			add_action( 'wp_ajax_wpsc_reset_ms_advanced', array( __CLASS__, 'reset_settings' ) );

			// Print in create ticket form.
			add_action( 'wpsc_print_tff', array( __CLASS__, 'print_tff' ) );
		}

		/**
		 * Reset settings
		 *
		 * @return void
		 */
		public static function reset() {

			$advanced = apply_filters(
				'wpsc_ms_advanced_settings',
				array(
					'public-mode'                    => 0,
					'public-mode-reply'              => 0,
					'reply-confirmation'             => 1,
					'note-confirmation'              => 0,
					'thread-date-display-as'         => 'diff',
					'thread-date-format'             => 'F d, Y h:i A',
					'do-not-notify-owner'            => 1,
					'do-not-notify-owner-status'     => 1,
					'ticket-id-format'               => 'sequential',
					'starting-ticket-id'             => 1,
					'random-id-length'               => 8,
					'ticket-history-macro-threads'   => 5,
					'register-user-if-not-exist'     => 0,
					'auto-archive-tickets-time'      => 0,
					'auto-archive-tickets-unit'      => 'days',
					'permanent-archive-tickets-time' => 0,
					'permanent-archive-tickets-unit' => 'days',
					'permanent-delete-tickets-time'  => 0,
					'permanent-delete-tickets-unit'  => 'days',
					'allow-bcc'                      => 0,
					'allow-cc'                       => 0,
					'view-more'                      => 1,
					'allow-reply-to-close-ticket'    => array( 'customer', 'agent' ),
					'raised-by-user'                 => 'customer',
					'allow-my-profile'               => 1,
					'allow-agent-profile'            => 1,
					'ticket-url-auth'                => 0,
					'rest-api'                       => 1,
					'agent-collision'                => 1,
					'self-assign'                    => 1,
					'reply-close'                    => 1,
				)
			);
			update_option( 'wpsc-ms-advanced-settings', $advanced );
		}

		/**
		 * Settings user interface
		 *
		 * @return void
		 */
		public static function load_settings_ui() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$settings = get_option( 'wpsc-ms-advanced-settings', array() );

			$close_reply = apply_filters(
				'wpsc_allowed_close_ticket_reply',
				array(
					'customer' => esc_attr__( 'Customer', 'supportcandy' ),
					'agent'    => esc_attr__( 'Agent', 'supportcandy' ),
				)
			);
			?>

			<form action="#" onsubmit="return false;" class="wpsc-frm-ms-advanced">
				<div class="wpsc-setting-group">
					<h3 class="wpsc-setting-group-title"><?php echo esc_html__( 'Security & Access', 'supportcandy' ); ?></h3>
					<div class="wpsc-setting-group-body">
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Public mode', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'When enabled, customers can see all tickets in the ticket list, including tickets raised by other customers.', 'supportcandy' ); ?></p>
							<select id="wpsc-public-mode" name="public-mode" data-initial="<?php echo esc_attr( $settings['public-mode'] ); ?>" data-confirm="<?php esc_attr_e( "Every customer will see all tickets, including other customers' tickets. Are you sure you want to enable public mode?", 'supportcandy' ); ?>">
								<option <?php selected( $settings['public-mode'], 1 ); ?> value="1"><?php esc_attr_e( 'Enable', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['public-mode'], 0 ); ?> value="0"><?php esc_attr_e( 'Disable', 'supportcandy' ); ?></option>
							</select>
							<div class="wpsc-setting-warning" data-depends-on="public-mode" data-depends-value="1">
								<?php esc_attr_e( "Every customer will see all tickets, including other customers' tickets.", 'supportcandy' ); ?>
							</div>
						</div>
						<div class="wpsc-input-group" data-depends-on="public-mode" data-depends-value="1">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Allow others to reply to public tickets', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Lets customers reply to public tickets raised by other customers. When disabled, they can only view those tickets.', 'supportcandy' ); ?></p>
							<select name="public-mode-reply">
								<option <?php selected( $settings['public-mode-reply'], 1 ); ?> value="1"><?php esc_attr_e( 'Enable', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['public-mode-reply'], 0 ); ?> value="0"><?php esc_attr_e( 'Disable', 'supportcandy' ); ?></option>
							</select>
						</div>
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Ticket url authentication', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'When enabled, visitors must sign in before a ticket link opens the ticket. When disabled, the secure link in emails opens the ticket without login.', 'supportcandy' ); ?></p>
							<select name="ticket-url-auth">
								<option <?php selected( $settings['ticket-url-auth'], '1' ); ?> value="1"><?php esc_attr_e( 'Enable', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['ticket-url-auth'], '0' ); ?> value="0"><?php esc_attr_e( 'Disable', 'supportcandy' ); ?></option>
							</select>
						</div>
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'REST API', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Turns on the SupportCandy REST API, used by external apps and by add-ons such as Slack. When disabled, its endpoints are not available.', 'supportcandy' ); ?></p>
							<select name="rest-api">
								<option <?php selected( $settings['rest-api'], 1 ); ?> value="1"><?php esc_attr_e( 'Enable', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['rest-api'], 0 ); ?> value="0"><?php esc_attr_e( 'Disable', 'supportcandy' ); ?></option>
							</select>
						</div>
					</div>
				</div>
				<div class="wpsc-setting-group">
					<h3 class="wpsc-setting-group-title"><?php echo esc_html__( 'Ticket Numbering', 'supportcandy' ); ?></h3>
					<div class="wpsc-setting-group-body">
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Ticket id', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Sequential numbers tickets one after another. Random gives each new ticket a random number with a fixed number of digits.', 'supportcandy' ); ?></p>
							<select id="wpsc-ticket-id-format" name="ticket-id-format">
								<option <?php selected( $settings['ticket-id-format'], 'sequential' ); ?> value="sequential"><?php esc_attr_e( 'Sequential', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['ticket-id-format'], 'random' ); ?> value="random"><?php esc_attr_e( 'Random', 'supportcandy' ); ?></option>
							</select>
						</div>
						<div class="wpsc-input-group" data-depends-on="ticket-id-format" data-depends-value="sequential">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Starting ticket id', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'ID given to the next new ticket. It only applies if it is higher than the highest existing ticket ID.', 'supportcandy' ); ?></p>
							<input type="number" onkeydown="javascript: return event.keyCode == 69 ? false : true" name="starting-ticket-id" value="<?php echo esc_attr( $settings['starting-ticket-id'] ); ?>">
						</div>
						<div class="wpsc-input-group" data-depends-on="ticket-id-format" data-depends-value="random">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Random id length', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Number of digits in a random ticket ID. For example, 8 gives IDs like 48215937.', 'supportcandy' ); ?></p>
							<input type="number" min="1" onkeydown="javascript: return event.keyCode == 69 ? false : true" name="random-id-length" value="<?php echo esc_attr( $settings['random-id-length'] ); ?>">
						</div>
					</div>
				</div>
				<div class="wpsc-setting-group">
					<h3 class="wpsc-setting-group-title"><?php echo esc_html__( 'Replying & Notes', 'supportcandy' ); ?></h3>
					<div class="wpsc-setting-group-body">
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Reply confirmation', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Shows an "Are you sure?" confirmation before a reply is submitted, including Reply & Close.', 'supportcandy' ); ?></p>
							<select id="wpsc-reply-confirmation" name="reply-confirmation">
								<option <?php selected( $settings['reply-confirmation'], 1 ); ?> value="1"><?php esc_attr_e( 'Enable', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['reply-confirmation'], 0 ); ?> value="0"><?php esc_attr_e( 'Disable', 'supportcandy' ); ?></option>
							</select>
						</div>
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Note confirmation', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Shows an "Are you sure?" confirmation before a private note is submitted.', 'supportcandy' ); ?></p>
							<select id="wpsc-note-confirmation" name="note-confirmation">
								<option <?php selected( $settings['note-confirmation'], 1 ); ?> value="1"><?php esc_attr_e( 'Enable', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['note-confirmation'], 0 ); ?> value="0"><?php esc_attr_e( 'Disable', 'supportcandy' ); ?></option>
							</select>
						</div>
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for="">CC</label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Adds a CC field to the ticket reply form. Email addresses entered there (comma separated) are copied on the reply email notification.', 'supportcandy' ); ?></p>
							<select name="allow-cc">
								<option <?php selected( $settings['allow-cc'], 1 ); ?> value="1"><?php esc_attr_e( 'Enable', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['allow-cc'], 0 ); ?> value="0"><?php esc_attr_e( 'Disable', 'supportcandy' ); ?></option>
							</select>
						</div>
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for="">BCC</label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Adds a BCC field to the ticket reply form. Email addresses entered there (comma separated) get a hidden copy of the reply email notification.', 'supportcandy' ); ?></p>
							<select name="allow-bcc">
								<option <?php selected( $settings['allow-bcc'], 1 ); ?> value="1"><?php esc_attr_e( 'Enable', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['allow-bcc'], 0 ); ?> value="0"><?php esc_attr_e( 'Disable', 'supportcandy' ); ?></option>
							</select>
						</div>
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Reply and Close button', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Shows a Reply & Close button that posts the reply and closes the ticket in one step. Only users allowed to close tickets see it.', 'supportcandy' ); ?></p>
							<select name="reply-close">
								<option <?php selected( $settings['reply-close'], 1 ); ?> value="1"><?php esc_attr_e( 'Enable', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['reply-close'], 0 ); ?> value="0"><?php esc_attr_e( 'Disable', 'supportcandy' ); ?></option>
							</select>
						</div>
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Allow reply to closed tickets', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Who can still reply to tickets in a closed status. Others will not see the reply form, and their email replies are rejected when Email Piping is used.', 'supportcandy' ); ?></p>
							<select id="wpsc-allowed-close-ticket-reply"  multiple name="allow-reply-to-close-ticket[]">
								<?php
								foreach ( $close_reply as $key => $tag ) :
									$selected = in_array( $key, $settings['allow-reply-to-close-ticket'] ) ? 'selected' : '';
									?>
									<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $tag ); ?></option>
									<?php
								endforeach;
								?>
							</select>
							<script>jQuery('#wpsc-allowed-close-ticket-reply').selectWoo();</script>
						</div>
					</div>
				</div>
				<div class="wpsc-setting-group">
					<h3 class="wpsc-setting-group-title"><?php echo esc_html__( 'Thread Display', 'supportcandy' ); ?></h3>
					<div class="wpsc-setting-group-body">
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Thread date display as', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Show the time of each ticket thread as a full date or as time passed, like "1 hour ago".', 'supportcandy' ); ?></p>
							<select name="thread-date-display-as">
								<option <?php selected( $settings['thread-date-display-as'], 'date' ); ?> value="date"><?php esc_attr_e( 'Date Format', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['thread-date-display-as'], 'diff' ); ?> value="diff"><?php esc_attr_e( 'Date Difference (e.g. 1 hour ago)', 'supportcandy' ); ?></option>
							</select>
						</div>
						<div class="wpsc-input-group" data-depends-on="thread-date-display-as" data-depends-value="date">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Thread date format', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'PHP date format used for thread dates and log entries. Example: F d, Y h:i A.', 'supportcandy' ); ?></p>
							<input type="text" id="wpsc-thread-date-format" name="thread-date-format" value="<?php echo esc_attr( $settings['thread-date-format'] ); ?>">
						</div>
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'View more/less thread', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Collapses long thread messages with a "View More" link so the ticket conversation is easier to scan.', 'supportcandy' ); ?></p>
							<select name="view-more">
								<option <?php selected( $settings['view-more'], 1 ); ?> value="1"><?php esc_attr_e( 'Enable', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['view-more'], 0 ); ?> value="0"><?php esc_attr_e( 'Disable', 'supportcandy' ); ?></option>
							</select>
						</div>
					</div>
				</div>
				<div class="wpsc-setting-group">
					<h3 class="wpsc-setting-group-title"><?php echo esc_html__( 'Notifications', 'supportcandy' ); ?></h3>
					<div class="wpsc-setting-group-body">
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Do not notify owner', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Adds a "Do not notify owner" checkbox to the ticket form for agents. When checked, the customer does not receive the new ticket email.', 'supportcandy' ); ?></p>
							<select id="wpsc-do-not-notify-owner" name="do-not-notify-owner">
								<option <?php selected( $settings['do-not-notify-owner'], 1 ); ?> value="1"><?php esc_attr_e( 'Enable', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['do-not-notify-owner'], 0 ); ?> value="0"><?php esc_attr_e( 'Disable', 'supportcandy' ); ?></option>
							</select>
						</div>
						<div class="wpsc-input-group" data-depends-on="do-not-notify-owner" data-depends-value="1">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Do not notify owner status', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Whether the "Do not notify owner" checkbox is checked or unchecked by default on the ticket form.', 'supportcandy' ); ?></p>
							<select id="wpsc-do-not-notify-owner-status" name="do-not-notify-owner-status">
								<option <?php selected( $settings['do-not-notify-owner-status'], 1 ); ?> value="1"><?php esc_attr_e( 'Checked', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['do-not-notify-owner-status'], 0 ); ?> value="0"><?php esc_attr_e( 'Unchecked', 'supportcandy' ); ?></option>
							</select>
						</div>
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Ticket history macro threads', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Number of recent messages included by the {{ticket_history}} macro. The latest reply is not included.', 'supportcandy' ); ?></p>
							<input type="number" onkeydown="javascript: return event.keyCode == 69 ? false : true" id="wpsc-ticket-history-macro-threads" name="ticket-history-macro-threads" value="<?php echo esc_attr( $settings['ticket-history-macro-threads'] ); ?>">
						</div>
					</div>
				</div>
				<div class="wpsc-setting-group">
					<h3 class="wpsc-setting-group-title"><?php echo esc_html__( 'Users & Profiles', 'supportcandy' ); ?></h3>
					<div class="wpsc-setting-group-body">
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Register user if not exist', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'When a ticket is created for an email address with no WordPress account, create an account for it and send the WordPress new user email.', 'supportcandy' ); ?></p>
							<select id="wpsc-register-user-if-not-exist" name="register-user-if-not-exist">
								<option <?php selected( $settings['register-user-if-not-exist'], 1 ); ?> value="1"><?php esc_attr_e( 'Enable', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['register-user-if-not-exist'], 0 ); ?> value="0"><?php esc_attr_e( 'Disable', 'supportcandy' ); ?></option>
							</select>
						</div>
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Raised By User', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'When an agent creates a ticket for a customer, show the first message as written by the customer or by the agent. Agent also adds the agent\'s signature.', 'supportcandy' ); ?></p>
							<select name="raised-by-user">
								<option <?php selected( $settings['raised-by-user'], 'customer' ); ?> value="customer"><?php esc_attr_e( 'Customer', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['raised-by-user'], 'agent' ); ?> value="agent"><?php esc_attr_e( 'Agent', 'supportcandy' ); ?></option>
							</select>
						</div>
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'My Profile tab', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Shows the My Profile tab on the support page, where users can update their profile details.', 'supportcandy' ); ?></p>
							<select id="wpsc-allow-my-profile" name="allow-my-profile">
								<option <?php selected( $settings['allow-my-profile'], 1 ); ?> value="1"><?php esc_attr_e( 'Enable', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['allow-my-profile'], 0 ); ?> value="0"><?php esc_attr_e( 'Disable', 'supportcandy' ); ?></option>
							</select>
						</div>
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Agent Profile tab', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Shows the Agent Profile tab to agents, where they can set their signature, default filter, default tab, working hours and leaves.', 'supportcandy' ); ?></p>
							<select id="wpsc-allow-agent-profile" name="allow-agent-profile">
								<option <?php selected( $settings['allow-agent-profile'], 1 ); ?> value="1"><?php esc_attr_e( 'Enable', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['allow-agent-profile'], 0 ); ?> value="0"><?php esc_attr_e( 'Disable', 'supportcandy' ); ?></option>
							</select>
						</div>
					</div>
				</div>
				<div class="wpsc-setting-group">
					<h3 class="wpsc-setting-group-title"><?php echo esc_html__( 'Agent Workflow', 'supportcandy' ); ?></h3>
					<div class="wpsc-setting-group-body">
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Agent collision', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Shows other agents who are viewing the same ticket at the same time, to avoid two agents replying at once.', 'supportcandy' ); ?></p>
							<select name="agent-collision">
								<option <?php selected( $settings['agent-collision'], 1 ); ?> value="1"><?php esc_attr_e( 'Enable', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['agent-collision'], 0 ); ?> value="0"><?php esc_attr_e( 'Disable', 'supportcandy' ); ?></option>
							</select>
						</div>
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Allow agent to self assign ticket', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Shows an "Assign to me" option on unassigned tickets. The agent\'s role also needs permission to assign unassigned tickets.', 'supportcandy' ); ?></p>
							<select name="self-assign">
								<option <?php selected( $settings['self-assign'], 1 ); ?> value="1"><?php esc_attr_e( 'Enable', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['self-assign'], 0 ); ?> value="0"><?php esc_attr_e( 'Disable', 'supportcandy' ); ?></option>
							</select>
						</div>
					</div>
				</div>
				<div class="wpsc-setting-group">
					<h3 class="wpsc-setting-group-title"><?php echo esc_html__( 'Cleanup & Retention', 'supportcandy' ); ?></h3>
					<div class="wpsc-setting-group-body">
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Auto archive closed tickets', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Move closed tickets to the archive after they have been closed for this long. Set 0 to turn off.', 'supportcandy' ); ?></p>
							<div class="divide-bar">
								<input type="number" id="wpsc-auto-archive-tickets-time" name="auto-archive-tickets-time" value="<?php echo esc_attr( $settings['auto-archive-tickets-time'] ); ?>">
								<select id="wpsc-auto-archive-tickets-unit" name="auto-archive-tickets-unit">
									<option <?php selected( $settings['auto-archive-tickets-unit'], 'days' ); ?> value="days"><?php esc_attr_e( 'Day(s)', 'supportcandy' ); ?></option>
									<option <?php selected( $settings['auto-archive-tickets-unit'], 'month' ); ?> value="month"><?php esc_attr_e( 'Month(s)', 'supportcandy' ); ?></option>
									<option <?php selected( $settings['auto-archive-tickets-unit'], 'year' ); ?> value="year"><?php esc_attr_e( 'Year(s)', 'supportcandy' ); ?></option>
								</select>  
							</div>
						</div>
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Permanently delete archived tickets', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Permanently delete archived tickets not updated for this long; this cannot be undone. Set 0 to turn off.', 'supportcandy' ); ?></p>
							<div class="divide-bar">
								<input type="number" id="wpsc-permanent-archive-tickets-time" name="permanent-archive-tickets-time" value="<?php echo esc_attr( $settings['permanent-archive-tickets-time'] ); ?>">
								<select id="wpsc-permanent-archive-tickets-unit" name="permanent-archive-tickets-unit">
									<option <?php selected( $settings['permanent-archive-tickets-unit'], 'days' ); ?> value="days"><?php esc_attr_e( 'Day(s)', 'supportcandy' ); ?></option>
									<option <?php selected( $settings['permanent-archive-tickets-unit'], 'month' ); ?> value="month"><?php esc_attr_e( 'Month(s)', 'supportcandy' ); ?></option>
									<option <?php selected( $settings['permanent-archive-tickets-unit'], 'year' ); ?> value="year"><?php esc_attr_e( 'Year(s)', 'supportcandy' ); ?></option>
								</select>  
							</div>
						</div>
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Permanently delete deleted tickets', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Permanently delete tickets in the deleted list not updated for this long; this cannot be undone. Set 0 to turn off.', 'supportcandy' ); ?></p>
							<div class="divide-bar">
								<input type="number" id="wpsc-permanent-delete-tickets-time" name="permanent-delete-tickets-time" value="<?php echo esc_attr( $settings['permanent-delete-tickets-time'] ); ?>">
								<select id="wpsc-permanent-delete-tickets-unit" name="permanent-delete-tickets-unit">
									<option <?php selected( $settings['permanent-delete-tickets-unit'], 'days' ); ?> value="days"><?php esc_attr_e( 'Day(s)', 'supportcandy' ); ?></option>
									<option <?php selected( $settings['permanent-delete-tickets-unit'], 'month' ); ?> value="month"><?php esc_attr_e( 'Month(s)', 'supportcandy' ); ?></option>
									<option <?php selected( $settings['permanent-delete-tickets-unit'], 'year' ); ?> value="year"><?php esc_attr_e( 'Year(s)', 'supportcandy' ); ?></option>
								</select>  
							</div>
						</div>
					</div>
				</div>
				<?php
				ob_start();
				do_action( 'wpsc_ms_advanced_settings' );
				$wpsc_addon_settings = trim( ob_get_clean() );
				if ( $wpsc_addon_settings ) :
					?>
					<div class="wpsc-setting-group">
						<h3 class="wpsc-setting-group-title"><?php echo esc_html__( 'Add-ons', 'supportcandy' ); ?></h3>
						<div class="wpsc-setting-group-body">
							<?php echo $wpsc_addon_settings; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- output of add-on hook, already escaped by add-ons. ?>
						</div>
					</div>
				<?php endif; ?>
				<script>wpsc_init_dependent_fields( jQuery( '.wpsc-frm-ms-advanced' ) );</script>
				<input type="hidden" name="action" value="wpsc_set_ms_advanced">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_set_ms_advanced' ) ); ?>">
			</form>
			<div class="setting-footer-actions">
				<button 
					class="wpsc-button normal primary margin-right"
					onclick="wpsc_set_ms_advanced(this);">
					<?php esc_attr_e( 'Submit', 'supportcandy' ); ?></button>
				<button 
					class="wpsc-button normal secondary"
					onclick="wpsc_reset_ms_advanced(this, '<?php echo esc_attr( wp_create_nonce( 'wpsc_reset_ms_advanced' ) ); ?>');">
					<?php esc_attr_e( 'Reset default', 'supportcandy' ); ?></button>
			</div>
			<?php
			wp_die();
		}

		/**
		 * Save settings
		 *
		 * @return void
		 */
		public static function save_settings() {

			if ( check_ajax_referer( 'wpsc_set_ms_advanced', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			global $wpdb;

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$advanced = apply_filters(
				'wpsc_set_ms_advanced',
				array(
					'public-mode'                    => isset( $_POST['public-mode'] ) ? intval( $_POST['public-mode'] ) : 1,
					'public-mode-reply'              => isset( $_POST['public-mode-reply'] ) ? intval( $_POST['public-mode-reply'] ) : 0,
					'reply-confirmation'             => isset( $_POST['reply-confirmation'] ) ? intval( $_POST['reply-confirmation'] ) : 1,
					'note-confirmation'              => isset( $_POST['note-confirmation'] ) ? intval( $_POST['note-confirmation'] ) : 0,
					'thread-date-display-as'         => isset( $_POST['thread-date-display-as'] ) ? sanitize_text_field( wp_unslash( $_POST['thread-date-display-as'] ) ) : 'diff',
					'thread-date-format'             => isset( $_POST['thread-date-format'] ) ? sanitize_text_field( wp_unslash( $_POST['thread-date-format'] ) ) : 'F d, Y h:i A',
					'do-not-notify-owner'            => isset( $_POST['do-not-notify-owner'] ) ? intval( $_POST['do-not-notify-owner'] ) : 0,
					'do-not-notify-owner-status'     => isset( $_POST['do-not-notify-owner-status'] ) ? intval( $_POST['do-not-notify-owner-status'] ) : 0,
					'ticket-id-format'               => isset( $_POST['ticket-id-format'] ) ? sanitize_text_field( wp_unslash( $_POST['ticket-id-format'] ) ) : 'sequential',
					'starting-ticket-id'             => isset( $_POST['starting-ticket-id'] ) ? intval( $_POST['starting-ticket-id'] ) : 1,
					'random-id-length'               => isset( $_POST['random-id-length'] ) ? intval( $_POST['random-id-length'] ) : 8,
					'ticket-history-macro-threads'   => isset( $_POST['ticket-history-macro-threads'] ) ? intval( $_POST['ticket-history-macro-threads'] ) : 5,
					'register-user-if-not-exist'     => isset( $_POST['register-user-if-not-exist'] ) ? intval( $_POST['register-user-if-not-exist'] ) : '0',
					'auto-archive-tickets-time'      => isset( $_POST['auto-archive-tickets-time'] ) ? intval( $_POST['auto-archive-tickets-time'] ) : '0',
					'auto-archive-tickets-unit'      => isset( $_POST['auto-archive-tickets-unit'] ) ? sanitize_text_field( wp_unslash( $_POST['auto-archive-tickets-unit'] ) ) : 'days',
					'permanent-archive-tickets-time' => isset( $_POST['permanent-archive-tickets-time'] ) ? intval( $_POST['permanent-archive-tickets-time'] ) : '0',
					'permanent-archive-tickets-unit' => isset( $_POST['permanent-archive-tickets-unit'] ) ? sanitize_text_field( wp_unslash( $_POST['permanent-archive-tickets-unit'] ) ) : 'days',
					'permanent-delete-tickets-time'  => isset( $_POST['permanent-delete-tickets-time'] ) ? intval( $_POST['permanent-delete-tickets-time'] ) : '0',
					'permanent-delete-tickets-unit'  => isset( $_POST['permanent-delete-tickets-unit'] ) ? sanitize_text_field( wp_unslash( $_POST['permanent-delete-tickets-unit'] ) ) : 'days',
					'allow-bcc'                      => isset( $_POST['allow-bcc'] ) ? intval( $_POST['allow-bcc'] ) : 0,
					'allow-cc'                       => isset( $_POST['allow-cc'] ) ? intval( $_POST['allow-cc'] ) : 0,
					'view-more'                      => isset( $_POST['view-more'] ) ? intval( $_POST['view-more'] ) : 1,
					'allow-reply-to-close-ticket'    => isset( $_POST['allow-reply-to-close-ticket'] ) ? array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['allow-reply-to-close-ticket'] ) ) ) : array(),
					'raised-by-user'                 => isset( $_POST['raised-by-user'] ) ? sanitize_text_field( wp_unslash( $_POST['raised-by-user'] ) ) : 'customer',
					'allow-my-profile'               => isset( $_POST['allow-my-profile'] ) ? intval( $_POST['allow-my-profile'] ) : 0,
					'allow-agent-profile'            => isset( $_POST['allow-agent-profile'] ) ? intval( $_POST['allow-agent-profile'] ) : 0,
					'ticket-url-auth'                => isset( $_POST['ticket-url-auth'] ) ? intval( $_POST['ticket-url-auth'] ) : 0,
					'rest-api'                       => isset( $_POST['rest-api'] ) ? intval( $_POST['rest-api'] ) : 0,
					'agent-collision'                => isset( $_POST['agent-collision'] ) ? intval( $_POST['agent-collision'] ) : 0,
					'self-assign'                    => isset( $_POST['self-assign'] ) ? intval( $_POST['self-assign'] ) : 0,
					'reply-close'                    => isset( $_POST['reply-close'] ) ? intval( $_POST['reply-close'] ) : 0,
				)
			);
			update_option( 'wpsc-ms-advanced-settings', $advanced );

			$new_id = isset( $_POST['starting-ticket-id'] ) ? intval( $_POST['starting-ticket-id'] ) : 1;
			$old_id = $wpdb->get_var( 'SELECT MAX(id) FROM  ' . $wpdb->prefix . 'psmsc_tickets' );

			if ( $new_id > $old_id ) {
				$wpdb->query( 'ALTER TABLE ' . $wpdb->prefix . "psmsc_tickets AUTO_INCREMENT = $new_id" );
			}

			wp_die();
		}

		/**
		 * Reset settings to default
		 *
		 * @return void
		 */
		public static function reset_settings() {

			if ( check_ajax_referer( 'wpsc_reset_ms_advanced', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}
			self::reset();
			wp_die();
		}

		/**
		 * Print ticket form field
		 *
		 * @return void
		 */
		public static function print_tff() {

			$current_user = WPSC_Current_User::$current_user;
			$advanced     = get_option( 'wpsc-ms-advanced-settings' );
			if ( $current_user->is_agent && $advanced['do-not-notify-owner'] ) {
				?>
				<div class="wpsc-tff do-not-notify wpsc-xs-12 wpsc-sm-12 wpsc-md-12 wpsc-lg-12 required wpsc-visible" data-cft="do-not-notify">
					<div class="checkbox-container">
						<?php
						$unique_id = uniqid( 'wpsc_' );
						$status    = $advanced['do-not-notify-owner-status'] ? 'checked' : '';
						?>
						<input id="<?php echo esc_attr( $unique_id ); ?>" type="checkbox" class="notify_owner" name="notify_owner" <?php echo esc_attr( $status ); ?> value="1"/>
						<label for="<?php echo esc_attr( $unique_id ); ?>"><?php esc_attr_e( 'Do not notify owner', 'supportcandy' ); ?></label>
					</div>
				</div>
				<?php
			}
		}
	}
endif;

WPSC_MS_Advanced::init();
