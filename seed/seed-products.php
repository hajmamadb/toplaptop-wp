<?php
/**
 * Seed script for تاپ لپتاپ store.
 * Run with: wp eval-file seed/seed-products.php
 * Creates global product attributes, product categories, and the active product catalog.
 */

// --- 1. Global product attributes (drives filters + comparison) ---
$attrs = array(
    'cpu'     => array('label' => 'پردازنده',          'name' => 'پردازنده'),
    'ram'     => array('label' => 'حافظه رم',          'name' => 'حافظه رم'),
    'storage' => array('label' => 'حافظه داخلی',       'name' => 'حافظه داخلی'),
    'gpu'     => array('label' => 'کارت گرافیک',      'name' => 'کارت گرافیک'),
    'display' => array('label' => 'نمایشگر',          'name' => 'نمایشگر'),
    'status'  => array('label' => 'وضعیت دستگاه',     'name' => 'وضعیت دستگاه'),
);
$taxonomies = array();
foreach ($attrs as $slug => $meta) {
    if (!taxonomy_exists('pa_' . $slug)) {
        register_taxonomy('pa_' . $slug, 'product', array(
            'labels'            => array('name' => $meta['label'], 'singular_name' => $meta['label']),
            'hierarchical'      => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
        ));
        $taxonomies['pa_' . $slug] = array(
            'name'   => $meta['label'],
            'type'   => 'select',
            'order'  => count($taxonomies) + 1,
            'has_archives' => 1,
        );
    }
}
update_option('woocommerce_attribute_taxonomies', $taxonomies);
flush_rewrite_rules();

// helper: ensure a term exists and return its id
function tlt_term($taxonomy, $name) {
    $t = term_exists($name, $taxonomy);
    if (is_array($t)) return $t['term_id'];
    $r = wp_insert_term($name, $taxonomy, array('slug' => sanitize_title($name)));
    return is_wp_error($r) ? 0 : $r['term_id'];
}

// --- 2. Product categories ---
$cats = array(
    'gaming'  => 'لپتاپ گیمینگ',
    'office'  => 'اداری و دانشجویی',
    'stock'   => 'استوک (تست‌شده)',
    'new'     => 'آکبند',
);
$cat_ids = array();
foreach ($cats as $key => $label) {
    $cat_ids[$key] = tlt_term('product_cat', $label);
}

// --- 3. Products ---
$common = '
<p><strong>شرایط خرید تاپ لپتاپ:</strong></p>
<ul>
  <li>امکان خرید <strong>نقدی و اقساطی</strong> (اقساط با پیش‌پرداخت یک‌سوم)</li>
  <li>گارانتی سلامت سخت‌افزار + <strong>۷ روز مهلت تست واقعی</strong></li>
  <li>ارسال به سراسر کشور</li>
  <li>مشاوره رایگان قبل از خرید: <strong>09103208705</strong> / <strong>@TLT_support</strong></li>
</ul>
<p>برای مشاوره و رزرو، با پشتیبانی تاپ لپتاپ در ارتباط باشید.</p>';

