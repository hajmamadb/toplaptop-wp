<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EN_Settings_TN' ) ) :

	final class WPSC_EN_Settings_TN {

		/**
		 * Ticket events
		 *
		 * @var array
		 */
		public static $events = array();

		/**
		 * Ignore current user recipient from these events
		 *
		 * @var array
		 */
		public static $ignore_current_user = array();

		/**
		 * Initialize this class
		 */
		public static function init() {

			// ignore cft.
			add_action( 'init', array( __CLASS__, 'load_ignore_list' ) );

			// list.
			add_action( 'wp_ajax_wpsc_get_ticket_notifications', array( __CLASS__, 'get_ticket_notifications' ) );

			// add new.
			add_action( 'wp_ajax_wpsc_en_get_add_ticket_notification', array( __CLASS__, 'get_add_ticket_notification' ) );
			add_action( 'wp_ajax_wpsc_en_set_add_ticket_notification', array( __CLASS__, 'set_add_ticket_notification' ) );

			// edit.
			add_action( 'wp_ajax_wpsc_en_get_edit_ticket_notification', array( __CLASS__, 'get_edit_ticket_notification' ) );
			add_action( 'wp_ajax_wpsc_en_set_edit_ticket_notification', array( __CLASS__, 'set_edit_ticket_notification' ) );

			// preview and test send.
			add_action( 'wp_ajax_wpsc_en_preview_ticket_notification', array( __CLASS__, 'preview_ticket_notification' ) );
			add_action( 'wp_ajax_wpsc_en_send_test_ticket_notification', array( __CLASS__, 'send_test_ticket_notification' ) );

			// delete.
			add_action( 'wp_ajax_wpsc_en_delete_ticket_notification', array( __CLASS__, 'delete_ticket_notification' ) );

			// clone.
			add_action( 'wp_ajax_wpsc_en_get_clone_ticket_notification', array( __CLASS__, 'get_clone_ticket_notification' ) );
			add_action( 'wp_ajax_wpsc_en_set_clone_ticket_notification', array( __CLASS__, 'set_clone_ticket_notification' ) );

			// enable or disable.
			add_action( 'wp_ajax_wpsc_en_enable_disable_template', array( __CLASS__, 'enable_disable_template' ) );

			// filter conditions.
			add_filter( 'wpsc_en_conditions', array( __CLASS__, 'filter_conditions' ) );

			// filter triggers.
			add_filter( 'wpsc_en_triggers', array( __CLASS__, 'filter_triggers' ) );
		}

		/**
		 * Set ignore custom field types for email notifications
		 *
		 * @return void
		 */
		public static function load_ignore_list() {

			self::$ignore_current_user = apply_filters(
				'wpsc_en_ignore_current_user_recipient_events',
				array( 'create-ticket' )
			);
		}

		/**
		 * Ticket notification list
		 *
		 * @return void
		 */
		public static function get_ticket_notifications() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$email_templates = get_option( 'wpsc-email-templates', array() );
			$unique_id       = uniqid( 'wpsc_' );?>
			<div class="wpsc-setting-header">
				<h2><?php esc_attr_e( 'Ticket Notifications', 'supportcandy' ); ?></h2>
				<?php WPSC_Functions::docs_button( 'https://supportcandy.net/docs/ticket-notifications/' ); ?>
			</div>
			<div class="wpsc-setting-section-body">
				<table class="ticket-notification-list wpsc-setting-tbl">
					<thead>
						<tr>
							<th><?php esc_attr_e( 'Title', 'supportcandy' ); ?></th>
							<th><?php esc_attr_e( 'Trigger', 'supportcandy' ); ?></th>
							<th><?php esc_attr_e( 'Status', 'supportcandy' ); ?></th>
							<th><?php esc_attr_e( 'Actions', 'supportcandy' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						if ( $email_templates ) :
							foreach ( $email_templates as $index => $et ) :
								if ( ! array_key_exists( $et['event'], WPSC_Triggers::$triggers ) ) {
									continue;
								}
								?>
								<tr>
									<td><span class="wpsc-link" onclick="wpsc_en_get_edit_ticket_notification(<?php echo esc_attr( $index ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_en_get_edit_ticket_notification' ) ); ?>')"><?php echo esc_attr( $et['title'] ); ?></span></td>
									<td><?php echo esc_attr( WPSC_Triggers::$triggers[ $et['event'] ] ); ?></td>
									<td><?php echo esc_attr( $et['is_enable'] ) == 1 ? esc_attr__( 'Enabled', 'supportcandy' ) : esc_attr__( 'Disabled', 'supportcandy' ); ?></td>
									<td>
										<?php
										if ( $et['is_enable'] ) {
											?>
											<span class="wpsc-link" onclick="wpsc_en_enable_disable_template(<?php echo esc_attr( $index ); ?>, '0', '<?php echo esc_attr( wp_create_nonce( 'wpsc_en_enable_disable_template' ) ); ?>')"><?php esc_attr_e( 'Disable', 'supportcandy' ); ?></span> |
											<?php
										} else {
											?>
											<span class="wpsc-link" onclick="wpsc_en_enable_disable_template(<?php echo esc_attr( $index ); ?>, '1', '<?php echo esc_attr( wp_create_nonce( 'wpsc_en_enable_disable_template' ) ); ?>')"><?php esc_attr_e( 'Enable', 'supportcandy' ); ?></span> |
											<?php
										}
										?>
										<span class="wpsc-link" onclick="wpsc_en_preview_ticket_notification('<?php echo esc_attr( wp_create_nonce( 'wpsc_en_preview_ticket_notification' ) ); ?>', <?php echo esc_attr( $index ); ?>)"><?php esc_attr_e( 'Preview', 'supportcandy' ); ?></span> |
										<span class="wpsc-link" onclick="wpsc_en_get_edit_ticket_notification(<?php echo esc_attr( $index ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_en_get_edit_ticket_notification' ) ); ?>')"><?php esc_attr_e( 'Edit', 'supportcandy' ); ?></span> |
										<span class="wpsc-link" onclick="wpsc_en_get_clone_ticket_notification(<?php echo esc_attr( $index ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_en_get_clone_ticket_notification' ) ); ?>')"><?php esc_attr_e( 'Clone', 'supportcandy' ); ?></span> |
										<span class="wpsc-link" onclick="wpsc_en_delete_ticket_notification(<?php echo esc_attr( $index ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_en_delete_ticket_notification' ) ); ?>')"><?php esc_attr_e( 'Delete', 'supportcandy' ); ?></span>
									</td>
								</tr>
								<?php
							endforeach;
						endif;
						?>
					</tbody>
				</table>
				<script>
					jQuery('table.ticket-notification-list').DataTable({
						ordering: false,
						pageLength: 20,
						bLengthChange: false,
						columnDefs: [ 
							{ targets: -1, searchable: false },
							{ targets: '_all', className: 'dt-left' }
						],
						layout: {
							topStart: {
								buttons: [
									{
										text: '<?php esc_attr_e( 'Add new', 'supportcandy' ); ?>',
										className: 'wpsc-button small primary',
										action: function ( e, dt, node, config ) {

											wpsc_show_modal();
											var data = { action: 'wpsc_en_get_add_ticket_notification' };
											jQuery.post(
												supportcandy.ajax_url,
												data,
												function (response) {

													jQuery( '.wpsc-modal-header' ).text( response.title );
													jQuery( '.wpsc-modal-body' ).html( response.body );
													jQuery( '.wpsc-modal-footer' ).html( response.footer );

													wpsc_show_modal_inner_container();
												}
											);
										}
									}
								],
							},
						},
						language: supportcandy.translations.datatables
					});
				</script>
			</div>
			<?php
			wp_die();
		}

		/**
		 * Get add ticket notification modal
		 *
		 * @return void
		 */
		public static function get_add_ticket_notification() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$title = esc_attr__( 'Add new', 'supportcandy' );
			$roles = get_option( 'wpsc-agent-roles', array() );
			ob_start();
			?>
			<form action="#" onsubmit="return false;" class="wpsc-frm-add-en">
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php esc_attr_e( 'Title', 'supportcandy' ); ?>
							<span class="required-char">*</span>
						</label>
					</div>
					<p class="wpsc-input-desc"><?php esc_attr_e( 'Name of this notification, shown only in the list.', 'supportcandy' ); ?></p>
					<input id="title" type="text" name="title" autocomplete="off">
				</div>
				<?php WPSC_Triggers::print( 'event', 'wpsc_en_triggers', '', true, '', false, __( 'Event that sends this email.', 'supportcandy' ) ); ?>
				<?php do_action( 'wpsc_get_add_ticket_notification' ); ?>
				<input type="hidden" name="action" value="wpsc_en_set_add_ticket_notification">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_en_set_add_ticket_notification' ) ); ?>">
			</form>
			<?php
			$body = ob_get_clean();

			ob_start();
			?>
			<button class="wpsc-button small primary" onclick="wpsc_en_set_add_ticket_notification(this);">
				<?php esc_attr_e( 'Submit', 'supportcandy' ); ?>
			</button>
			<button class="wpsc-button small secondary" onclick="wpsc_close_modal();">
				<?php esc_attr_e( 'Cancel', 'supportcandy' ); ?>
			</button>
			<?php
			do_action( 'wpsc_get_edit_agent_footer' );
			$footer = ob_get_clean();

			$response = array(
				'title'  => $title,
				'body'   => $body,
				'footer' => $footer,
			);
			wp_send_json( $response );
		}

		/**
		 * Set add ticket notification
		 *
		 * @return void
		 */
		public static function set_add_ticket_notification() {

			if ( check_ajax_referer( 'wpsc_en_set_add_ticket_notification', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
			if ( ! $title ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			$event = isset( $_POST['event'] ) ? sanitize_text_field( wp_unslash( $_POST['event'] ) ) : '';
			if ( ! $event || ! WPSC_Triggers::is_valid( 'wpsc_en_triggers', $event ) ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			$et = array(
				'is_enable'  => 1,
				'title'      => $title,
				'event'      => $event,
				'conditions' => '',
				'subject'    => '',
				'body'       => array(
					'text'   => '',
					'editor' => 'html',
				),
				'to'         => array(
					'general-recipients' => array(),
					'agent-roles'        => array(),
					'custom'             => array(),
				),
				'cc'         => array(
					'general-recipients' => array(),
					'agent-roles'        => array(),
					'custom'             => array(),
				),
				'bcc'        => array(
					'general-recipients' => array(),
					'agent-roles'        => array(),
					'custom'             => array(),
				),
			);

			$email_templates = get_option( 'wpsc-email-templates', array() );
			if ( count( $email_templates ) == 0 ) {

				$email_templates[1] = $et;

			} else {

				$email_templates[] = $et;
			}
			update_option( 'wpsc-email-templates', $email_templates );

			end( $email_templates );
			$index = key( $email_templates );

			$nonce = wp_create_nonce( 'wpsc_en_get_edit_ticket_notification' );
			wp_send_json(
				array(
					'index' => $index,
					'nonce' => $nonce,
				)
			);
			wp_die();
		}

		/**
		 * Get edit email notification
		 *
		 * @return void
		 */
		public static function get_edit_ticket_notification() {

			if ( check_ajax_referer( 'wpsc_en_get_edit_ticket_notification', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$template_id = isset( $_POST['template_id'] ) ? intval( $_POST['template_id'] ) : 0;
			if ( ! $template_id ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			$email_templates = get_option( 'wpsc-email-templates' );
			$et              = array_key_exists( $template_id, $email_templates ) ? $email_templates[ $template_id ] : null;
			if ( ! $et ) {
				wp_send_json_error( 'Bad request', 400 );
			}
			?>

			<div style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
				<button 
					class="wpsc-button small secondary"
					onclick="wpsc_en_preview_ticket_notification('<?php echo esc_attr( wp_create_nonce( 'wpsc_en_preview_ticket_notification' ) ); ?>');">
					<?php esc_attr_e( 'Preview', 'supportcandy' ); ?>
				</button>
			</div>

			<form action="#" onsubmit="return false;" class="wpsc-frm-edit-en">

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php esc_attr_e( 'Title', 'supportcandy' ); ?>
							<span class="required-char">*</span>
						</label>
					</div>
					<p class="wpsc-input-desc"><?php esc_attr_e( 'Name of this notification, shown only in the list.', 'supportcandy' ); ?></p>
					<input type="text" name="title" value="<?php echo esc_attr( $et['title'] ); ?>" autocomplete="off">
				</div>

				<?php WPSC_Triggers::print( 'event', 'wpsc_en_triggers', $et['event'], true, '', true, __( 'Event that sends this email. It cannot be changed after the notification is created.', 'supportcandy' ) ); ?>

				<?php WPSC_Ticket_Conditions::print( 'conditions', 'wpsc_en_conditions', $et['conditions'], false, '', true, __( 'Send only when the ticket matches these conditions. Leave empty to send every time.', 'supportcandy' ) ); ?>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php esc_attr_e( 'Subject', 'supportcandy' ); ?>
							<span class="required-char">*</span>
						</label>
					</div>
					<p class="wpsc-input-desc"><?php esc_attr_e( 'Email subject. Macros like {{ticket_id}} work here; see Insert Macro below the body.', 'supportcandy' ); ?></p>
					<?php
					$en_tn_subject = $et['subject'] ? WPSC_Translations::get( 'wpsc-en-tn-subject-' . $template_id, stripslashes( $et['subject'] ) ) : stripslashes( $et['subject'] );
					?>
					<input type="text" name="subject" value="<?php echo esc_attr( $en_tn_subject ); ?>" autocomplete="off">
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php esc_attr_e( 'Body', 'supportcandy' ); ?>
							<span class="required-char">*</span>
						</label>
					</div>
					<p class="wpsc-input-desc"><?php esc_attr_e( 'Email content. Use Insert Macro to add ticket details.', 'supportcandy' ); ?></p>
					<?php
					$en_tn_body = $et['body']['text'] ? WPSC_Translations::get( 'wpsc-en-tn-body-' . $template_id, stripslashes( $et['body']['text'] ) ) : stripslashes( $et['body']['text'] );

					// visual editor removes html, head, body and style tags. open such templates in text editor.
					$is_full_html = preg_match( '/<(html|head|body|style)[\s>]/i', $en_tn_body );
					$editor       = $is_full_html ? 'text' : $et['body']['editor'];
					?>
					<div class="textarea-container ">
						<div class = "wpsc_tinymce_editor_btns">
							<div class="inner-container">
								<button class="visual wpsc-switch-editor <?php echo $editor == 'html' ? 'active' : ''; ?>" type="button" onclick="wpsc_get_tinymce(this, 'wpsc-en-body','wpsc_en_body');"><?php esc_attr_e( 'Visual', 'supportcandy' ); ?></button>
								<button class="text wpsc-switch-editor <?php echo $editor == 'text' ? 'active' : ''; ?>" type="button" onclick="wpsc_get_textarea(this, 'wpsc-en-body')"><?php esc_attr_e( 'Text', 'supportcandy' ); ?></button>
							</div>
						</div>
						<textarea id="wpsc-en-body" name="body" class="wpsc_textarea"><?php echo esc_attr( $en_tn_body ); ?></textarea>
						<?php
						if ( $is_full_html ) :
							?>
							<div class="wpsc-setting-warning">
								<?php esc_attr_e( 'This template contains full HTML (html, head, body or style tags), so it is opened in the Text editor. The Visual editor would remove these tags and break the design.', 'supportcandy' ); ?>
							</div>
							<?php
						endif;
						?>
						<div class="wpsc-it-editor-action-container">
							<div class="actions">
								<div class="wpsc-editor-actions">
									<span class="wpsc-link" onclick="wpsc_get_macros()"><?php esc_attr_e( 'Insert Macro', 'supportcandy' ); ?></span>
								</div>
							</div>
						</div>
					</div>
					<script>
						<?php
						if ( $editor == 'html' ) :
							?>
							jQuery('.wpsc-switch-editor.visual').trigger('click');
							<?php
						endif;
						?>

						function wpsc_get_tinymce(el, selector, body_id){
							if (
								/<(html|head|body|style)[\s>]/i.test(jQuery('#'+selector).val()) &&
								!confirm('<?php echo esc_js( __( 'The Visual editor will remove html, head, body and style tags from this template and may break its design. Continue?', 'supportcandy' ) ); ?>')
							) {
								return;
							}
							jQuery(el).parent().find('.text').removeClass('active');
							jQuery(el).addClass('active');
							tinymce.remove('#'+selector);
							tinymce.init({ 
								selector:'#'+selector,
								body_id: body_id,
								menubar: false,
								height : '200',
								plugins: [
								'lists link image directionality'
								],
								image_advtab: true,
								toolbar: 'bold italic underline blockquote | alignleft aligncenter alignright | bullist numlist | rtl | link image',
								directionality: '<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>',
								branding: false,
								autoresize_bottom_margin: 20,
								browser_spellcheck : true,
								relative_urls : false,
								remove_script_host : false,
								convert_urls : false,
								setup: function (editor) {
								}
							});
							jQuery('#editor').val('html');
						}

						function wpsc_get_textarea(el, selector){
							jQuery(el).parent().find('.visual').removeClass('active');
							jQuery(el).addClass('active');
							tinymce.remove('#'+selector);
							jQuery('#editor').val('text');
						}
					</script>
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php esc_attr_e( 'To address', 'supportcandy' ); ?>
							<span class="required-char">*</span>
						</label>
					</div>
					<p class="wpsc-input-desc"><?php esc_attr_e( 'Main recipients of the email. Everyone can see these addresses.', 'supportcandy' ); ?></p>
					<?php echo esc_attr( self::print_recipient_options( $et, 'to' ) ); ?>
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">CC</label>
					</div>
					<p class="wpsc-input-desc"><?php esc_attr_e( 'Copy of the email for people who should stay informed. All recipients can see CC addresses.', 'supportcandy' ); ?></p>
					<?php echo esc_attr( self::print_recipient_options( $et, 'cc' ) ); ?>
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">BCC</label>
					</div>
					<p class="wpsc-input-desc"><?php esc_attr_e( 'Hidden copy of the email. Other recipients cannot see BCC addresses.', 'supportcandy' ); ?></p>
					<?php echo esc_attr( self::print_recipient_options( $et, 'bcc' ) ); ?>
				</div>

				<?php do_action( 'wpsc_get_edit_ticket_notification', $et ); ?>
				<input type="hidden" name="action" value="wpsc_en_set_edit_ticket_notification">
				<input type="hidden" name="template_id" value="<?php echo esc_attr( $template_id ); ?>">
				<input type="hidden" id="editor" name="editor" value="<?php echo esc_attr( $editor ); ?>">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_en_set_edit_ticket_notification' ) ); ?>">

			</form>

			<div class="setting-footer-actions">
				<button 
					class="wpsc-button normal primary"
					onclick="wpsc_en_set_edit_ticket_notification();">
					<?php esc_attr_e( 'Submit', 'supportcandy' ); ?>
				</button>
				<button 
					class="wpsc-button normal secondary"
					onclick="wpsc_en_preview_ticket_notification('<?php echo esc_attr( wp_create_nonce( 'wpsc_en_preview_ticket_notification' ) ); ?>');">
					<?php esc_attr_e( 'Preview', 'supportcandy' ); ?>
				</button>
				<button 
					class="wpsc-button normal secondary"
					onclick="wpsc_get_ticket_notifications();">
					<?php esc_attr_e( 'Cancel', 'supportcandy' ); ?>
				</button>
			</div>
			<?php

			wp_die();
		}

		/**
		 * Set edit ticket notification
		 */
		public static function set_edit_ticket_notification() {

			if ( check_ajax_referer( 'wpsc_en_set_edit_ticket_notification', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			// template id.
			$template_id = isset( $_POST['template_id'] ) ? intval( $_POST['template_id'] ) : 0;
			if ( ! $template_id ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			// template.
			$email_templates = get_option( 'wpsc-email-templates' );
			$et              = array_key_exists( $template_id, $email_templates ) ? $email_templates[ $template_id ] : null;
			if ( ! $et ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			// title.
			$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
			if ( ! $title ) {
				wp_send_json_error( 'Bad request', 400 );
			}
			$et['title'] = $title;

			// conditions.
			$conditions = isset( $_POST['conditions'] ) ? sanitize_text_field( wp_unslash( $_POST['conditions'] ) ) : '';
			if ( ! WPSC_Ticket_Conditions::is_valid_input_conditions( 'wpsc_en_conditions', $conditions ) ) {
				wp_send_json_error( 'Bad request', 400 );
			}
			$et['conditions'] = $conditions;

			// subject.
			$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
			if ( ! $subject ) {
				wp_send_json_error( 'Bad request', 400 );
			}
			$et['subject'] = $subject;

			// body.
			$body = isset( $_POST['body'] ) ? wp_kses_post( wp_unslash( $_POST['body'] ) ) : '';
			if ( ! $body ) {
				wp_send_json_error( 'Bad request', 400 );
			}
			$et['body'] = array(
				'text'   => $body,
				'editor' => isset( $_POST['editor'] ) ? sanitize_text_field( wp_unslash( $_POST['editor'] ) ) : 'html',
			);

			// to.
			$general_recipients = isset( $_POST['to'] ) && isset( $_POST['to']['general-recipients'] ) ? array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['to']['general-recipients'] ) ) ) : array();
			$agent_roles = isset( $_POST['to'] ) && isset( $_POST['to']['agent-roles'] ) ? array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['to']['agent-roles'] ) ) ) : array();
			$custom = isset( $_POST['to'] ) && isset( $_POST['to']['custom'] ) ? sanitize_textarea_field( wp_unslash( $_POST['to']['custom'] ) ) : '';
			$custom = array_unique( array_filter( array_map( 'sanitize_email', explode( PHP_EOL, $custom ) ) ) );
			if ( ! ( $general_recipients || $agent_roles || $custom ) ) {
				wp_send_json_error( 'Bad request', 400 );
			}
			$et['to'] = array(
				'general-recipients' => $general_recipients,
				'agent-roles'        => $agent_roles,
				'custom'             => $custom,
			);

			// cc.
			$general_recipients = isset( $_POST['cc'] ) && isset( $_POST['cc']['general-recipients'] ) ? array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['cc']['general-recipients'] ) ) ) : array();
			$agent_roles = isset( $_POST['cc'] ) && isset( $_POST['cc']['agent-roles'] ) ? array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['cc']['agent-roles'] ) ) ) : array();
			$custom = isset( $_POST['cc'] ) && isset( $_POST['cc']['custom'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cc']['custom'] ) ) : '';
			$custom = array_unique( array_filter( array_map( 'sanitize_email', explode( PHP_EOL, $custom ) ) ) );
			$et['cc'] = array(
				'general-recipients' => $general_recipients,
				'agent-roles'        => $agent_roles,
				'custom'             => $custom,
			);

			// bcc.
			$general_recipients = isset( $_POST['bcc'] ) && isset( $_POST['bcc']['general-recipients'] ) ? array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['bcc']['general-recipients'] ) ) ) : array();
			$agent_roles = isset( $_POST['bcc'] ) && isset( $_POST['bcc']['agent-roles'] ) ? array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['bcc']['agent-roles'] ) ) ) : array();
			$custom = isset( $_POST['bcc'] ) && isset( $_POST['bcc']['custom'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bcc']['custom'] ) ) : '';
			$custom = array_unique( array_filter( array_map( 'sanitize_email', explode( PHP_EOL, $custom ) ) ) );
			$et['bcc'] = array(
				'general-recipients' => $general_recipients,
				'agent-roles'        => $agent_roles,
				'custom'             => $custom,
			);

			$email_templates[ $template_id ] = apply_filters( 'wpsc_en_set_edit_et', $et );
			update_option( 'wpsc-email-templates', $email_templates );

			// remove string translations.
			WPSC_Translations::remove( 'wpsc-en-tn-subject-' . $template_id );
			WPSC_Translations::remove( 'wpsc-en-tn-body-' . $template_id );

			// add string translations.
			WPSC_Translations::add( 'wpsc-en-tn-subject-' . $template_id, stripslashes( $et['subject'] ) );
			WPSC_Translations::add( 'wpsc-en-tn-body-' . $template_id, stripslashes( $et['body']['text'] ) );

			wp_die();
		}

		/**
		 * Print preview button for an email template. See wpsc_en_preview_email() in admin.js for $args.
		 *
		 * @param array  $args - preview args.
		 * @param string $type - "button" or "link".
		 * @return void
		 */
		public static function print_preview_button( $args, $type = 'button' ) {

			$onclick = 'wpsc_en_preview_email(\'' . wp_create_nonce( 'wpsc_en_preview_ticket_notification' ) . '\', ' . wp_json_encode( $args ) . ');';
			if ( $type == 'link' ) {
				?>
				<a href="#" onclick="<?php echo esc_attr( $onclick ); ?> return false;"><?php esc_attr_e( 'Preview', 'supportcandy' ); ?></a>
				<?php
			} else {
				?>
				<button class="wpsc-button normal secondary margin-right" onclick="<?php echo esc_attr( $onclick ); ?>"><?php esc_attr_e( 'Preview', 'supportcandy' ); ?></button>
				<?php
			}
		}

		/**
		 * Render subject and body of an email with sample values. Used by preview and test send of all email templates.
		 *
		 * Request params:
		 * source + template_id - render a saved template. "ticket-notification" is handled here, other sources by "wpsc_en_preview_saved_template" filter.
		 * subject + body       - render unsaved subject and body from an edit screen.
		 * context              - "ticket" (default) replaces ticket macros, any other context replaces values given by "wpsc_en_preview_sample_values" filter.
		 * no_alias             - do not prefix ticket alias to subject (ticket context only).
		 *
		 * @return array - subject and body.
		 */
		private static function render_template_request() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$template_id = isset( $_POST['template_id'] ) ? intval( $_POST['template_id'] ) : 0; //phpcs:ignore
			$source      = isset( $_POST['source'] ) ? sanitize_key( wp_unslash( $_POST['source'] ) ) : ''; //phpcs:ignore
			$context     = isset( $_POST['context'] ) ? sanitize_key( wp_unslash( $_POST['context'] ) ) : 'ticket'; //phpcs:ignore
			$no_alias    = ! empty( $_POST['no_alias'] ); //phpcs:ignore

			if ( $template_id && ( ! $source || $source == 'ticket-notification' ) ) {
				$email_templates = get_option( 'wpsc-email-templates', array() );
				if ( ! isset( $email_templates[ $template_id ] ) ) {
					wp_send_json_error( 'Bad request', 400 );
				}
				$et      = $email_templates[ $template_id ];
				$subject = WPSC_Translations::get( 'wpsc-en-tn-subject-' . $template_id, stripslashes( $et['subject'] ) );
				$body    = WPSC_Translations::get( 'wpsc-en-tn-body-' . $template_id, stripslashes( $et['body']['text'] ) );
			} elseif ( $source ) {
				// saved template of an add-on. return array( 'subject' => '', 'body' => '' ).
				$template = apply_filters( 'wpsc_en_preview_saved_template', null, $source, $template_id );
				if ( ! is_array( $template ) ) {
					wp_send_json_error( 'Bad request', 400 );
				}
				$subject = $template['subject'];
				$body    = $template['body'];
			} else {
				$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : ''; //phpcs:ignore
				// not filtered so that full html templates (head, style) render as they will be emailed; preview shows it in a sandboxed iframe.
				$body = isset( $_POST['body'] ) ? wp_unslash( $_POST['body'] ) : ''; //phpcs:ignore
			}
			if ( ! $subject || ! $body ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			if ( $context != 'ticket' ) {
				$values = apply_filters( 'wpsc_en_preview_sample_values', array(), $context );
				return array(
					'subject' => str_replace( array_keys( $values ), array_values( $values ), $subject ),
					'body'    => str_replace( array_keys( $values ), array_values( $values ), $body ),
				);
			}

			return array(
				'subject' => ( $no_alias ? '' : '[' . WPSC_Macros::replace_sample( '{{alias}}' ) . '] ' ) . WPSC_Macros::replace_sample( $subject ),
				'body'    => WPSC_Macros::replace_sample( $body ),
			);
		}

		/**
		 * Preview ticket notification
		 *
		 * @return void
		 */
		public static function preview_ticket_notification() {

			if ( check_ajax_referer( 'wpsc_en_preview_ticket_notification', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			$rendered = self::render_template_request();

			ob_start();
			?>
			<div style="width: 100%;">
				<p><strong><?php esc_attr_e( 'Subject', 'supportcandy' ); ?>:</strong> <?php echo esc_html( $rendered['subject'] ); ?></p>
				<?php
				// sandboxed iframe: template css can not affect admin page and admin css can not affect the template. scripts are not allowed.
				?>
				<iframe
					class="wpsc-en-preview-frame"
					sandbox="allow-same-origin allow-popups allow-popups-to-escape-sandbox"
					srcdoc="<?php echo esc_attr( '<base target="_blank">' . $rendered['body'] ); ?>"
					style="width: 100%; height: 250px; border: 1px solid #ddd; background: #fff;"
					onload="this.style.height = (this.contentDocument.documentElement.scrollHeight + 5) + 'px';"></iframe>
			</div>
			<?php
			$body = ob_get_clean();

			ob_start();
			?>
			<input
				type="email"
				class="wpsc-en-test-email"
				value="<?php echo esc_attr( wp_get_current_user()->user_email ); ?>"
				placeholder="<?php esc_attr_e( 'Send test email to', 'supportcandy' ); ?>"
				title="<?php esc_attr_e( 'Send test email to', 'supportcandy' ); ?>"
				style="width: 220px; margin-inline-end: 5px;">
			<button class="wpsc-button small primary" onclick="wpsc_en_send_test_ticket_notification('<?php echo esc_attr( wp_create_nonce( 'wpsc_en_send_test_ticket_notification' ) ); ?>');"><?php esc_attr_e( 'Send test', 'supportcandy' ); ?></button>
			<button class="wpsc-button small secondary" onclick="wpsc_close_modal();"><?php esc_attr_e( 'Close', 'supportcandy' ); ?></button>
			<?php
			$footer = ob_get_clean();

			wp_send_json(
				array(
					'title'  => esc_attr__( 'Preview - (This preview uses sample values)', 'supportcandy' ),
					'body'   => $body,
					'footer' => $footer,
				)
			);
		}

		/**
		 * Send test email of the ticket notification to the given email address
		 *
		 * @return void
		 */
		public static function send_test_ticket_notification() {

			if ( check_ajax_referer( 'wpsc_en_send_test_ticket_notification', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			$rendered = self::render_template_request();
			$to       = isset( $_POST['to'] ) ? sanitize_email( wp_unslash( $_POST['to'] ) ) : '';
			if ( ! is_email( $to ) ) {
				wp_send_json_error( __( 'Please enter a valid email address.', 'supportcandy' ), 400 );
			}

			$en_general = get_option( 'wpsc-en-general' );
			$en         = new WPSC_Email_Notifications();
			$en->from_name  = $en_general['from-name'] ? $en_general['from-name'] : '';
			$en->from_email = $en_general['from-email'] ? $en_general['from-email'] : '';
			$en->reply_to   = $en_general['reply-to'] ? $en_general['reply-to'] : $en->from_email;
			$en->subject    = $rendered['subject'];
			$en->body       = $rendered['body'];
			$en->to         = array( $to );

			// capture the mailer error so that admin knows why it failed.
			$mail_error = '';
			add_action(
				'wp_mail_failed',
				function ( $error ) use ( &$mail_error ) {
					$mail_error = $error->get_error_message();
				}
			);

			if ( ! $en->send() ) {
				$message = __( 'Unable to send the test email. Check your email settings.', 'supportcandy' );
				wp_send_json_error( $mail_error ? $message . ' ' . $mail_error : $message, 500 );
			}
			wp_send_json_success( $to );
		}

		/**
		 * Print recipient options for "to", "cc" and "bcc"
		 *
		 * @param array  $et -email template.
		 * @param string $type - one of "to", "cc" and "bcc".
		 * @return void
		 */
		public static function print_recipient_options( $et, $type ) {

			// general recipients.
			$gereral_recipients = array(
				'customer' => esc_attr__( 'Customer', 'supportcandy' ),
				'assignee' => esc_attr__( 'Assignee', 'supportcandy' ),
			);

			if ( $et['event'] == 'change-assignee' ) {
				$gereral_recipients['prev-assignee'] = esc_attr__( 'Previous Assignee', 'supportcandy' );
			}

			if ( ! in_array( $et['event'], self::$ignore_current_user ) ) {
				$gereral_recipients['current-user'] = esc_attr__( 'Current User', 'supportcandy' );
			}

			$gereral_recipients['add-recipients'] = esc_attr__( 'Additional Recipients', 'supportcandy' );
			$gereral_recipients                   = apply_filters( 'wpsc_en_general_recipients', $gereral_recipients );

			// agent roles.
			$agent_roles = get_option( 'wpsc-agent-roles' );
			$unique_id   = uniqid( 'wpsc_' );

			$etgr = $et[ $type ]['general-recipients'];
			$etar = $et[ $type ]['agent-roles'];
			$etc  = implode( PHP_EOL, $et[ $type ]['custom'] );
			?>

			<div style="border: none;box-shadow: 0 0 3px 0 #a4b0be;border-radius: 5px;padding: 15px 15px 0 15px; background-color:#eee;">
				<div class="wpsc-input-group">
					<div class="label-container"><label for=""><?php esc_attr_e( 'General Recipients', 'supportcandy' ); ?></label></div>
					<p class="wpsc-input-desc"><?php esc_attr_e( 'People linked to the ticket, such as the customer or assignee.', 'supportcandy' ); ?></p>
					<select class="<?php echo esc_attr( $unique_id ); ?>" name="<?php echo esc_attr( $type ); ?>[general-recipients][]" multiple>
						<?php
						foreach ( $gereral_recipients as $key => $label ) {
							$selected = in_array( $key, $etgr ) ? 'selected' : ''
							?>
							<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $label ); ?></option>
							<?php
						}
						?>
					</select>
				</div>
				<div class="wpsc-input-group">
					<div class="label-container"><label for=""><?php esc_attr_e( 'Agent Roles', 'supportcandy' ); ?></label></div>
					<p class="wpsc-input-desc"><?php esc_attr_e( 'All active agents with the selected roles.', 'supportcandy' ); ?></p>
					<select class="<?php echo esc_attr( $unique_id ); ?>" name="<?php echo esc_attr( $type ); ?>[agent-roles][]" multiple>
						<?php
						foreach ( $agent_roles as $key => $role ) {
							$selected = in_array( $key, $etar ) ? 'selected' : ''
							?>
							<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $role['label'] ); ?></option>
							<?php
						}
						?>
					</select>
				</div>
				<div class="wpsc-input-group">
					<div class="label-container"><label for=""><?php esc_attr_e( 'Custom Email Addresses (one per line)', 'supportcandy' ); ?></label></div>
					<p class="wpsc-input-desc"><?php esc_attr_e( 'Any other email addresses.', 'supportcandy' ); ?></p>
					<textarea name="<?php echo esc_attr( $type ); ?>[custom]"><?php echo esc_attr( $etc ); ?></textarea>
				</div>
			</div>
			<script type="text/javascript">
				jQuery('.<?php echo esc_attr( $unique_id ); ?>').selectWoo({
					allowClear: true,
					placeholder: "<?php esc_attr_e( 'Click to select options', 'supportcandy' ); ?>"
				});
			</script>
			<?php
		}

		/**
		 * Delete email notification
		 *
		 * @return void
		 */
		public static function delete_ticket_notification() {

			if ( check_ajax_referer( 'wpsc_en_delete_ticket_notification', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			// template id.
			$template_id = isset( $_POST['template_id'] ) ? intval( $_POST['template_id'] ) : 0;
			if ( ! $template_id ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			// template.
			$email_templates = get_option( 'wpsc-email-templates' );
			$et              = array_key_exists( $template_id, $email_templates ) ? $email_templates[ $template_id ] : null;
			if ( ! $et ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			unset( $email_templates[ $template_id ] );
			update_option( 'wpsc-email-templates', $email_templates );

			// remove string translations.
			WPSC_Translations::remove( 'wpsc-en-tn-body-' . $template_id );
			WPSC_Translations::remove( 'wpsc-en-tn-subject-' . $template_id );

			wp_die();
		}

		/**
		 * Enable or Disable email notification
		 *
		 * @return void
		 */
		public static function enable_disable_template() {

			if ( check_ajax_referer( 'wpsc_en_enable_disable_template', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			// template id.
			$template_id = isset( $_POST['template_id'] ) ? intval( $_POST['template_id'] ) : 0;
			if ( ! $template_id ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			// status.
			$status = isset( $_POST['status'] ) ? intval( $_POST['status'] ) : 0;

			$email_templates = get_option( 'wpsc-email-templates' );
			$et = array_key_exists( $template_id, $email_templates ) ? $email_templates[ $template_id ] : null;
			if ( ! $et ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			$et['is_enable'] = $status;
			$email_templates[ $template_id ] = apply_filters( 'wpsc_en_enable_disable_et', $et );
			update_option( 'wpsc-email-templates', $email_templates );

			wp_die();
		}

		/**
		 * Filter conditions for email templates
		 *
		 * @param array $conditions - all possible ticket conditions.
		 * @return array
		 */
		public static function filter_conditions( $conditions ) {

			$ignore_list = apply_filters(
				'wpsc_en_conditions_ignore_list',
				array(
					'cft'   => array(
						'df_last_reply_by',
						'df_last_reply_on',
						'cf_woo_order',
						'cf_woo_subscription',
						'cf_edd_order',
						'cf_tutor_order',
						'cf_learnpress_order',
						'cf_lifter_order',
					), // custom field types.
					'other' => array(), // other(custom) condition slug.
				)
			);

			foreach ( $conditions as $slug => $item ) {

				if ( $item['type'] == 'cf' ) {

					$cf = WPSC_Custom_Field::get_cf_by_slug( $slug );
					if ( in_array( $cf->type::$slug, $ignore_list['cft'] ) ) {
						unset( $conditions[ $slug ] );
					}
				} elseif ( in_array( $slug, $ignore_list['other'] ) ) {
						unset( $conditions[ $slug ] );
				}
			}

			return $conditions;
		}

		/**
		 * Filter triggers for email notification templates
		 *
		 * @param array $triggers - all possible triggers.
		 * @return array
		 */
		public static function filter_triggers( $triggers ) {

			$ignore_list = apply_filters( 'wpsc_en_triggers_ignore_list', array() );
			foreach ( $triggers as $key => $val ) {
				if ( in_array( $key, $ignore_list ) ) {
					unset( $triggers[ $key ] );
				}
			}
			return $triggers;
		}

		/**
		 * Get add clone ticket notification modal
		 *
		 * @return void
		 */
		public static function get_clone_ticket_notification() {

			if ( check_ajax_referer( 'wpsc_en_get_clone_ticket_notification', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			// template id.
			$template_id = isset( $_POST['template_id'] ) ? intval( $_POST['template_id'] ) : 0;
			if ( ! $template_id ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			$email_templates = get_option( 'wpsc-email-templates' );
			$et              = array_key_exists( $template_id, $email_templates ) ? $email_templates[ $template_id ] : null;
			if ( ! $et ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			$title = esc_attr__( 'Clone notification', 'supportcandy' );
			ob_start();
			?>
			<form action="#" onsubmit="return false;" class="wpsc-en-add-clone">
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php esc_attr_e( 'Title', 'supportcandy' ); ?>
							<span class="required-char">*</span>
						</label>
					</div>
					<p class="wpsc-input-desc"><?php esc_attr_e( 'Name of this notification, shown only in the list.', 'supportcandy' ); ?></p>
					<input type="text" name="title" value="<?php echo esc_attr( $et['title'] ); ?>" autocomplete="off">
				</div>
				<?php WPSC_Triggers::print( 'event', 'wpsc_en_triggers', $et['event'], true, '', false, __( 'Event that sends this email.', 'supportcandy' ) ); ?>
				<?php do_action( 'wpsc_get_clone_ticket_notification' ); ?>
				<input type="hidden" name="template_id" value="<?php echo esc_attr( $template_id ); ?>">
				<input type="hidden" name="action" value="wpsc_en_set_clone_ticket_notification">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_en_set_clone_ticket_notification' ) ); ?>">
			</form>
			<?php
			$body = ob_get_clean();

			ob_start();
			?>
			<button class="wpsc-button small primary" onclick="wpsc_en_set_clone_ticket_notification(this);">
				<?php esc_attr_e( 'Submit', 'supportcandy' ); ?>
			</button>
			<button class="wpsc-button small secondary" onclick="wpsc_close_modal();">
				<?php esc_attr_e( 'Cancel', 'supportcandy' ); ?>
			</button>
			<?php
			$footer = ob_get_clean();

			$response = array(
				'title'  => $title,
				'body'   => $body,
				'footer' => $footer,
			);
			wp_send_json( $response );
		}

		/**
		 * Set clone ticket notification
		 *
		 * @return void
		 */
		public static function set_clone_ticket_notification() {

			if ( check_ajax_referer( 'wpsc_en_set_clone_ticket_notification', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			// template id.
			$template_id = isset( $_POST['template_id'] ) ? intval( $_POST['template_id'] ) : 0;
			if ( ! $template_id ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			// template.
			$email_templates = get_option( 'wpsc-email-templates' );
			$et              = array_key_exists( $template_id, $email_templates ) ? $email_templates[ $template_id ] : null;
			if ( ! $et ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( ( $_POST['title'] ) ) ) : '';
			if ( ! $title ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$event = isset( $_POST['event'] ) ? sanitize_text_field( wp_unslash( ( $_POST['event'] ) ) ) : '';
			if ( ! $event ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$clone = array(
				'title'      => $title,
				'event'      => $event,
				'is_enable'  => 0,
				'subject'    => $et['subject'],
				'body'       => array(
					'text'   => $et['body']['text'],
					'editor' => $et['body']['editor'],
				),
				'to'         => array(
					'general-recipients' => $et['to']['general-recipients'],
					'agent-roles'        => $et['to']['agent-roles'],
					'custom'             => $et['to']['custom'],
				),
				'cc'         => array(
					'general-recipients' => $et['cc']['general-recipients'],
					'agent-roles'        => $et['cc']['agent-roles'],
					'custom'             => $et['cc']['custom'],
				),
				'bcc'        => array(
					'general-recipients' => $et['bcc']['general-recipients'],
					'agent-roles'        => $et['bcc']['agent-roles'],
					'custom'             => $et['bcc']['custom'],
				),
				'conditions' => $et['conditions'],
			);

			$clone = apply_filters( 'wpsc_en_clone_ticket_notification', $clone, $et );

			$email_templates[] = $clone;

			WPSC_Translations::add( 'wpsc-en-tn-subject-' . array_key_last( $email_templates ), stripslashes( $et['subject'] ) );
			WPSC_Translations::add( 'wpsc-en-tn-body-' . array_key_last( $email_templates ), stripslashes( $et['body']['text'] ) );

			update_option( 'wpsc-email-templates', $email_templates );

			end( $email_templates );
			$index = key( $email_templates );

			$nonce = wp_create_nonce( 'wpsc_en_get_edit_ticket_notification' );
			wp_send_json(
				array(
					'index' => $index,
					'nonce' => $nonce,
				)
			);
		}
	}
endif;

WPSC_EN_Settings_TN::init();
