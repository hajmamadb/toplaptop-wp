<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_PS_AIT_Cron' ) ) :

	final class WPSC_PS_AIT_Cron {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// Register custom cron schedules.
			add_filter( 'cron_schedules', array( __CLASS__, 'register_cron_schedules' ) ); //phpcs:ignore

			// schedule cron jobs.
			add_action( 'wpsc_ai_training_upload', array( __CLASS__, 'ai_training_upload' ) );
			add_action( 'wpsc_delete_ai_training_record', array( __CLASS__, 'delete_ai_training_record' ) );

			// Periodic check to requeue/fail training files stuck in PROCESSING.
			add_action( 'init', array( __CLASS__, 'schedule_stale_processing_check' ) );
			add_action( 'wpsc_ai_training_stale_check', array( __CLASS__, 'stale_processing_check' ) );
		}

		/**
		 * Register custom cron schedules.
		 *
		 * @param array $schedules Existing schedules.
		 * @return array
		 */
		public static function register_cron_schedules( $schedules ) {

			if ( ! isset( $schedules['wpsc_every_fifteen_minutes'] ) ) {
				$schedules['wpsc_every_fifteen_minutes'] = array(
					'interval' => 15 * MINUTE_IN_SECONDS,
					'display'  => __( 'Every 15 Minutes', 'supportcandy' ),
				);
			}

			return $schedules;
		}

		/**
		 * Ensure the stale-processing check runs on a recurring schedule, independent of
		 * whether new files are currently being queued (which is what normally triggers
		 * the single-shot upload cron).
		 *
		 * @return void
		 */
		public static function schedule_stale_processing_check() {

			$hook = 'wpsc_ai_training_stale_check';
			$recurrence = 'wpsc_every_fifteen_minutes';

			$event = wp_get_scheduled_event( $hook );

			// Compare the interval, not the schedule name: cron-to-Action-Scheduler bridges store
			// only the interval and can report another schedule name with the same interval, which
			// would reschedule on every request.
			if ( ! $event ) {
				wp_schedule_event( time(), $recurrence, $hook );
			} elseif ( empty( $event->interval ) || 15 * MINUTE_IN_SECONDS !== (int) $event->interval ) {
				wp_clear_scheduled_hook( $hook );
				wp_schedule_event( time(), $recurrence, $hook );
			}
		}

		/**
		 * Requeue or fail training files stuck in PROCESSING status.
		 *
		 * @return void
		 */
		public static function stale_processing_check() {

			// Resume or give up on any post-type sync jobs stuck mid-chain (a lost cron
			// tick, a crashed request) regardless of whether an admin has the training
			// source's settings screen open - see recover_all_stalled_syncs(). This does
			// not depend on the AI assistant being active, since a sync can be left running
			// from before it was disabled.
			WPSC_PS_AI_Setting_AI_Training_Actions::recover_all_stalled_syncs();

			$ai_settings = get_option( 'wpsc-ps-ai-assistant-settings', array() );
			if ( empty( $ai_settings['is-active'] ) ) {
				return;
			}

			WPSC_PS_AIT_Controller::reset_stale_processing_files();
			self::resume_pending_queue( $ai_settings );
		}

		/**
		 * Reschedule upload/delete for training files still waiting in the queue.
		 * Their single events get removed on plugin deactivation, so this picks the queue up again after reactivation.
		 *
		 * @param array $ai_settings AI assistant settings.
		 * @return void
		 */
		public static function resume_pending_queue( $ai_settings ) {

			$provider = isset( $ai_settings['provider'] ) ? $ai_settings['provider'] : '';
			if ( ! $provider ) {
				return;
			}

			$queues = array(
				'wpsc_ai_training_upload'        => WPSC_PS_AIT_Status::NEW,
				'wpsc_delete_ai_training_record' => WPSC_PS_AIT_Status::DELETE,
			);

			foreach ( $queues as $hook => $status ) {

				if ( wp_next_scheduled( $hook ) ) {
					continue;
				}

				// Upload waits for active syncs; their finalize step kicks it off.
				if ( 'wpsc_ai_training_upload' === $hook && WPSC_PS_AI_Setting_AI_Training_Actions::is_any_sync_active() ) {
					continue;
				}

				$pending = WPSC_RAG_Training_File::find(
					array(
						'items_per_page' => 1,
						'meta_query'     => array(
							'relation' => 'AND',
							array(
								'slug'    => 'status',
								'compare' => '=',
								'val'     => $status,
							),
							array(
								'slug'    => 'provider',
								'compare' => '=',
								'val'     => $provider,
							),
						),
					)
				)['results'];

				if ( $pending ) {
					wp_schedule_single_event( time(), $hook );
				}
			}
		}

		/**
		 * Execute AI training upload cron
		 */
		public static function ai_training_upload() {

			$ai_settings = get_option( 'wpsc-ps-ai-assistant-settings', array() );
			if ( empty( $ai_settings['is-active'] ) ) {
				return;
			}

			WPSC_PS_AIT_Controller::upload_file_to_training( $ai_settings );
		}

		/**
		 * Execute delete AI training record cron
		 */
		public static function delete_ai_training_record() {

			$ai_settings = get_option( 'wpsc-ps-ai-assistant-settings', array() );
			if ( empty( $ai_settings['is-active'] ) ) {
				return;
			}

			WPSC_PS_AIT_Controller::delete_ai_training_record( $ai_settings );
		}
	}
endif;
WPSC_PS_AIT_Cron::init();
