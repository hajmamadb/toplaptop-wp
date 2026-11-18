<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * YITH_Multistep_Checkout_Frontend Class.
 *
 * @package YITH\MultiStepCheckout\Includes
 * @author  YITH <plugins@yithemes.com>
 */

if ( ! defined( 'YITH_WCMS_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

if ( ! class_exists( 'YITH_Multistep_Checkout_Frontend_Premium' ) ) {
	/**
	 * Class YITH_Multistep_Checkout_Frontend_Premium
	 *
	 */
	class YITH_Multistep_Checkout_Frontend_Premium extends YITH_Multistep_Checkout_Frontend {

		/**
		 * Construct
		 *
		 * @since  1.0
		 */
		public function __construct() {
			/* === Order Received Customizzation === */
			add_filter( 'body_class', array( $this, 'body_class' ) );
			add_action( 'wp_head', array( $this, 'thankyou_style' ) );

			/* === Checkout Customizzation === */
			add_filter( 'wc_get_template', array( $this, 'get_template' ), 10, 5 );
			add_filter( 'the_title', array( $this, 'remove_endpoint_title' ), 30 );

			/* === Enqueue Scripts === */
			add_filter( 'yith_wcms_main_script', array( $this, 'premium_script' ) );
			add_action( 'yith_wcms_enqueue_scripts', array( $this, 'premium_enqueue_scripts' ) );
			add_filter( 'yith_wcms_frontend_to_localize_object', array( $this, 'localized_object' ) );

			/* WooCommerce Multiple Shipping Support */
			if ( class_exists( 'WC_Ship_Multiple' ) ) {
				global $wcms;
				if ( $wcms ) {
					remove_action(
						'woocommerce_before_checkout_form',
						array(
							$wcms->checkout,
							'before_checkout_form',
						)
					);
					add_action( 'woocommerce_checkout_shipping', array( $wcms->checkout, 'before_checkout_form' ) );
				}
			}

			/* YITH Multiple Shipping Addresses for WooCommerce */
			if ( class_exists( 'YITH_Multiple_Addresses_Shipping' ) ) {
				// phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
				$YITH_Multiple_Addresses_Shipping_Frontend = YITH_Multiple_Addresses_Shipping::instance()->frontend;
				if ( ! empty( $YITH_Multiple_Addresses_Shipping_Frontend ) && ! get_current_user_id() && 'no' !== get_option( 'ywcmas_enable_guest_users', 'yes' ) ) {
					remove_action(
						'woocommerce_before_checkout_form',
						array(
							$YITH_Multiple_Addresses_Shipping_Frontend,
							'manage_addresses_cb',
						)
					);
					add_action(
						'woocommerce_checkout_shipping',
						array(
							$YITH_Multiple_Addresses_Shipping_Frontend,
							'manage_addresses_cb',
						)
					);
				}
				// phpcs:enable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
			}

			/* YITH WooCommerce Delivery Date Premium Support */
			if ( class_exists( 'YITH_Delivery_Date_Shipping_Manager' ) ) {
				$shipping_manager = YITH_Delivery_Date_Shipping_Manager();
				if ( false !== $shipping_manager ) {
					remove_action(
						'woocommerce_checkout_shipping',
						array(
							$shipping_manager,
							'print_delivery_from',
						),
						20
					);
					remove_action(
						'woocommerce_after_order_notes',
						array(
							$shipping_manager,
							'print_delivery_from',
						),
						20
					);
					add_action(
						'yith_woocommerce_checkout_order_review',
						array(
							$shipping_manager,
							'print_delivery_from',
						),
						30
					);
				}
			}

			if ( 'yes' === get_option( 'yith_wcms_show_amount_on_payments', 'no' ) ) {
				add_action( 'woocommerce_review_order_before_submit', array( $this, 'cart_totals_order_total_html' ) );
				add_action( 'woocommerce_review_order_before_submit', 'wc_cart_totals_order_total_html' );
			}

			/* WooCommerce Amazon Pay Gateway Support  */
			if ( class_exists( 'WC_Amazon_Payments_Advanced' ) ) {
				add_action( 'woocommerce_checkout_init', array( $this, 'wc_amazon_payments_support' ), 15 );
			}

			if ( function_exists( 'YITH_WC_Points_Rewards' ) ) {
				add_action( 'template_redirect', array( $this, 'yith_points_and_rewards_support' ), 40 );
			}

			parent::__construct();

			if ( 'yes' === get_option( 'yith_wcms_timeline_use_my_account_in_login_step', 'no' ) ) {
				remove_action( 'yith_wcms_checkout_login_form', 'yith_wcms_login_form', 10, 1 );
				add_action( 'yith_wcms_checkout_login_form', 'yith_wcms_my_account_login_form' );
				add_action( 'woocommerce_login_form_end', array( $this, 'redirect_and_refer_fields' ) );
			}
		}

		/**
		 * Add a body class(es)
		 *
		 * @param array $classes The classes array.
		 *
		 * @return array
		 * @since  1.0
		 */
		public function body_class( $classes ) {
			if ( ( is_order_received_page() || is_view_order_page() || is_page( 'my-account' ) ) && 'plugin' === get_option( 'yith_wcms_thankyou_style' ) ) {
				$classes[] = 'yith-wcms-pro-myaccount';
			}

			$is_checkout = is_checkout();

			if ( $is_checkout ) {

				if ( 'yes' === get_option( 'yith_wcms_show_amount_on_payments', 'no' ) ) {
					$classes[] = 'yith_wcms_show_amount_on_payments';
				}

				$terms_page_id = wc_get_page_id( 'terms' );

				if ( $terms_page_id > 0 && apply_filters( 'woocommerce_checkout_show_terms', true ) ) {
					$classes[] = 'yith_wcms_wc_checkout_show_terms';
				}
			}

			return $classes;
		}

		/**
		 * Add a body class(es)
		 *
		 * @param string $located Path of the currently located template.
		 * @param string $template_name Template name.
		 * @param array  $args Arguments to pass to the template.
		 * @param string $template_path Expected path to template.
		 * @param string $default_path Path to fallback template folder.
		 *
		 * @return string
		 * @since    1.0
		 */
		public function get_template( $located, $template_name, $args, $template_path, $default_path ) {
			if ( 'plugin' === get_option( 'yith_wcms_thankyou_style' ) && 'checkout/thankyou.php' === $template_name ) {
				$located = YITH_WCMS_WC_TEMPLATE_PATH . 'checkout/thankyou.php';
			}

			return $located;
		}

		/**
		 * Add a body class(es)
		 *
		 * @param string $title The page title.
		 *
		 * @return   array
		 * @since    1.0
		 */
		public function remove_endpoint_title( $title ) {
			return 'plugin' === get_option( 'yith_wcms_thankyou_style' ) && 'order-received' === WC()->query->get_current_endpoint() && WC()->query->get_endpoint_title( 'order-received' ) === $title ? __return_empty_string() : $title;
		}

		/**
		 * Add thankyou style
		 *
		 * @return   array
		 * @since    1.0
		 */
		public function thankyou_style() {
			$is_enable_customizzation = ( is_order_received_page() || is_view_order_page() || is_page( 'my-account' ) ) && 'plugin' === get_option( 'yith_wcms_thankyou_style' );
			if ( ! $is_enable_customizzation ) {
				return false;
			}

			ob_start();
			yith_wcms_get_template( 'thankyou-style.php', array(), 'style' );
			echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Add timeline style
		 *
		 * @return   array
		 * @since    1.0
		 */
		public function timeline_style() {
			if ( ! is_checkout() ) {
				return false;
			}

			$timeline_template = $this->get_checkout_style();

			ob_start();
			yith_wcms_get_template( "timeline-{$timeline_template}.php", array(), 'style' );
			echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Get the object to localize
		 *
		 * @param array $to_localize Localize object.
		 *
		 * @return array
		 */
		public function localized_object( $to_localize ) {
			$premium_dom = array(
				'active_timeline'             => '.timeline.active',
				'button_back_to_cart'         => '#yith-wcms-back-to-cart-button',
				'shipping_check'              => '#ship-to-different-address-checkbox',
				'create_account'              => '#createaccount',
				'create_account_wrapper'      => '.create-account',
				'account_password'            => '#account_password',
				'wc_invalid_required'         => '.woocommerce-invalid-required-field',
				'required_fields_check'       => '.input-text, select, input:radio, input[type=checkbox]',
				'select2_fields'              => array(
					'billing_country',
					'shipping_country',
					'billing_state',
					'shipping_state',
				),
				'day_of_birth'                => '#ywces_birthday',
				'email'                       => '#billing_email_field',
				'wc_checkout_addons'          => '#wc_checkout_add_ons',
				'ship-to-different-address'   => '#ship-to-different-address',
				'additional_fields'           => '.woocommerce-shipping-fields',
				'scroll_top_anchor'           => get_option( 'yith_wcms_scroll_top_anchor', '#checkout_timeline' ),
				'shipping_under_billing_step' => '.woocommerce-shipping-fields',
			);

			$to_localize['dom'] = array_merge( $to_localize['dom'], $premium_dom );

			$merge_billing_shipping         = 'yes' === get_option( 'yith_wcms_timeline_options_merge_billing_and_shipping_step', 'no' );
			$merge_order_info_payment       = 'yes' === get_option( 'yith_wcms_timeline_options_merge_order_and_payment_step', 'no' );
			$to_localize['dom']['shipping'] = $merge_billing_shipping ? '#customer_billing_details' : '#customer_shipping_details';

			$validate_checkout_event = array( 'input', 'validate', 'change', 'focusout' );
			$remove_shipping_step    = ! $this->is_step_enabled( 'shipping' );
			$use_cookie              = 'yes' === get_option( 'yith_wcms_use_cookie', 'yes' );
			$mobile_style            = get_option( 'yith_wcms_timeline_template_on_mobile', 'style4' );
			$have_mobile_timeline    = 'style4' === $mobile_style;

			$to_localize_premium = array(
				'live_fields_validation'              => get_option( 'yith_wcms_enable_ajax_validator', 'no' ),
				'disabled_prev_button'                => get_option( 'yith_wcms_nav_disabled_prev_button', 'no' ),
				'disabled_back_to_cart_button'        => get_option( 'yith_wcms_nav_disabled_back_to_cart_button', 'no' ),
				'is_old_wc'                           => version_compare( WC()->version, '2.5', '<' ),
				'checkout_login_reminder_enabled'     => 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder', 'yes' ),
				'is_order_received_endpoint'          => is_wc_endpoint_url( 'order-received' ),
				'skip_login_label'                    => get_option( 'yith_wcms_timeline_options_skip_login', esc_html_x( 'Skip Login', 'Frontend: button label', 'yith-woocommerce-multi-step-checkout' ) ),
				'next_label'                          => get_option( 'yith_wcms_timeline_options_next' ),
				/**
				 * APPLY_FILTERS: yith_wcms_use_cookie
				 *
				 * Filters whether to use the cookie.
				 *
				 * @param string $use_cookie Whether to use the cookie or not.
				 *
				 * @return string
				 */
				'use_cookie'                          => apply_filters( 'yith_wcms_use_cookie', $use_cookie ),
				'is_scroll_top_enabled'               => get_option( 'yith_wcms_scroll_top_enabled', 'no' ),
				'is_coupon_email_system_enabled'      => defined( 'YWCES_PREMIUM' ),
				'is_delivery_date_enabled'            => defined( 'YITH_DELIVERY_DATE_PREMIUM' ),
				'is_wc_checkout_addons_enabled'       => class_exists( 'WC_Checkout_Add_Ons' ),
				/**
				 * APPLY_FILTERS: yith_wcms_skip_shipping_method
				 *
				 * Filters whether to skip the shipping method.
				 *
				 * @param bool $skip_shipping_method Whether to skip the shipping method or not.
				 *
				 * @return bool
				 */
				'skip_shipping_method'                => apply_filters( 'yith_wcms_skip_shipping_method', false ),
				/**
				 * APPLY_FILTERS: yith_wcms_skip_payment_method
				 *
				 * Filters whether to skip the payment method.
				 *
				 * @param bool $skip_payment_method Whether to skip the payment method or not.
				 *
				 * @return bool
				 */
				'skip_payment_method'                 => apply_filters( 'yith_wcms_skip_payment_method', false ),
				'remove_shipping_step'                => $remove_shipping_step,
				// Validate checkout event documented in wp-content/plugins/woocommerce/assets/js/frontend/checkout.js:35.
				/**
				 * APPLY_FILTERS: yith_wcms_validate_checkout_event
				 *
				 * Filters the checkout events to validate.
				 *
				 * @param array $validate_checkout_event Checkout events to validate.
				 *
				 * @return array
				 */
				'validate_checkout_event'             => apply_filters( 'yith_wcms_validate_checkout_event', $validate_checkout_event ),
				'merge_billing_shipping'              => $merge_billing_shipping,
				'merge_order_info_payment'            => $merge_order_info_payment,
				/**
				 * APPLY_FILTERS: yith_wcmv_max_mobile_width
				 *
				 * Filters the max width for mobile devices.
				 *
				 * @param int $max_width Max width.
				 *
				 * @return int
				 */
				'max_mobile_width'                    => apply_filters( 'yith_wcmv_max_mobile_width', 992 ),
				/**
				 * APPLY_FILTERS: yith_wcmv_have_mobile_timeline
				 *
				 * Filters whether the current checkout style has a mobile timeline.
				 *
				 * @param bool   $have_mobile_timeline Whether the current style has a mobile timeline or not.
				 * @param string $mobile_style         Mobile style.
				 *
				 * @return bool
				 */
				'have_mobile_timeline'                => apply_filters( 'yith_wcmv_have_mobile_timeline', $have_mobile_timeline, $mobile_style ),
				/* === 3rd-party plugin === */
				'wp_gdpr'                             => array(
					'is_enabled'              => class_exists( 'GDPR' ),
					'add_consent_on_checkout' => get_option( 'gdpr_add_consent_checkboxes_checkout', false ),
					'consents'                => $consents = get_option( 'gdpr_consent_types', array() ),
					'consents_number'         => is_array( $consents ) ? count( $consents ) : 1,
				),
				'extra_brazil_checkout_fields_for_wc' => array(
					'is_enabled' => class_exists( 'Extra_Checkout_Fields_For_Brazil' ),
					'billing'    => array(
						'check'         => '#billing_persontype',
						'value'         => 1, // 1 = Individuals - 2 = Legal person
						'skip_to_check' => array(
							1 => array(
								'billing_cnpj_field',
								'billing_ie_field',
							),
							2 => array(
								'billing_cpf_field',
							),
						),
					),
				),
			);

			$to_localize = array_merge( $to_localize, $to_localize_premium );
			return $to_localize;
		}

		/**
		 * Enqueue Scripts
		 *
		 * Register and enqueue scripts for Frontend
		 *
		 * @return void
		 * @since 1.0
		 */
		public function premium_enqueue_scripts() {
			// Registered js-cookie.
			if ( ! wp_script_is( 'js-cookie', 'registered' ) ) {
				$js_cookie = function_exists( 'yit_load_js_file' ) ? yit_load_js_file( 'js.cookie.js' ) : str_replace( '.js', '.min.js', 'js.cookie.js' );
				wp_register_script( 'js-cookie', YITH_WCMS_ASSETS_URL . 'third-party/js/js-cookie/' . $js_cookie, array(), '2.1.3', true );
			}

			/* Buttons Style */
			$css                    = '';
			$main_stylesheet_handle = 'yith-wcms-checkout';
			$navigation_style       = get_option( 'yith_wcms_nav_buttons_style', 'theme-style' );
			$back_to_cart_style     = get_option( 'yith_wcms_back_to_cart_button_style', 'theme-style' );

			if ( 'custom-style' === $navigation_style ) {
				$background_default = array(
					'default' => '#43A08C',
					'hover'   => '#30615e',
				);

				$text_default = array(
					'default' => '#ffffff',
					'hover'   => '#ffffff',
				);

				$background_colors = get_option( 'yith_wcms_navigation_buttons_background_colors', $background_default );
				$text_colors       = get_option( 'yith_wcms_navigation_buttons_text_colors', $text_default );

				$css .= '#form_actions .yith-wcms-button.prev, #form_actions .yith-wcms-button.next {background: %1$s; background-color: %1$s; color: %3$s}';
				$css .= '#form_actions .yith-wcms-button.prev:hover, #form_actions .yith-wcms-button.next:hover {background: %2$s; background-color: %2$s; color: %4$s}';

				$css = sprintf( $css, $background_colors['default'], $background_colors['hover'], $text_colors['default'], $text_colors['hover'] );
			}

			if ( 'custom-style' === $back_to_cart_style ) {
				$background_default = array(
					'default' => '#43A08C',
					'hover'   => '#30615e',
				);

				$text_default = array(
					'default' => '#ffffff',
					'hover'   => '#ffffff',
				);

				$css .= '#yith-wcms-back-to-cart-button{background: %1$s; background-color: %1$s; color: %3$s}';
				$css .= '#yith-wcms-back-to-cart-button:hover{background: %2$s; background-color: %2$s; color: %4$s}';

				$background_colors = get_option( 'yith_wcms_back_to_cart_button_background_colors', $background_default );
				$text_colors       = get_option( 'yith_wcms_back_to_cart_button_text_colors', $text_default );

				$css = sprintf( $css, $background_colors['default'], $background_colors['hover'], $text_colors['default'], $text_colors['hover'] );
			}

			$checkout_container_width = get_option( 'yith_wcms_checkout_container_width', 100 );
			$css                     .= "#checkout-wrapper.timeline-horizontal{ width:{$checkout_container_width}%; margin: 0 auto; }";

			if ( is_checkout() ) {
				if ( apply_filters( 'yith_wcms_use_cookie', true ) ) {
					wp_enqueue_script( 'js-cookie' );
				}

				if ( ! empty( $css ) ) {
					wp_add_inline_style( $main_stylesheet_handle, $css );
				}

				wp_enqueue_style( 'dashicons' );
			}
		}

		/**
		 * Order total amount in last checkout step (payment tab)
		 *
		 * @return void
		 * @since 1.3.13
		 */
		public function cart_totals_order_total_html() {
			$text = get_option( 'yith_wcms_show_amount_on_payments_text', __( 'Order total amount', 'yith-woocommerce-multi-step-checkout' ) );
			printf( '<span class="order-total-in-payment"><strong>%s</strong> </span>', esc_html( $text ) );
		}

		/**
		 * Premium Script File
		 *
		 * Register and enqueue scripts for Frontend
		 *
		 * @param string $js_file The premium js filename.
		 *
		 * @return string The new filename
		 * @since  1.0
		 */
		public function premium_script( $js_file ) {
			return 'multistep-premium.js';
		}

		/**
		 * Support for WooCommerce Amazon Payments Plugin
		 *
		 * @return void
		 * @since  1.5.1
		 *
		 */
		public function wc_amazon_payments_support() {
			if (  function_exists( 'wc_apa' ) ) {
				$amazon_gateway = wc_apa()->get_gateway();

				/* Remove and add actions as needed. */
				remove_action( 'woocommerce_before_checkout_form', array( $amazon_gateway, 'checkout_message' ), 5 );
				add_action( 'yith_woocommerce_checkout_payment', array( $amazon_gateway, 'checkout_message' ), 5 );
			}
		}

		/**
		 * Support for YITH WooCommerce Points and Rewards Premium Plugin
		 *
		 * @return void
		 * @since  1.6.3
		 *
		 */
		public function yith_points_and_rewards_support() {
			if ( defined( 'YITH_YWPAR_VERSION' ) ) {
				if ( version_compare( YITH_YWPAR_VERSION, '3.0', '<' ) ) {
					if ( YITH_WC_Points_Rewards()->get_option( 'enabled_rewards_cart_message' ) === 'yes' && method_exists( YITH_WC_Points_Rewards(), 'is_user_enabled' ) && YITH_WC_Points_Rewards()->is_user_enabled( 'redeem' ) ) {
						remove_action(
							'woocommerce_before_checkout_form',
							array(
								YITH_WC_Points_Rewards_Frontend(),
								'print_rewards_message_in_cart',
							)
						);
						add_action(
							'yith_woocommerce_checkout_coupon',
							array(
								YITH_WC_Points_Rewards_Frontend(),
								'print_rewards_message_in_cart',
							),
							5
						);
					}

					if ( 'vertical' === get_option( 'yith_wcms_timeline_display' ) && YITH_WC_Points_Rewards()->get_option( 'enabled_checkout_message' ) === 'yes' ) {
						remove_action(
							'woocommerce_before_checkout_form',
							array(
								YITH_WC_Points_Rewards_Frontend(),
								'print_messages_in_cart',
							)
						);
						add_action(
							'yith_woocommerce_show_wc_notices',
							array(
								YITH_WC_Points_Rewards_Frontend(),
								'print_messages_in_cart',
							),
							15
						);
						add_filter( 'yith_par_messages_class', 'YITH_Multistep_Checkout_Frontend_Premium::add_vertical_timeline_html_class_for_yith_points_and_rewards' );
					}
				} else {
					$customer = ywpar_get_current_customer();
					if ( ywpar_get_option( 'enabled_rewards_cart_message' ) === 'yes' ) {
						if ( $customer && $customer->is_enabled( 'redeem' ) ) {
							remove_action( 'woocommerce_before_checkout_form', array( yith_points()->frontend, 'print_rewards_message_in_cart' ) );
							add_action( 'yith_woocommerce_checkout_coupon', array( yith_points()->frontend, 'print_rewards_message_in_cart' ), 5 );
						}
					}

					if ( 'vertical' === get_option( 'yith_wcms_timeline_display' ) && ywpar_get_option( 'enabled_checkout_message' ) === 'yes' ) {
						remove_action( 'woocommerce_before_checkout_form', array( yith_points()->frontend, 'print_messages_in_cart' ) );
						add_action( 'yith_woocommerce_show_wc_notices', array( yith_points()->frontend, 'print_messages_in_cart' ), 15 );
						add_filter( 'yith_par_messages_class', 'YITH_Multistep_Checkout_Frontend_Premium::add_vertical_timeline_html_class_for_yith_points_and_rewards' );
					}
				}
			}
		}

		/**
		 * Add specific class for YITH WooCommerce Points and Rewards Premium Plugin
		 * if vertical timeline is enabled
		 *
		 * @param array $classes Classes.
		 *
		 * @return array classes
		 * @since  1.6.3
		 *
		 */
		public static function add_vertical_timeline_html_class_for_yith_points_and_rewards( $classes ) {
			$classes[] = 'yith-wcms-vertical-timeline';

			return $classes;
		}

		/**
		 * Get the checkout style
		 *
		 * @since  2.0
		 *
		 * @return string checkout style
		 */
		public function get_checkout_style() {
			$option = wp_is_mobile() ? 'yith_wcms_timeline_template_on_mobile' : 'yith_wcms_timeline_template';
			return get_option( $option, 'style4' );
		}

		/**
		 * Check if a step is enabled
		 *
		 * @param string $step The step to check.
		 * @since  2.0
		 *
		 * @return bool true if enabled, false otherwise
		 */
		public function is_step_enabled( $step ) {
			$enabled = true;

			if ( 'shipping' === $step ) {
				$merge_billing_shipping = 'yes' === get_option( 'yith_wcms_timeline_options_merge_billing_and_shipping_step', 'no' );
				$enabled                = $merge_billing_shipping ? false : 'no' === get_option( 'yith_wcms_timeline_remove_shipping_step', 'no' );
			}

			if ( 'payment' === $step ) {
				$merge_order_info_payment = 'yes' === get_option( 'yith_wcms_timeline_options_merge_order_and_payment_step', 'no' );
				$enabled                  = $merge_order_info_payment ? false : true;
			}

			/**
			 * APPLY_FILTERS: yith_wcms_is_step_enabled
			 *
			 * Filters if the step is enabled.
			 *
			 * @param bool $enabled Step enabled.
			 * @param string $step Step name.
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wcms_is_step_enabled', $enabled, $step );
		}

		/**
		 * Add redirect and refer fields in order to use the correct checkout url
		 *
		 * @return void
		 * @since 2.0.7
		 */
		public function redirect_and_refer_fields() {
			if ( is_checkout() ) {
				printf( '<input type="hidden" name="redirect" value="%s" />', esc_url( wc_get_checkout_url() ) );
			}
		}
	}
}
