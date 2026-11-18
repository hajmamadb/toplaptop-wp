<?php
/**
 *
 */
if ( ! function_exists('woo_invoice_filter_label') ) {

    function woo_invoice_filter_label( $label, $order, $template ) {
        $translated = __( $label, 'webappick-pdf-invoice-for-woocommerce' );//phpcs:ignore

        // The bundled Persian translation catalog is incomplete (and a few entries are
        // mistranslated). When the active PDF locale is Persian, use a complete fallback
        // map for the labels that can appear in invoices/packing slips.
        $active_locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
        $active_locale = strtolower( str_replace( '-', '_', (string) $active_locale ) );

        if ( 0 === strpos( $active_locale, 'fa' ) ) {
            $fa_labels = array(
                'Invoice'                 => 'فاکتور',
                'Credit Note'             => 'صورتحساب اصلاحی',
                'Billing'                 => 'مشخصات خریدار',
                'Shipping'                => 'آدرس ارسال',
                'Invoice Number'          => 'شماره فاکتور',
                'Credit Note Number'      => 'شماره صورتحساب اصلاحی',
                'Invoice Date'            => 'تاریخ فاکتور',
                'Order Number'            => 'شماره سفارش',
                'Order Date'              => 'تاریخ سفارش',
                'Order Status'            => 'وضعیت سفارش',
                'Payment Method'          => 'روش پرداخت',
                'Shipping Method'         => 'روش ارسال',
                'Delivery Date'           => 'تاریخ تحویل',
                'Time Slot'               => 'بازه زمانی',
                'Place of Supply'         => 'محل عرضه',
                'No.'                     => 'ردیف',
                'Item'                    => 'محصول',
                'Cost'                    => 'قیمت',
                'Qty'                     => 'تعداد',
                'Quantity'                => 'تعداد',
                'Tax'                     => 'مالیات',
                'Taxes'                   => 'مالیات ها',
                'Tax Rate'                => 'نرخ مالیات',
                'Total'                   => 'جمع',
                'Regular Price'           => 'قیمت اصلی',
                'Regular Price with Tax'  => 'قیمت اصلی با مالیات',
                'Sale Price'              => 'قیمت فروش',
                'Sale Price with Tax'     => 'قیمت فروش با مالیات',
                'Price with Tax'          => 'قیمت با مالیات',
                'Unit Price'              => 'قیمت واحد',
                'Discount'                => 'تخفیف',
                'Total Discount'          => 'مجموع تخفیف',
                'Taxable Value'           => 'مبلغ مشمول مالیات',
                'Items Subtotal'          => 'جمع اقلام',
                'Coupon(s)'               => 'کوپن ها',
                'Fees'                    => 'هزینه ها',
                'Order Total Without Tax' => 'جمع سفارش بدون مالیات',
                'Order Total'             => 'مبلغ نهایی سفارش',
                'Refund'                  => 'بازپرداخت',
                'Net Payment'             => 'پرداخت خالص',
                'Phone'                   => 'تلفن',
                'Email'                   => 'ایمیل',
                'ID'                      => 'شناسه',
                'SKU'                     => 'کد محصول',
                'Parent SKU'              => 'کد محصول والد',
                'Category'                => 'دسته بندی',
                'Description'             => 'توضیحات',
                'Dimensions'              => 'ابعاد',
                'Dimension'               => 'ابعاد',
                'Weight'                  => 'وزن',
                'Vendor'                  => 'فروشنده',
                'Customer Note'           => 'یادداشت مشتری',
                'Total Quantity'          => 'تعداد کل',
                'Total Weight'            => 'وزن کل',
                'Account Name'            => 'نام صاحب حساب',
                'Account No'              => 'شماره حساب',
                'Bank Name'               => 'نام بانک',
                'IBAN'                    => 'شماره شبا',
                'BIC/Swift'               => 'کد سوئیفت',
                'Sort Code'               => 'کد شعبه',
                'Authorized Signature'    => 'امضای مجاز',
                'Payment Slip'            => 'رسید پرداخت',
                'Refund Address'          => 'آدرس بازپرداخت',
                'From'                    => 'از',
                'To'                      => 'تا',
                'Duration'                => 'مدت',
                'VAT Number'              => 'شماره مالیات بر ارزش افزوده',
                'SSN'                     => 'شناسه ملی',
                'GSTIN'                   => 'شناسه مالیاتی',
            );

            if ( isset( $fa_labels[ $label ] ) ) {
                $translated = $fa_labels[ $label ];
            }
        }

        return apply_filters(
            'woo_invoice_filter_template_label',
            $translated,
            $order,
            $template
        );
    }
}

