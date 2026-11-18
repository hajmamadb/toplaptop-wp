<?php
/**
 * Settings page.
 *
 * @package ThemeGrill\WoocommerceCustomizer
 * @since 0.1.0
 */

namespace ThemeGrill\WoocommerceCustomizer;

defined( 'ABSPATH' ) || exit;

class Settings {

	/**
	 * List of tabs.
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	private $tabs = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialization.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	private function init() {
		$this->tabs = apply_filters(
			'tgwc_settings_tabs',
			array(
				'settings'  => esc_html__( 'Settings', 'customize-my-account-page-for-woocommerce' ),
				'endpoints' => esc_html__( 'Endpoints', 'customize-my-account-page-for-woocommerce' ),
			)
		);

		$this->init_hooks();

		do_action( 'tgwc_settings_unhook', $this );
	}

	/**
	 * Initialization hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_setting' ) );
		add_action( 'tgwc_customization_panel_tabs', array( $this, 'display_tabs' ) );
		add_action( 'tgwc_customization_panel_tab_content', array( $this, 'display_tab_content' ) );
		add_action( 'tgwc_after_customization_panel_form', array( $this, 'display_endpoint_dialog_form' ) );
		add_action( 'admin_footer', array( $this, 'add_js_templates' ) );
		add_action( 'tgwc_endpoints_content', array( $this, 'display_endpoints_content' ), 20, 3 );
	}

	/**
	 * Register settings.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register_setting() {
		// Remove the customization as well.
		if ( isset( $_POST['_wpnonce'] ) ) {
			$nonce = sanitize_text_field( $_POST['_wpnonce'] );
			$valid = wp_verify_nonce( $nonce, 'tgwc-options' );

			if ( isset( $_POST['tgwc_reset_options']['customization'] ) && false !== $valid ) {
				$my_account_file = \tgwc_get_my_account_file();
				file_exists( $my_account_file ) && unlink( $my_account_file );

				// Remvoe the customization option as well.
				delete_option( 'tgwc_customize' );

				add_settings_error(
					'tgwc',
					'tgwc-reset-customization',
					esc_html__( 'Customization reset successfully.', 'customize-my-account-page-for-woocommerce' ),
					'success'
				);
			}
		}

		register_setting(
			'tgwc',
			'tgwc_settings',
			array(
				'sanitize_callback' => array( $this, 'process_settings' ),
			)
		);

		register_setting(
			'tgwc',
			'tgwc_endpoints',
			array(
				'sanitize_callback' => array( $this, 'process_endpoints' ),
			)
		);

		register_setting(
			'tgwc',
			'tgwc_debug_settings',
			array(
				'sanitize_callback' => array( $this, 'process_debug_settings' ),
			)
		);
	}

	/**
	 * Process the settings POST data.
	 *
	 * @since 0.1.0
	 *
	 * @param array $settings Settings.
	 *
	 * @return array Processed settings.
	 */
	public function process_settings( $settings ) {
		// Bail early with settings if the page is not settings.
		if ( ! isset( $_POST['tgwc_page'] ) || 'settings' !== $_POST['tgwc_page'] ) {
			return TGWC()->get_settings()->get_settings();
		}

		// Bail early if the nonce verifcation fails.
		if ( isset( $_POST['_wpnonce'] ) ) {
			$nonce = sanitize_text_field( $_POST['_wpnonce'] );
			$valid = wp_verify_nonce( $nonce, 'tgwc-options' );
			if ( false === $valid ) {
				return;
			}
		}

		// Remove the customization as well.
		if ( isset( $_POST['tgwc_reset_options']['setting'] ) ) {
			add_settings_error(
				'tgwc',
				'tgwc-reset-settings',
				esc_html__( 'Settings reset successfully.', 'customize-my-account-page-for-woocommerce' ),
				'success'
			);
			return false;
		}

		$settings = apply_filters( 'tgwc_before_process_settings', $settings );

		if ( ! \is_array( $settings ) || empty( $settings ) ) {
			return TGWC()->get_settings()->get_settings();
		}

		$settings['custom_avatar'] = isset( $settings['custom_avatar'] ) ? true : false;
		$settings['icon']          = isset( $settings['icon'] ) ? true : false;

		$settings = apply_filters( 'tgwc_after_process_settings', $settings );

		add_settings_error(
			'tgwc',
			'tgwc_endpoints_saved',
			esc_html__( 'Settings saved successfully', 'customize-my-account-page-for-woocommerce' ),
			'success'
		);

		return $settings;
	}

