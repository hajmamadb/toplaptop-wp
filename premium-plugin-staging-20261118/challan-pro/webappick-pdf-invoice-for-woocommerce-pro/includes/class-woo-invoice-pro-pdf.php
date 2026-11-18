<?php
/**
 * Created by PhpStorm.
 * User: wahid
 * Date: 4/10/20
 * Time: 9:55 PM
 */

class Woo_Invoice_Pro_PDF {

	/**
	 * Template Content.
	 *
	 * @var string $html Template Content.
	 */
	private $html;
	/**
	 * Paper Size
	 *
	 * @var null
	 */
	private $paper_size;
	/**
	 * Template Type
	 *
	 * @var string $template
	 */
	private $template;
	/**
	 * File name.
	 *
	 * @var string $file_name
	 */
	private $file_name;
	/**
	 * Order Id.
	 *
	 * @var $order_id
	 */
	private $order_id;
	/**
	 * MPDF Config
	 *
	 * @var array $config
	 */
	private $config = array();

	/**
	 * Woo_Invoice_Pro_PDF constructor.
	 *
	 * @param string    $html       Template Content.
	 * @param string    $file_name  File Name.
	 * @param int|array $order_id   Order Ids.
	 * @param string    $template   Template type.
	 * @param string    $paper_size Paper Size.
	 */
	public function __construct( $html, $file_name, $order_id, $template, $paper_size = null ) {
		$this->html       = $html;
		$this->template   = $template;
		$this->file_name  = $file_name;
		//$this->paper_size = $this->paper_size( $paper_size );
        // Apply correct paper size dynamically
        $this->paper_size = ($paper_size) ? $paper_size : $this->paper_size($template);
		$this->order_id   = $order_id;
		$this->mpdf_config();
	}

