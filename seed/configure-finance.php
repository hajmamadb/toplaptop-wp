<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
if (!class_exists('FrmForm')) { WP_CLI::error('Formidable Forms is required.'); }
$form = FrmForm::getOne('tlt-installments');
if (!$form) {
    $id = FrmForm::create(array('form_key' => 'tlt-installments', 'name' => 'درخواست خرید اقساطی تاپ لپتاپ', 'description' => 'اطلاعات اولیه برای بررسی درخواست؛ ثبت فرم به معنای تأیید اعتبار یا قرارداد نیست.', 'logged_in' => 1, 'options' => array('no_save' => 0, 'submit_value' => 'ثبت درخواست بررسی', 'success_msg' => 'درخواست شما ذخیره شد. وضعیت آن را در بخش درخواست‌های اقساطی حساب کاربری پیگیری کنید.', 'ajax_submit' => 1)));
    if (!$id) { WP_CLI::error('Failed to create installment form.'); }
} else {
    $id = (int) $form->id;
}
$products = wc_get_products(array('limit' => -1, 'status' => 'publish', 'stock_status' => 'instock', 'visibility' => 'visible'));
$names = array_map(fn($p) => $p->get_name(), $products);
$specs = array(
    'name' => array('text', 'نام و نام خانوادگی', 1, array()),
    'phone' => array('text', 'شماره همراه', 1, array()),
    'product' => array('select', 'لپتاپ مورد نظر', 1, $names),
    'plan' => array('select', 'تعداد اقساط پیشنهادی', 1, array('۳ قسط', '۶ قسط', '۱۰ قسط', '۱۲ قسط')),
    'message' => array('textarea', 'توضیحات و پرسش شما', 0, array()),
    'consent' => array('checkbox', 'رضایت برای بررسی درخواست', 1, array('با مطالعه سیاست حریم خصوصی، با تماس پشتیبانی برای بررسی این درخواست موافقم.')),
    'status' => array('hidden', 'وضعیت بررسی', 0, array())
);
$fields = array();
foreach ($specs as $key => [$type, $label, $required, $choices]) {
    $field = FrmField::getOne('tlt-finance-' . $key);
    if ($field) {
        $fields[$key] = (int) $field->id;
        if ($key === 'product') { FrmField::update($field->id, array('options' => $choices)); }
        continue;
    }
    $values = FrmFieldsHelper::setup_new_vars($type, $id);
    $values['field_key'] = 'tlt-finance-' . $key;
    $values['name'] = $label;
    $values['required'] = $required;
    $values['options'] = $choices;
    $values['field_options']['blank'] = 'لطفاً این بخش را تکمیل کنید.';
    if ($key === 'status') { $values['default_value'] = 'pending'; }
    if ($key === 'phone') { $values['description'] = 'شماره ۱۱ رقمی با ۰۹؛ اعداد فارسی هم پذیرفته می‌شوند.'; }
    $fields[$key] = FrmField::create($values);
    if (!$fields[$key]) { WP_CLI::error('Failed to create field: ' . $key); }
}
update_option('tlt_finance_form', array('id' => $id, 'fields' => $fields), false);
if (!get_option('tlt_before_finance_page')) { update_option('tlt_before_finance_page', get_post(25)->post_content, false); }
$content = '<h2>خرید اقساطی با پیش‌پرداخت یک‌سوم</h2><p>لپتاپ را انتخاب کنید، مبلغ پایه اقساط را بررسی کنید و درخواست اعتبارسنجی بدهید. طرح‌های ۳، ۶، ۱۰ و ۱۲ قسط برای بررسی پیشنهاد می‌شوند؛ هزینه تأمین مالی، مدارک، زمان تحویل و شرایط نهایی باید پیش از پرداخت در قرارداد مشخص شوند.</p><ol><li>انتخاب دستگاه و ثبت درخواست</li><li>بررسی پشتیبانی و اعلام شرایط نهایی</li><li>تأیید قرارداد و سپس پرداخت پیش‌پرداخت</li></ol>[tlt_installment]<h3>پیگیری درخواست‌های من</h3>[tlt_finance_requests]';
wp_update_post(wp_slash(array('ID' => 25, 'post_content' => $content)));
update_post_meta(25, '_wp_page_template', 'templates/fullwidth.php');
update_option('woocommerce_enable_myaccount_registration', 'yes');
update_option('woocommerce_registration_generate_username', 'yes');
flush_rewrite_rules();
WP_CLI::success('Saved finance form ' . $id . ' and ' . count($fields) . ' fields.');
