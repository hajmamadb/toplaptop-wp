<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * This file manage all plugins updates
 *
 * @package YITH\TabManager/Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'yith_tab_manager_update_200_get_contact_form_field' ) ) {

	/**
	 * Get the right option for the form field
	 *
	 * @param string $field_key The form field.
	 * @param array  $value The old value.
	 *
	 * @return array
	 */
	function yith_tab_manager_update_200_get_contact_form_field( $field_key, $value ) {

		$labels  = array(
			'name'    => __( 'Name', 'yith-woocommerce-tab-manager' ),
			'email'   => __( 'Email', 'yith-woocommerce-tab-manager' ),
			'webaddr' => __( 'Website', 'yith-woocommerce-tab-manager' ),
			'subj'    => __( 'Subject', 'yith-woocommerce-tab-manager' ),
			'message' => __( 'Message', 'yith-woocommerce-tab-manager' ),

		);
		$value   = ! is_array( $value ) ? array() : $value;
		$options = array(
			'label'       => $labels[ $field_key ],
			'placeholder' => '',
		);
		switch ( $field_key ) {
			case 'name':
			case 'webaddr':
			case 'subj':
				if ( isset( $value[ $field_key ]['show'] ) && 'on' === $value[ $field_key ]['show'] ) {
					$options['enabled'] = 'yes';
				}
				if ( isset( $value[ $field_key ]['req'] ) && 'on' === $value[ $field_key ]['req'] ) {
					$options['required'] = 'yes';
				}
				break;
			default:
				$options['enabled']  = 'yes';
				$options['required'] = 'yes';
				break;
		}

		return $options;
	}
}
if ( ! function_exists( 'yith_tab_manager_update_200_wc_tabs_priority' ) ) {
	/**
	 * Calculate the right order of WooCommerce tabs
	 *
	 * @return array
	 */
	function yith_tab_manager_update_200_wc_tabs_priority() {

		$option = get_option( 'yith_tab_manager_wc_tabs_priority', false );

		if ( ! $option ) {
			$desc_tab = get_posts(
				array(
					'numberposts' => - 1,
					'post_type'   => 'ywtm_tab',
					'fields'      => 'ids',
					'meta_query'  => array(
						array(
							'key'     => '_ywtm_tab_ported',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => '_ywtm_is_editable',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => '_ywtm_order_tab',
							'compare' => '<=',
							'value'   => '10',
							'type'    => 'NUMERIC',
						),
					),
				)
			);
			$add_tab  = get_posts(
				array(
					'numberposts' => - 1,
					'post_type'   => 'ywtm_tab',
					'fields'      => 'ids',
					'meta_query'  => array(
						array(
							'key'     => '_ywtm_tab_ported',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => '_ywtm_is_editable',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => '_ywtm_order_tab',
							'compare' => 'BETWEEN',
							'value'   => array( 11, 20 ),
							'type'    => 'NUMERIC',
						),
					),
				)
			);
			$rev_tab  = get_posts(
				array(
					'numberposts' => - 1,
					'post_type'   => 'ywtm_tab',
					'fields'      => 'ids',
					'meta_query'  => array(
						array(
							'key'     => '_ywtm_tab_ported',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => '_ywtm_is_editable',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => '_ywtm_order_tab',
							'compare' => 'BETWEEN',
							'value'   => array( 21, 30 ),
							'type'    => 'NUMERIC',
						),
					),
				)
			);

			$desc_tab = count( $desc_tab ) + 1;
			$add_tab  = $desc_tab + count( $add_tab ) + 1;
			$rev_tab  = $add_tab + count( $rev_tab ) + 1;

			$option = array(
				'description'            => $desc_tab,
				'additional_information' => $add_tab,
				'reviews'                => $rev_tab,
			);

			$wc_tabs = get_option( 'yith_tab_manager_wc_added' );
			foreach ( $wc_tabs as $key => $id ) {
				$wc_tab = ywtm_get_tab( $id );
				$wc_tab->set_order( $option[ $key ] );
				$wc_tab->save();
			}
			update_option(
				'yith_tab_manager_wc_tabs_priority',
				$option
			);

		}

		return $option;
	}
}
if ( ! function_exists( 'yith_tab_manager_update_200_porting_plugin_tabs' ) ) {
	/**
	 * Porting the old free tabs , update the priority with new logic
	 *
	 * @return bool
	 */
	function yith_tab_manager_update_200_porting_plugin_tabs() {

		$tabs = get_posts(
			array(
				'post_type'        => 'ywtm_tab',
				'limit'            => 20,
				'fields'           => 'ids',
				'meta_key'         => '_ywtm_order_tab',
				'orderby'          => 'meta_value_num',
				'order'            => 'ASC',
				'suppress_filters' => true,
				'meta_query'       => array(
					array(
						'key'     => '_ywtm_tab_ported',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => '_ywtm_is_editable',
						'compare' => 'NOT EXISTS',
					),
				),
			)
		);

		$wc_priorities = yith_tab_manager_update_200_wc_tabs_priority();

		if ( ! $tabs ) {
			delete_option( 'ywtm_porting_200_counter' );

			return false;
		}

		$counter = get_option( 'ywtm_porting_200_counter', 1 );
		$backup  = get_option( 'ywtm_porting_200_backup', array() );
		foreach ( $tabs as $tab_id ) {
			$backup[ $tab_id ] = get_post_meta( $tab_id );
			$show_tab          = get_post_meta( $tab_id, '_ywtm_show_tab', true );
			$show_tab          = wc_bool_to_string( $show_tab );
			$layout            = get_post_meta( $tab_id, '_ywtm_layout_type', true );

			$wc_key = array_search( $counter, $wc_priorities ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			if ( false !== $wc_key ) {
				++ $counter;
			}
			$post    = get_post( $tab_id );
			$excerpt = $post->post_excerpt;
			update_post_meta( $tab_id, '_ywtm_order_tab', $counter );
			update_post_meta( $tab_id, '_ywtm_show_tab', $show_tab );
			update_post_meta( $tab_id, '_ywtm_tab_title', get_the_title( $tab_id ) );
			update_post_meta( $tab_id, '_ywtm_tab_excerpt', $excerpt );
			update_post_meta( $tab_id, '_ywtm_origin', 'plugin' );

			if ( 'video' === $layout ) {
				yith_tab_manager_porting_video_tab( $tab_id );
			} else {
				update_post_meta( $tab_id, '_ywtm_video', array() );
				if ( 'shortcode' === $layout ) {
					yith_tab_manager_porting_shortcode_to_default( $tab_id );
				} elseif ( 'contact' === $layout ) {
					yith_tab_manager_porting_contact_tab( $tab_id );
				}
			}
			$old_non_in_product = get_post_meta( $tab_id, '_ywtm_enable_custom_content', true );
			$content_in         = 'no' === wc_bool_to_string( $old_non_in_product ) ? 'product' : 'all';
			update_post_meta( $tab_id, '_ywtm_tab_content_in', $content_in );
			if ( 'no' === wc_bool_to_string( $old_non_in_product ) ) { // if the tab is customized in products -> porting.
				yith_tab_manager_porting_product_tab( $tab_id, $layout );
			}

			++ $counter;
			update_post_meta( $tab_id, '_ywtm_tab_ported', 'yes' );
			update_post_meta( $tab_id, '_ywtm_is_editable', 'yes' );
		}
		update_option( 'ywtm_porting_200_backup', $backup );
		update_option( 'ywtm_porting_200_counter', $counter );

		return true;
	}
}

if ( ! function_exists( 'yith_tab_manager_update_200_db_version' ) ) {
	/**
	 * Update the db version
	 *
	 * @return void
	 */
	function yith_tab_manager_update_200_db_version() {
		YITH_Tab_Manager_Install::update_db_version( '2.0.0' );
	}
}

if ( ! function_exists( 'yith_tab_manager_porting_shortcode_to_default' ) ) {

	/**
	 * Porting the old shortcode tab type into default type
	 *
	 * @param int $tab_id The tab id.
	 *
	 * @return void
	 */
	function yith_tab_manager_porting_shortcode_to_default( $tab_id ) {
		update_post_meta( $tab_id, '_ywtm_layout_type', 'default' );
		$content = get_post_meta( $tab_id, '_ywtm_shortcode_tab', true );

		update_post_meta( $tab_id, '_ywtm_text_tab', $content );
	}
}

if ( ! function_exists( 'yith_tab_manager_porting_video_tab' ) ) {

	/**
	 * Porting the video type
	 *
	 * @param int $tab_id The tab id.
	 *
	 * @return void
	 */
	function yith_tab_manager_porting_video_tab( $tab_id ) {
		$content = get_post_meta( $tab_id, '_ywtm_video', true );
		$videos  = isset( $content['video_info'] ) && is_array( $content['video_info'] ) ? $content['video_info'] : array();
		$columns = isset( $content['columns'] ) ? $content['columns'] : 2;

		$new_video = array();
		foreach ( $videos as $video ) {
			$new_video[] = array(
				'name' => $video['video_name'],
				'id'   => $video['id'],
				'url'  => $video['url'],
				'host' => $video['host'],
			);

		}

		update_post_meta( $tab_id, '_ywtm_video', $new_video );
		update_post_meta( $tab_id, '_ywtm_video_rows', $columns );
	}
}

if ( ! function_exists( 'yith_tab_manager_porting_contact_tab' ) ) {

	/**
	 * Porting the contact type
	 *
	 * @param int $tab_id The tab id.
	 *
	 * @return void
	 */
	function yith_tab_manager_porting_contact_tab( $tab_id ) {
		$form = get_post_meta( $tab_id, '_ywtm_form_tab', true );

		$new_form = array(
			'name'    => yith_tab_manager_update_200_get_contact_form_field( 'name', $form ),
			'email'   => yith_tab_manager_update_200_get_contact_form_field( 'email', $form ),
			'webaddr' => yith_tab_manager_update_200_get_contact_form_field( 'webaddr', $form ),
			'subj'    => yith_tab_manager_update_200_get_contact_form_field( 'subj', $form ),
			'message' => yith_tab_manager_update_200_get_contact_form_field( 'message', $form ),
		);

		update_post_meta( $tab_id, '_ywtm_form_tab', $new_form );
	}
}

if ( ! function_exists( 'yith_tab_manager_porting_product_tab' ) ) {

	/**
	 * Create a new tab by products
	 *
	 * @param int    $tab_id The tab id.
	 * @param string $layout The layout.
	 *
	 * @return void
	 */
	function yith_tab_manager_porting_product_tab( $tab_id, $layout ) {

		$post_args = array(
			'post_type'        => 'product',
			'fields'           => 'ids',
			'post_status'      => 'publish',
			'suppress_filters' => true,
			'posts_per_page'   => 500,
			'offset'           => 0
		);
		$products  = get_posts( $post_args );

		$totals = count( $products );
		$time   = 0;
		$i      = 0;

		while ( $totals > 0 ) {
			$args          = array(
				'products' => $products,
				'tab_id'   => $tab_id,
				'layout'   => $layout,
			);
			$has_scheduled = WC()->queue()->get_next( 'yith_tab_manager_porting_product', $args, 'ywtm-porting-product-process-' . $i );
			if ( $has_scheduled ) {

				WC()->queue()->cancel( 'yith_tab_manager_porting_product', $args, 'ywtm-porting-product-process-' . $i );
			}
			WC()->queue()->schedule_single(
				time() + $time,
				'yith_tab_manager_porting_product',
				$args,
				'ywtm-porting-product-process-' . $i
			);
			$i ++;
			$time                += MINUTE_IN_SECONDS;
			$post_args['offset'] += 500;
			$products            = get_posts( $post_args );
			$totals              = count( $products );
		}
	}
}

if ( ! function_exists( 'yith_tab_manager_update_200_porting_options' ) ) {

	/**
	 * Porting the deprecated option to hide WC tabs in the tab object
	 *
	 * @return void
	 */
	function yith_tab_manager_update_200_porting_options() {
		$hide_desc     = get_option( 'ywtm_hide_wc_desc_tab_in_mobile', 'no' );
		$hide_add      = get_option( 'ywtm_hide_wc_reviews_tab', 'no' );
		$hide_rev      = get_option( 'ywtm_hide_wc_addinfo_tab', 'no' );
		$wc_tabs_added = get_option( 'yith_tab_manager_wc_added' );
		if ( 'yes' === $hide_desc ) {
			update_post_meta( $wc_tabs_added['description'], '_ywtm_show_tab', 'no' );
		}

		if ( 'yes' === $hide_add ) {
			update_post_meta( $wc_tabs_added['additional_information'], '_ywtm_show_tab', 'no' );
		}

		if ( 'yes' === $hide_rev ) {
			update_post_meta( $wc_tabs_added['reviews'], '_ywtm_show_tab', 'no' );
		}
	}
}

if ( ! function_exists( 'yith_tab_manager_update_201_fix_porting_plugin_tab' ) ) {
	function yith_tab_manager_update_201_fix_porting_plugin_tab() {

		$fixing = yith_tab_manager_update_200_porting_plugin_tabs();

		if ( ! $fixing ) {
			$tabs = get_posts(
				array(
					'post_type'   => 'ywtm_tab',
					'numberposts' => - 1,
					'fields'      => 'ids',
					'meta_key'    => '_ywtm_order_tab',
					'orderby'     => 'meta_value_num',
					'order'       => 'ASC',
					'meta_query'  => array(
						array(
							'key'     => '_ywtm_tab_content_in',
							'value'   => 'product',
							'compare' => '=',
						),
					),
				)
			);
			foreach ( $tabs as $tab_id ) {
				$layout = get_post_meta( $tab_id, '_ywtm_layout_type', true );
				yith_tab_manager_porting_product_tab( $tab_id, $layout );
			}
		}

		return $fixing;
	}
}

if ( ! function_exists( 'yith_tab_manager_update_201_db_version' ) ) {
	/**
	 * Update the db version
	 *
	 * @return void
	 */
	function yith_tab_manager_update_201_db_version() {
		YITH_Tab_Manager_Install::update_db_version( '2.0.1' );
	}
}

if ( ! function_exists( 'yith_tab_manager_update_202_fix_porting_plugin_tab' ) ) {
	/**
	 * Add the fix for WPML
	 *
	 * @return boolean
	 */
	function yith_tab_manager_update_202_fix_porting_plugin_tab() {
		global $sitepress;

		if ( ! is_null( $sitepress ) ) {
			return yith_tab_manager_update_200_porting_plugin_tabs();
		}

		return false;
	}
}

if ( ! function_exists( 'yith_tab_manager_update_202_db_version' ) ) {
	/**
	 * Update the db version
	 *
	 * @return void
	 */
	function yith_tab_manager_update_202_db_version() {
		YITH_Tab_Manager_Install::update_db_version( '2.0.2' );
	}
}