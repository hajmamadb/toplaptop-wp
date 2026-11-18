<?php
if (!defined('ABSPATH')) { exit; }

add_filter('kuteshop_site_logo', function () {
    return '<a href="' . esc_url(home_url('/')) . '" aria-label="تاپ لپتاپ؛ صفحه اصلی"><span style="display:block;font-size:27px;font-weight:700;color:#0f766e;white-space:nowrap">تاپ لپتاپ</span><span style="display:block;font-size:11px;color:#526579">انتخاب مطمئن، خرید آسان</span></a>';
});

add_filter('get_avatar_url', function ($url, $id_or_email) {
    return !$id_or_email ? plugins_url('tlt-avatar.svg', __FILE__) : $url;
}, 10, 2);

add_filter('woocommerce_shortcode_products_query', function ($args) {
    $visibility = wc_get_product_visibility_term_ids();
    $args['tax_query'][] = array('taxonomy' => 'product_visibility', 'field' => 'term_taxonomy_id', 'terms' => array($visibility['exclude-from-catalog'], $visibility['outofstock']), 'operator' => 'NOT IN');
    return $args;
});

add_action('wp', function () {
    if (class_exists('YITH_WooCompare_Frontend')) {
        $compare = array(YITH_WooCompare_Frontend::instance(), 'output_button');
        if (has_action('woocommerce_after_shop_loop_item', $compare) !== false) {
            remove_action('woocommerce_after_shop_loop_item', $compare, 20);
            remove_action('kuteshop_function_shop_loop_item_compare', 'kuteshop_wc_loop_product_compare_btn', 1);
            add_action('kuteshop_function_shop_loop_item_compare', $compare, 1);
        }
    }
    if (class_exists('YITH_WCQV_Frontend')) {
        $quickview = array(YITH_WCQV_Frontend::get_instance(), 'yith_add_quick_view_button');
        if (has_action('woocommerce_after_shop_loop_item', $quickview) !== false) {
            remove_action('woocommerce_after_shop_loop_item', $quickview, 15);
            add_action('kuteshop_function_shop_loop_item_quickview', $quickview, 5);
        }
    }
});

add_shortcode('tlt_support', function () {
    return shortcode_exists('supportcandy') ? do_shortcode('[supportcandy]') : '<p role="status">سامانه ثبت و پیگیری تیکت هنوز آماده نیست؛ فعلاً از تماس با پشتیبانی استفاده کنید.</p>';
});

add_action('wp_enqueue_scripts', function () {
    wp_dequeue_style('kuteshop-fonts');
    wp_add_inline_style('kuteshop-main-style', '.woocommerce-cart-form .product-remove a.remove,.wishlist_table .product-remove a.remove,.wishlist_table a.remove_from_wishlist{width:44px;height:44px;display:inline-flex;align-items:center;justify-content:center}.product-item .group-button .yith-add-to-wishlist-button-block{width:44px}.product-item .group-button .yith-wcwl-add-to-wishlist-button{width:44px;height:44px;justify-content:center;background:#fff;border-radius:3px}.product-item .group-button .yith-wcwl-add-to-wishlist-button-icon{width:24px;height:24px}.product-item .group-button .yith-wcwl-add-to-wishlist-button__label{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap}.product-item .group-button>.button{width:44px;height:44px;min-width:44px;min-height:44px;line-height:44px;padding:0}body.home .kuteshop-tabs .product-list-owl.nav-center{padding-bottom:56px}body.home .kuteshop-tabs .product-list-owl.nav-center>.slick-arrow{top:auto;bottom:0;width:44px;height:44px;line-height:44px;text-align:center}#yith-quick-view-modal .yith-quick-view-close{position:absolute;top:0;left:0;width:44px;height:44px;display:flex;align-items:center;justify-content:center;color:#172b42;background:#fff;z-index:2}#yith-quick-view-modal .yith-quick-view-close svg{width:24px;height:24px}@media(max-width:480px){#yith-quick-view-modal.open .yith-wcqv-main{max-height:90dvh;overflow-y:auto}#yith-quick-view-modal #yith-quick-view-content::before{display:none}#yith-quick-view-modal.open #yith-quick-view-content div.entry-summary{position:static}#yith-quick-view-modal.open #yith-quick-view-content .summary-content>*:not(.price){color:#172b42}body.rtl #yith-woocompare table.dataTable.compare-list th:not(.filler){width:88px!important;min-width:88px;max-width:88px;font-size:12px;padding:10px 6px}body #yith-woocompare table.compare-list th h2{font-size:14px;overflow-wrap:anywhere}body.rtl #yith-woocompare table.dataTable.compare-list td:not(.filler){width:200px!important;min-width:200px;max-width:200px;padding-inline:12px;font-size:13px}body.rtl #yith-woocompare table.compare-list thead th,body.rtl #yith-woocompare table.compare-list tfoot th{display:table-cell}body.rtl #yith-woocompare .DTFC_LeftBodyLiner{padding-right:0!important;padding-left:20px}.dgwt-wcas-search-input,.form-search .input{color:#4a4a4a}.dgwt-wcas-search-input::placeholder,.form-search .input::placeholder{color:#595959;opacity:1}.kuteshop-category h4.title span{color:#595959!important}.kuteshop-tabs .tab-control a.section-up,.kuteshop-tabs .tab-control a.section-down{min-width:44px;min-height:44px;display:inline-flex;align-items:center;justify-content:center}');
}, 30);