$products = array(
    array(
        'name' => 'لپتاپ گیمینگ Intel Core i5-13420H / RTX 3050 (استوک تست‌شده)',
        'sku'  => 'TLT-G13420',
        'reg'  => '241500000',
        'sale' => '234500000',
        'cats' => array('gaming', 'stock'),
        'stock'=> 1,
        'attrs'=> array('cpu'=>'Intel Core i5-13420H','ram'=>'16GB DDR4','storage'=>'512GB SSD NVMe','gpu'=>'NVIDIA GeForce RTX 3050 6GB','display'=>'15.6 اینچ Full HD 144Hz','status'=>'استوک / تست‌شده'),
        'short'=>'لپتاپ گیمینگ و کاری با پردازنده Core i5-13420H، گرافیک RTX 3050 6GB و نمایشگر ۱۴۴ هرتز؛ ایده‌آل برای بازی و کارهای سنگین. تست‌شده با ۷ روز مهلت تست.',
    ),
    array(
        'name' => 'لپتاپ ۱۴ اینچ لمسی Intel Core i5-1335U (استوک تست‌شده)',
        'sku'  => 'TLT-U1335',
        'reg'  => '140000000',
        'sale' => '130000000',
        'cats' => array('office', 'stock'),
        'stock'=> 1,
        'attrs'=> array('cpu'=>'Intel Core i5-1335U','ram'=>'16GB DDR4','storage'=>'256GB SSD NVMe','gpu'=>'Intel Iris Xe','display'=>'14 اینچ Full HD لمسی (Touch)','status'=>'استوک / تست‌شده'),
        'short'=>'لپتاپ سبک با صفحه نمایش لمسی، مناسب کار روزمره، اداری و دانشجویی. فوق‌العاده تمیز و تست‌شده با ۷ روز مهلت تست.',
    ),
    array(
        'name' => 'لپتاپ Asus VivoBook 14 - Intel Core i3 (آکبند)',
        'sku'  => 'TLT-ASV14',
        'reg'  => '52000000',
        'sale' => '',
        'cats' => array('office', 'new'),
        'stock'=> 5,
        'attrs'=> array('cpu'=>'Intel Core i3-10110U','ram'=>'8GB','storage'=>'256GB SSD','gpu'=>'Intel UHD Graphics','display'=>'14 اینچ Full HD','status'=>'آکبند'),
        'short'=>'سبک، شیک و اقتصادی؛ ایده‌آل برای کارهای روزمره، اداری و دانشجویی. به همراه کیف هدیه.',
    ),
    array(
        'name' => 'لپتاپ گیمینگ HP Victus 15 - Ryzen 7 / RTX 2050',
        'sku'  => 'TLT-HPV15',
        'reg'  => '118000000',
        'sale' => '',
        'cats' => array('gaming'),
        'stock'=> 3,
        'attrs'=> array('cpu'=>'AMD Ryzen 7 7740HS','ram'=>'16GB DDR5','storage'=>'512GB SSD NVMe','gpu'=>'NVIDIA GeForce RTX 2050 4GB','display'=>'15.6 اینچ Full HD 144Hz','status'=>'موجود'),
        'short'=>'دستگاه گیمینگ و مهندسی سری رایزن با پردازنده پرقدرت سری HS و نمایشگر ۱۴۴ هرتز. سیستم خنک‌کننده دو فن سری Victus.',
    ),
    array(
        'name' => 'لپتاپ Dell Latitude E7250 - Intel Core i5 (استوک)',
        'sku'  => 'TLT-DLE7250',
        'reg'  => '30000000',
        'sale' => '',
        'cats' => array('office', 'stock'),
        'stock'=> 0,
        'attrs'=> array('cpu'=>'Intel Core i5-5300U','ram'=>'8GB','storage'=>'256GB SSD','gpu'=>'Intel HD Graphics 5500','display'=>'12.5 اینچ HD','status'=>'استوک / تست‌شده'),
        'short'=>'سبک، جمع‌وجور و خوش‌قیمت؛ انتخابی عالی برای ترید، امور روزمره و دانشجویی. بدنه مستحکم سری تجاری Latitude.',
    ),
);

$created = 0;
foreach ($products as $p) {
    // skip if SKU already exists
    $existing = wc_get_product_id_by_sku($p['sku']);
    if ($existing) {
        wp_die('SKU ' . $p['sku'] . ' already exists (id ' . $existing . '). Re-run blocked to avoid duplicates.');
    }
    $prod = new WC_Product_Simple();
    $prod->set_name($p['name']);
    $prod->set_sku($p['sku']);
    $prod->set_catalog_visibility('visible');
    $prod->set_status('publish');
    $prod->set_regular_price($p['reg']);
    if (!empty($p['sale'])) $prod->set_sale_price($p['sale']);
    $prod->set_short_description($p['short']);
    $prod->set_description($p['short'] . $common);
    $cid = array();
    foreach ($p['cats'] as $k) $cid[] = $cat_ids[$k];
    $prod->set_category_ids($cid);

    // attributes
    $att = array();
    foreach ($p['attrs'] as $slug => $val) {
        $term_id = tlt_term('pa_' . $slug, $val);
        $att[] = array('name' => $slug, 'option' => $val, 'value' => (string) $term_id);
    }
    $prod->set_attributes($att);

    // stock
    $prod->set_manage_stock(true);
    $prod->set_stock_quantity((int) $p['stock']);
    $prod->set_backorders('no');
    $prod->set_sold_individually(false);

    $prod->save();
    $created++;
    echo "Created: {$p['name']} (id {$prod->get_id()})\n";
}

// --- 4. Default shop/campaign pages already exist from WooCommerce. Ensure a 'sale' tag term ---
echo "Seeding done. Products created: $created\n";
