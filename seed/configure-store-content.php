<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
update_option('woocommerce_coming_soon', 'no');
update_option('woocommerce_allowed_countries', 'specific');
update_option('woocommerce_specific_allowed_countries', array('IR'));
update_option('woocommerce_ship_to_countries', 'specific');
update_option('woocommerce_specific_ship_to_countries', array('IR'));
update_option('yith_wcms_enable_ajax_validator', 'yes');
update_option('yith_wcms_use_cookie', 'no');
update_option('woocommerce_checkout_privacy_policy_text', 'اطلاعات شخصی شما برای پردازش سفارش، پشتیبانی و اهداف توضیح‌داده‌شده در [privacy_policy] استفاده می‌شود.');
update_option('woocommerce_registration_privacy_policy_text', 'اطلاعات شخصی شما برای مدیریت حساب کاربری، پشتیبانی و اهداف توضیح‌داده‌شده در [privacy_policy] استفاده می‌شود.');
$comparison_fields = YITH_WooCompare_Helper::get_default_table_fields();
update_option('yith_woocompare_fields', array_intersect_key($comparison_fields, array_flip(array('title', 'image', 'add_to_cart', 'price', 'stock', 'pa_cpu', 'pa_ram', 'pa_storage', 'pa_gpu', 'pa_display', 'pa_status'))));
$wishlist_id = (int) get_option('yith_wcwl_wishlist_page_id');
if (!$wishlist_id || !get_post($wishlist_id)) {
    $wishlist_id = wp_insert_post(array('post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'علاقه‌مندی‌های من', 'post_content' => '[yith_wcwl_wishlist]'), true);
    if (is_wp_error($wishlist_id)) { WP_CLI::error($wishlist_id->get_error_message()); }
    update_post_meta($wishlist_id, '_wp_page_template', 'templates/fullwidth.php');
    update_option('yith_wcwl_wishlist_page_id', $wishlist_id);
    flush_rewrite_rules(false);
}
$backup = get_option('tlt_before_content_repair', array());
foreach (array(26, 27, 28, 29, 30, 31, 32, 45, 46) as $id) {
    if (!isset($backup[$id])) { $backup[$id] = get_post($id)->post_content; }
}
update_option('tlt_before_content_repair', $backup, false);
$campaign_model = new \Wlr\App\Models\EarnCampaign();
$purchase_id = $campaign_model->save(array('id' => (int) get_option('tlt_loyalty_purchase_id'), 'name' => 'امتیاز خرید تاپ لپتاپ', 'description' => 'به ازای هر یک میلیون تومان مبلغ واجد شرایط سفارش تکمیل‌شده، یک امتیاز.', 'active' => 1, 'action_type' => 'point_for_purchase', 'campaign_type' => 'point', 'is_show_way_to_earn' => 1, 'point_rule' => array('wlr_point_earn_price' => 1000000, 'earn_point' => 1, 'minimum_point' => 0, 'maximum_point' => 0, 'single_product_message' => 'با تکمیل خرید این دستگاه، {wlr_product_points} امتیاز باشگاه می‌گیرید.', 'variable_product_message' => 'تا {wlr_product_points} امتیاز باشگاه کسب کنید.'), 'conditions' => array(), 'condition_relationship' => 'and'));
$reward_id = (new \Wlr\App\Models\Rewards())->save(array('id' => (int) get_option('tlt_loyalty_reward_id'), 'name' => 'تبدیل امتیاز تاپ لپتاپ', 'display_name' => 'تخفیف باشگاه مشتریان', 'active' => 1, 'reward_type' => 'redeem_point', 'discount_type' => 'points_conversion', 'coupon_type' => 'fixed_cart', 'require_point' => 1, 'discount_value' => 10000, 'minimum_point' => 100, 'maximum_point' => 0, 'conditions' => array()));
if (!$purchase_id || !$reward_id) { WP_CLI::error('Failed to save loyalty rules.'); }
update_option('tlt_loyalty_purchase_id', $purchase_id, false);
update_option('tlt_loyalty_reward_id', $reward_id, false);
update_option('wlr_settings', array_replace((array) get_option('wlr_settings', array()), array('wlr_point_label' => 'امتیاز', 'wlr_point_singular_label' => 'امتیاز', 'wlr_cart_earn_points_message' => 'با تکمیل این سفارش، {wlr_cart_points} {wlr_points_label} برای تخفیف خرید بعدی می‌گیرید.', 'wlr_checkout_earn_points_message' => 'با تکمیل این سفارش، {wlr_cart_points} {wlr_points_label} برای تخفیف خرید بعدی می‌گیرید.', 'wlr_earning_status' => array('completed'), 'wlr_removing_status' => array('refunded', 'cancelled'), 'wlr_point_rounding_type' => 'floor', 'user_action_list' => 'signin,signup', 'is_reward_display' => 'yes')));
$club = '<h2>باشگاه مشتریان تاپ لپتاپ</h2><p>برای خریدهای بعدی امتیاز جمع کنید. امتیاز خرید پس از تکمیل سفارش ثبت می‌شود و با لغو یا بازپرداخت سفارش برگشت می‌خورد.</p><ul><li>هر یک میلیون تومان مبلغ واجد شرایط خرید: یک امتیاز.</li><li>هر امتیاز هنگام تبدیل: ۱۰٬۰۰۰ تومان تخفیف.</li><li>حداقل امتیاز برای تبدیل به کوپن: ۱۰۰ امتیاز.</li><li>کوپن شخصی و یک‌بارمصرف است. برنامه معرفی دوستان هنوز فعال نیست.</li></ul>[wlr_page_content]';
wp_update_post(wp_slash(array('ID' => 27, 'post_content' => $club)));
$shop = wc_get_page_permalink('shop');
$advisor = '<h2>انتخاب لپتاپ بر اساس کاربرد و بودجه</h2><p>برای کار اداری، دانشگاه و وب، پردازنده مناسب و حافظه SSD اهمیت دارند. برای بازی و رندر، کارت گرافیک اختصاصی و توان خنک‌کاری را بررسی کنید. در دستگاه استوک، وضعیت باتری و مهلت تست را پیش از خرید بپرسید.</p><form class="tlt-budget-search tlt-finance-card" method="get" action="' . esc_url($shop) . '"><label for="tlt-use">کاربرد اصلی</label><select id="tlt-use" name="product_cat"><option value="">همه کاربردها</option>';
foreach (array(16, 17, 18) as $id) {
    $term = get_term($id, 'product_cat');
    $advisor .= '<option value="' . esc_attr(rawurldecode($term->slug)) . '">' . esc_html($term->name) . '</option>';
}
$advisor .= '</select><label for="tlt-budget">حداکثر بودجه نقدی (تومان؛ اختیاری)</label><input id="tlt-budget" name="max_price" type="text" inputmode="numeric" placeholder="مثلاً ۱۵۰۰۰۰۰۰۰"><p class="tlt-budget-error" role="alert"></p><button class="tlt-button" type="submit">نمایش لپتاپ‌های متناسب</button></form><p>مدل‌های بدون قیمت نیازمند استعلام هستند و در بازه قیمت قطعی پیشنهاد نمی‌شوند. برای مشاوره با <a href="tel:09103208705">۰۹۱۰۳۲۰۸۷۰۵</a> تماس بگیرید.</p>';
wp_update_post(wp_slash(array('ID' => 26, 'post_content' => $advisor)));
$support = '<h2>پشتیبانی و خدمات پس از فروش</h2><p>برای بررسی وضعیت ارسال، مهلت تست، ایراد دستگاه یا درخواست مرجوعی، موضوع و شماره سفارش را در تیکت بنویسید؛ رمز عبور، اطلاعات کارت بانکی یا مدارک حساس را ارسال نکنید.</p><p><a href="' . esc_url(wc_get_endpoint_url('orders', '', wc_get_page_permalink('myaccount'))) . '">سفارش‌های من</a> · <a href="tel:09103208705">تماس: ۰۹۱۰۳۲۰۸۷۰۵</a></p>[tlt_support]';
wp_update_post(wp_slash(array('ID' => 31, 'post_content' => $support)));
$guide = '<h2>راهنمای خرید از تاپ لپتاپ</h2><ol><li>از <a href="' . esc_url($shop) . '">فروشگاه</a> یا <a href="' . esc_url(get_permalink(26)) . '">مشاور انتخاب</a> شروع کنید.</li><li>مشخصات، وضعیت دستگاه و قیمت به تومان را بررسی کنید. برای مدل‌های استعلامی با پشتیبانی تماس بگیرید.</li><li>محصول قیمت‌دار را به سبد اضافه کنید؛ در تسویه‌حساب هزینه و روش ارسال را بررسی کنید.</li><li>برای اقساط، ابتدا <a href="' . esc_url(get_permalink(25)) . '">درخواست بررسی</a> ثبت کنید. بدون توافق شرایط و قرارداد، پیش‌پرداخت نکنید.</li><li>پس از ثبت، سفارش را از حساب کاربری پیگیری کنید.</li></ol><p>این فروشگاه فعلاً نسخه محلی است؛ پرداخت آنلاین و ارسال پیامک واقعی تا اتصال سرویس‌ها فعال نیست.</p>';
wp_update_post(wp_slash(array('ID' => 32, 'post_content' => $guide)));
$bf = '<h2>جشنواره بلک فرایدی تاپ لپتاپ</h2><p>برنامه معرفی پیشنهادهای منتخب در بازه ۵ تا ۷ آذر. تخفیف قطعی فقط با درج قیمت معتبر روی محصول یا کوپن فعال در تسویه‌حساب اعمال می‌شود.</p><p>در حال حاضر کوپن BLACKFRIDAY، ارسال رایگان یا هدیه تأییدشده‌ای فعال نیست. قیمت، موجودی و شرایط هر پیشنهاد را پیش از خرید بررسی کنید.</p>[sale_products limit="6" columns="3"]<p><a href="' . esc_url($shop) . '">مشاهده همه دستگاه‌ها</a></p>';
wp_update_post(wp_slash(array('ID' => 45, 'post_content' => $bf)));
$deals = '<h2>پیشنهادهای ویژه تاپ لپتاپ</h2><p>دستگاه‌های دارای تخفیف فعال در این بخش نمایش داده می‌شوند. پایان مهلت و موجودی هر پیشنهاد ملاک است؛ تخفیف هفتگی خودکار هنوز تنظیم نشده است.</p>[sale_products limit="6" columns="3"]<p>برای قیمت روز و موجودی با <a href="tel:09103208705">۰۹۱۰۳۲۰۸۷۰۵</a> تماس بگیرید.</p>';
wp_update_post(wp_slash(array('ID' => 46, 'post_content' => $deals)));
$terms = '<h2>قوانین خرید از تاپ لپتاپ</h2><p>قیمت‌ها به تومان هستند. مدل‌های بدون قیمت قطعی فقط برای استعلام نمایش داده می‌شوند. پیش از پرداخت، قیمت نهایی، موجودی، مشخصات و وضعیت دستگاه را تأیید کنید.</p><h3>مهلت تست و ضمانت</h3><p>مهلت تست، ضمانت و لوازم همراه هر دستگاه تابع توضیحات همان محصول و توافق نهایی خرید است؛ مهلت تست یک محصول به سایر محصولات تعمیم داده نمی‌شود. این توضیحات حقوق قانونی خریدار را محدود نمی‌کند.</p><h3>خرید اقساطی</h3><p>ثبت درخواست یا تأیید اولیه، قرارداد فروش یا تأیید قطعی اعتبار نیست. محاسبه یک‌سوم پیش‌پرداخت و اقساط صرفاً برآورد مبلغ پایه است و هزینه احتمالی تأمین مالی و ارسال را شامل نمی‌شود. پیش از توافق مبلغ، سررسیدها و قرارداد، پیش‌پرداخت نکنید.</p><h3>پرداخت و ارسال</h3><p>روش و هزینه ارسال باید پیش از پرداخت مشخص شود. فروشگاه فعلاً نسخه محلی است و درگاه پرداخت و ارسال واقعی تا اتصال سرویس‌ها فعال نیست.</p>';
$privacy = '<h2>سیاست حریم خصوصی</h2><p>اطلاعات حساب، تماس و نشانی برای مدیریت حساب، پردازش سفارش و پشتیبانی استفاده می‌شود. اطلاعات فرم اقساط برای بررسی درخواست و اطلاعات تیکت برای رسیدگی به موضوع پشتیبانی ثبت می‌شود.</p><p>کوکی‌های ضروری برای نگهداری نشست، سبد خرید و فهرست علاقه‌مندی استفاده می‌شوند. اطلاعات لازم برای پرداخت، ارسال و اطلاع‌رسانی سفارش، در صورت اتصال، به سرویس مربوط ارائه می‌شود؛ تبلیغات و معرفی دوستان در این نسخه فعال نیست.</p><p>رمز عبور، اطلاعات کامل کارت بانکی یا مدارک حساس را در فرم درخواست و تیکت ارسال نکنید. برای درخواست دسترسی، اصلاح یا حذف اطلاعات با پشتیبانی تماس بگیرید؛ نگهداری اسناد مالی تابع الزامات قانونی است.</p><p><a href="tel:09103208705">پشتیبانی: ۰۹۱۰۳۲۰۸۷۰۵</a></p>';
$returns = '<h2>مرجوعی و مهلت تست</h2><p>پیش از خرید، مهلت تست و شرایط ضمانت همان دستگاه را از توضیحات محصول و پشتیبانی بررسی کنید. فقط محصولاتی که مهلت تست برای آن‌ها اعلام شده است با همان شرایط عرضه می‌شوند؛ وعده عمومی هفت‌روزه برای همه محصولات وجود ندارد.</p><p>برای گزارش ایراد یا مغایرت، شماره سفارش و شرح موضوع را از طریق پشتیبانی ارسال کنید و پیش از ارسال دستگاه هماهنگ شوید. نتیجه بررسی، روش مرجوعی و زمان بازپرداخت باید به شما اعلام شود. مدارک خرید و لوازم همراه را نگه دارید و پیش از تحویل، از داده‌های شخصی نسخه پشتیبان تهیه کنید.</p><p>این راهنما حقوق قانونی خریدار را محدود نمی‌کند. <a href="' . esc_url(get_permalink(31)) . '">ثبت درخواست پشتیبانی</a></p>';
foreach (array(28 => $terms, 29 => $privacy, 30 => $returns) as $id => $content) { wp_update_post(wp_slash(array('ID' => $id, 'post_content' => $content))); }
foreach (array(26, 27, 28, 29, 30, 31, 32, 45, 46) as $id) { update_post_meta($id, '_wp_page_template', 'templates/fullwidth.php'); }
WP_CLI::success('Loyalty purchase/redeem rules configured; advisor, support and campaign copy repaired.');
