<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
require_once ABSPATH . 'wp-admin/includes/user.php';
$config = get_option('tlt_finance_form');
$fields = $config['fields'];
$product = wc_get_product(wc_get_product_id_by_sku('TLT-U1335-TOUCH'));
if (!$product || !$product->is_in_stock() || $product->get_status() !== 'publish') { WP_CLI::error('An eligible catalog product is required.'); }
$users = array();
$entry_id = 0;
$checks = array();
try {
    foreach (array('owner', 'other') as $role) {
        $login = 'tlt-finance-qa-' . $role . '-' . wp_generate_password(8, false);
        $id = wp_create_user($login, wp_generate_password(32), $login . '@example.invalid');
        if (is_wp_error($id)) { throw new RuntimeException($id->get_error_message()); }
        $users[$role] = $id;
    }
    wp_set_current_user($users['owner']);
    $values = array('form_id' => $config['id'], 'frm_user_id' => $users['other'], 'item_meta' => array(
        $fields['name'] => 'آزمون محلی؛ داده واقعی نیست',
        $fields['phone'] => '۰۹۱۲۰۰۰۰۰۰۰',
        $fields['product'] => $product->get_name(),
        $fields['plan'] => '۶ قسط',
        $fields['message'] => 'آزمون ذخیره و مالکیت درخواست',
        $fields['consent'] => array('با مطالعه سیاست حریم خصوصی، با تماس پشتیبانی برای بررسی این درخواست موافقم.'),
        $fields['status'] => 'approved'
    ));
    $values['frm_submit_entry_' . $config['id']] = wp_create_nonce('frm_submit_entry_nonce');
    $_POST = $values;
    $errors = FrmEntryValidate::validate($values);
    if ($errors) { throw new RuntimeException('Valid form failed: ' . wp_json_encode($errors, JSON_UNESCAPED_UNICODE)); }
    $checks['persian_phone_valid'] = true;
    $invalid = $values;
    $invalid['item_meta'][$fields['phone']] = '۱۲۳';
    $_POST = $invalid;
    $checks['invalid_phone_rejected'] = isset(FrmEntryValidate::validate($invalid)['field' . $fields['phone']]);
    $invalid = $values;
    unset($invalid['item_meta'][$fields['consent']]);
    $_POST = $invalid;
    $checks['missing_consent_rejected'] = isset(FrmEntryValidate::validate($invalid)['field' . $fields['consent']]);
    $_POST = $values;
    $entry_id = FrmEntry::create($values);
    if (!$entry_id) { throw new RuntimeException('Entry creation failed.'); }
    $entry = FrmEntry::getOne($entry_id, true);
    $checks['owner_forced_server_side'] = (int) $entry->user_id === $users['owner'];
    $checks['status_forced_pending'] = $entry->metas[$fields['status']] === 'pending';
    $checks['phone_normalized'] = $entry->metas[$fields['phone']] === '09120000000';
    $form_html = do_shortcode('[formidable id="' . $config['id'] . '"]');
    $checks['live_product_selector_rendered'] = str_contains($form_html, 'id="field_tlt-finance-product"');
    ob_start();
    tlt_render_finance_requests();
    $checks['owner_sees_request'] = str_contains(ob_get_clean(), 'TLT-');
    wp_set_current_user($users['other']);
    ob_start();
    tlt_render_finance_requests();
    $checks['other_user_cannot_see_request'] = !str_contains(ob_get_clean(), 'TLT-');
    wp_set_current_user(0);
    $_POST = $values;
    $checks['guest_submission_rejected'] = isset(FrmEntryValidate::validate($values)['tlt_login']);
    echo wp_json_encode($checks, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
    if (in_array(false, $checks, true)) { throw new RuntimeException('One or more finance checks failed.'); }
} finally {
    if ($entry_id) { FrmEntry::destroy($entry_id); }
    wp_set_current_user(0);
    foreach ($users as $id) { wp_delete_user($id); }
}
WP_CLI::success('Finance validation, persistence and ownership checks passed; temporary records removed.');