	/**
	 * MPDF Config
	 */
	public function mpdf_config() {
		// Increase mpdf generate execution time.
		set_time_limit(500);
		// Increase mpdf HTML code size.
		ini_set("pcre.backtrack_limit", "9000000");

		// Temporary Directory.
		$upload = wp_upload_dir();
		$basedir = $upload['basedir'];

		// Extra Fonts
		$extra_fonts = [
			'sun-exta' => 'Sun-ExtA.ttf',
			'sun-extb' => 'Sun-ExtB.ttf',
			'unbatang' => 'UnBatang_0613.ttf',
		];

		$default_config = (new Mpdf\Config\ConfigVariables())->getDefaults();
		$font_dirs = $default_config['fontDir'];

		// Add custom font dir for extra fonts.
		$extra_font_dir = $upload['basedir'] . '/WOO-INVOICE/WOO-INVOICE-FONTS';
		if (file_exists($extra_font_dir)) {
			$font_dirs = array_merge($font_dirs, [$extra_font_dir]);
		}

		// Persian build: reuse the DanaPro font already bundled with this plugin.
		// This avoids depending on the remote font downloader for Persian invoices.
		$persian_font_dir = CHALLAN_PRO_ADMIN_PATH . 'css/fonts';
		if ( file_exists( $persian_font_dir . '/DanaPro.ttf' ) ) {
			$font_dirs[] = $persian_font_dir;
		}
		$this->config['fontDir'] = array_values( array_unique( $font_dirs ) );

		$default_font_config = (new Mpdf\Config\FontVariables())->getDefaults();

		// Load Custom Fonts.
		$custom_fonts = get_option('wpifw_custom_font_list') ?: [];
		$font_data = array_merge($default_font_config['fontdata'], $custom_fonts);

		if ( file_exists( CHALLAN_PRO_ADMIN_PATH . 'css/fonts/DanaPro.ttf' ) ) {
			$font_data['danapro'] = array(
				'R'          => 'DanaPro.ttf',
				'B'          => 'DanaPro.ttf',
				'I'          => 'DanaPro.ttf',
				'BI'         => 'DanaPro.ttf',
				'useOTL'     => 0xFF,
				'useKashida' => 75,
			);
		}

		foreach ($extra_fonts as $key => $value) {
			$font_path = $extra_font_dir . '/' . $value;
			if (file_exists($font_path)) {
				switch ($key) {
					case 'sun-exta':
						$font_data['sun-exta'] = [
							'R' => 'Sun-ExtA.ttf',
							'sip-ext' => 'sun-extb', /* SIP=Plane2 Unicode (extension B) */
						];
						break;
					case 'sun-extb':
						$font_data['sun-extb'] = ['R' => 'Sun-ExtB.ttf'];
						break;
					case 'unbatang':
						$font_data['unbatang'] = ['R' => 'UnBatang_0613.ttf'];
						break;
				}
			}
		}

		$this->config['fontdata'] = apply_filters('woo_invoice_pdf_font_data', $font_data, $this->template);

		$this->config['allowCJKoverflow'] = true;
		$this->config['allow_charset_conversion'] = true;
		$this->config['autoLangToFont'] = true;
		$this->config['mode'] = 'utf-8';
        $this->config['format'] = $this->paper_size;
		$this->config['default_font_size'] = 10;

		$challan_default_fonts = '';
		global $locale;
		$current_locale = function_exists( 'determine_locale' ) ? determine_locale() : $locale;
		$current_locale = strtolower( str_replace( '-', '_', (string) $current_locale ) );

		if ( 'bn_bd' === $current_locale ) {
			$this->config['default_font'] = 'freeserif';
			$challan_default_fonts = $this->config['default_font'];
		} elseif ( 0 === strpos( $current_locale, 'fa' ) && isset( $font_data['danapro'] ) ) {
			$this->config['default_font'] = 'danapro';
			$this->config['autoLangToFont'] = false;
			$challan_default_fonts = 'danapro';
		} else {
			$this->config['default_font'] = get_option('wpifw_pdf_font_family') ?: '';
			$challan_default_fonts = $this->config['default_font'];
		}

		$this->config['default_font'] = apply_filters('woo_invoice_default_font_family_name', $challan_default_fonts);

		$default_page_no_style = ('0' != get_option('wpifw_page_number_style')) ? get_option('wpifw_page_number_style') : '1';

		$template_type = get_option('wpifw_add_page_number');
		if (1 == get_option('wpifw_enable_page_number') && count($template_type) > 0) {
			if (in_array('invoice', $template_type) && 'invoice' === $this->template) {
				$this->config['defaultPageNumStyle'] = $default_page_no_style;
			} elseif (in_array('packing_slip', $template_type) && 'packing_slip' === $this->template) {
				$this->config['defaultPageNumStyle'] = $default_page_no_style;
			}
		}

		$this->config['fontdata']['xbriyaz']['R'] = 'XB-Riyaz.ttf';
		$this->config['fontdata']['xbriyaz']['B'] = 'XB-RiyazBd.ttf';
		$this->config['fontdata']['xbriyaz']['I'] = 'XB-RiyazIt.ttf';
		$this->config['fontdata']['xbriyaz']['BI'] = 'XB-RiyazBdIt.ttf';
		$this->config['fontdata']['xb riyaz.ttf']['R'] = 'XB-Riyaz.ttf';

		return apply_filters('woo_invoice_mpdf_settings', $this->config);
	}

