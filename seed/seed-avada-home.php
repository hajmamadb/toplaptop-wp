<?php
/**
 * Update homepage with high-end Avada Ecommerce (378779/2) layout and styling.
 */

$css = '
<style>
:root {
  --tlt-primary: #e02424;
  --tlt-primary-hover: #c81e1e;
  --tlt-dark: #0f172a;
  --tlt-dark-card: #1e293b;
  --tlt-accent: #3b82f6;
  --tlt-success: #10b981;
  --tlt-bg-light: #f8fafc;
  --tlt-border: #e2e8f0;
}

.tlt-container {
  font-family: inherit;
  direction: rtl;
  max-width: 1240px;
  margin: 0 auto;
  padding: 0 15px;
}

/* 1. Hero Showcase */
.tlt-hero-section {
  background: linear-gradient(135deg, #0b0f19 0%, #1a2234 100%);
  border-radius: 20px;
  padding: 45px 35px;
  color: #fff;
  margin-bottom: 35px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.15);
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 30px;
  position: relative;
  overflow: hidden;
}
.tlt-hero-section::before {
  content: "";
  position: absolute;
  top: -50%;
  right: -20%;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(224,36,36,0.15) 0%, transparent 70%);
  pointer-events: none;
}
.tlt-hero-text {
  flex: 1 1 500px;
  z-index: 1;
}
.tlt-badge-hero {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(224,36,36,0.15);
  border: 1px solid rgba(224,36,36,0.4);
  color: #ff6b6b;
  padding: 6px 14px;
  border-radius: 30px;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 18px;
}
.tlt-hero-title {
  font-size: 2.3em;
  font-weight: 900;
  line-height: 1.35;
  margin: 0 0 15px;
  color: #ffffff;
}
.tlt-hero-title span {
  color: #f87171;
}
.tlt-hero-desc {
  font-size: 1.1em;
  color: #94a3b8;
  line-height: 1.8;
  margin-bottom: 25px;
}
.tlt-hero-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}
.tlt-btn-main {
  background: var(--tlt-primary);
  color: #fff !important;
  padding: 13px 26px;
  border-radius: 12px;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.25s ease;
  box-shadow: 0 8px 20px rgba(224,36,36,0.3);
}
.tlt-btn-main:hover {
  background: var(--tlt-primary-hover);
  transform: translateY(-2px);
}
.tlt-btn-outline {
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.2);
  color: #fff !important;
  padding: 13px 24px;
  border-radius: 12px;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.25s ease;
}
.tlt-btn-outline:hover {
  background: rgba(255,255,255,0.15);
  transform: translateY(-2px);
}
.tlt-hero-image {
  flex: 1 1 380px;
  text-align: center;
  z-index: 1;
}
.tlt-hero-card {
  background: rgba(30, 41, 59, 0.7);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 18px;
  padding: 25px;
  box-shadow: 0 15px 30px rgba(0,0,0,0.3);
}

/* 2. Features Row (Avada Style) */
.tlt-features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px;
  margin-bottom: 35px;
}
.tlt-feature-box {
  background: #ffffff;
  border: 1px solid var(--tlt-border);
  border-radius: 16px;
  padding: 22px 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.03);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.tlt-feature-box:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}
.tlt-feat-icon {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  background: #fef2f2;
  color: var(--tlt-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  flex-shrink: 0;
}
.tlt-feat-title {
  font-weight: 800;
  font-size: 15px;
  margin: 0 0 4px;
  color: var(--tlt-dark);
}
.tlt-feat-desc {
  font-size: 13px;
  color: #64748b;
  margin: 0;
}

/* 3. Category Cards */
.tlt-cats-section {
  margin-bottom: 40px;
}
.tlt-section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 22px;
  border-bottom: 2px solid #f1f5f9;
  padding-bottom: 12px;
}
.tlt-section-title {
  font-size: 1.4em;
  font-weight: 800;
  margin: 0;
  color: var(--tlt-dark);
  position: relative;
}
.tlt-section-title::after {
  content: "";
  position: absolute;
  bottom: -14px;
  right: 0;
  width: 60px;
  height: 3px;
  background: var(--tlt-primary);
  border-radius: 3px;
}
.tlt-cats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
}
.tlt-cat-card {
  background: #ffffff;
  border: 1px solid var(--tlt-border);
  border-radius: 14px;
  padding: 20px 15px;
  text-align: center;
  text-decoration: none;
  transition: all 0.25s ease;
  display: block;
}
.tlt-cat-card:hover {
  border-color: var(--tlt-primary);
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(224,36,36,0.08);
}
.tlt-cat-icon {
  font-size: 32px;
  margin-bottom: 10px;
}
.tlt-cat-name {
  font-weight: 700;
  font-size: 15px;
  color: var(--tlt-dark);
  margin-bottom: 4px;
}
.tlt-cat-sub {
  font-size: 12px;
  color: #94a3b8;
}

