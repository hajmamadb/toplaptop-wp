<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_ITW_Ticket_Tags' ) ) :

	final class WPSC_ITW_Ticket_Tags {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// get edit ticket tags.
			add_action( 'wp_ajax_wpsc_it_set_edit_ticket_tags', array( __CLASS__, 'update_edit_ticket_tags' ) );
			add_action( 'wp_ajax_nopriv_wpsc_it_set_edit_ticket_tags', array( __CLASS__, 'update_edit_ticket_tags' ) );

			// Ticket tags.
			add_action( 'wp_ajax_wpsc_get_tw_ticket_tags', array( __CLASS__, 'get_tw_ticket_tags' ) );
			add_action( 'wp_ajax_wpsc_set_tw_ticket_tags', array( __CLASS__, 'set_tw_ticket_tags' ) );

			// tags autocomplete.
			add_action( 'wp_ajax_wpsc_tag_autocomplete', array( __CLASS__, 'tag_autocomplete' ) );
			add_action( 'wp_ajax_nopriv_wpsc_tag_autocomplete', array( __CLASS__, 'tag_autocomplete' ) );

			// refresh tags list.
			add_action( 'wp_ajax_wpsc_it_refresh_tags', array( __CLASS__, 'refresh_tags_list' ) );
			add_action( 'wp_ajax_nopriv_wpsc_it_refresh_tags', array( __CLASS__, 'refresh_tags_list' ) );

			// print timer script.
			add_action( 'wpsc_it_layout_section', array( __CLASS__, 'print_tags_script' ) );
		}

		/**
		 * Prints body of current widget
		 *
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @param array       $settings - widget settings.
		 * @return void
		 */
		public static function print_widget( $ticket, $settings ) {

			$current_user = WPSC_Current_User::$current_user;
			if ( ! (
				(
					(
						WPSC_Individual_Ticket::$view_profile == 'customer' ||
						$ticket->customer->id == $current_user->customer->id
					) &&
					$settings['allow-customer']
				) ||
				( WPSC_Individual_Ticket::$view_profile == 'agent' && in_array( $current_user->agent->role, $settings['allowed-agent-roles'] ) )
			) ) {
				return;
			}

			$title = $settings['title']
					? WPSC_Translations::get( 'wpsc-twt-tags', stripslashes( $settings['title'] ) )
					: stripslashes( $settings['title'] );

			$show_edit = (
				$ticket->is_active &&
				WPSC_Individual_Ticket::$view_profile == 'agent' &&
				WPSC_Individual_Ticket::has_ticket_cap( 'tt' ) &&
				! WPSC_Individual_Ticket::$is_restricted
			);
			?>
			<div class="wpsc-it-widget wpsc-itw-ticket-tags">
				<div class="wpsc-widget-header">
					<h2><?php echo esc_attr( $title ); ?></h2>
					<?php
					if ( $show_edit ) :
						?>
						<span class="wpsc-add-tag-trigger" onclick="wpsc_tags_edit_from_header(this)"><?php WPSC_Icons::get( 'edit' ); ?></span>
						<?php
					endif;
					?>
					<span class="wpsc-itw-toggle" data-widget="wpsc-itw-ticket-tags"><?php WPSC_Icons::get( 'chevron-up' ); ?></span>
				</div>
				<div class="wpsc-widget-body">
					<div class="wpsc-it-tag-body"></div>
				</div>
			</div>
			<?php
		}

		/**
		 * Prints body of current widget
		 *
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @param array       $settings - widget settings.
		 * @return void
		 */
		public static function print_archive_widget( $ticket, $settings ) {

			$current_user = WPSC_Current_User::$current_user;
			if ( ! (
				(
					(
						WPSC_Individual_Archive_Ticket::$view_profile == 'customer' ||
						$ticket->customer->id == $current_user->customer->id
					) &&
					$settings['allow-customer']
				) ||
				( WPSC_Individual_Archive_Ticket::$view_profile == 'agent' && in_array( $current_user->agent->role, $settings['allowed-agent-roles'] ) )
			) ) {
				return;
			}
			$title = $settings['title']
					? WPSC_Translations::get( 'wpsc-twt-tags', stripslashes( $settings['title'] ) )
					: stripslashes( $settings['title'] );
			?>
			<div class="wpsc-it-widget wpsc-itw-ticket-tags">
				<div class="wpsc-widget-header">
					<h2><?php echo esc_attr( $title ); ?></h2>
					<span class="wpsc-itw-toggle" data-widget="wpsc-itw-ticket-tags"><?php WPSC_Icons::get( 'chevron-up' ); ?></span>
				</div>
				<div class="wpsc-widget-body">
					<?php
					if ( $ticket->tags ) {
						?>
						<div class="wpsc-tag-list wpsc-add-tag-trigger">
							<?php
							foreach ( $ticket->tags as $tag ) {
								if ( ! $tag->id ) {
									continue;
								}
								?>
								<div class="wpsc-ticket-tag" style="background-color: <?php echo esc_attr( $tag->bg_color ); ?>; color: <?php echo esc_attr( $tag->color ); ?>;">
									<?php echo esc_attr( $tag->name ); ?>
								</div>
								<?php
							}
							?>
						</div>
						<?php
					} else {
						?>
						<div class="wpsc-widget-default wpsc-add-tag-trigger"><?php esc_attr_e( 'Not Applicable', 'supportcandy' ); ?></div>
						<?php
					}
					?>
				</div>
			</div>
			<?php
		}

		/**
		 * Print timer script in open ticket.
		 *
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return void
		 */
		public static function print_tags_script( $ticket ) {

			// check if $ticket is an object of WPSC_Ticket.
			if ( ! is_object( $ticket ) || ! is_a( $ticket, 'WPSC_Ticket' ) || ! $ticket->id ) {
				return;
			}
			?>
			<script>
				wpsc_it_refresh_tags(<?php echo esc_attr( $ticket->id ); ?>);
			</script>
			<?php
		}

		/**
		 * Tag autocomplete
		 *
		 * @return void
		 */
		public static function tag_autocomplete() {

			if ( check_ajax_referer( 'wpsc_tag_autocomplete', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			$term = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
			$tags = isset( $_GET['tags'] ) ? array_filter( array_map( 'intval', $_GET['tags'] ) ) : array();

			// default is 5 for existing consumers, inline tag editor asks for more.
			$per_page = isset( $_GET['per_page'] ) ? min( max( intval( $_GET['per_page'] ), 1 ), 20 ) : 5;

			$args = array(
				'items_per_page' => $per_page,
				'search'         => $term,
				'order'          => 'ASC',
				'orderby'        => 'id',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'slug'    => 'id',
						'compare' => 'NOT IN',
						'val'     => $tags,
					),
				),
			);

			$tags = WPSC_Ticket_Tags::find( $args )['results'];
			$response = array();
			foreach ( $tags as $tag ) {
				$response[] = array(
					'id'       => $tag->id,
					'title'    => $tag->name,
					'color'    => $tag->color,
					'bg_color' => $tag->bg_color,
				);
			}
			wp_send_json( $response );
		}

		/**
		 *  Set ticket  tags
		 *
		 * @return void
		 */
		public static function update_edit_ticket_tags() {

			if ( check_ajax_referer( 'wpsc_it_set_edit_ticket_tags', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			WPSC_Individual_Ticket::load_current_ticket();
			if ( WPSC_Individual_Ticket::$is_restricted ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			$current_user = WPSC_Current_User::$current_user;
			if ( ! ( WPSC_Individual_Ticket::$view_profile == 'agent' && WPSC_Individual_Ticket::has_ticket_cap( 'tt' ) ) ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			$ticket = WPSC_Individual_Ticket::$ticket;
			if ( ! $ticket->is_active ) {
				wp_send_json_error( 'Something went wrong!', 400 );
			}

			$tags = isset( $_POST['tags'] ) ? array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['tags'] ) ) ) : array();

			$general = get_option( 'wpsc-ticket-tags-general-settings' );

			$temp = array();
			foreach ( $ticket->tags as $tem ) {
				$temp[] = $tem->id;
			}

			$ticket_tags = array();
			foreach ( $tags as $key => $tag_id ) {

				$tag = new WPSC_Ticket_Tags( $tag_id );
				if ( ! $tag->id ) {

					$data = array(
						'name'        => $tag_id,
						'description' => '',
						'color'       => $general['color'],
						'bg_color'    => $general['bg-color'],
					);
					$new_tag = WPSC_Ticket_Tags::insert( $data );
					$tags[ $key ] = $new_tag->id;
				}
				$ticket_tags[] = $tags[ $key ];
			}

			// tags to be created (sent by inline editor). Reuse existing tag if same name already exists.
			$new_names = isset( $_POST['new_tags'] ) ? array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['new_tags'] ) ) ) : array();
			foreach ( $new_names as $name ) {

				$name  = trim( $name );
				$found = null;
				foreach ( WPSC_Ticket_Tags::find(
					array(
						'items_per_page' => 20,
						'search'         => $name,
					)
				)['results'] as $existing ) {
					if ( strcasecmp( $existing->name, $name ) === 0 ) {
						$found = $existing;
						break;
					}
				}
				if ( ! $found ) {
					$found = WPSC_Ticket_Tags::insert(
						array(
							'name'        => $name,
							'description' => '',
							'color'       => $general['color'],
							'bg_color'    => $general['bg-color'],
						)
					);
				}
				$ticket_tags[] = $found->id;
			}

			$ticket_tags = array_unique( $ticket_tags );
			$ticket->tags = $ticket_tags;
			$ticket->date_updated = new DateTime();
			$ticket->save();
			echo esc_attr( self::get_tw_body( $ticket ) );
			wp_die();
		}

		/**
		 * Get Ticket tags
		 */
		public static function get_tw_ticket_tags() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$ticket_widgets    = get_option( 'wpsc-ticket-widget', array() );
			$ticket_tags = $ticket_widgets['tags'];
			$title             = $ticket_tags['title'];
			$roles             = get_option( 'wpsc-agent-roles', array() );
			ob_start();
			?>
			<form action="#" onsubmit="return false;" class="wpsc-frm-edit-tags">
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Title', 'supportcandy' ); ?></label>
					</div>
					<p class="wpsc-input-desc"><?php esc_attr_e( 'Heading shown on the widget in the ticket page.', 'supportcandy' ); ?></p>
					<input name="label" type="text" value="<?php echo esc_attr( $ticket_tags['title'] ); ?>" autocomplete="off">
				</div>
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Enable', 'supportcandy' ); ?></label>
					</div>
					<p class="wpsc-input-desc"><?php esc_attr_e( 'Shows or hides this widget on the ticket page.', 'supportcandy' ); ?></p>
					<select name="is_enable">
						<option <?php selected( $ticket_tags['is_enable'], '1' ); ?> value="1"><?php esc_attr_e( 'Yes', 'supportcandy' ); ?></option>
						<option <?php selected( $ticket_tags['is_enable'], '0' ); ?>  value="0"><?php esc_attr_e( 'No', 'supportcandy' ); ?></option>
					</select>
				</div>
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Allowed agent roles', 'supportcandy' ); ?></label>
					</div>
					<p class="wpsc-input-desc"><?php esc_attr_e( 'Agents with these roles can see this widget.', 'supportcandy' ); ?></p>
					<select  multiple id="wpsc-select-agents" name="agents[]" placeholder="search agent...">
						<?php
						foreach ( $roles as $key => $role ) :
							$selected = in_array( $key, $ticket_tags['allowed-agent-roles'] ) ? 'selected="selected"' : ''
							?>
							<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $role['label'] ); ?></option>
							<?php
							endforeach;
						?>
					</select>
				</div>
				<script>
					jQuery('#wpsc-select-agents').selectWoo({
						allowClear: false,
						placeholder: ""
					});
				</script>
				<?php do_action( 'wpsc_get_ticket_tags_body' ); ?>
				<input type="hidden" name="action" value="wpsc_set_tw_ticket_tags">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_set_tw_ticket_tags' ) ); ?>">
			</form>
			<?php
			$body = ob_get_clean();

			ob_start();
			?>
			<button class="wpsc-button small primary" onclick="wpsc_set_tw_ticket_tags(this);">
				<?php esc_attr_e( 'Submit', 'supportcandy' ); ?>
			</button>
			<button class="wpsc-button small secondary" onclick="wpsc_close_modal();">
				<?php esc_attr_e( 'Cancel', 'supportcandy' ); ?>
			</button>
			<?php
			do_action( 'wpsc_get_tw_ticket_tags_widget_footer' );
			$footer = ob_get_clean();

			$response = array(
				'title'  => $title,
				'body'   => $body,
				'footer' => $footer,
			);

			wp_send_json( $response );
		}

		/**
		 * Set ticket tags
		 */
		public static function set_tw_ticket_tags() {

			if ( check_ajax_referer( 'wpsc_set_tw_ticket_tags', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$label = isset( $_POST['label'] ) ? sanitize_text_field( wp_unslash( $_POST['label'] ) ) : '';
			if ( ! $label ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			$is_enable = isset( $_POST['is_enable'] ) ? intval( $_POST['is_enable'] ) : 0;
			$agents    = isset( $_POST['agents'] ) ? array_filter( array_map( 'intval', $_POST['agents'] ) ) : array();

			$ticket_widgets = get_option( 'wpsc-ticket-widget', array() );

			$ticket_widgets['tags']['title']               = $label;
			$ticket_widgets['tags']['is_enable']           = $is_enable;
			$ticket_widgets['tags']['allowed-agent-roles'] = $agents;
			update_option( 'wpsc-ticket-widget', $ticket_widgets );

			// remove string translations.
			WPSC_Translations::remove( 'wpsc-twt-tags' );
			WPSC_Translations::add( 'wpsc-twt-tags', stripslashes( $label ) );
			wp_die();
		}

		/**
		 * Refresh tags list
		 *
		 * @return void
		 */
		public static function refresh_tags_list() {

			$current_user = WPSC_Current_User::$current_user;

			WPSC_Individual_Ticket::load_current_ticket();
			$ticket = WPSC_Individual_Ticket::$ticket;
			if ( ! $current_user->is_agent ) {
				return;
			}
			self::get_tw_body( $ticket );
			wp_die();
		}

		/**
		 * Tag widget
		 *
		 * @param object $ticket - ticket object.
		 * @return void
		 */
		public static function get_tw_body( $ticket ) {

			$can_edit = (
				$ticket->is_active &&
				WPSC_Individual_Ticket::$view_profile == 'agent' &&
				WPSC_Individual_Ticket::has_ticket_cap( 'tt' ) &&
				! WPSC_Individual_Ticket::$is_restricted
			);

			$tags = array();
			foreach ( $ticket->tags as $tag ) {
				if ( $tag->id ) {
					$tags[] = $tag;
				}
			}

			if ( ! $can_edit ) {
				if ( $tags ) {
					?>
					<div class="wpsc-tag-list">
						<?php
						foreach ( $tags as $tag ) {
							?>
							<div class="wpsc-ticket-tag" style="background-color: <?php echo esc_attr( $tag->bg_color ); ?>; color: <?php echo esc_attr( $tag->color ); ?>;">
								<?php echo esc_attr( $tag->name ); ?>
							</div>
							<?php
						}
						?>
					</div>
					<?php
				} else {
					?>
					<div class="wpsc-widget-default"><?php esc_attr_e( 'Not Applicable', 'supportcandy' ); ?></div>
					<?php
				}
				return;
			}

			$general = get_option( 'wpsc-ticket-tags-general-settings' );
			$config  = array(
				'ticketId'    => (int) $ticket->id,
				'tags'        => array_map(
					fn( $tag ) => array(
						'id'       => (int) $tag->id,
						'name'     => $tag->name,
						'color'    => $tag->color,
						'bg_color' => $tag->bg_color,
					),
					$tags
				),
				'newColors'   => array(
					'color'    => $general['color'],
					'bg_color' => $general['bg-color'],
				),
				'saveNonce'   => wp_create_nonce( 'wpsc_it_set_edit_ticket_tags' ),
				'searchNonce' => wp_create_nonce( 'wpsc_tag_autocomplete' ),
				'i18n'        => array(
					'placeholder' => __( 'Add a tag...', 'supportcandy' ),
					/* translators: %s: tag name */
					'create'      => __( 'Create "%s"', 'supportcandy' ),
					'noResults'   => __( 'No matching tags', 'supportcandy' ),
					'remove'      => __( 'Remove', 'supportcandy' ),
					'submit'      => __( 'Submit', 'supportcandy' ),
					'cancel'      => __( 'Cancel', 'supportcandy' ),
				),
			);
			?>
			<div class="wpsc-tags-wrap" data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">
				<div class="wpsc-tags-view" role="button" tabindex="0" onclick="wpsc_tags_edit(this)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();wpsc_tags_edit(this);}">
					<?php
					if ( $tags ) {
						?>
						<div class="wpsc-tag-list">
							<?php
							foreach ( $tags as $tag ) {
								?>
								<div class="wpsc-ticket-tag" style="background-color: <?php echo esc_attr( $tag->bg_color ); ?>; color: <?php echo esc_attr( $tag->color ); ?>;">
									<?php echo esc_attr( $tag->name ); ?>
								</div>
								<?php
							}
							?>
						</div>
						<?php
					} else {
						?>
						<div class="wpsc-widget-default"><?php esc_attr_e( 'Not Applicable', 'supportcandy' ); ?></div>
						<?php
					}
					?>
				</div>
				<div class="wpsc-tags-editor wpsc-ie-panel"></div>
			</div>
			<?php
		}
	}

endif;

WPSC_ITW_Ticket_Tags::init();
