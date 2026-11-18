<?php
if (!defined('ABSPATH')) { exit; }

function tlt_finance_digits($value) {
    return strtr((string) $value, array_combine(preg_split('//u', '۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩', -1, PREG_SPLIT_NO_EMPTY), str_split('01234567890123456789')));
}

function tlt_finance_statuses() {
    return array('pending' => 'ثبت‌شده؛ در انتظار بررسی', 'reviewing' => 'در حال بررسی', 'documents' => 'نیازمند تکمیل اطلاعات؛ تماس با پشتیبانی', 'approved' => 'تأیید اولیه؛ در انتظار توافق و قرارداد', 'rejected' => 'درخواست تأیید نشد');
}

add_filter('frm_validate_entry', function ($errors, $values) {
    $config = get_option('tlt_finance_form');
    if (!$config || (int) ($values['form_id'] ?? 0) !== (int) $config['id']) { return $errors; }
    if (!is_user_logged_in()) { $errors['tlt_login'] = 'برای ثبت درخواست وارد حساب کاربری شوید.'; }
    $phone = $values['item_meta'][$config['fields']['phone']] ?? '';
    if (!is_string($phone) || !preg_match('/^09[0-9]{9}$/', tlt_finance_digits(trim($phone)))) {
        $errors['field' . $config['fields']['phone']] = 'شماره همراه معتبر ۱۱ رقمی با ۰۹ وارد کنید.';
    }
    return $errors;
}, 20, 2);

add_filter('frm_pre_create_entry', function ($values) {
    $config = get_option('tlt_finance_form');
    if ($config && (int) ($values['form_id'] ?? 0) === (int) $config['id']) {
        $values['frm_user_id'] = get_current_user_id();
        $values['item_meta'][$config['fields']['status']] = 'pending';
        $values['item_meta'][$config['fields']['phone']] = tlt_finance_digits(trim($values['item_meta'][$config['fields']['phone']] ?? ''));
    }
    return $values;
});

add_action('wp_enqueue_scripts', function () {
    if (is_page(26)) { wp_enqueue_script('tlt-finance', plugins_url('tlt-finance.js', __FILE__), array(), '1.3', true); }
});

add_shortcode('tlt_installment', function () {
    $raw = $_GET['product_id'] ?? 0;
    $product = is_scalar($raw) ? wc_get_product(absint($raw)) : false;
    if ($product && ($product->get_status() !== 'publish' || !$product->is_visible() || !$product->is_in_stock())) { $product = false; }
    $config = get_option('tlt_finance_form');
    $settings = get_option('tlt_installment_settings', array());
    $rate = isset($settings['interest_rate']) ? $settings['interest_rate'] : 5;
    wp_enqueue_script('tlt-finance', plugins_url('tlt-finance.js', __FILE__), array(), '1.3', true);
    wp_add_inline_script('tlt-finance', 'window.tltFinance = ' . wp_json_encode(array('product' => $product ? $product->get_name() : '', 'productField' => $config ? 'field_tlt-finance-product' : '', 'planField' => 'field_tlt-finance-plan', 'interestRate' => $rate)) . ';', 'before');
    ob_start();
    ?>
    <section class="tlt-finance-card" aria-labelledby="tlt-calculator-title">
        <h3 id="tlt-calculator-title">محاسبه مبلغ پایه اقساط</h3>
        <?php if ($product) { echo '<p>دستگاه انتخابی: <a href="' . esc_url($product->get_permalink()) . '">' . esc_html($product->get_name()) . '</a></p>'; } ?>
        <p>این محاسبه شامل هزینه احتمالی تأمین مالی و ارسال نیست و پیشنهاد قطعی فروش محسوب نمی‌شود.</p>
        <label for="tlt-calc-price">قیمت نقدی دستگاه (تومان)</label>
        <input id="tlt-calc-price" type="text" inputmode="numeric" value="<?php echo esc_attr($product ? $product->get_price() : ''); ?>" placeholder="مثلاً ۱۳۰۰۰۰۰۰۰" autocomplete="off">
        <label for="tlt-calc-plan">تعداد اقساط ماهانه</label>
        <select id="tlt-calc-plan"><option value="3">۳ قسط</option><option value="6" selected>۶ قسط</option><option value="10">۱۰ قسط</option><option value="12">۱۲ قسط</option></select>
        <output id="tlt-calc-result" aria-live="polite"></output>
    </section>
    <h3>درخواست بررسی خرید اقساطی</h3>
    <p>مدارک هویتی یا بانکی را در این فرم ارسال نکنید. <a href="<?php echo esc_url(get_privacy_policy_url()); ?>">سیاست حریم خصوصی</a></p>
    <?php
    if (!is_user_logged_in()) {
        echo '<p><a class="tlt-button" href="' . esc_url(wc_get_page_permalink('myaccount')) . '">ورود یا ساخت حساب برای ثبت درخواست</a></p>';
    } elseif ($config && class_exists('FrmFormsController')) {
        echo do_shortcode('[formidable id="' . absint($config['id']) . '"]');
    } else {
        echo '<p>فرم در دسترس نیست؛ برای بررسی شرایط با پشتیبانی تماس بگیرید.</p>';
    }
    return ob_get_clean();
});

