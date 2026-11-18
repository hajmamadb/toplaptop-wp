<?php
/**
 * This class manage the Tab object
 *
 * @package YITH/TabManager/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class manage the Tab object
 */
class YITH_Tab_Manager_Obj extends WC_Data {
	/**
	 * The default data of the price rule
	 *
	 * @var array
	 */
	protected $data = array(
		'name'                => '',
		'excerpt'             => '',
		'additional_content'  => '',
		'type'                => 'global',
		'active'              => 'yes',
		'order'               => 20,
		'categories'          => array(),
		'products'            => array(),
		'enable_exclude'      => 'no',
		'exclude_in'          => 'category',
		'categories_excluded' => array(),
		'products_excluded'   => array(),
		'icon'                => array(
			'select' => 'none',
			'icon'   => '',
			'custom' => '',
		),
		'layout'              => 'default',
		'faqs'                => array(),
		'gallery_columns'     => 1,
		'gallery_ids'         => '',
		'video'               => array(),
		'video_rows'          => 2,
		'download'            => array(),
		'map_full_width'      => 'no',
		'map_width'           => 100,
		'map_height'          => 100,
		'map_overlay_address' => '',
		'map_overlay_zoom'    => 15,
		'content'             => '',
		'form'                => array(),
		'is_editable'         => 'yes',
		'origin'              => 'plugin',
		'content_in'          => 'all',
	);

	/**
	 * Create a price rule object
	 *
	 * @param int|string|WP_Post|YITH_Tab_Manager_Obj $tab The tab to retrieve.
	 *
	 * @throws Exception Error from WC_Data_Store::load.
	 */
	public function __construct( $tab = 0 ) {
		parent::__construct( $tab );

		$this->object_type = 'product-tab';
		if ( is_numeric( $tab ) && absint( $tab ) > 0 ) {
			$this->set_id( absint( $tab ) );
		} elseif ( $tab instanceof self ) {
			$this->set_id( absint( $tab->get_id() ) );
		} elseif ( ! empty( $tab->ID ) ) {
			$this->set_id( absint( $tab->ID ) );
		} else {
			$this->set_object_read( true );
		}

		$this->data_store = WC_Data_Store::load( 'ywtm-data-store' );
		if ( $this->get_id() > 0 ) {
			$this->data_store->read( $this );
		}
	}

	/**
	 * Customize the hook prefix
	 *
	 * @return string
	 */
	public function get_hook_prefix() {
		return 'ywtm_' . $this->object_type;
	}

	// SETTER METHOD.

	/**
	 * Set the name of the tab
	 *
	 * @param string $name The name.
	 *
	 * @return void
	 */
	public function set_name( $name ) {
		$this->set_prop( 'name', $name );
	}

	/**
	 * Set the additional content of the tab
	 *
	 * @param string $additional_content The additional content.
	 *
	 * @return void
	 */
	public function set_additional_content( $additional_content ) {
		$this->set_prop( 'additional_content', $additional_content );
	}

	/**
	 * Set the excerpt of the tab
	 *
	 * @param string $excerpt The excerpt.
	 *
	 * @return void
	 */
	public function set_excerpt( $excerpt ) {
		$this->set_prop( 'excerpt', $excerpt );
	}

	/**
	 * Set the tab type ( global, for product or categories )
	 *
	 * @param string $type The type.
	 *
	 * @return void
	 */
	public function set_type( $type ) {
		$this->set_prop( 'type', $type );
	}

	/**
	 * Set if the tab should be views on product or not
	 *
	 * @param string $active Yes or not.
	 *
	 * @return void
	 */
	public function set_active( $active ) {
		$this->set_prop( 'active', $active );
	}

	/**
	 * Set the position of the tab
	 *
	 * @param int $order The order.
	 *
	 * @return void
	 */
	public function set_order( $order ) {
		$this->set_prop( 'order', $order );
	}

	/**
	 * Set the categories of the tab
	 *
	 * @param array $categories The categories id.
	 *
	 * @return void
	 */
	public function set_categories( $categories ) {
		$this->set_prop( 'categories', $categories );
	}

	/**
	 * Set the products of the tab
	 *
	 * @param array $products The product id.
	 *
	 * @return void
	 */
	public function set_products( $products ) {
		$this->set_prop( 'products', $products );
	}