add_action('wp_footer', function () {
    if (is_admin()) { return; }
    echo '<script>(function(){function fix(){var main=document.querySelector(".fullwidth-template");if(main&&!main.getAttribute("role"))main.setAttribute("role","main");document.querySelectorAll("a.menu-bar:not([aria-label])").forEach(function(a){a.setAttribute("aria-label","باز کردن منو")});document.querySelectorAll("a.backtotop:not([aria-label])").forEach(function(a){a.setAttribute("aria-label","بازگشت به بالای صفحه")});document.querySelectorAll("a.section-up:not([aria-label])").forEach(function(a){a.setAttribute("aria-label","بخش‌های قبلی")});document.querySelectorAll("a.section-down:not([aria-label])").forEach(function(a){a.setAttribute("aria-label","بخش‌های بعدی")});document.querySelectorAll("a.chosen-single").forEach(function(a){a.setAttribute("aria-hidden","true");a.setAttribute("tabindex","-1")})}function whenReady(){if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",run)}else{run()}}function run(){fix();[300,800,1500].forEach(function(ms){setTimeout(fix,ms)})}whenReady()})();</script>';
});

add_action('woof_after_inline_js', function () {
    wp_add_inline_script('woof_front', 'Object.assign(woof_lang,{"on-sale":"تخفیف‌دارها",sale:"فروش",feature:"منتخب‌ها"});jQuery(function($){$(".woof_range_slider").each(function(){const slider=$(this).data("ionRangeSlider");if(slider)slider.update({force_edges:true,grid:false});});});');
});

add_filter('generate_carousel_data_attributes', function ($data, $atts) {
    if (($atts['productsliststyle'] ?? '') !== 'owl') { return $data; }
    // Initial mobile layout can expand innerWidth before Slick constrains the product track.
    return str_replace(' data-slick = {', ' data-slick = {"respondTo":"min",', $data);
}, 20, 2);

add_filter('woocommerce_product_query_meta_query', function ($meta_query) {
    if ((isset($_GET['min_price']) && $_GET['min_price'] !== '') || (isset($_GET['max_price']) && $_GET['max_price'] !== '')) {
        $meta_query[] = array('key' => '_price', 'value' => '', 'compare' => '!=');
    }
    return $meta_query;
});

add_filter('yith_wcms_validate_checkout_event', function ($events) {
    // Synthetic province changes rebuild the Persian city selector and erase its selection.
    return array_values(array_diff($events, array('change')));
});

add_action('woocommerce_after_checkout_validation', function ($data, $errors) {
    foreach (array('billing', 'shipping') as $type) {
        if ($type === 'shipping' && empty($data['ship_to_different_address'])) { continue; }
        if (($data[$type . '_country'] ?? '') === 'IR' && in_array($data[$type . '_city'] ?? '', array('-1', '0'), true)) {
            $errors->add($type . '_city_required', 'لطفاً شهر نشانی ' . ($type === 'billing' ? 'صورتحساب' : 'ارسال') . ' را انتخاب کنید.');
        }
    }
}, 10, 2);

