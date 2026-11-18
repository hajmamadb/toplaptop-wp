<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_Ticket_Conditions' ) ) :

	final class WPSC_Ticket_Conditions {

		/**
		 * Ticket conditions
		 *
		 * @var array
		 */
		public static $conditions = array();

		/**
		 * Ignore custom field types to match for email piping rules
		 *
		 * @var array
		 */
		public static $ignore_cft = array();

		/**
		 * Initialize the class
		 *
		 * @return void
		 */
		public static function init() {

			add_action( 'init', array( __CLASS__, 'load_ticket_conditions' ) );

			add_action( 'wp_ajax_wpsc_tc_get_operators', array( __CLASS__, 'get_operators' ) );
			add_action( 'wp_ajax_nopriv_wpsc_tc_get_operators', array( __CLASS__, 'get_operators' ) );

			add_action( 'wp_ajax_wpsc_tc_get_operand', array( __CLASS__, 'get_operands' ) );
			add_action( 'wp_ajax_nopriv_wpsc_tc_get_operand', array( __CLASS__, 'get_operands' ) );

			// ignore custom field types.
			add_action( 'init', array( __CLASS__, 'set_ignore_cft_list' ) );

			// condition builder live summary.
			add_action( 'wp_ajax_wpsc_tc_get_summary', array( __CLASS__, 'get_summary_ajax' ) );
		}

		/**
		 * Set ignore custom field types for pipe rules
		 *
		 * @return void
		 */
		public static function set_ignore_cft_list() {

			self::$ignore_cft = apply_filters(
				'wpsc_ticket_condition_ignore_cft',
				array(
					'cf_woo_order',
					'cf_woo_subscription',
					'cf_edd_order',
					'cf_tutor_order',
					'cf_learnpress_order',
					'cf_lifter_order',
				)
			);
		}

		/**
		 * Load ticket conditions along with user permitted to utilize them
		 *
		 * @return void
		 */
		public static function load_ticket_conditions() {

			$agent_filters = get_option( 'wpsc-atl-filter-items', array() );
			$customer_filters = get_option( 'wpsc-ctl-filter-items', array() );

			// custom fields.
			foreach ( WPSC_Custom_Field::$custom_fields as $cf ) {

				if ( ! (
					class_exists( $cf->type ) &&
					$cf->type::$is_filter &&
					in_array( $cf->field, array( 'ticket', 'agentonly', 'customer' ) )
				) ) {
					continue;
				}

				$levels = array( 'admin' );

				if ( in_array( $cf->slug, $agent_filters ) ) {
					$levels[] = 'agent';
				}

				if ( in_array( $cf->slug, $customer_filters ) ) {
					$levels[] = 'customer';
				}

				self::$conditions[ $cf->slug ] = array(
					'name'   => $cf->name,
					'type'   => 'cf',
					'levels' => $levels,
				);
			}

			// custom conditions.
			$other_conditions = apply_filters(
				'wpsc_ticket_conditions',
				array(
					'submitted_by' => esc_attr__( 'Ticket submitted by', 'supportcandy' ),
					'user_role'    => esc_attr__( 'WP user role', 'supportcandy' ),
				)
			);

			foreach ( $other_conditions as $slug => $name ) {
				self::$conditions[ $slug ] = array(
					'name'   => $name,
					'type'   => 'other',
					'levels' => array( 'admin' ),
				);
			}
		}

		/**
		 * Print condition input in the form
		 *
		 * @param string  $name - form element name. Helpful to identify if multiple conditions need to be added.
		 * @param string  $hook - filter hook for conditions. Uniquely identify and filter conditions available in the form.
		 * @param string  $set_conditions - pre-defined conditions to print within input.
		 * @param boolean $is_required - whether or not to print required character for label.
		 * @param string  $label - whether or not to print required character for label.
		 * @param boolean $show_tools - whether or not to print plain-English live summary below the builder.
		 * @param string  $desc - short description printed below the label.
		 * @return void
		 */
		public static function print( $name, $hook, $set_conditions = '', $is_required = false, $label = '', $show_tools = false, $desc = '' ) {

			$label = $label ? $label : __( 'Conditions', 'supportcandy' );
			$conditions = apply_filters( $hook, self::$conditions );
			$set_conditions = $set_conditions ? json_decode( html_entity_decode( $set_conditions ), true ) : array();
			$unique_id = wp_unique_id( 'wpsc_' );
			?>
			<div class="wpsc-input-group">
				<div class="label-container">
					<label for="">
						<?php
						echo esc_attr( $label );
						if ( $is_required ) {
							?>
							<span class="required-char">*</span>
							<?php
						}
						?>
					</label>
				</div>
				<?php if ( $desc ) : ?>
					<p class="wpsc-input-desc"><?php echo esc_html( $desc ); ?></p>
				<?php endif; ?>
				<div class="wpsc-form-filter-container <?php echo esc_attr( $name ); ?>">
					<div class="and-container">
						<?php
						if ( $set_conditions ) {
							foreach ( $set_conditions as $and_condition ) {
								?>
								<div class="and-item">
									<div class="or-container">
										<?php
										foreach ( $and_condition as $or_condition ) {
											if ( ! isset( $conditions[ $or_condition['slug'] ] ) ) {
												continue;
											}
											?>
											<div class="wpsc-form-filter-item">
												<div class="content">
													<div class="item">
														<select class="filter" onchange="wpsc_tc_get_operators(this, '<?php echo esc_attr( wp_create_nonce( 'wpsc_tc_get_operators' ) ); ?>');">
															<option value=""><?php esc_attr_e( 'Select field', 'supportcandy' ); ?></option>
															<?php
															foreach ( $conditions as $slug => $item ) {
																?>
																<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $or_condition['slug'], $slug, true ); ?>><?php echo esc_attr( $item['name'] ); ?></option>
																<?php
															}
															?>
														</select>
													</div>
													<?php
													if ( $conditions[ $or_condition['slug'] ]['type'] == 'cf' ) {
														$cf = WPSC_Custom_Field::get_cf_by_slug( $or_condition['slug'] );
														$cf->type::get_operators( $cf, $or_condition );
														$cf->type::get_operands( $or_condition['operator'], $cf, $or_condition );
													} else {
														self::print_operators( $or_condition['slug'], $or_condition );
														self::print_operands( $or_condition['slug'], $or_condition['operator'], $or_condition );
													}
													?>
												</div>
												<div class="remove-container">
													<span onclick="wpsc_remove_condition_item(this)"><?php WPSC_Icons::get( 'times-circle' ); ?></span>
												</div>
											</div>
											<?php
										}
										?>
									</div>
									<button class="wpsc-button small secondary" onclick="wpsc_add_or_condition( this, '<?php echo esc_attr( $unique_id ); ?>' );"><?php esc_html_e( '+ OR', 'supportcandy' ); ?></button>
								</div>
								<?php
							}
						}
						?>
					</div>
					<button class="wpsc-button small secondary" onclick="wpsc_add_and_condition( this, '<?php echo esc_attr( $unique_id ); ?>' );"><?php esc_html_e( '+ AND', 'supportcandy' ); ?></button>
					<?php
					if ( $show_tools ) {
						self::print_tools( $name, $hook );
					}
					?>
				</div>
				<script>jQuery( '.wpsc-form-filter-container select.filter' ).selectWoo();</script>
				<div style="display: none;">
					<div class="and-template <?php echo esc_attr( $unique_id ); ?>">
						<div class="and-item">
							<div class="or-container"></div>
							<button class="wpsc-button small secondary" onclick="wpsc_add_or_condition( this, '<?php echo esc_attr( $unique_id ); ?>' );"><?php esc_html_e( '+ OR', 'supportcandy' ); ?></button>
						</div>
					</div>
					<div class="or-template <?php echo esc_attr( $unique_id ); ?>">
						<div class="wpsc-form-filter-item">
							<div class="content">
								<div class="item">
									<select class="filter" onchange="wpsc_tc_get_operators(this, '<?php echo esc_attr( wp_create_nonce( 'wpsc_tc_get_operators' ) ); ?>');">
										<option value=""><?php esc_attr_e( 'Select field', 'supportcandy' ); ?></option>
										<?php
										foreach ( $conditions as $slug => $item ) {
											?>
											<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_attr( $item['name'] ); ?></option>
											<?php
										}
										?>
									</select>
								</div>
							</div>
							<div class="remove-container">
								<span onclick="wpsc_remove_condition_item(this)"><?php WPSC_Icons::get( 'times-circle' ); ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Check whether or not conditions submitted by client are valid or not before inserting into the database or any further use
		 *
		 * @param string $hook - filter hook for conditions. Uniquely identify and filter conditions available in the form.
		 * @param string $conditions - conditions received from the client.
		 * @return boolean
		 */
		public static function is_valid_input_conditions( $hook, $conditions ) {

			// No conditions.
			if ( ! $conditions || $conditions == '[]' ) {
				return true;
			}

			$allowed_conditions = apply_filters( $hook, self::$conditions );
			$conditions = json_decode( html_entity_decode( $conditions ), true );

			foreach ( $conditions as $and_condition ) {
				foreach ( $and_condition as $or_condition ) {
					if ( ! isset( $allowed_conditions[ $or_condition['slug'] ] ) ) {
						return false;
					}
				}
			}

			return true;
		}

		/**
		 * Get condition operators ajax callback.
		 *
		 * @return void
		 */
		public static function get_operators() {

			$current_user = WPSC_Current_User::$current_user;

			if ( check_ajax_referer( 'wpsc_tc_get_operators', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
			if ( ! $slug || ! array_key_exists( $slug, self::$conditions ) ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			if ( ! in_array( $current_user->level, self::$conditions[ $slug ]['levels'] ) ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( self::$conditions[ $slug ]['type'] == 'cf' ) {

				$cf = WPSC_Custom_Field::get_cf_by_slug( $slug );
				$cf->type::get_operators( $cf );

			} else {

				self::print_operators( $slug );
			}

			wp_die();
		}

		/**
		 * Print condition operators
		 *
		 * @param string $slug - condition slug.
		 * @param array  $filter - predefined condition to set operator.
		 * @return void
		 */
		public static function print_operators( $slug, $filter = array() ) {

			switch ( $slug ) {

				case 'submitted_by':
					?>
					<div class="item conditional">
						<select class="operator" onchange="wpsc_tc_get_operand(this, '<?php echo esc_attr( $slug ); ?>', '<?php echo esc_attr( wp_create_nonce( 'wpsc_tc_get_operand' ) ); ?>');">
							<option value=""><?php esc_attr_e( 'Compare As', 'supportcandy' ); ?></option>
							<option <?php isset( $filter['operator'] ) && selected( $filter['operator'], '=' ); ?> value="="><?php esc_attr_e( 'Equals', 'supportcandy' ); ?></option>
						</select>
					</div>
					<?php
					break;

				case 'user_role':
					?>
					<div class="item conditional">
						<select class="operator" onchange="wpsc_tc_get_operand(this, '<?php echo esc_attr( $slug ); ?>', '<?php echo esc_attr( wp_create_nonce( 'wpsc_tc_get_operand' ) ); ?>');">
							<option value=""><?php esc_attr_e( 'Compare As', 'supportcandy' ); ?></option>
							<option <?php isset( $filter['operator'] ) && selected( $filter['operator'], '=' ); ?> value="="><?php esc_attr_e( 'Equals', 'supportcandy' ); ?></option>
							<option <?php isset( $filter['operator'] ) && selected( $filter['operator'], 'IN' ); ?> value="IN"><?php esc_attr_e( 'Matches', 'supportcandy' ); ?></option>
							<option <?php isset( $filter['operator'] ) && selected( $filter['operator'], 'NOT IN' ); ?> value="NOT IN"><?php esc_attr_e( 'Not Matches', 'supportcandy' ); ?></option>
						</select>
					</div>
					<?php
					break;

				default:
					do_action( 'wpsc_tc_print_operators', $slug, $filter );
			}
		}

		/**
		 * Get condition operands ajax callback.
		 *
		 * @return void
		 */
		public static function get_operands() {

			$current_user = WPSC_Current_User::$current_user;

			if ( check_ajax_referer( 'wpsc_tc_get_operand', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
			if ( ! $slug || ! array_key_exists( $slug, self::$conditions ) ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			if ( ! in_array( $current_user->level, self::$conditions[ $slug ]['levels'] ) ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			$operator = isset( $_POST['operator'] ) ? sanitize_text_field( wp_unslash( $_POST['operator'] ) ) : '';
			if ( ! $operator ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			if ( self::$conditions[ $slug ]['type'] == 'cf' ) {

				$cf = WPSC_Custom_Field::get_cf_by_slug( $slug );
				$cf->type::get_operands( $operator, $cf );

			} else {

				self::print_operands( $slug, $operator );
			}

			wp_die();
		}

		/**
		 * Print condition operands
		 *
		 * @param string $slug - condition slug.
		 * @param string $operator - operator string.
		 * @param array  $filter - predefined condition to set operand.
		 * @return void
		 */
		public static function print_operands( $slug, $operator, $filter = array() ) {

			global $wp_roles;

			switch ( $slug ) {

				case 'submitted_by':
					?>
					<div class="item conditional operand single">
						<select 
							class="operand_val_1">
							<option <?php isset( $filter['operand_val_1'] ) && selected( $filter['operand_val_1'], 'user' ); ?> value="user"><?php esc_attr_e( 'User', 'supportcandy' ); ?></option>
							<option <?php isset( $filter['operand_val_1'] ) && selected( $filter['operand_val_1'], 'agent' ); ?> value="agent"><?php esc_attr_e( 'Agent', 'supportcandy' ); ?></option>
						</select>
					</div>
					<?php
					break;

				case 'user_role':
					$is_multiple = $operator !== '=' ? true : false;
					$unique_id   = uniqid( 'wpsc_' );
					?>
					<div class="item conditional operand single">
						<select 
							class="operand_val_1 <?php echo esc_attr( $unique_id ); ?>" <?php echo $is_multiple ? 'multiple' : ''; ?>>
							<?php
							foreach ( $wp_roles->roles as $key => $role ) {
								$selected = '';
								if ( isset( $filter['operand_val_1'] ) && ( ( $is_multiple && in_array( $key, $filter['operand_val_1'] ) ) || ( ! $is_multiple && $key == $filter['operand_val_1'] ) ) ) {
									$selected = 'selected="selected"';
								}
								?>
								<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $role['name'] ); ?></option>
								<?php
							}
							?>
						</select>
					</div>
					<script>jQuery('.operand_val_1.<?php echo esc_attr( $unique_id ); ?>').selectWoo();</script>
					<?php
					break;

				default:
					do_action( 'wpsc_tc_print_operand', $slug, $operator, $filter );
			}
		}

		/**
		 * Get meta query for the given conditions
		 *
		 * @param string $conditions - conditions json from database.
		 * @param string $is_user_query - check if it needs to check the permission of the filter of current user. e.g. custom filters, saved filters need to check permissions but default filters need not.
		 * @return array
		 */
		public static function get_meta_query( $conditions, $is_user_query = false ) {

			$current_user = WPSC_Current_User::$current_user;

			$meta_query = array();
			$conditions = $conditions ? json_decode( html_entity_decode( $conditions ), true ) : array();
			if ( ! $conditions ) {
				return $meta_query;
			}

			foreach ( $conditions as $and_condition ) {

				$temp = array();
				foreach ( $and_condition as $or_condition ) {

					if (
						! isset( self::$conditions[ $or_condition['slug'] ] ) ||
						( $is_user_query && ! in_array( $current_user->level, self::$conditions[ $or_condition['slug'] ]['levels'] ) )
					) {
						continue;
					}

					$cf = WPSC_Custom_Field::get_cf_by_slug( $or_condition['slug'] );
					$val = $cf->type::get_meta_value( $or_condition );
					if ( $val === false ) {
						continue;
					}

					$temp[] = array(
						'slug'    => $or_condition['slug'],
						'compare' => $or_condition['operator'],
						'val'     => $val,
					);
				}

				if ( $temp ) {
					$meta_query[] = array_merge( array( 'relation' => 'OR' ), $temp );
				}
			}

			return $meta_query;
		}

		/**
		 * Print live plain-English summary for the condition builder of given name
		 *
		 * @param string $name - form element name used in print function.
		 * @param string $hook - filter hook for conditions used in print function.
		 * @return void
		 */
		public static function print_tools( $name, $hook ) {

			$nonce = wp_create_nonce( 'wpsc_tc_tools_' . $hook );
			?>
			<div class="wpsc-tc-tools <?php echo esc_attr( $name ); ?>" style="display: none; width: 100%; text-align: start;">
				<div class="wpsc-tc-summary">
					<strong><?php esc_html_e( 'Summary:', 'supportcandy' ); ?></strong>
					<span class="text"></span>
				</div>
			</div>
			<script>wpsc_tc_init_summary( '<?php echo esc_attr( $name ); ?>', '<?php echo esc_attr( $hook ); ?>', '<?php echo esc_attr( $nonce ); ?>' );</script>
			<?php
		}

		/**
		 * Get plain-English summary of the conditions
		 *
		 * @param string|array $conditions - conditions json from database or already decoded array.
		 * @param string       $filter - filter hook for conditions (same as one used in print function). Use to get names of the conditions available in the form.
		 * @param boolean      $html - true to get escaped html with AND and OR in bold.
		 * @return string - plain text (not escaped) by default. Empty string if there are no conditions.
		 */
		public static function get_summary( $conditions, $filter = '', $html = false ) {

			if ( ! is_array( $conditions ) ) {
				$conditions = $conditions ? json_decode( html_entity_decode( $conditions ), true ) : array();
			}
			if ( ! is_array( $conditions ) || ! $conditions ) {
				return '';
			}

			$allowed = $filter ? apply_filters( $filter, self::$conditions ) : array();
			$allowed = $allowed + self::$conditions;

			$groups = array();
			foreach ( $conditions as $and_condition ) {

				$items = array();
				foreach ( (array) $and_condition as $or_condition ) {
					$text = self::get_condition_summary( $or_condition, $allowed, $html );
					if ( $text !== '' ) {
						$items[] = $text;
					}
				}

				if ( ! $items ) {
					continue;
				}

				$groups[] = $items;
			}

			$and = ' ' . esc_html__( 'AND', 'supportcandy' ) . ' ';
			$or  = ' ' . esc_html__( 'OR', 'supportcandy' ) . ' ';
			if ( $html ) {
				$and    = ' <strong>' . esc_html__( 'AND', 'supportcandy' ) . '</strong> ';
				$or     = ' <strong>' . esc_html__( 'OR', 'supportcandy' ) . '</strong> ';
			}

			$texts = array();
			foreach ( $groups as $items ) {
				$text    = implode( $or, $items );
				$texts[] = count( $items ) > 1 && count( $groups ) > 1 ? '(' . $text . ')' : $text;
			}

			return implode( $and, $texts );
		}

		/**
		 * Get plain-English summary of a single condition
		 *
		 * @param array   $condition - condition (slug, operator, operand_val_1 and operand_val_2).
		 * @param array   $allowed - conditions available in the form.
		 * @param boolean $html - true to get escaped html.
		 * @return string - empty string if condition is not valid or its field does not exist anymore.
		 */
		public static function get_condition_summary( $condition, $allowed, $html = false ) {

			if ( ! is_array( $condition ) || ! isset( $condition['slug'], $condition['operator'] ) || ! isset( $allowed[ $condition['slug'] ] ) ) {
				return '';
			}

			$cf = null;
			if ( $allowed[ $condition['slug'] ]['type'] == 'cf' ) {
				$cf = WPSC_Custom_Field::get_cf_by_slug( $condition['slug'] );
				if ( ! $cf ) {
					return '';
				}
			}

			$is_date = $cf && ( $cf->type::$ref_class == 'datetime' || $cf->type::$slug == 'cf_time' );

			$short_filters = array(
				'today'        => esc_attr__( 'is today', 'supportcandy' ),
				'yesterday'    => esc_attr__( 'is yesterday', 'supportcandy' ),
				'this-week'    => esc_attr__( 'is this week', 'supportcandy' ),
				'last-week'    => esc_attr__( 'is last week', 'supportcandy' ),
				'last-7-days'  => esc_attr__( 'is in the last 7 days', 'supportcandy' ),
				'last-30-days' => esc_attr__( 'is in the last 30 days', 'supportcandy' ),
				'this-month'   => esc_attr__( 'is this month', 'supportcandy' ),
				'last-month'   => esc_attr__( 'is last month', 'supportcandy' ),
			);

			// escape text when html is requested.
			$esc = $html ? 'esc_html' : fn( $text ) => $text;

			$name     = $esc( $allowed[ $condition['slug'] ]['name'] );
			$operator = $condition['operator'];
			if ( isset( $short_filters[ $operator ] ) ) {
				return $name . ' ' . $esc( $short_filters[ $operator ] );
			}

			$values = isset( $condition['operand_val_1'] ) ? (array) $condition['operand_val_1'] : array();
			$labels = array();
			foreach ( $values as $value ) {
				$labels[] = self::get_operand_label( $condition['slug'], $cf, $value );
			}
			$operand = $esc( implode( ', ', $labels ) );

			switch ( $operator ) {

				case '=':
					$text = $esc( esc_attr__( 'is', 'supportcandy' ) ) . ' ' . $operand;
					break;

				case 'IN':
					$text = $esc( count( $labels ) > 1 ? esc_attr__( 'is one of', 'supportcandy' ) : esc_attr__( 'is', 'supportcandy' ) ) . ' ' . $operand;
					break;

				case 'NOT IN':
					$text = $esc( count( $labels ) > 1 ? esc_attr__( 'is not one of', 'supportcandy' ) : esc_attr__( 'is not', 'supportcandy' ) ) . ' ' . $operand;
					break;

				case 'LIKE':
					$text = $esc( esc_attr__( 'contains', 'supportcandy' ) ) . ' "' . $operand . '"';
					break;

				case 'NOT LIKE':
					$text = $esc( esc_attr__( 'does not contain', 'supportcandy' ) ) . ' "' . $operand . '"';
					break;

				case '<':
					$text = $esc( $is_date ? esc_attr__( 'is before', 'supportcandy' ) : esc_attr__( 'is less than', 'supportcandy' ) ) . ' ' . $operand;
					break;

				case '<=':
					$text = $esc( $is_date ? esc_attr__( 'is on or before', 'supportcandy' ) : esc_attr__( 'is less than or equal to', 'supportcandy' ) ) . ' ' . $operand;
					break;

				case '>':
					$text = $esc( $is_date ? esc_attr__( 'is after', 'supportcandy' ) : esc_attr__( 'is greater than', 'supportcandy' ) ) . ' ' . $operand;
					break;

				case '>=':
					$text = $esc( $is_date ? esc_attr__( 'is on or after', 'supportcandy' ) : esc_attr__( 'is greater than or equal to', 'supportcandy' ) ) . ' ' . $operand;
					break;

				case 'BETWEEN':
					$to   = $esc( isset( $condition['operand_val_2'] ) ? $condition['operand_val_2'] : '' );
					$text = $esc( esc_attr__( 'is between', 'supportcandy' ) ) . ' ' . $operand . ' ' . $esc( esc_attr__( 'and', 'supportcandy' ) ) . ' ' . $to;
					break;

				default:
					$text = $esc( $operator ) . ' ' . $operand;
			}

			return $name . ' ' . $text;
		}

		/**
		 * Get human readable label for an operand value of a condition. Ids are replaced with names.
		 *
		 * @param string                 $slug - condition slug.
		 * @param WPSC_Custom_Field|null $cf - custom field object if condition is of custom field type.
		 * @param mixed                  $value - operand value.
		 * @return string
		 */
		public static function get_operand_label( $slug, $cf, $value ) {

			$label = null;
			if ( is_scalar( $value ) ) {

				if ( $cf ) {

					$type = $cf->type;
					if ( $type::$slug == 'df_user_type' ) {

						$label = $value == 'guest' ? esc_attr__( 'Guest', 'supportcandy' ) : esc_attr__( 'Registered', 'supportcandy' );

					} elseif ( property_exists( $type, 'sources' ) && isset( $type::$sources[ $value ] ) ) {

						$label = $type::$sources[ $value ];

					} elseif ( $type::$has_ref && $type::$ref_class && $type::$ref_class != 'datetime' && ctype_digit( (string) $value ) ) {

						$label = '#' . $value;
						if ( ! intval( $value ) ) {
							$label = esc_attr__( 'None', 'supportcandy' );
						} elseif ( class_exists( $type::$ref_class ) ) {
							$object = new $type::$ref_class( intval( $value ) );
							$data   = method_exists( $object, 'to_array' ) ? $object->to_array() : array();
							foreach ( array( 'name', 'title' ) as $key ) {
								if ( ! empty( $data[ $key ] ) ) {
									$label = $data[ $key ];
									break;
								}
							}
						}
					}
				} elseif ( $slug == 'user_role' ) {

					$roles = wp_roles()->role_names;
					$label = isset( $roles[ $value ] ) ? translate_user_role( $roles[ $value ] ) : null;

				} elseif ( $slug == 'submitted_by' ) {

					$label = $value == 'agent' ? esc_attr__( 'Agent', 'supportcandy' ) : esc_attr__( 'User', 'supportcandy' );
				}
			}

			if ( $label === null ) {
				$label = is_scalar( $value ) ? (string) $value : '';
			}

			return apply_filters( 'wpsc_tc_summary_operand_label', $label, $slug, $value );
		}

		/**
		 * Get condition summary ajax callback (for live summary under condition builder)
		 *
		 * @return void
		 */
		public static function get_summary_ajax() {

			$hook = isset( $_POST['hook'] ) ? sanitize_text_field( wp_unslash( $_POST['hook'] ) ) : '';
			if ( ! WPSC_Functions::is_site_admin() || check_ajax_referer( 'wpsc_tc_tools_' . $hook, '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			$conditions = isset( $_POST['conditions'] ) ? sanitize_text_field( wp_unslash( $_POST['conditions'] ) ) : '';
			wp_send_json_success( array( 'summary' => self::get_summary( $conditions, $hook, true ) ) );
		}

		/**
		 * Return whether or not given conditions are satisfied by the given ticket
		 *
		 * @param string      $conditions - conditions json from database.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return boolean
		 */
		public static function is_valid( $conditions, $ticket ) {

			$conditions = $conditions ? json_decode( html_entity_decode( $conditions ), true ) : array();
			if ( ! $conditions ) {
				return true;
			}

			foreach ( $conditions as $and_condition ) {

				$flag = false;
				foreach ( $and_condition as $or_condition ) {

					if ( ! isset( $or_condition['slug'] ) || ! isset( self::$conditions[ $or_condition['slug'] ] ) ) {
						continue;
					}

					if ( self::$conditions[ $or_condition['slug'] ]['type'] == 'cf' ) {

						$cf = WPSC_Custom_Field::get_cf_by_slug( $or_condition['slug'] );
						if ( in_array( $cf->type::$slug, self::$ignore_cft ) ) {
							continue;
						}
						if ( $cf->field == 'customer' ) {
							$flag = $cf->type::is_valid_customer_condition( $or_condition, $cf, $ticket->customer );
						} else {
							$flag = $cf->type::is_valid_ticket_condition( $or_condition, $cf, $ticket );
						}
					} else {

						switch ( $or_condition['slug'] ) {

							case 'submitted_by':
								if (
									(
										$or_condition['operand_val_1'] == 'agent' &&
										is_object( $ticket->agent_created )
									) ||
									(
										$or_condition['operand_val_1'] == 'user' &&
										! is_object( $ticket->agent_created )
									)
								) {
									$flag = true;
								}
								break;

							case 'user_role':
								$user_roles = $ticket->customer->user->roles;
								switch ( $or_condition['operator'] ) {

									case '=':
										$flag = in_array( $or_condition['operand_val_1'], $user_roles ) ? true : false;
										break;

									case 'IN':
										foreach ( $user_roles as $id ) {
											if ( in_array( $id, $or_condition['operand_val_1'] ) ) {
												$flag = true;
												break;
											}
										}
										break;

									case 'NOT IN':
										$temp = true;
										foreach ( $user_roles as $id ) {
											if ( in_array( $id, $or_condition['operand_val_1'] ) ) {
												$temp = false;
												break;
											}
										}
										$flag = $temp;
										break;
								}
								break;

							default:
								$flag = apply_filters( 'wpsc_tc_is_valid', $flag, $or_condition['slug'], $or_condition, $ticket );
								break;
						}
					}

					// no need to check further OR conditions if this condition is TRUE.
					if ( $flag ) {
						break;
					}
				}

				// no need to check further AND conditions if this condition is FALSE.
				if ( ! $flag ) {
					return false;
				}
			}

			// if we make upto this point, all conditions are satisfied.
			return true;
		}
	}
endif;

WPSC_Ticket_Conditions::init();
