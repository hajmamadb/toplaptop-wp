<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_Blocks' ) ) :

	final class WPSC_Blocks {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			add_action( 'init', array( __CLASS__, 'register_blocks' ) );
			add_filter( 'block_categories_all', array( __CLASS__, 'register_category' ) );
		}

		/**
		 * Block name => shortcode, title and description. Shortcodes take no attributes.
		 *
		 * @return array
		 */
		public static function get_blocks() {

			return array(
				'supportcandy/support'                 => array(
					'shortcode'   => 'supportcandy',
					'title'       => __( 'Support', 'supportcandy' ),
					'description' => __( 'Full customer interface: ticket list, new ticket form and ticket view.', 'supportcandy' ),
				),
				'supportcandy/create-ticket'           => array(
					'shortcode'   => 'wpsc_create_ticket',
					'title'       => __( 'Create ticket', 'supportcandy' ),
					'description' => __( 'New ticket form only.', 'supportcandy' ),
				),
				'supportcandy/open-ticket'             => array(
					'shortcode'   => 'wpsc_open_ticket',
					'title'       => __( 'Ticket lookup', 'supportcandy' ),
					'description' => __( 'Lets guests look up an existing ticket.', 'supportcandy' ),
				),
				'supportcandy/my-profile'              => array(
					'shortcode'   => 'wpsc_my_profile',
					'title'       => __( 'My profile', 'supportcandy' ),
					'description' => __( 'Customer profile of the logged-in user.', 'supportcandy' ),
				),
				'supportcandy/agent-profile'           => array(
					'shortcode'   => 'wpsc_agent_profile',
					'title'       => __( 'Agent profile', 'supportcandy' ),
					'description' => __( 'Agent profile of the logged-in agent.', 'supportcandy' ),
				),
				'supportcandy/unresolved-ticket-count' => array(
					'shortcode'   => 'wpsc_unresolved_ticket_count',
					'title'       => __( 'Unresolved ticket count', 'supportcandy' ),
					'description' => __( 'Number of unresolved tickets of the logged-in user.', 'supportcandy' ),
				),
			);
		}

		/**
		 * Registered block names.
		 *
		 * @return array
		 */
		public static function get_block_names() {

			return array_keys( self::get_blocks() );
		}

		/**
		 * Register "SupportCandy" block category.
		 *
		 * @param array $categories existing categories.
		 * @return array
		 */
		public static function register_category( $categories ) {

			$categories[] = array(
				'slug'  => 'supportcandy',
				'title' => __( 'SupportCandy', 'supportcandy' ),
			);
			return $categories;
		}

		/**
		 * Register one server-rendered block per shortcode.
		 *
		 * @return void
		 */
		public static function register_blocks() {

			if ( ! function_exists( 'register_block_type' ) ) {
				return;
			}

			wp_register_script(
				'wpsc-blocks',
				WPSC_PLUGIN_URL . 'asset/js/blocks.js',
				array( 'wp-blocks', 'wp-element', 'wp-block-editor' ),
				WPSC_VERSION,
				true
			);
			wp_localize_script( 'wpsc-blocks', 'wpscBlocks', array( 'blocks' => self::get_blocks() ) );

			foreach ( self::get_blocks() as $name => $block ) {
				register_block_type(
					$name,
					array(
						'api_version'     => 2,
						'editor_script'   => 'wpsc-blocks',
						'render_callback' => function () use ( $block ) {
							return do_shortcode( '[' . $block['shortcode'] . ']' );
						},
					)
				);
			}
		}
	}
endif;

WPSC_Blocks::init();
