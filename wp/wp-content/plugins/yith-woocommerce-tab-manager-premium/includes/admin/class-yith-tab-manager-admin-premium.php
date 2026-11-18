<?php
/**
 * This class manage the ADMIN Premium features
 *
 * @package YITH\TabManager\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Tab_Manager_Admin_Premium' ) ) {
	/**
	 * The admin premium class
	 */
	class YITH_Tab_Manager_Admin_Premium extends YITH_Tab_Manager_Admin {

		/**
		 * The construct
		 */
		protected function __construct() {
			parent::__construct();
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_updates' ), 99 );
			add_filter(
				'pre_update_option_ywtm_contact_recipient',
				array(
					$this,
					'sync_with_woocommerce_email_recipient',
				),
				20,
				2
			);
			add_filter(
				'woocommerce_product_write_panel_tabs',
				array(
					$this,
					'add_customized_product_tabs_edit',
				),
				15
			);
			add_filter( 'woocommerce_product_write_panel_tabs', array( $this, 'add_woocommerce_tabs_edit' ), 20 );
			add_action( 'woocommerce_process_product_meta', array( $this, 'save_custom_product_meta' ), 20 );
			add_action(
				'woocommerce_process_product_meta',
				array(
					$this,
					'save_custom_woocommerce_product_meta',
				),
				30
			);
		}

		/**
		 * Add the action links to plugin admin page.
		 *
		 * @param array  $new_row_meta_args Plugin Meta New args.
		 * @param array  $plugin_meta Plugin Meta.
		 * @param string $plugin_file Plugin file.
		 * @param array  $plugin_data Plugin data.
		 * @param string $status Status.
		 * @param string $init_file Init file.
		 *
		 * @return   Array
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use plugin_row_meta
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YWTM_INIT' ) {
			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['slug']       = YWTM_SLUG;
				$new_row_meta_args['is_premium'] = true;
			}

			return $new_row_meta_args;
		}

		/**
		 * Include the Post type admin class
		 *
		 * @return void
		 */
		public function add_post_type_admin() {
			YITH_Tab_Manager_Post_Type_Admin_Premium::instance();
		}

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since    2.0.0
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once YWTM_DIR . 'plugin-fw/licence/lib/yit-licence.php';
				require_once YWTM_DIR . 'plugin-fw/licence/lib/yit-plugin-licence.php';
			}
			YIT_Plugin_Licence()->register( YWTM_INIT, YWTM_SECRET_KEY, YWTM_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since    1.0.0
		 */
		public function register_plugin_for_updates() {
			if ( ! class_exists( 'YIT_Upgrade' ) ) {
				require_once YWTM_DIR . 'plugin-fw/lib/yit-upgrade.php';
			}
			YIT_Upgrade()->register( YWTM_SLUG, YWTM_INIT );
		}

		/**
		 * Add custom type in meta-box
		 *
		 * @param string $field_path The field path.
		 * @param array  $field The field.
		 *
		 * @override
		 * @return string
		 */
		public function add_custom_types( $field_path, $field ) {

			$custom_types = array(
				'ywtm-modal-editor',
				'ywtm-icon-list',
				'ywtm-video',
				'ywtm-media-gallery',
				'ywtm-contact-form',
				'ywtm-faq',
				'ywtm-download',
			);

			if ( in_array( $field['type'], $custom_types, true ) ) {
				$field_name = str_replace( 'ywtm-', '', $field['type'] );
				$field_path = YWTM_DIR . '/views/meta-boxes/types/' . $field_name . '.php';
			}

			return $field_path;
		}

		/**
		 * Get the admin tabs
		 *
		 * @return array[]
		 */
		public function get_admin_tabs() {
			$tabs = parent::get_admin_tabs();

			$premium = array(
				'settings'      => array(
					'title'       => __( 'Settings', 'yith-woocommerce-tab-manager' ),
					'icon'        => 'settings',
					'description' => __( 'Set the general behavior of the plugin.', 'yith-woocommerce-tab-manager' ),
				),
				'customization' => array(
					'title'       => __( 'Customization', 'yith-woocommerce-tab-manager' ),
					'icon'        => '<svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" d="M15 11.25l1.5 1.5.75-.75V8.758l2.276-.61a3 3 0 10-3.675-3.675l-.61 2.277H12l-.75.75 1.5 1.5M15 11.25l-8.47 8.47c-.34.34-.8.53-1.28.53s-.94.19-1.28.53l-.97.97-.75-.75.97-.97c.34-.34.53-.8.53-1.28s.19-.94.53-1.28L12.75 9M15 11.25L12.75 9"></path>
					</svg>',
					'description' => __( 'Customization options for the tabs shown on your product pages.', 'yith-woocommerce-tab-manager' ),
				),
			);

			return array_merge( $tabs, $premium );
		}

		/**
		 * Return the store tools
		 *
		 * @return array[]
		 */
		public function get_your_store_tools_args() {
			return array(
				'items' => array(
					'ajax-product-filter'           => array(
						'name'           => 'Ajax Product Filter',
						'icon_url'       => YWTM_ASSETS_URL . '/images/plugins/ajax-product-filter.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-ajax-product-filter/',
						// translators: Description of "YITH WooCommerce Ajax Product Filter" plugin in the "Your Store Tools" tab.
						'description'    => __(
							'Help your customers to easily find the products they are looking for and improve the user experience of your shop.',
							'yith-woocommerce-tab-manager'
						),
						'is_active'      => defined( 'YITH_WCAN_PREMIUM' ),
						'is_recommended' => true,
					),
					'ajax-search'                   => array(
						'name'           => 'Ajax Search',
						'icon_url'       => YWTM_ASSETS_URL . '/images/plugins/ajax-search.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-ajax-search/',
						// translators: Description of "YITH WooCommerce Ajax Search" plugin in the "Your Store Tools" tab.
						'description'    => __(
							'Add an intelligent search engine to your store so that your customers can quickly find the products they are looking for.',
							'yith-woocommerce-tab-manager'
						),
						'is_active'      => defined( 'YITH_WCAS_PREMIUM' ),
						'is_recommended' => true,
					),
					'wishlist'                      => array(
						'name'           => 'Wishlist',
						'icon_url'       => YWTM_ASSETS_URL . '/images/plugins/wishlist.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-wishlist/',
						// translators: Description of "YITH WooCommerce Wishlist" plugin in the "Your Store Tools" tab.
						'description'    => __(
							'Allow your customers to create lists of products they want and share them with family and friends.',
							'yith-woocommerce-tab-manager'
						),
						'is_active'      => defined( 'YITH_WCWL_PREMIUM' ),
						'is_recommended' => true,
					),
					'gift-cards'                    => array(
						'name'           => 'Gift Cards',
						'icon_url'       => YWTM_ASSETS_URL . '/images/plugins/gift-card.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-gift-cards/',
						// translators: Description of "YITH WooCommerce Gift Cards" plugin in the "Your Store Tools" tab.
						'description'    => __(
							'Sell gift cards to increase your store\'s revenue and win new customers.',
							'yith-woocommerce-tab-manager'
						),
						'is_active'      => defined( 'YITH_YWGC_PREMIUM' ),
						'is_recommended' => false,
					),
					'product-addons'                => array(
						'name'           => 'Product Add-Ons & Extra Options',
						'icon_url'       => YWTM_ASSETS_URL . '/images/plugins/product-add-ons.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-product-add-ons/',
						// translators: Description of "YITH WooCommerce Product Add-Ons & Extra Options" plugin in the "Your Store Tools" tab.
						'description'    => __(
							'Add paid or free advanced options to your product pages using fields like radio buttons, checkboxes, drop-downs, custom text inputs, and more.',
							'yith-woocommerce-tab-manager'
						),
						'is_active'      => defined( 'YITH_WAPO_PREMIUM' ),
						'is_recommended' => false,
					),
					'dynamic-pricing-and-discounts' => array(
						'name'           => 'Dynamic Pricing & Discounts',
						'icon_url'       => YWTM_ASSETS_URL . '/images/plugins/dynamic-pricing-and-discounts.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-dynamic-pricing-and-discounts/',
						// translators: Description of "YITH WooCommerce Dynamic Pricing & Discounts" plugin in the "Your Store Tools" tab.
						'description'    => __(
							'The best-selling plugin for creating promotions and upsell strategies in your store: 3x2, 2x1, BOGO, free products in the cart, quantity discounts, last-minute offers, and more.',
							'yith-woocommerce-tab-manager'
						),
						'is_active'      => defined( 'YITH_YWDPD_PREMIUM' ),
						'is_recommended' => false,
					),
					'customize-my-account'          => array(
						'name'           => 'Customize My Account Page',
						'icon_url'       => YWTM_ASSETS_URL . '/images/plugins/customize-myaccount-page.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-customize-my-account-page/',
						// translators: Description of "YITH WooCommerce Customize My Account Page" plugin in the "Your Store Tools" tab.
						'description'    => __(
							'Customize the My Account page of your customers by creating custom sections with promotions and ad-hoc content based on your needs.',
							'yith-woocommerce-tab-manager'
						),
						'is_active'      => defined( 'YITH_WCMAP_PREMIUM' ),
						'is_recommended' => false,
					),
					'request-a-quote'               => array(
						'name'           => 'Request a Quote',
						'icon_url'       => YWTM_ASSETS_URL . '/images/plugins/request-a-quote.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-request-a-quote/',
						// translators: Description of "YITH Request a Quote for WooCommerce" plugin in the "Your Store Tools" tab.
						'description'    => __(
							'Hide prices and/or the "Add to cart" button and let your customers request a custom quote for every product.',
							'yith-woocommerce-tab-manager'
						),
						'is_active'      => defined( 'YITH_YWRAQ_PREMIUM' ),
						'is_recommended' => false,
					),
				),
			);
		}

		/**
		 * Return the welcome modals
		 *
		 * @return array
		 */
		public function get_welcome_modals() {
			return array(
				'on_close' => function () {
					update_option( 'ywtm-welcome-modal', 'no' );
				},
				'modals'   => array(
 
				),
			);
		}

		/**
		 * Sync the recipient option with woocommerce_yith_tab_manager_send_info_settings
		 *
		 * @param string $new_option The new value.
		 * @param string $old_option The old value.
		 *
		 * @return string
		 */
		public function sync_with_woocommerce_email_recipient( $new_option, $old_option ) {

			$wc_settings = get_option( 'woocommerce_yith_tab_manager_send_info_settings' );
			if ( isset( $wc_settings['recipient'] ) && $wc_settings['recipient'] !== $new_option ) {
				$wc_settings['recipient'] = $new_option;
				update_option( 'woocommerce_yith_tab_manager_send_info_settings', $wc_settings );
			}

			return $new_option;
		}

		/**
		 * Show the custom tabs in product data meta box
		 *
		 * @return void
		 * @throws Exception The error.
		 */
		public function add_customized_product_tabs_edit() {
			global $product_object;

			if ( $product_object ) {
				$tabs = ywtm_get_tabs(
					array(
						'active'     => 'yes',
						'origin'     => 'plugin',
						'limit'      => - 1,
						'content_in' => 'product',
						'orderby'    => 'meta_value_num',
						'meta_key'   => '_ywtm_order_tab', // phpcs:ignore WordPress.DB.SlowDBQuery
						'order'      => 'ASC',
					)
				);
				foreach ( $tabs as $tab ) {
					if ( ! $tab->can_show( $product_object ) ) {
						continue;
					}
					?>
                    <li class="ywtm-custom-tabs <?php echo esc_attr( $tab->get_id() ); ?>_tab">
                        <a href="#ywtm-custom-tab-<?php echo esc_attr( $tab->get_id() ); ?>"><?php echo wp_kses_post( $tab->get_formatted_name() ); ?></a>
                    </li>
					<?php
				}

				add_action( 'woocommerce_product_data_panels', array( $this, 'write_custom_tab_panels' ) );
			}
		}

		/**
		 * Show the custom tabs in product data meta box
		 *
		 * @return void
		 * @throws Exception The exception.
		 */
		public function write_custom_tab_panels() {
			/**
			 * The global variable
			 *
			 * @var WC_Product $product_object
			 */
			global $product_object;

			if ( $product_object ) {

				$tabs = ywtm_get_tabs(
					array(
						'active'     => 'yes',
						'origin'     => 'plugin',
						'limit'      => - 1,
						'content_in' => 'product',
					)
				);
				$i    = 0;
				foreach ( $tabs as $tab ) {
					if ( ! $tab->can_show( $product_object ) ) {
						continue;
					}
					?>
                    <div id="ywtm-custom-tab-<?php echo esc_attr( $tab->get_id() ); ?>"
                         class="panel woocommerce_options_panel hidden yith-plugin-ui">
						<?php
						$file_path = YWTM_DIR . "/views/product/{$tab->get_layout()}-product-tab.php";
						if ( file_exists( $file_path ) ) {
							include $file_path;
						}

						?>
                    </div>
					<?php
					++ $i;
				}
			}
		}

		/**
		 * Add woocommerce tabs in product edit
		 *
		 * @since 1.1.0
		 */
		public function add_woocommerce_tabs_edit() {
			?>

            <li class="my-tabs_ywtm_wc_tab">
                <a href="#ywtm_wc_tab"><span><?php esc_html_e( 'WooCommerce Tab', 'yith-woocommerce-tab-manager' ); ?></span></a>
            </li>

			<?php
			add_action( 'woocommerce_product_data_panels', array( $this, 'edit_woocommerce_tabs' ) );
		}

		/**
		 * Show the content
		 *
		 * @return void
		 */
		public function edit_woocommerce_tabs() {

			include YWTM_DIR . 'views/product//woocommerce-tabs.php';
		}


		/**
		 * Save the product meta for the customized tabs
		 *
		 * @param int $product_id The product id.
		 *
		 * @return void
		 */
		public function save_custom_product_meta( $product_id ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended,WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$product = wc_get_product( $product_id );

			if ( isset( $_POST['ywtm_product_tabs'] ) ) {
				$product_tabs = wp_unslash( $_POST['ywtm_product_tabs'] );
				$new_tabs     = $product->get_meta( 'ywtm_product_tabs' );
				$new_tabs     = ! $new_tabs ? array() : $new_tabs;
				foreach ( $product_tabs as $tab_id => $tab_data ) {

					$data_validated = $this->validate_data( $tab_data );
					if ( ! $data_validated ) {
						if ( isset( $new_tabs[ $tab_id ] ) ) {
							unset( $new_tabs[ $tab_id ] );
						}
					} else {
						$type                         = $tab_data['type'];
						$tab_data['value'][ $type ]   = $data_validated;
						$new_tabs[ $tab_id ]['value'] = $tab_data['value'];
					}
				}

				$product->update_meta_data( 'ywtm_product_tabs', $new_tabs );
				$product->save();

			}

			// phpcs:enable
		}

		/**
		 * Check if the data is valid for specific tab type
		 *
		 * @param array $data The data.
		 *
		 * @return bool
		 */
		public function validate_data( $data ) {
			$tab_type = $data['type'];
			if ( isset( $data['value'][ $tab_type ] ) ) {
				$value = $data['value'][ $tab_type ];
				switch ( $tab_type ) {
					case 'video':
						foreach ( $value as $index => $video_data ) {
							if ( empty( $video_data['name'] ) || ( empty( $video_data['url'] ) && empty( $video_data['id'] ) ) ) {
								unset( $value[ $index ] );
							}
						}
						break;
					case 'faqs':
						foreach ( $value as $index => $faq_data ) {
							if ( empty( $faq_data['question'] ) || empty( $faq_data['answer'] ) ) {
								unset( $value[ $index ] );
							}
						}
						break;
					case 'download':
						foreach ( $value as $index => $download_data ) {
							if ( empty( $download_data['name'] ) || empty( $download_data['file'] ) ) {
								unset( $value[ $index ] );
							}
						}
						break;
				}

				return ! empty( $value ) ? $value : false;
			}

			return false;
		}

		/**
		 * Save the product meta
		 *
		 * @param int $product_id The product id.
		 *
		 * @return void
		 */
		public function save_custom_woocommerce_product_meta( $product_id ) {

			$product = wc_get_product( $product_id );
			$tabs    = yith_tab_manager_get_default_tab( $product );

			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			foreach ( $tabs as $tab_key => $tab ) {
				$override_meta_key = 'ywtm_override_' . $tab_key;
				$hide_meta_key     = 'ywtm_hide_' . $tab_key;
				$title_meta_key    = 'ywtm_title_tab_' . $tab_key;
				$content_meta_key  = 'ywtm_content_tab_' . $tab_key;

				$override_value = isset( $_REQUEST[ $override_meta_key ] ) ? 'yes' : 'no';
				$hide_value     = isset( $_REQUEST[ $hide_meta_key ] ) ? 'yes' : 'no';
				$title_value    = isset( $_REQUEST[ $title_meta_key ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $title_meta_key ] ) ) : '';
				$content_value  = isset( $_REQUEST[ $content_meta_key ] ) ? wp_kses_post( wp_unslash( $_REQUEST[ $content_meta_key ] ) ) : '';
				$product->update_meta_data( '_' . $override_meta_key, $override_value );
				$product->update_meta_data( '_' . $hide_meta_key, $hide_value );
				$product->update_meta_data( '_' . $title_meta_key, $title_value );
				$product->update_meta_data( '_' . $content_meta_key, $content_value );
				$product->save();
			}

			// phpcs:enable
		}
	}
}