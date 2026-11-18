<?php
/**
 * WC_CSP_Brands_Compatibility class
 *
 * @package  WooCommerce Conditional Shipping and Payments
 * @since    3.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Brands Compatibility.
 *
 * @todo Remove this when WooCommerce min-version is bumped to 9.6+
 * @version 3.3.0
 */
class WC_CSP_Brands_Compatibility {

	/**
	 * Initialization.
	 */
	public static function init() {
		self::load_conditions();
	}

	/**
	 * Load additional conditions by adding to the global conditions array.
	 *
	 * @return void
	 */
	public static function load_conditions() {

		$load_conditions = array(
			'WC_CSP_Condition_Cart_Brand',
			'WC_CSP_Condition_Package_Brand',
		);

		if ( is_array( WC_CSP()->conditions->conditions ) ) {

			foreach ( $load_conditions as $condition ) {

				$condition = new $condition();
				WC_CSP()->conditions->conditions[ $condition->id ] = $condition;
			}
		}
	}
}

WC_CSP_Brands_Compatibility::init();