	/**
	 * Set the exclude field in the tab
	 *
	 * @param string $enable_exclude yes or no.
	 *
	 * @return void
	 */
	public function set_enable_exclude( $enable_exclude ) {
		$this->set_prop( 'enable_exclude', $enable_exclude );
	}

	/**
	 * Set the exclusion type field in the tab
	 *
	 * @param string $exclude_in category or product.
	 *
	 * @return void
	 */
	public function set_exclude_in( $exclude_in ) {
		$this->set_prop( 'exclude_in', $exclude_in );
	}

	/**
	 * Set the categories id excluded
	 *
	 * @param array $categories_excluded The category id list.
	 *
	 * @return void
	 */
	public function set_categories_excluded( $categories_excluded ) {
		$this->set_prop( 'categories_excluded', $categories_excluded );
	}

	/**
	 * Set the product id excluded
	 *
	 * @param array $products_excluded The product id list.
	 *
	 * @return void
	 */
	public function set_products_excluded( $products_excluded ) {
		$this->set_prop( 'products_excluded', $products_excluded );
	}

	/**
	 * Set the icon of the tab
	 *
	 * @param array $icon The icon.
	 *
	 * @return void
	 */
	public function set_icon( $icon ) {
		$this->set_prop( 'icon', $icon );
	}

	/**
	 * Set the tab layout ( content, faq, downloads etc )
	 *
	 * @param string $layout The layout.
	 *
	 * @return void
	 */
	public function set_layout( $layout ) {
		$this->set_prop( 'layout', $layout );
	}

	/**
	 * Set the faqs content for the tab
	 *
	 * @param array $faqs The faqs.
	 *
	 * @return void
	 */
	public function set_faqs( $faqs ) {
		$this->set_prop( 'faqs', $faqs );
	}

	/**
	 * Set how many columns use for the gallery
	 *
	 * @param int $gallery_columns The columns.
	 *
	 * @return void
	 */
	public function set_gallery_columns( $gallery_columns ) {
		$this->set_prop( 'gallery_columns', $gallery_columns );
	}

	/**
	 * Set the image to show in the gallery content
	 *
	 * @param string $gallery_ids The image ids.
	 *
	 * @return void
	 */
	public function set_gallery_ids( $gallery_ids ) {
		$this->set_prop( 'gallery_ids', $gallery_ids );
	}

	/**
	 * Set the video content for the tab
	 *
	 * @param array $video The video.
	 *
	 * @return void
	 */
	public function set_video( $video ) {
		$this->set_prop( 'video', $video );
	}

	/**
	 * Set how many videos show for each row
	 *
	 * @param int $rows The rows.
	 *
	 * @return void
	 */
	public function set_video_rows( $rows ) {

		$this->set_prop( 'video_rows', $rows );
	}

	/**
	 * Set the download content for the tab
	 *
	 * @param array $download The downloads.
	 *
	 * @return void
	 */
	public function set_download( $download ) {
		$this->set_prop( 'download', $download );
	}

	/**
	 * Set if show the map in full width
	 *
	 * @param string $map_full_width Yes or no.
	 *
	 * @return void
	 */
	public function set_map_full_width( $map_full_width ) {
		$this->set_prop( 'map_full_width', $map_full_width );
	}

	/**
	 * Set the map width
	 *
	 * @param int $map_width The width.
	 *
	 * @return void
	 */
	public function set_map_width( $map_width ) {
		$this->set_prop( 'map_width', $map_width );
	}

	/**
	 * Set the map height
	 *
	 * @param int $map_height The height.
	 *
	 * @return void
	 */
	public function set_map_height( $map_height ) {
		$this->set_prop( 'map_height', $map_height );
	}

	/**
	 * Set the address to show in the map
	 *
	 * @param string $map_overlay_address The address.
	 *
	 * @return void
	 */
	public function set_map_overlay_address( $map_overlay_address ) {
		$this->set_prop( 'map_overlay_address', $map_overlay_address );
	}

	/**
	 * Set the zoom level for the map
	 *
	 * @param int $map_overlay_zoom The zoom.
	 *
	 * @return void
	 */
	public function set_map_overlay_zoom( $map_overlay_zoom ) {
		$this->set_prop( 'map_overlay_zoom', $map_overlay_zoom );
	}

