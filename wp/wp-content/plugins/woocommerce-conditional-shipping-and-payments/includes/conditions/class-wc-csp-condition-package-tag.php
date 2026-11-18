<?php
/**
 * WC_CSP_Condition_Package_Tag class
 *
 * @package  WooCommerce Conditional Shipping and Payments
 * @since    2.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tag in Package Condition.
 *
 * @class    WC_CSP_Condition_Package_Tag
 * @version  3.3.0
 */
class WC_CSP_Condition_Package_Tag extends WC_CSP_Package_Condition {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                            = 'tag_in_package';
		$this->title                         = __( 'Tag', 'woocommerce-conditional-shipping-and-payments' );
		$this->priority                      = 60;
		$this->supported_global_restrictions = array( 'shipping_methods', 'shipping_countries' );
	}

	/**
	 * Tags condition matching relationship. Values 'or' | 'and'.
	 *
	 * @return string
	 */
	protected function get_term_relationship(): string {
		/**
		 * Filters the tags condition matching relationship.
		 *
		 * @since 2.1.0
		 *
		 * @param string $term_relationship Tags condition matching relationship. Values 'or' | 'and'.
		 */
		return apply_filters( 'woocommerce_csp_package_tag_matching_relationship', 'or' );
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

		$tag_names = array();

		foreach ( $data['value'] as $tag_id ) {

			$term = get_term_by( 'id', $tag_id, 'product_tag' );

			if ( $term ) {
				$tag_names[] = $term->name;
			}
		}

		$package_count = $this->get_package_count( $args );
		$tags          = $this->merge_titles( $tag_names, array( 'rel' => $this->get_term_relationship() ) );
		$message       = false;

		if ( $this->modifier_is( $data['modifier'], array( 'in' ) ) ) {

			$product_names = $this->get_condition_violation_subjects( $data, $args );
			$products      = $this->merge_titles( $product_names );

			if ( count( $product_names ) > 4 ) {
				if ( count( $tag_names ) > 1 ) {
					// translators: %1$s - products, %2$s - tags.
					$message = sprintf( _nx( 'remove all products with the %2$s tags from your cart', 'make sure it contains no products with the %2$s tags', $package_count, 'products with tags', 'woocommerce-conditional-shipping-and-payments' ), $products, $tags );
				} else {
					// translators: %1$s - products, %2$s - tag.
					$message = sprintf( _nx( 'remove all products with the %2$s tag from your cart', 'make sure it contains no products with the %2$s tag', $package_count, 'products with tag', 'woocommerce-conditional-shipping-and-payments' ), $products, $tags );
				}
			} elseif ( count( $tag_names ) > 1 ) {
				// translators: %1$s - products, %2$s - tags.
				$message = sprintf( _nx( 'remove %1$s from your cart', 'remove %1$s from it', $package_count, 'products with tags', 'woocommerce-conditional-shipping-and-payments' ), $products, $tags );
			} else {
				// translators: %1$s - products, %2$s - tag.
				$message = sprintf( _nx( 'remove %1$s from your cart', 'remove %1$s from it', $package_count, 'products with tag', 'woocommerce-conditional-shipping-and-payments' ), $products, $tags );
			}
		} elseif ( $this->modifier_is( $data['modifier'], array( 'not-in' ) ) ) {
			if ( count( $tag_names ) > 1 ) {
				// translators: %1$s - tags.
				$message = sprintf( _nx( 'add some products with the %s tags to your cart', 'make sure it contains some products with the %s tags', $package_count, 'products with tags', 'woocommerce-conditional-shipping-and-payments' ), $tags );
			} else {
				// translators: %1$s - tag.
				$message = sprintf( _nx( 'add some products with the %s tag to your cart', 'make sure it contains some products with the %s tag', $package_count, 'products with tag', 'woocommerce-conditional-shipping-and-payments' ), $tags );
			}
		} elseif ( $this->modifier_is( $data['modifier'], array( 'all-in' ) ) ) {
			if ( count( $tag_names ) > 1 ) {
				// translators: %1$s - tags.
				$message = sprintf( _nx( 'make sure that your cart doesn\'t only contain products with the %s tag', 'make sure it doesn\'t only contain products with the %s tag', $package_count, 'products with tags', 'woocommerce-conditional-shipping-and-payments' ), $tags );
			} else {
				// translators: %1$s - tags.
				$message = sprintf( _nx( 'make sure that your cart doesn\'t only contain products with the %s tag', 'make sure it doesn\'t only contain products with the %s tag', $package_count, 'products with tag', 'woocommerce-conditional-shipping-and-payments' ), $tags );
			}
		} elseif ( $this->modifier_is( $data['modifier'], array( 'not-all-in' ) ) ) {
			if ( count( $tag_names ) > 1 ) {
				// translators: %1$s - tags.
				$message = sprintf( _nx( 'make sure that your cart contains only products with the %s tags', 'make sure it contains only products with the %s tags', $package_count, 'products with tags', 'woocommerce-conditional-shipping-and-payments' ), $tags );
			} elseif ( 1 === $package_count ) {
				// translators: %1$s - tags.
				$message = sprintf( _nx( 'make sure that your cart contains only products with the %s tag', 'make sure it contains only products with the %s tag', $package_count, 'products with tag', 'woocommerce-conditional-shipping-and-payments' ), $tags );
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

			$product_tag_ids = array();

			if ( isset( $cart_item['data'] ) && ( $cart_item['data'] instanceof WC_Product ) ) {
				$product         = $cart_item['variation_id'] ? wc_get_product( $cart_item['product_id'] ) : $cart_item['data'];
				$product_tag_ids = $product ? $product->get_tag_ids() : array();
			} else {
				$product_tag_terms = get_the_terms( $cart_item['product_id'], 'product_tag' );
				if ( $product_tag_terms && ! is_wp_error( $product_tag_terms ) ) {
					$product_tag_ids = wp_list_pluck( $product_tag_terms, 'term_id' );
				}
			}

			if ( ! empty( $product_tag_ids ) ) {

				$matching_tag_ids = array();

				foreach ( $product_tag_ids as $product_tag_id ) {
					if ( in_array( $product_tag_id, $data['value'], true ) ) {
						$matching_tag_ids[] = $product_tag_id;
					}
				}

				$term_relationship = $this->get_term_relationship();
				$found_item        = false;

				if ( 'or' === $term_relationship && ! empty( $matching_tag_ids ) ) {
					$found_item = true;
				} elseif ( 'and' === $term_relationship && count( $matching_tag_ids ) === count( $data['value'] ) ) {
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
	 * @param array  $tag_ids          Tag IDs.
	 * @param string $modifier         Condition modifier.
	 *
	 * @return bool
	 */
	protected function check_package( array $package_contents, array $tag_ids, string $modifier ): bool {
		$found_items = $this->modifier_is( $modifier, array( 'all-in', 'not-all-in' ) );

		foreach ( $package_contents as $cart_item_key => $cart_item ) {
			$product_tag_ids = array();

			if ( isset( $cart_item['data'] ) && ( $cart_item['data'] instanceof WC_Product ) ) {
				$product         = $cart_item['variation_id'] ? wc_get_product( $cart_item['product_id'] ) : $cart_item['data'];
				$product_tag_ids = $product ? $product->get_tag_ids() : array();
			} else {
				$product_tag_terms = get_the_terms( $cart_item['product_id'], 'product_tag' );
				if ( $product_tag_terms && ! is_wp_error( $product_tag_terms ) ) {
					$product_tag_ids = wp_list_pluck( $product_tag_terms, 'term_id' );
				}
			}

			$tags_matching = 0;

			foreach ( $product_tag_ids as $product_tag_id ) {
				if ( in_array( $product_tag_id, $tag_ids, true ) ) {
					++$tags_matching;
				}
			}

			$term_relationship = $this->get_term_relationship();

			if ( $this->modifier_is( $modifier, array( 'in', 'not-in' ) ) ) {
				if ( 'or' === $term_relationship && $tags_matching ) {
					$found_items = true;
				} elseif ( 'and' === $term_relationship && count( $tag_ids ) === $tags_matching ) {
					$found_items = true;
				}

				if ( $found_items ) {
					break;
				}
			} elseif ( $this->modifier_is( $modifier, array( 'all-in', 'not-all-in' ) ) ) {
				if ( 'or' === $term_relationship && ! $tags_matching ) {
					$found_items = false;
				} elseif ( 'and' === $term_relationship && count( $tag_ids ) === $tags_matching ) {
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
	 * Get tags-in-package condition content for global restrictions.
	 *
	 * @param int   $index           Condition index.
	 * @param int   $condition_index Condition field index.
	 * @param array $condition_data  Condition field data.
	 *
	 * @return void
	 */
	public function get_admin_fields_html( $index, $condition_index, $condition_data ): void {

		$tags     = array();
		$modifier = '';

		if ( ! empty( $condition_data['modifier'] ) ) {
			$modifier = $condition_data['modifier'];
		}

		if ( ! empty( $condition_data['value'] ) ) {
			$tags = $condition_data['value'];
		}

		$product_tags = get_terms(
			array(
				'taxonomy'   => 'product_tag',
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
					data-placeholder="<?php esc_attr_e( 'Select tags&hellip;', 'woocommerce-conditional-shipping-and-payments' ); ?>">
					<?php
					foreach ( $product_tags as $tag ) {
						echo '<option value="' . esc_attr( $tag->term_id ) . '" ' . selected( in_array( $tag->term_id, $tags, true ), true, false ) . '>' . esc_html( $tag->name ) . '</option>';
					}
					?>
				</select>
			</div>
		</div>
		<?php
	}
}
