<?php
/**
 * Create store pages, CF7 installment-request form, menus, and set the front page.
 * Run with: wp eval-file seed/seed-pages.php
 */

// ---------- 1. Contact Form 7: installment purchase request ----------
$form_html = '
<p>درخواست خرید اقساطی خود را ثبت کنید؛ پشتیبانی تاپ لپتاپ برای بررسی اعتبارسنجی و اعلام شرایط با شما تماس می‌گیرد.</p>
<p>
نام و نام خانوادگی: [text* name placeholder="نام کامل"]<br>
شماره تماس: [text* phone placeholder="0912 000 0000"]<br>
محصول مورد نظر: [text* product placeholder="نام مدل لپتاپ"]<br>
نوع خرید: [radio* paytype "نقدی" "اقساطی"]<br>
مدت اقساط (در صورت اقساطی): [select years { "یکجا" "۳ قسط" "۶ قسط" "۱۰ قسط" "۱۲ قسط" }]<br>
توضیحات: [textarea message placeholder="هر توضیحاتی دارید..."]
</p>
<p>[submit "ثبت درخواست"]</p>';
$mail = "[subject]درخواست خرید از تاپ لپتاپ[/subject]
[body]
نام: [name]
تماس: [phone]
محصول: [product]
نوع خرید: [paytype]
مدت اقساط: [years]
توضیحات: [message]
[/body]
[additional-headers]Content-Type: text/plain; charset=UTF-8[/additional-headers]";
$cf7_id = wp_insert_post(array(
    'post_type'    => 'contact',
    'post_title'   => 'درخواست خرید اقساطی تاپ لپتاپ',
    'post_status'  => 'publish',
    'post_content' => '',
));
update_post_meta($cf7_id, '_title', 'درخواست خرید اقساطی تاپ لپتاپ');
update_post_meta($cf7_id, '_form', $form_html);
update_post_meta($cf7_id, '_mail', $mail);
update_post_meta($cf7_id, '_mail_2', $mail);

// ---------- 2. Pages ----------
function tlt_page($title, $content, $parent = 0) {
    $existing = get_page_by_title($title);
    if ($existing) { $existing->post_content = $content; $existing->post_parent = $parent; wp_update_post(array('ID'=>$existing->ID,'post_content'=>$content,'post_parent'=>$parent)); return $existing->ID; }
    return wp_insert_post(array('post_title'=>$title,'post_content'=>$content,'post_status'=>'publish','post_type'=>'page','post_parent'=>$parent,'comment_status'=>'closed'));
}

// -- Installment page with calculator + request form --
$calc_js = '
<script>
(function(){
  var priceInput = document.getElementById("tl_calc_price");
  var planSel = document.getElementById("tl_calc_plan");
  var out = document.getElementById("tl_calc_out");
  var interest = {3:0, 6:0.05, 10:0.09, 12:0.12}; // approximate financing charge by plan (adjustable)
  function calc(){
    var p = parseFloat(priceInput.value.replace(/[^0-9.]/g,""));
    var m = parseInt(planSel.value,10);
    if(!p || !m){ out.innerHTML="قیمت و تعداد قسط را وارد کنید."; return; }
    var down = Math.round(p*1/3);
    var remainder = p - down;
    var financed = remainder * (1 + (interest[m]||0));
    var monthly = Math.round(financed / m);
    function fmt(n){ return n.toLocaleString("fa-IR"); }
    out.innerHTML =
      "<div style=\'direction:rtl;line-height:2\'>" +
      "پیش‌پرداخت (یک‌سوم): <b>"+fmt(down)+" تومان</b><br>" +
      "مبلغ اقساطی قابل‌تقسیط: <b>"+fmt(remainder)+" تومان</b><br>" +
      "مبلغ کل با نرخ خدمات اقساط: <b>"+fmt(Math.round(financed))+" تومان</b><br>" +
      "تعداد قسط: <b>"+m+"</b><br>" +
      "هر قسط حدود: <b style=\'color:#e03131;font-size:1.1em\'>"+fmt(monthly)+" تومان</b><br>" +
      "<small>این محاسبه تقریبی است؛ شرایط نهایی توسط پشتیبانی اعلام می‌شود.</small>" +
      "</div>";
  }
  if(priceInput){ priceInput.addEventListener("input",calc); planSel.addEventListener("change",calc); }
})();
</script>';

