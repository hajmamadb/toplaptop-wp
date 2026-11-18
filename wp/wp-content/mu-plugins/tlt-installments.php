<?php
/**
 * تاپ لپتاپ — موتور اقساط، قرارداد و سررسید
 * MU plugin. مستقل از هسته و افزونه‌های خریداری‌شده.
 */
if (!defined('ABSPATH')) { exit; }

define('TLT_INS_VERSION', '1.0.0');
define('TLT_INS_DB_VERSION', '1');

/* ---------------------------------------------------------------------------
 * تنظیمات
 * ------------------------------------------------------------------------ */
function tlt_ins_event_defaults() {
    return array(
        'application_submitted' => array('label' => 'ثبت درخواست اقساطی', 'channels' => array('ticket' => 1, 'email' => 1, 'sms' => 0), 'template' => 'درخواست خرید اقساطی {product} شما ثبت شد. کد پیگیری: {app_code}. تیم پشتیبانی به‌زودی بررسی می‌کند.'),
        'application_status'    => array('label' => 'تغییر وضعیت درخواست', 'channels' => array('ticket' => 1, 'email' => 1, 'sms' => 1), 'template' => 'وضعیت درخواست اقساطی {product} شما به «{status}» تغییر کرد.'),
        'contract_created'      => array('label' => 'بسته‌شدن قرارداد', 'channels' => array('ticket' => 1, 'email' => 1, 'sms' => 1), 'template' => 'قرارداد اقساطی {product} بسته شد: {months} قسط ماهانه به مبلغ {monthly} تومان. پیش‌پرداخت: {down} تومان.'),
        'down_paid'             => array('label' => 'دریافت پیش‌پرداخت / خرید قطعی', 'channels' => array('ticket' => 1, 'email' => 1, 'sms' => 1), 'template' => 'پیش‌پرداخت {down} تومان قرارداد {product} دریافت شد؛ قرارداد فعال شد. اولین قسط {monthly} تومان با سررسید {first_due}.'),
        'installment_due'       => array('label' => 'یادآوری سررسید قسط', 'channels' => array('ticket' => 1, 'email' => 1, 'sms' => 1), 'template' => 'یادآوری: قسط {seq} از {months} قرارداد {product} به مبلغ {amount} تومان با سررسید {due_date} پرداختنی است.'),
        'installment_paid'      => array('label' => 'پرداخت قسط', 'channels' => array('ticket' => 1, 'email' => 1, 'sms' => 1), 'template' => 'قسط {seq} از {months} قرارداد {product} به مبلغ {amount} تومان پرداخت شد. اقساط باقیمانده: {remaining}.'),
        'installment_overdue'   => array('label' => 'تاخیر در پرداخت قسط', 'channels' => array('ticket' => 1, 'email' => 1, 'sms' => 1), 'template' => 'هشدار: قسط {seq} قرارداد {product} به مبلغ {amount} تومان با سررسید {due_date} هنوز پرداخت نشده است.'),
        'contract_finished'     => array('label' => 'تسویه‌شدن کامل قرارداد', 'channels' => array('ticket' => 1, 'email' => 1, 'sms' => 1), 'template' => 'تمام اقساط قرارداد {product} تسویه شد. از همراهی شما سپاسگزاریم.'),
        'contract_cancelled'    => array('label' => 'لغو قرارداد', 'channels' => array('ticket' => 1, 'email' => 1, 'sms' => 0), 'template' => 'قرارداد اقساطی {product} لغو شد.'),
    );
}

function tlt_ins_get_settings() {
    $s = get_option('tlt_installment_settings', array());
    $defaults = array(
        'interest_rate'   => 5,
        'grace_days'      => 0,
        'kavenegar_token' => '',
        'kavenegar_sender' => '',
    );
    $s = wp_parse_args((array) $s, $defaults);
    $events = tlt_ins_event_defaults();
    if (!empty($s['events']) && is_array($s['events'])) {
        foreach ($events as $key => $val) {
            if (isset($s['events'][$key]) && is_array($s['events'][$key])) {
                $events[$key] = array_merge($val, $s['events'][$key]);
            }
        }
    }
    $s['events'] = $events;
    return $s;
}

/* ---------------------------------------------------------------------------
 * محاسبهٔ قسط (مطابق فرمول: اصل + اصل×ماه×نرخ)
 * ------------------------------------------------------------------------ */
function tlt_ins_compute($price, $months, $rate) {
    $price   = max(0, (float) $price);
    $months  = max(1, (int) $months);
    $rate    = max(0, (float) $rate);
    $down    = (int) ceil($price / 3);
    $principal = $price - $down;
    $interest  = $principal * $months * ($rate / 100);
    $total   = $principal + $interest;
    $monthly = floor($total / $months);
    $last    = $total - $monthly * ($months - 1);
    return array('down' => $down, 'principal' => $principal, 'interest' => $interest, 'total' => $total, 'monthly' => $monthly, 'last' => $last, 'months' => $months, 'rate' => $rate);
}

/* ---------------------------------------------------------------------------
 * جداول
 * ------------------------------------------------------------------------ */