/* 4. Flash Deal Section (سه‌شنبه‌های تخفیفی) */
.tlt-deal-banner {
  background: linear-gradient(135deg, #18181b 0%, #27272a 100%);
  border: 1px solid #3f3f46;
  border-radius: 20px;
  padding: 30px;
  color: #fff;
  margin-bottom: 45px;
}
.tlt-deal-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 15px;
  margin-bottom: 25px;
  border-bottom: 1px solid #3f3f46;
  padding-bottom: 15px;
}
.tlt-timer-box {
  background: #e02424;
  color: #fff;
  padding: 6px 16px;
  border-radius: 10px;
  font-weight: 800;
  font-size: 14px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

/* 5. Custom Product Cards (Avada Grid) */
.tlt-prod-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
  gap: 22px;
  margin-bottom: 45px;
}
.tlt-prod-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  overflow: hidden;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  position: relative;
  box-shadow: 0 4px 15px rgba(0,0,0,0.02);
}
.tlt-prod-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 30px rgba(0,0,0,0.08);
  border-color: #cbd5e1;
}
.tlt-prod-badge {
  position: absolute;
  top: 14px;
  right: 14px;
  background: var(--tlt-primary);
  color: #fff;
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  z-index: 2;
}
.tlt-prod-thumb {
  padding: 25px 20px 10px;
  text-align: center;
  background: #fafafa;
}
.tlt-prod-thumb img {
  max-width: 100%;
  height: 180px;
  object-fit: contain;
  transition: transform 0.3s ease;
}
.tlt-prod-card:hover .tlt-prod-thumb img {
  transform: scale(1.05);
}
.tlt-prod-body {
  padding: 18px 20px 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.tlt-prod-title {
  font-size: 15px;
  font-weight: 800;
  line-height: 1.5;
  margin: 0 0 10px;
  color: var(--tlt-dark);
}
.tlt-prod-title a {
  color: inherit;
  text-decoration: none;
}
.tlt-prod-specs {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-bottom: 14px;
}
.tlt-spec-pill {
  background: #f1f5f9;
  color: #475569;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
}
.tlt-prod-pricing {
  margin-top: auto;
  border-top: 1px dashed #e2e8f0;
  padding-top: 12px;
}
.tlt-reg-price {
  font-size: 13px;
  color: #94a3b8;
  text-decoration: line-through;
  margin-bottom: 2px;
}
.tlt-sale-price {
  font-size: 18px;
  font-weight: 900;
  color: var(--tlt-primary);
}
.tlt-inst-note {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #166534;
  padding: 5px 8px;
  border-radius: 6px;
  font-size: 11.5px;
  font-weight: 700;
  margin-top: 8px;
  text-align: center;
}
.tlt-card-btn {
  display: block;
  background: var(--tlt-dark);
  color: #fff !important;
  text-align: center;
  padding: 10px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 13px;
  text-decoration: none;
  margin-top: 12px;
  transition: background 0.2s ease;
}
.tlt-card-btn:hover {
  background: var(--tlt-primary);
}

/* 6. Campaign Promo Banner (Black Friday) */
.tlt-bf-strip {
  background: linear-gradient(90deg, #18181b 0%, #27272a 50%, #18181b 100%);
  border: 1px solid #3f3f46;
  border-radius: 18px;
  padding: 30px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 20px;
  color: #fff;
  margin-bottom: 45px;
}
.tlt-bf-text h3 {
  font-size: 1.8em;
  font-weight: 900;
  margin: 0 0 6px;
  color: #fff;
}
.tlt-bf-text p {
  color: #a1a1aa;
  margin: 0;
  font-size: 14px;
}
.tlt-bf-code {
  background: rgba(224,36,36,0.15);
  border: 1px dashed #e02424;
  padding: 8px 16px;
  border-radius: 10px;
  color: #f87171;
  font-weight: 800;
  letter-spacing: 1px;
}
</style>
';

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
        عرضه انواع لپ‌تاپ‌های گیمینگ، اداری و استوک اروپایی با ضمانت گارانتی رسمی آمیتیس، ۷ روز مهلت تست فنی بی‌قیدوشرط و امکان خرید با یک‌سوم پیش‌پرداخت.
      </p>
      <div class="tlt-hero-actions">
        <a href="/shop/" class="tlt-btn-main">مشاهده لپ‌تاپ‌ها ←</a>
        <a href="/خرید-اقساطی/" class="tlt-btn-outline">محاسبه اقساط ماهانه</a>
        <a href="/مشاوره-انتخاب-لپتاپ/" class="tlt-btn-outline">مشاوره هوشمند خرید</a>
      </div>
    </div>
    <div class="tlt-hero-image">
      <div class="tlt-hero-card">
        <div style="font-size: 40px; margin-bottom: 10px;">💻</div>
        <h3 style="color:#fff; font-size:18px; margin:0 0 8px;">پیشنهاد ویژه این هفته</h3>
        <p style="color:#94a3b8; font-size:13px; margin:0 0 15px;">HP Victus 15 با پردازنده قدرتمند Ryzen 7 و کارت گرافیک RTX</p>
        <div style="color:#f87171; font-weight:800; font-size:1.3em; margin-bottom:12px;">شروع اقساط با یک‌سوم پیش‌پرداخت</div>
        <a href="/خرید-اقساطی/" class="tlt-btn-main" style="display:block; padding:10px;">محاسبه و رزرو فوری</a>
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
        <div class="tlt-cat-sub">پردازنده و گرافیک پرقدرت RTX</div>
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
        <div class="tlt-cat-sub">پلمپ شرکتی با گارانتی</div>
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
        <h3 style="color:#fff; font-size:1.7em; font-weight:900; margin:0 0 5px;">🔥 سه‌شنبه‌های تخفیفی تاپ لپتاپ</h3>
        <p style="color:#a1a1aa; margin:0; font-size:14px;">پیشنهاد شگفت‌انگیز ۲۴ ساعته روی دستگاه‌های منتخب استوک</p>
      </div>
      <div class="tlt-timer-box">
        ⏳ اعتبار تخفیف: <span id="tlt-clock">۲۳:۵۹:۴۵</span>
      </div>
    </div>
    
    <div class="tlt-prod-grid" style="margin-bottom:0;">
      <!-- Product 1 -->
      <div class="tlt-prod-card">
        <span class="tlt-prod-badge">۷ میلیون سود خرید</span>
        <div class="tlt-prod-thumb">
          <img src="/wp-content/uploads/tl-placeholder/gaming.png" alt="لپتاپ گیمینگ Intel Core i5">
        </div>
        <div class="tlt-prod-body">
          <h3 class="tlt-prod-title">
            <a href="/product/لپتاپ-گیمینگ-intel-core-i5-13420h-rtx-3050/">لپتاپ گیمینگ Core i5-13420H / RTX 3050</a>
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
            <div class="tlt-inst-note">پیش‌پرداخت اقساطی: ۷۸,۰۰۰,۰۰۰ تومان</div>
            <a href="/خرید-اقساطی/" class="tlt-card-btn">ثبت سفارش و خرید اقساطی ←</a>
          </div>
        </div>
      </div>

      <!-- Product 2 -->
      <div class="tlt-prod-card">
        <span class="tlt-prod-badge" style="background:#059669;">۱۰ میلیون سود خرید</span>
        <div class="tlt-prod-thumb">
          <img src="/wp-content/uploads/tl-placeholder/ultrabook.png" alt="لپتاپ لمسی Intel Core i5">
        </div>
        <div class="tlt-prod-body">
          <h3 class="tlt-prod-title">
            <a href="/product/لپتاپ-۱۴-اینچ-لمسی-intel-core-i5-1335u/">لپتاپ ۱۴ اینچ لمسی Core i5-1335U (Touch)</a>
          </h3>
          <div class="tlt-prod-specs">
            <span class="tlt-spec-pill">i5-1335U</span>
            <span class="tlt-spec-pill">16GB RAM</span>
            <span class="tlt-spec-pill">256GB SSD</span>
            <span class="tlt-spec-pill">Iris Xe</span>
            <span class="tlt-spec-pill">صفحه لمسی</span>
          </div>
          <div class="tlt-prod-pricing">
            <div class="tlt-reg-price">۱۴۰,۰۰۰,۰۰۰ تومان</div>
            <div class="tlt-sale-price">۱۳۰,۰۰۰,۰۰۰ تومان</div>
            <div class="tlt-inst-note">پیش‌پرداخت اقساطی: ۴۳,۳۰۰,۰۰۰ تومان</div>
            <a href="/خرید-اقساطی/" class="tlt-card-btn">ثبت سفارش و خرید اقساطی ←</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 5. BLACK FRIDAY PROMO STRIP -->
  <div class="tlt-bf-strip">
    <div class="tlt-bf-text">
      <h3>🖤 جشنواره بزرگ بلک فرایدی تاپ لپتاپ</h3>
      <p>تخفیف‌های استثنایی سالانه ویژه لپ‌تاپ‌های استوک از ۵ تا ۷ آذر ماه همراه با ۷ روز مهلت تست</p>
    </div>
    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
      <div class="tlt-bf-code">کد تخفیف: BLACKFRIDAY</div>
      <a href="/جشنواره-بلک-فرایدی/" class="tlt-btn-main">ورود به جشنواره ←</a>
    </div>
  </div>

  <!-- 6. ALL ACTIVE PRODUCTS SHOWCASE -->
  <div class="tlt-cats-section">
    <div class="tlt-section-header">
      <h2 class="tlt-section-title">ویترین لپ‌تاپ‌های موجود</h2>
      <a href="/shop/" style="color:#e02424; font-weight:700; text-decoration:none; font-size:14px;">مشاهده لیست کامل ←</a>
    </div>
    
    <div class="tlt-prod-grid">
      <!-- Victus 15 -->
      <div class="tlt-prod-card">
        <span class="tlt-prod-badge" style="background:#2563eb;">گیمینگ و مهندسی</span>
        <div class="tlt-prod-thumb">
          <img src="/wp-content/uploads/tl-placeholder/gaming.png" alt="HP Victus 15">
        </div>
        <div class="tlt-prod-body">
          <h3 class="tlt-prod-title">
            <a href="/product/لپتاپ-گیمینگ-hp-victus-15-ryzen-7-rtx-2050/">HP Victus 15 - Ryzen 7 7740HS / RTX 2050</a>
          </h3>
          <div class="tlt-prod-specs">
            <span class="tlt-spec-pill">Ryzen 7 7740HS</span>
            <span class="tlt-spec-pill">16GB DDR5</span>
            <span class="tlt-spec-pill">512GB SSD</span>
            <span class="tlt-spec-pill">RTX 2050 4GB</span>
            <span class="tlt-spec-pill">144Hz</span>
          </div>
          <div class="tlt-prod-pricing">
            <div class="tlt-sale-price">فروش نقدی و اقساطی</div>
            <div class="tlt-inst-note">پیش‌پرداخت یک‌سوم + کیف هدیه رایگان</div>
            <a href="/خرید-اقساطی/" class="tlt-card-btn">استعلام قیمت و خرید اقساطی ←</a>
          </div>
        </div>
      </div>

      <!-- Dell Latitude -->
      <div class="tlt-prod-card">
        <span class="tlt-prod-badge" style="background:#475569;">اقتصادی / دانشجویی</span>
        <div class="tlt-prod-thumb">
          <img src="/wp-content/uploads/tl-placeholder/business.png" alt="Dell Latitude E7250">
        </div>
        <div class="tlt-prod-body">
          <h3 class="tlt-prod-title">
            <a href="/product/لپتاپ-dell-latitude-e7250-intel-core-i5/">Dell Latitude E7250 - Core i5 / 8GB / 256GB SSD</a>
          </h3>
          <div class="tlt-prod-specs">
            <span class="tlt-spec-pill">Core i5 5300U</span>
            <span class="tlt-spec-pill">8GB RAM</span>
            <span class="tlt-spec-pill">256GB SSD</span>
            <span class="tlt-spec-pill">12.5 اینچ HD</span>
            <span class="tlt-spec-pill">فوق‌سبک</span>
          </div>
          <div class="tlt-prod-pricing">
            <div class="tlt-sale-price">قیمت اقتصادی</div>
            <div class="tlt-inst-note">مناسب ترید، دانشگاه و کارهای اداری</div>
            <a href="/خرید-اقساطی/" class="tlt-card-btn">استعلام و خرید اقساطی ←</a>
          </div>
        </div>
      </div>

      <!-- Asus VivoBook 14 -->
      <div class="tlt-prod-card">
        <span class="tlt-prod-badge" style="background:#0891b2;">آکبند شرکتی</span>
        <div class="tlt-prod-thumb">
          <img src="/wp-content/uploads/tl-placeholder/ultrabook.png" alt="Asus VivoBook 14">
        </div>
        <div class="tlt-prod-body">
          <h3 class="tlt-prod-title">
            <a href="/product/لپتاپ-asus-vivobook-14-intel-core-i3/">Asus VivoBook 14 - Intel Core i3 (آکبند)</a>
          </h3>
          <div class="tlt-prod-specs">
            <span class="tlt-spec-pill">Core i3-10110U</span>
            <span class="tlt-spec-pill">8GB RAM</span>
            <span class="tlt-spec-pill">256GB SSD</span>
            <span class="tlt-spec-pill">14 اینچ FHD</span>
            <span class="tlt-spec-pill">آکبند</span>
          </div>
          <div class="tlt-prod-pricing">
            <div class="tlt-sale-price">۵۲,۰۰۰,۰۰۰ تومان</div>
            <div class="tlt-inst-note">پیش‌پرداخت اقساطی: ۱۷,۳۰۰,۰۰۰ تومان</div>
            <a href="/خرید-اقساطی/" class="tlt-card-btn">خرید نقدی یا اقساطی ←</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 7. ADVICE & INSTALLMENT CALCULATOR PROMO -->
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

$full_content = $css . $html;

wp_update_post(array(
    'ID'           => 33,
    'post_content' => $full_content,
));

echo "Homepage (ID 33) successfully updated with Avada Ecommerce styling!\n";
