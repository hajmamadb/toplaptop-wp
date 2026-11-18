<?php
/**
 * Import real products and photos from Eitaa channel into WooCommerce.
 * Also adds Avada Ecommerce styling for Shop, Single Product, and Archive pages.
 */

// Helper to attach image to media library
function tlt_import_image($local_rel_path, $title) {
    $full_path = 'D:/تاپلپتاپ2/' . str_replace('\\', '/', $local_rel_path);
    if (!file_exists($full_path)) {
        echo "Image file not found: $full_path\n";
        return 0;
    }
    
    // Check if attachment with same file already exists
    $filename = basename($full_path);
    $existing = get_posts(array(
        'post_type'   => 'attachment',
        'meta_key'    => '_wp_attached_file',
        'meta_value'  => 'eitaa/' . $filename,
        'posts_per_page' => 1,
    ));
    if (!empty($existing)) {
        return $existing[0]->ID;
    }

    $uploads = wp_upload_dir();
    $target_dir = $uploads['basedir'] . '/eitaa';
    if (!is_dir($target_dir)) wp_mkdir_p($target_dir);
    $target_file = $target_dir . '/' . $filename;
    copy($full_path, $target_file);

    $attachment = array(
        'guid'           => $uploads['baseurl'] . '/eitaa/' . $filename,
        'post_mime_type' => 'image/jpeg',
        'post_title'     => $title,
        'post_content'   => '',
        'post_status'    => 'inherit'
    );
    $attach_id = wp_insert_attachment($attachment, $target_file);
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $target_file);
    wp_update_attachment_metadata($attach_id, $attach_data);
    update_post_meta($attach_id, '_wp_attached_file', 'eitaa/' . $filename);
    update_post_meta($attach_id, '_wp_attachment_image_alt', $title);
    return $attach_id;
}

// 1. Ensure Categories & Tags
function tlt_term($tax, $name, $slug = '') {
    $t = term_exists($name, $tax);
    if (is_array($t)) return (int)$t['term_id'];
    $r = wp_insert_term($name, $tax, array('slug' => $slug ?: sanitize_title($name)));
    return is_wp_error($r) ? 0 : (int)$r['term_id'];
}

$cats = array(
    'gaming'  => tlt_term('product_cat', 'لپتاپ گیمینگ', 'gaming'),
    'office'  => tlt_term('product_cat', 'اداری و دانشجویی', 'office'),
    'stock'   => tlt_term('product_cat', 'استوک (تست‌شده)', 'stock'),
    'new'     => tlt_term('product_cat', 'آکبند', 'brand-new'),
);

$tags_map = array();
$tag_names = array('لپ تاپ HP', 'لپ تاپ Dell', 'لپ تاپ Asus', 'سرفیس مایکروسافت', 'RTX 3050', 'RTX 2050', 'Core i5', 'Core i3', 'Core Ultra 7', 'Ryzen 7', 'لپ تاپ گیمینگ', 'لپ تاپ لمسی', 'استوک اروپایی', 'بلک فرایدی');
foreach ($tag_names as $tn) {
    $tags_map[$tn] = tlt_term('product_tag', $tn);
}