function tlt_ins_maybe_upgrade() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    $contracts = $wpdb->prefix . 'tlt_contracts';
    $rows      = $wpdb->prefix . 'tlt_installments';
    $events    = $wpdb->prefix . 'tlt_events';
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta("CREATE TABLE $contracts (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        code VARCHAR(32) NOT NULL,
        application_id BIGINT(20) UNSIGNED NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        product_id BIGINT(20) UNSIGNED NULL,
        product_name TEXT NULL,
        customer_name VARCHAR(190) NULL,
        customer_phone VARCHAR(32) NULL,
        total_price BIGINT(20) NOT NULL DEFAULT 0,
        down_payment BIGINT(20) NOT NULL DEFAULT 0,
        principal BIGINT(20) NOT NULL DEFAULT 0,
        interest BIGINT(20) NOT NULL DEFAULT 0,
        interest_rate DECIMAL(6,2) NOT NULL DEFAULT 0,
        months INT NOT NULL DEFAULT 1,
        monthly_amount BIGINT(20) NOT NULL DEFAULT 0,
        total_amount BIGINT(20) NOT NULL DEFAULT 0,
        first_due_date DATETIME NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'draft',
        created_at DATETIME NULL,
        updated_at DATETIME NULL,
        PRIMARY KEY  (id),
        KEY code (code),
        KEY user_id (user_id),
        KEY status (status)
    ) $charset;");
    dbDelta("CREATE TABLE $rows (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        contract_id BIGINT(20) UNSIGNED NOT NULL,
        seq INT NOT NULL,
        due_date DATETIME NULL,
        amount BIGINT(20) NOT NULL DEFAULT 0,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        paid_at DATETIME NULL,
        payment_ref VARCHAR(120) NULL,
        paid_by BIGINT(20) UNSIGNED NULL,
        note TEXT NULL,
        PRIMARY KEY  (id),
        KEY contract_id (contract_id),
        KEY status (status)
    ) $charset;");
    dbDelta("CREATE TABLE $events (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        contract_id BIGINT(20) UNSIGNED NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
        type VARCHAR(40) NOT NULL,
        message TEXT NULL,
        channels_sent TEXT NULL,
        created_at DATETIME NULL,
        PRIMARY KEY  (id),
        KEY contract_id (contract_id),
        KEY user_id (user_id)
    ) $charset;");
    update_option('tlt_ins_db_version', TLT_INS_DB_VERSION);
}

add_action('init', function () {
    if (get_option('tlt_ins_db_version') !== TLT_INS_DB_VERSION) { tlt_ins_maybe_upgrade(); }
});

/* ---------------------------------------------------------------------------
 * نقش حسابدار
 * ------------------------------------------------------------------------ */
add_action('init', function () {
    if (!get_role('accountant')) {
        add_role('accountant', 'حسابدار', array('read' => true, 'manage_tlt_installments' => true));
    }
});

function tlt_ins_can_manage() {
    return current_user_can('manage_woocommerce') || current_user_can('manage_tlt_installments');
}

/* ---------------------------------------------------------------------------
 * ساخت قرارداد + جدول اقساط
 * ------------------------------------------------------------------------ */