if ( ! function_exists('woo_invoice_switch_language_callback') ) {
    /**
     * @param WC_Order $order Order Object.
     * @param $template
     */
    function woo_invoice_switch_language_callback( $language_code, $cookie_lang = true ) {
        if ( ! empty($language_code) ) {
            if ( class_exists('SitePress', false) ) {
                // WPML Switch Language.
                global $sitepress;
                if ( $sitepress->get_current_language() !== $language_code ) {
                    $sitepress->switch_lang($language_code, $cookie_lang);
                }
            }
            // when polylang plugin is activated.
            if ( defined('POLYLANG_BASENAME') || function_exists('PLL') ) {
                if ( pll_current_language() !== $language_code ) {
                    PLL()->curlang = PLL()->model->get_language($language_code);
                }
            }

            if ( 'wpifw_pdf_site_language' !== get_option('wpifw_pdf_document_language') ) {
                switch_to_locale($language_code);
            }
}
    }
}

if ( ! function_exists('woo_invoice_restore_language_callback') ) {
    /**
     * restore previous language.
     */
    function woo_invoice_restore_language_callback() {
        $language_code = '';
        if ( class_exists('SitePress', false) ) {
            // WPML restore Language.
            global $sitepress;
            $language_code = $sitepress->get_default_language();
        }

        // when polylang plugin is activated
        if ( defined('POLYLANG_BASENAME') || function_exists('PLL') ) {
            $language_code = pll_default_language();
        }
        /**
         * Filter to hijack Default Language code before restore.
         *
         * @param string $language_code
         */

        if ( ! empty($language_code) ) {
            woo_invoice_switch_language_callback($language_code);
        } else {
            $defaul_lang = get_option('wpifw_site_default_language');

            woo_invoice_switch_language_callback($defaul_lang);
        }
    }
}

if ( ! function_exists('woo_invoice_reload_text_domain') ) {
    /**
     * Load plugin textdomain during generating invoice.
     */
    function woo_invoice_reload_text_domain() {
        load_plugin_textdomain(
            'webappick-pdf-invoice-for-woocommerce',
            false,
            dirname(CHALLAN_PRO_BASE_NAME) . '/languages/'
        );
    }
}


if ( ! function_exists('woo_invoice_product_meta_query') ) {
    /**
     * @return array|object|null
     */
    function woo_invoice_product_meta_query(){
        global $wpdb;
        // $meta_query = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta GROUP BY meta_key" );
		if ( class_exists( 'WooCommerce' ) ) {
			$product_meta_query = $wpdb->get_results(
				"SELECT $wpdb->postmeta.meta_key, $wpdb->postmeta.meta_id FROM $wpdb->postmeta
                                    LEFT JOIN $wpdb->posts ON $wpdb->postmeta.post_id = $wpdb->posts.id
                                    WHERE $wpdb->posts.post_type = 'product'
                                    GROUP BY $wpdb->postmeta.meta_key"
			);
		} else {
			$product_meta_query = array();
		}

        return $product_meta_query;
    }
}

if ( ! function_exists('woo_invoice_order_meta_query') ) {
    /**
     * @return array|object|null
     */
    function woo_invoice_order_meta_query(){
        global $wpdb;
		if ( class_exists( 'WooCommerce' ) ) {
            // Check if HPOS (High-Performance Order Storage) is enabled
            if ( woo_invoice_pro_hpos_enabled() ) {
                // HPOS is enabled, query the wc_orders_meta table
                $order_meta_query_arr = $wpdb->get_results("SELECT DISTINCT meta_key FROM {$wpdb->prefix}wc_orders_meta");
            } else {
                // Traditional CPT-based orders are in use, query the postmeta table
                $order_meta_query_arr = $wpdb->get_results(
                    "SELECT $wpdb->postmeta.meta_key FROM $wpdb->postmeta
                                    LEFT JOIN $wpdb->posts ON $wpdb->postmeta.post_id = $wpdb->posts.id
                                    WHERE $wpdb->posts.post_type = 'shop_order'
                                    GROUP BY $wpdb->postmeta.meta_key"
                );
            }
		} else {
			$order_meta_query_arr = array();
		}
        $order_meta_query = array();
        $order_meta_query = $order_meta_query_arr;

        return $order_meta_query;
    }
}

