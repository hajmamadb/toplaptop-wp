<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_Cron' ) ) :

	final class WPSC_Cron {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// Add custom cron intervals.
			add_filter( 'cron_schedules', array( __CLASS__, 'custom_interval' ) ); //phpcs:ignore

			// Schedule cron jobs.
			add_action( 'init', array( __CLASS__, 'schedule_events' ) );

			// cron event callbacks.
			add_action( 'wpsc_auto_archive_closed_tickets', array( __CLASS__, 'auto_archive_closed_tickets' ) );
			add_action( 'wpsc_permanently_delete_archive_tickets', array( __CLASS__, 'permanently_delete_archive_tickets' ) );
			add_action( 'wpsc_permanently_delete_tickets', array( __CLASS__, 'permanently_delete_tickets' ) );

			// run background processes.
			add_action( 'wp_ajax_wpsc_run_ajax_background_process', array( __CLASS__, 'run_background_process' ) );
			add_action( 'wp_ajax_nopriv_wpsc_run_ajax_background_process', array( __CLASS__, 'run_background_process' ) );
		}

		/**
		 * Custom cron job intervals for SupportCandy
		 *
		 * @param array $schedules - schedule time.
		 * @return array
		 */
		public static function custom_interval( $schedules ) {

			$schedules['wpsc_1min'] = array(
				'interval' => 60,
				'display'  => esc_attr__( 'Every one minute', 'supportcandy' ),
			);

			$schedules['wpsc_5min'] = array(
				'interval' => 300,
				'display'  => esc_attr__( 'Every five minutes', 'supportcandy' ),
			);

			return $schedules;
		}

		/**
		 * Schedule cron job events for SupportCandy
		 *
		 * @return void
		 */
		public static function schedule_events() {

			$advanced = get_option( 'wpsc-ms-advanced-settings' );

			// Schedule cron job for every five minute events.
			if ( ! wp_next_scheduled( 'wpsc_cron_five_minute' ) ) {
				wp_schedule_event(
					time(),
					'wpsc_5min',
					'wpsc_cron_five_minute'
				);
			}

			// Schedule cron job for daily events.
			if ( ! wp_next_scheduled( 'wpsc_cron_daily' ) ) {
				wp_schedule_event(
					self::get_midnight_timestamp(),
					'daily',
					'wpsc_cron_daily'
				);
			}

			// license checker.
			if ( ! wp_next_scheduled( 'wpsc_license_checker' ) ) {
				wp_schedule_event(
					self::get_midnight_timestamp(),
					'daily',
					'wpsc_license_checker'
				);
			}

			// Auto-archive closed tickets.
			$auto_archive_time = isset( $advanced['auto-archive-tickets-time'] ) ? $advanced['auto-archive-tickets-time'] : 0;
			if ( $auto_archive_time > 0 && ! wp_next_scheduled( 'wpsc_auto_archive_closed_tickets' ) ) {
				wp_schedule_single_event( time(), 'wpsc_auto_archive_closed_tickets' );
			}

			// Permanently delete archive tickets.
			$permanent_archive_time = isset( $advanced['permanent-archive-tickets-time'] ) ? $advanced['permanent-archive-tickets-time'] : 0;
			if ( $permanent_archive_time > 0 && ! wp_next_scheduled( 'wpsc_permanently_delete_archive_tickets' ) ) {
				wp_schedule_single_event( time(), 'wpsc_permanently_delete_archive_tickets' );
			}

			// Permanently delete deleted tickets.
			$permanent_delete_time = isset( $advanced['permanent-delete-tickets-time'] ) ? $advanced['permanent-delete-tickets-time'] : 0;
			if ( $permanent_delete_time > 0 && ! wp_next_scheduled( 'wpsc_permanently_delete_tickets' ) ) {
				wp_schedule_single_event( time(), 'wpsc_permanently_delete_tickets' );
			}

			// Attachment garbage collector.
			if ( ! wp_next_scheduled( 'wpsc_attach_garbage_collector' ) ) {
				wp_schedule_event(
					time(),
					'hourly',
					'wpsc_attach_garbage_collector'
				);
			}
		}

		/**
		 * All cron hooks scheduled by SupportCandy core.
		 * Add every new hook scheduled by core here so that it gets removed on deactivation.
		 * Add-ons must not be added here; each add-on removes its own hooks on its deactivation.
		 *
		 * @return array
		 */
		public static function get_cron_hooks() {

			return array(
				// Recurring events.
				'wpsc_cron_five_minute',
				'wpsc_cron_daily',
				'wpsc_license_checker',
				'wpsc_attach_garbage_collector',
				'wpsc_reset_missing_counts',
				'wpsc_acb_session_check',
				'wpsc_ai_training_stale_check',
				'wpsc_v1_upgrade_cleanup',
				'wpsc_v2_upgrade_cleanup',
				// Single events.
				'wpsc_auto_archive_closed_tickets',
				'wpsc_permanently_delete_archive_tickets',
				'wpsc_permanently_delete_tickets',
				'wpsc_delete_acb_logs',
				'wpsc_delete_aia_logs',
				'wpsc_ai_training_upload',
				'wpsc_delete_ai_training_record',
				'wpsc_ait_run_sync',
				'wpsc_execute_scheduled_task',
				'wpsc_upgrade_v1',
				'wpsc_upgrade_v2',
			);
		}

		/**
		 * Remove existing scheduled events.
		 * Can be used while deactivation of plugin or resetting schedules after an update etc.
		 * These get scheduled again on init once the plugin is active.
		 *
		 * @return void
		 */
		public static function unschedule_events() {

			// wp_unschedule_hook() removes events regardless of their args.
			foreach ( self::get_cron_hooks() as $hook ) {
				wp_unschedule_hook( $hook );
			}
		}

		/**
		 * Provide mid-night unix timestamp
		 *
		 * @return String
		 */
		public static function get_midnight_timestamp() {

			$tz   = wp_timezone();
			$date = new DateTime( 'now', $tz );
			$date->setTime( 0, 0, 0 );
			$date->add( new DateInterval( 'P1D' ) );
			return $date->getTimestamp();
		}

		/**
		 * Auto archive closed ticket after x days/months/years
		 *
		 * @return void
		 */
		public static function auto_archive_closed_tickets() {

			$tz = wp_timezone();
			$today = new DateTime( 'now', $tz );
			$ms_settings = get_option( 'wpsc-ms-advanced-settings' );
			$ad_settings = get_option( 'wpsc-tl-ms-advanced' );
			$time = isset( $ms_settings['auto-archive-tickets-time'] ) ? $ms_settings['auto-archive-tickets-time'] : 0;
			$unit = isset( $ms_settings['auto-archive-tickets-unit'] ) ? $ms_settings['auto-archive-tickets-unit'] : 'days';
			if ( $time === 0 ) {
				return;
			}

			// Find the date after which tickets should be archived.
			$age = clone $today;
			switch ( $unit ) {
				case 'days':
					$age->sub( new DateInterval( 'P' . $time . 'D' ) );
					break;

				case 'month':
					$age->sub( new DateInterval( 'P' . $time . 'M' ) );
					break;

				case 'year':
					$age->sub( new DateInterval( 'P' . $time . 'Y' ) );
					break;
			}

			// Arguments for fetching closed tickets to be archived.
			$filters = array(
				'items_per_page' => 20,
				'orderby'        => 'date_closed',
				'order'          => 'ASC',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'slug'    => 'status',
						'compare' => 'IN',
						'val'     => $ad_settings['closed-ticket-statuses'],
					),
					array(
						'slug'    => 'date_closed',
						'compare' => '<',
						'val'     => $age->format( 'Y-m-d' ),
					),
				),
			);

			$filters = apply_filters( 'wpsc_auto_archive_closed_tickets_query', $filters );
			// Get tickets to be archive.
			$tickets = WPSC_Ticket::find( $filters );

			// Archive tickets.
			if ( $tickets['total_items'] > 0 ) {
				foreach ( $tickets['results'] as $ticket ) {
					WPSC_Individual_Ticket::$ticket = $ticket;
					WPSC_Individual_Ticket::archive_ticket();
				}
			}

			// Schedule next run.
			if ( $tickets['has_next_page'] ) {
				wp_schedule_single_event( time(), 'wpsc_auto_archive_closed_tickets' );
			} else {
				wp_schedule_single_event( time() + DAY_IN_SECONDS, 'wpsc_auto_archive_closed_tickets' );
			}
		}

		/**
		 * Permanently delete tickets after x days/months/years
		 *
		 * @return void
		 */
		public static function permanently_delete_archive_tickets() {

			$tz = wp_timezone();
			$today = new DateTime( 'now', $tz );
			$ms_settings = get_option( 'wpsc-ms-advanced-settings' );
			$time = isset( $ms_settings['permanent-archive-tickets-time'] ) ? $ms_settings['permanent-archive-tickets-time'] : 0;
			$unit = isset( $ms_settings['permanent-archive-tickets-unit'] ) ? $ms_settings['permanent-archive-tickets-unit'] : 'days';
			if ( $time === 0 ) {
				return;
			}

			// find the date before which tickets to be deleted.
			$age = clone $today;
			switch ( $unit ) {
				case 'days':
					$age->sub( new DateInterval( 'P' . $time . 'D' ) );
					break;

				case 'month':
					$age->sub( new DateInterval( 'P' . $time . 'M' ) );
					break;

				case 'year':
					$age->sub( new DateInterval( 'P' . $time . 'Y' ) );
					break;
			}

			// get tickets to be deleted.
			$tickets = WPSC_Archive_Ticket::find(
				array(
					'items_per_page' => 25,
					'orderby'        => 'date_updated',
					'order'          => 'ASC',
					'meta_query'     => array(
						'relation' => 'AND',
						array(
							'slug'    => 'date_updated',
							'compare' => '<',
							'val'     => $age->format( 'Y-m-d' ),
						),
					),
				)
			);

			// Delete tickets.
			if ( $tickets['total_items'] > 0 ) {
				foreach ( $tickets['results'] as $ticket ) {
					WPSC_Individual_Archive_Ticket::delete_archive_ticket( $ticket );
				}
			}

			// schedule next run.
			if ( $tickets['has_next_page'] ) {
				wp_schedule_single_event( time(), 'wpsc_permanently_delete_archive_tickets' );
			} else {
				wp_schedule_single_event( time() + DAY_IN_SECONDS, 'wpsc_permanently_delete_archive_tickets' );
			}
		}

		/**
		 * Permanently delete tickets after x days/months/years
		 *
		 * @return void
		 */
		public static function permanently_delete_tickets() {

			$tz = wp_timezone();
			$today = new DateTime( 'now', $tz );
			$ms_settings = get_option( 'wpsc-ms-advanced-settings' );
			$time = isset( $ms_settings['permanent-delete-tickets-time'] ) ? $ms_settings['permanent-delete-tickets-time'] : 0;
			$unit = isset( $ms_settings['permanent-delete-tickets-unit'] ) ? $ms_settings['permanent-delete-tickets-unit'] : 'days';
			if ( $time === 0 ) {
				return;
			}

			// find the date before which tickets to be deleted.
			$age = clone $today;
			switch ( $unit ) {
				case 'days':
					$age->sub( new DateInterval( 'P' . $time . 'D' ) );
					break;

				case 'month':
					$age->sub( new DateInterval( 'P' . $time . 'M' ) );
					break;

				case 'year':
					$age->sub( new DateInterval( 'P' . $time . 'Y' ) );
					break;
			}

			$args = array(
				'items_per_page' => 20,
				'orderby'        => 'date_updated',
				'order'          => 'ASC',
				'is_active'      => 0,
			);

			$meta_query = array( 'relation' => 'AND' );

			$meta_query[] = array(
				'slug'    => 'date_updated',
				'compare' => '<',
				'val'     => $age->format( 'Y-m-d' ),
			);

			$filters = array_merge( $args, array( 'meta_query' => $meta_query ) );
			$filters = apply_filters( 'wpsc_permanently_delete_tickets_query', $filters );

			// get tickets to be deleted.
			$tickets = WPSC_Ticket::find( $filters );

			// Delete tickets.
			if ( $tickets['total_items'] > 0 ) {
				foreach ( $tickets['results'] as $ticket ) {
					WPSC_Individual_Ticket::$ticket = $ticket;
					WPSC_Individual_Ticket::delete_permanently();
				}
			}

			// schedule next run.
			if ( $tickets['has_next_page'] ) {
				wp_schedule_single_event( time(), 'wpsc_permanently_delete_tickets' );
			} else {
				wp_schedule_single_event( time() + DAY_IN_SECONDS, 'wpsc_permanently_delete_tickets' );
			}
		}

		/**
		 * Execute background processes
		 *
		 * @return void
		 */
		public static function run_background_process() {

			do_action( 'wpsc_run_ajax_background_process' );
			wp_die();
		}
	}
endif;

WPSC_Cron::init();
