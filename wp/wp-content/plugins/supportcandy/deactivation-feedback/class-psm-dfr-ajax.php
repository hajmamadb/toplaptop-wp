<?php
/**
 * AJAX handler.
 *
 * @package PSM_DFR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PSM_DFR_Ajax {

	/**
	 * Init.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_ajax_psm_dfr_submit', array( __CLASS__, 'submit' ) );
	}

	/**
	 * Submit feedback.
	 *
	 * @return void
	 */
	public static function submit() {

		check_ajax_referer( 'psm_dfr_nonce', 'nonce' );

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error();
		}

		$plugin_file = isset( $_POST['plugin_file'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_file'] ) ) : '';
		$reason      = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';
		$note        = isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '';

		$plugins = PSM_DFR::get_plugins();

		if ( empty( $plugins[ $plugin_file ] ) ) {
			wp_send_json_error();
		}

		$config  = $plugins[ $plugin_file ];
		$reasons = PSM_DFR::get_reasons( $config );

		if ( ! isset( $reasons[ $reason ] ) ) {
			wp_send_json_error();
		}

		$payload = array(
			'plugin_file'    => $config['plugin_file'],
			'plugin_name'    => $config['plugin_name'],
			'plugin_version' => $config['plugin_version'],
			'reason'         => $reason,
			'note'           => $note,
			'wp_version'     => get_bloginfo( 'version' ),
			'php_version'    => PHP_VERSION,
			'website'        => '',
			'context'        => PSM_DFR::get_context( $config ),
		);

		$body = wp_json_encode( $payload );

		// Legacy signature covers the original fields only, so receivers that
		// rebuild that fixed set keep accepting requests. V2 signs the full body.
		$legacy = wp_json_encode(
			array(
				'plugin_file'    => $payload['plugin_file'],
				'plugin_name'    => $payload['plugin_name'],
				'plugin_version' => $payload['plugin_version'],
				'reason'         => $payload['reason'],
				'note'           => $payload['note'],
				'wp_version'     => $payload['wp_version'],
				'php_version'    => $payload['php_version'],
				'website'        => '',
			)
		);

		wp_remote_post(
			esc_url_raw( $config['endpoint'] ),
			array(
				'timeout'  => 3,
				'blocking' => true,
				'headers'  => array(
					'Content-Type'         => 'application/json',
					'X-PSMDF-API-KEY'      => $config['api_key'],
					'X-PSMDF-SIGNATURE'    => hash_hmac( 'sha256', $legacy, $config['api_key'] ),
					'X-PSMDF-SIGNATURE-V2' => hash_hmac( 'sha256', $body, $config['api_key'] ),
				),
				'body'     => $body,
			)
		);

		wp_send_json_success();
	}
}
PSM_DFR_Ajax::init();