	/**
	 * Generate PDF
	 *
	 * @return string
	 */
	public function generatePDF() {

		try {
		$mpdf               = new \Mpdf\Mpdf( $this->config );
        $mpdf->autoScriptToLang = true; //PHPCS:ignore
        $mpdf->baseScript       = 1;//PHPCS:ignore
        $mpdf->autoVietnamese   = true;//PHPCS:ignore
        $mpdf->autoArabic       = true;//PHPCS:ignore

			// $mpdf->fonttrans['freeserif'] = true;
			$mpdf->debug = get_option( 'wpifw_pdf_invoice_debug_mode' ) == '1' ? true : false;
			// Add Background Image.
			$water_mark = $this->get_watermark();
			if ( $water_mark ) {
				$mpdf->SetWatermarkImage(
					$water_mark['background'],
					$water_mark['opacity'],
					'P',
					'P'
				);
                $mpdf->showWatermarkImage = true;//PHPCS:ignore
			}

			$template = ucwords( str_replace( '_', ' ', $this->template ) );
			$order_id = '-' . $this->order_id;
			if ( strpos( $order_id, ',' ) !== false ) {
				$order_id = '';
				$template = $template . 's';
			}
			$mpdf->allow_charset_conversion = true;
			$mpdf->charset_in               = 'UTF-8';


			$mpdf->WriteHTML( $this->html);
            $invoice_no = ( ! empty(woo_invoice_pro_get_meta($this->order_id, 'wpifw_invoice_no_'.$this->order_id, true))) ? woo_invoice_pro_get_meta($this->order_id, 'wpifw_invoice_no_'.$this->order_id, true) : woo_invoice_pro_get_invoice_number($this->order_id);
            $credit_note_no = ( ! empty(woo_invoice_pro_get_meta($this->order_id, 'wpifw_credit_note_no_'.$this->order_id, true)))
                ? woo_invoice_pro_get_meta($this->order_id, 'wpifw_credit_note_no_'.$this->order_id, true)
                : woo_invoice_pro_get_credit_note_number($this->order_id);
            $order = new WC_Order( $order_id );
            $items = $order->get_items();
            if ( 'save' == $this->template ) { // Save Invoice before email.
                $order = wc_get_order($this->order_id);
                if ( $order instanceof WC_Order && 'refunded' === $order->get_status() ) {
                    $filename = 'Credit-Note-Of-Invoice-' . $credit_note_no;
	                $filename = apply_filters( 'woo_invoice_file_name', $filename, $this->template, $order_id );
                }else {
                    $filename = 'Invoice-' . $invoice_no;
	                $filename = apply_filters( 'woo_invoice_file_name', $filename, $this->template, $order_id );
                }


                $mpdf->Output( CHALLAN_PRO_INVOICE_DIR . $filename . '.pdf', 'F' );
			} else {
                if ( 'save_packing_slip' == $this->template ) { // Save Invoice before email.
                    $filename = 'Packing-slip-' . $invoice_no;
                    $filename = apply_filters( 'woo_invoice_file_name', $filename, $this->template, $order_id );
                    return $mpdf->Output( CHALLAN_PRO_INVOICE_DIR . $filename . '.pdf', 'F' );
                }
				$filename = $template .'-'. $invoice_no;
				$filename = apply_filters( 'woo_invoice_file_name', $filename, $this->template, $order_id );

				if ( get_option( 'wpifw_pdf_invoice_button_behaviour' ) == 'download' ) {
					$mpdf->Output( $filename . '.pdf', 'D' );
				} else {
					$mpdf->Output( $filename . '.pdf', 'I' );
				}
			}
} catch ( \Mpdf\MpdfException $e ) { // Note: safer fully qualified exception name used for catch.
			// Process the exception, log, print etc.
			if ( 'save' !== $this->template ) {
                $pdf_error_message = esc_attr($e->getMessage());
                if ( strpos($pdf_error_message, 'Cannot find TTF') !== false ) {
                    $doc_link = '<a target="_blank" href="'.admin_url() .'admin.php?page=webappick-woo-invoice-settings">' .esc_html('Go to setting page').'</a>'; //phpcs:ignore
                    if( is_admin() ) {
                        echo $pdf_error_message. '<h3><br/>' . __('Please go to Challan Pro plugins setting page to download the missing fonts. '.$doc_link, 'webappick-pdf-invoice-for-woocommerce') . '</h3>'; //phpcs:ignore
                    }else{
                        echo $pdf_error_message. '<h4><br/>' . __('Please notify site admin to download invoice.', 'webappick-pdf-invoice-for-woocommerce') . '</h4>'; //phpcs:ignore
                    }
                } else {
                    echo esc_attr($e->getMessage());
                }
			}
		}
	}

