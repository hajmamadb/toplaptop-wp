<?php
/**
 * WC_CSP_Condition_Package_Brand class
 *
 * @package  WooCommerce Conditional Shipping and Payments
 * @since    3.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Brand in Package Condition.
 *
 * @class    WC_CSP_Condition_Package_Brand
 * @version  3.3.0
 */
class WC_CSP_Condition_Package_Brand extends WC_CSP_Package_Condition {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                            = 'brand_in_package';
		$this->title                         = __( 'Brand', 'woocommerce-conditional-shipping-and-payments' );
		$this->priority                      = 60;
		$this->supported_global_restrictions = array( 'shipping_methods', 'shipping_countries' );
	}

	/**
	 * Brands condition matching relationship. Values 'or' | 'and'.
	 *
	 * @return string
	 */
	protected function get_term_relationship(): string {
		/**
		 * Filters the brands condition matching relationship.
		 *
		 * @since 3.3.0
		 *
		 * @param string $term_relationship Brands condition matching relationship. Values 'or' | 'and'.
		 */
		return apply_filters( 'woocommerce_csp_package_brand_matching_relationship', 'or' );
	}

	/**
	 * Return condition field-specific resolution message which is combined along with others into a single restriction "resolution message".
	 *
	 * @param array $data Condition field data.
	 * @param array $args Optional arguments passed by restriction.
	 *
	 * @return string|false
	 */
	public function get_condition_resolution( $data, $args ) {

		// Empty conditions always return false (not evaluated).
		if ( empty( $data['value'] ) ) {
			return false;
		}

		if ( empty( $args['package'] ) || empty( $args['package']['contents'] ) ) {
			return false;
		}

		$brand_names = array();

		foreach ( $data['value'] as $brand_id ) {

			$term = get_term_by( 'id', $brand_id, 'product_brand' );

			if ( $term ) {
				$brand_names[] = $term->name;
			}
		}

		$package_count = $this->get_package_count( $args );
		$brands        = $this->merge_titles( $brand_names, array( 'rel' => $this->get_term_relationship() ) );
		$message       = false;

		if ( $this->modifier_is( $data['modifier'], array( 'in' ) ) ) {

			$product_names = $this->get_condition_violation_subjects( $data, $args );
			$products      = $this->merge_titles( $product_names );

			if ( count( $product_names ) > 4 ) {
				if ( count( $brand_names ) > 1 ) {
					// translators: %1$s - products, %2$s - brands.
					$message = sprintf( _nx( 'remove all products with the %2$s brands from your cart', 'make sure it contains no products with the %2$s brands', $package_count, 'products with brands', 'woocommerce-conditional-shipping-and-payments' ), $products, $brands );
				} else {
					// translators: %1$s - products, %2$s - brand.
					$message = sprintf( _nx( 'remove all products with the %2$s brand from your cart', 'make sure it contains no products with the %2$s brand', $package_count, 'products with brand', 'woocommerce-conditional-shipping-and-payments' ), $products, $brands );
				}
			} elseif ( count( $brand_names ) > 1 ) {
				// translators: %1$s - products, %2$s - brands.
				$message = sprintf( _nx( 'remove %1$s from your cart', 'remove %1$s from it', $package_count, 'products with brands', 'woocommerce-conditional-shipping-and-payments' ), $products, $brands );
			} else {
				// translators: %1$s - products, %2$s - brand.
				$message = sprintf( _nx( 'remove %1$s from your cart', 'remove %1$s from it', $package_count, 'products with brand', 'woocommerce-conditional-shipping-and-payments' ), $products, $brands );
			}
		} elseif ( $this->modifier_is( $data['modifier'], array( 'not-in' ) ) ) {
			if ( count( $brand_names ) > 1 ) {
				// translators: %1$s - brands.
				$message = sprintf( _nx( 'add some products with the %s brands to your cart', 'make sure it contains some products with the %s brands', $package_count, 'products with brands', 'woocommerce-conditional-shipping-and-payments' ), $brands );
			} else {
				// translators: %1$s - brand.
				$message = sprintf( _nx( 'add some products with the %s brand to your cart', 'make sure it contains some products with the %s brand', $package_count, 'products with brand', 'woocommerce-conditional-shipping-and-payments' ), $brands );
			}
		} elseif ( $this->modifier_is( $data['modifier'], array( 'all-in' ) ) ) {
			if ( count( $brand_names ) > 1 ) {
				// translators: %1$s - brands.
				$message = sprintf( _nx( 'make sure that your cart doesn\'t only contain products with the %s brand', 'make sure it doesn\'t only contain products with the %s brand', $package_count, 'products with brands', 'woocommerce-conditional-shipping-and-payments' ), $brands );
			} else {
				// translators: %1$s - brands.
				$message = sprintf( _nx( 'make sure that your cart doesn\'t only contain products with the %s brand', 'make sure it doesn\'t only contain products with the %s brand', $package_count, 'products with brand', 'woocommerce-conditional-shipping-and-payments' ), $brands );
			}
		} elseif ( $this->modifier_is( $data['modifier'], array( 'not-all-in' ) ) ) {
			if ( count( $brand_names ) > 1 ) {
				// translators: %1$s - brands.
				$message = sprintf( _nx( 'make sure that your cart contains only products with the %s brands', 'make sure it contains only products with the %s brands', $package_count, 'products with brands', 'woocommerce-conditional-shipping-and-payments' ), $brands );
			} elseif ( 1 === $package_count ) {
				// translators: %1$s - brands.
				$message = sprintf( _nx( 'make sure that your cart contains only products with the %s brand', 'make sure it contains only products with the %s brand', $package_count, 'products with brand', 'woocommerce-conditional-shipping-and-payments' ), $brands );
			}
		}

		return $message;
	}

	/**
	 * Returns condition resolution subjects.
	 *
	 * @param array $data Condition field data.
	 * @param array $args Optional arguments passed by restriction.
	 *
	 * @return array
	 */
	public function get_condition_violation_subjects( array $data, array $args ): array {

		$subjects = array();

		foreach ( $args['package']['contents'] as $cart_item_key => $cart_item ) {
			$terms             = get_the_terms( $cart_item['product_id'], 'product_brand' );
			$product_brand_ids = ( ! is_wp_error( $terms ) && $terms ) ? wp_list_pluck( $terms, 'term_id' ) : array();

			if ( ! empty( $product_brand_ids ) ) {

				$matching_brand_ids = array();

				foreach ( $product_brand_ids as $product_brand_id ) {
					if ( in_array( $product_brand_id, $data['value'], true ) ) {
						$matching_brand_ids[] = $product_brand_id;
					}
				}

				$term_relationship = $this->get_term_relationship();
				$found_item        = false;

				if ( 'or' === $term_relationship && ! empty( $matching_brand_ids ) ) {
					$found_item = true;
				} elseif ( 'and' === $term_relationship && count( $matching_brand_ids ) === count( $data['value'] ) ) {
					$found_item = true;
				}

				// Only 'in' and 'not-all-in' modifiers can have violations.
				if ( $found_item && $this->modifier_is( $data['modifier'], array( 'in' ) ) ) {
					$subjects[] = $cart_item['data']->get_title();
				} elseif ( ! $found_item && $this->modifier_is( $data['modifier'], array( 'not-all-in' ) ) ) {
					$subjects[] = $cart_item['data']->get_title();
				}
			}
		}

		return array_unique( $subjects );
	}

	/**
	 * Evaluate if a condition field is in effect or not.
	 *
	 * @param array $data Condition field data.
	 * @param array $args Optional arguments passed by restrictions.
	 *
	 * @return boolean
	 */
	public function check_condition( $data, $args ): bool {

		// Empty conditions always apply (not evaluated).
		if ( empty( $data['value'] ) ) {
			return true;
		}

		if ( empty( $args['package'] ) || empty( $args['package']['contents'] ) ) {
			return true;
		}

		return $this->check_package( $args['package']['contents'], $data['value'], $data['modifier'] );
	}

	/**
	 * Checks a package.
	 *
	 * @param array  $package_contents Cart items.
	 * @param array  $brand_ids          Brand IDs.
	 * @param string $modifier         Condition modifier.
	 *
	 * @return bool
	 */
	protected function check_package( array $package_contents, array $brand_ids, string $modifier ): bool {
		$found_items = $this->modifier_is( $modifier, array( 'all-in', 'not-all-in' ) );

		foreach ( $package_contents as $cart_item_key => $cart_item ) {
			$terms             = get_the_terms( $cart_item['product_id'], 'product_brand' );
			$product_brand_ids = ( ! is_wp_error( $terms ) && $terms ) ? wp_list_pluck( $terms, 'term_id' ) : array();

			$brands_matching = 0;

			foreach ( $product_brand_ids as $product_brand_id ) {
				if ( in_array( $product_brand_id, $brand_ids, true ) ) {
					++$brands_matching;
				}
			}

			$term_relationship = $this->get_term_relationship();

			if ( $this->modifier_is( $modifier, array( 'in', 'not-in' ) ) ) {
				if ( 'or' === $term_relationship && $brands_matching ) {
					$found_items = true;
				} elseif ( 'and' === $term_relationship && count( $brand_ids ) === $brands_matching ) {
					$found_items = true;
				}

				if ( $found_items ) {
					break;
				}
			} elseif ( $this->modifier_is( $modifier, array( 'all-in', 'not-all-in' ) ) ) {
				if ( 'or' === $term_relationship && ! $brands_matching ) {
					$found_items = false;
				} elseif ( 'and' === $term_relationship && count( $brand_ids ) === $brands_matching ) {
					$found_items = false;
				}

				if ( ! $found_items ) {
					break;
				}
			}
		}

		if ( $found_items ) {
			$result = $this->modifier_is( $modifier, array( 'in', 'all-in' ) );
		} else {
			$result = $this->modifier_is( $modifier, array( 'not-in', 'not-all-in' ) );
		}

		return $result;
	}

	/**
	 * Validate, process and return condition fields.
	 *
	 * @param array $posted_condition_data Posted condition data.
	 *
	 * @return array|bool
	 */
	public function process_admin_fields( $posted_condition_data ) {

		$processed_condition_data = array();

		if ( empty( $posted_condition_data['value'] ) ) {
			return false;
		}

		$processed_condition_data['condition_id'] = $this->id;
		$processed_condition_data['value']        = array_map( 'intval', $posted_condition_data['value'] );
		$processed_condition_data['modifier']     = stripslashes( $posted_condition_data['modifier'] );

		return $processed_condition_data;
	}

	/**
	 * Get brands-in-package condition content for global restrictions.
	 *
	 * @param int   $index           Condition index.
	 * @param int   $condition_index Condition field index.
	 * @param array $condition_data  Condition field data.
	 *
	 * @return void
	 */
	public function get_admin_fields_html( $index, $condition_index, $condition_data ): void {

		$brands   = array();
		$modifier = '';

		if ( ! empty( $condition_data['modifier'] ) ) {
			$modifier = $condition_data['modifier'];
		}

		if ( ! empty( $condition_data['value'] ) ) {
			$brands = $condition_data['value'];
		}

		$product_brands = get_terms(
			array(
				'taxonomy'   => 'product_brand',
				'hide_empty' => false,
			)
		);

		?>
		<input type="hidden"
				name="restriction[<?php echo esc_attr( $index ); ?>][conditions][<?php echo esc_attr( $condition_index ); ?>][condition_id]"
				value="<?php echo esc_attr( $this->id ); ?>"/>
		<div class="condition_row_inner">
			<div class="condition_modifier">
				<div class="sw-enhanced-select">
					<select
						name="restriction[<?php echo esc_attr( $index ); ?>][conditions][<?php echo esc_attr( $condition_index ); ?>][modifier]">
						<option
							value="in" <?php selected( $modifier, 'in', true ); ?>><?php esc_html_e( 'in package', 'woocommerce-conditional-shipping-and-payments' ); ?></option>
						<option
							value="not-in" <?php selected( $modifier, 'not-in', true ); ?>><?php esc_html_e( 'not in package', 'woocommerce-conditional-shipping-and-payments' ); ?></option>
						<option
							value="all-in" <?php selected( $modifier, 'all-in', true ); ?>><?php esc_html_e( 'all items in package', 'woocommerce-conditional-shipping-and-payments' ); ?></option>
						<option
							value="not-all-in" <?php selected( $modifier, 'not-all-in', true ); ?>><?php esc_html_e( 'not all items in package', 'woocommerce-conditional-shipping-and-payments' ); ?></option>
					</select>
				</div>
			</div>
			<div class="condition_value">
				<select
					name="restriction[<?php echo esc_attr( $index ); ?>][conditions][<?php echo esc_attr( $condition_index ); ?>][value][]"
					class="multiselect sw-select2" multiple="multiple"
					data-placeholder="<?php esc_attr_e( 'Select brands&hellip;', 'woocommerce-conditional-shipping-and-payments' ); ?>">
					<?php
					foreach ( $product_brands as $brand ) {
						echo '<option value="' . esc_attr( $brand->term_id ) . '" ' . selected( in_array( $brand->term_id, $brands, true ), true, false ) . '>' . esc_html( $brand->name ) . '</option>';
					}
					?>
				</select>
			</div>
		</div>
		<?php
	}
}
