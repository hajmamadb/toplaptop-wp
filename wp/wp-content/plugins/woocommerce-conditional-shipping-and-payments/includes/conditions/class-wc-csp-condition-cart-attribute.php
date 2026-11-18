<?php
/**
 * WC_CSP_Condition_Cart_Attribute class
 *
 * @package  WooCommerce Conditional Shipping and Payments
 * @since    2.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Attribute in Cart Condition.
 *
 * @class    WC_CSP_Condition_Cart_Attribute
 * @version  3.3.0
 */
class WC_CSP_Condition_Cart_Attribute extends WC_CSP_Condition {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                            = 'attribute_in_cart';
		$this->title                         = __( 'Product Attribute', 'woocommerce-conditional-shipping-and-payments' );
		$this->priority                      = 60;
		$this->supported_global_restrictions = array( 'payment_gateways' );
	}

	/**
	 * Categories condition matching relationship. Values 'or' | 'and'.
	 *
	 * @return string
	 */
	protected function get_term_relationship() {
		/**
		 * Filters the cart attribute matching relationship.
		 *
		 * @since 2.1.0
		 *
		 * @param string $relationship The cart attribute matching relationship. Default 'or'.
		 */
		return apply_filters( 'woocommerce_csp_cart_attribute_matching_relationship', 'or' );
	}

	/**
	 * Return condition resolution message.
	 *
	 * @param array $data Condition field data.
	 * @param array $args Optional arguments passed by restriction.
	 *
	 * @return string|false
	 */
	public function get_condition_resolution( $data, $args ) {
		if ( empty( $data['value'] ) || empty( $data['attribute'] ) ) {
			return false;
		}

		$cart_contents = WC()->cart->get_cart();

		if ( empty( $cart_contents ) ) {
			return false;
		}

		$attribute_label = wc_attribute_label( wc_sanitize_taxonomy_name( $data['attribute'] ) );
		$value_names     = array();

		foreach ( $data['value'] as $term_id ) {

			$term = get_term_by( 'id', $term_id, 'pa_' . $data['attribute'] );

			if ( $term ) {
				$value_names[] = $term->name;
			}
		}

		$values       = $this->merge_titles( $value_names, array( 'rel' => $this->get_term_relationship() ) );
		$message      = false;
		$values_count = count( $value_names );

		if ( $this->modifier_is( $data['modifier'], array( 'in' ) ) ) {
			$product_names = $this->get_condition_violation_subjects( $data, $args );
			$products      = $this->merge_titles( $product_names );

			if ( count( $product_names ) > 4 ) {
				// translators: %1$s - products, %2$s - attribute label, %3$s - values.
				$message = sprintf( _x( 'remove all products with a %2$s of %3$s from your cart', 'products with attribute', 'woocommerce-conditional-shipping-and-payments' ), $products, $attribute_label, $values );
			} else {
				// translators: %1$s - products, %2$s - attribute label, %3$s - values.
				$message = sprintf( _x( 'remove %1$s from your cart', 'products with attribute', 'woocommerce-conditional-shipping-and-payments' ), $products, $attribute_label, $values );
			}
		} elseif ( $this->modifier_is( $data['modifier'], array( 'not-in' ) ) ) {
			// translators: %1$s - attribute label, %2$s - values.
			$message = sprintf( _x( 'add some products tagged with a %1$s of %2$s to your cart', 'products with attribute', 'woocommerce-conditional-shipping-and-payments' ), $attribute_label, $values );
		} elseif ( $this->modifier_is( $data['modifier'], array( 'all-in' ) ) ) {
			// translators: %1$s - attribute label, %2$s - values.
			$message = sprintf( _x( 'make sure that your cart doesn\'t only contain products with a %1$s of %2$s', 'products with attribute', 'woocommerce-conditional-shipping-and-payments' ), $attribute_label, $values );
		} elseif ( $this->modifier_is( $data['modifier'], array( 'not-all-in' ) ) ) {
			// translators: %1$s - attribute label, %2$s - values.
			$message = sprintf( _x( 'make sure that your cart contains only products with a %1$s of %2$s', 'products with attribute', 'woocommerce-conditional-shipping-and-payments' ), $attribute_label, $values );
		}

		return $message;
	}

	/**
	 * Retrieves the values for a specific product attribute.
	 *
	 * @param WC_Product $product  The product object.
	 * @param string     $taxonomy The attribute name.
	 *
	 * @return array List of attribute values.
	 */
	protected function get_product_attribute_values( WC_Product $product, string $taxonomy ): array {
		// For variations, get attributes from the parent variable product.
		$product = $product->is_type( 'variation' ) ? wc_get_product( $product->get_parent_id() ) : $product;

		// Get attribute values from the product.
		$attributes = $product->get_attributes();
		if ( isset( $attributes[ $taxonomy ] ) ) {
			$values = $attributes[ $taxonomy ]->get_options();
			return array_map( 'intval', (array) $values ); // Normalize values for comparison.
		}

		return array();
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

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_attribute_values = $this->get_product_attribute_values( $cart_item['data'], 'pa_' . $data['attribute'] );

			if ( ! empty( $product_attribute_values ) ) {
				$matching_product_attribute_values = array();

				foreach ( $product_attribute_values as $product_attribute_value ) {
					if ( in_array( $product_attribute_value, $data['value'], true ) ) {
						$matching_product_attribute_values[] = $product_attribute_value;
					}
				}

				$term_relationship = $this->get_term_relationship();
				$found_item        = false;

				if ( 'or' === $term_relationship && ! empty( $matching_product_attribute_values ) ) {
					$found_item = true;
				} elseif ( 'and' === $term_relationship && count( $matching_product_attribute_values ) === count( $data['value'] ) ) {
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
	 * Evaluate if the condition is met.
	 *
	 * @param array $data Condition field data.
	 * @param array $args Optional arguments passed by restriction.
	 *
	 * @return boolean
	 */
	public function check_condition( $data, $args ) {

		if ( empty( $data['value'] ) || empty( $data['attribute'] ) ) {
			return true;
		}

		$attribute = wc_sanitize_taxonomy_name( $data['attribute'] );

		if ( ! empty( $args['order'] ) ) {
			$items = $args['order']->get_items( 'line_item' );
		} else {
			$items = WC()->cart->get_cart();
		}

		$taxonomy = 'pa_' . $attribute;

		return $this->check_items( $items, $taxonomy, $data['value'], $data['modifier'] );
	}

	/**
	 * Checks a set of cart or order items.
	 *
	 * @param array  $items               Cart or order items.
	 * @param string $taxonomy            Attribute taxonomy.
	 * @param array  $attribute_value_ids Tag IDs.
	 * @param string $modifier            Condition modifier.
	 *
	 * @return bool
	 */
	protected function check_items( $items, $taxonomy, $attribute_value_ids, $modifier ) {

		$found_items = $this->modifier_is( $modifier, array( 'all-in', 'not-all-in' ) );

		foreach ( $items as $item_key => $item_data ) {
			$product_attribute_values = array();

			if ( isset( $item_data['data'] ) && ( $item_data['data'] instanceof WC_Product ) ) {
				$product_attribute_values = $this->get_product_attribute_values( $item_data['data'], $taxonomy );
			} else {
				$product_attribute_terms = get_the_terms( $item_data['product_id'], $taxonomy );
				if ( $product_attribute_terms && ! is_wp_error( $product_attribute_terms ) ) {
					$product_attribute_values = wp_list_pluck( $product_attribute_terms, 'term_id' );
				}
			}

			$attributes_matching = 0;

			foreach ( $product_attribute_values as $product_attribute_value ) {
				if ( in_array( $product_attribute_value, $attribute_value_ids, true ) ) {
					++$attributes_matching;
				}
			}

			$term_relationship = $this->get_term_relationship();

			if ( $this->modifier_is( $modifier, array( 'in', 'not-in' ) ) ) {
				if ( 'or' === $term_relationship && $attributes_matching ) {
					$found_items = true;
				} elseif ( 'and' === $term_relationship && count( $attribute_value_ids ) === $attributes_matching ) {
					$found_items = true;
				}

				if ( $found_items ) {
					break;
				}
			} elseif ( $this->modifier_is( $modifier, array( 'all-in', 'not-all-in' ) ) ) {
				if ( 'or' === $term_relationship && ! $attributes_matching ) {
					$found_items = false;
				} elseif ( 'and' === $term_relationship && count( $attribute_value_ids ) !== $attributes_matching ) {
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
	 * @return array|false
	 */
	public function process_admin_fields( $posted_condition_data ) {

		if ( empty( $posted_condition_data['value'] ) || empty( $posted_condition_data['attribute'] ) ) {
			return false;
		}

		return array(
			'condition_id' => $this->id,
			'attribute'    => wc_sanitize_taxonomy_name( $posted_condition_data['attribute'] ),
			'value'        => array_map( 'intval', $posted_condition_data['value'] ),
			'modifier'     => stripslashes( $posted_condition_data['modifier'] ),
		);
	}

	/**
	 * Get admin fields for the condition.
	 *
	 * @param int   $index           Condition index.
	 * @param int   $condition_index Condition field index.
	 * @param array $condition_data  Condition field data.
	 *
	 * @return void
	 */
	public function get_admin_fields_html( $index, $condition_index, $condition_data ) {
		$modifier  = ! empty( $condition_data['modifier'] ) ? $condition_data['modifier'] : '';
		$attribute = ! empty( $condition_data['attribute'] ) ? $condition_data['attribute'] : '';
		$values    = ! empty( $condition_data['value'] ) ? (array) $condition_data['value'] : array();

		// Get all global attributes.
		$global_attributes = wc_get_attribute_taxonomies();

		// Determine the selected attribute or default to the first attribute if available.
		if ( empty( $attribute ) && ! empty( $global_attributes ) ) {
			$attribute = reset( $global_attributes )->attribute_name;
		}

		// Fetch terms for the selected attribute.
		$taxonomy        = 'pa_' . $attribute;
		$attribute_terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
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
							value="in" <?php selected( $modifier, 'in' ); ?>><?php esc_html_e( 'in cart', 'woocommerce-conditional-shipping-and-payments' ); ?></option>
						<option
							value="not-in" <?php selected( $modifier, 'not-in' ); ?>><?php esc_html_e( 'not in cart', 'woocommerce-conditional-shipping-and-payments' ); ?></option>
						<option
							value="all-in" <?php selected( $modifier, 'all-in' ); ?>><?php esc_html_e( 'all cart items', 'woocommerce-conditional-shipping-and-payments' ); ?></option>
						<option
							value="not-all-in" <?php selected( $modifier, 'not-all-in' ); ?>><?php esc_html_e( 'not all cart items', 'woocommerce-conditional-shipping-and-payments' ); ?></option>
					</select>
				</div>
			</div>
			<div class="condition_value select-field">
				<div class="sw-enhanced-select">
					<select class="csp_product_attribute sw-select2"
							name="restriction[<?php echo esc_attr( $index ); ?>][conditions][<?php echo esc_attr( $condition_index ); ?>][attribute]"
							data-placeholder="<?php esc_attr_e( 'Select product attribute&hellip;', 'woocommerce-conditional-shipping-and-payments' ); ?>">
						<?php if ( ! empty( $global_attributes ) ) : ?>
							<?php foreach ( $global_attributes as $attr ) : ?>
								<option
									value="<?php echo esc_attr( $attr->attribute_name ); ?>" <?php selected( $attribute, $attr->attribute_name ); ?>>
									<?php echo esc_html( $attr->attribute_label ); ?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>
			</div>
		</div>

		<div class="condition_row_inner">
			<div class="condition_modifier">
			</div>
			<div class="condition_value excluded_states select-field">
				<select class="csp_product_attribute_value multiselect sw-select2"
						name="restriction[<?php echo esc_attr( $index ); ?>][conditions][<?php echo esc_attr( $condition_index ); ?>][value][]"
						multiple="multiple"
						data-placeholder="<?php esc_attr_e( 'Select attribute values&hellip;', 'woocommerce-conditional-shipping-and-payments' ); ?>"
				>
					<?php if ( ! empty( $attribute_terms ) ) : ?>
						<?php foreach ( $attribute_terms as $term ) : ?>
							<option
								value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected( ! empty( $values ) && in_array( $term->term_id, $values, true ), true ); ?>>
								<?php echo esc_html( $term->name ); ?>
							</option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
				<div class="condition_form_row">
					<a class="wccsp_select_all button" href="#"><?php esc_html_e( 'All', 'woocommerce' ); ?></a>
					<a class="wccsp_select_none button" href="#"><?php esc_html_e( 'None', 'woocommerce' ); ?></a>
				</div>
			</div>
		</div>
		<?php
	}
}