if ( ! function_exists('woo_invoice_item_meta_query') ) {
    /**
     * @return array|object|null
     */
    function woo_invoice_item_meta_query(){
        global $wpdb;
		if ( class_exists( 'WooCommerce' ) ) {
			$item_meta = $wpdb->get_results(
				"SELECT  DISTINCT  $wpdb->order_itemmeta.meta_key
        FROM $wpdb->order_itemmeta");
		} else {
			$item_meta = array();
		}

        return $item_meta;
    }
}



if ( ! function_exists('woo_invoice_order_meta_data_position') ) {
    /**
     * @return string[].
     */
    function woo_invoice_order_meta_data_position(){
        $order_meta_position_arr = array(
            'after_order_data'        => 'After order data',
            'before_order_data'       => 'Before order data',
            'before_billing_address'  => 'Before billing address',
            'after_billing_address'   => 'After billing address',
            'before_shipping_address' => 'Before shipping address',
            'after_shipping_address'  => 'After shipping address',
        );

        return $order_meta_position_arr;
    }
}

if ( ! function_exists('woo_invoice_fonts') ) {
    /**
     *
     * @return string[].
     */
    function woo_invoice_fonts(){
        // MPDF font list.
        $woo_invoice_fonts = array(
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/AboriginalSansREGULAR.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/Abyssinica_SIL.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/Aegean.otf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/Aegyptus.otf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/Akkadian.otf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/DBSILBR.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/Dhyana-Bold.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/Dhyana-Regular.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/FreeMono.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/FreeMonoBold.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/FreeMonoBoldOblique.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/FreeMonoOblique.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/FreeSans.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/FreeSansBold.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/FreeSansBoldOblique.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/FreeSansOblique.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/FreeSerif.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/FreeSerifBold.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/FreeSerifBoldItalic.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/FreeSerifItalic.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/Garuda-BoldOblique.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/Garuda-Oblique.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/Jomolhari.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/KhmerOS.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/LateefRegOT.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/Padauk-book.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/Quivira.otf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/Sun-ExtA.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/Sun-ExtB.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/TaameyDavidCLM-Medium.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/TaiHeritagePro.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/Tharlon-Regular.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/UnBatang_0613.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/XB+Riyaz.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/XB+RiyazBd.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/XB+RiyazBdIt.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/XB+RiyazIt.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/ZawgyiOne.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/ayar.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/damase_v.2.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/lannaalif-v1-03.ttf',
            'https://challan-assets.s3.ap-southeast-1.amazonaws.com/fonts/ocrb10.ttf',
        );

        return $woo_invoice_fonts;
    }
}
/**
 * Get all WordPress registered language for generating pdf invoice.
 * @return string[]
 */
function woo_invoice_pro_get_default_languages(){
    return array(
        'af'             => 'Afrikaans',
        'ar'             => 'العربية',
        'ary'            => 'العربية المغربية',
        'as'             => 'অসমীয়া',
        'azb'            => 'گؤنئی آذربایجان',
        'az'             => 'Azərbaycan dili',
        'bel'            => 'Беларуская мова',
        'bg_BG'          => 'Български',
        'bn_BD'          => 'বাংলা',
        'bo'             => 'བོད་ཡིག',
        'bs_BA'          => 'Bosanski',
        'ca'             => 'Català',
        'ceb'            => 'Cebuano',
        'cs_CZ'          => 'Čeština',
        'cy'             => 'Cymraeg',
        'da_DK'          => 'Dansk',
        'de_DE_formal'   => 'Deutsch (Sie)',
        'de_DE'          => 'Deutsch',
        'de_CH_informal' => 'Deutsch (Schweiz, Du)',
        'de_CH'          => 'Deutsch (Schweiz)',
        'de_AT'          => 'Deutsch (Österreich)',
        'dsb'            => 'Dolnoserbšćina',
        'dzo'            => 'རྫོང་ཁ',
        'el'             => 'Ελληνικά',
        'en_CA'          => 'English (Canada)',
        'en_NZ'          => 'English (New Zealand)',
        'en_ZA'          => 'English (South Africa)',
        'en_GB'          => 'English (UK)',
        'en_AU'          => 'English (Australia)',
        'eo'             => 'Esperanto',
        'es_DO'          => 'Español de República Dominicana',
        'es_CR'          => 'Español de Costa Rica',
        'es_VE'          => 'Español de Venezuela',
        'es_CO'          => 'Español de Colombia',
        'es_CL'          => 'Español de Chile',
        'es_UY'          => 'Español de Uruguay',
        'es_PR'          => 'Español de Puerto Rico',
        'es_ES'          => 'Español',
        'es_GT'          => 'Español de Guatemala',
        'es_PE'          => 'Español de Perú',
        'es_MX'          => 'Español de México',
        'es_EC'          => 'Español de Ecuador',
        'es_AR'          => 'Español de Argentina',
        'et'             => 'Eesti',
        'eu'             => 'Euskara',
        'fa_AF'          => '(فارسی (افغانستان',
        'fa_IR'          => 'فارسی',
        'fi'             => 'Suomi',
        'fr_FR'          => 'Français',
        'fr_CA'          => 'Français du Canada',
        'fr_BE'          => 'Français de Belgique',
        'fur'            => 'Friulian',
        'gd'             => 'Gàidhlig',
        'gl_ES'          => 'Galego',
        'gu'             => 'ગુજરાતી',
        'haz'            => 'هزاره گی',
        'he_IL'          => 'עִבְרִית',
        'hi_IN'          => 'हिन्दी',
        'hr'             => 'Hrvatski',
        'hsb'            => 'Hornjoserbšćina',
        'hu_HU'          => 'Magyar',
        'hy'             => 'Հայերեն',
        'id_ID'          => 'Bahasa Indonesia',
        'is_IS'          => 'Íslenska',
        'it_IT'          => 'Italiano',
        'ja'             => '日本語',
        'jv_ID'          => 'Basa Jawa',
        'ka_GE'          => 'ქართული',
        'kab'            => 'Taqbaylit',
        'kk'             => 'Қазақ тілі',
        'km'             => 'ភាសាខ្មែរ',
        'kn'             => 'ಕನ್ನಡ',
        'ko_KR'          => '한국어',
        'ckb'            => 'كوردی‎',
        'lo'             => 'ພາສາລາວ',
        'lt_LT'          => 'Lietuvių kalba',
        'lv'             => 'Latviešu valoda',
        'mk_MK'          => 'Македонски јазик',
        'ml_IN'          => 'മലയാളം',
        'mn'             => 'Монгол',
        'mr'             => 'मराठी',
        'ms_MY'          => 'Bahasa Melayu',
        'my_MM'          => 'ဗမာစာ',
        'nb_NO'          => 'Norsk bokmål',
        'ne_NP'          => 'नेपाली',
        'nl_NL_formal'   => 'Nederlands (Formeel)',
        'nl_BE'          => 'Nederlands (België)',
        'nl_NL'          => 'Nederlands',
        'nn_NO'          => 'Norsk nynorsk',
        'oci'            => 'Occitan',
        'pa_IN'          => 'ਪੰਜਾਬੀ',
        'pl_PL'          => 'Polski',
        'ps'             => 'پښتو',
        'pt_PT'          => 'Português',
        'pt_PT_ao90'     => 'Português (AO90)',
        'pt_AO'          => 'Português de Angola',
        'pt_BR'          => 'Português do Brasil',
        'rhg'            => 'Ruáinga',
        'ro_RO'          => 'Română',
        'ru_RU'          => 'Русский',
        'sah'            => 'Сахалыы',
        'snd'            => 'سنڌي',
        'si_LK'          => 'සිංහල',
        'sk_SK'          => 'Slovenčina',
        'skr'            => 'سرائیکی',
        'sl_SI'          => 'Slovenščina',
        'sq'             => 'Shqip',
        'sr_RS'          => 'Српски језик',
        'sv_SE'          => 'Svenska',
        'sw'             => 'Kiswahili',
        'szl'            => 'Ślōnskŏ gŏdka',
        'ta_IN'          => 'தமிழ்',
        'ta_LK'          => 'தமிழ்',
        'te'             => 'తెలుగు',
        'th'             => 'ไทย',
        'tl'             => 'Tagalog',
        'tr_TR'          => 'Türkçe',
        'tt_RU'          => 'Татар теле',
        'tah'            => 'Reo Tahiti',
        'ug_CN'          => 'ئۇيغۇرچە',
        'uk'             => 'Українська',
        'ur'             => 'اردو',
        'uz_UZ'          => 'O‘zbekcha',
        'vi'             => 'Tiếng Việt',
        'zh_TW'          => '繁體中文',
        'zh_HK'          => '香港中文版	',
        'zh_CN'          => '简体中文',
    );
}

/**
 * Check if current status is checked for showing button to my account page and during email attachment.
 * @param $status
 * @param $checked_array
 * @return bool
 */
function is_current_status_checked( $status, $checked_array ) {
    if ( 'always_allow' == $status ) {
        return true;
    }
    if ( in_array( $status, $checked_array ) ) {
        return  true;
    }
    foreach ( $checked_array as &$value ) {
        $value = str_replace( '_', '-', $value );
        if ( strpos( $value, $status) !== false ) {
            return true;
        }
    }

    return false;
}

/**
 * Should generate invoice and other functionality by challan.
 * By default 'all' means generate invoice and other functionalities as usual.
 * If needs to generate invoice for specific order status then apply the filter
 * 'woo_invoice_pro_generate_pdf_for_order_statuses' and first reset the value of the filter.
 * Then return an array of expected order statuses.
 *
 * @param WC_Order|array|int $order
 * @return bool
 */
function should_generate_invoice($order) {
    $statuses = apply_filters('woo_invoice_pro_generate_pdf_for_order_statuses', ['all']);

    if (!in_array('all', $statuses)) {
        if (is_array($order) && isset($order['id'])) {
            $order = wc_get_order($order['id']);
        } elseif (is_array($order) && isset($order['order_id'])) {
            $order = wc_get_order($order['order_id']);
        } elseif (is_numeric($order)) {
            $order = wc_get_order($order);
        }

        if (!is_a($order, 'WC_Order')) {
            return false;
        }

        $order_status = $order->get_status();
        if (!in_array($order_status, $statuses)) {
            return false;
        }
    }

    return true;
}

/**
 * Check if HPOS (High-Performance Order Storage) is enabled.
 *
 * @return bool
 */
function woo_invoice_pro_hpos_enabled() {
    return class_exists('Automattic\WooCommerce\Utilities\OrderUtil') && Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
}

/**
 * Get WC_Order object from the given value.
 *
 * @since 4.4.7
 * @param int|WC_Order $order Order id or order object
 * @return WC_Order|bool Order object or false if invalid
 */
function woo_invoice_pro_get_order($order) {
    // Check if the order is an integer
    if (is_int($order)) {
        $order = wc_get_order($order);
    }
    // Check if the order is a string that can be converted to a positive integer
    elseif (is_string($order) && 0 < absint($order)) {
        $order = wc_get_order(absint($order));
    }

    // If the order is already a WC_Order object, return it
    if ($order instanceof WC_Order) {
        return $order;
    }

    // Return false if the order is invalid
    return false;
}

/**
 * Get order meta data depending on whether HPOS is enabled.
 *
 * @param \WC_Order $order
 * @param string $meta_key
 * @param bool $is_single
 * @return mixed
 */
function woo_invoice_pro_get_meta($order, $meta_key, $is_single = true) {
    $order = woo_invoice_pro_get_order($order);

    if (!$order) {
        return false;
    }

    if (woo_invoice_pro_hpos_enabled()) {
        return $order->get_meta($meta_key, $is_single);
    }
    return get_post_meta($order->get_id(), $meta_key, $is_single);

}

/**
 * Update order meta data depending on whether HPOS is enabled.
 *
 * @param int|WC_Order $order Order id or order object
 * @param string $meta_key
 * @param mixed $meta_value
 */
function woo_invoice_pro_update_meta($order, $meta_key, $meta_value) {
    $order = woo_invoice_pro_get_order($order);

    if (!$order) {
        return false;
    }

    if (woo_invoice_pro_hpos_enabled()) {
        $order->update_meta_data($meta_key, $meta_value);
        $order->save();
    } else {
        update_post_meta($order->get_id(), $meta_key, $meta_value);
    }
}

// End of file helper.php