// 2. Real Products Catalog Data from Channel
$catalog = array(
    array(
        'sku'      => 'TLT-VIC-R7',
        'name'     => 'لپتاپ گیمینگ HP Victus 15 - AMD Ryzen 7 7740HS / RTX 2050',
        'reg_price'=> '179000000',
        'sale_price'=>'172000000',
        'img_file' => 'eitaa_images/eitaa_Toplaptopsir_6049.jpg',
        'cats'     => array($cats['gaming'], $cats['stock']),
        'tags'     => array('لپ تاپ HP', 'RTX 2050', 'Ryzen 7', 'لپ تاپ گیمینگ', 'استوک اروپایی'),
        'cpu'      => 'AMD Ryzen 7 7740HS (سری پرقدرت HS)',
        'ram'      => '16GB DDR5',
        'storage'  => '512GB SSD NVMe',
        'gpu'      => 'Nvidia GeForce RTX 2050 (4GB GDDR6)',
        'display'  => '15.6 اینچ Full HD (144Hz)',
        'status'   => 'استوک گرید A++ همراه با مهلت تست',
        'gift'     => 'کیف ضربه‌گیر هدیه',
        'short'    => 'لپتاپ گیمینگ و مهندسی پرقدرت با پردازنده سری HS، حافظه سریع DDR5، کارت گرافیک RTX و نمایشگر ۱۴۴ هرتز با سیستم خنک‌کننده دو فن Victus. همراه با ۷ روز مهلت تست واقعی.',
    ),
    array(
        'sku'      => 'TLT-DEL-E7250',
        'name'     => 'لپتاپ Dell Latitude E7250 - Core i5 / 8GB / 256GB SSD',
        'reg_price'=> '39500000',
        'sale_price'=>'37500000',
        'img_file' => 'eitaa_images/eitaa_Toplaptopsir_6054.jpg',
        'cats'     => array($cats['office'], $cats['stock']),
        'tags'     => array('لپ تاپ Dell', 'Core i5', 'استوک اروپایی'),
        'cpu'      => 'Intel Core i5 5300U (سری کم‌مصرف U)',
        'ram'      => '8GB',
        'storage'  => '256GB SSD',
        'gpu'      => 'Intel HD Graphics 5500',
        'display'  => '12.5 اینچ HD مات ضد انعکاس',
        'status'   => 'استوک بسیار تمیز / تست‌شده',
        'gift'     => 'کیف ضربه‌گیر هدیه',
        'short'    => 'فوق‌العاده سبک، جمع‌وجور با بدنه مستحکم سری تجاری Latitude؛ انتخابی بی‌نظیر برای ترید، امور دانشجویی، وب‌گردی و اداری همراه با کیف هدیه و ضمانت سلامت.',
    ),
    array(
        'sku'      => 'TLT-G13420-DEAL',
        'name'     => 'لپتاپ گیمینگ نسل ۱۳ Intel Core i5-13420H / RTX 3050 (سه‌شنبه تخفیفی)',
        'reg_price'=> '241500000',
        'sale_price'=>'234500000',
        'img_file' => 'eitaa_images/eitaa_Toplaptopsir_6061.jpg',
        'cats'     => array($cats['gaming'], $cats['stock']),
        'tags'     => array('لپ تاپ HP', 'RTX 3050', 'Core i5', 'لپ تاپ گیمینگ', 'بلک فرایدی'),
        'cpu'      => 'Intel Core i5 13420H (سری قدرتمند نسل ۱۳)',
        'ram'      => '16GB DDR4',
        'storage'  => '512GB SSD NVMe پرسرعت',
        'gpu'      => 'NVIDIA GeForce RTX 3050 (6GB GDDR6 اختصاصی)',
        'display'  => '15.6 اینچ Full HD با نرخ نوسازی 144Hz',
        'status'   => 'تست‌شده و تمیز با ۷ روز مهلت تست واقعی',
        'gift'     => '۷,۰۰۰,۰۰۰ تومان تخفیف مستقیم + کیف هدیه',
        'short'    => 'پیشنهاد اول سه‌شنبه‌های تخفیفی تاپ لپتاپ؛ غول گیمینگ و طراحی مجهز به گرافیک ۶ گیگابایت RTX 3050 و پردازنده سری H با ۷ میلیون تومان تخفیف مستقیم.',
    ),
    array(
        'sku'      => 'TLT-U1335-TOUCH',
        'name'     => 'لپتاپ ۱۴ اینچ لمسی Intel Core i5-1335U (سه‌شنبه تخفیفی)',
        'reg_price'=> '140000000',
        'sale_price'=>'130000000',
        'img_file' => 'eitaa_images/eitaa_Toplaptopsir_6062.jpg',
        'cats'     => array($cats['office'], $cats['stock']),
        'tags'     => array('Core i5', 'لپ تاپ لمسی', 'استوک اروپایی', 'بلک فرایدی'),
        'cpu'      => 'Intel Core i5 1335U (۱۰ هسته‌ای نسل ۱۳)',
        'ram'      => '16GB DDR4',
        'storage'  => '256GB SSD NVMe',
        'gpu'      => 'Intel Iris Xe Graphics (مشابه ۲ گیگ)',
        'display'  => '14 اینچ Full HD لمسی (TouchScreen)',
        'status'   => 'فوق‌العاده تمیز همراه ۷ روز مهلت تست',
        'gift'     => '۱۰,۰۰۰,۰۰۰ تومان تخفیف مستقیم + کیف هدیه',
        'short'    => 'پیشنهاد دوم سه‌شنبه‌های تخفیفی تاپ لپتاپ؛ لپ‌تاپ سبک و مدرن لمسی با ۱۰ میلیون تومان سود خرید مستقیم، ایده‌آل برای جلسات، تدریس، اداری و ترید.',
    ),
    array(
        'sku'      => 'TLT-VIC-I513',
        'name'     => 'لپتاپ گیمینگ HP Victus 15 - Core i5 13420H / RTX 3050 6GB',
        'reg_price'=> '220000000',
        'sale_price'=>'215000000',
        'img_file' => 'eitaa_images/eitaa_Toplaptopsir_6040.jpg',
        'cats'     => array($cats['gaming'], $cats['stock']),
        'tags'     => array('لپ تاپ HP', 'RTX 3050', 'Core i5', 'لپ تاپ گیمینگ'),
        'cpu'      => 'Intel Core i5 13420H',
        'ram'      => '16GB DDR4',
        'storage'  => '512GB SSD NVMe',
        'gpu'      => 'Nvidia GeForce RTX 3050 (6GB GDDR6)',
        'display'  => '15.6 اینچ Full HD 144Hz',
        'status'   => 'استوک تمیز با ضمانت سلامت',
        'gift'     => 'کیف هدیه',
        'short'    => 'دستگاه گیمینگ و مهندسی نسل ۱۳ با پردازنده سری H و گرافیک ۶ گیگابایتی RTX 3050، ایده‌آل برای رندرینگ و گیمینگ روز.',
    ),
    array(
        'sku'      => 'TLT-ASUS-VIVO14',
        'name'     => 'لپتاپ Asus VivoBook 14 - Intel Core i3 (آکبند شرکتی)',
        'reg_price'=> '54000000',
        'sale_price'=>'52000000',
        'img_file' => 'eitaa_images/eitaa_Toplaptopsir_6044.jpg',
        'cats'     => array($cats['office'], $cats['new']),
        'tags'     => array('لپ تاپ Asus', 'Core i3'),
        'cpu'      => 'Intel Core i3 10110U',
        'ram'      => '8GB',
        'storage'  => '256GB SSD',
        'gpu'      => 'Intel UHD Graphics',
        'display'  => '14 اینچ Full HD',
        'status'   => 'آکبند پلمپ با گارانتی معتبر شرکتی',
        'gift'     => 'کیف هدیه + ماوس',
        'short'    => 'شیک، سبک و اقتصادی با وزن مناسب و شارژدهی عالی؛ مناسب برای امور اداری، دانش‌آموزی و کارهای روزمره.',
    ),
    array(
        'sku'      => 'TLT-SURFACE-6',
        'name'     => 'مایکروسافت Surface Laptop 6 - Intel Core Ultra 7 / 2K Touch',
        'reg_price'=> '305000000',
        'sale_price'=>'295000000',
        'img_file' => 'eitaa_images/eitaa_Toplaptopsir_6018.jpg',
        'cats'     => array($cats['office'], $cats['stock']),
        'tags'     => array('سرفیس مایکروسافت', 'Core Ultra 7', 'لپ تاپ لمسی'),
        'cpu'      => 'Intel Core Ultra 7 165H (پردازنده مجهز به هوش مصنوعی)',
        'ram'      => '16GB DDR5',
        'storage'  => '256GB SSD NVMe',
        'gpu'      => 'Intel Iris Xe Graphics',
        'display'  => '13.5 اینچ 2K لمسی (PixelSense Touch)',
        'status'   => 'فوق‌العاده تمیز گرید Open Box با گارانتی سلامت',
        'gift'     => 'کیف هدیه چرمی',
        'short'    => 'شاهکار مهندسی مایکروسافت با پردازنده نسل جدید سری Ultra، مجهز به هوش مصنوعی، بدنه آلومینیومی فوق‌العاده باریک و نمایشگر لمسی خیره‌کننده 2K.',
    ),
    array(
        'sku'      => 'TLT-VIC-HX14',
        'name'     => 'لپتاپ گیمینگ HP Victus 15 - Intel Core i5 14450HX (آکبند نسل ۱۴)',
        'reg_price'=> '285000000',
        'sale_price'=>'275000000',
        'img_file' => 'eitaa_images/eitaa_Toplaptopsir_6015.jpg',
        'cats'     => array($cats['gaming'], $cats['new']),
        'tags'     => array('لپ تاپ HP', 'RTX 3050', 'Core i5', 'لپ تاپ گیمینگ'),
        'cpu'      => 'Intel Core i5 14450HX (نسل ۱۴ اینتل - ۱۶ ترد)',
        'ram'      => '16GB DDR5 پرسرعت',
        'storage'  => '512GB SSD NVMe',
        'gpu'      => 'Nvidia GeForce RTX 3050 (6GB GDDR6)',
        'display'  => '15.6 اینچ Full HD (144Hz IPS)',
        'status'   => 'آکبند شرکتی با گارانتی ۱۸ ماهه رسمی',
        'gift'     => 'پکیج گیمینگ (کیف + ماوس پد)',
        'short'    => 'جدیدترین کانفیگ HP Victus مجهز به پردازنده پرچمدار 14450HX و رم DDR5 و گرافیک RTX 3050 شش گیگابایت برای سنگین‌ترین پردازش‌های مهندسی و بازی‌ها.',
    ),
);