	/**
	 * Set the default content for the tab
	 *
	 * @param string $content The content.
	 *
	 * @return void
	 */
	public function set_content( $content ) {
		$this->set_prop( 'content', $content );
	}


	/**
	 * Set the form content for the tab
	 *
	 * @param array $form The form.
	 *
	 * @return void
	 */
	public function set_form( $form ) {
		$this->set_prop( 'form', $form );
	}


	/**
	 * Set if the tab is editable or not
	 *
	 * @param string $is_editable Yes or no.
	 *
	 * @return void
	 */
	public function set_is_editable( $is_editable ) {
		$this->set_prop( 'is_editable', $is_editable );
	}

	/**
	 * Set the tab origin
	 *
	 * @param string $origin plugin|woocommerce.
	 *
	 * @return void
	 */
	public function set_origin( $origin ) {
		$this->set_prop( 'origin', $origin );
	}

	/**
	 * Set the where the content is set for the tab
	 *
	 * @param string $content_in The value.
	 *
	 * @return void
	 */
	public function set_content_in( $content_in ) {
		$this->set_prop( 'content_in', $content_in );
	}
	// GETTER METHOD.

	/**
	 * Get the name of the tab
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_name( $context = 'view' ) {
		return $this->get_prop( 'name', $context );
	}

	/**
	 * Get the additional content of the tab
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_additional_content( $context = 'view' ) {
		return $this->get_prop( 'additional_content', $context );
	}

	/**
	 * Get the excerpt of the tab
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_excerpt( $context = 'view' ) {
		return $this->get_prop( 'excerpt', $context );
	}

	/**
	 * Set the tab type ( global, for product or categories )
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_type( $context = 'view' ) {
		return $this->get_prop( 'type', $context );
	}

	/**
	 * Get if the tab should be views on product or not
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_active( $context = 'view' ) {
		return $this->get_prop( 'active', $context );
	}

	/**
	 * Get the position of the tab
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int
	 */
	public function get_order( $context = 'view' ) {
		return $this->get_prop( 'order', $context );
	}

	/**
	 * Get the categories of the tab
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 */
	public function get_categories( $context = 'view' ) {
		return $this->get_prop( 'categories', $context );
	}

	/**
	 * Get the products of the tab
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 */
	public function get_products( $context = 'view' ) {
		return $this->get_prop( 'products', $context );
	}


	/**
	 * Get the exclude field in the tab
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_enable_exclude( $context = 'view' ) {
		return $this->get_prop( 'enable_exclude', $context );
	}

	/**
	 * Get the exclusion type field in the tab
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_exclude_in( $context = 'view' ) {
		return $this->get_prop( 'exclude_in', $context );
	}

	/**
	 * Get the categories id excluded
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 */
	public function get_categories_excluded( $context = 'view' ) {
		return $this->get_prop( 'categories_excluded', $context );
	}

	/**
	 * Get the product id excluded
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 */
	public function get_products_excluded( $context = 'view' ) {
		return $this->get_prop( 'products_excluded', $context );
	}

	/**
	 * Get the icon of the tab
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 */
	public function get_icon( $context = 'view' ) {
		return $this->get_prop( 'icon', $context );
	}

	/**
	 * Get the tab layout ( content, faq, downloads etc )
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_layout( $context = 'view' ) {
		return $this->get_prop( 'layout', $context );
	}

	/**
	 * Get the faqs content for the tab
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 */
	public function get_faqs( $context = 'view' ) {
		return $this->get_prop( 'faqs', $context );
	}

	/**
	 * Get how many columns use for the gallery
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int
	 */
	public function get_gallery_columns( $context = 'view' ) {
		return $this->get_prop( 'gallery_columns', $context );
	}

	/**
	 * Get the image to show in the gallery content
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 */
	public function get_gallery_ids( $context = 'view' ) {
		if ( 'edit' === $context ) {
			return $this->get_prop( 'gallery_ids', $context );
		}

		return explode( ',', $this->get_prop( 'gallery_ids', $context ) );
	}

	/**
	 * Get the video content for the tab
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 */
	public function get_video( $context = 'view' ) {
		return $this->get_prop( 'video', $context );
	}