$calc_box = '
<div style="max-width:520px;background:#f8f9fa;border:1px solid #e3e6ea;border-radius:12px;padding:20px;direction:rtl">
  <h3>ماشین‌حساب اقساط</h3>
  <label>قیمت دستگاه (تومان):</label><br>
  <input id="tl_calc_price" type="text" inputmode="numeric" placeholder="مثلاً 130000000" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;direction:ltr;text-align:right">
  <br><br>
  <label>تعداد قسط:</label><br>
  <select id="tl_calc_plan" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px">
    <option value="3">۳ قسط</option><option value="6" selected>۶ قسط</option><option value="10">۱۰ قسط</option><option value="12">۱۲ قسط</option>
  </select>
  <br><br>
  <div id="tl_calc_out" style="background:#fff;border:1px dashed #cbd2d9;border-radius:8px;padding:14px;min-height:60px"></div>
</div>
' . $calc_js;

$installment_page = '
<h2>خرید اقساطی لپتاپ در تاپ لپتاپ</h2>
<p>در تاپ لپتاپ می‌توانید لپتاپ مورد نظر خود را به‌صورت <strong>نقدی</strong> یا <strong>اقساطی</strong> خریداری کنید. در خرید اقساطی، <strong>یک‌سوم مبلغ به‌عنوان پیش‌پرداخت</strong> پرداخت می‌شود و مابقی در قالب قسط‌های ماهانه (۳، ۶، ۱۰ یا ۱۲ قسط) تسویه می‌گردد.</p>
<h3>مراحل خرید اقساطی</h3>
<ol>
  <li>دستگاه مورد نظر را انتخاب کنید و فرم زیر را تکمیل کنید.</li>
  <li>پشتیبانی برای بررسی و اعلام شرایط نهایی با شما تماس می‌گیرد.</li>
  <li>پیش‌پرداخت (یک‌سوم) پرداخت و قرارداد ثبت می‌شود.</li>
  <li>اقساط باقی‌مانده ماهانه پرداخت و دستگاه تحویل داده می‌شود.</li>
</ol>
' . $calc_box . '
<h3>ثبت درخواست خرید اقساطی</h3>
<div>' . do_shortcode('[contact-form-7 id="' . $cf7_id . '"]') . '</div>';
$pid_install = tlt_page('خرید اقساطی', $installment_page);

// -- Laptop advisor --
$advisor = '
<h2>مشاوره انتخاب لپتاپ</h2>
<p>برای انتخاب لپتاپ مناسب، به <strong>کاربرد</strong> و <strong>بودجه</strong> خود دقت کنید:</p>
<ul>
  <li><strong>بازی و کارهای سنگین (گرافیک/مهندسی):</strong> لپتاپ با گرافیک اختصاصی (RTX) و نمایشگر ۱۴۴ هرتز. <a href="/shop/?pa_gpu=nvidia-geforce-rtx-3050-6gb">مشاهده لپتاپ‌های گیمینگ</a></li>
  <li><strong>کار اداری، دانشجویی و روزمره:</strong> لپتاپ سبک با پردازنده Core i3/i5 و صفحه نمایش ۱۴ اینچ. <a href="/?product_cat=اداری-و-دانشجویی">مشاهده لپتاپ‌های اداری</a></li>
  <li><strong>بودجه محدود:</strong> لپتاپ‌های استوک تست‌شده با قیمت مناسب‌تر و ۷ روز مهلت تست. <a href="/?product_cat=استوک-تست‌شده">مشاهده استوک‌ها</a></li>
</ul>
<h3>راهنمای سریع بر اساس بودجه</h3>
<table style="width:100%;border-collapse:collapse;direction:rtl">
  <tr style="background:#f1f3f5"><th style="padding:8px;border:1px solid #dee2e6;text-align:right">بودجه تقریبی</th><th style="padding:8px;border:1px solid #dee2e6;text-align:right">پیشنهاد</th></tr>
  <tr><td style="padding:8px;border:1px solid #dee2e6">تا ۵۰ میلیون تومان</td><td style="padding:8px;border:1px solid #dee2e6">اداری/دانشجویی سبک یا استوک اقتصادی</td></tr>
  <tr><td style="padding:8px;border:1px solid #dee2e6">۵۰ تا ۱۵۰ میلیون تومان</td><td style="padding:8px;border:1px solid #dee2e6">اداری-کاری رده متوسط یا استوک پرچمدار</td></tr>
  <tr><td style="padding:8px;border:1px solid #dee2e6">بالای ۱۵۰ میلیون تومان</td><td style="padding:8px;border:1px solid #dee2e6">گیمینگ با گرافیک RTX و نمایشگر ۱۴۴Hz</td></tr>
