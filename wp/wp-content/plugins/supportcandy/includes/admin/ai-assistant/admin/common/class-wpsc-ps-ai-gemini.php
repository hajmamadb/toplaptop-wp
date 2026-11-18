<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_PS_AI_Gemini' ) ) :

	final class WPSC_PS_AI_Gemini {

		/**
		 * Model tiers, lowest to highest cost. The low tier is the default model, and the
		 * mid/pro tiers are what resolve_retry_model() escalates to on a retry.
		 * Chosen from the models that every Gemini API user can call and that support the
		 * File Search tool - the Gemini 2.5 family is only available to projects that used it
		 * before, so a new user's key cannot reach it. Models stored before these tiers (the 2.5
		 * family) keep working for users that had access - see thinking_config().
		 */
		const MODEL_LOW = 'gemini-3.5-flash-lite';
		const MODEL_MID = 'gemini-3.6-flash';
		const MODEL_PRO = 'gemini-4-argon';

		/**
		 * Placeholder accepted in place of a real thought signature. Gemini 3 and later reject a
		 * replayed functionCall that has none, and a tool call we recovered from text has no real one.
		 */
		const DUMMY_THOUGHT_SIGNATURE = 'skip_thought_signature_validator';

		/**
		 * Mark a training file as failed with an error message
		 *
		 * @param string $api_key API key for authentication.
		 * @return mixed
		 */
		public static function wpsc_provider_store_id( $api_key ) {

			// Try fetching stored ID.
			$stored = get_option( 'wpsc_gemini_file_search_store_id', '' );
			if ( ! empty( $stored ) && is_string( $stored ) ) {
				return sanitize_text_field( $stored );
			}

			// API endpoint (IMPORTANT: API key in URL).
			$url = add_query_arg(
				array( 'key' => $api_key ),
				'https://generativelanguage.googleapis.com/v1beta/fileSearchStores'
			);

			// Request body. Site URL is prefixed in square brackets so the store can be
			// told apart from other sites' stores under the same Google AI project - a
			// random suffix is kept so re-creating a store for the same site (e.g. after
			// clear_stored_file_search_store_id()) doesn't collide with the old one.
			$display_name = '[' . home_url() . '] ' . get_bloginfo( 'name' ) . '_' . wp_generate_password( 6, false );
			$display_name = sanitize_text_field( $display_name );
			$display_name = function_exists( 'mb_substr' )
				? mb_substr( $display_name, 0, 512, 'UTF-8' )
				: substr( $display_name, 0, 512 );

			$body = array(
				'displayName' => $display_name,
			);

			$response = self::wpsc_remote_post( $url, $body );

			// Handle error.
			if ( is_wp_error( $response ) ) {
				return $response;
			}

			// Validate response.
			if ( empty( $response['name'] ) ) {
				return new WP_Error( 'invalid_response', __( 'Invalid API response: missing store ID.', 'supportcandy' ) );
			}

			$file_search_store_id = sanitize_text_field( $response['name'] );

			// Save option (no autoload for performance).
			update_option( 'wpsc_gemini_file_search_store_id', $file_search_store_id, false );
			return $file_search_store_id;
		}

		/**
		 * Clear the cached file search store ID so the next call to wpsc_provider_store_id()
		 * creates a fresh store under whichever API key/project is currently configured.
		 * Needed because file search stores are project-scoped in Google AI: a cached ID
		 * created under one project is not reachable from a key belonging to a different
		 * project (e.g. after rotating to a new API key), and it is never re-validated on
		 * its own.
		 *
		 * @return void
		 */
		public static function clear_stored_file_search_store_id() {

			delete_option( 'wpsc_gemini_file_search_store_id' );
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
		 * Thinking settings that keep a model from spending its output budget on reasoning.
		 *
		 * Thinking tokens are drawn from maxOutputTokens, so a model left on its default can use
		 * the whole budget and return an empty reply. The way to limit it differs by family, and
		 * a value one family accepts is rejected by another (a 400 error), so this is the only
		 * place that decides it:
		 * - Gemini 2.5: thinkingBudget. Flash/Flash-Lite accept 0 (off), Pro needs at least 128.
		 * - Gemini 3 and later: thinkingLevel; thinkingBudget 0 is rejected by Flash-Lite. The
		 *   low and mid tiers accept 'minimal'. 'low' is the lowest level accepted by every
		 *   other model (Gemini 4 Argon, 3.8 Flash and Pro reject 'minimal').
		 *
		 * @param string $model Model name.
		 * @return array|null thinkingConfig value, or null to leave the model's default.
		 */
		public static function thinking_config( string $model ) {

			$model = strtolower( $model );

			if ( 0 === strpos( $model, 'gemini-2.5-' ) ) {
				return array( 'thinkingBudget' => false !== strpos( $model, 'pro' ) ? 128 : 0 );
			}

			if ( in_array( $model, array( self::MODEL_LOW, self::MODEL_MID ), true ) ) {
				return array( 'thinkingLevel' => 'minimal' );
			}

			if ( 0 === strpos( $model, 'gemini-3' ) || 0 === strpos( $model, 'gemini-4' ) ) {
				return array( 'thinkingLevel' => 'low' );
			}

			return null;
		}

		/**
		 * Build the generationConfig for a request, with the thinking limit for the model and
		 * extra output tokens for models that cannot turn thinking fully off (their reasoning
		 * is drawn from the same budget, so the reply would otherwise be cut short).
		 *
		 * @param string $model      Model name used for this attempt.
		 * @param int    $max_tokens Tokens wanted for the reply itself.
		 * @param array  $extra      Other generationConfig keys (temperature, responseMimeType...).
		 * @return array
		 */
		public static function generation_config( string $model, int $max_tokens, array $extra = array() ): array {

			$config   = $extra;
			$thinking = self::thinking_config( $model );
			$headroom = 0;

			if ( $thinking ) {
				$config['thinkingConfig'] = $thinking;
				if ( isset( $thinking['thinkingBudget'] ) ) {
					$headroom = (int) $thinking['thinkingBudget'];
				} elseif ( 'minimal' !== ( $thinking['thinkingLevel'] ?? '' ) ) {
					$headroom = 512;
				}
			}

			$config['maxOutputTokens'] = $max_tokens + $headroom;
			return $config;
		}

		/**
		 * Concatenate the reply text of a generateContent response. A reply can be split across
		 * parts (a part may carry only a thought signature, and thought summaries are separate
		 * parts), so reading just the first part can miss it.
		 *
		 * @param mixed $body Decoded response body.
		 * @return string
		 */
		public static function extract_text( $body ): string {

			$text = '';
			foreach ( $body['candidates'][0]['content']['parts'] ?? array() as $part ) {
				if ( isset( $part['text'] ) && is_string( $part['text'] ) && empty( $part['thought'] ) ) {
					$text .= $part['text'];
				}
			}
			return trim( $text );
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

		/**
		 * Send a POST request to a remote URL with JSON-encoded body and API key authentication.
		 *
		 * @param string $url The URL to send the request to.
		 * @param array  $body The body of the request.
		 * @return array The decoded JSON response or an empty array on error.
		 */
		private static function wpsc_remote_post( $url, $body = array() ) {

			$args = array(
				'headers'     => array(
					'Content-Type' => 'application/json',
				),
				'body'        => ! empty( $body ) ? wp_json_encode( $body ) : null,
				'timeout'     => 60,
				'data_format' => 'body',
			);

			$response = wp_remote_post( esc_url_raw( $url ), $args );

			// Transport error.
			if ( is_wp_error( $response ) ) {
				return new WP_Error(
					'http_request_failed',
					$response->get_error_message()
				);
			}

			$status_code = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );

			// Decode JSON.
			$data = json_decode( $response_body, true );

			// Invalid JSON.
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				return new WP_Error(
					'invalid_json',
					__( 'Invalid JSON response from API.', 'supportcandy' )
				);
			}

			// Handle API errors.
			if ( $status_code < 200 || $status_code >= 300 ) {

				$error_message = isset( $data['error']['message'] )
					? $data['error']['message']
					: __( 'Unknown API error.', 'supportcandy' );

				return new WP_Error(
					'api_error',
					sprintf(
						/* translators: %1$d: HTTP status code, %2$s: Error message */
						__( 'API request failed with status %1$d: %2$s', 'supportcandy' ),
						$status_code,
						sanitize_text_field( $error_message )
					)
				);
			}

			return $data;
		}
	}
endif;