	/**
	 * Process the endpoints POST data.
	 *
	 * @since 0.1.0
	 *
	 * @param array $endpoints Endpoints.
	 *
	 * @return array Processed endpoints.
	 */
	public function process_endpoints( $endpoints ) {
		// Bail early with settings if the page is not settings.
		if ( ! isset( $_POST['tgwc_page'] ) || 'settings' !== $_POST['tgwc_page'] ) {
			return TGWC()->get_settings()->get_endpoints();
		}

		// Bail early if the nonce is not set.
		if ( ! isset( $_POST['_wpnonce'] ) ) {
			add_settings_error(
				'tgwc',
				'tgwc_nonce_required',
				esc_html__( 'Nonce is required.', 'customize-my-account-page-for-woocommerce' )
			);
			return false;
		}

		// Bail early if the nonce verifcation fails.
		$nonce = sanitize_text_field( $_POST['_wpnonce'] );
		if ( false === wp_verify_nonce( $nonce, 'tgwc-options' ) ) {
			add_settings_error(
				'tgwc',
				'tgwc_invalid-nonce',
				esc_html__( 'Invalid nonce.', 'customize-my-account-page-for-woocommerce' )
			);
			return false;
		}

		// Reset settings if the options is so.
		if ( isset( $_POST['tgwc_reset_options']['setting'] ) ) {
			return false;
		}

		if ( ! \is_array( $endpoints ) || empty( $endpoints ) ) {
			return TGWC()->get_settings()->get_endpoints();
		}

		if ( isset( $endpoints['endpoints_order'] ) ) {
			$endpoints_order = json_decode( $endpoints['endpoints_order'], true );
			unset( $endpoints['endpoints_order'] );
		} else {
			return $endpoints;
		}

		$endpoints         = apply_filters( 'tgwc_before_process_endpoints', $endpoints, $endpoints_order );
		$default_endpoints = \tgwc_get_default_endpoints();

		// Update the endpoints order id to the slugs.
		foreach ( $endpoints as $key => $endpoint ) {
			if ( isset( $default_endpoints[ $key ] ) ) {
				continue;
			}

			if ( 'endpoint' !== $endpoint['type'] ) {
				continue;
			}

			// Get slug.
			$slug = isset( $endpoint['slug'] ) ? $endpoint['slug'] : '';
			$slug = empty( $slug ) ? $key : $slug;
			$slug = sanitize_title( $slug );

			// No need to update the id if the slug and key of the endpoint is same.
			if ( $key === $slug ) {
				continue;
			}

			$endpoints[ $slug ] = $endpoints[ $key ];

			// Update the endpoints order id to the slug.
			$endpoints_order = array_map(
				function( $order ) use ( $key, $slug ) {
						// Update to id to the slug if it matches.
					if ( $order['id'] === $key ) {
						  $order['id'] = $slug;
					}

						// Loop through children in case of group.
					if ( isset( $order['children'] ) ) {
						foreach ( $order['children'] as $index => $child ) {
							if ( ! isset( $order['id'] ) ) {
								  continue;
							}

							if ( $child['id'] === $key ) {
								  $order['children'][ $index ]['id'] = $slug;
							}
						}
					}

						return $order;
				},
				$endpoints_order
			);

			unset( $endpoints[ $key ] );
		}

		// Convert input checkbox values to boolean.
		$endpoints = array_map(
			function( $endpoint ) {
				if ( 'link' === $endpoint['type'] ) {
					  $endpoint['new_tab'] = isset( $endpoint['new_tab'] ) ? true : false;
				} elseif ( 'group' === $endpoint['type'] ) {
					  $endpoint['show_open'] = isset( $endpoint['show_open'] ) ? true : false;
				}

					$endpoint['enable'] = isset( $endpoint['enable'] ) ? true : false;

				if ( isset( $endpoint['class'] ) ) {
					  $endpoint['class'] = explode( ' ', $endpoint['class'] );
					$endpoint['class']   = array_filter(
						$endpoint['class'],
						function( $class ) {
							$class = trim( $class );
							return ! empty( $class );
						}
					);
				} else {
					  $endpoint['class'] = array();
				}
					return $endpoint;
			},
			$endpoints
		);

		// Create the endpoints according to the order.
		$endpoints = array_reduce(
			$endpoints_order,
			function( $result, $endpoint_order ) use ( $endpoints ) {
				$endpoint = $endpoint_order['id'];
				$type     = $endpoint_order['type'];

				if ( ! isset( $endpoints[ $endpoint ] ) ) {
					return;
				}

				unset( $endpoint_order['uiTabsAriaControls'] );

				if ( isset( $endpoint_order['children'] ) ) {
					$children = $endpoint_order['children'];

					$children = array_reduce(
						$children,
						function( $result, $child ) use ( $endpoints ) {
							$slug = $child['id'];
							if ( isset( $endpoints[ $slug ] ) ) {
								$result[ $slug ] = $endpoints[ $slug ];
								return $result;
							}
						},
						array()
					);

					$endpoint_order['children'] = $children;
				}

				unset( $endpoint_order['id'] );
				$result[ $endpoint ] = array_merge( $endpoint_order, $endpoints[ $endpoint ] );
				return $result;
			},
			array()
		);

		// Set flag to flush the rewrite rules on next page load.
		update_option( 'tgwc_flush_rewrite', true );

		// Don't add endpoints saved message when the design customization reset is being performed.
		if ( ! isset( $_POST['tgwc_reset_options']['customization'] ) ) {
			add_settings_error(
				'tgwc',
				'tgwc_endpoints_saved',
				esc_html__( 'Endpoints saved successfully', 'customize-my-account-page-for-woocommerce' ),
				'success'
			);
		}

		return apply_filters( 'tgwc_after_process_endpoints', $endpoints );
	}

