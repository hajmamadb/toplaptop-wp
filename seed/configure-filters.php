<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$settings = get_option('woof_settings', array());
$widgets = get_option('widget_woof_widget', array());
$sidebars = get_option('sidebars_widgets', array());
$options = get_option('_cs_options', array());
$taxonomies = array('product_cat', 'pa_cpu', 'pa_ram', 'pa_storage', 'pa_gpu', 'pa_display', 'pa_status');
foreach ($taxonomies as $tax) {
    if (!taxonomy_exists($tax)) { WP_CLI::error('Missing taxonomy: ' . $tax); }
}
$settings = array_replace_recursive($settings, array('by_price' => array('show' => 3), 'tax_type' => array_fill_keys($taxonomies, 'checkbox'), 'show_title_label' => array_fill_keys($taxonomies, 1)));
$settings['tax'] = array_fill_keys($taxonomies, 1);
$settings['custom_tax_label'] = array('product_cat' => 'دسته‌بندی', 'pa_cpu' => 'پردازنده', 'pa_ram' => 'حافظه رم', 'pa_storage' => 'حافظه داخلی', 'pa_gpu' => 'کارت گرافیک', 'pa_display' => 'نمایشگر', 'pa_status' => 'وضعیت دستگاه');
$settings['cache_terms'] = 0;
$settings['cache_count_data'] = 0;
$products = wc_get_products(array('limit' => -1, 'status' => 'publish', 'stock_status' => 'instock', 'visibility' => 'visible'));
$product_ids = array_map(fn($product) => $product->get_id(), $products);
foreach ($taxonomies as $tax) {
    $all = get_terms(array('taxonomy' => $tax, 'hide_empty' => false, 'fields' => 'ids'));
    $visible = wp_get_object_terms($product_ids, $tax, array('fields' => 'ids'));
    $settings['excluded_terms'][$tax] = implode(',', array_diff($all, $visible));
    $settings['excluded_terms_reverse'][$tax] = 0;
}
$index = (int) get_option('tlt_filter_widget_index');
if (!$index) {
    $used = array();
    foreach ($sidebars as $ids) { if (is_array($ids)) { $used = array_merge($used, $ids); } }
    $index = 2;
    while (isset($widgets[$index]) || in_array('woof_widget-' . $index, $used, true)) { ++$index; }
}
$widgets[$index] = array('title' => 'فیلتر پیشرفته لپتاپ', 'additional_text_before' => '', 'redirect' => '', 'woof_start_filtering_btn' => 0, 'ajax_redraw' => 0, 'dynamic_recount' => 1, 'btn_position' => 'b', 'mobile_mode' => 0, 'autosubmit' => 1);
$widgets['_multiwidget'] = 1;
$sidebars['shop-widget-area'] = array_values(array_unique(array_merge($sidebars['shop-widget-area'] ?? array(), array('woof_widget-' . $index))));
$options['sidebar_shop_page_position'] = 'left';
$options['shop_page_sidebar'] = 'shop-widget-area';
update_option('woof_settings', $settings);
update_option('widget_woof_widget', $widgets);
update_option('sidebars_widgets', $sidebars);
update_option('_cs_options', $options);
update_option('woof_reset_btn_txt', 'پاک کردن فیلترها');
update_option('woof_try_ajax', 0);
update_option('woof_show_count', 0);
update_option('woof_show_count_dynamic', 1);
update_option('woof_hide_dynamic_empty_pos', 1);
update_option('tlt_filter_widget_index', $index, false);
WP_CLI::success('HUSKY price/category/specification filters attached to shop sidebar.');
