<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_Elementor_Widget' ) ) :

	/**
	 * One class for all SupportCandy widgets. Elementor passes the registered default args
	 * to every instance it creates, so the shortcode is known when rendering saved data.
	 */
	class WPSC_Elementor_Widget extends \Elementor\Widget_Base {

		/**
		 * Widget args: widget_name, shortcode, title, description
		 *
		 * @var array
		 */
		private $wpsc_args = array();

		/**
		 * Constructor
		 *
		 * @param array      $data - element data.
		 * @param array|null $args - widget args.
		 */
		public function __construct( $data = array(), $args = null ) {

			$this->wpsc_args = $args ? $args : array();
			parent::__construct( $data, $args );
		}

		/**
		 * Widget name
		 *
		 * @return string
		 */
		public function get_name() {

			return $this->wpsc_args['widget_name'];
		}

		/**
		 * Widget title
		 *
		 * @return string
		 */
		public function get_title() {

			return $this->wpsc_args['title'];
		}

		/**
		 * Widget icon
		 *
		 * @return string
		 */
		public function get_icon() {

			return 'eicon-headphones';
		}

		/**
		 * Widget categories
		 *
		 * @return array
		 */
		public function get_categories() {

			return array( 'supportcandy' );
		}

		/**
		 * Widget search keywords
		 *
		 * @return array
		 */
		public function get_keywords() {

			return array( 'supportcandy', 'support', 'ticket', 'helpdesk', $this->wpsc_args['shortcode'] );
		}

		/**
		 * No inner wrapper div needed (lighter DOM)
		 *
		 * @return bool
		 */
		public function has_widget_inner_wrapper(): bool {

			return false;
		}

		/**
		 * Render the shortcode on the frontend, a placeholder in the editor.
		 * The SupportCandy interface loads over AJAX and is not usable inside the editor.
		 *
		 * @return void
		 */
		protected function render() {

			$plugin = \Elementor\Plugin::$instance;
			if ( $plugin->editor->is_edit_mode() || $plugin->preview->is_preview_mode() ) {
				?>
				<div style="padding:16px;border:1px dashed #8c8f94;border-radius:4px;background:#f6f7f7;color:#1d2327;">
					<strong><?php echo esc_html( 'SupportCandy: ' . $this->wpsc_args['title'] ); ?></strong>
					<p style="margin:4px 0;"><?php echo esc_html( $this->wpsc_args['description'] ); ?></p>
					<code>[<?php echo esc_html( $this->wpsc_args['shortcode'] ); ?>]</code>
				</div>
				<?php
				return;
			}

			echo do_shortcode( '[' . $this->wpsc_args['shortcode'] . ']' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Plain content saved to post content: the shortcode itself, like Elementor's
		 * Shortcode widget. Keeps 'auto' load scripts detection and search working, and
		 * the page still works if Elementor is deactivated.
		 *
		 * @return void
		 */
		public function render_plain_content() {

			echo '[' . esc_html( $this->wpsc_args['shortcode'] ) . ']';
		}
	}
endif;