function tlt_render_finance_requests($admin = false) {
    $config = get_option('tlt_finance_form');
    if (!$config || !class_exists('FrmEntry')) { echo '<p>فرم درخواست هنوز آماده نیست.</p>'; return; }
    if (!is_user_logged_in() || ($admin && !current_user_can('manage_woocommerce'))) { return; }
    $where = array('it.form_id' => (int) $config['id']);
    if (!$admin) { $where['it.user_id'] = get_current_user_id(); }
    $page = $admin ? max(1, absint($_GET['paged'] ?? 1)) : 1;
    $entries = FrmEntry::getAll($where, ' ORDER BY it.id DESC', ' LIMIT ' . (($page - 1) * 20) . ',20', true);
    if (!$entries) { echo '<p>درخواست ثبت‌شده‌ای ندارید.</p>'; return; }
    $fields = $config['fields'];
    echo '<div class="tlt-finance-table"><table class="widefat striped"><thead><tr><th>کد پیگیری</th><th>دستگاه و طرح</th><th>تاریخ ثبت</th><th>وضعیت بررسی</th>' . ($admin ? '<th>متقاضی</th><th>تغییر وضعیت</th>' : '') . '</tr></thead><tbody>';
    foreach ($entries as $entry) {
        $meta = $entry->metas ?? array();
        $status = $meta[$fields['status']] ?? 'pending';
        echo '<tr><td>TLT-' . esc_html(number_format_i18n($entry->id, 0)) . '</td><td>' . esc_html($meta[$fields['product']] ?? '') . '<br>' . esc_html($meta[$fields['plan']] ?? '') . '</td><td>' . esc_html(wp_date('Y/m/d', strtotime($entry->created_at . ' UTC'))) . '</td><td>' . esc_html(tlt_finance_statuses()[$status] ?? 'در انتظار بررسی') . '</td>';
        if ($admin) {
            echo '<td>' . esc_html($meta[$fields['name']] ?? '') . '<br>' . esc_html($meta[$fields['phone']] ?? '') . '<p>' . esc_html($meta[$fields['message']] ?? '') . '</p></td><td><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="tlt_finance_status"><input type="hidden" name="entry_id" value="' . absint($entry->id) . '">';
            wp_nonce_field('tlt_finance_' . $entry->id);
            echo '<select name="status" aria-label="وضعیت درخواست">';
            foreach (tlt_finance_statuses() as $key => $label) { echo '<option value="' . esc_attr($key) . '"' . selected($status, $key, false) . '>' . esc_html($label) . '</option>'; }
            echo '</select><button class="button" type="submit">ذخیره</button></form></td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table></div>';
    if ($admin) { echo '<p><a href="' . esc_url(add_query_arg(array('page' => 'tlt-finance', 'paged' => $page + 1), admin_url('admin.php'))) . '">درخواست‌های قدیمی‌تر ←</a></p>'; }
}

add_shortcode('tlt_finance_requests', function () {
    if (!is_user_logged_in()) { return '<p>وضعیت درخواست‌ها فقط در حساب شخصی شما نمایش داده می‌شود.</p>'; }
    ob_start();
    tlt_render_finance_requests();
    return ob_get_clean();
});

add_action('init', function () { add_rewrite_endpoint('finance-requests', EP_ROOT | EP_PAGES); });
add_filter('woocommerce_account_menu_items', function ($items) {
    $logout = $items['customer-logout'] ?? 'خروج';
    unset($items['customer-logout']);
    return $items + array('finance-requests' => 'درخواست‌های اقساطی', 'customer-logout' => $logout);
});
add_action('woocommerce_account_finance-requests_endpoint', function () { tlt_render_finance_requests(); });

add_action('admin_menu', function () {
    add_submenu_page('woocommerce', 'درخواست‌های خرید اقساطی', 'درخواست‌های اقساطی', 'manage_woocommerce', 'tlt-finance', function () {
        echo '<div class="wrap" dir="rtl"><h1>درخواست‌های خرید اقساطی</h1><p>تأیید اولیه در این صفحه، جایگزین قرارداد و توافق بر مبلغ نهایی نیست.</p>';
        tlt_render_finance_requests(true);
        echo '</div>';
    });
});

add_action('admin_post_tlt_finance_status', function () {
    if (!current_user_can('manage_woocommerce')) { wp_die('دسترسی مجاز نیست.', '', array('response' => 403)); }
    $id = absint($_POST['entry_id'] ?? 0);
    check_admin_referer('tlt_finance_' . $id);
    $status = sanitize_key($_POST['status'] ?? '');
    $config = get_option('tlt_finance_form');
    $entry = FrmEntry::getOne($id);
    if (!$config || !$entry || (int) $entry->form_id !== (int) $config['id'] || !isset(tlt_finance_statuses()[$status])) { wp_die('درخواست یا وضعیت نامعتبر است.', '', array('response' => 400)); }
    FrmEntryMeta::update_entry_meta($id, $config['fields']['status'], null, $status);
    // اطلاع‌رسانی رویداد تغییر وضعیت به موتور اقساط (تیکت/پیامک/ایمیل)
    do_action('tlt_ins_application_status', $status, $entry,
        FrmEntryMeta::get_one($id, $config['fields']['product']),
        tlt_finance_digits((string) FrmEntryMeta::get_one($id, $config['fields']['phone'])),
        (int) $entry->user_id);
    wp_safe_redirect(admin_url('admin.php?page=tlt-finance'));
    exit;
});