	/**
	 * Get the video rows
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int
	 */
	public function get_video_rows( $context = 'view' ) {
		return $this->get_prop( 'video_rows', $context );
	}

	/**
	 * Get the download content for the tab
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 */
	public function get_download( $context = 'view' ) {
		return $this->get_prop( 'download', $context );
	}

	/**
	 * Get if show the map in full width
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_map_full_width( $context = 'view' ) {
		return $this->get_prop( 'map_full_width', $context );
	}

	/**
	 * Get the map width
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int
	 */
	public function get_map_width( $context = 'view' ) {
		return $this->get_prop( 'map_width', $context );
	}

	/**
	 * Get the map height
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int
	 */
	public function get_map_height( $context = 'view' ) {
		return $this->get_prop( 'map_height', $context );
	}

	/**
	 * Set the address to show in the map
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_map_overlay_address( $context = 'view' ) {
		return $this->get_prop( 'map_overlay_address', $context );
	}

	/**
	 * Get the zoom level for the map
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int
	 */
	public function get_map_overlay_zoom( $context = 'view' ) {
		return $this->get_prop( 'map_overlay_zoom', $context );
	}

	/**
	 * Get the default content for the tab
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_content( $context = 'view' ) {
		return $this->get_prop( 'content', $context );
	}


	/**
	 * Get the form content for the tab
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 */
	public function get_form( $context = 'view' ) {
		return $this->get_prop( 'form', $context );
	}

	/**
	 * Set if the tab is editable or not
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_is_editable( $context = 'view' ) {
		return $this->get_prop( 'is_editable', $context );
	}


	/**
	 * Get the tab origin
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_origin( $context = 'view' ) {
		return $this->get_prop( 'origin', $context );
	}

	/**
	 * Get where the content is set for the tab
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_content_in( $context = 'view' ) {
		return $this->get_prop( 'content_in', $context );
	}
	/**
	 * The tab can be shown in the product
	 *
	 * @param WC_Product $product The product.
	 *
	 * @return boolean
	 */
	public function can_show( $product ) {
		if ( 'product' === $this->get_type() ) {
			return in_array( intval( $product->get_id() ), array_map( 'intval', $this->get_products() ), true );
		} elseif ( 'category' === $this->get_type() ) {
			$product_categories  = wc_get_product_term_ids( $product->get_id(), 'product_cat' );
			$selected_categories = $this->get_categories();

			$can_show = count( array_intersect( $selected_categories, $product_categories ) ) > 0;

			return $can_show && ! $this->is_excluded_in_product( $product );
		} else {
			return ! $this->is_excluded_in_product( $product );
		}
	}

	/**
	 * Check if the tab is excluded from the product
	 *
	 * @param WC_Product $product The product.
	 *
	 * @return bool
	 */
	public function is_excluded_in_product( $product ) {

		$ex_active = yith_plugin_fw_is_true( $this->get_enable_exclude() );

		if ( $ex_active ) {
			$type = $this->get_exclude_in();

			if ( 'product' === $type ) {
				return in_array( intval( $product->get_id() ), array_map( 'intval', $this->get_products_excluded() ), true );
			} else {
				$product_categories  = wc_get_product_term_ids( $product->get_id(), 'product_cat' );
				$selected_categories = $this->get_categories_excluded();

				return count( array_intersect( $selected_categories, $product_categories ) ) > 0;
			}
		}

		return false;
	}

