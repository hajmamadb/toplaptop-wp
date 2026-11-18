<?php
/**
 * Create campaign pages and update main menu.
 */
function tlt_get_or_create_page($title, $content) {
    $existing = get_page_by_title($title);
    if ($existing) {
        wp_update_post(array('ID' => $existing->ID, 'post_content' => $content));
        return $existing->ID;
    }
    return wp_insert_post(array(
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'comment_status' => 'closed',
    ));
}

// 1. Black Friday Campaign Page
$black_friday_content = '
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #343a40 100%); color: #fff; padding: 40px 20px; border-radius: 12px; text-align: center; margin-bottom: 30px; direction: rtl;">
  <span style="background: #e03131; color: #fff; padding: 6px 14px; border-radius: 20px; font-weight: bold; font-size: 14px;">جشنواره بزرگ بلک فرایدی تاپ لپتاپ</span>
  <h1 style="color: #fff; margin-top: 15px; font-size: 2.2em;">تخفیف‌های تکرارنشدنی لپ‌تاپ‌های استوک</h1>
  <p style="font-size: 1.1em; color: #ced4da; max-width: 650px; margin: 15px auto;">شگفت‌انگیزترین جشنواره سال از ۵ تا ۷ آذر ماه؛ تخفیف ویژه بر روی مدل‌های منتخب استوک تست‌شده به همراه ۷ روز مهلت تست و ضمانت سلامت سخت‌افزار.</p>
  <div style="display: inline-block; background: rgba(255,255,255,0.1); border: 1px dashed #f03e3e; border-radius: 10px; padding: 12px 25px; margin-top: 15px;">
    <strong>کد تخفیف ویژه جشنواره:</strong> <span style="color: #ff6b6b; font-size: 1.3em; letter-spacing: 2px;">BLACKFRIDAY</span>
  </div>
</div>

<div style="direction: rtl; margin-bottom: 30px;">
  <h2 style="border-right: 4px solid #e03131; padding-right: 12px;">محصولات منتخب جشنواره</h2>
  <p>تمام لپ‌تاپ‌های زیر شامل تخفیف جشنواره و شرایط خرید اقساطی با یک‌سوم پیش‌پرداخت می‌باشند:</p>
  [products limit="6" columns="3" category="استوک-تست‌شده" orderby="date" order="desc"]
</div>

<div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 12px; padding: 25px; direction: rtl;">
  <h3>مزایای خرید در جشنواره بلک فرایدی تاپ لپتاپ:</h3>
  <ul style="line-height: 2;">
    <li>تخفیف نقدی مستقیم روی کلیه دستگاه‌های استوک تست‌شده</li>
    <li>امکان پرداخت اقساطی حتی برای خریدهای جشنواره با پیش‌پرداخت یک‌سوم</li>
    <li>۷ روز مهلت تست فنی و کارشناسی بی‌قیدوشرط</li>
    <li>ارسال رایگان و بیمه‌شده به تمام نقاط کشور</li>
    <li>کیف ضربه‌گیر هدیه به همراه تمام دستگاه‌ها</li>
  </ul>
  <p style="margin-top: 15px;"><strong>مشاوره سریع جشنواره:</strong> با شماره 09103208705 تماس بگیرید یا در ایتا به @TLT_support پیام دهید.</p>
</div>
';

$pid_bf = tlt_get_or_create_page('جشنواره بلک فرایدی', $black_friday_content);

// 2. Tuesday 24-Hour Deal Campaign Page
$tuesday_content = '
<div style="background: linear-gradient(135deg, #e03131 0%, #c92a2a 100%); color: #fff; padding: 35px 20px; border-radius: 12px; text-align: center; margin-bottom: 30px; direction: rtl;">
  <span style="background: #ffd43b; color: #212529; padding: 6px 14px; border-radius: 20px; font-weight: bold; font-size: 14px;">پیشنهاد شگفت‌انگیز ۲۴ ساعته</span>
  <h1 style="color: #fff; margin-top: 15px; font-size: 2.2em;">سه‌شنبه‌های تخفیفی تاپ لپتاپ</h1>
  <p style="font-size: 1.1em; color: #ffe3e3; max-width: 650px; margin: 15px auto;">هر سه‌شنبه، ۲ دستگاه پرطرفدار با تخفیف طلایی و استثنایی فقط به مدت ۲۴ ساعت عرضه می‌شوند!</p>
  <div style="background: rgba(0,0,0,0.25); display: inline-block; padding: 10px 20px; border-radius: 8px;">
    ⏳ مهلت خرید: فقط تا ساعت ۲۴ سه‌شنبه یا اتمام موجودی
  </div>
</div>

<div style="direction: rtl; margin-bottom: 30px;">
  <h2 style="border-right: 4px solid #e03131; padding-right: 12px;">پیشنهادهای طلایی این هفته</h2>
  [products limit="2" columns="2" on_sale="true"]
</div>

<div style="background: #fff3bf; border: 1px solid #ffe066; border-radius: 12px; padding: 20px; direction: rtl;">
  <h3 style="color: #d9480f;">پیشنهاد بده، تخفیف بگیر!</h3>
  <p>دوست داری سه‌شنبه آینده چه لپ‌تاپی تو لیست تخفیف باشه؟ مدل مد نظرت یا بودجه‌ت رو برای پشتیبانی ما بفرست تا در تخفیف هفته بعد قرار بگیره:</p>
  <p>👈 ارتباط با پشتیبانی در ایتا: <a href="https://eitaa.com/TLT_support" target="_blank" style="font-weight: bold; color: #e03131;">@TLT_support</a> | تماس: <strong>09103208705</strong></p>
</div>
';

$pid_tuesday = tlt_get_or_create_page('سه‌شنبه‌های تخفیفی', $tuesday_content);

// Update primary navigation menu
$menu = wp_get_nav_menu_object('Main Menu');
if ($menu) {
    wp_update_nav_menu_item($menu->term_id, 0, array(
        'menu-item-title'  => 'بلک فرایدی 🔥',
        'menu-item-url'    => get_permalink($pid_bf),
        'menu-item-status' => 'publish',
        'menu-item-position' => 3,
    ));
    wp_update_nav_menu_item($menu->term_id, 0, array(
        'menu-item-title'  => 'سه‌شنبه تخفیفی ⚡',
        'menu-item-url'    => get_permalink($pid_tuesday),
        'menu-item-status' => 'publish',
        'menu-item-position' => 4,
    ));
}

echo "Campaign pages seeded! BF ID: $pid_bf, Tuesday ID: $pid_tuesday\n";