	/**
	 * Get Water mark image
	 *
	 * @return array|bool
	 */
	private function get_watermark() {
		$background = '';
		$opacity    = '';
		if ( 'invoice' == $this->template || 'credit_note' == $this->template || 'save' == $this->template ) {
			if ( get_option( 'wpifw_enable_invoice_background' ) == '1' ) {
				$opacity = ( get_option( 'wpifw_invoice_background_opacity' ) ) ? get_option( 'wpifw_invoice_background_opacity' ) : .3;
				if ( get_option( 'wpifw_enable_invoice_background' ) ) {
					$background = get_option( 'wpifw_invoice_background_attachment_id' );
				}
			}
		} elseif ( 'packing_slip' == $this->template || 'save_packing_slip' == $this->template ) {
			if ( get_option( 'wpifw_enable_packingslip_background' ) == '1' ) {
				$opacity = get_option( 'wpifw_packingslip_background_opacity' ) != false && ! empty( 'wpifw_packingslip_background_opacity' ) ? get_option( 'wpifw_packingslip_background_opacity' ) : 1;
				if ( get_option( 'wpifw_enable_packingslip_background' ) != false && ! empty( get_option( 'wpifw_packingslip_background_attachment_id' ) ) ) {
					$background = get_option( 'wpifw_packingslip_background_attachment_id' );
				}
			}
		}

		if ( ! empty( $background ) ) {

			return array(
				'background' => $background,
				'opacity'    => $opacity,
			);
		}

		return false;
	}

	/**
	 * Get Template Paper Size
	 *
	 * @param  string|array $paper_size Paper Size.
	 * @return mixed
	 */
	public function paper_size($paper_size ) {
		if ( 'invoice' == $this->template || 'credit_note' == $this->template ) {
			if ( false != get_option( 'wpifw_invoice_paper_size' ) && ! empty( get_option( 'wpifw_invoice_paper_size' ) ) && 'custom' != get_option( 'wpifw_invoice_paper_size' ) ) {
				$this->paper_size = get_option( 'wpifw_invoice_paper_size' );
			} elseif ( false != get_option( 'wpifw_invoice_paper_size' ) && ! empty( get_option( 'wpifw_invoice_paper_size' ) ) && 'custom' == get_option( 'wpifw_invoice_paper_size' ) ) {
				$this->paper_size = array();
				array_push( $this->paper_size, get_option( 'wpifw_invoice_custom_paper_wide' ) );
				array_push( $this->paper_size, get_option( 'wpifw_invoice_custom_paper_height' ) );
			} else {
				$this->paper_size = 'A4';
			}
		} elseif ( 'packing_slip' == $this->template || 'save' == $this->template ) {
			if ( false != get_option( 'wpifw-pickingslip-paper-size' ) && ! empty( get_option( 'wpifw-pickingslip-paper-size' ) ) && 'custom' != get_option( 'wpifw-pickingslip-paper-size' ) ) {
				$this->paper_size = get_option( 'wpifw-pickingslip-paper-size' );
			} elseif ( false != get_option( 'wpifw-pickingslip-paper-size' ) && ! empty( get_option( 'wpifw-pickingslip-paper-size' ) ) && 'custom' == get_option( 'wpifw-pickingslip-paper-size' ) ) {
				$this->paper_size = array();
				array_push( $this->paper_size, get_option( 'wpifw_pickingslip_custom_paper_wide' ) );
				array_push( $this->paper_size, get_option( 'wpifw_pickingslip_custom_paper_height' ) );
			} else {
				$this->paper_size = 'A4';
			}
		} elseif ( 'label' == $this->template ) {
			$paper_sizes = array( 'A3', 'A4', 'A5', 'Letter' );
			if ( in_array( $paper_size, $paper_sizes ) ) {
				$this->paper_size = $paper_size;
			} else {
				$this->paper_size = explode( ',', $paper_size );
			}
		}
		return apply_filters( 'woo_invoice_paper_size', $this->paper_size, $this->template );
	}
}

/**
 * Init PDF Class
 *
 * @param string    $html       Template Content.
 * @param string    $file_name  File Name.
 * @param int|array $order_id   Order Ids.
 * @param string    $template   Template type.
 * @param string    $paper_size Paper Size.
 *
 * @return Woo_Invoice_Pro_PDF
 */
function woo_invoice_pro_pdf( $html, $file_name, $order_id, $template, $paper_size = null ) {

    // Retrieve paper size dynamically if not provided
    if (!$paper_size) {
        $invoice_pdf = new Woo_Invoice_Pro_PDF('', '', $order_id, $template, '');
        $paper_size = $invoice_pdf->paper_size($template);
    }

    return new Woo_Invoice_Pro_PDF( $html, $file_name, $order_id, $template, $paper_size );
}