function tlt_ins_create_contract($data) {
    global $wpdb;
    $settings = tlt_ins_get_settings();
    $rate = isset($data['interest_rate']) ? $data['interest_rate'] : $settings['interest_rate'];
    $months = max(1, (int) ($data['months'] ?? 0));
    $price = (float) ($data['total_price'] ?? 0);
    if ($months < 1 || $price <= 0) { return new WP_Error('tlt_ins_invalid', 'قیمت و تعداد اقساط معتبر نیست.'); }
    $c = tlt_ins_compute($price, $months, $rate);

    $code = 'INS-' . wp_generate_password(8, false, false);
    $now = current_time('mysql');
    if (!empty($data['first_due_date'])) {
        $first_due = date('Y-m-d H:i:s', strtotime($data['first_due_date'] . ' 09:00:00'));
    } else {
        $first_due = date('Y-m-d H:i:s', strtotime('+1 month', strtotime($now)));
    }

    $wpdb->insert($wpdb->prefix . 'tlt_contracts', array(
        'code' => $code,
        'application_id' => (int) ($data['application_id'] ?? 0),
        'user_id' => (int) ($data['user_id'] ?? get_current_user_id()),
        'product_id' => (int) ($data['product_id'] ?? 0),
        'product_name' => (string) ($data['product_name'] ?? ''),
        'customer_name' => (string) ($data['customer_name'] ?? ''),
        'customer_phone' => (string) ($data['customer_phone'] ?? ''),
        'total_price' => (int) round($price),
        'down_payment' => $c['down'],
        'principal' => (int) round($c['principal']),
        'interest' => (int) round($c['interest']),
        'interest_rate' => (float) $rate,
        'months' => $months,
        'monthly_amount' => (int) $c['monthly'],
        'total_amount' => (int) round($c['total']),
        'first_due_date' => $first_due,
        'status' => 'draft',
        'created_at' => $now,
        'updated_at' => $now,
    ), array('%s', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%f', '%d', '%d', '%d', '%s', '%s', '%s', '%s'));
    $contract_id = $wpdb->insert_id;
    if (!$contract_id) { return new WP_Error('tlt_ins_db', 'خطا در ذخیرهٔ قرارداد.'); }

    tlt_ins_generate_schedule($contract_id, $first_due);
    $contract = tlt_ins_get_contract($contract_id);
    tlt_ins_notify('contract_created', $contract, $data + array('app_code' => $code));
    do_action('tlt_ins_contract_created', $contract_id, $contract);
    return $contract;
}

function tlt_ins_generate_schedule($contract_id, $first_due) {
    global $wpdb;
    $c = tlt_ins_get_contract($contract_id);
    if (!$c) return;
    $table = $wpdb->prefix . 'tlt_installments';
    $wpdb->query($wpdb->prepare("DELETE FROM $table WHERE contract_id=%d", (int) $contract_id));
    $last_amount = tlt_ins_contract_last_amount($c);
    for ($i = 1; $i <= $c->months; $i++) {
        $due_ts = strtotime('+' . $i . ' month', strtotime($c->first_due_date));
        $wpdb->insert($table, array(
            'contract_id' => (int) $c->id,
            'seq' => $i,
            'due_date' => date('Y-m-d H:i:s', $due_ts),
            'amount' => ($i < $c->months) ? (int) $c->monthly_amount : $last_amount,
            'status' => 'pending',
        ), array('%d', '%d', '%s', '%d', '%s'));
    }
}

function tlt_ins_contract_last_amount($c) {
    $sum = 0;
    global $wpdb;
    for ($i = 1; $i < $c->months; $i++) { $sum += (int) $c->monthly_amount; }
    return (int) round($c->total_amount - $sum);
}

/* ---------------------------------------------------------------------------
 * خواندن قراردادها و اقساط
 * ------------------------------------------------------------------------ */
function tlt_ins_get_contract($id) {
    global $wpdb;
    $table = $wpdb->prefix . 'tlt_contracts';
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d", (int) $id));
    if (!$row) return null;
    $row = (array) $row;
    $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tlt_installments WHERE contract_id=%d ORDER BY seq ASC", (int) $id));
    $row['installments'] = $rows ? array_map(fn($r) => (array) $r, $rows) : array();
    $row['paid_count'] = 0;
    $row['remaining_count'] = 0;
    $row['overdue_count'] = 0;
    foreach ($row['installments'] as $ins) {
        if ($ins['status'] === 'paid') $row['paid_count']++;
        elseif ($ins['status'] === 'overdue') $row['overdue_count']++;
        else $row['remaining_count']++;
    }
    return (object) $row;
}

function tlt_ins_list_contracts($filter = array()) {
    global $wpdb;
    $table = $wpdb->prefix . 'tlt_contracts';
    $where = array('1=1');
    $args = array();
    if (!empty($filter['status'])) { $where[] = 'status=%s'; $args[] = $filter['status']; }
    if (!empty($filter['user_id'])) { $where[] = 'user_id=%d'; $args[] = (int) $filter['user_id']; }
    $sql = "SELECT * FROM $table WHERE " . implode(' AND ', $where) . ' ORDER BY id DESC LIMIT 200';
    if ($args) $sql = $wpdb->prepare($sql, $args);
    return $wpdb->get_results($sql) ? array_map(fn($r) => (array) $r, $wpdb->get_results($sql)) : array();
}

/* ---------------------------------------------------------------------------
 * وضعیت: پیش‌پرداخت/فعال، پرداخت قسط، لغو
 * ------------------------------------------------------------------------ */
function tlt_ins_activate_contract($contract_id) {
    global $wpdb;
    $c = tlt_ins_get_contract($contract_id);
    if (!$c || $c->status === 'active') return $c;
    $wpdb->update($wpdb->prefix . 'tlt_contracts', array('status' => 'active', 'updated_at' => current_time('mysql')), array('id' => (int) $contract_id));
    $c = tlt_ins_get_contract($contract_id);
    tlt_ins_notify('down_paid', $c);
    do_action('tlt_ins_contract_activated', (int) $contract_id, $c);
    return $c;
}

function tlt_ins_mark_paid($contract_id, $installment_id, $ref = '') {
    global $wpdb;
    $c = tlt_ins_get_contract($contract_id);
    if (!$c) return new WP_Error('tlt_ins_no', 'قرارداد یافت نشد.');
    $seq = 0;
    foreach ($c->installments as $ins) {
        if ((int) $ins['id'] === (int) $installment_id) {
            $wpdb->update($wpdb->prefix . 'tlt_installments', array('status' => 'paid', 'paid_at' => current_time('mysql'), 'payment_ref' => (string) $ref, 'paid_by' => get_current_user_id()), array('id' => (int) $installment_id));
            $seq = (int) $ins['seq'];
            break;
        }
    }
    if (!$seq) return new WP_Error('tlt_ins_no_row', 'قسط یافت نشد.');
    $c = tlt_ins_get_contract($contract_id);
    $ctx = array('seq' => $seq, 'months' => $c->months, 'product' => $c->product_name, 'amount' => number_format_i18n(tlt_ins_row_amount($c, $seq)), 'remaining' => $c->remaining_count);
    tlt_ins_notify('installment_paid', $c, $ctx);
    if ($c->remaining_count === 0 && $c->overdue_count === 0) {
        $wpdb->update($wpdb->prefix . 'tlt_contracts', array('status' => 'finished', 'updated_at' => current_time('mysql')), array('id' => (int) $contract_id));
        $c = tlt_ins_get_contract($contract_id);
        tlt_ins_notify('contract_finished', $c);
        do_action('tlt_ins_contract_finished', (int) $contract_id, $c);
    }
    do_action('tlt_ins_installment_paid', (int) $contract_id, (int) $installment_id);
    return $c;
}

function tlt_ins_cancel_contract($contract_id) {
    global $wpdb;
    $c = tlt_ins_get_contract($contract_id);
    if (!$c) return;
    $wpdb->update($wpdb->prefix . 'tlt_contracts', array('status' => 'cancelled', 'updated_at' => current_time('mysql')), array('id' => (int) $contract_id));
    $c = tlt_ins_get_contract($contract_id);
    tlt_ins_notify('contract_cancelled', $c);
    do_action('tlt_ins_contract_cancelled', (int) $contract_id, $c);
}

function tlt_ins_row_amount($c, $seq) {
    foreach ($c->installments as $ins) { if ((int) $ins['seq'] === (int) $seq) return (int) $ins['amount']; }
    return (int) $c->monthly_amount;
}

/* ---------------------------------------------------------------------------
 * رویدادها و اعلان‌ها
 * ------------------------------------------------------------------------ */
function tlt_ins_log_event($contract_id, $user_id, $type, $message, $channels_sent = array()) {
    global $wpdb;
    $wpdb->insert($wpdb->prefix . 'tlt_events', array(
        'contract_id' => (int) $contract_id,
        'user_id' => (int) $user_id,
        'type' => (string) $type,
        'message' => (string) $message,
        'channels_sent' => wp_json_encode($channels_sent),
        'created_at' => current_time('mysql'),
    ));
}

function tlt_ins_render_template($text, $vars) {
    foreach ($vars as $k => $v) { $text = str_replace('{' . $k . '}', (string) $v, $text); }
    return $text;
}

function tlt_ins_build_vars($type, $c, $extra = array()) {
    $status_labels = array('pending' => 'ثبت‌شده', 'reviewing' => 'در حال بررسی', 'approved' => 'تأیید اولیه', 'rejected' => 'عدم تأیید');
    $vars = array(
        'contract_code' => $c ? $c->code : '',
        'product' => $c ? $c->product_name : '',
        'months' => $c ? number_format_i18n($c->months) : '',
        'down' => $c ? number_format_i18n($c->down_payment) : '',
        'monthly' => $c ? number_format_i18n($c->monthly_amount) : '',
        'total' => $c ? number_format_i18n($c->total_amount) : '',
        'first_due' => $c && $c->first_due_date ? date_i18n('j F Y', strtotime($c->first_due_date)) : '',
        'seq' => 0, 'amount' => 0, 'due_date' => '', 'remaining' => $c ? $c->remaining_count : 0,
        'status' => '', 'app_code' => '',
    );
    if ($type === 'application_status' && isset($extra['status'])) { $vars['status'] = $status_labels[$extra['status']] ?? $extra['status']; }
    if (isset($extra['due_date'])) { $vars['due_date'] = date_i18n('j F Y', strtotime($extra['due_date'])); }
    return array_merge($vars, array_intersect_key($extra, array_flip(array('seq', 'amount', 'due_date', 'remaining', 'app_code'))));
}

function tlt_ins_notify($type, $contract, $extra = array()) {
    $settings = tlt_ins_get_settings();
    $ev = $settings['events'][$type] ?? null;
    if (!$ev) return;
    $vars = tlt_ins_build_vars($type, $contract, $extra);
    $message = tlt_ins_render_template($ev['template'], $vars);
    $contract_id = $contract ? (int) $contract->id : (int) ($extra['contract_id'] ?? 0);
    $user_id = $contract ? (int) $contract->user_id : (int) ($extra['user_id'] ?? 0);
    $channels = array();

    // کانال تیکت (ثبت داخلی)
    if (!empty($ev['channels']['ticket'])) {
        tlt_ins_log_event($contract_id, $user_id, $type, $message, $channels);
        $channels[] = 'ticket';
    }

    // ایمیل
    if (!empty($ev['channels']['email'])) {
        $email = '';
        if ($user_id) { $u = get_userdata($user_id); if ($u) $email = $u->user_email; }
        if ($email) {
            $site = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
            wp_mail($email, $site . ' — ' . $ev['label'], $message);
            $channels[] = 'email';
        }
    }

    // پیامک (افزونه wp-sms/کاوه‌نگار به‌عنوان درگاه اصلی، در نبودش فراخوان مستقیم)
    if (!empty($ev['channels']['sms'])) {
        $phone = tlt_finance_digits((string) ($contract ? $contract->customer_phone : ($extra['phone'] ?? '')));
        if (preg_match('/^09\d{9}$/', $phone) && tlt_ins_sms_send($phone, $message, $settings['kavenegar_sender'])) {
            $channels[] = 'sms';
        }
    }

    do_action('tlt_ins_notified', $type, $contract, $channels);
}

function tlt_ins_sms_send($to, $message, $sender = '') {
    // درگاه اصلی: افزونه «پیامک» (wp-sms). توکن کاوه‌نگار و سایر درگاه‌ها در پیشخوان خودش تنظیم می‌شود.
    if (function_exists('wp_sms_send')) {
        $res = wp_sms_send($to, $message, false, $sender ?: null);
        return !is_wp_error($res);
    }
    // پس‌فول: فراخوان مستقیم کاوه‌نگار (در صورتی که wp-sms غیرفعال باشد)
    return tlt_ins_kavenegar_send($to, $message, $sender);
}

function tlt_ins_kavenegar_send($to, $message, $sender = '') {
    $settings = tlt_ins_get_settings();
    $token = $settings['kavenegar_token'];
    if (empty($token)) { return false; }
    $body = http_build_query(array('messages' => array($message), 'receptors' => $to));
    if ($sender) { $body .= '&' . http_build_query(array('sender' => $sender)); }
    $res = wp_remote_post('https://api.kavenegar.com/v3/' . rawurlencode($token) . '/sms/send.json', array('timeout' => 20, 'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'), 'body' => $body));
    if (is_wp_error($res)) { error_log('tlt_ins kavenegar error: ' . $res->get_error_message()); return false; }
    $data = json_decode(wp_remote_retrieve_body($res), true);
    return !empty($data['status']) && (int) $data['status'] === 200;
}

/* ---------------------------------------------------------------------------
 * کران: بررسی سررسید (روزانه)
 * ------------------------------------------------------------------------ */
add_action('init', function () {
    if (!wp_next_scheduled('tlt_ins_daily')) { wp_schedule_event(time() + 60, 'daily', 'tlt_ins_daily'); }
});

add_action('tlt_ins_daily', function () {
    global $wpdb;
    $settings = tlt_ins_get_settings();
    $grace = max(0, (int) $settings['grace_days']);
    $today = current_time('mysql');
    $active = tlt_ins_list_contracts(array('status' => 'active'));
    foreach ($active as $arr) {
        $c = tlt_ins_get_contract($arr['id']);
        if (!$c) continue;
        $overdue_marked = false;
        foreach ($c->installments as $ins) {
            if ($ins['status'] !== 'pending') continue;
            $due = strtotime($ins['due_date']);
            $overdue_until = $due + $grace * DAY_IN_SECONDS;
            // یادآوری سررسید (یک‌بار، در روز سررسید)
            if (!$overdue_marked && strtotime(date('Y-m-d', $due)) === strtotime(date('Y-m-d', strtotime($today)))) {
                tlt_ins_notify('installment_due', $c, array('seq' => $ins['seq'], 'months' => $c->months, 'product' => $c->product_name, 'amount' => number_format_i18n($ins['amount']), 'due_date' => $ins['due_date']));
            }
            // تاخیر
            if (time() > $overdue_until) {
                $wpdb->update($wpdb->prefix . 'tlt_installments', array('status' => 'overdue'), array('id' => (int) $ins['id']));
                tlt_ins_notify('installment_overdue', $c, array('seq' => $ins['seq'], 'amount' => number_format_i18n($ins['amount']), 'due_date' => $ins['due_date']));
                $overdue_marked = true;
            }
        }
    }
});

/* ---------------------------------------------------------------------------
 * نمای مشتری: endpoint در حساب کاربری
 * ------------------------------------------------------------------------ */
add_action('init', function () { add_rewrite_endpoint('my-installments', EP_ROOT | EP_PAGES); });
add_filter('woocommerce_account_menu_items', function ($items) {
    $items['my-installments'] = 'اقساط و قرارداد من';
    $logout = $items['customer-logout'] ?? 'خروج';
    unset($items['customer-logout']);
    $items['customer-logout'] = $logout;
    return $items;
});
add_action('woocommerce_account_my-installments_endpoint', function () {
    if (!is_user_logged_in()) { return; }
    $list = tlt_ins_list_contracts(array('user_id' => get_current_user_id()));
    if (!$list) { echo '<p>هنوز قراردادی برای شما ثبت نشده است.</p>'; return; }
    foreach ($list as $row) {
        $c = tlt_ins_get_contract($row['id']);
        if (!$c) continue;
        echo '<div class="tlt-ins-card" style="border:1px solid #dde6ed;border-radius:6px;padding:16px;margin:12px 0">';
        echo '<h3>' . esc_html($c->product_name ? $c->product_name : 'قرارداد') . ' <small style="color:#6b7683">(' . esc_html($c->code) . ')</small></h3>';
        echo '<p>' . esc_html(tlt_ins_status_label($c->status)) . ' — قسط پرداخت‌شده: <strong>' . (int) $c->paid_count . '</strong> / باقیمانده: <strong>' . (int) $c->remaining_count . '</strong>' . ($c->overdue_count ? ' — دیررس: <strong style="color:#c0392b">' . (int) $c->overdue_count . '</strong>' : '') . '</p>';
        echo tlt_ins_render_schedule_table($c);
        echo '</div>';
    }
});

function tlt_ins_status_label($s) {
    return array('draft' => 'پیش‌نویس (در انتظار خرید قطعی)', 'active' => 'فعال', 'finished' => 'تسویه‌شده', 'cancelled' => 'لغوشده')[$s] ?? $s;
}

function tlt_ins_render_schedule_table($c) {
    $html = '<table class="widefat striped" style="margin-top:10px"><thead><tr><th>قسط</th><th>سررسید</th><th>مبلغ (تومان)</th><th>وضعیت</th></tr></thead><tbody>';
    foreach ($c->installments as $ins) {
        $status = array('pending' => 'در انتظار', 'paid' => 'پرداخت‌شده', 'overdue' => 'دیررس')[$ins['status']] ?? $ins['status'];
        $cls = $ins['status'] === 'overdue' ? 'color:#c0392b' : ($ins['status'] === 'paid' ? 'color:#1e8e5a' : '');
        $html .= '<tr><td>' . (int) $ins['seq'] . '</td><td>' . ($ins['due_date'] ? date_i18n('j F Y', strtotime($ins['due_date'])) : '—') . '</td><td>' . number_format_i18n($ins['amount']) . '</td><td style="' . $cls . '">' . esc_html($status) . '</td></tr>';
    }
    $html .= '</tbody></table>';
    return $html;
}

/* ---------------------------------------------------------------------------
 * مدیریت: منو + صفحهٔ قراردادها (فروشنده + حسابدار)
 * ------------------------------------------------------------------------ */
add_action('admin_menu', function () {
    add_submenu_page('woocommerce', 'قراردادهای اقساطی', 'قراردادهای اقساطی', 'manage_woocommerce', 'tlt-installments', 'tlt_ins_admin_contracts');
    add_submenu_page('woocommerce', 'تنظیمات اقساط', 'تنظیمات اقساط', 'manage_woocommerce', 'tlt-ins-settings', 'tlt_ins_admin_settings');
    // دسترسی حسابدار
    if (get_role('accountant')) { get_role('accountant')->add_cap('manage_woocommerce'); }
});

function tlt_ins_admin_contracts() {
    if (!tlt_ins_can_manage()) { echo '<div class="notice notice-error"><p>دسترسی ندارید.</p></div>'; return; }
    // ساخت قرارداد از درخواست یا دستی
    if (isset($_POST['tlt_ins_create'])) {
        check_admin_referer('tlt_ins_create');
        $data = array(
            'user_id' => absint($_POST['user_id'] ?? 0),
            'product_id' => absint($_POST['product_id'] ?? 0),
            'product_name' => sanitize_text_field($_POST['product_name'] ?? ''),
            'customer_name' => sanitize_text_field($_POST['customer_name'] ?? ''),
            'customer_phone' => tlt_finance_digits(sanitize_text_field($_POST['customer_phone'] ?? '')),
            'total_price' => tlt_finance_digits(sanitize_text_field($_POST['total_price'] ?? '')),
            'months' => absint($_POST['months'] ?? 0),
            'interest_rate' => floatval($_POST['interest_rate'] ?? tlt_ins_get_settings()['interest_rate']),
            'first_due_date' => sanitize_text_field($_POST['first_due_date'] ?? ''),
            'application_id' => absint($_POST['application_id'] ?? 0),
        );
        $c = tlt_ins_create_contract($data);
        if (is_wp_error($c)) { echo '<div class="notice notice-error"><p>' . esc_html($c->get_error_message()) . '</p></div>'; }
        else { echo '<div class="notice notice-success"><p>قرارداد ' . esc_html($c->code) . ' ساخته شد.</p></div>'; }
    }
    // پرداخت قسط
    if (isset($_POST['tlt_ins_pay'])) {
        check_admin_referer('tlt_ins_pay');
        $res = tlt_ins_mark_paid(absint($_POST['contract_id']), absint($_POST['installment_id']), sanitize_text_field($_POST['payment_ref'] ?? ''));
        if (is_wp_error($res)) { echo '<div class="notice notice-error"><p>' . esc_html($res->get_error_message()) . '</p></div>'; }
        else { echo '<div class="notice notice-success"><p>قسط به‌عنوان پرداخت‌شده ثبت شد.</p></div>'; }
    }
    // پیش‌پرداخت / فعال‌سازی
    if (isset($_POST['tlt_ins_activate'])) {
        check_admin_referer('tlt_ins_activate');
        tlt_ins_activate_contract(absint($_POST['contract_id']));
        echo '<div class="notice notice-success"><p>پیش‌پرداخت ثبت شد و قرارداد فعال شد.</p></div>';
    }
    if (isset($_POST['tlt_ins_cancel'])) {
        check_admin_referer('tlt_ins_cancel');
        tlt_ins_cancel_contract(absint($_POST['contract_id']));
        echo '<div class="notice notice-success"><p>قرارداد لغو شد.</p></div>';
    }

    $settings = tlt_ins_get_settings();
    echo '<div class="wrap" dir="rtl"><h1>قراردادهای اقساطی</h1>';
    // فرم ساخت
    echo '<h2>ساخت قرارداد جدید</h2><form method="post">';
    wp_nonce_field('tlt_ins_create');
    echo '<table class="form-table"><tbody>';
    echo '<tr><th>نام مشتری</th><td><input name="customer_name" class="regular-text"></td></tr>';
    echo '<tr><th>شمارهٔ همراه</th><td><input name="customer_phone" class="regular-text" placeholder="09..."></td></tr>';
    echo '<tr><th>نام دستگاه</th><td><input name="product_name" class="regular-text"></td></tr>';
    echo '<tr><th>قیمت نهایی (تومان)</th><td><input name="total_price" class="regular-text" placeholder="120000000"></td></tr>';
    echo '<tr><th>تعداد اقساط ماهانه</th><td><input type="number" name="months" value="6" min="1" max="60"></td></tr>';
    echo '<tr><th>نرخ سود ماهانه (٪)</th><td><input type="number" step="0.1" name="interest_rate" value="' . esc_attr($settings['interest_rate']) . '"></td></tr>';
    echo '<tr><th>سررسید قسط اول (اختیاری)</th><td><input type="date" name="first_due_date"><p class="description">خالی = یک ماه از امروز. پیش‌پرداخت یک‌سوم خودکار محاسبه می‌شود.</p></td></tr>';
    echo '</tbody></table><p><button class="button button-primary" name="tlt_ins_create" value="1" type="submit">ساخت قرارداد و جدول اقساط</button></p></form>';

    // لیست
    $status = sanitize_key($_GET['status'] ?? '');
    $list = tlt_ins_list_contracts($status ? array('status' => $status) : array());
    echo '<h2>همهٔ قراردادها</h2>';
    if (!$list) { echo '<p>قراردادی نیست.</p>'; return; }
    echo '<table class="widefat striped"><thead><tr><th>کد</th><th>دستگاه</th><th>مشتری</th><th>مبلغ کل (با سود)</th><th>قسط ماهی</th><th>پرداخت‌شده/باقی</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>';
    foreach ($list as $row) {
        $c = tlt_ins_get_contract($row['id']);
        if (!$c) continue;
        echo '<tr><td>' . esc_html($c->code) . '</td><td>' . esc_html($c->product_name) . '</td><td>' . esc_html($c->customer_name) . '<br><small>' . esc_html($c->customer_phone) . '</small></td><td>' . number_format_i18n($c->total_amount) . '</td><td>' . number_format_i18n($c->monthly_amount) . '</td><td>' . (int) $c->paid_count . ' / ' . (int) $c->remaining_count . '</td><td>' . esc_html(tlt_ins_status_label($c->status)) . '</td><td>';
        echo '<details><summary>جزئیات</summary>';
        echo tlt_ins_render_schedule_table_with_actions($c);
        if ($c->status === 'draft') { echo '<form method="post" style="display:inline">' . wp_nonce_field('tlt_ins_activate', '', false) . '<input type="hidden" name="contract_id" value="' . (int) $c->id . '"><button class="button" name="tlt_ins_activate" value="1">ثبت پیش‌پرداخت و فعال‌سازی</button></form> '; }
        echo '<form method="post" style="display:inline">' . wp_nonce_field('tlt_ins_cancel', '', false) . '<input type="hidden" name="contract_id" value="' . (int) $c->id . '"><button class="button" name="tlt_ins_cancel" value="1">لغو</button></form>';
        echo '</details></td></tr>';
    }
    echo '</tbody></table></div>';
}

function tlt_ins_render_schedule_table_with_actions($c) {
    $html = '<table class="widefat striped" style="margin:8px 0;max-width:900px"><thead><tr><th>قسط</th><th>سررسید</th><th>مبلغ</th><th>وضعیت</th><th>ثبت پرداخت</th></tr></thead><tbody>';
    foreach ($c->installments as $ins) {
        $status = array('pending' => 'در انتظار', 'paid' => 'پرداخت‌شده', 'overdue' => 'دیررس')[$ins['status']] ?? $ins['status'];
        $html .= '<tr><td>' . (int) $ins['seq'] . '</td><td>' . ($ins['due_date'] ? date_i18n('j F Y', strtotime($ins['due_date'])) : '—') . '</td><td>' . number_format_i18n($ins['amount']) . '</td><td>' . esc_html($status) . '</td><td>';
        if ($ins['status'] === 'paid') { $html .= '<em>' . esc_html($ins['payment_ref'] ?: 'پرداخت‌شده') . '</em>'; }
        else { $html .= '<form method="post">' . wp_nonce_field('tlt_ins_pay', '', false) . '<input type="hidden" name="contract_id" value="' . (int) $c->id . '"><input type="hidden" name="installment_id" value="' . (int) $ins['id'] . '"><input name="payment_ref" class="small-text" placeholder="کد تراکنش/شماره" style="width:120px"> <button class="button button-small" name="tlt_ins_pay" value="1">پرداخت شد</button></form>'; }
        $html .= '</td></tr>';
    }
    $html .= '</tbody></table>';
    return $html;
}

/* ---------------------------------------------------------------------------
 * تنظیمات: نرخ سود + پیامک + قالب/کانال هر رویداد
 * ------------------------------------------------------------------------ */
function tlt_ins_admin_settings() {
    if (!tlt_ins_can_manage()) { echo '<div class="notice notice-error"><p>دسترسی ندارید.</p></div>'; return; }
    $settings = tlt_ins_get_settings();
    if (isset($_POST['tlt_ins_save_settings'])) {
        check_admin_referer('tlt_ins_settings');
        $new = array(
            'interest_rate' => max(0, floatval($_POST['interest_rate'] ?? 5)),
            'grace_days' => absint($_POST['grace_days'] ?? 0),
            'kavenegar_token' => sanitize_text_field($_POST['kavenegar_token'] ?? ''),
            'kavenegar_sender' => sanitize_text_field($_POST['kavenegar_sender'] ?? ''),
            'events' => array(),
        );
        foreach ($settings['events'] as $key => $val) {
            $ch = array();
            foreach (array('ticket', 'sms', 'email') as $chn) { $ch[$chn] = !empty($_POST['ev_' . $key . '_' . $chn]) ? 1 : 0; }
            $new['events'][$key] = array('label' => $val['label'], 'channels' => $ch, 'template' => sanitize_textarea_field($_POST['ev_' . $key . '_template'] ?? $val['template']));
        }
        update_option('tlt_installment_settings', $new, false);
        echo '<div class="notice notice-success"><p>تنظیمات ذخیره شد.</p></div>';
        $settings = tlt_ins_get_settings();
    }
    echo '<div class="wrap" dir="rtl"><h1>تنظیمات اقساط</h1><form method="post">';
    wp_nonce_field('tlt_ins_settings');
    echo '<table class="form-table"><tbody>';
    echo '<tr><th>نرخ سود ماهانه (٪)</th><td><input type="number" step="0.1" name="interest_rate" value="' . esc_attr($settings['interest_rate']) . '"></td></tr>';
    echo '<tr><th>مهلت تاخیر (روز)</th><td><input type="number" name="grace_days" value="' . esc_attr($settings['grace_days']) . '"><p class="description">چند روز پس از سررسید تا قسط «دیررس» نشود.</p></td></tr>';
    echo '<tr><th>توکن کاوه‌نگار</th><td><input name="kavenegar_token" class="regular-text" value="' . esc_attr($settings['kavenegar_token']) . '"></td></tr>';
    echo '<tr><th>شناسهٔ مجازی (اختیاری)</th><td><input name="kavenegar_sender" class="regular-text" value="' . esc_attr($settings['kavenegar_sender']) . '"></td></tr>';
    echo '</tbody></table>';
    echo '<h2>اعلان رویدادها (تیکت / پیامک / ایمیل)</h2><table class="widefat striped"><thead><tr><th>رویداد</th><th>تیکت</th><th>پیامک</th><th>ایمیل</th><th>متن خودکار</th></tr></thead><tbody>';
    foreach ($settings['events'] as $key => $val) {
        echo '<tr><td><strong>' . esc_html($val['label']) . '</strong></td>';
        foreach (array('ticket', 'sms', 'email') as $chn) { $lbl = array('ticket' => 'تیکت', 'sms' => 'پیامک', 'email' => 'ایمیل')[$chn]; echo '<td style="text-align:center"><label><input type="checkbox" name="ev_' . $key . '_' . $chn . '"' . (!empty($val['channels'][$chn]) ? ' checked' : '') . '> ' . $lbl . '</label></td>'; }
        echo '<td><textarea name="ev_' . $key . '_template" rows="2" class="large-text">' . esc_textarea($val['template']) . '</textarea></td></tr>';
    }
    echo '</tbody></table>';
    echo '<p><button class="button button-primary" name="tlt_ins_save_settings" value="1" type="submit">ذخیرهٔ تنظیمات</button></p></form>';
    echo '<p class="description">جای‌گذارهای مجاز در متن: {product} {contract_code} {months} {down} {monthly} {total} {seq} {amount} {due_date} {first_due} {remaining} {status} {app_code}</p></div>';
}

/* ---------------------------------------------------------------------------
 * اتصال خودکار: تأیید درخواست → پیشنهاد ساخت قرارداد (رویداد وضعیت)
 * ------------------------------------------------------------------------ */
add_action('tlt_ins_application_status', function ($status, $entry, $product_name, $phone, $user_id) {
    tlt_ins_notify('application_status', null, array('status' => $status, 'product' => $product_name, 'phone' => $phone, 'user_id' => $user_id, 'contract_id' => 0));
}, 10, 5);
