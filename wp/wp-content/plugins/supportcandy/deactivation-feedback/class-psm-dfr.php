<?php
/**
 * PSM Deactivation Feedback Framework
 *
 * @package PSM_DFR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'PSM_DFR', false ) ) {
	return;
}

final class PSM_DFR {

	/**
	 * Registered plugins.
	 *
	 * @var array
	 */
	private static $plugins = array();

	/**
	 * Framework booted.
	 *
	 * @var bool
	 */
	private static $booted = false;

	/**
	 * Register plugin.
	 *
	 * @param array $config Plugin configuration.
	 *
	 * @return void
	 */
	public static function init( $config ) {

		$defaults = array(
			'plugin_file'      => '',
			'plugin_name'      => '',
			'plugin_version'   => '',
			'endpoint'         => '',
			'api_key'          => '',
			'reasons'          => array(),
			'context_callback' => null,
		);

		$config = wp_parse_args( $config, $defaults );

		if ( empty( $config['plugin_file'] ) ) {
			return;
		}

		self::$plugins[ $config['plugin_file'] ] = $config;

		if ( self::$booted ) {
			return;
		}

		self::$booted = true;

		require_once __DIR__ . '/class-psm-dfr-admin.php';
		require_once __DIR__ . '/class-psm-dfr-ajax.php';
	}

	/**
	 * Get plugins.
	 *
	 * @return array
	 */
	public static function get_plugins() {
		return self::$plugins;
	}

	/**
	 * Get normalized reasons of a plugin.
	 *
	 * Reasons can be passed as an array or as a callable returning an array
	 * (lets plugins translate labels after the textdomain is loaded). Each
	 * reason can be a plain label string or an array with the keys:
	 * label, note_placeholder, note_required.
	 *
	 * @param array $config Plugin configuration.
	 * @return array
	 */
	public static function get_reasons( $config ) {

		$reasons = is_callable( $config['reasons'] ) ? call_user_func( $config['reasons'] ) : $config['reasons'];

		$normalized = array();
		foreach ( (array) $reasons as $key => $reason ) {

			if ( ! is_array( $reason ) ) {
				$reason = array( 'label' => $reason );
			}

			$reason = wp_parse_args(
				$reason,
				array(
					'label'            => '',
					'note_placeholder' => '',
					'note_required'    => false,
				)
			);

			$normalized[ (string) $key ] = $reason;
		}

		return $normalized;
	}

	/**
	 * Get anonymous usage context of a plugin.
	 *
	 * @param array $config Plugin configuration.
	 * @return array
	 */
	public static function get_context( $config ) {

		if ( ! is_callable( $config['context_callback'] ) ) {
			return array();
		}

		$context = call_user_func( $config['context_callback'] );
		if ( ! is_array( $context ) ) {
			return array();
		}

		// Allow scalars only.
		$clean = array();
		foreach ( $context as $key => $value ) {
			if ( is_scalar( $value ) || is_null( $value ) ) {
				$clean[ sanitize_key( $key ) ] = $value;
			}
		}

		return $clean;
	}
}
