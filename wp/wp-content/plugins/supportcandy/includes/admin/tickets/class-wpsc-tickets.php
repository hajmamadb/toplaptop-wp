<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_Tickets' ) ) :

	final class WPSC_Tickets {

		/**
		 * Set if current screen is tickets page
		 *
		 * @var boolean
		 */
		public static $is_current_page;

		/**
		 * Sections for this view
		 *
		 * @var array
		 */
		private static $sections = array();

		/**
		 * Current section to load
		 *
		 * @var string
		 */
		public static $current_section;

		/**
		 * Initialize this class
		 */
		public static function init() {

			// Load sections for this screen.
			add_action( 'admin_init', array( __CLASS__, 'load_sections' ) );

			// Add current section to admin localization data.
			add_filter( 'wpsc_admin_localizations', array( __CLASS__, 'localizations' ) );

			// Humbargar modal.
			add_action( 'admin_footer', array( __CLASS__, 'humbargar_menu' ) );

			// JS dynamic functions.
			add_action( 'wpsc_js_ready', array( __CLASS__, 'register_js_ready_function' ) );
			add_action( 'wpsc_js_after_ticket_reply', array( __CLASS__, 'js_after_ticket_reply' ) );
			add_action( 'wpsc_js_after_close_ticket', array( __CLASS__, 'js_after_close_ticket' ) );

			// agent dashboard.
			add_action( 'wp_ajax_wpsc_get_agent_dashboard', array( __CLASS__, 'get_agent_dashboard' ) );

			// dismiss upsell cards.
			add_action( 'wp_ajax_wpsc_dismiss_upsell_cards', array( __CLASS__, 'dismiss_upsell_cards' ) );
		}

		/**
		 * Whether pro-feature upsell card should be shown to current user.
		 * Shown to admins on free version once there is some real usage:
		 * 10 tickets, or 7 days since install with at least 3 tickets.
		 * Hidden for 15 days after the user dismisses a card, then the next card is shown.
		 *
		 * @return boolean
		 */
		public static function should_show_upsell_cards() {

			global $wpdb;

			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}

			$dismissed = intval( get_user_meta( get_current_user_id(), get_current_blog_id() . '-wpsc-upsell-dismissed', true ) );
			if ( $dismissed && ( time() - $dismissed ) < 15 * DAY_IN_SECONDS ) {
				return false;
			}

			if ( WPSC_Functions::is_paid_customer() ) {
				return false;
			}

			$ticket_count = intval( $wpdb->get_var( "SELECT COUNT(*) FROM (SELECT id FROM {$wpdb->prefix}psmsc_tickets LIMIT 10) t" ) );
			if ( $ticket_count >= 10 ) {
				return true;
			}

			$installed_on = intval( get_option( 'wpsc_installed_on', 0 ) );
			if ( ! $installed_on ) {
				// Sites installed before this option existed: fall back to oldest ticket, else now.
				$oldest       = $wpdb->get_var( "SELECT MIN(date_created) FROM {$wpdb->prefix}psmsc_tickets" );
				$installed_on = $oldest ? strtotime( $oldest . ' UTC' ) : time();
				update_option( 'wpsc_installed_on', $installed_on );
			}

			return ( time() - $installed_on ) >= 7 * DAY_IN_SECONDS && $ticket_count >= 3;
		}

		/**
		 * Upsell cards (paid add-ons) in priority order. The first card that matches
		 * site usage and has not been dismissed by the user before is shown.
		 *
		 * @return array
		 */
		public static function get_upsell_cards() {

			return array(
				// Integrations: shown only when the related plugin is active.
				'woocommerce'             => array(
					'icon'     => 'tags',
					'title'    => esc_attr__( 'See every customer\'s orders right inside the ticket', 'supportcandy' ),
					'desc'     => esc_attr__( 'Customers raise tickets from their orders, and agents see order history and lifetime spend without leaving the ticket.', 'supportcandy' ),
					'cta'      => esc_attr__( 'See WooCommerce Integration', 'supportcandy' ),
					'url'      => 'https://supportcandy.net/downloads/woocommerce-integration/',
					'eligible' => array( __CLASS__, 'upsell_has_woocommerce' ),
				),
				'edd'                     => array(
					'icon'     => 'tags',
					'title'    => esc_attr__( 'See every customer\'s purchases right inside the ticket', 'supportcandy' ),
					'desc'     => esc_attr__( 'Customers pick the order or download they need help with, and agents see their purchase history in the ticket.', 'supportcandy' ),
					'cta'      => esc_attr__( 'See EDD Integration', 'supportcandy' ),
					'url'      => 'https://supportcandy.net/downloads/edd-integration/',
					'eligible' => array( __CLASS__, 'upsell_has_edd' ),
				),
				'gravity-forms'           => array(
					'icon'     => 'contact-form',
					'title'    => esc_attr__( 'Create tickets from your Gravity Forms', 'supportcandy' ),
					'desc'     => esc_attr__( 'Use the forms you already built with Gravity Forms as ticket forms, with as many forms as you need.', 'supportcandy' ),
					'cta'      => esc_attr__( 'See Gravity Forms Integration', 'supportcandy' ),
					'url'      => 'https://supportcandy.net/downloads/gravity-forms-integration/',
					'eligible' => array( __CLASS__, 'upsell_has_gravity_forms' ),
				),
				'lms'                     => array(
					'icon'     => 'id-card',
					'title'    => esc_attr__( 'Support your students with their course details at hand', 'supportcandy' ),
					'desc'     => esc_attr__( 'Agents see the student\'s courses and progress right inside the ticket. Works with popular LMS plugins.', 'supportcandy' ),
					'cta'      => esc_attr__( 'See LMS Integration', 'supportcandy' ),
					'url'      => 'https://supportcandy.net/downloads/lms-integration/',
					'eligible' => array( __CLASS__, 'upsell_has_lms' ),
				),
				// Team: shown when there is more than one active agent.
				'workflows'               => array(
					'icon'     => 'cogs',
					'title'    => esc_attr__( 'Let tickets assign and update themselves', 'supportcandy' ),
					'desc'     => esc_attr__( 'Set rules once to auto-assign agents, change statuses and add notes whenever tickets are created or updated.', 'supportcandy' ),
					'cta'      => esc_attr__( 'See Workflows', 'supportcandy' ),
					'url'      => 'https://supportcandy.net/downloads/workflows/',
					'eligible' => array( __CLASS__, 'upsell_has_team' ),
				),
				'agentgroups'             => array(
					'icon'     => 'users',
					'title'    => esc_attr__( 'Organize agents into teams', 'supportcandy' ),
					'desc'     => esc_attr__( 'Assign tickets to a team like a single agent, and let the team supervisor distribute them.', 'supportcandy' ),
					'cta'      => esc_attr__( 'See Agent Groups', 'supportcandy' ),
					'url'      => 'https://supportcandy.net/downloads/agentgroups/',
					'eligible' => array( __CLASS__, 'upsell_has_team' ),
				),
				// Volume: shown once the site has 50 closed tickets.
				'satisfaction-survey'     => array(
					'icon'     => 'line-graph',
					'title'    => esc_attr__( 'Find out how happy your customers really are', 'supportcandy' ),
					'desc'     => esc_attr__( 'Collect one-click ratings after tickets close and track customer satisfaction in a single report.', 'supportcandy' ),
					'cta'      => esc_attr__( 'See Satisfaction Survey', 'supportcandy' ),
					'url'      => 'https://supportcandy.net/downloads/satisfaction-survey/',
					'eligible' => array( __CLASS__, 'upsell_has_closed_tickets' ),
				),
				'reports'                 => array(
					'icon'     => 'pie-chart',
					'title'    => esc_attr__( 'Know how fast your team responds and resolves', 'supportcandy' ),
					'desc'     => esc_attr__( 'Track response time, resolution time and ticket trends with advanced support reports.', 'supportcandy' ),
					'cta'      => esc_attr__( 'See Reports', 'supportcandy' ),
					'url'      => 'https://supportcandy.net/downloads/reports/',
					'eligible' => array( __CLASS__, 'upsell_has_closed_tickets' ),
				),
				// General: useful for every site.
				'email-piping'            => array(
					'icon'     => 'envelope',
					'title'    => esc_attr__( 'Turn customer emails into tickets automatically', 'supportcandy' ),
					'desc'     => esc_attr__( 'Customers create and reply to tickets straight from their inbox, no login needed. Works with Gmail, Microsoft 365 and IMAP.', 'supportcandy' ),
					'cta'      => esc_attr__( 'See Email Piping', 'supportcandy' ),
					'url'      => 'https://supportcandy.net/downloads/email-piping/',
					'eligible' => '__return_true',
				),
				'productivity-suite'      => array(
					'icon'     => 'widget',
					'title'    => esc_attr__( 'Merge duplicate tickets into one conversation', 'supportcandy' ),
					'desc'     => esc_attr__( 'Combine tickets from the same customer about the same issue, plus more time-saving tools for agents.', 'supportcandy' ),
					'cta'      => esc_attr__( 'See Productivity Suite', 'supportcandy' ),
					'url'      => 'https://supportcandy.net/downloads/productivity-suite/',
					'eligible' => '__return_true',
				),
				'export-tickets'          => array(
					'icon'     => 'list-alt',
					'title'    => esc_attr__( 'Export your tickets to CSV in one click', 'supportcandy' ),
					'desc'     => esc_attr__( 'Download the tickets from your current filter as a CSV file for reporting, audits or backups.', 'supportcandy' ),
					'cta'      => esc_attr__( 'See Export Tickets', 'supportcandy' ),
					'url'      => 'https://supportcandy.net/downloads/export-tickets/',
					'eligible' => '__return_true',
				),
				'canned-reply'            => array(
					'icon'     => 'reply',
					'title'    => esc_attr__( 'Answer common questions in two clicks', 'supportcandy' ),
					'desc'     => esc_attr__( 'Save your best replies and insert them into any ticket instead of typing the same answer again.', 'supportcandy' ),
					'cta'      => esc_attr__( 'See Canned Reply', 'supportcandy' ),
					'url'      => 'https://supportcandy.net/downloads/canned-reply/',
					'eligible' => '__return_true',
				),
				'sla'                     => array(
					'icon'     => 'clock',
					'title'    => esc_attr__( 'Never miss a response deadline', 'supportcandy' ),
					'desc'     => esc_attr__( 'Set response and resolution targets per ticket type and see which tickets are about to breach.', 'supportcandy' ),
					'cta'      => esc_attr__( 'See SLA', 'supportcandy' ),
					'url'      => 'https://supportcandy.net/downloads/sla/',
					'eligible' => '__return_true',
				),
				'automatic-close-tickets' => array(
					'icon'     => 'closed',
					'title'    => esc_attr__( 'Clean up inactive tickets automatically', 'supportcandy' ),
					'desc'     => esc_attr__( 'Close tickets after a set number of days without a reply, with an optional warning email to the customer first.', 'supportcandy' ),
					'cta'      => esc_attr__( 'See Automatic Close Tickets', 'supportcandy' ),
					'url'      => 'https://supportcandy.net/downloads/automatic-close-tickets/',
					'eligible' => '__return_true',
				),
				'usergroups'              => array(
					'icon'     => 'users-avatar',
					'title'    => esc_attr__( 'Support companies, not just individual customers', 'supportcandy' ),
					'desc'     => esc_attr__( 'Group customers by company so a supervisor can see and manage all tickets raised by their team.', 'supportcandy' ),
					'cta'      => esc_attr__( 'See User Groups', 'supportcandy' ),
					'url'      => 'https://supportcandy.net/downloads/usergroups/',
					'eligible' => '__return_true',
				),
				'timer'                   => array(
					'icon'     => 'clock',
					'title'    => esc_attr__( 'Track time spent on every ticket', 'supportcandy' ),
					'desc'     => esc_attr__( 'Agents record time with a stopwatch on each ticket, so you know where support hours go.', 'supportcandy' ),
					'cta'      => esc_attr__( 'See Timer', 'supportcandy' ),
					'url'      => 'https://supportcandy.net/downloads/timer/',
					'eligible' => '__return_true',
				),
				'print-ticket'            => array(
					'icon'     => 'notes',
					'title'    => esc_attr__( 'Print tickets with your own templates', 'supportcandy' ),
					'desc'     => esc_attr__( 'Print a ticket and its conversation in a clean, branded layout using custom templates.', 'supportcandy' ),
					'cta'      => esc_attr__( 'See Print Ticket', 'supportcandy' ),
					'url'      => 'https://supportcandy.net/downloads/print-ticket/',
					'eligible' => '__return_true',
				),
				'slack'                   => array(
					'icon'     => 'headset',
					'title'    => esc_attr__( 'Get new tickets in Slack and reply from there', 'supportcandy' ),
					'desc'     => esc_attr__( 'Receive instant ticket notifications in your Slack channel and reply directly from the thread.', 'supportcandy' ),
					'cta'      => esc_attr__( 'See Slack Integration', 'supportcandy' ),
					'url'      => 'https://supportcandy.net/downloads/slack-integration/',
					'eligible' => '__return_true',
				),
			);
		}

		/**
		 * Upsell signal: WooCommerce is active
		 *
		 * @return boolean
		 */
		public static function upsell_has_woocommerce() {

			return class_exists( 'WooCommerce' );
		}

		/**
		 * Upsell signal: Easy Digital Downloads is active
		 *
		 * @return boolean
		 */
		public static function upsell_has_edd() {

			return class_exists( 'Easy_Digital_Downloads' );
		}

		/**
		 * Upsell signal: Gravity Forms is active
		 *
		 * @return boolean
		 */
		public static function upsell_has_gravity_forms() {

			return class_exists( 'GFForms' );
		}

		/**
		 * Upsell signal: a supported LMS plugin is active (LearnDash, LifterLMS, Tutor LMS)
		 *
		 * @return boolean
		 */
		public static function upsell_has_lms() {

			return defined( 'LEARNDASH_VERSION' ) || class_exists( 'LifterLMS' ) || defined( 'TUTOR_VERSION' );
		}

		/**
		 * Upsell signal: more than one active agent
		 *
		 * @return boolean
		 */
		public static function upsell_has_team() {

			global $wpdb;
			return intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}psmsc_agents WHERE is_active = 1 AND is_agentgroup = 0" ) ) > 1;
		}

		/**
		 * Upsell signal: at least 50 closed tickets
		 *
		 * @return boolean
		 */
		public static function upsell_has_closed_tickets() {

			global $wpdb;
			$statuses = array_map( 'intval', WPSC_Functions::get_closed_statuses() );
			if ( ! $statuses ) {
				return false;
			}
			$statuses = implode( ',', $statuses );
			return intval( $wpdb->get_var( "SELECT COUNT(*) FROM (SELECT id FROM {$wpdb->prefix}psmsc_tickets WHERE is_active = 1 AND status IN ($statuses) LIMIT 50) t" ) ) >= 50; // phpcs:ignore
		}

		/**
		 * Get upsell card to show to current user, if any.
		 * Cards dismissed earlier are skipped so the user sees a different one next time.
		 *
		 * @return array|null
		 */
		public static function get_upsell_card() {

			if ( ! self::should_show_upsell_cards() ) {
				return null;
			}

			$meta_key = get_current_blog_id() . '-wpsc-upsell-dismissed-cards';
			$seen     = (array) get_user_meta( get_current_user_id(), $meta_key, true );

			$eligible = array();
			foreach ( self::get_upsell_cards() as $key => $card ) {
				if ( call_user_func( $card['eligible'] ) ) {
					$eligible[ $key ] = $card;
				}
			}

			$unseen = array_diff_key( $eligible, array_flip( $seen ) );
			if ( ! $unseen ) {
				// All eligible cards seen once: start the rotation again.
				delete_user_meta( get_current_user_id(), $meta_key );
				$unseen = $eligible;
			}

			$key         = array_key_first( $unseen );
			$card        = $unseen[ $key ];
			$card['key'] = $key;
			return $card;
		}

		/**
		 * Print upsell card on top of ticket list
		 *
		 * @return void
		 */
		public static function print_upsell_card() {

			$card = self::get_upsell_card();
			if ( ! $card ) {
				return;
			}

			$nonce = wp_create_nonce( 'wpsc_dismiss_upsell_cards' );
			?>
			<div class="wpsc-upsell-card" style="display:none;">
				<div class="wpsc-upsell-icon"><?php WPSC_Icons::get( $card['icon'] ); ?></div>
				<div class="wpsc-upsell-content">
					<div class="wpsc-upsell-title">
						<span class="wpsc-upsell-badge"><?php esc_attr_e( 'PRO', 'supportcandy' ); ?></span>
						<?php echo esc_attr( $card['title'] ); ?>
					</div>
					<p><?php echo esc_attr( $card['desc'] ); ?></p>
					<div class="wpsc-upsell-actions">
						<a class="wpsc-button small primary" target="_blank" rel="noopener" href="<?php echo esc_url( $card['url'] ); ?>"><?php echo esc_attr( $card['cta'] ); ?> &rarr;</a>
						<button type="button" class="wpsc-upsell-later" onclick="wpsc_dismiss_upsell_cards(this, '<?php echo esc_attr( $card['key'] ); ?>', '<?php echo esc_attr( $nonce ); ?>');"><?php esc_attr_e( 'Maybe later', 'supportcandy' ); ?></button>
					</div>
				</div>
				<button type="button" class="wpsc-upsell-dismiss" aria-label="<?php esc_attr_e( 'Dismiss', 'supportcandy' ); ?>" title="<?php esc_attr_e( 'Dismiss', 'supportcandy' ); ?>" onclick="wpsc_dismiss_upsell_cards(this, '<?php echo esc_attr( $card['key'] ); ?>', '<?php echo esc_attr( $nonce ); ?>');">&times;</button>
			</div>
			<script>
				// Show only on ticket list section (not on individual ticket or other sections).
				function wpsc_toggle_upsell_card() {
					var show = supportcandy.current_section === 'ticket-list' && ! supportcandy.ticketListIsIndividual;
					jQuery('.wpsc-upsell-card').toggle(show);
				}
				jQuery(document).ajaxComplete(wpsc_toggle_upsell_card);
				function wpsc_dismiss_upsell_cards(el, card, nonce) {
					jQuery(el).closest('.wpsc-upsell-card').remove();
					jQuery.post(supportcandy.ajax_url, { action: 'wpsc_dismiss_upsell_cards', card: card, _ajax_nonce: nonce });
				}
			</script>
			<style>
				.wpsc-upsell-card {
					position: relative;
					display: flex;
					gap: 12px;
					align-items: flex-start;
					background: #fff;
					border: 1px solid #e2e4e7;
					border-left: 4px solid #4a5bdc;
					border-radius: 6px;
					padding: 12px 40px 12px 14px;
					margin-bottom: 15px;
					box-sizing: border-box;
				}
				.wpsc-upsell-icon svg {
					width: 22px;
					height: 22px;
					fill: #4a5bdc;
				}
				.wpsc-upsell-content {
					flex: 1;
					min-width: 0;
				}
				.wpsc-upsell-title {
					font-size: 14px;
					font-weight: 600;
					color: #1d2327;
				}
				.wpsc-upsell-badge {
					display: inline-block;
					background: #4a5bdc;
					color: #fff;
					font-size: 10px;
					font-weight: 700;
					letter-spacing: .5px;
					line-height: 1;
					padding: 3px 6px;
					border-radius: 10px;
					margin-right: 6px;
					vertical-align: 1px;
				}
				.wpsc-upsell-content p {
					font-size: 13px;
					color: #50575e;
					margin: 4px 0 10px;
				}
				.wpsc-upsell-actions {
					display: flex;
					flex-wrap: wrap;
					align-items: center;
					gap: 12px;
				}
				.wpsc-upsell-actions a.wpsc-button {
					text-decoration: none;
				}
				.wpsc-upsell-later {
					border: none;
					background: none;
					padding: 0;
					font-size: 13px;
					color: #646970;
					cursor: pointer;
				}
				.wpsc-upsell-later:hover {
					text-decoration: underline;
				}
				.wpsc-upsell-dismiss {
					position: absolute;
					top: 6px;
					right: 6px;
					width: 28px;
					height: 28px;
					border: none;
					background: none;
					font-size: 20px;
					line-height: 1;
					cursor: pointer;
					color: #8c8f94;
					border-radius: 4px;
				}
				.wpsc-upsell-dismiss:hover {
					background: #f0f0f1;
					color: #1d2327;
				}
			</style>
			<?php
		}

		/**
		 * Dismiss upsell card for current user
		 *
		 * @return void
		 */
		public static function dismiss_upsell_cards() {

			if ( check_ajax_referer( 'wpsc_dismiss_upsell_cards', '_ajax_nonce', false ) !== 1 ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$user_id = get_current_user_id();
			$prefix  = get_current_blog_id();

			$card = isset( $_POST['card'] ) ? sanitize_key( wp_unslash( $_POST['card'] ) ) : '';
			if ( $card && array_key_exists( $card, self::get_upsell_cards() ) ) {
				$seen   = (array) get_user_meta( $user_id, $prefix . '-wpsc-upsell-dismissed-cards', true );
				$seen   = array_values( array_unique( array_filter( array_merge( $seen, array( $card ) ) ) ) );
				update_user_meta( $user_id, $prefix . '-wpsc-upsell-dismissed-cards', $seen );
			}

			update_user_meta( $user_id, $prefix . '-wpsc-upsell-dismissed', time() );
			wp_send_json_success();
		}

		/**
		 * Load section (nav elements) for this screen
		 *
		 * @return void
		 */
		public static function load_sections() {

			self::$is_current_page = isset( $_REQUEST['page'] ) && $_REQUEST['page'] === 'wpsc-tickets' ? true : false; // phpcs:ignore

			$current_user = WPSC_Current_User::$current_user;
			if ( ! ( $current_user->is_agent && self::$is_current_page ) ) {
				return;
			}

			// get default tab.
			$tab = $current_user->agent->get_default_tab();

			$gs = get_option( 'wpsc-gs-general' );
			$ms = get_option( 'wpsc-ms-advanced-settings' );

			// allow create ticket.
			$allow_create_ticket = in_array( $current_user->agent->role, $gs['allow-create-ticket'] ) ? true : false;

			$sections = array();
			// Supportcandy dashboard.
			if ( $current_user->is_agent && $current_user->agent->has_cap( 'dash-access' ) ) {
				$sections['dashboard'] = array(
					'slug'     => 'dashboard',
					'icon'     => 'dashboard',
					'label'    => esc_attr__( 'Dashboard', 'supportcandy' ),
					'callback' => 'wpsc_get_agent_dashboard',
				);
			}

			// ticket list.
			$sections['ticket-list'] = array(
				'slug'     => 'ticket_list',
				'icon'     => 'list-alt',
				'label'    => esc_attr__( 'Ticket List', 'supportcandy' ),
				'callback' => 'wpsc_get_ticket_list',
			);

			// new ticket.
			if ( $allow_create_ticket ) {
				$sections['new-ticket'] = array(
					'slug'     => 'new_ticket',
					'icon'     => 'plus-square',
					'label'    => esc_attr__( 'New Ticket', 'supportcandy' ),
					'callback' => 'wpsc_get_ticket_form',
				);
			}

			// my profile.
			if ( $ms['allow-my-profile'] ) {
				$sections['my-profile'] = array(
					'slug'     => 'my_profile',
					'icon'     => 'id-card',
					'label'    => esc_attr__( 'My Profile', 'supportcandy' ),
					'callback' => 'wpsc_get_user_profile',
				);
			}

			// agent profile.
			if ( $current_user->is_agent && $ms['allow-agent-profile'] ) {
				$sections['agent-profile'] = array(
					'slug'     => 'agent_profile',
					'icon'     => 'headset',
					'label'    => esc_attr__( 'Agent Profile', 'supportcandy' ),
					'callback' => 'wpsc_get_agent_profile',
				);
			}

			self::$sections        = apply_filters( 'wpsc_tickets_page_sections', $sections );
			self::$current_section = isset( $_REQUEST['section'] ) ? sanitize_text_field( $_REQUEST['section'] ) : $tab; // phpcs:ignore
		}

		/**
		 * Add localizations to local JS
		 *
		 * @param array $localizations - localization list.
		 * @return array
		 */
		public static function localizations( $localizations ) {

			if ( ! self::$is_current_page ) {
				return $localizations;
			}

			// Humbargar Titles.
			$localizations['humbargar_titles'] = self::get_humbargar_titles();

			// Current section.
			$localizations['current_section'] = self::$current_section;

			// Section callbacks used to restore screens on browser back/forward.
			$localizations['section_callbacks'] = wp_list_pluck( self::$sections, 'callback' );

			// Current ticket id.
			if ( self::$current_section === 'ticket-list' && isset( $_REQUEST['id'] ) ) { // phpcs:ignore
				$localizations['current_ticket_id'] = intval( $_REQUEST['id'] ); // phpcs:ignore
			}

			return $localizations;
		}

		/**
		 * UI foundation for this screen
		 *
		 * @return void
		 */
		public static function layout() {

			?>
			<div class="wrap">
				<hr class="wp-header-end">
				<?php self::print_upsell_card(); ?>
				<div id="wpsc-container" style="display:none;">
					<?php do_action( 'wpsc_before_tickets_page' ); ?>
					<div class="wpsc-shortcode-container">
						<div class="wpsc-header wpsc-hidden-xs">
							<?php
							foreach ( self::$sections as $key => $section ) :
								$active = self::$current_section === $key ? 'active' : '';
								?>
								<div class="wpsc-menu-list wpsc-tickets-nav <?php echo esc_attr( $key ) . ' ' . esc_attr( $active ); ?>" onclick="<?php echo esc_attr( $section['callback'] ) . '();'; ?>">
									<?php WPSC_Icons::get( $section['icon'] ); ?>
									<label><?php echo esc_attr( $section['label'] ); ?></label>
								</div>
								<?php
							endforeach;
							?>
							<div class="wpsc-menu-list wpsc-tickets-nav log-out" onclick="wpsc_user_logout(this, '<?php echo esc_attr( wp_create_nonce( 'wpsc_user_logout' ) ); ?>');">
								<?php WPSC_Icons::get( 'log-out' ); ?>
								<label><?php echo esc_attr__( 'Logout', 'supportcandy' ); ?></label>
							</div>
						</div>
						<div class="wpsc-header wpsc-visible-xs">
							<div class="wpsc-humbargar-title">
								<?php WPSC_Icons::get( self::$sections[ self::$current_section ]['icon'] ); ?>
								<label><?php echo esc_attr( self::$sections[ self::$current_section ]['label'] ); ?></label>
							</div>
							<div class="wpsc-humbargar" onclick="wpsc_toggle_humbargar();">
								<?php WPSC_Icons::get( 'bars' ); ?>
							</div>
						</div>
						<div class="wpsc-body"></div>
						<?php
						self::load_html_snippets();
						?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Print humbargar menu in footer
		 *
		 * @return void
		 */
		public static function humbargar_menu() {

			if ( ! self::$is_current_page ) {
				return;
			}

			?>
			<div class="wpsc-humbargar-overlay" onclick="wpsc_toggle_humbargar();" style="display:none"></div>
			<div class="wpsc-humbargar-menu" style="display:none">
				<div class="box-inner">
					<div class="wpsc-humbargar-header">
						<span class="wpsc-humbargar-heading"><?php esc_html_e( 'Menu', 'supportcandy' ); ?></span>
						<div class="wpsc-humbargar-close" onclick="wpsc_toggle_humbargar();" role="button" aria-label="<?php esc_attr_e( 'Close', 'supportcandy' ); ?>">
							<?php WPSC_Icons::get( 'close-thin' ); ?>
						</div>
					</div>
					<?php
					foreach ( self::$sections as $key => $section ) :

						$active = self::$current_section === $key ? 'active' : '';
						?>
						<div 
							class="wpsc-humbargar-menu-item <?php echo esc_attr( $key ) . ' ' . esc_attr( $active ); ?>"
							onclick="<?php echo esc_attr( $section['callback'] ) . '(true);'; ?>">
							<?php WPSC_Icons::get( $section['icon'] ); ?>
							<label><?php echo esc_attr( $section['label'] ); ?></label>
						</div>
					<?php endforeach; ?>
					<div class="wpsc-humbargar-menu-item log-out" onclick="wpsc_user_logout(this, '<?php echo esc_attr( wp_create_nonce( 'wpsc_user_logout' ) ); ?>');">
						<?php WPSC_Icons::get( 'log-out' ); ?>
						<label><?php echo esc_attr__( 'Logout', 'supportcandy' ); ?></label>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Humbargar mobile titles to be used in localizations
		 *
		 * @return array
		 */
		private static function get_humbargar_titles() {

			$titles = array();
			foreach ( self::$sections as $section ) {

				ob_start();
				WPSC_Icons::get( $section['icon'] );
				echo '<label>' . esc_attr( $section['label'] ) . '</label>';
				$titles[ $section['slug'] ] = ob_get_clean();
			}
			return $titles;
		}

		/**
		 * Register JS functions to call on document ready
		 *
		 * @return void
		 */
		public static function register_js_ready_function() {

			if ( ! self::$is_current_page ) {
				return;
			}

			echo esc_attr( self::$sections[ self::$current_section ]['callback'] ) . '();' . PHP_EOL;
		}

		/**
		 * After ticket reply
		 *
		 * @return void
		 */
		public static function js_after_ticket_reply() {

			if ( ! self::$is_current_page ) {
				return;
			}

			$current_user     = WPSC_Current_User::$current_user;
			$agent_settings   = get_option( 'wpsc-tl-ms-agent-view' );
			$call_to_function = $agent_settings['ticket-reply-redirect'] == 'ticket-list' ? 'wpsc_get_ticket_list();' : 'wpsc_get_individual_ticket(ticket_id)';
			echo esc_attr( $call_to_function ) . PHP_EOL;
		}

		/**
		 * JS after close ticket
		 *
		 * @return void
		 */
		public static function js_after_close_ticket() {

			if ( ! self::$is_current_page ) {
				return;
			}
			echo 'wpsc_get_ticket_list();' . PHP_EOL;
		}

		/**
		 * Load HTML snippets that can be used by js to load dynamically
		 *
		 * @return void
		 */
		public static function load_html_snippets() {
			?>

			<div class="wpsc-page-snippets" style="display: none;">
				<div class="wpsc-editor-attachment upload-waiting">
					<div class="attachment-label"></div>
					<div class="attachment-remove" onclick="wpsc_remove_attachment(this)">
						<?php WPSC_Icons::get( 'times' ); ?>
					</div>
					<div class="attachment-waiting"></div>
				</div>
			</div>
			<?php
		}

		/**
		 * Get current agent dashboard layout
		 *
		 * @return void
		 */
		public static function get_agent_dashboard() {

			$settings = get_option( 'wpsc-ap-dashboard' );
			$db_gs = get_option( 'wpsc-db-gs-settings', array() );
			?>
			<div class="wpsc-dashboard-view">
				<div class="wpsc-dash-widgets-row">
					<?php
					$cards = get_option( 'wpsc-dashboard-cards', array() );
					foreach ( $cards as $slug => $card ) {
						if ( ! $card['is_enable'] ) {
							continue;
						}
						if ( ! class_exists( $card['class'] ) ) {
							continue;
						}
						$card['class']::print_dashboard_card( $slug, $card );
					}
					?>
				</div>
				<div class="wpsc-dash-widgets-row wpsc-dashboard-widget-view">
					<?php
					$widgets = get_option( 'wpsc-dashboard-widgets', array() );
					foreach ( $widgets as $slug => $widget ) {
						if ( ! $widget['is_enable'] ) {
							continue;
						}
						if ( ! class_exists( $widget['class'] ) ) {
							continue;
						}
						$widget['class']::print_dashboard_widget( $slug, $widget );
					}
					?>
				</div>
			</div>
			<?php
			if ( $db_gs['dash-auto-refresh'] ) {
				?>
				<script>
					jQuery(document).ready(function() {
						refresh_dashboard();
					});
					var wpsc_db_refresh_timeout;
					function refresh_dashboard() {
						
						if( supportcandy.current_section !== "dashboard" ) {
							supportcandy.db_auto_refresh_schedule = false;
							return;
						}

						// Clear the previous timeout, if any.
						clearTimeout( wpsc_db_refresh_timeout );

						wpsc_db_refresh_timeout = setTimeout(function () {
							supportcandy.db_auto_refresh_schedule = true;
							refresh_dashboard();
						}, 300000);

						if (
							typeof supportcandy.db_auto_refresh_schedule === "undefined" ||
							supportcandy.db_auto_refresh_schedule === false
						) {
							return;
						}
						supportcandy.db_auto_refresh_schedule = false;
						wpsc_get_agent_dashboard();
					}
				</script>
				<?php
			}
			?>
			<style>
				.wpsc-dash-widget-small svg{
					color: <?php echo esc_attr( $settings['card-body-svg-color'] ); ?>;
				}
				.wpsc-dash-widget-small{
					background-color:<?php echo esc_attr( $settings['card-body-bg-color'] ); ?> !important;
				}
				.wpsc-dash-widget-small, .wpsc-dash-widget-small h2{
					color:<?php echo esc_attr( $settings['card-body-text-color'] ); ?> !important;
				}
				.wpsc-dash-widget-mid, .wpsc-dash-widget-large{
					background-color:<?php echo esc_attr( $settings['widget-body-bg-color'] ); ?> !important;
					color:<?php echo esc_attr( $settings['widget-body-text-color'] ); ?> !important;
				}
				.wpsc-dash-widget-header svg {
					color:<?php echo esc_attr( $settings['widget-body-svg-color'] ); ?>;
				}
			</style>
			<?php
			wp_die();
		}
	}
endif;

WPSC_Tickets::init();