	/**
	 * Register menus.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register_menu() {
		if ( ! function_exists( 'wc_get_page_permalink' ) ) {
			return;
		}

		$settings_url = add_query_arg(
			array(
				'page' => 'tgwc-customize-my-account-page',
			),
			admin_url() . 'admin.php'
		);

		$customize_url = add_query_arg(
			array(
				'tgwc-customizer' => true,
				'return'          => rawurlencode( $settings_url ),
				'url'             => rawurlencode( wc_get_page_permalink( 'myaccount' ) ),
			),
			admin_url() . 'customize.php'
		);

		add_menu_page(
			esc_html__( 'Customize My Account Page For WooCommerce', 'customize-my-account-page-for-woocommerce' ),
			esc_html__( 'Customize My Account', 'customize-my-account-page-for-woocommerce' ),
			'manage_options',
			'tgwc-customize-my-account-page',
			array( $this, 'display_settings_page' ),
			'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0OCA0OCI+CiAgPGNpcmNsZSBmaWxsPSIjZmZmZmZmIiBjeD0iMjQiIGN5PSIxNSIgcj0iNyIvPgogIDxwYXRoIGZpbGw9IiNmZmZmZmYiIGQ9Ik0zMiA0MGExNCAxNCAwIDAwMTQtMTRIMThhMTQgMTQgMCAwMDE0IDE0eiIvPgogIDxwYXRoIGZpbGw9IiNmZmZmZmYiIGQ9Ik0xNiA0MGExNCAxNCAwIDAwMTQtMTRIMmExNCAxNCAwIDAwMTQgMTR6IiAvPgogIDxwYXRoIGZpbGw9IiNmZmZmZmYiIGQ9Ik0yNCAzNy40OEExNCAxNCAwIDAwMzAgMjZIMThhMTQgMTQgMCAwMDYgMTEuNDh6IiAvPgo8L3N2Zz4='
		);

		add_submenu_page(
			'tgwc-customize-my-account-page',
			esc_html__( 'Customize My Account Page', 'customize-my-account-page-for-woocommerce' ),
			esc_html__( 'Settings', 'customize-my-account-page-for-woocommerce' ),
			'manage_options',
			'tgwc-customize-my-account-page',
			array( $this, 'display_settings_page' )
		);

		add_submenu_page(
			'tgwc-customize-my-account-page',
			esc_html__( 'Customize My Account Page', 'customize-my-account-page-for-woocommerce' ),
			esc_html__( 'Customizer', 'customize-my-account-page-for-woocommerce' ),
			'manage_options',
			$customize_url
		);

		add_submenu_page(
			'tgwc-customize-my-account-page',
			esc_html__( 'Customize My Account Page', 'customize-my-account-page-for-woocommerce' ),
			esc_html__( 'Debug', 'customize-my-account-page-for-woocommerce' ),
			'manage_options',
			'tgwc-customize-my-account-page-debug',
			array( $this, 'display_debug_page' )
		);
	}

	/**
	 * Display settings page.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function display_settings_page() {
		global $pagenow;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['page'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = sanitize_text_field( $_GET['page'] );

		if ( 'admin.php' === $pagenow
			&& 'tgwc-customize-my-account-page' === $page ) {
			require_once plugin_dir_path( TGWC_PLUGIN_FILE ) . 'templates/admin/customization.php';
		}
	}

	/**
	 * Display customization page tabs list.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function display_tabs() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab_selected = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'settings';

		$tabs         = $this->tabs;
		$tab_selected = apply_filters( 'tgwc_settings_tab_selected', $tab_selected );

		require_once plugin_dir_path( TGWC_PLUGIN_FILE ) . 'templates/admin/tabs.php';
	}

	/**
	 * Display customization page tab content.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function display_tab_content() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab_selected = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'settings';
		$settings     = $this->get_settings();
		$endpoints    = $this->get_endpoints();

		if ( 'settings' === $tab_selected ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'templates/admin/tab-content/settings.php';
		} elseif ( 'endpoints' === $tab_selected ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'templates/admin/tab-content/endpoints.php';
		} else {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'templates/admin/tab-content/settings.php';
		}
	}

	/**
	 * Display endpoint dialog form.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function display_endpoint_dialog_form() {
		require_once plugin_dir_path( TGWC_PLUGIN_FILE ) . 'templates/admin/endpoint-dialog.php';
	}

	/**
	 * Display endpoints header.
	 *
	 * @since 0.1.0
	 *
	 * @param string $key Endpoint key.
	 * @param array  $endpoint Endpoint.
	 * @return void
	 */
	public function display_endpoints_header( $key, $endpoint ) {
		require plugin_dir_path( TGWC_PLUGIN_FILE ) . 'templates/admin/endpoints/header.php';
	}