// 3. Insert / Update Products in WooCommerce
foreach ($catalog as $item) {
    // Check if product exists by SKU
    $existing_id = wc_get_product_id_by_sku($item['sku']);
    if ($existing_id) {
        $product = wc_get_product($existing_id);
    } else {
        $product = new WC_Product_Simple();
    }

    $product->set_name($item['name']);
    $product->set_sku($item['sku']);
    $product->set_regular_price($item['reg_price']);
    if (!empty($item['sale_price'])) {
        $product->set_sale_price($item['sale_price']);
    }
    $product->set_short_description($item['short']);

    // Build rich single-product description table (Avada / Luxury style)
    $table_html = '
    <div class="tlt-specs-wrapper" style="direction:rtl; margin:20px 0;">
      <h3 style="border-right:4px solid #e02424; padding-right:12px; margin-bottom:15px; font-weight:800;">مشخصات فنی و سخت‌افزاری</h3>
      <table class="tlt-specs-table" style="width:100%; border-collapse:collapse; margin-bottom:25px;">
        <tr style="background:#f8fafc;"><th style="padding:10px 14px; border:1px solid #e2e8f0; width:25%; text-align:right;">پردازنده (CPU)</th><td style="padding:10px 14px; border:1px solid #e2e8f0;">' . esc_html($item['cpu']) . '</td></tr>
        <tr><th style="padding:10px 14px; border:1px solid #e2e8f0; text-align:right;">حافظه رم (RAM)</th><td style="padding:10px 14px; border:1px solid #e2e8f0;">' . esc_html($item['ram']) . '</td></tr>
        <tr style="background:#f8fafc;"><th style="padding:10px 14px; border:1px solid #e2e8f0; text-align:right;">حافظه ذخیره‌سازی (SSD)</th><td style="padding:10px 14px; border:1px solid #e2e8f0;">' . esc_html($item['storage']) . '</td></tr>
        <tr><th style="padding:10px 14px; border:1px solid #e2e8f0; text-align:right;">کارت گرافیک (GPU)</th><td style="padding:10px 14px; border:1px solid #e2e8f0;">' . esc_html($item['gpu']) . '</td></tr>
        <tr style="background:#f8fafc;"><th style="padding:10px 14px; border:1px solid #e2e8f0; text-align:right;">صفحه نمایش</th><td style="padding:10px 14px; border:1px solid #e2e8f0;">' . esc_html($item['display']) . '</td></tr>
        <tr><th style="padding:10px 14px; border:1px solid #e2e8f0; text-align:right;">وضعیت فیزیکی و ظاهری</th><td style="padding:10px 14px; border:1px solid #e2e8f0;"><span style="color:#059669; font-weight:bold;">' . esc_html($item['status']) . '</span></td></tr>
        <tr style="background:#f8fafc;"><th style="padding:10px 14px; border:1px solid #e2e8f0; text-align:right;">هدایای ویژه همراه</th><td style="padding:10px 14px; border:1px solid #e2e8f0;"><span style="color:#e02424; font-weight:bold;">🎁 ' . esc_html($item['gift']) . '</span></td></tr>
      </table>

      <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:12px; padding:18px 22px; margin-bottom:20px;">
        <h4 style="color:#166534; margin:0 0 8px; font-weight:800;">🛡️ شرایط ضمانت و مهلت تست تاپ لپتاپ:</h4>
        <ul style="color:#15803d; line-height:1.9; margin:0; padding-right:20px;">
          <li>۷ روز مهلت تست فنی و کارشناسی کامل سخت‌افزار با ضمانت تعویض یا عودت وجه.</li>
          <li>پشتیبانی و خدمات رسمی نمایندگی گارانتی آمیتیس.</li>
          <li>امکان خرید نقدی و یا خرید اقساطی (یک‌سوم پیش‌پرداخت، مابقی در قسط‌های ماهانه).</li>
          <li>ارسال رایگان و بسته‌بندی ضدضربه به سراسر کشور.</li>
        </ul>
      </div>

      <div style="background:#eff6ff; border:1px solid #93c5fd; border-radius:12px; padding:18px 22px;">
        <h4 style="color:#1e40af; margin:0 0 8px; font-weight:800;">📞 مشاوره تلفنی و رزرو خرید اقساطی:</h4>
        <p style="color:#1d4ed8; margin:0;">
          برای دریافت شرایط دقیق اقساط، چک و پیش‌پرداخت با شماره‌های <strong>09103208705</strong> یا <strong>09033334584</strong> تماس بگیرید و یا در ایتا به آیدی <strong>@TLT_support</strong> پیام دهید.
        </p>
      </div>
    </div>';

    $product->set_description($table_html);
    $product->set_category_ids($item['cats']);
    
    // Tag IDs
    $cur_tag_ids = array();
    foreach ($item['tags'] as $tname) {
        if (isset($tags_map[$tname])) $cur_tag_ids[] = $tags_map[$tname];
    }
    $product->set_tag_ids($cur_tag_ids);

    $product->set_manage_stock(true);
    $product->set_stock_quantity(3);
    $product->set_stock_status('instock');

    // Attach Image
    $attach_id = tlt_import_image($item['img_file'], $item['name']);
    if ($attach_id) {
        $product->set_image_id($attach_id);
    }

    $pid = $product->save();
    echo "Saved product ID $pid: " . $item['name'] . " (Image ID: $attach_id)\n";
}

