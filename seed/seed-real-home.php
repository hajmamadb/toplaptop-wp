<?php
/**
 * Update homepage with real photos and products from Eitaa channel.
 */

$html = '
<div class="tlt-container">

  <!-- 1. HERO SHOWCASE (Avada Style) -->
  <div class="tlt-hero-section">
    <div class="tlt-hero-text">
      <div class="tlt-badge-hero">
        <span>⚡</span> فروشگاه تخصصی تاپ لپتاپ (تأسیس شعب تهران، مشهد و یزد)
      </div>
      <h1 class="tlt-hero-title">
        جدیدترین لپ‌تاپ‌های دنیا،<br>
        با <span>شرایط اقساطی واقعی</span>
      </h1>
      <p class="tlt-hero-desc">
        عرضه انواع لپ‌تاپ‌های گیمینگ، مهندسی، اولترابوک و استوک اروپایی گرید A++ مجهز به گارانتی رسمی آمیتیس، ۷ روز مهلت تست فنی بی‌قیدوشرط سخت‌افزار و امکان پرداخت با یک‌سوم پیش‌پرداخت.
      </p>
      <div class="tlt-hero-actions">
        <a href="/shop/" class="tlt-btn-main">مشاهده ویترین محصولات ←</a>
        <a href="/خرید-اقساطی/" class="tlt-btn-outline">محاسبه اقساط ماهانه</a>
        <a href="/مشاوره-انتخاب-لپتاپ/" class="tlt-btn-outline">مشاوره هوشمند بر اساس بودجه</a>
      </div>
    </div>
    <div class="tlt-hero-image">
      <div class="tlt-hero-card">
        <img src="/wp-content/uploads/eitaa/eitaa_Toplaptopsir_6018.jpg" alt="Surface Laptop 6" style="width:100%; height:180px; object-fit:cover; border-radius:12px; margin-bottom:12px;">
        <span style="background:#e02424; color:#fff; padding:3px 10px; border-radius:6px; font-size:11px; font-weight:800;">شاهکار جدید مایکروسافت</span>
        <h3 style="color:#fff; font-size:16px; margin:8px 0 4px; font-weight:800;">Surface Laptop 6 - Core Ultra 7</h3>
        <p style="color:#94a3b8; font-size:12px; margin:0 0 10px;">نمایشگر لمسی 2K PixelSense و پردازنده هوش مصنوعی</p>
        <a href="/product/%d9%85%d8%a7%db%8c%da%a9%d8%b1%d9%88%d8%b3%d8%a7%d9%81%d8%aa-surface-laptop-6-intel-core-ultra-7-2k-touch/" class="tlt-btn-main" style="display:block; padding:9px; font-size:13px;">مشاهده و خرید اقساطی ←</a>
      </div>
    </div>
  </div>

  <!-- 2. TRUST / FEATURES ROW -->
  <div class="tlt-features-grid">
    <div class="tlt-feature-box">
      <div class="tlt-feat-icon">💳</div>
      <div>
        <h4 class="tlt-feat-title">خرید اقساطی آسان</h4>
        <p class="tlt-feat-desc">یک‌سوم پیش‌پرداخت، اقساط تا ۱۲ ماه</p>
      </div>
    </div>
    <div class="tlt-feature-box">
      <div class="tlt-feat-icon">🛡️</div>
      <div>
        <h4 class="tlt-feat-title">۷ روز مهلت تست واقعی</h4>
        <p class="tlt-feat-desc">تضمین کارشناسی و تعویض بی‌قیدوشرط</p>
      </div>
    </div>
    <div class="tlt-feature-box">
      <div class="tlt-feat-icon">📦</div>
      <div>
        <h4 class="tlt-feat-title">ارسال سریع و بیمه‌شده</h4>
        <p class="tlt-feat-desc">بسته‌بندی ضربه‌گیر به سراسر کشور</p>
      </div>
    </div>
    <div class="tlt-feature-box">
      <div class="tlt-feat-icon">📞</div>
      <div>
        <h4 class="tlt-feat-title">مشاوره تخصصی رایگان</h4>
        <p class="tlt-feat-desc">پشتیبانی: 09103208705</p>
      </div>
    </div>
  </div>

  <!-- 3. CATEGORIES ROW -->
  <div class="tlt-cats-section">
    <div class="tlt-section-header">
      <h2 class="tlt-section-title">دسته‌بندی لپ‌تاپ‌ها</h2>
      <a href="/shop/" style="color:#e02424; font-weight:700; text-decoration:none; font-size:14px;">مشاهده تمام محصولات ←</a>
    </div>
    <div class="tlt-cats-grid">
      <a href="/?product_cat=لپتاپ-گیمینگ" class="tlt-cat-card">
        <div class="tlt-cat-icon">🎮</div>
        <div class="tlt-cat-name">لپ‌تاپ‌های گیمینگ</div>
        <div class="tlt-cat-sub">پردازنده H/HX و گرافیک RTX</div>
      </a>
      <a href="/?product_cat=اداری-و-دانشجویی" class="tlt-cat-card">
        <div class="tlt-cat-icon">💼</div>
        <div class="tlt-cat-name">اداری و دانشجویی</div>
        <div class="tlt-cat-sub">سبک، پرتابل و اقتصادی</div>
      </a>
      <a href="/?product_cat=استوک-تست‌شده" class="tlt-cat-card">
        <div class="tlt-cat-icon">⚡</div>
        <div class="tlt-cat-name">استوک اروپایی</div>
        <div class="tlt-cat-sub">گرید A++ همراه ۷ روز تست</div>
      </a>
      <a href="/?product_cat=آکبند" class="tlt-cat-card">
        <div class="tlt-cat-icon">✨</div>
        <div class="tlt-cat-name">لپ‌تاپ‌های آکبند</div>
        <div class="tlt-cat-sub">پلمپ شرکتی با گارانتی ۱۸ ماهه</div>
      </a>
      <a href="/خرید-اقساطی/" class="tlt-cat-card">
        <div class="tlt-cat-icon">📊</div>
        <div class="tlt-cat-name">طرح خرید اقساطی</div>
        <div class="tlt-cat-sub">محاسبه آنلاین اقساط</div>
      </a>
    </div>
  </div>

  <!-- 4. TIMED DEALS / سه‌شنبه‌های تخفیفی (Flash Deals) -->
  <div class="tlt-deal-banner">
    <div class="tlt-deal-head">
      <div>
        <h3 style="color:#fff; font-size:1.7em; font-weight:900; margin:0 0 5px;">🔥 سه‌شنبه‌های تخفیفی تاپ لپتاپ (فقط ۲۴ ساعت)</h3>
        <p style="color:#a1a1aa; margin:0; font-size:14px;">پیشنهاد شگفت‌انگیز ۲۴ ساعته روی ۲ دستگاه پرطرفدار استوک کانال</p>
      </div>
      <div class="tlt-timer-box">
        ⏳ اعتبار تخفیف: <span id="tlt-clock">۲۳:۵۹:۴۵</span>
      </div>
    </div>
    
    <div class="tlt-prod-grid" style="margin-bottom:0;">
      <!-- Product 103: Core i5 13420H / RTX 3050 -->
      <div class="tlt-prod-card">
        <span class="tlt-prod-badge">۷ میلیون سود خرید</span>
        <div class="tlt-prod-thumb">
          <img src="/wp-content/uploads/eitaa/eitaa_Toplaptopsir_6061.jpg" alt="لپتاپ گیمینگ نسل ۱۳ Core i5">
        </div>
        <div class="tlt-prod-body">
          <h3 class="tlt-prod-title">
            <a href="/product/%d9%84%d9%be%d8%aa%d8%a7%d9%be-%da%af%db%8c%d9%85%db%8c%d9%86%da%af-%d9%86%d8%b3%d9%84-%db%b1%db%b3-intel-core-i5-13420h-rtx-3050-%d8%b3%d9%87%d8%b4%d9%86%d8%a8%d9%87-%d8%aa%d8%ae%d9%81/">لپتاپ گیمینگ نسل ۱۳ Core i5-13420H / RTX 3050 6GB</a>
          </h3>
          <div class="tlt-prod-specs">
            <span class="tlt-spec-pill">i5-13420H</span>
            <span class="tlt-spec-pill">16GB RAM</span>
            <span class="tlt-spec-pill">512GB SSD</span>
            <span class="tlt-spec-pill">RTX 3050 6GB</span>
            <span class="tlt-spec-pill">144Hz</span>
          </div>
          <div class="tlt-prod-pricing">
            <div class="tlt-reg-price">۲۴۱,۵۰۰,۰۰۰ تومان</div>
            <div class="tlt-sale-price">۲۳۴,۵۰۰,۰۰۰ تومان</div>
            <div class="tlt-inst-note">پیش‌پرداخت اقساطی: ۷۸,۰۰۰,۰۰۰ تومان (یک‌سوم)</div>
            <a href="/product/%d9%84%d9%be%d8%aa%d8%a7%d9%be-%da%af%db%8c%d9%85%db%8c%d9%86%da%af-%d9%86%d8%b3%d9%84-%db%b1%db%b3-intel-core-i5-13420h-rtx-3050-%d8%b3%d9%87%d8%b4%d9%86%d8%a8%d9%87-%d8%aa%d8%ae%d9%81/" class="tlt-card-btn">مشاهده مشخصات و خرید اقساطی ←</a>
          </div>
        </div>
      </div>

      <!-- Product 105: Core i5 1335U Touch -->
      <div class="tlt-prod-card">
        <span class="tlt-prod-badge" style="background:#059669;">۱۰ میلیون سود خرید</span>
        <div class="tlt-prod-thumb">
          <img src="/wp-content/uploads/eitaa/eitaa_Toplaptopsir_6062.jpg" alt="لپتاپ لمسی Core i5 1335U">
        </div>
        <div class="tlt-prod-body">
          <h3 class="tlt-prod-title">
            <a href="/product/%d9%84%d9%be%d8%aa%d8%a7%d9%be-%db%b1%db%b4-%d8%a7%db%8c%d9%86%da%86-%d9%84%d9%85%d8%b3%db%8c-intel-core-i5-1335u-%d8%b3%d9%87%d8%b4%d9%86%d8%a8%d9%87-%d8%aa%d8%ae%d9%81%db%8c%d9%81%db%8c/">لپتاپ ۱۴ اینچ لمسی Core i5-1335U (TouchScreen)</a>
          </h3>
          <div class="tlt-prod-specs">
            <span class="tlt-spec-pill">i5-1335U نسل ۱۳</span>
            <span class="tlt-spec-pill">16GB RAM</span>
            <span class="tlt-spec-pill">256GB SSD</span>
            <span class="tlt-spec-pill">Iris Xe</span>
            <span class="tlt-spec-pill">لمسی FHD</span>
          </div>
          <div class="tlt-prod-pricing">
            <div class="tlt-reg-price">۱۴۰,۰۰۰,۰۰۰ تومان</div>
            <div class="tlt-sale-price">۱۳۰,۰۰۰,۰۰۰ تومان</div>
            <div class="tlt-inst-note">پیش‌پرداخت اقساطی: ۴۳,۳۰۰,۰۰۰ تومان (یک‌سوم)</div>
            <a href="/product/%d9%84%d9%be%d8%aa%d8%a7%d9%be-%db%b1%db%b4-%d8%a7%db%8c%d9%86%da%86-%d9%84%d9%85%d8%b3%db%8c-intel-core-i5-1335u-%d8%b3%d9%87%d8%b4%d9%86%d8%a8%d9%87-%d8%aa%d8%ae%d9%81%db%8c%d9%81%db%8c/" class="tlt-card-btn">مشاهده مشخصات و خرید اقساطی ←</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 5. BLACK FRIDAY PROMO STRIP -->
  <div class="tlt-bf-strip">
    <div class="tlt-bf-text">
      <h3>🖤 شگفت‌انگیزترین جشنواره بلک فرایدی تاپ لپتاپ</h3>
      <p>تخفیف‌های استثنایی سالانه ویژه لپ‌تاپ‌های استوک از ۵ تا ۷ آذر ماه همراه با ۷ روز مهلت تست و شرایط اقساطی</p>
    </div>
    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
      <div class="tlt-bf-code">کد تخفیف: BLACKFRIDAY</div>
      <a href="/جشنواره-بلک-فرایدی/" class="tlt-btn-main">ورود به جشنواره بلک فرایدی ←</a>
    </div>
  </div>

  <!-- 6. ALL ACTIVE PRODUCTS SHOWCASE (Real Products with Real Photos) -->
  <div class="tlt-cats-section">
    <div class="tlt-section-header">
      <h2 class="tlt-section-title">ویترین لپ‌تاپ‌های آماده تحویل کانال</h2>
      <a href="/shop/" style="color:#e02424; font-weight:700; text-decoration:none; font-size:14px;">مشاهده تمام محصولات فروشگاه ←</a>
    </div>
    
    <div class="tlt-prod-grid">
      <!-- 1. HP Victus 15 Ryzen 7 (ID 99) -->
      <div class="tlt-prod-card">
        <span class="tlt-prod-badge" style="background:#2563eb;">گیمینگ پرطرفدار</span>
        <div class="tlt-prod-thumb">
          <img src="/wp-content/uploads/eitaa/eitaa_Toplaptopsir_6049.jpg" alt="HP Victus 15 Ryzen 7">
        </div>
        <div class="tlt-prod-body">
          <h3 class="tlt-prod-title">
            <a href="/product/%d9%84%d9%be%d8%aa%d8%a7%d9%be-%da%af%db%8c%d9%85%db%8c%d9%86%da%af-hp-victus-15-amd-ryzen-7-7740hs-rtx-2050/">HP Victus 15 - Ryzen 7 7740HS / RTX 2050</a>
          </h3>
          <div class="tlt-prod-specs">
            <span class="tlt-spec-pill">Ryzen 7 7740HS</span>
            <span class="tlt-spec-pill">16GB DDR5</span>
            <span class="tlt-spec-pill">512GB SSD</span>
            <span class="tlt-spec-pill">RTX 2050 4GB</span>
            <span class="tlt-spec-pill">144Hz</span>
          </div>
          <div class="tlt-prod-pricing">
            <div class="tlt-reg-price">۱۷۹,۰۰۰,۰۰۰ تومان</div>
            <div class="tlt-sale-price">۱۷۲,۰۰۰,۰۰۰ تومان</div>
            <div class="tlt-inst-note">پیش‌پرداخت اقساطی: ۵۷,۳۰۰,۰۰۰ تومان + کیف هدیه</div>
            <a href="/product/%d9%84%d9%be%d8%aa%d8%a7%d9%be-%da%af%db%8c%d9%85%db%8c%d9%86%da%af-hp-victus-15-amd-ryzen-7-7740hs-rtx-2050/" class="tlt-card-btn">مشاهده و خرید اقساطی ←</a>
          </div>
        </div>
      </div>

      <!-- 2. HP Victus 15 HX 14th Gen (ID 113) -->
      <div class="tlt-prod-card">
        <span class="tlt-prod-badge" style="background:#0891b2;">آکبند نسل ۱۴</span>
        <div class="tlt-prod-thumb">
          <img src="/wp-content/uploads/eitaa/eitaa_Toplaptopsir_6015.jpg" alt="HP Victus 15 14450HX">
        </div>
        <div class="tlt-prod-body">
          <h3 class="tlt-prod-title">
            <a href="/product/%d9%84%d9%be%d8%aa%d8%a7%d9%be-%da%af%db%8c%d9%85%db%8c%d9%86%da%af-hp-victus-15-intel-core-i5-14450hx-%d8%a2%da%a9%d8%a8%d9%86%d8%af-%d9%86%d8%b3%d9%84-%db%b1%db%b4/">HP Victus 15 - Core i5 14450HX (آکبند نسل ۱۴)</a>
          </h3>
          <div class="tlt-prod-specs">
            <span class="tlt-spec-pill">Core i5 14450HX</span>
            <span class="tlt-spec-pill">16GB DDR5</span>
            <span class="tlt-spec-pill">512GB SSD</span>
            <span class="tlt-spec-pill">RTX 3050 6GB</span>
            <span class="tlt-spec-pill">گارانتی ۱۸ ماهه</span>
          </div>
          <div class="tlt-prod-pricing">
            <div class="tlt-reg-price">۲۸۵,۰۰۰,۰۰۰ تومان</div>
            <div class="tlt-sale-price">۲۷۵,۰۰۰,۰۰۰ تومان</div>
            <div class="tlt-inst-note">پیش‌پرداخت اقساطی: ۹۱,۶۰۰,۰۰۰ تومان</div>
            <a href="/product/%d9%84%d9%be%d8%aa%d8%a7%d9%be-%da%af%db%8c%d9%85%db%8c%d9%86%da%af-hp-victus-15-intel-core-i5-14450hx-%d8%a2%da%a9%d8%a8%d9%86%d8%af-%d9%86%d8%b3%d9%84-%db%b1%db%b4/" class="tlt-card-btn">مشاهده و خرید اقساطی ←</a>
          </div>
        </div>
      </div>

      <!-- 3. Surface Laptop 6 (ID 111) -->
      <div class="tlt-prod-card">
        <span class="tlt-prod-badge" style="background:#7c3aed;">لوکس / مهندسی</span>
        <div class="tlt-prod-thumb">
          <img src="/wp-content/uploads/eitaa/eitaa_Toplaptopsir_6018.jpg" alt="Surface Laptop 6">
        </div>
        <div class="tlt-prod-body">
          <h3 class="tlt-prod-title">
            <a href="/product/%d9%85%d8%a7%db%8c%da%a9%d8%b1%d9%88%d8%b3%d8%a7%d9%81%d8%aa-surface-laptop-6-intel-core-ultra-7-2k-touch/">Microsoft Surface Laptop 6 - Core Ultra 7</a>
          </h3>
          <div class="tlt-prod-specs">
            <span class="tlt-spec-pill">Core Ultra 7 165H</span>
            <span class="tlt-spec-pill">16GB DDR5</span>
            <span class="tlt-spec-pill">256GB SSD</span>
            <span class="tlt-spec-pill">2K لمسی PixelSense</span>
          </div>
          <div class="tlt-prod-pricing">
            <div class="tlt-reg-price">۳۰۵,۰۰۰,۰۰۰ تومان</div>
            <div class="tlt-sale-price">۲۹۵,۰۰۰,۰۰۰ تومان</div>
            <div class="tlt-inst-note">پیش‌پرداخت اقساطی: ۹۸,۳۰۰,۰۰۰ تومان</div>
            <a href="/product/%d9%85%d8%a7%db%8c%da%a9%d8%b1%d9%88%d8%b3%d8%a7%d9%81%d8%aa-surface-laptop-6-intel-core-ultra-7-2k-touch/" class="tlt-card-btn">مشاهده و خرید اقساطی ←</a>
          </div>
        </div>
      </div>

      <!-- 4. Dell Latitude E7250 (ID 101) -->
      <div class="tlt-prod-card">
        <span class="tlt-prod-badge" style="background:#475569;">اقتصادی دانشجویی</span>
        <div class="tlt-prod-thumb">
          <img src="/wp-content/uploads/eitaa/eitaa_Toplaptopsir_6054.jpg" alt="Dell Latitude E7250">
        </div>
        <div class="tlt-prod-body">
          <h3 class="tlt-prod-title">
            <a href="/product/%d9%84%d9%be%d8%aa%d8%a7%d9%be-dell-latitude-e7250-core-i5-8gb-256gb-ssd/">Dell Latitude E7250 - Core i5 / 8GB / 256GB SSD</a>
          </h3>
          <div class="tlt-prod-specs">
            <span class="tlt-spec-pill">Core i5 5300U</span>
            <span class="tlt-spec-pill">8GB RAM</span>
            <span class="tlt-spec-pill">256GB SSD</span>
            <span class="tlt-spec-pill">12.5 اینچ مات</span>
            <span class="tlt-spec-pill">فوق‌سبک</span>
          </div>
          <div class="tlt-prod-pricing">
            <div class="tlt-reg-price">۳۹,۵۰۰,۰۰۰ تومان</div>
            <div class="tlt-sale-price">۳۷,۵۰۰,۰۰۰ تومان</div>
            <div class="tlt-inst-note">پیش‌پرداخت اقساطی: ۱۲,۵۰۰,۰۰۰ تومان + کیف هدیه</div>
            <a href="/product/%d9%84%d9%be%d8%aa%d8%a7%d9%be-dell-latitude-e7250-core-i5-8gb-256gb-ssd/" class="tlt-card-btn">مشاهده و خرید اقساطی ←</a>
          </div>
        </div>
      </div>

      <!-- 5. Asus VivoBook 14 (ID 109) -->
      <div class="tlt-prod-card">
        <span class="tlt-prod-badge" style="background:#059669;">آکبند شرکتی</span>
        <div class="tlt-prod-thumb">
          <img src="/wp-content/uploads/eitaa/eitaa_Toplaptopsir_6044.jpg" alt="Asus VivoBook 14">
        </div>
        <div class="tlt-prod-body">
          <h3 class="tlt-prod-title">
            <a href="/product/%d9%84%d9%be%d8%aa%d8%a7%d9%be-asus-vivobook-14-intel-core-i3-%d8%a2%da%a9%d8%a8%d9%86%d8%af-%d8%b4%d8%b1%da%a9%d8%aa%db%8c/">Asus VivoBook 14 - Intel Core i3 (آکبند شرکتی)</a>
          </h3>
          <div class="tlt-prod-specs">
            <span class="tlt-spec-pill">Core i3 10110U</span>
            <span class="tlt-spec-pill">8GB RAM</span>
            <span class="tlt-spec-pill">256GB SSD</span>
            <span class="tlt-spec-pill">14 اینچ FHD</span>
            <span class="tlt-spec-pill">کیف و ماوس هدیه</span>
          </div>
          <div class="tlt-prod-pricing">
            <div class="tlt-reg-price">۵۴,۰۰۰,۰۰۰ تومان</div>
            <div class="tlt-sale-price">۵۲,۰۰۰,۰۰۰ تومان</div>
            <div class="tlt-inst-note">پیش‌پرداخت اقساطی: ۱۷,۳۰۰,۰۰۰ تومان</div>
            <a href="/product/%d9%84%d9%be%d8%aa%d8%a7%d9%be-asus-vivobook-14-intel-core-i3-%d8%a2%da%a9%d8%a8%d9%86%d8%af-%d8%b4%d8%b1%da%a9%d8%aa%db%8c/" class="tlt-card-btn">مشاهده و خرید اقساطی ←</a>
          </div>
        </div>
      </div>

      <!-- 6. HP Victus 15 Core i5 13th Gen (ID 107) -->
      <div class="tlt-prod-card">
        <span class="tlt-prod-badge" style="background:#2563eb;">گیمینگ قدرتمند</span>
        <div class="tlt-prod-thumb">
          <img src="/wp-content/uploads/eitaa/eitaa_Toplaptopsir_6040.jpg" alt="HP Victus 15 Core i5">
        </div>
        <div class="tlt-prod-body">
          <h3 class="tlt-prod-title">
            <a href="/product/%d9%84%d9%be%d8%aa%d8%a7%d9%be-%da%af%db%8c%d9%85%db%8c%d9%86%da%af-hp-victus-15-core-i5-13420h-rtx-3050-6gb/">HP Victus 15 - Core i5 13420H / RTX 3050 6GB</a>
          </h3>
          <div class="tlt-prod-specs">
            <span class="tlt-spec-pill">i5 13420H</span>
            <span class="tlt-spec-pill">16GB RAM</span>
            <span class="tlt-spec-pill">512GB SSD</span>
            <span class="tlt-spec-pill">RTX 3050 6GB</span>
            <span class="tlt-spec-pill">144Hz</span>
          </div>
          <div class="tlt-prod-pricing">
            <div class="tlt-reg-price">۲۲۰,۰۰۰,۰۰۰ تومان</div>
            <div class="tlt-sale-price">۲۱۵,۰۰۰,۰۰۰ تومان</div>
            <div class="tlt-inst-note">پیش‌پرداخت اقساطی: ۷۱,۶۰۰,۰۰۰ تومان</div>
            <a href="/product/%d9%84%d9%be%d8%aa%d8%a7%d9%be-%da%af%db%8c%d9%85%db%8c%d9%86%da%af-hp-victus-15-core-i5-13420h-rtx-3050-6gb/" class="tlt-card-btn">مشاهده و خرید اقساطی ←</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 7. EDUCATIONAL BLOG CARDS FROM CHANNEL -->
  <div class="tlt-cats-section">
    <div class="tlt-section-header">
      <h2 class="tlt-section-title">راهنمای تخصصی و مقالات انتخاب لپ‌تاپ</h2>
      <a href="/وبلاگ-و-اخبار/" style="color:#e02424; font-weight:700; text-decoration:none; font-size:14px;">مشاهده تمام مطالب وبلاگ ←</a>
    </div>
    
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:22px; margin-bottom:45px;">
      <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.03);">
        <img src="/wp-content/uploads/eitaa/eitaa_Toplaptopsir_6016.jpg" alt="مقایسه مک‌بوک و ویندوز" style="width:100%; height:190px; object-fit:cover;">
        <div style="padding:20px;">
          <h3 style="font-size:16px; font-weight:800; margin:0 0 10px; color:#0f172a;">مک‌بوک یا لپ‌تاپ ویندوزی؟ مقایسه تخصصی و راهنمای انتخاب</h3>
          <p style="color:#64748b; font-size:13px; line-height:1.7; margin:0 0 15px;">بررسی جامع تفاوت‌های macOS و Windows؛ کدام سیستم‌عامل برای برنامه‌نویسی، گیمینگ و امور دانشجویی مناسب‌تر است؟</p>
          <a href="/?p=114" style="color:#e02424; font-weight:700; text-decoration:none; font-size:13px;">ادامه مطلب و راهنما ←</a>
        </div>
      </div>

      <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.03);">
        <img src="/wp-content/uploads/eitaa/eitaa_Toplaptopsir_6015.jpg" alt="بررسی ۵ کانفیگ HP Victus" style="width:100%; height:190px; object-fit:cover;">
        <div style="padding:20px;">
          <h3 style="font-size:16px; font-weight:800; margin:0 0 10px; color:#0f172a;">بررسی و مقایسه ۵ کانفیگ مختلف لپ‌تاپ محبوب HP Victus 15</h3>
          <p style="color:#64748b; font-size:13px; line-height:1.7; margin:0 0 15px;">راهنمای خرید ۵ کانفیگ مختلف سری HP Victus با پردازنده‌های نسل ۱۳ و ۱۴ اینتل و رایزن سری ۷ به همراه گرافیک‌های RTX.</p>
          <a href="/?p=116" style="color:#e02424; font-weight:700; text-decoration:none; font-size:13px;">ادامه مطلب و راهنما ←</a>
        </div>
      </div>
    </div>
  </div>

  <!-- 8. ADVICE & INSTALLMENT CALCULATOR PROMO -->
  <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:20px; padding:35px; margin-bottom:45px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:25px;">
    <div style="flex:1 1 500px;">
      <h3 style="font-size:1.6em; font-weight:900; margin:0 0 10px; color:#0f172a;">ماشین‌حساب آنلاین و ثبت درخواست اقساط</h3>
      <p style="color:#64748b; line-height:1.8; margin:0 0 20px;">
        نمی‌دانید اقساط لپ‌تاپ مد نظرتان چقدر می‌شود؟ در ماشین‌حساب تاپ لپتاپ، مبلغ دستگاه و تعداد قسط را مشخص کنید تا میزان دقیق پیش‌پرداخت (یک‌سوم) و اقساط ماهانه را مشاهده کنید.
      </p>
      <div style="display:flex; gap:12px; flex-wrap:wrap;">
        <a href="/خرید-اقساطی/" class="tlt-btn-main">ورود به ماشین‌حساب اقساط ←</a>
        <a href="/مشاوره-انتخاب-لپتاپ/" class="tlt-btn-outline" style="color:#0f172a !important; border-color:#cbd5e1;">راهنمای انتخاب با بودجه</a>
      </div>
    </div>
    <div style="flex:0 1 300px; background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:20px; text-align:center; box-shadow:0 10px 25px rgba(0,0,0,0.04);">
      <div style="font-size:32px; margin-bottom:8px;">☎️</div>
      <h4 style="margin:0 0 5px; font-weight:800;">مشاوره تلفنی سریع</h4>
      <p style="color:#64748b; font-size:13px; margin:0 0 12px;">پاسخگویی مستقیم کارشناسان تاپ لپتاپ</p>
      <a href="tel:09103208705" style="display:block; background:#fef2f2; color:#e02424; padding:10px; border-radius:10px; font-weight:900; font-size:16px; text-decoration:none;">09103208705</a>
    </div>
  </div>

</div>

<script>
// Countdown Timer for Flash Deals
(function(){
  var timerEl = document.getElementById("tlt-clock");
  if(!timerEl) return;
  var sec = 23 * 3600 + 59 * 60 + 45;
  setInterval(function(){
    sec--;
    if(sec < 0) sec = 24 * 3600;
    var h = Math.floor(sec / 3600);
    var m = Math.floor((sec % 3600) / 60);
    var s = sec % 60;
    function pad(n){ return (n < 10 ? "0" : "") + n; }
    function toFa(str){
      var f = ["۰","۱","۲","۳","۴","۵","۶","۷","۸","۹"];
      return String(str).replace(/[0-9]/g, function(d){ return f[d]; });
    }
    timerEl.innerText = toFa(pad(h) + ":" + pad(m) + ":" + pad(s));
  }, 1000);
})();
</script>
';

wp_update_post(array(
    'ID'           => 33,
    'post_content' => $html,
));

echo "Real Homepage (ID 33) updated with channel photos and products!\n";
