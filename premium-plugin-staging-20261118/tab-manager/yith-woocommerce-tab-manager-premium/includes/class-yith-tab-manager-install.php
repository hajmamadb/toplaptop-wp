<?php
/**
 * This class manage updates , create table and register data stores
 *
 * @package YITH/TabManager/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Tab_Manager_Install' ) ) {
	/**
	 * The class that manage updates
	 */
	class YITH_Tab_Manager_Install {
		use YITH_Tab_Manager_Trait_Singleton;

		/**
		 * The updates to fire.
		 *
		 * @var callable[][]
		 */
		private $db_updates = array(
			'2.0.0' => array(
				'yith_tab_manager_update_200_porting_plugin_tabs',
				'yith_tab_manager_update_200_porting_options',
				'yith_tab_manager_update_200_db_version',
			),
			'2.0.1' => array(
				'yith_tab_manager_update_201_fix_porting_plugin_tab',
				'yith_tab_manager_update_201_db_version'
			),
			'2.0.2' => array(
				'yith_tab_manager_update_202_fix_porting_plugin_tab',
				'yith_tab_manager_update_202_db_version'
			)
		);


		/**
		 * Callbacks to be fired soon, instead of being scheduled.
		 *
		 * @var callable[]
		 */
		private $soon_callbacks = array();
		/**
		 * The version option.
		 */
		const VERSION_OPTION = 'yith_woocommerce_tab_manager_version';

		/**
		 * The version option.
		 */
		const DB_VERSION_OPTION = 'yith_tab_manager_db_version';

		/**
		 * The update scheduled option.
		 */
		const DB_UPDATE_SCHEDULED_OPTION = 'yith_tab_manager_db_update_scheduled_for';

		/**
		 * The construct
		 */
		protected function __construct() {
			$this->install_data_stores();
			add_action( 'init', array( $this, 'check_version' ), 5 );
			add_action( 'yith_tab_manager_run_update_callback', array( $this, 'run_update_callback' ) );
			add_action( 'yith_tab_manager_porting_product', array( $this, 'run_update_single_product' ), 10, 3 );
		}

		/**
		 * Get list of DB update callbacks.
		 *
		 * @return array
		 */
		public function get_db_update_callbacks() {
			return $this->db_updates;
		}

		/**
		 * Return true if the callback needs to be fired soon, instead of being scheduled.
		 *
		 * @param string $callback The callback name.
		 *
		 * @return bool
		 */
		private function is_soon_callback( $callback ) {
			return in_array( $callback, $this->soon_callbacks, true );
		}

		/**
		 * Check if install updates
		 *
		 * @return void
		 */
		public function check_version() {

			if ( version_compare( get_option( self::VERSION_OPTION, '1.0.0' ), YWTM_VERSION, '<' ) ) {
				$this->install();
				/**
				 * DO_ACTION: yith_tab_manager_updated
				 * Action fired after updating the plugin.
				 */
				do_action( 'yith_tab_manager_updated' );

			}
		}

		/**
		 * Install
		 *
		 * @return void
		 */
		public function install() {
			// Check if we are not already running this routine.

			if ( 'yes' === get_transient( 'yith_tab_manager_installing' ) ) {
				return;
			}

			set_transient( 'yith_tab_manager_installing', 'yes', MINUTE_IN_SECONDS * 10 );
			if ( ! defined( 'YITH_TAB_MANAGER_INSTALLING' ) ) {
				define( 'YITH_TAB_MANAGER_INSTALLING', true );
			}

			$this->maybe_create_default_tabs();
			$this->maybe_update_db_version();
			update_option( self::VERSION_OPTION, YWTM_VERSION );

			delete_transient( 'yith_tab_manager_installing' );

			/**
			 * DO_ACTION: yith_tab_manager_installed
			 * Action fired after installing the plugin.
			 */
			do_action( 'yith_tab_manager_installed' );
		}

		/**
		 * In a fresh installation or after update to 1.x.x to 2.x.x the plugin needs to create the WooCommerce Tabs
		 *
		 * @throws Exception The error.
		 */
		public function maybe_create_default_tabs() {

			$wc_tabs_added = get_option( 'yith_tab_manager_wc_added', array() );
			$tabs_to_add   = array(
				'description'            => array(
					'title'    => __( 'Description', 'woocommerce' ),
					'priority' => 1,
				),
				'additional_information' => array(
					'title'    => __( 'Additional Information', 'woocommerce' ),
					'priority' => 2,
				),
				'reviews'                => array(
					'title'    => __( 'Reviews', 'woocommerce' ),
					'priority' => 3,
				),
			);

			if ( empty( $wc_tabs_added ) ) {
				$wc_tabs = ywtm_get_tabs(
					array(
						'origin' => 'woocommerce',
						'return' => 'ids',
					)
				);

				if ( empty( $wc_tabs ) ) {

					foreach ( $tabs_to_add as $type => $tab ) {
						$new_tab = ywtm_get_tab();
						$new_tab->set_name( $tab['title'] );
						$new_tab->set_order( $tab['priority'] );
						$new_tab->set_is_editable( 'no' );
						$new_tab->set_origin( 'woocommerce' );
						$new_tab->save();
						$wc_tabs_added[ $type ] = $new_tab->get_id();
					}
				}

				update_option( 'yith_tab_manager_wc_added', $wc_tabs_added );
			}
		}

		/**
		 * Maybe update db
		 */
		private function maybe_update_db_version() {

			if ( $this->needs_db_update() ) {

				$this->update();
			} else {
				$this->update_db_version();
			}
		}

		/**
		 * The DB needs to be updated?
		 *
		 * @return bool
		 */
		public function needs_db_update() {
			$current_db_version = get_option( self::DB_VERSION_OPTION, '1.0.0' );

			return version_compare( $current_db_version, $this->get_greatest_db_version_in_updates(), '<' );
		}

		/**
		 * Retrieve the major version in update callbacks.
		 *
		 * @return string
		 */
		private function get_greatest_db_version_in_updates() {
			$update_callbacks = $this->get_db_update_callbacks();
			$update_versions  = array_keys( $update_callbacks );
			usort( $update_versions, 'version_compare' );

			return end( $update_versions );
		}

		/**
		 * Update DB version to current.
		 *
		 * @param string|null $version New DB version or null.
		 */
		public static function update_db_version( $version = null ) {
			delete_option( self::DB_VERSION_OPTION );
			add_option( self::DB_VERSION_OPTION, is_null( $version ) ? YWTM_VERSION : $version );

			// Delete "update scheduled for" option, to allow future update scheduling.
			delete_option( self::DB_UPDATE_SCHEDULED_OPTION );
		}

		/**
		 * Check if there are update in progress
		 *
		 * @return bool
		 */
		public static function update_in_progess() {
			$update_in_progess = WC()->queue()->search(
				array(
					'group'  => 'yith-tab-manager-db-updates',
					'status' => ActionScheduler_Store::STATUS_PENDING,
				),
				ARRAY_A
			);

			$product_update_in_proqress = WC()->queue()->search(
				array(
					'hook'   => 'yith_tab_manager_porting_product',
					'status' => ActionScheduler_Store::STATUS_PENDING,
				),
				ARRAY_A
			);
			$total                      = count( $update_in_progess ) + count( $product_update_in_proqress );

			return $total > 0;
		}

		/**
		 * Install data stores for the plugin
		 *
		 * @return void.
		 */
		protected function install_data_stores() {
			add_filter( 'woocommerce_data_stores', array( $this, 'add_data_stores' ) );
		}

		/**
		 * Add plugin's data stores to list of available ones
		 *
		 * @param array $data_stores Available Data Stores.
		 *
		 * @return array Filtered array of Data Stores
		 */
		public function add_data_stores( $data_stores ) {
			return array_merge(
				$data_stores,
				array(
					'ywtm-data-store' => 'YITH_Tab_Manager_Data_Store',
				)
			);
		}

		/**
		 * Push all needed DB updates to the queue for processing.
		 */
		private function update() {
			$current_db_version   = get_option( self::DB_VERSION_OPTION, '1.0.0' );
			$loop                 = 0;
			$greatest_version     = $this->get_greatest_db_version_in_updates();
			$is_already_scheduled = get_option( self::DB_UPDATE_SCHEDULED_OPTION, '' ) === $greatest_version;
			if ( ! $is_already_scheduled ) {
				foreach ( $this->get_db_update_callbacks() as $version => $update_callbacks ) {
					if ( version_compare( $current_db_version, $version, '<' ) ) {
						foreach ( $update_callbacks as $update_callback ) {
							if ( $this->is_soon_callback( $update_callback ) ) {
								$this->run_update_callback( $update_callback );
							} else {

								WC()->queue()->schedule_single(
									time() + $loop,
									'yith_tab_manager_run_update_callback',
									array(
										'update_callback' => $update_callback,
									),
									'yith-tab-manager-db-updates'
								);
								++ $loop;
							}
						}
					}
				}
				update_option( self::DB_UPDATE_SCHEDULED_OPTION, $greatest_version );
			}
		}


		/**
		 * Run an update callback when triggered by ActionScheduler.
		 *
		 * @param string $callback Callback name.
		 */
		public function run_update_callback( $callback ) {
			include_once YWTM_INC . '/functions.yith-tab-manager-update.php';

			if ( is_callable( $callback ) ) {
				self::run_update_callback_start( $callback );
				$result = (bool) call_user_func( $callback );
				self::run_update_callback_end( $callback, $result );
			}
		}

		/**
		 * Triggered when a callback will run.
		 *
		 * @param string $callback Callback name.
		 */
		protected function run_update_callback_start( $callback ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
			if ( ! defined( 'YITH_TAB_MANAGER_UPDATING' ) ) {
				define( 'YITH_TAB_MANAGER_UPDATING', true );
			}
		}

		/**
		 * Triggered when a callback has ran.
		 *
		 * @param string $callback Callback name.
		 * @param bool   $result Return value from callback. Non-false need to run again.
		 */
		protected function run_update_callback_end( $callback, $result ) {
			if ( $result ) {
				WC()->queue()->add(
					'yith_tab_manager_run_update_callback',
					array(
						'update_callback' => $callback,
					),
					'yith-tab-manager-db-updates'
				);
			}
		}

		/**
		 * Porting the single product tab
		 *
		 * @param array  $products The product ids.
		 * @param int    $tab_id The tab ids.
		 * @param string $layout The tab layout.
		 *
		 * @return void
		 */
		public function run_update_single_product( $products, $tab_id, $layout ) {
			include_once YWTM_INC . '/functions.yith-tab-manager-update.php';

			$original_id = apply_filters( 'wpml_original_element_id', null, $tab_id, 'post_ywtm_tab' );
			$tab_key = !is_null( $original_id ) && $original_id !== false ? $original_id : $tab_id;
			// phpcs:enable
			foreach ( $products as $product_id ) {
				$product   = wc_get_product( $product_id );
				$tab_fixed = $product->get_meta( 'ywtm_tab_fixed' );
				$tab_fixed = empty( $tab_fixed ) ? array() : explode( ',', $tab_fixed );
				if ( ! in_array( $tab_id, $tab_fixed )  ) {
					switch ( $layout ) {
						case 'faq':
							$meta_key = 'custom_list_faqs';
							break;
						case 'download':
							$meta_key = 'custom_list_file';
							break;
						case 'gallery':
							$meta_key = 'custom_gallery';
							break;
						case 'map':
							$meta_key = 'custom_map';
							break;
						case 'video':
							$meta_key = 'custom_video';
							break;
						case 'contact':
							$meta_key = 'custom_form';
							break;
						case 'shortcode':
							$meta_key = 'custom_shortcode';
							break;
						default:
							$meta_key = 'default_editor';
							break;
					}

					$meta_key = $tab_key . '_' . $meta_key;

					$value = maybe_unserialize( $product->get_meta( $meta_key ) );
					if ( 'default' === $layout && empty( $value ) ) {
						$meta_key = $tab_key . '_custom_shortcode';
						$value    = maybe_unserialize( $product->get_meta( $meta_key ) );
					}

					if ( empty( $value ) ) {
						continue;
					}
					$stored_value = $product->get_meta( 'ywtm_product_tabs' );
					$stored_value = empty( $stored_value ) ? array() : $stored_value;
					$new_value    = array(
						'value' => array(),
					);

					switch ( $layout ) {
						case 'faq':
							$new_value['value']['faqs'] = $value;
							break;
						case 'download':
							$new_files = array();
							foreach ( $value as $file ) {

								$new_files[] = array(
									'name' => $file['name'],
									'file' => $file['file'],
									'desc' => $file['desc'],
								);
							}
							$new_value['value']['download'] = $new_files;
							break;
						case 'gallery':
							$image_ids                             = implode( ',', wp_list_pluck( $value['images'], 'id' ) );
							$new_value['value']['gallery_ids']     = $image_ids;
							$new_value['value']['gallery_columns'] = $value['settings']['columns'];
							break;
						case 'map':
							$new_value['value']['map_overlay_address'] = $value['addr'];
							$new_value['value']['map_full_width']      = $value['show_width'];
							$new_value['value']['map_width']           = $value['wid'];
							$new_value['value']['map_height']          = $value['heig'];
							$new_value['value']['map_overlay_zoom']    = $value['zoom'];
							break;
						case 'video':
							$new_video = array();
							$i         = 1;
							foreach ( $value['video'] as $video ) {
								$new_video[] = array(
									'name' => "Video {$i}",
									'id'   => $video['id'],
									'url'  => $video['url'],
									'host' => $video['host'],
								);
								++ $i;
							}
							$new_value['value']['video']      = $new_video;
							$new_value['value']['video_rows'] = $value['settings']['columns'];
							break;
						case 'contact':
							$new_form                   = array(
								'name'    => yith_tab_manager_update_200_get_contact_form_field( 'name', $value ),
								'email'   => yith_tab_manager_update_200_get_contact_form_field( 'email', $value ),
								'webaddr' => yith_tab_manager_update_200_get_contact_form_field( 'webaddr', $value ),
								'subj'    => yith_tab_manager_update_200_get_contact_form_field( 'subj', $value ),
								'message' => yith_tab_manager_update_200_get_contact_form_field( 'message', $value ),
							);
							$new_value['value']['form'] = $new_form;
							break;
						default:
							$new_value['value']['content'] = $value;
							break;
					}


					$stored_value[ $tab_id ] = $new_value;
					$tab_fixed[]             = $tab_id;
					$product->update_meta_data( 'ywtm_tab_fixed', implode( ',', $tab_fixed ) );
					$product->update_meta_data( 'ywtm_product_tabs', $stored_value );

					$product->save();
				}
			}
		}
	}
}
