<?php
/**
 * Settings page.
 *
 * @package ThemeGrill\WoocommerceCustomizer
 * @since 0.1.0
 */

namespace ThemeGrill\WoocommerceCustomizer;

defined( 'ABSPATH' ) || exit;

class AccountMenu {

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
	 * Initialization.`
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	private function init() {
		$this->init_hooks();

		do_action( 'tgwc_account_menu_unhook', $this );
	}

	/**
	 * Initialization hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'add_endpoints' ) );
		add_action( 'init', array( $this, 'flush_rewrite_rules' ) );
		add_action( 'woocommerce_init', array( $this, 'remove_woocommerce_hooks' ) );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'add_account_menu_items' ), PHP_INT_MAX - 10 );
		add_filter( 'woocommerce_get_endpoint_url', array( $this, 'modify_endpoint_url' ), PHP_INT_MAX, 4 );
		add_filter( 'woocommerce_account_menu_item_classes', array( $this, 'add_classes'), PHP_INT_MAX - 10, 2 );
		add_filter( 'woocommerce_get_query_vars', array( $this, 'add_custom_query_vars' ) );

		foreach ( tgwc_get_endpoints_by_type( 'endpoint' ) as $slug => $endpoint ) {
			add_filter( "woocommerce_endpoint_{$slug}_title", array( $this, 'custom_endpoint_title' ), 20, 2 );
		}

		$default_endpoint = \tgwc_get_default_endpoint();
		if ( 'dashboard' !== $default_endpoint ) {
			add_filter( 'the_title', array( $this, 'change_the_title' ), 11 );
		}
	}

	/**
	 * Change the default endpoint title.
	 *
	 * @since 0.2.0
	 *
	 * @param string $title Endpoint title.
	 *
	 * @return string
	 */
	public function change_the_title( $title ) {
		global $wp;
		if ( in_the_loop() && is_account_page() ) {
			$default_endpoint = \tgwc_get_default_endpoint();
			$endpoint         = \tgwc_get_endpoint( $default_endpoint );

			if ( ! isset( $wp->query_vars['page'], $endpoint['label'] ) ) {
				return $title;
			}

			if ( empty( $endpoint['label'] ) ) {
				return $title;
			}

			// unhook after we've returned our title to prevent it from overriding others
			remove_filter( 'the_title', array( $this, __FUNCTION__ ), 11 );

			return $endpoint['label'];
		}

		return $title;
	}

	/**
	 * Return the title of custom endpoint.
	 *
	 * @since 0.1.0
	 *
	 * @return string Title of custom endpoint.
	 */
	public function custom_endpoint_title( $title, $endpoint ) {
		$endpoint = \tgwc_get_endpoint( $endpoint );

		if ( isset( $endpoint['label'] ) ) {
			$title = $endpoint['label'];
		}

		return $title;
	}

	/**
	 * Add custom endpoint to WooCommerce query vars.
	 *
	 * @since 0.1.0
	 *
	 * @param array $query_vars WooCommerce query vars.
	 *
	 * @return array Modified query vars.
	 */
	public function add_custom_query_vars( $query_vars ) {
		$endpoints = \tgwc_get_endpoints_by_type( 'endpoint' );
		$endpoints = array_keys( $endpoints );
		$endpoints = array_reduce( $endpoints, function( $result, $endpoint ) {
			$result[$endpoint] = $endpoint;
			return $result;
		}, array());

		return array_merge( $endpoints, $query_vars );
	}

	/**
	 * Flush rewrite rules on page load.
	 *
	 * @since  0.1.0
	 *
	 * @return void
	 */
	public function flush_rewrite_rules() {
		if ( get_option( 'tgwc_flush_rewrite' ) ) {
			flush_rewrite_rules();
			update_option( 'tgwc_flush_rewrite', false );
		}
	}

	/**
	 * Display account menu item.
	 *
	 * @since 0.1.0
	 *
	 * @param string $slug  Endpoint slug.
	 *
	 * @return void
	 */
	public function display_myaccount_menu_item( $slug ) {
		$endpoint = \tgwc_get_endpoint( $slug );

		if ( false === $endpoint ) {
			return;
		}

		$account_menu_types = array(
			'endpoint',
			'link',
			'group'
		);

		$type = $endpoint['type'];
		if ( in_array( $type, $account_menu_types, true ) ) {
			$endpoint['slug'] = $slug;
			wc_get_template(
				"frontend/{$type}-item.php",
				$endpoint,
				TGWC_TEMPLATE_PATH,
				TGWC_TEMPLATE_PATH
			);
		}
	}

	/**
	 * Remove woocommerce default hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function remove_woocommerce_hooks() {
		$priority = has_action( 'woocommerce_account_navigation', 'woocommerce_account_navigation' );
		if ( false !== $priority ) {
			remove_action( 'woocommerce_account_navigation', 'woocommerce_account_navigation', $priority );
		}

		$priority = has_action( 'woocommerce_account_content', 'woocommerce_account_content' );
		if ( false !== $priority ) {
			remove_action( 'woocommerce_account_content', 'woocommerce_account_content', $priority );
		}
	}

	/**
	 * Add classes to the endpoints.
	 *
	 * @since 0.1.0
	 *
	 * @param array $classes CSS classes list.
	 * @param string $endpoint Endpoint slug.
	 *
	 * @return array Modified classes list.
	 */
	public function add_classes( $classes, $endpoint ) {
		global $wp_query;
		$query = $wp_query->query;

		$class   = tgwc_get_endpoint_class( $endpoint );
		$classes = array_merge( $classes, $class );

		if ( isset( $query[ $endpoint ] ) ) {
			$classes[] = 'tab_selected';
		}

		// Set the tab selected and is-active for default endpoint set in the settings.
		if ( isset( $query['page'] ) && empty( $query['page'] )
			&& tgwc_get_default_endpoint() === $endpoint ) {
			$classes[] = 'tab_selected';
			$classes[] = 'is-active';
		}

		// Remove the is-active for dashboard if it is not default endpoint.
		if ( isset( $query['page'] ) && empty( $query['page'] ) && 'dashboard' === $endpoint ) {
			$index = array_search( 'is-active', $classes, true );
			unset( $classes[ $index ] );
		}

		$endpoint  = \tgwc_get_endpoint( $endpoint );
		$classes[] = 'tgwc-' . $endpoint['type'];

		$settings  = TGWC()->get_settings()->get_settings();
		$classes[] = "tgwc-navicon-{$settings['icon_position']}";

		return apply_filters( 'tgwc_account_menu_item_classes', array_unique( $classes ), $endpoint );
	}

	/**
	 * Modify the url for link type endpoint.
	 *
	 * @since 0.1.0
	 *
	 * @param String $url 		Endpoint's URL.
	 * @param String $endpoint 	Endpoint slug.
	 * @param String $value		Endpoint value.
	 * @param String $permalink	Endpoint permalink
	 *
	 * @return String Modified endpoint URL.
	 */
	public function modify_endpoint_url( $url, $endpoint, $value, $permalink ) {
		// Compatible with WooCommerce Membership by SkyVerge.
		if ( class_exists( 'WC_Memberships' ) && 'members-area' === $endpoint ) {
			$members_area_endpoint = wc_memberships_get_members_area_endpoint();
			$url = str_replace( $endpoint, $members_area_endpoint, $url );
		}

		$link_url = tgwc_get_link_url( $endpoint );
		if ( ! empty( $link_url ) ) {
			$url = $link_url;
		}

		return $url;
	}

	/**
	 * Rewrite custom endpoint's URL.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function add_endpoints() {
		$roles     = tgwc_get_user_roles();
		$endpoints = tgwc_get_endpoints_flat();

		$endpoints = array_filter( $endpoints, function( $endpoint ) use( $roles ) {
			return 'endpoint' === $endpoint['type'];
		} );
		$default_endpoint_slugs = tgwc_get_default_endpoints();

		// Rewrite rule for endpoint.
		foreach ( $endpoints as $slug => $endpoint ) {
			if ( ! isset( $default_endpoint_slugs[ $slug ] ) ) {
				$slug = ! empty( $endpoint['slug'] ) ? $endpoint['slug'] : $slug;
				add_rewrite_endpoint( $slug, EP_ROOT | EP_PAGES );
			}
		}

		$default_endpoint = \tgwc_get_default_endpoint();
		if ( 'dashboard' !== $default_endpoint ) {
			add_rewrite_endpoint( 'dashboard', EP_ROOT | EP_PAGES );
		}
	}

	/**
	 * Add custom endpoints list to account menu items list.
	 *
	 * @since 0.1.0
	 *
	 * @param array $menu_links Account menu items links.
	 *
	 * @return array Modified account menu items list.
	 */
	public function add_account_menu_items( $menu_links ) {
		$endpoints = TGWC()->get_settings()->get_endpoints();
		$roles     = tgwc_get_user_roles();

		$endpoints = array_filter( $endpoints, function( $endpoint ) use( $roles ) {

			$is_capable = array_reduce( $roles, function( $previous, $role ) use( $endpoint ) {
				return $previous || in_array( $role, $endpoint['user_role'], true );
			}, false );

			return $endpoint['enable'] && ( $is_capable || empty( $endpoint['user_role'] ) );
		} );

		$endpoints = array_map( function( $endpoint ) {
			return $endpoint['label'];
		}, $endpoints );

		return $endpoints;
	}
}