</table>
<p>اگر هنوز مطمئن نیستید، <a href="/خرید-اقساطی/">فرم مشاوره</a> را پر کنید یا با 09103208705 تماس بگیرید؛ رایگان راهنمایی می‌کنیم.</p>';
$pid_advisor = tlt_page('مشاوره انتخاب لپتاپ', $advisor);

// -- Customer club --
$club = '
<h2>باشگاه مشتریان تاپ لپتاپ</h2>
<p>با خرید از تاپ لپتاپ امتیاز کسب می‌کنید و می‌توانید امتیازها را در خریدهای بعدی به‌عنوان تخفیف استفاده کنید.</p>
<ul>
  <li><strong>امتیازدهی:</strong> برای هر ریال خرید، امتیاز یکسان تودای شما شارژ می‌شود.</li>
  <li><strong>استفاده از امتیاز:</strong> در تسویه‌حساب، امتیاز خود را کسر کنید.</li>
  <li><strong>معرفی دوستان:</strong> با کد معرف، شما و دوستتان هر دوی امتیاز ویژه می‌گیرید.</li>
</ul>
<p>برای فعال‌سازی، هنگام ثبت سفارش یک حساب کاربری بسازید تا امتیازهای شما ذخیره شود.</p>';
$pid_club = tlt_page('باشگاه مشتریان', $club);

// -- Legal / policy pages --
$terms = '<h2>قوانین و مقررات</h2><p>با خرید از فروشگاه تاپ لپتاپ، شما قوانین و مقررات این فروشگاه را می‌پذیرید. کلیه محصولات با گارانتی سلامت سخت‌افزار و ۷ روز مهلت تست واقعی عرضه می‌شوند. قیمت‌ها به تومان است و قابل تغییر می‌باشد. چک کردن موجودی و قیمت نهایی پیش از پرداخت الزامی است.</p>';
$privacy = '<h2>سیاست حریم خصوصی</h2><p>تاپ لپتاپ اطلاعات شما (نام، شماره تماس و اطلاعات سفارش) را صرفاً برای پردازش سفارش، پشتیبانی و ارسال اطلاع‌رسانی‌های مرتبط استفاده می‌کند. اطلاعات شما با هیچ شخص یا سازمان ثالثی به‌منظور بازاریابی سهیم نمی‌شود، مگر با رضایت شما.</p>';
$returns = '<h2>شرایط مرجوعی و ۷ روز مهلت تست</h2><ul><li>همه محصولات ۷ روز مهلت تست واقعی دارند.</li><li>در صورت وجود عیب یا خطا در مهلت تست، دستگاه بررسی و در صورت تأیید عیب، تعویض یا عودت وجه انجام می‌شود.</li><li>برای مرجوعی، سلامت دستگاه، فاکتور و لوازم همراه باید کامل باشد.</li><li>لطفاً پیش از مرجوعی با پشتیبانی هماهنگ کنید.</li></ul>';
$aftersales = '<h2>خدمات پس از فروش</h2><p>تاپ لپتاپ پس از فروش نیز در کنار شماست. پیگیری سفارش، وضعیت ارسال، گارانتی و تست، از طریق پشتیبانی انجام می‌شود.</p><ul><li>پشتیبانی: @TLT_support</li><li>تلفن: 09103208705</li><li>پیگیری سفارش از بخش "سفارش‌های من" در حساب کاربری</li></ul>';
$guide = '<h2>راهنمای خرید از تاپ لپتاپ</h2><p>خرید از تاپ لپتاپ در چند مرحله ساده:</p><ol><li>لپتاپ مورد نظر را در <a href="/shop/">فروشگاه</a> انتخاب کنید.</li><li>مشخصات، قیمت نقدی و امکان اقساط را بررسی کنید.</li><li>دستگاه را به سبد خرید اضافه کنید.</li><li>در صفحه تسویه‌حساب، نوع خرید (نقدی/اقساطی) و اطلاعات ارسال را وارد کنید.</li><li>برای اقساطی، فرم درخواست را تکمیل کنید تا شرایط نهایی اعلام شود.</li></ol><p>برای مشاوره رایگان قبل از خرید با 09103208705 در تماس باشید.</p>';
$pid_terms = tlt_page('قوانین و مقررات', $terms);
$pid_privacy = tlt_page('سیاست حریم خصوصی', $privacy);
$pid_returns = tlt_page('شرایط مرجوعی و مهلت تست', $returns);
$pid_aftersales = tlt_page('خدمات پس از فروش', $aftersales);
$pid_guide = tlt_page('راهنمای خرید', $guide);