	/**
	 * Display endpoints content.
	 *
	 * @since 0.1.0
	 *
	 * @param string $key Endpoint key.
	 * @param array  $endpoint Endpoint.
	 * @param string $endpoint Initial endpoint slug.
	 * @return void
	 */
	public function display_endpoints_content( $key, $endpoint, $initial ) {
		$endpoint['class'] = isset( $endpoint['class'] ) ? implode( ' ', $endpoint['class'] ) : '';

		// Set key as slug for if it is not set.
		$default_endpoints = \tgwc_get_default_endpoints();
		if ( ! isset( $default_endpoints[ $key ] ) ) {
			$endpoint['slug'] = empty( $endpoint['slug'] ) ? $key : $endpoint['slug'];
		}

		if ( 'endpoint' === $endpoint['type'] ) {
			require plugin_dir_path( TGWC_PLUGIN_FILE ) . 'templates/admin/endpoints/endpoint.php';
		} elseif ( 'link' === $endpoint['type'] ) {
			require plugin_dir_path( TGWC_PLUGIN_FILE ) . 'templates/admin/endpoints/link.php';
		} elseif ( 'group' === $endpoint['type'] ) {
			require plugin_dir_path( TGWC_PLUGIN_FILE ) . 'templates/admin/endpoints/group.php';
		}
	}

	/**
	 * Add js templates.
	 */
	public function add_js_templates() {
		global $pagenow;

		if ( 'admin.php' === $pagenow
			&& isset( $_GET['page'] )                               // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			&& 'tgwc-customize-my-account-page' === $_GET['page']   // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			&& isset( $_GET['tab'] )                                // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			&& 'endpoints' === $_GET['tab'] ) {                     // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				require_once plugin_dir_path( TGWC_PLUGIN_FILE ) . 'templates/admin/js/endpoint.php';
				require_once plugin_dir_path( TGWC_PLUGIN_FILE ) . 'templates/admin/js/link.php';
				require_once plugin_dir_path( TGWC_PLUGIN_FILE ) . 'templates/admin/js/group.php';
		}

		echo "<script>jQuery('#toplevel_page_tgwc-customize-my-account-page').find('a').eq(2).attr( 'target', '_blank' );</script>";
	}

	/**
	 * Get settings.
	 *
	 * @since 0.1.0
	 *
	 * @return array Settings.
	 */
	public function get_settings() {
		$settings = get_option( 'tgwc_settings' );

		$settings = wp_parse_args(
			$settings,
			array(
				'custom_avatar'    => true,
				'icon'             => true,
				'icon_position'    => 'right',
				'default_endpoint' => 'dashboard',
			)
		);

		return apply_filters( 'tgwc_settings', $settings );
	}

