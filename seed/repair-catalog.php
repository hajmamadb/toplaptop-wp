<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$labels = array('cpu' => 'پردازنده', 'ram' => 'حافظه رم', 'storage' => 'حافظه داخلی', 'gpu' => 'کارت گرافیک', 'display' => 'نمایشگر', 'status' => 'وضعیت دستگاه');
$attribute_ids = array();
foreach ($labels as $slug => $label) {
    $id = wc_attribute_taxonomy_id_by_name($slug);
    if (!$id) {
        $id = wc_create_attribute(array('name' => $label, 'slug' => $slug, 'type' => 'select', 'order_by' => 'menu_order', 'has_archives' => true));
    }
    if (is_wp_error($id)) { WP_CLI::error($id->get_error_message()); }
    $attribute_ids[$slug] = $id;
    if (!taxonomy_exists('pa_' . $slug)) {
        register_taxonomy('pa_' . $slug, 'product', array('hierarchical' => false, 'label' => $label, 'show_ui' => true, 'public' => true));
    }
}
$catalog = array(
    'TLT-G13420-DEAL' => array('source' => 'Toplaptopsir/6061', 'name' => 'لپتاپ گیمینگ Intel Core i5-13420H / RTX 3050 6GB', 'regular' => '241500000', 'sale' => '234500000', 'sale_end' => '2026-11-18T10:05:14+00:00', 'cpu' => 'Intel Core i5-13420H', 'ram' => '16GB DDR4', 'storage' => '512GB SSD NVMe', 'gpu' => 'NVIDIA GeForce RTX 3050 6GB', 'display' => '15.6 اینچ Full HD 144Hz', 'status' => 'تست‌شده و تمیز؛ ۷ روز مهلت تست'),
    'TLT-U1335-TOUCH' => array('source' => 'Toplaptopsir/6062', 'name' => 'لپتاپ ۱۴ اینچ لمسی Intel Core i5-1335U', 'regular' => '140000000', 'sale' => '130000000', 'sale_end' => '2026-11-18T10:06:12+00:00', 'cpu' => 'Intel Core i5-1335U', 'ram' => '16GB DDR4', 'storage' => '256GB SSD NVMe', 'gpu' => 'Intel Iris Xe', 'display' => '14 اینچ Full HD لمسی (Touch)', 'status' => 'تست‌شده و تمیز؛ ۷ روز مهلت تست'),
    'TLT-ASUS-VIVO14' => array('source' => 'Toplaptopsir/6044', 'name' => 'لپتاپ Asus VivoBook 14 – Intel Core i3', 'cpu' => 'Intel Core i3-10110U', 'ram' => '8GB', 'storage' => '256GB SSD', 'gpu' => 'Intel UHD Graphics', 'display' => '14 اینچ Full HD', 'status' => 'نیازمند استعلام وضعیت'),
    'TLT-VIC-R7' => array('source' => 'Toplaptopsir/6049', 'name' => 'لپتاپ HP Victus 15 – AMD Ryzen 7 7740HS / RTX 2050', 'cpu' => 'AMD Ryzen 7 7740HS', 'ram' => '16GB DDR5', 'storage' => '512GB SSD NVMe', 'gpu' => 'NVIDIA GeForce RTX 2050 4GB', 'display' => '15.6 اینچ Full HD 144Hz', 'status' => 'نیازمند استعلام وضعیت'),
    'TLT-DEL-E7250' => array('source' => 'Toplaptopsir/6054؛ اتمام موجودی: Toplaptopsir/6059', 'name' => 'لپتاپ Dell Latitude E7250 – Core i5 / 8GB / 256GB SSD', 'cpu' => 'Intel Core i5-5300U', 'ram' => '8GB', 'storage' => '256GB SSD', 'gpu' => 'Intel HD Graphics 5500', 'display' => '12.5 اینچ HD', 'status' => 'ناموجود'),
    'TLT-VIC-I513' => array('source' => 'Toplaptopsir/6040', 'name' => 'لپتاپ HP Victus 15 – Core i5-13420H / RTX 3050 6GB', 'cpu' => 'Intel Core i5-13420H', 'ram' => '16GB DDR4', 'storage' => '512GB SSD NVMe', 'gpu' => 'NVIDIA GeForce RTX 3050 6GB', 'display' => '15.6 اینچ Full HD 144Hz', 'status' => 'نیازمند استعلام وضعیت'),
    'TLT-SURFACE-6' => array('source' => 'Toplaptopsir/6018', 'name' => 'Microsoft Surface Laptop 6 – Core Ultra 7 / نمایشگر لمسی', 'cpu' => 'Intel Core Ultra 7-165H', 'ram' => '16GB DDR5', 'storage' => '256GB SSD', 'gpu' => 'Intel Iris Xe؛ مطابق اعلام کانال', 'display' => '13.5 اینچ 2K لمسی؛ مطابق اعلام کانال', 'status' => 'نیازمند استعلام وضعیت'),
    'TLT-VIC-HX14' => array('source' => 'Toplaptopsir/6015؛ کانفیگ ۱', 'name' => 'لپتاپ HP Victus – Core i5-14450HX / RTX 3050 6GB', 'cpu' => 'Intel Core i5-14450HX', 'ram' => '16GB DDR5', 'storage' => '512GB SSD NVMe', 'gpu' => 'NVIDIA GeForce RTX 3050 6GB', 'display' => '15.6 اینچ Full HD 144Hz', 'status' => 'نیازمند استعلام وضعیت'),
);
$products = array();
foreach ($catalog as $sku => $values) {
    $products[$sku] = wc_get_product(wc_get_product_id_by_sku($sku));
    if (!$products[$sku]) { WP_CLI::error('Missing catalog SKU: ' . $sku); }
}
foreach ($catalog as $sku => $item) {
    $product = $products[$sku];
    $id = $product->get_id();
    if (!get_post_meta($id, '_tlt_before_catalog_reconciliation', true)) {
        update_post_meta($id, '_tlt_before_catalog_reconciliation', $product->get_data());
    }
    $values = array_intersect_key($item, $labels);
    $attributes = array();
    foreach ($values as $slug => $value) {
        $term = term_exists($value, 'pa_' . $slug);
        if (!$term) { $term = wp_insert_term($value, 'pa_' . $slug); }
        if (is_wp_error($term)) { WP_CLI::error($term->get_error_message()); }
        $attribute = new WC_Product_Attribute();
        $attribute->set_id($attribute_ids[$slug]);
        $attribute->set_name('pa_' . $slug);
        $attribute->set_options(array((int) $term['term_id']));
        $attribute->set_visible(true);
        $attribute->set_variation(false);
        $attributes[] = $attribute;
    }
    $product->set_attributes($attributes);
    $product->set_name($item['name']);
    $product->set_manage_stock(false);
    $product->set_stock_quantity(null);
    $product->set_regular_price($item['regular'] ?? '');
    $product->set_sale_price($item['sale'] ?? '');
    $product->set_date_on_sale_from(null);
    $product->set_date_on_sale_to($item['sale_end'] ?? null);
    $product->set_stock_status($sku === 'TLT-DEL-E7250' ? 'outofstock' : 'instock');
    $product->set_catalog_visibility($sku === 'TLT-DEL-E7250' ? 'hidden' : 'visible');
    $categories = $product->get_category_ids();
    foreach ($categories as $index => $category_id) {
        $term = get_term($category_id, 'product_cat');
        if ($term && !is_wp_error($term) && in_array($term->name, array('آکبند', 'استوک (تست‌شده)'), true) && !isset($item['regular'])) {
            unset($categories[$index]);
        }
    }
    $product->set_category_ids(array_values($categories));
    $short = '<p>مشخصات مطابق اعلام کانال؛ قبل از خرید، وضعیت دستگاه و موجودی نهایی با پشتیبانی تأیید شود.</p>';
    if (!isset($item['regular']) && $sku !== 'TLT-DEL-E7250') {
        $short .= '<p>قیمت روز اعلام نشده است؛ برای استعلام تماس بگیرید.</p>';
    }
    if (isset($item['regular'])) {
        $short .= '<p>دستگاه تست‌شده و تمیز با ۷ روز مهلت تست طبق اعلام کانال. قیمت ویژه فقط تا پایان بازهٔ ۲۴ ساعتهٔ اعلام‌شده معتبر است.</p>';
    }
    if ($sku === 'TLT-DEL-E7250') {
        $short = '<p>اتمام موجودی این دستگاه در پیام ۶۰۵۹ کانال اعلام شده است.</p>';
    }
    $product->set_short_description($short);
    $product->set_description($short . '<p>مشخصات در زبانهٔ اطلاعات تکمیلی درج شده‌اند. هزینه ارسال، جزئیات ضمانت و شرایط نهایی اقساط پیش از پرداخت مشخص می‌شوند. برای هماهنگی با شمارهٔ ۰۹۱۰۳۲۰۸۷۰۵ تماس بگیرید.</p>');
    $product->update_meta_data('_tlt_source_post', $item['source']);
    $product->save();
    $image_ids = array_unique(array_filter(array_merge(array($product->get_image_id()), $product->get_gallery_image_ids())));
    foreach ($image_ids as $image_id) {
        if (!get_post_meta($image_id, '_tlt_before_image_reconciliation', true)) {
            update_post_meta($image_id, '_tlt_before_image_reconciliation', array('title' => get_post($image_id)->post_title, 'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true)));
        }
        wp_update_post(array('ID' => $image_id, 'post_title' => $item['name']));
        update_post_meta($image_id, '_wp_attachment_image_alt', 'تصویر منتشرشده در کانال برای ' . $item['name']);
    }
    WP_CLI::log('Product ' . $id . ': ' . count($attributes) . ' attributes, price ' . ($product->get_price() ?: 'inquiry'));
}
delete_transient('wc_attribute_taxonomies');
update_option('timezone_string', 'Asia/Tehran');
flush_rewrite_rules();
WP_CLI::success('Catalog specifications repaired; unverified prices removed; sold-out Dell hidden.');