add_action('wp_enqueue_scripts', function () {
    if (!is_checkout() || is_order_received_page()) { return; }
    wp_add_inline_script('wc-checkout', <<<'JS'
jQuery(function ($) {
    if (!window.yith_wcms) return;
    const settings = window.yith_wcms;
    $('form.checkout').on('input validate change focusout', '#billing_city, #shipping_city', function () {
        const invalid = !$(this).val() || $(this).val() === '-1';
        $(this).closest('.form-row')
            .toggleClass('woocommerce-invalid woocommerce-invalid-required-field', invalid)
            .toggleClass('woocommerce-validated', !invalid);
    });
    $(document).on('input change focusout', 'form.checkout .input-text, form.checkout select', function () {
        queueMicrotask(function () {
            const step = $(settings.dom.checkout_timeline).find('.active').data('step');
            if (step !== 'billing' && step !== 'shipping') return;
            const invalid = $(settings.dom[step]).find(settings.dom.wc_invalid_required).filter(':visible').length > 0;
            $(settings.dom.button_next).toggleClass('disabled', invalid).prop('disabled', invalid);
        });
    });
});
JS
    );
}, 30);

add_filter('woocommerce_get_price_html', function ($html, $product) {
    return $product->get_price() === '' ? '<span class="tlt-inquiry-price">برای قیمت روز، استعلام کنید</span>' : $html;
}, 10, 2);

add_filter('yith_wcwl_item_formatted_price', function ($html, $base_price, $product) {
    return $base_price === '' ? $product->get_price_html() : $html;
}, 10, 3);

add_action('woocommerce_single_product_summary', function () {
    global $product;
    if (!$product || !$product->is_in_stock()) { return; }
    $url = add_query_arg('product_id', $product->get_id(), get_permalink(25));
    echo '<div class="tlt-product-finance" style="border:1px solid #dde6ed;border-radius:5px;padding:16px;margin:18px 0;background:#f0fdfa">';
    if ($product->get_price() !== '') {
        echo '<p>پیش‌پرداخت تقریبی یک‌سوم: <strong>' . wc_price(ceil((float) $product->get_price() / 3)) . '</strong></p>';
    }
    echo '<p>۳، ۶، ۱۰ یا ۱۲ قسط؛ پس از بررسی و تأیید شرایط نهایی.</p><a href="' . esc_url($url) . '">محاسبه و ثبت درخواست خرید اقساطی ←</a>';
    if ($product->get_price() === '') {
        echo '<p><a href="tel:09103208705">تماس برای قیمت و موجودی: ۰۹۱۰۳۲۰۸۷۰۵</a></p>';
    }
    echo '</div>';
}, 25);

add_filter('gettext', function ($translation, $text, $domain) {
    if (is_admin() || !in_array($domain, array('kuteshop', 'kuteshop-toolkit', 'ovic-toolkit', 'ovic-addon-toolkit', 'woocommerce-products-filter', 'yith-woocommerce-compare', 'wp-loyalty-rules'), true)) { return $translation; }
    return array('categories' => 'دسته‌بندی', 'Qty:' => 'تعداد:', 'END IN' => 'پایان پیشنهاد', 'Guest' => 'مهمان', 'Example@email.com' => 'برای ورود روی حساب کاربری بزنید', 'MAIN MENU' => 'فهرست اصلی', 'Main Menu' => 'فهرست اصلی', 'Sort by Sale' => 'مرتب‌سازی بر اساس فروش', 'Sort by On-Sale' => 'نمایش تخفیف‌دارها', 'Sort by Feature' => 'نمایش منتخب‌ها', 'In stock' => 'موجود', 'On sale' => 'دارای تخفیف', 'Clear All' => 'پاک کردن فیلترها', 'Reset' => 'پاک کردن فیلترها', 'orderby' => 'مرتب‌سازی', 'date' => 'تاریخ', 'per page' => 'تعداد در صفحه', 'price range' => 'بازهٔ قیمت', 'menu order' => 'پیش‌فرض', 'popularity' => 'محبوبیت', 'rating' => 'امتیاز', 'price low to high' => 'ارزان‌ترین', 'price high to low' => 'گران‌ترین', 'Added to compare' => 'افزوده شد؛ مشاهده مقایسه', 'View comparison' => 'مشاهده مقایسه', 'No products added in the comparison table.' => 'هنوز محصولی به فهرست مقایسه اضافه نشده است.', 'Title' => 'نام محصول', 'Image' => 'تصویر', 'Price' => 'قیمت', 'Availability' => 'موجودی', 'Ways to earn %s ' => 'روش‌های کسب %s', '%s opportunities' => 'فرصت‌های دریافت %s', 'rewards' => 'پاداش‌ها', 'reward' => 'پاداش', 'Show more' => 'نمایش بیشتر', 'Show less' => 'نمایش کمتر', '%1$d %2$s for each %3$s spent' => '%1$d %2$s به‌ازای هر %3$s خرید', '%1$d %2$s for each %3$d spent' => '%1$d %2$s به‌ازای هر %3$d خرید')[$text] ?? $translation;
}, 20, 3);
