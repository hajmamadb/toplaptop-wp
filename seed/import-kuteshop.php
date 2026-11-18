<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }

$xml = simplexml_load_file(get_template_directory() . '/importer/data/content-rtl.xml', 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
$demo = array();
foreach ($xml->channel->item as $item) {
    if (in_array((string) $item->title, array('Home 12', 'Footer 12'), true)) {
        $demo[(string) $item->title] = $item;
    }
}
$home_id = 33;
if (!get_option('tlt_before_kuteshop_design')) {
    update_option('tlt_before_kuteshop_design', array('home' => get_post($home_id)->post_content, 'theme_options' => get_option('_cs_options'), 'menus' => get_theme_mod('nav_menu_locations')));
}
$cats = array_map(fn($id) => get_term($id, 'product_cat'), array(16, 17, 18, 19));
$shop = wc_get_page_permalink('shop');
$install = get_permalink(25);
$advisor = get_permalink(26);
$club = get_permalink(27);
$account = wc_get_page_permalink('myaccount');
$contact = get_permalink(31);
$empty_category_titles = array_map(fn($term) => $term->name, array_filter($cats, fn($term) => $term->count === 0));
$menu_specs = array(
    'tlt-primary' => array('فروشگاه' => $shop, 'خرید اقساطی' => $install, 'مشاوره انتخاب' => $advisor, 'باشگاه مشتریان' => $club, 'راهنمای خرید' => get_permalink(32)),
    'tlt-categories' => array_combine(array_map(fn($t) => $t->name, $cats), array_map(fn($t) => get_term_link($t), $cats)) + array('همه لپتاپ‌ها' => $shop, 'پیشنهادهای ویژه' => get_permalink(46), 'جشنواره بلک فرایدی' => get_permalink(45), 'مشاوره رایگان' => $advisor),
    'tlt-help' => array('راهنمای خرید' => get_permalink(32), 'شرایط خرید اقساطی' => $install, 'خدمات پس از فروش' => $contact, 'شرایط مرجوعی' => get_permalink(30)),
    'tlt-legal' => array('قوانین و مقررات' => get_permalink(28), 'حریم خصوصی' => get_permalink(29), 'تماس با پشتیبانی' => $contact),
    'tlt-account' => array('حساب کاربری' => $account, 'سفارش‌های من' => wc_get_endpoint_url('orders', '', $account), 'باشگاه مشتریان' => $club, 'سبد خرید' => wc_get_cart_url()),
    'tlt-top-left' => array('خرید نقدی و اقساطی با پیش‌پرداخت یک‌سوم' => $install),
    'tlt-top-right' => array('پشتیبانی: ۰۹۱۰۳۲۰۸۷۰۵' => 'tel:09103208705'),
);
$menus = array();
foreach ($menu_specs as $slug => $links) {
    $menu = wp_get_nav_menu_object($slug);
    $id = $menu ? $menu->term_id : wp_create_nav_menu($slug);
    if (is_wp_error($id)) { WP_CLI::error($id->get_error_message()); }
    $existing = wp_get_nav_menu_items($id, array('post_status' => 'any')) ?: array();
    $position = 0;
    foreach ($links as $title => $url) {
        $matched = array_values(array_filter($existing, fn($i) => $i->title === $title));
        wp_update_nav_menu_item($id, $matched ? $matched[0]->ID : 0, array('menu-item-title' => $title, 'menu-item-url' => $url, 'menu-item-type' => 'custom', 'menu-item-status' => $slug === 'tlt-categories' && in_array($title, $empty_category_titles, true) ? 'draft' : 'publish', 'menu-item-position' => ++$position));
    }
    $menus[$slug] = $id;
}
set_theme_mod('nav_menu_locations', array('primary' => $menus['tlt-primary'], 'vertical_menu' => $menus['tlt-categories'], 'top_left_menu' => $menus['tlt-top-left'], 'top_right_menu' => $menus['tlt-top-right']));

$illustration = '<svg viewBox="0 0 460 310" role="img" aria-label="تصویر نمادین لپتاپ" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="screen" x2="1" y2="1"><stop stop-color="#0f766e"/><stop offset="1" stop-color="#38bdf8"/></linearGradient></defs><ellipse cx="230" cy="275" rx="200" ry="16" fill="#0f172a" opacity=".15"/><rect x="68" y="34" width="324" height="218" rx="15" fill="#172b42"/><rect x="80" y="47" width="300" height="190" rx="6" fill="url(#screen)"/><path d="M80 220L260 47H380V237H80Z" fill="white" opacity=".09"/><circle cx="230" cy="40" r="2" fill="#94a3b8"/><path d="M68 252H392L437 271Q439 278 420 280H40Q21 278 23 271Z" fill="#cbd5e1"/><path d="M192 252H268L278 263H182Z" fill="#64748b"/><path d="M25 274H435" stroke="#94a3b8" stroke-width="3"/></svg>';
$hero = '<section class="tlt-hero"><div><span class="tlt-eyebrow">تاپ لپتاپ؛ انتخاب مطمئن شما</span><h1>لپتاپ مناسب تو،<br>با شرایط مناسب تو</h1><p>از کار و دانشگاه تا بازی و طراحی؛<br>خرید نقدی و اقساطی با مشاوره تخصصی.</p><a class="tlt-button" href="' . esc_url($shop) . '">انتخاب لپتاپ</a><a class="tlt-secondary" href="' . esc_url($install) . '">محاسبه اقساط</a></div><div class="tlt-hero-art">' . $illustration . '<span>تصویر نمادین؛ مشخصات هر دستگاه در صفحه محصول</span></div></section>';
$promo = function ($title, $text, $url, $class = '') {
    return '[vc_column_text]<a class="tlt-promo ' . esc_attr($class) . '" href="' . esc_url($url) . '"><strong>' . esc_html($title) . '</strong><span>' . esc_html($text) . '</span><b>مشاهده و بررسی ←</b></a>[/vc_column_text]';
};
$content = (string) $demo['Home 12']->children('http://purl.org/rss/1.0/modules/content/')->encoded;
$content = preg_replace('/^<p>|<\/p>$/', '', trim($content));
$content = str_replace('[rev_slider_vc alias="kuteshop-opt-12"]', '[vc_column_text]' . $hero . '[/vc_column_text]', $content);
$content = preg_replace('/\[kuteshop_slider[^\]]*\].*?\[\/kuteshop_slider\]/s', '', $content);
$content = preg_replace('/\[kuteshop_testimonials[^\]]*\]/', $promo('با خیال راحت انتخاب کن', 'مشاوره قبل از خرید و پشتیبانی پس از فروش', $advisor), $content);
$content = preg_replace('/\[kuteshop_blog[^\]]*\]/', '[vc_column_text]<h2>راهنمای انتخاب و خرید لپتاپ</h2><p><a href="' . esc_url(get_permalink(32)) . '">مراحل خرید و بررسی دستگاه</a> · <a href="' . esc_url($advisor) . '">انتخاب بر اساس کاربرد و بودجه</a></p>[/vc_column_text]', $content);
$promos = array(
    4080 => array('خرید اقساطی', 'شروع با یک‌سوم پیش‌پرداخت', $install),
    4071 => array('قدرت برای بازی', 'لپتاپ‌های گیمینگ و مهندسی', get_term_link($cats[0])),
    4074 => array('همراه کار و دانشگاه', 'سبک، کاربردی و اقتصادی', get_term_link($cats[1])),
    4077 => array('استوک تست‌شده', 'انتخاب مقرون‌به‌صرفه با مهلت تست', get_term_link($cats[2])),
    6000 => array('باشگاه مشتریان', 'خرید، امتیاز و پاداش', $club),
    4218 => array('سه‌شنبه‌های تخفیفی', 'پیشنهادهای محدود روی دستگاه‌های منتخب', get_permalink(46)),
    4221 => array('بلک فرایدی تاپ لپتاپ', '۵ تا ۷ آذر؛ ویژه لپتاپ‌های استوک', get_permalink(45)),
);
$content = preg_replace_callback('/\[vc_single_image[^\]]*image="(\d+)"[^\]]*\]/', function ($m) use ($promos, $promo) {
    return isset($promos[(int) $m[1]]) ? $promo(...$promos[(int) $m[1]]) : '';
}, $content);
$content = preg_replace_callback('/(\[kuteshop_tabs\b[^\]]*\])(.*?)(\[\/kuteshop_tabs\])/s', function ($match) {
    preg_match('/\[vc_tta_section\b[^\]]*\].*?\[\/vc_tta_section\]/s', $match[2], $section);
    return $match[1] . $section[0] . $match[3];
}, $content);
$section = -1;
$content = preg_replace_callback('/\[(kuteshop_tabs|kuteshop_products|vc_tta_section|kuteshop_category|kuteshop_blog)\b([^\]]*)\]/', function ($m) use (&$section, $cats) {
    $tag = $m[1];
    $a = shortcode_parse_atts($m[2]);
    $a = is_array($a) ? $a : array();
    unset($a['css_animation']);
    if ($tag === 'kuteshop_tabs') {
        ++$section;
        $a['tab_title'] = array('لپتاپ‌های گیمینگ', 'اداری و دانشجویی', 'استوک تست‌شده')[$section];
        $a['taxonomy'] = $cats[$section]->slug;
        $a['tab_color'] = '#0f766e';
        $a['use_tabs_filter'] = '';
        $a['short_title'] = '0';
        $a['ajax_check'] = '0';
        unset($a['tabs_banner']);
    } elseif ($tag === 'vc_tta_section') {
        $a['title'] = 'مدل‌های موجود';
    } elseif ($tag === 'kuteshop_products') {
        $a['per_page'] = '4';
        $a['owl_number_row'] = '1';
        $a['owl_loop'] = 'false';
        $a['enable_countdown'] = '';
        $a['content_ajax'] = '';
        $a['owl_sm_items'] = '2';
        $a['owl_xs_items'] = '2';
        $a['owl_ts_items'] = '1';
        if (($a['product_style'] ?? '') === '16') {
            $a['target'] = 'on_sale';
            $a['the_title'] = 'پیشنهادهای ویژه امروز';
            unset($a['ids']);
        } elseif (($a['product_style'] ?? '') === '17') {
            $a['target'] = 'recent-product';
            $a['the_title'] = 'تازه‌های فروشگاه';
            unset($a['ids']);
        } else {
            $a['taxonomy'] = $cats[$section]->slug;
            $a['target'] = 'product-category';
        }
    } elseif ($tag === 'kuteshop_category') {
        static $category_index = 0;
        $a['title'] = $cats[$category_index]->name;
        $a['taxonomy'] = $cats[$category_index]->slug;
        $a['link'] = 'url:' . rawurlencode(get_term_link($cats[$category_index])) . '|title:' . rawurlencode('مشاهده همه');
        unset($a['banner']);
        ++$category_index;
    } elseif ($tag === 'kuteshop_blog') {
        $a['blog_title'] = 'راهنمای انتخاب و خرید لپتاپ';
        $a['blog_desc'] = 'قبل از خرید، آگاهانه انتخاب کنید';
        $a['owl_loop'] = 'false';
    }
    $out = '[' . $tag;
    foreach ($a as $key => $value) { $out .= ' ' . $key . '="' . esc_attr($value) . '"'; }
    return $out . ']';
}, $content);
wp_update_post(wp_slash(array('ID' => $home_id, 'post_content' => $content)));
foreach ($demo['Home 12']->children('http://wordpress.org/export/1.2/')->postmeta as $meta) {
    $wp = $meta->children('http://wordpress.org/export/1.2/');
    $key = (string) $wp->meta_key;
    if (in_array($key, array('_Ovic_VC_Shortcode_Custom_Css', '_Kuteshop_Shortcode_custom_css', '_wp_page_template', '_wpb_vc_js_status'), true)) {
        update_post_meta($home_id, $key, wp_slash((string) $wp->meta_value));
    }
}
update_post_meta($home_id, '_custom_metabox_theme_options', array('kuteshop_metabox_used_header' => 'style-12', 'metabox_enable_vertical_menu' => true, 'metabox_vertical_item_visible' => 8, 'metabox_vertical_menu_title' => 'دسته‌بندی لپتاپ‌ها', 'metabox_vertical_menu_button_all_text' => 'همه دسته‌بندی‌ها', 'metabox_vertical_menu_button_close_text' => 'بستن'));
update_post_meta($home_id, '_custom_page_side_options', array('sidebar_page_layout' => 'full', 'page_extra_class' => 'tlt-home'));

$footer = (string) $demo['Footer 12']->children('http://purl.org/rss/1.0/modules/content/')->encoded;
$footer = str_replace(array('text="KUTESHOP"', 'text="COMPANY"', 'text="ACCOUNT"', 'text="LET\'S SOCIALIZE"', 'text="SIGN UP FOR NEWSLETTER"', 'menu="menu-kuteshop"', 'menu="menu-company"', 'menu="menu-my-account"'), array('text="راهنمای خرید"', 'text="تاپ لپتاپ"', 'text="حساب شما"', 'text="در کنار شما"', 'text="مشاوره و پشتیبانی"', 'menu="tlt-help"', 'menu="tlt-legal"', 'menu="tlt-account"'), $footer);
$footer = preg_replace('/ google_fonts="[^"]*"/', ' use_theme_fonts="yes"', $footer);
$footer = preg_replace('/\[kuteshop_socials[^\]]*\]/', '[vc_column_text]<p>فروشگاه تخصصی لپتاپ.<br>مشاوره خرید نقدی و بررسی شرایط اقساط.</p>[/vc_column_text]', $footer);
$footer = preg_replace('/\[kuteshop_newsletter[^\]]*\]/', '[vc_column_text]<p><a href="tel:09103208705">۰۹۱۰۳۲۰۸۷۰۵</a><br><a href="https://eitaa.com/Toplaptopsir">کانال تاپ لپتاپ در ایتا</a></p>[/vc_column_text]', $footer);
$footer = preg_replace('/\[kuteshop_simpleseo[^\]]*\]/', '', $footer);
$footer = preg_replace('/<p style="font-size: 14px;.*?<\/p>/s', '<p style="text-align:center">تمامی حقوق این فروشگاه متعلق به تاپ لپتاپ است.</p>', $footer);
$footer_id = (int) get_option('tlt_footer_id');
$footer_id = wp_insert_post(wp_slash(array('ID' => $footer_id, 'post_type' => 'footer', 'post_status' => 'publish', 'post_title' => 'فوتر تاپ لپتاپ — دمو ۱۲', 'post_content' => $footer)));
update_option('tlt_footer_id', $footer_id);
update_post_meta($footer_id, '_custom_footer_options', array('kuteshop_footer_style' => 'style-06'));
update_post_meta($footer_id, '_wpb_vc_js_status', 'true');
foreach ($demo['Footer 12']->children('http://wordpress.org/export/1.2/')->postmeta as $meta) {
    $wp = $meta->children('http://wordpress.org/export/1.2/');
    if ((string) $wp->meta_key === '_Ovic_VC_Shortcode_Custom_Css') {
        update_post_meta($footer_id, '_Ovic_VC_Shortcode_Custom_Css', wp_slash((string) $wp->meta_value));
    }
}
$options = get_option('_cs_options', array());
$options['kuteshop_used_header'] = 'style-12';
$options['kuteshop_footer_options'] = $footer_id;
$options['kuteshop_main_color'] = '#0f766e';
$options['enable_vertical_menu'] = true;
$options['block_vertical_menu'] = array((string) $home_id);
$options['vertical_item_visible'] = 8;
$options['vertical_menu_title'] = 'دسته‌بندی لپتاپ‌ها';
$options['vertical_menu_button_all_text'] = 'همه دسته‌بندی‌ها';
$options['header_social'] = '';
$options['user_all_social'] = array();
$options['body_typography'] = array('font-family' => 'Tahoma', 'font-size' => '15px', 'line-height' => '1.8', 'font-weight' => '400');
$options['sidebar_shop_page_position'] = 'left';
$options['shop_page_sidebar'] = 'shop-widget-area';
$options['kuteshop_shop_product_style'] = '8';
$options['kuteshop_custom_css'] = <<<'CSS'
:root{--tlt-primary:#0f766e;--tlt-ink:#172b42;--tlt-surface:#f0fdfa}body,h1,h2,h3,h4,h5,h6,input,select,textarea,button,.kuteshop-tabs,.main-menu{font-family:Tahoma,Arial,sans-serif!important}body{color:var(--tlt-ink);line-height:1.8}a:focus-visible,button:focus-visible,input:focus-visible,select:focus-visible{outline:3px solid #0369a1;outline-offset:3px}.tlt-hero{display:flex;align-items:center;justify-content:space-between;gap:24px;min-height:340px;background:var(--tlt-surface);padding:28px;border-radius:8px;margin:0}.tlt-hero h1{font-size:30px;line-height:1.6;color:var(--tlt-ink);margin:12px 0}.tlt-hero p{font-size:15px}.tlt-eyebrow{font-size:13px;color:var(--tlt-primary);font-weight:bold}.tlt-button{display:inline-block;min-height:44px;padding:10px 18px;background:var(--tlt-primary);color:#fff!important;border-radius:5px;margin:8px 0 0 12px}.tlt-secondary{display:inline-block;padding:10px 0;min-height:44px;color:var(--tlt-primary);font-weight:bold}.tlt-hero-art{width:46%;flex-shrink:0;text-align:center}.tlt-hero-art span{display:block;font-size:11px;color:#526579}.tlt-promo{display:flex;min-height:156px;flex-direction:column;justify-content:center;background:#f4f7fa;color:var(--tlt-ink);padding:22px;border:1px solid #dde6ed;border-radius:5px;margin-bottom:16px}.tlt-promo strong{font-size:19px}.tlt-promo span{font-size:14px;margin:5px 0}.tlt-promo b{font-size:13px;color:var(--tlt-primary)}.tlt-promo:hover{border-color:var(--tlt-primary);color:var(--tlt-ink)}.price{color:var(--tlt-primary)!important}.product-name{line-height:1.9!important}.header.style12 .main-menu>li>a{padding-right:13px;padding-left:13px}.header.style12 .logo img{max-width:205px;max-height:68px}.tlt-home .kuteshop-category.style3{min-height:170px}.block-footer{margin-top:32px}.header.style12 .header-top,.header.style12 .header-middle,.header.style12 .header-nav{background:#fff;color:var(--tlt-ink)}.header.style12 .header-top a,.header.style12 .header-middle a,.header.style12 .header-nav .main-menu>li>a{color:var(--tlt-ink)}.header.style12 .vertical-wapper .block-title{background:var(--tlt-primary);color:#fff}.header.style12 .header-middle{border-bottom:1px solid #dde6ed}.tlt-inquiry-price{font-size:14px;line-height:1.9}.tlt-finance-card{max-width:720px;padding:24px;background:#f0fdfa;border:1px solid #dde6ed;border-radius:8px;margin:24px 0}.tlt-finance-card label{display:block;margin:16px 0 6px;font-weight:bold}.tlt-finance-card input,.tlt-finance-card select{width:100%;height:48px;background:#fff;border:1px solid #94a3b8;border-radius:4px;padding:8px 12px;font-size:16px}.tlt-finance-card output{display:block;background:#fff;padding:16px;margin-top:20px;border-radius:4px}.tlt-finance-card output p{margin:4px 0}.tlt-finance-table{overflow:auto}.tlt-finance-table th,.tlt-finance-table td{padding:12px;border:1px solid #dde6ed;min-width:120px;text-align:right}.tlt-finance-table table{width:100%}@media(max-width:767px){.tlt-hero{padding:20px;min-height:0;gap:12px;flex-direction:column}.tlt-hero h1{font-size:26px}.tlt-hero-art{width:75%;max-width:280px}.tlt-promo{min-height:124px;padding:18px}.tlt-home .revolution-custom-15{display:none}.header.style12 .logo img{max-width:160px}.tlt-home .kuteshop-tabs .tab-head{overflow:auto}.tlt-home .vc_column_container{max-width:100%}}@media(prefers-reduced-motion:reduce){html{scroll-behavior:auto!important}*,*::before,*::after{animation-duration:.01ms!important;transition-duration:.01ms!important}}
CSS;
update_option('_cs_options', $options);
update_option('show_on_front', 'page');
update_option('page_on_front', $home_id);
update_option('wp_page_for_privacy_policy', 29);
update_option('woocommerce_terms_page_id', 28);
foreach (array(5 => 'فروشگاه لپتاپ', 6 => 'سبد خرید', 7 => 'تسویه حساب', 8 => 'حساب کاربری') as $id => $title) {
    wp_update_post(array('ID' => $id, 'post_title' => $title));
}
update_option('woocommerce_currency', 'IRT');
update_option('woocommerce_price_num_decimals', 0);
update_option('woocommerce_hide_out_of_stock_items', 'yes');
update_option('permalink_structure', '/%postname%/');
flush_rewrite_rules();
WP_CLI::success('Home 12 adapted: homepage ' . $home_id . ', footer ' . $footer_id . '; no demo products or orders imported.');