// 4. Create Educational Blog Posts from Channel
$posts_data = array(
    array(
        'title'   => 'مک‌بوک یا لپ‌تاپ ویندوزی؟ مقایسه تخصصی و راهنمای انتخاب',
        'content' => '
        <p>انتخاب بین macOS و Windows به نوع استفاده، نرم‌افزارهای مورد نیاز و بودجه شما بستگی دارد. در این مقاله به بررسی مزایا و تفاوت‌های کلیدی هر کدام می‌پردازیم.</p>
        <h3>دلایل انتخاب مک‌بوک (Apple):</h3>
        <ul>
          <li>شارژدهی باتری فوق‌العاده و مصرف انرژی بهینه مخصوصاً در سری تراشه‌های M1, M2, M3.</li>
          <li>هماهنگی بی‌نظیر با اکوسیستم اپل (آیفون، آیپد، اپل واچ).</li>
          <li>کیفیت ساخت خیره‌کننده، نمایشگر Liquid Retina و تاچ‌پد بی‌رقیب.</li>
          <li>عملکرد بسیار روان در برنامه‌نویسی و طراحی وب.</li>
        </ul>
        <h3>دلایل انتخاب لپ‌تاپ ویندوزی:</h3>
        <ul>
          <li>تنوع بی‌شمار قطعات، مدل‌ها و بازه‌های قیمتی از اقتصادی تا فوق حرفه‌ای.</li>
          <li>پشتیبانی بومی از کلیه بازی‌های روز و چیپ‌های گرافیک قدرتمند NVIDIA RTX.</li>
          <li>امکان ارتقای آسان رم و حافظه SSD در اغلب مدل‌ها.</li>
          <li>پشتیبانی از انواع نمایشگرهای لمسی و دستگاه‌های دوکاره.</li>
        </ul>
        <p>کارشناسان تاپ لپتاپ آماده‌اند با توجه به رشته تحصیلی و کاربرد شما، بهترین لپ‌تاپ را با شرایط نقد و اقساط پیشنهاد دهند.</p>',
        'img_file'=> 'eitaa_images/eitaa_Toplaptopsir_6016.jpg',
    ),
    array(
        'title'   => 'بررسی و مقایسه ۵ کانفیگ مختلف لپ‌تاپ محبوب HP Victus 15',
        'content' => '
        <p>اگر دنبال یک لپ‌تاپ گیمینگ و مهندسی پرقدرت با ارزش خرید بسیار بالا هستید، خانواده محبوب HP Victus در ۵ کانفیگ مختلف در تاپ لپتاپ آماده تحویل است.</p>
        <h3>مشخصات مشترک در تمام مدل‌های سری Victus 15:</h3>
        <ul>
          <li>صفحه نمایش: 15.6 اینچ Full HD با نرخ تازه‌سازی ۱۴۴ هرتز (144Hz) روان.</li>
          <li>حافظه رم: ۱۶ گیگابایت (DDR4 / DDR5 متناسب با نسل پردازنده).</li>
          <li>حافظه ذخیره‌سازی: 512GB SSD NVMe پرسرعت با قابلیت ارتقا.</li>
          <li>سیستم خنک‌کننده بهینه دو فن برای عملکرد پایدار در بار کاری سنگین.</li>
        </ul>
        <p>کلیه مدل‌های HP Victus در تاپ لپتاپ با ۷ روز مهلت تست واقعی، گارانتی آمیتیس و امکان خرید اقساطی با یک‌سوم پیش‌پرداخت عرضه می‌گردند.</p>',
        'img_file'=> 'eitaa_images/eitaa_Toplaptopsir_6015.jpg',
    ),
);

foreach ($posts_data as $p) {
    $existing = get_page_by_title($p['title'], OBJECT, 'post');
    if ($existing) {
        $post_id = $existing->ID;
    } else {
        $post_id = wp_insert_post(array(
            'post_title'   => $p['title'],
            'post_content' => $p['content'],
            'post_status'  => 'publish',
            'post_type'    => 'post',
        ));
    }
    $attach_id = tlt_import_image($p['img_file'], $p['title']);
    if ($attach_id) {
        set_post_thumbnail($post_id, $attach_id);
    }
    echo "Saved post ID $post_id: " . $p['title'] . "\n";
}

echo "Catalog and real images import completed!\n";
