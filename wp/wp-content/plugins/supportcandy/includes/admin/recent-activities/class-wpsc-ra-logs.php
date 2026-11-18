<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_RA_Logs' ) ) :

	final class WPSC_RA_Logs {

		/**
		 * Initialize this class
		 */
		public static function init() {

			add_action( 'wp_ajax_wpsc_get_recent_activities', array( __CLASS__, 'get_recent_activities' ) );

			add_action( 'wp_ajax_wpsc_ra_agents_autocomplete', array( __CLASS__, 'ra_agents_autocomplete' ) );
			add_action( 'wp_ajax_nopriv_wpsc_ra_agents_autocomplete', array( __CLASS__, 'ra_agents_autocomplete' ) );
		}

		/**
		 * Admin submenu layout
		 *
		 * @return void
		 */
		public static function layout() {

			$customer_id = isset( $_REQUEST['cid'] ) ? intval( $_REQUEST['cid'] ) : 0; // phpcs:ignore
			if ( $customer_id ) {
				$customer = new WPSC_Customer( $customer_id );
			}
			?>
			<div class="wrap">
				<hr class="wp-header-end">
				<div id="wpsc-container">
					<div class="wpsc-setting-header">
						<h2><?php esc_attr_e( 'Recent Activities', 'supportcandy' ); ?></h2>
					</div>
					<form action="#" onsubmit="return false;" class="wpsc-ra-custom-filter">
						<div class="wpsc-setting-section-body">

							<div class="wpsc-ra-toolbar">

								<div class="wpsc-ra-toolbar-item">
									<label><?php esc_attr_e( 'Duration', 'supportcandy' ); ?></label>
									<select id="wpsc-ra-duration-filter">
										<option value="all" selected="selected"><?php esc_attr_e( 'All time', 'supportcandy' ); ?></option>
										<option value="today"><?php esc_attr_e( 'Today', 'supportcandy' ); ?></option>
										<option value="yesterday"><?php esc_attr_e( 'Yesterday', 'supportcandy' ); ?></option>
										<option value="this-week"><?php esc_attr_e( 'This Week', 'supportcandy' ); ?></option>
										<option value="last-week"><?php esc_attr_e( 'Last Week', 'supportcandy' ); ?></option>
										<option value="last-30-days"><?php esc_attr_e( 'Last 30 Days', 'supportcandy' ); ?></option>
										<option value="this-month"><?php esc_attr_e( 'This Month', 'supportcandy' ); ?></option>
										<option value="last-month"><?php esc_attr_e( 'Last Month', 'supportcandy' ); ?></option>
										<option value="custom"><?php esc_attr_e( 'Custom', 'supportcandy' ); ?></option>
									</select>
								</div>

								<div class="wpsc-ra-toolbar-item">
									<label><?php esc_attr_e( 'User', 'supportcandy' ); ?></label>
									<select id="wpsc-customer">
										<?php
										if ( isset( $customer ) && $customer->id ) {
											?>
											<option value="<?php echo intval( $customer->id ); ?>" selected="selected"><?php echo esc_attr( $customer->name ); ?></option>
											<?php
										}
										?>
									</select>
								</div>

								<div class="wpsc-ra-toolbar-item wpsc-ra-from-date-filter" style="display:none;">
									<label><?php esc_attr_e( 'From', 'supportcandy' ); ?></label>
									<input type="text" id="wpsc-ra-from-date" placeholder="YYYY-MM-DD" autocomplete="off" />
								</div>

								<div class="wpsc-ra-toolbar-item wpsc-ra-to-date-filter" style="display:none;">
									<label><?php esc_attr_e( 'To', 'supportcandy' ); ?></label>
									<input type="text" id="wpsc-ra-to-date" placeholder="YYYY-MM-DD" autocomplete="off" />
								</div>

								<div class="wpsc-ra-toolbar-item wpsc-ra-apply-filter" style="display:none;">
									<button type="button" class="wpsc-button normal primary" onclick="wpsc_get_activity_logs();">
										<?php esc_attr_e( 'Apply Filter', 'supportcandy' ); ?>
									</button>
								</div>

								<div class="wpsc-ra-toolbar-item wpsc-ra-toolbar-reset">
									<button type="button" class="wpsc-button normal secondary" id="wpsc-ra-reset-filter">
										<?php esc_attr_e( 'Reset', 'supportcandy' ); ?>
									</button>
								</div>

							</div>

							<div id="wpsc-ra-timeline" class="wpsc-ra-timeline"></div>
							<div class="wpsc-ra-loader" style="display:none;"></div>
							<div class="wpsc-ra-empty" style="display:none;"><?php esc_attr_e( 'No activities found.', 'supportcandy' ); ?></div>
							<div class="wpsc-ra-load-more" style="display:none;">
								<button type="button" class="wpsc-button normal secondary" onclick="wpsc_get_activity_logs( wpsc_ra_page + 1 );"><?php esc_attr_e( 'Load more', 'supportcandy' ); ?></button>
							</div>

							<script>
								var wpsc_ra_page = 1;
								var wpsc_ra_xhr = null;
								function wpsc_get_activity_logs( page ){

									page = page || 1;
									var timeline = jQuery('#wpsc-ra-timeline');
									if ( page === 1 ) {
										timeline.html('');
										jQuery('.wpsc-ra-empty').hide();
									}
									if ( wpsc_ra_xhr ) {
										wpsc_ra_xhr.abort();
									}
									jQuery('.wpsc-ra-load-more').hide();
									jQuery('.wpsc-ra-loader').html(supportcandy.loader_html).show();

									wpsc_ra_xhr = jQuery.post(
										supportcandy.ajax_url,
										{
											action: 'wpsc_get_recent_activities',
											page_no: page,
											from_date: jQuery('#wpsc-ra-from-date').val(),
											to_date: jQuery('#wpsc-ra-to-date').val(),
											customer: jQuery('#wpsc-customer').val(),
											_ajax_nonce: '<?php echo esc_attr( wp_create_nonce( 'wpsc_get_recent_activities' ) ); ?>'
										},
										function( res ) {
											jQuery('.wpsc-ra-loader').hide().html('');
											wpsc_ra_page = page;
											// Items of the same day may span two pages, so append to an existing day group when there is one.
											jQuery.each( res.items, function( i, item ) {
												var day = timeline.children('.wpsc-ra-day[data-date="' + item.date_key + '"]');
												if ( ! day.length ) {
													day = jQuery('<div class="wpsc-ra-day"><div class="wpsc-ra-day-label"></div><ul class="wpsc-ra-day-items"></ul></div>').attr('data-date', item.date_key);
													day.find('.wpsc-ra-day-label').text(item.date_label);
													timeline.append(day);
												}
												day.find('.wpsc-ra-day-items').append(item.html);
											});
											if ( page === 1 && ! timeline.children().length ) {
												jQuery('.wpsc-ra-empty').show();
											}
											if ( res.has_more ) {
												jQuery('.wpsc-ra-load-more').show();
											}
										}
									).fail( function( xhr, status ) {
										if ( status !== 'abort' ) {
											jQuery('.wpsc-ra-loader').hide().html('');
										}
									});
								}
								function wpsc_ra_format_date( d ) {
									return d.getFullYear() + '-' + String( d.getMonth() + 1 ).padStart( 2, '0' ) + '-' + String( d.getDate() ).padStart( 2, '0' );
								}
								function wpsc_ra_duration_range( duration ) {
									// Site's today, so presets match the timezone the server filters in.
									var now = new Date( <?php echo intval( wp_date( 'Y' ) ); ?>, <?php echo intval( wp_date( 'n' ) ) - 1; ?>, <?php echo intval( wp_date( 'j' ) ); ?> );
									var y = now.getFullYear(), m = now.getMonth(), d = now.getDate();
									var monday = d + ( now.getDay() === 0 ? -6 : 1 - now.getDay() );
									var ranges = {
										'today': [ new Date( y, m, d ), new Date( y, m, d ) ],
										'yesterday': [ new Date( y, m, d - 1 ), new Date( y, m, d - 1 ) ],
										'this-week': [ new Date( y, m, monday ), new Date( y, m, monday + 6 ) ],
										'last-week': [ new Date( y, m, monday - 7 ), new Date( y, m, monday - 1 ) ],
										'last-30-days': [ new Date( y, m, d - 29 ), new Date( y, m, d ) ],
										'this-month': [ new Date( y, m, 1 ), new Date( y, m + 1, 0 ) ],
										'last-month': [ new Date( y, m - 1, 1 ), new Date( y, m, 0 ) ]
									};
									if ( ! ranges[ duration ] ) {
										return [ '', '' ];
									}
									return ranges[ duration ].map( wpsc_ra_format_date );
								}
								jQuery(document).ready(function() {

									wpsc_get_activity_logs();

									jQuery('#wpsc-ra-from-date, #wpsc-ra-to-date').flatpickr({
										dateFormat: 'Y-m-d',
										maxDate: '<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>',
										allowInput: true
									});

									jQuery('#wpsc-ra-duration-filter').on('change', function(){
										var duration = jQuery(this).val();
										var isCustom = duration === 'custom';
										jQuery('.wpsc-ra-from-date-filter, .wpsc-ra-to-date-filter, .wpsc-ra-apply-filter').toggle( isCustom );
										if ( isCustom ) {
											return;
										}
										var range = wpsc_ra_duration_range( duration );
										jQuery('#wpsc-ra-from-date').val( range[0] );
										jQuery('#wpsc-ra-to-date').val( range[1] );
										wpsc_get_activity_logs();
									});

									jQuery('#wpsc-customer').on('change', function(){
										wpsc_get_activity_logs();
									});

									jQuery('#wpsc-ra-reset-filter').on('click', function(){
										jQuery('#wpsc-customer').val(null).trigger('change.select2');
										jQuery('#wpsc-ra-duration-filter').val('all').trigger('change');
										window.history.replaceState( {}, null, '<?php echo esc_url_raw( admin_url( 'admin.php?page=wpsc-recent-activities' ) ); ?>' );
									});

									jQuery('select#wpsc-customer').selectWoo({
										ajax: {
											url: supportcandy.ajax_url,
											dataType: 'json',
											delay: 250,
											data: function (params) {
												return {
													q: params.term, // search term
													action: 'wpsc_ra_agents_autocomplete',
													_ajax_nonce: '<?php echo esc_attr( wp_create_nonce( 'wpsc_ra_agents_autocomplete' ) ); ?>'
												};
											},
											processResults: function (data, params) {
												var terms = [];
												if ( data ) {
													jQuery.each( data, function( id, customer ) {
														terms.push({ 
															id: customer.id, 
															text: customer.text,
															name: customer.name
														});
													});
												}
												return {
													results: terms
												};
											},
											cache: true
										},
										escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
										minimumInputLength: 1,
										allowClear: true,
										placeholder:'<?php esc_attr_e( 'All users', 'supportcandy' ); ?>'
									});
								});
							</script>
						</div>
					</form>
				</div>
			</div>
			<?php
		}

		/**
		 * Get all recent activities
		 *
		 * @return void
		 */
		public static function get_recent_activities() {

			if ( check_ajax_referer( 'wpsc_get_recent_activities', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			$page_no    = isset( $_POST['page_no'] ) && intval( $_POST['page_no'] ) > 0 ? intval( $_POST['page_no'] ) : 1;
			$rowperpage = 20;

			$from_date = isset( $_POST['from_date'] ) ? sanitize_text_field( wp_unslash( $_POST['from_date'] ) ) : '';
			$from_date = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $from_date ) ? $from_date : '';
			$to_date   = isset( $_POST['to_date'] ) ? sanitize_text_field( wp_unslash( $_POST['to_date'] ) ) : '';
			$to_date   = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $to_date ) ? $to_date : '';
			$date_range = $from_date || $to_date ? array( $from_date ? $from_date : '1970-01-01', $to_date ? $to_date : wp_date( 'Y-m-d' ) ) : array();

			$customer_id = isset( $_POST['customer'] ) && intval( $_POST['customer'] ) ? intval( $_POST['customer'] ) : 'all';

			$now       = new DateTime();
			$tz        = wp_timezone();
			$today     = wp_date( 'Y-m-d', null, $tz );
			$yesterday = wp_date( 'Y-m-d', strtotime( '-1 day' ), $tz );
			$items     = array();
			$logs      = self::get_activity_logs( $rowperpage, $page_no, $customer_id, $date_range );

			foreach ( $logs['results'] as $log ) {
				$log_str   = '';
				$icon      = '';
				$ticket_a  = '<a href="' . admin_url( 'admin.php?page=wpsc-tickets&section=ticket-list&id=' . esc_attr( $log->ticket->id ) ) . '">#' . $log->ticket->id . ' ' . esc_html( $log->ticket->subject ) . '</a>';
				if ( $log->type == 'report' ) {
					$icon    = 'plus-square';
					$log_str = esc_html( $log->customer->name ) . ' created a ticket ' . $ticket_a;
				} elseif ( $log->type == 'reply' ) {
					$icon    = 'reply';
					$log_str = esc_html( $log->customer->name ) . ' replied to ticket ' . $ticket_a;
				} elseif ( $log->type == 'note' ) {
					$icon    = 'notes';
					$log_str = esc_html( $log->customer->name ) . ' added a note to ticket ' . $ticket_a;
				} elseif ( $log->type == 'log' ) {
					if ( ! $log->customer->id ) {
						continue;
					}
					$body = json_decode( $log->body );
					$is_json = ( json_last_error() == JSON_ERROR_NONE ) ? true : false;
					if ( $is_json ) {
						$cf = WPSC_Custom_Field::get_cf_by_slug( $body->slug );
						if ( ! $cf ) {
							continue;
						}
						$icon    = 'edit';
						$log_str = $cf->type::print_activity( $cf, $log, $body, '0' );
					}
				}
				if ( ! $log_str ) {
					continue;
				}

				$timestamp = $log->date_created->getTimestamp();
				$date_key  = wp_date( 'Y-m-d', $timestamp, $tz );
				if ( $date_key == $today ) {
					$date_label = esc_attr__( 'Today', 'supportcandy' );
				} elseif ( $date_key == $yesterday ) {
					$date_label = esc_attr__( 'Yesterday', 'supportcandy' );
				} else {
					$date_label = wp_date( 'l, ' . get_option( 'date_format' ), $timestamp, $tz );
				}
				$time_ago = WPSC_Functions::date_interval_highest_unit_ago( $log->date_created->diff( $now ) );

				ob_start();
				WPSC_Icons::get( $icon );
				$icon_html = ob_get_clean();

				$items[] = array(
					'date_key'   => $date_key,
					'date_label' => $date_label,
					'html'       => '<li class="wpsc-ra-item wpsc-ra-type-' . esc_attr( $log->type ) . '">
								<span class="wpsc-ra-marker">' . $icon_html . '</span>
								<div class="wpsc-ra-card">
									<div class="wpsc-ra-avatar">' . WPSC_Functions::get_avatar( $log->customer->name, $log->customer->email, $log->customer->id, 32 ) . '</div>
									<div class="wpsc-ra-log-desc">' . $log_str . '</div>
									<span class="wpsc-ra-log-time" title="' . esc_attr( $time_ago ) . '">' . esc_html( wp_date( get_option( 'time_format' ), $timestamp, $tz ) ) . '</span>
								</div>
							</li>',
				);
			}

			$response = array(
				'items'    => $items,
				'has_more' => $page_no * $rowperpage < $logs['total_items'],
			);

			wp_send_json( $response );
		}

		/**
		 * Get activity logs
		 *
		 * @param int    $rowperpage - items per page count.
		 * @param int    $page_no - page number.
		 * @param string $customer_id - customer id.
		 * @param array  $date_range - date range.
		 * @return array
		 */
		public static function get_activity_logs( $rowperpage, $page_no, $customer_id = 'all', $date_range = array() ) {

			$gs = get_option( 'wpsc-db-gs-settings' );
			$current_user = WPSC_Current_User::$current_user;

			$type = array();
			if ( in_array( 'report', $gs['allowed-recent-activity-logs'] ) ) {
				$type[] = 'report';
			}
			if ( in_array( 'reply', $gs['allowed-recent-activity-logs'] ) ) {
				$type[] = 'reply';
			}
			if ( in_array( 'note', $gs['allowed-recent-activity-logs'] ) ) {
				$type[] = 'note';
			}

			$meta_query = array(
				'relation' => 'OR',
				array(
					'relation' => 'AND',
					array(
						'slug'    => 'is_active',
						'compare' => '=',
						'val'     => '1',
					),
					array(
						'slug'    => 'type',
						'compare' => 'IN',
						'val'     => $type,
					),
				),
			);
			if ( $customer_id === 'all' ) {
				$meta_query[0][] = array(
					'slug'    => 'customer',
					'compare' => 'NOT IN',
					'val'     => array( '0' ),
				);
			} else {

				$meta_query[0][] = array(
					'slug'    => 'customer',
					'compare' => 'IN',
					'val'     => array( $customer_id ),
				);
			}

			// Picked dates are in the site timezone while date_created is stored in UTC.
			$date_between = array();
			if ( $date_range ) {
				$from_date = trim( $date_range[0] );
				$to_date = isset( $date_range[1] ) ? trim( $date_range[1] ) : $from_date;
				$date_between = array(
					WPSC_Functions::get_utc_date_str( $from_date . ' 00:00:00' ),
					WPSC_Functions::get_utc_date_str( $to_date . ' 23:59:59' ),
				);
				$meta_query[0][] = array(
					'slug'    => 'date_created',
					'compare' => 'BETWEEN',
					'val'     => $date_between,
				);
			}

			$log_meta = array();
			$cust_fields = array_diff( $gs['allowed-recent-activity-logs'], array( 'report', 'reply', 'note' ) );
			if ( $cust_fields ) {
				$cust_fields = "'" . implode( "','", $cust_fields ) . "'";
				$log_meta = array(
					array(
						'relation' => 'AND',
						array(
							'slug'    => 'type',
							'compare' => '=',
							'val'     => 'log',
						),
						array(
							'slug'    => 'custom_query',
							'compare' => '=',
							'val'     => 'JSON_VALID(body)',
						),
						array(
							'slug'    => 'custom_query',
							'compare' => '=',
							'val'     => "JSON_EXTRACT(body, '$.slug') IN (" . $cust_fields . ')',
						),
					),
				);
				if ( $customer_id === 'all' ) {
					$log_meta[0][] = array(
						'slug'    => 'customer',
						'compare' => 'NOT IN',
						'val'     => array( '0' ),
					);
				} else {

					$log_meta[0][] = array(
						'slug'    => 'customer',
						'compare' => 'IN',
						'val'     => array( $customer_id ),
					);
				}

				if ( $date_between ) {
					$log_meta[0][] = array(
						'slug'    => 'date_created',
						'compare' => 'BETWEEN',
						'val'     => $date_between,
					);
				}
			}
			$meta_query = array_merge( $meta_query, $log_meta );

			$logs = WPSC_Thread::find(
				array(
					'items_per_page' => $rowperpage,
					'page_no'        => $page_no,
					'order'          => 'DESC',
					'order_by'       => 'date_created',
					'meta_query'     => $meta_query,
				)
			);

			return $logs;
		}

		/**
		 * Create as auto-complete
		 *
		 * @return void
		 */
		public static function ra_agents_autocomplete() {

			if ( check_ajax_referer( 'wpsc_ra_agents_autocomplete', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			$current_user = WPSC_Current_User::$current_user;

			if ( ! $current_user->is_agent ) {
				wp_send_json_error( new WP_Error( '001', 'Unauthorized access!' ), 401 );
			}

			$term = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
			if ( ! $term ) {
				wp_send_json_error( new WP_Error( '002', 'Search term should have at least one character!' ), 400 );
			}

			wp_send_json(
				array_map(
					fn( $customer ) => array(
						'id'   => $customer->id,
						'text' => esc_html( $customer->name ),
						'name' => esc_html( $customer->name ),
					),
					WPSC_Customer::customer_search( $term )
				)
			);
		}
	}
endif;
WPSC_RA_Logs::init();