	/**
	 * Get the tab content
	 *
	 * @return string
	 */
	public function show_tab_content() {

		$content_type = $this->get_layout();

		switch ( $content_type ) {
			case 'video':
				$template_name = 'video-gallery';
				$content       = array(
					'video'         => $this->get_video(),
					'columns'       => $this->get_video_rows(),
					'extra_content' => $this->get_additional_content(),
				);
				break;
			case 'gallery':
				$template_name = 'image-gallery';
				$content       = array(
					'tab_id'        => $this->get_id(),
					'image_ids'     => $this->get_gallery_ids(),
					'columns'       => $this->get_gallery_columns(),
					'extra_content' => $this->get_additional_content(),
				);
				break;
			case 'faq':
				$template_name = 'faq';
				$content       = array(
					'faqs'          => $this->get_faqs(),
					'extra_content' => $this->get_additional_content(),
				);
				break;
			case 'download':
				$template_name = 'download';
				$content       = array(
					'files'         => $this->get_download(),
					'extra_content' => $this->get_additional_content(),
				);
				break;
			case 'map':
				$template_name = 'map';
				$content       = array(
					'address'       => $this->get_map_overlay_address(),
					'width'         => $this->get_map_width(),
					'height'        => $this->get_map_height(),
					'zoom'          => $this->get_map_overlay_zoom(),
					'full_width'    => yith_plugin_fw_is_true( $this->get_map_full_width() ),
					'extra_content' => $this->get_additional_content(),
				);
				break;
			case 'contact':
				$template_name = 'form';
				$content       = array(
					'form_element'  => $this->get_form(),
					'extra_content' => $this->get_additional_content(),
				);
				break;
			default:
				$template_name = 'default';
				$content       = array(
					'default' => $this->get_content(),
				);
				break;
		}

		$args = $content;

		return wc_get_template_html( "woocommerce/single-product/tabs/{$template_name}.php", $args, '', YWTM_TEMPLATE_PATH );
	}

	/**
	 * Return the html icon
	 *
	 * @return string
	 */
	public function get_html_icon() {
		$icon = $this->get_icon();
		switch ( $icon['select'] ) {
			case 'icon':
				$icon      = ywtm_map_old_icon_with_new( $icon['icon'] );
				$icon_data = explode( ':', $icon );
				$class     = '';
				if ( is_array( $icon_data ) && 2 === count( $icon_data ) ) {
					if ( 'FontAwesome' === $icon_data[0] ) {
						$class = 'fas fa-' . $icon_data[1];
					} elseif ( 'Dashicons' === $icon_data[0] ) {
						$class = 'dashicons dashicons-' . $icon_data[1];
					} else {

						$class = 'retinaiconfont- ' . $icon_data[1];
					}
				}
				$tab_icon = sprintf( '<span class="ywtm_icon %s" style="padding-right:10px;"></span>', $class );

				break;
			case 'upload':
				/**
				 * APPLY_FILTERS: ywtm_custom_icon_alt_tag
				 *
				 * The filter allow to edit the icon alt tag.
				 *
				 * @param string               $icon_alt The icon alt.
				 * @param YITH_Tab_Manager_Obj $tab The tab.
				 *
				 * @return string
				 */
				$alt_tag  = apply_filters( 'ywtm_custom_icon_alt_tag', 'alt="' . $this->get_name() . '"', $this );
				$tab_icon = '<span class="ywtm_custom_icon" style="padding-right:10px;" ><img ' . $alt_tag . ' src="' . $icon['custom'] . '" style="max-width :27px;max-height: 25px;display: inline-block"/></span>';
				break;
			default:
				$tab_icon = '';
				break;
		}

		return $tab_icon;
	}

	/**
	 * Return the name with the icon
	 *
	 * @return string
	 */
	public function get_formatted_name() {
		$name = $this->get_name();
		$icon = $this->get_html_icon();

		/**
		 * APPLY_FILTERS: ywtm_get_tab_title
		 *
		 * The filter allow to show or not the tab in the product.
		 *
		 * @param string               $title The tab title with the icon.
		 * @param YITH_Tab_Manager_Obj $tab The tab.
		 *
		 * @return string
		 */
		return apply_filters( 'ywtm_get_tab_title', $icon . $name, $this );
	}

	/**
	 * Check if the tab is empty
	 *
	 * @return bool
	 */
	public function is_empty() {

		$content_type = $this->get_layout();

		switch ( $content_type ) {
			case 'video':
				$is_empty = count( $this->get_video() ) < 1;
				break;
			case 'gallery':
				$is_empty = count( $this->get_gallery_ids() ) < 1;
				break;
			case 'faq':
				$is_empty = count( $this->get_faqs() ) < 1;
				break;
			case 'download':
				$is_empty = count( $this->get_download() ) < 1;
				break;
			case 'map':
				$is_empty = empty ( $this->get_map_overlay_address() ) ;
				break;
			case 'contact':
				$is_empty = false;
				break;
			default:
				$is_empty = empty ( $this->get_content() );
				break;
		}

		return $is_empty;
	}

}