// -- Home page --
$home = '
<div style="text-align:center;padding:20px 0">
  <h1 style="font-size:2em">تاپ لپتاپ</h1>
  <p style="font-size:1.1em;color:#495057">فروشگاه تخصصی لپتاپ؛ خرید آکبند و استوک با <strong>گزاره‌ی اقساطی</strong>، گارانتی سلامت و ۷ روز مهلت تست واقعی</p>
  <p><a class="wp-block-button__link" href="/shop/" style="background:#e03131;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none">ورود به فروشگاه</a></p>
</div>
<h2>دسته‌بندی محصولات</h2>
<ul>
  <li><a href="/?product_cat=لپتاپ-گیمینگ">لپتاپ گیمینگ</a></li>
  <li><a href="/?product_cat=اداری-و-دانشجویی">اداری و دانشجویی</a></li>
  <li><a href="/?product_cat=استوک-تست‌شده">استوک (تست‌شده)</a></li>
  <li><a href="/?product_cat=آکبند">آکبند</a></li>
</ul>
<h2>پیشنهاد ویژه</h2>
[products limit="4" columns="4" orderby="date" order="desc" on_sale="true"]
<h2>جدیدترین محصولات</h2>
[products limit="4" columns="4" orderby="date" order="desc"]
<h2>چرا تاپ لپتاپ؟</h2>
<ul>
  <li>امکان خرید <strong>نقدی و اقساطی</strong> (پیش‌پرداخت یک‌سوم)</li>
  <li>گارانتی سلامت سخت‌افزار + <strong>۷ روز مهلت تست واقعی</strong></li>
  <li>ارسال به سراسر کشور</li>
  <li>مشاوره رایگان انتخاب لپتاپ: 09103208705</li>
</ul>
<h2>خرید اقساطی</h2>
<p>لپتاپ مورد نظر را با پرداخت یک‌سوم پیش‌پرداخت و پرداخت مابقی در قسط‌های ماهانه تهیه کنید. <a href="/خرید-اقساطی/">محاسبه اقساط و ثبت درخواست</a></p>
';
$pid_home = tlt_page('خانه', $home);

// ---------- 3. Set front page + posts page ----------
update_option('show_on_front', 'page');
update_option('page_on_front', $pid_home);
// keep blog on a page
$blog = tlt_page('وبلاگ و اخبار', '<p>راهنماها، اخبار و تخفیف‌های تاپ لپتاپ.</p>');
update_option('page_for_posts', $blog);

// ---------- 4. Menus ----------
$locations = array('primary' => 'Primary');
register_nav_menu('primary', 'Primary');
$menu_name = 'Main Menu';
$menu_id = wp_create_nav_menu($menu_name);
if (!is_wp_error($menu_id)) {
    $items = array();
    $add = function($title, $url) use (&$items, $menu_name) {
        $items[] = array('title'=>$title,'url'=>$url,'type'=>'custom','target'=>'','attr_title'=>'','description'=>'','class_names'=>array(),'xfn'=>'');
    };
    $add('خانه', '/');
    $add('فروشگاه', '/shop/');
    $add('گیمینگ', '/?product_cat=لپتاپ-گیمینگ');
    $add('اداری و دانشجویی', '/?product_cat=اداری-و-دانشجویی');
    $add('استوک', '/?product_cat=استوک-تست‌شده');
    $add('خرید اقساطی', get_permalink($pid_install));
    $add('مشاوره انتخاب', get_permalink($pid_advisor));
    $add('باشگاه مشتریان', get_permalink($pid_club));
    $add('راهنمای خرید', get_permalink($pid_guide));
    $add('تماس با ما', '/?product_cat=خدمات-پس-از-فروش');
    // add items
    foreach ($items as $it) {
        $items_data = wp_get_nav_menu_items($menu_id);
        $existing_id = 0;
        foreach (($items_data) as $mi) { if ($mi->title == $it['title']) { $existing_id = $mi->ID; break; } }
        if (!$existing_id) {
            $new = array_merge($it, array('menu_id'=>$menu_id, 'position'=>count($items_data)+1));
            wp_update_nav_menu_item($menu_id, 0, $new);
        }
    }
    $locs = get_theme_mod('nav_menu_locations', array());
    $locs['primary'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locs);
}

echo "CF7 form id: $cf7_id\n";
echo "Pages -> home:$pid_home install:$pid_install advisor:$pid_advisor club:$pid_club terms:$pid_terms privacy:$pid_privacy returns:$pid_returns aftersales:$pid_aftersales guide:$pid_guide blog:$blog\n";
echo "Menu id: " . $menu_id . "\n";
echo "Pages done.\n";
