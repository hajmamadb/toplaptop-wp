<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_PS_AI_OpenAI' ) ) :

	final class WPSC_PS_AI_OpenAI {

		/**
		 * Model tiers, lowest to highest cost. The low tier is the default model, and the
		 * mid/pro tiers are what resolve_retry_model() escalates to on a retry. The GPT-4o and
		 * GPT-4.1 models used before these are retired by OpenAI on January 14, 2027.
		 */
		const MODEL_LOW = 'gpt-6-luna';
		const MODEL_MID = 'gpt-6.1-terra';
		const MODEL_PRO = 'gpt-6.1-sol';

		/**
		 * Mark a training file as failed with an error message
		 *
		 * @param string $api_key API key for authentication.
		 * @return mixed
		 */
		public static function wpsc_provider_store_id( $api_key ) {

			// Try fetching stored ID.
			$stored = get_option( 'wpsc_openai_vector_store_id', '' );

			if ( ! empty( $stored ) && is_string( $stored ) ) {
				return sanitize_text_field( $stored );
			}

			// Prepare request body.
			$body = array(
				'name'     => '[' . esc_url( home_url() ) . '] ' . sanitize_text_field( get_bloginfo( 'name' ) ),
				'metadata' => array(
					'site_title'  => sanitize_text_field( get_bloginfo( 'name' ) ),
					'site_url'    => esc_url( home_url() ),
					'site_host'   => sanitize_text_field( wp_parse_url( home_url(), PHP_URL_HOST ) ),
					'plugin'      => 'supportcandy',
					'environment' => defined( 'WP_ENV' ) ? sanitize_text_field( WP_ENV ) : 'production',
				),
			);

			// Make API request.
			$response = wp_remote_post(
				'https://api.openai.com/v1/vector_stores',
				array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $api_key,
						'Content-Type'  => 'application/json',
					),
					'body'    => wp_json_encode( $body ),
					'timeout' => 60,
				)
			);

			// Handle WP_Error.
			if ( is_wp_error( $response ) ) {
				return false;
			}

			$code = wp_remote_retrieve_response_code( $response );
			$body = wp_remote_retrieve_body( $response );

			$data = json_decode( $body, true );

			if ( $code < 200 || $code >= 300 ) {
				return false;
			}

			// Validate response structure.
			if ( empty( $data ) || ! is_array( $data ) ) {
				return false;
			}

			// Check API-level error.
			if ( isset( $data['error'] ) ) {
				return false;
			}

			// Validate ID.
			if ( empty( $data['id'] ) || ! is_string( $data['id'] ) ) {
				return false;
			}

			$vector_store_id = sanitize_text_field( $data['id'] );

			// Save safely.
			update_option( 'wpsc_openai_vector_store_id', $vector_store_id, false );
			return $vector_store_id;
		}

		/**
		 * Clear the cached vector store ID so the next call to wpsc_provider_store_id()
		 * creates a fresh store under whichever API key/project is currently configured.
		 * Needed because vector stores are project-scoped in OpenAI: a cached ID created
		 * under one project is not reachable from a key belonging to a different project
		 * (e.g. after rotating to a new API key), and it is never re-validated on its own.
		 *
		 * @return void
		 */
		public static function clear_stored_vector_store_id() {

			delete_option( 'wpsc_openai_vector_store_id' );
		}

		/**
		 * Resolve the model to use for retry attempts based on the current attempt number.
		 *
		 * @param string $model The original model name.
		 * @param int    $attempt The current attempt number (1-based).
		 * @return string The resolved model name for the retry attempt.
		 */
		public static function resolve_retry_model( string $model, int $attempt ): string {

			switch ( $attempt ) {
				case 1:
					return $model;
				case 2:
					return self::MODEL_MID;
				default:
					return self::MODEL_PRO;
			}
		}

		/**
		 * Reasoning settings for a model, or null for a model that does not reason.
		 *
		 * The GPT-6 models reason before they answer, and those reasoning tokens are drawn from
		 * max_output_tokens - left on the default effort, a model can spend the whole budget and
		 * return no text at all (status "incomplete"). So effort is set as low as each model
		 * allows, and each model rejects the values it does not support (a 400 error):
		 * - the low and mid tiers accept 'none' (no reasoning);
		 * - every other GPT-6 model needs at least 'low'.
		 * Models that do not reason (GPT-4o, GPT-4.1) reject a reasoning parameter altogether.
		 *
		 * @param string $model Model name.
		 * @return array|null Value for the request's "reasoning" parameter.
		 */
		public static function reasoning_config( string $model ) {

			$model = strtolower( $model );

			if ( in_array( $model, array( self::MODEL_LOW, self::MODEL_MID ), true ) ) {
				return array( 'effort' => 'none' );
			}

			if ( 0 === strpos( $model, 'gpt-6' ) ) {
				return array( 'effort' => 'low' );
			}

			return null;
		}

		/**
		 * Model-dependent parameters for a Responses API request: the model, its reasoning
		 * effort, the output token limit (with room for reasoning on models that cannot turn it
		 * off), and the sampling temperature, which reasoning models reject (a 400 error) and
		 * so is only sent to models that do not reason.
		 *
		 * @param string     $model       Model used for this attempt.
		 * @param int        $max_tokens  Tokens wanted for the reply itself.
		 * @param float|null $temperature Sampling temperature, or null to leave the default.
		 * @return array Request keys to merge into the request body.
		 */
		public static function request_params( string $model, int $max_tokens, $temperature = null ): array {

			$params    = array( 'model' => $model );
			$reasoning = self::reasoning_config( $model );
			$headroom  = 0;

			if ( $reasoning ) {
				$params['reasoning'] = $reasoning;
				$headroom            = 'none' === $reasoning['effort'] ? 0 : 512;
			} elseif ( null !== $temperature ) {
				$params['temperature'] = $temperature;
			}

			$params['max_output_tokens'] = $max_tokens + $headroom;
			return $params;
		}

		/**
		 * Determine if the response from the API is retryable based on status code or error.
		 *
		 * @param array $response The response from the API.
		 * @return bool True if the response is retryable, false otherwise.
		 */
		public static function is_retryable_response( $response ) {

			if ( is_wp_error( $response ) ) {
				return true;
			}

			$status_code = wp_remote_retrieve_response_code( $response );
			if ( $status_code >= 500 ) {
				return true;
			}

			if ( 429 === $status_code ) {
				return true;
			}

			return false;
		}
	}
endif;
