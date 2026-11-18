<?php // phpcs:ignore
/**
 * Plugin Name: SupportCandy
 * Plugin URI: https://supportcandy.net/
 * Description: Easy & Powerful support ticket system for WordPress
 * Version: 3.5.6
 * Author: SupportCandy
 * Author URI: https://wordpress.org/plugins/supportcandy/
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * Tested up to: 7.1
 * Text Domain: supportcandy
 * Domain Path: /i18n
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

if ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) {
	return;
}

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'PSM_Support_Candy' ) ) :

	final class PSM_Support_Candy {

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		public static $version = '3.5.6';

		/**
		 * Database version
		 *
		 * @var string
		 */
		public static $db_version = '3.0';

		/**
		 * Constructor for main class
		 */
		public static function init() {

			self::define_constants();
			self::load_files();

			// "Settings" link on plugins screen.
			WPSC_Addons::add_settings_link( plugin_basename( __FILE__ ), 'admin.php?page=wpsc-settings' );

			// Let add-ons boot once core is ready, independent of plugin load order.
			if ( ! defined( 'WPSC_INSTALLING' ) ) {
				add_action( 'plugins_loaded', array( __CLASS__, 'loaded' ), 5 );
			}
		}

		/**
		 * Core is ready; add-ons can register and initialize themselves now.
		 *
		 * @return void
		 */
		public static function loaded() {

			do_action( 'wpsc_loaded' );
		}

		/**
		 * Defines global constants that can be available anywhere in WordPress
		 *
		 * @return void
		 */
		public static function define_constants() {

			self::define( 'WPSC_STORE_URL', 'https://supportcandy.net' );
			self::define( 'WPSC_PLUGIN_FILE', __FILE__ );
			self::define( 'WPSC_ABSPATH', __DIR__ . '/' );
			self::define( 'WPSC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			self::define( 'WPSC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			self::define( 'WPSC_VERSION', self::$version );
			self::define( 'WPSC_DB_VERSION', self::$db_version );
		}

		/**
		 * Load all classes
		 *
		 * @return void
		 */
		private static function load_files() {

			// Load installation.
			include_once WPSC_ABSPATH . 'class-wpsc-installation.php';
			include_once WPSC_ABSPATH . 'global-functions.php';

			// Add-on registry is needed by add-ons even while database upgrade is in progress.
			include_once WPSC_ABSPATH . 'includes/admin/misc/class-wpsc-addons.php';

			// Return if installation is in progress.
			if ( defined( 'WPSC_INSTALLING' ) ) {
				return;
			}

			// Database upgrade functionality.
			if ( defined( 'WPSC_DB_UPGRADING' ) ) {

				include_once WPSC_ABSPATH . 'includes/class-wpsc-sc-upgrade.php';

				switch ( WPSC_Installation::$current_db_version ) {

					case '1.0':
						include_once WPSC_ABSPATH . 'upgrade/class-wpsc-upgrade-db-v1.php';
						break;

					case '2.0':
						include_once WPSC_ABSPATH . 'upgrade/class-wpsc-upgrade-db-v2.php';
						break;
				}

				// do not load further files.
				return;
			}

			$advanced = get_option( 'wpsc-ms-advanced-settings' );

			// Load common classes.
			foreach ( glob( WPSC_ABSPATH . 'includes/*.php' ) as $filename ) {
				include_once $filename;
			}

			// Load Gutenberg blocks and Elementor widgets. The Elementor widget class is
			// loaded by WPSC_Elementor once Elementor is ready.
			include_once WPSC_ABSPATH . 'includes/blocks/class-wpsc-blocks.php';
			include_once WPSC_ABSPATH . 'includes/blocks/class-wpsc-elementor.php';

			// Load custom field types.
			foreach ( glob( WPSC_ABSPATH . 'includes/custom-field-types/*.php' ) as $filename ) {
				include_once $filename;
			}

			// Load models.
			foreach ( glob( WPSC_ABSPATH . 'includes/models/*.php' ) as $filename ) {
				include_once $filename;
			}

			// Load rest api classes.
			if ( ! empty( $advanced['rest-api'] ) ) {
				foreach ( glob( WPSC_ABSPATH . 'includes/rest-api/*.php' ) as $filename ) {
					include_once $filename;
				}
			}

			// Load framework.
			if ( ! ( defined( 'DOING_AJAX' ) || defined( 'DOING_CRON' ) ) ) {
				foreach ( glob( WPSC_ABSPATH . 'framework/*.php' ) as $filename ) {
					include_once $filename;
				}
			}

			// Load classes that is related to admin section.
			foreach ( glob( WPSC_ABSPATH . 'includes/admin/*.php' ) as $filename ) {
				include_once $filename;
			}

			// Load classes that is related to frontend section.
			foreach ( glob( WPSC_ABSPATH . 'includes/frontend/*.php' ) as $filename ) {
				include_once $filename;
			}

			// load deactivation feedback class.
			self::init_deactivation_feedback();
		}

		/**
		 * Define constants
		 *
		 * @param string $name - name of global constant.
		 * @param string $value - value of constant.
		 * @return void
		 */
		private static function define( $name, $value ) {

			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}



		/**
		 * Initialize deactivation feedback system.
		 *
		 * @return void
		 */
		public static function init_deactivation_feedback() {
			require_once WPSC_ABSPATH . 'deactivation-feedback/class-psm-dfr.php';
			PSM_DFR::init(
				array(
					'plugin_file'      => plugin_basename( __FILE__ ),
					'plugin_name'      => 'SupportCandy',
					'plugin_version'   => WPSC_VERSION,
					'endpoint'         => 'https://feedback.psmplugins.com/wp-json/psm/v1/feedback',
					'api_key'          => 'b8p-MO>d}!aq<bY6nx]mRG~&ndXO=3jwIi{iX<YAt2#Z!>n-[.Q9](OZ--}#@C|',
					'reasons'          => array( __CLASS__, 'get_deactivation_reasons' ),
					'context_callback' => array( __CLASS__, 'get_deactivation_context' ),
				)
			);
		}

		/**
		 * Deactivation reasons. Keys must match the feedback receiver's reason list.
		 *
		 * @return array
		 */
		public static function get_deactivation_reasons() {

			return array(
				'too_complex'       => array(
					'label'            => __( 'Too complicated to set up', 'supportcandy' ),
					'note_placeholder' => __( 'Which part was hard to set up?', 'supportcandy' ),
				),
				'testing'           => __( 'Just testing / comparing plugins', 'supportcandy' ),
				'found_alternative' => array(
					'label'            => __( 'Found a better plugin', 'supportcandy' ),
					'note_placeholder' => __( 'Which plugin are you switching to?', 'supportcandy' ),
				),
				'missing_features'  => array(
					'label'            => __( 'Missing a feature I need', 'supportcandy' ),
					'note_placeholder' => __( 'Which feature would have kept you?', 'supportcandy' ),
				),
				'bugs_errors'       => array(
					'label'            => __( 'Bugs, errors or slow performance', 'supportcandy' ),
					'note_placeholder' => __( 'What went wrong?', 'supportcandy' ),
				),
				'theme_display'     => array(
					'label'            => __( 'Does not look right with my theme', 'supportcandy' ),
					'note_placeholder' => __( 'Which theme or page builder?', 'supportcandy' ),
				),
				'not_needed'        => __( 'No longer need a helpdesk', 'supportcandy' ),
				'temporary'         => __( 'Temporary deactivation', 'supportcandy' ),
				'other'             => array(
					'label'            => __( 'Other', 'supportcandy' ),
					'note_required'    => true,
					'note_placeholder' => __( 'What made you deactivate?', 'supportcandy' ),
				),
			);
		}

		/**
		 * Anonymous usage context sent with deactivation feedback.
		 * Nothing identifies the site or its users.
		 *
		 * @return array
		 */
		public static function get_deactivation_context() {

			global $wpdb;

			// Install age. Older installs have no stored date, fall back to the first ticket.
			$installed_on   = (int) get_option( 'wpsc_installed_on', 0 );
			$install_source = 'option';
			if ( ! $installed_on ) {
				$first          = $wpdb->get_var( "SELECT MIN(date_created) FROM {$wpdb->prefix}psmsc_tickets" );
				$installed_on   = $first ? (int) strtotime( $first . ' UTC' ) : 0;
				$install_source = $installed_on ? 'first_ticket' : 'unknown';
			}

			// Onboarding wizard completion. Installs that never had the wizard report null.
			$onboarding = get_option( 'wpsc-onboarding-wizard', null );

			return array(
				'onboarding_completed' => is_array( $onboarding ) && isset( $onboarding['status'] ) ? 'completed' === $onboarding['status'] : null,
				'days_active'          => $installed_on ? max( 0, (int) floor( ( time() - $installed_on ) / DAY_IN_SECONDS ) ) : null,
				'install_source'       => $install_source,
			);
		}
	}
endif;

PSM_Support_Candy::init();