	/**
	 * Get endpoints.
	 *
	 * @since 0.1.0
	 *
	 * @return array Endpoints.
	 */
	public function get_endpoints() {
		$endpoints         = get_option( 'tgwc_endpoints' );
		$default_endpoints = tgwc_get_default_endpoints();

		// Get default WooCommerce endpoints,if not endpoints are in database.
		if ( empty( $endpoints ) ) {
			$endpoints = array();
			foreach ( $default_endpoints as $key => $endpoint ) {
				$endpoints[ $key ] = tgwc_get_default_endpoint_options( $endpoint, $key );
			}
		}

		foreach ( $endpoints as $key => $endpoint ) {
			if ( isset( $default_endpoints[ $key ] ) ) {
				$endpoints[ $key ]['slug'] = '';
			} else {
				$endpoints[ $key ]['slug'] = $key;
			}
		}

		// Add default value to endpoints, link and group items.
		foreach ( $endpoints as $key => $endpoint ) {
			if ( 'endpoint' === $endpoint['type'] ) {
				$endpoints[ $key ] = wp_parse_args(
					$endpoint,
					tgwc_get_default_endpoint_options()
				);
			} elseif ( 'link' === $endpoint['type'] ) {
				$endpoints[ $key ] = wp_parse_args(
					$endpoint,
					tgwc_get_default_link_options()
				);
			} elseif ( 'group' === $endpoint['type'] ) {
				$endpoints[ $key ] = wp_parse_args(
					$endpoint,
					tgwc_get_default_group_options()
				);
			}
		}

		// Add default value to group children.
		foreach ( $endpoints as $key => $endpoint ) {
			if ( isset( $endpoint['children'] ) ) {
				foreach ( $endpoint['children'] as $child_slug => $child ) {
					if ( 'endpoint' === $child['type'] ) {
						$endpoints[ $key ]['children'][ $child_slug ] = wp_parse_args(
							$child,
							tgwc_get_default_endpoint_options()
						);
					} elseif ( 'link' === $child['type'] ) {
						$endpoints[ $key ]['children'][ $child_slug ] = wp_parse_args(
							$child,
							tgwc_get_default_link_options()
						);
					} elseif ( 'group' === $child['type'] ) {
						$endpoints[ $key ]['children'][ $child_slug ] = wp_parse_args(
							$child,
							tgwc_get_default_group_options()
						);
					}
				}
			}
		}

		return $endpoints;
	}

	/**
	 * Process debug settings.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function process_debug_settings( $settings ) {
		if ( ! isset( $_POST['tgwc_page'] ) || 'debug' !== $_POST['tgwc_page'] ) {
			return tgwc_get_debug_settings();
		}

		// Bail early if the nonce is not set.
		if ( ! isset( $_POST['_wpnonce'] ) ) {
			add_settings_error(
				'tgwc',
				'tgwc_nonce_required',
				esc_html__( 'Nonce is required.', 'customize-my-account-page-for-woocommerce' )
			);
			return false;
		}

		// Bail early if the nonce verifcation fails.
		$nonce = sanitize_text_field( $_POST['_wpnonce'] );
		if ( false === wp_verify_nonce( $nonce, 'tgwc-options' ) ) {
			add_settings_error(
				'tgwc',
				'tgwc_invalid-nonce',
				esc_html__( 'Invalid nonce.', 'customize-my-account-page-for-woocommerce' )
			);
			return false;
		}

		// Reset settings if the options is so.
		if ( isset( $_POST['tgwc_reset_debug'] ) ) {
			add_settings_error(
				'tgwc',
				'tgwc-reset-debug-settings',
				esc_html__( 'Debug settings reset successfully.', 'customize-my-account-page-for-woocommerce' ),
				'success'
			);
			return false;
		}

		$settings['enable_debug'] = isset( $settings['enable_debug'] ) ? tgwc_string_to_bool( $settings['enable_debug' ] ) : false;

		$settings = tgwc_parse_args(
			$settings,
			array(
				'frontend' => array(
					'dropzone'         => array(),
					'fontawesome'      => array(),
					'jqueryscrolltabs' => array()
				),
			)
		);

		if ( isset( $settings['frontend'] ) ) {
			foreach ( $settings['frontend'] as $library => $setting ) {
				$settings['frontend'][ $library ]['css'] =  isset( $setting['css'] ) ? true : false;
				$settings['frontend'][ $library ]['js']  =  isset( $setting['js'] ) ? true : false;
			}
		}

		add_settings_error(
			'tgwc',
			'tgwc_debug_saved',
			esc_html__( 'Debug settings saved successfully', 'customize-my-account-page-for-woocommerce' ),
			'success'
		);

		return $settings;
	}

	/**
	 * Display debug page.
	 *
	 * @since 0.1.0
	 */
	public function display_debug_page() {
		$debug = tgwc_get_debug_settings();

		require_once plugin_dir_path( TGWC_PLUGIN_FILE ) . 'templates/admin/debug.php';
	}
}
