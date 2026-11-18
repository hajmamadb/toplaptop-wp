<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_Elementor' ) ) :

	final class WPSC_Elementor {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// These hooks only fire when Elementor is active.
			add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'register_category' ) );
			add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );

			// load SupportCandy scripts on Elementor pages having our widgets ('auto' load scripts mode).
			add_filter( 'wpsc_load_frontend_scripts', array( __CLASS__, 'load_frontend_scripts' ), 10, 3 );
		}

		/**
		 * Register "SupportCandy" widget category.
		 *
		 * @param \Elementor\Elements_Manager $elements_manager - elements manager.
		 * @return void
		 */
		public static function register_category( $elements_manager ) {

			$elements_manager->add_category(
				'supportcandy',
				array(
					'title' => __( 'SupportCandy', 'supportcandy' ),
					'icon'  => 'eicon-headphones',
				)
			);
		}

		/**
		 * Register one widget per shortcode, same list as the Gutenberg blocks.
		 *
		 * @param \Elementor\Widgets_Manager $widgets_manager - widgets manager.
		 * @return void
		 */
		public static function register_widgets( $widgets_manager ) {

			include_once WPSC_ABSPATH . 'includes/blocks/class-wpsc-elementor-widget.php';

			foreach ( WPSC_Blocks::get_blocks() as $name => $block ) {
				$widgets_manager->register(
					new WPSC_Elementor_Widget(
						array(),
						array(
							'widget_name' => self::get_widget_name( $name ),
							'shortcode'   => $block['shortcode'],
							'title'       => $block['title'],
							'description' => $block['description'],
						)
					)
				);
			}
		}

		/**
		 * Elementor widget name for a block name, e.g. supportcandy/support => supportcandy-support.
		 *
		 * @param string $block_name - block name.
		 * @return string
		 */
		public static function get_widget_name( $block_name ) {

			return str_replace( '/', '-', $block_name );
		}

		/**
		 * Our widgets also save their shortcode in post content (plain content fallback), but
		 * that copy can be stale, e.g. for imported pages. Check the Elementor data as well.
		 *
		 * @param boolean $load - whether to load scripts.
		 * @param array   $page_settings - page settings.
		 * @param string  $mode - load scripts mode.
		 * @return boolean
		 */
		public static function load_frontend_scripts( $load, $page_settings = array(), $mode = '' ) {

			if ( $load || 'auto' !== $mode || ! is_singular() ) {
				return $load;
			}

			$post_id = get_queried_object_id();
			if ( ! $post_id || get_post_meta( $post_id, '_elementor_edit_mode', true ) !== 'builder' ) {
				return $load;
			}

			$data = get_post_meta( $post_id, '_elementor_data', true );
			if ( ! is_string( $data ) || ! $data ) {
				return $load;
			}

			foreach ( WPSC_Blocks::get_block_names() as $name ) {
				if ( strpos( $data, '"widgetType":"' . self::get_widget_name( $name ) . '"' ) !== false ) {
					return true;
				}
			}

			return $load;
		}
	}
endif;

WPSC_Elementor::init();
