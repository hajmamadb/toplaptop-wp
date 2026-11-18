<?php
/**
 * Plugin Name: Top Laptop - Avada Ecommerce UI Styling
 * Description: Injects luxury Avada Ecommerce styling across all pages (Shop, Single Product, Archives, Tags, Homepage).
 */

add_action('wp_head', function() {
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<style id="tlt-avada-ecommerce-core-css">
/* ====================================================
   TOP LAPTOP - AVADA ECOMMERCE GLOBAL DESIGN SYSTEM
   ==================================================== */
:root {
  --tlt-font: 'Vazirmatn', -apple-system, BlinkMacSystemFont, 'Segoe UI', Tahoma, Arial, sans-serif;
  --tlt-primary: #e02424;
  --tlt-primary-dark: #b91c1c;
  --tlt-dark: #0f172a;
  --tlt-dark-light: #1e293b;
  --tlt-gray-50: #f8fafc;
  --tlt-gray-100: #f1f5f9;
  --tlt-gray-200: #e2e8f0;
  --tlt-gray-400: #94a3b8;
  --tlt-gray-600: #475569;
  --tlt-gray-800: #1e293b;
  --tlt-radius-sm: 8px;
  --tlt-radius-md: 14px;
  --tlt-radius-lg: 20px;
  --tlt-shadow-sm: 0 4px 15px rgba(0,0,0,0.03);
  --tlt-shadow-md: 0 10px 25px rgba(0,0,0,0.07);
  --tlt-shadow-lg: 0 20px 40px rgba(0,0,0,0.12);
}

body, html, * {
  font-family: var(--tlt-font) !important;
}

body {
  direction: rtl;
  text-align: right;
  color: var(--tlt-gray-800);
  background-color: #fafbfc;
  line-height: 1.7;
}

/* 1. TOP ANNOUNCEMENT BAR */
.tlt-topbar {
  background: var(--tlt-dark);
  color: #94a3b8;
  padding: 8px 0;
  font-size: 13px;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}
.tlt-topbar a {
  color: #fff;
  text-decoration: none;
  font-weight: 600;
}
.tlt-topbar a:hover {
  color: var(--tlt-primary);
}

/* 2. ARCHIVE & SHOP GRID (Avada Ecommerce Grid) */
.woocommerce ul.products,
ul.products {
  display: grid !important;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)) !important;
  gap: 24px !important;
  margin: 25px 0 40px !important;
  padding: 0 !important;
  list-style: none !important;
}
.woocommerce ul.products::before,
.woocommerce ul.products::after {
  display: none !important;
}

.woocommerce ul.products li.product,
ul.products li.product {
  background: #ffffff !important;
  border: 1px solid var(--tlt-gray-200) !important;
  border-radius: var(--tlt-radius-md) !important;
  padding: 0 0 18px 0 !important;
  margin: 0 !important;
  width: 100% !important;
  float: none !important;
  box-shadow: var(--tlt-shadow-sm) !important;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
  display: flex !important;
  flex-direction: column !important;
  justify-content: space-between !important;
  position: relative !important;
  overflow: hidden !important;
}

.woocommerce ul.products li.product:hover,
ul.products li.product:hover {
  transform: translateY(-5px) !important;
  box-shadow: var(--tlt-shadow-md) !important;
  border-color: #cbd5e1 !important;
}

/* Product Badge */
.woocommerce span.onsale,
.woocommerce ul.products li.product .onsale {
  position: absolute !important;
  top: 12px !important;
  right: 12px !important;
  left: auto !important;
  background: var(--tlt-primary) !important;
  color: #ffffff !important;
  font-size: 12px !important;
  font-weight: 800 !important;
  padding: 5px 12px !important;
  border-radius: var(--tlt-radius-sm) !important;
  min-height: auto !important;
  line-height: normal !important;
  z-index: 5 !important;
  box-shadow: 0 4px 10px rgba(224,36,36,0.3) !important;
}

/* Product Image in Grid */
.woocommerce ul.products li.product a img,
ul.products li.product a img {
  width: 100% !important;
  height: 220px !important;
  object-fit: contain !important;
  background: #f8fafc !important;
  padding: 20px !important;
  margin: 0 0 14px 0 !important;
  transition: transform 0.35s ease !important;
  border-bottom: 1px solid #f1f5f9 !important;
}
.woocommerce ul.products li.product:hover a img {
  transform: scale(1.04) !important;
}

/* Product Title in Grid */
.woocommerce ul.products li.product .woocommerce-loop-product__title,
.woocommerce ul.products li.product h2,
.woocommerce ul.products li.product h3 {
  font-size: 15px !important;
  font-weight: 800 !important;
  line-height: 1.5 !important;
  color: var(--tlt-dark) !important;
  padding: 0 16px !important;
  margin: 0 0 10px 0 !important;
  display: -webkit-box !important;
  -webkit-line-clamp: 2 !important;
  -webkit-box-orient: vertical !important;
  overflow: hidden !important;
  min-height: 44px !important;
}

/* Price in Grid */
.woocommerce ul.products li.product .price,
ul.products li.product .price {
  color: var(--tlt-primary) !important;
  font-size: 17px !important;
  font-weight: 900 !important;
  padding: 0 16px !important;
  margin: auto 0 12px 0 !important;
  display: flex !important;
  align-items: center !important;
  gap: 8px !important;
  flex-wrap: wrap !important;
}
.woocommerce ul.products li.product .price del,
ul.products li.product .price del {
  color: var(--tlt-gray-400) !important;
  font-size: 13px !important;
  font-weight: 500 !important;
}
.woocommerce ul.products li.product .price ins,
ul.products li.product .price ins {
  text-decoration: none !important;
  color: var(--tlt-primary) !important;
}

/* Button in Grid */
.woocommerce ul.products li.product .button,
ul.products li.product .button {
  background: var(--tlt-dark) !important;
  color: #ffffff !important;
  margin: 0 16px !important;
  padding: 10px 14px !important;
  border-radius: var(--tlt-radius-sm) !important;
  font-size: 13px !important;
  font-weight: 700 !important;
  text-align: center !important;
  transition: all 0.2s ease !important;
  border: none !important;
}
.woocommerce ul.products li.product .button:hover,
ul.products li.product .button:hover {
  background: var(--tlt-primary) !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 15px rgba(224,36,36,0.25) !important;
}

/* 3. SINGLE PRODUCT PAGE (Avada Ecommerce Single) */
.woocommerce div.product {
  background: #ffffff;
  border: 1px solid var(--tlt-gray-200);
  border-radius: var(--tlt-radius-lg);
  padding: 35px 30px;
  box-shadow: var(--tlt-shadow-sm);
  margin-bottom: 40px;
}

.woocommerce div.product div.images {
  margin-bottom: 30px;
}
.woocommerce div.product div.images img {
  border-radius: var(--tlt-radius-md);
  border: 1px solid var(--tlt-gray-200);
  background: #f8fafc;
  padding: 15px;
}

.woocommerce div.product .product_title {
  font-size: 24px !important;
  font-weight: 900 !important;
  line-height: 1.4 !important;
  color: var(--tlt-dark) !important;
  margin: 0 0 16px 0 !important;
}

.woocommerce div.product p.price,
.woocommerce div.product span.price {
  color: var(--tlt-primary) !important;
  font-size: 24px !important;
  font-weight: 900 !important;
  margin-bottom: 20px !important;
}
.woocommerce div.product p.price del {
  color: var(--tlt-gray-400) !important;
  font-size: 16px !important;
}

/* Single Add to Cart & Buttons */
.woocommerce div.product form.cart {
  margin-bottom: 25px !important;
  padding: 20px !important;
  background: #f8fafc !important;
  border-radius: var(--tlt-radius-md) !important;
  border: 1px solid var(--tlt-gray-200) !important;
}
.woocommerce div.product form.cart .button {
  background: var(--tlt-primary) !important;
  color: #fff !important;
  padding: 14px 28px !important;
  border-radius: var(--tlt-radius-sm) !important;
  font-weight: 800 !important;
  font-size: 15px !important;
  box-shadow: 0 8px 20px rgba(224,36,36,0.3) !important;
  transition: all 0.25s ease !important;
}
.woocommerce div.product form.cart .button:hover {
  background: var(--tlt-primary-dark) !important;
  transform: translateY(-2px) !important;
}

/* Tabs */
.woocommerce div.product .woocommerce-tabs ul.tabs {
  border-bottom: 2px solid var(--tlt-gray-200) !important;
  padding: 0 !important;
  margin: 30px 0 20px 0 !important;
}
.woocommerce div.product .woocommerce-tabs ul.tabs li {
  background: none !important;
  border: none !important;
  border-bottom: 3px solid transparent !important;
  margin: 0 0 -2px 25px !important;
  padding: 10px 4px !important;
}
.woocommerce div.product .woocommerce-tabs ul.tabs li.active {
  border-bottom-color: var(--tlt-primary) !important;
}
.woocommerce div.product .woocommerce-tabs ul.tabs li a {
  color: var(--tlt-gray-600) !important;
  font-weight: 700 !important;
  font-size: 16px !important;
}
.woocommerce div.product .woocommerce-tabs ul.tabs li.active a {
  color: var(--tlt-primary) !important;
}

/* 4. SIDEBAR & WIDGETS */
.widget-area .widget {
  background: #ffffff;
  border: 1px solid var(--tlt-gray-200);
  border-radius: var(--tlt-radius-md);
  padding: 22px;
  margin-bottom: 25px;
  box-shadow: var(--tlt-shadow-sm);
}
.widget-area .widget .widget-title,
.widget-area .widget h2,
.widget-area .widget h3 {
  font-size: 16px !important;
  font-weight: 800 !important;
  color: var(--tlt-dark) !important;
  border-right: 4px solid var(--tlt-primary) !important;
  padding-right: 12px !important;
  margin: 0 0 16px 0 !important;
}

/* 5. BREADCRUMBS */
.woocommerce-breadcrumb {
  font-size: 13px !important;
  color: var(--tlt-gray-600) !important;
  margin-bottom: 25px !important;
  padding: 12px 18px !important;
  background: #ffffff !important;
  border: 1px solid var(--tlt-gray-200) !important;
  border-radius: var(--tlt-radius-sm) !important;
}
.woocommerce-breadcrumb a {
  color: var(--tlt-gray-800) !important;
  text-decoration: none !important;
  font-weight: 600 !important;
}
.woocommerce-breadcrumb a:hover {
  color: var(--tlt-primary) !important;
}

/* 6. PAGINATION */
.woocommerce nav.woocommerce-pagination ul {
  border: none !important;
  display: flex !important;
  justify-content: center !important;
  gap: 8px !important;
}
.woocommerce nav.woocommerce-pagination ul li {
  border: none !important;
}
.woocommerce nav.woocommerce-pagination ul li a,
.woocommerce nav.woocommerce-pagination ul li span {
  border: 1px solid var(--tlt-gray-200) !important;
  border-radius: var(--tlt-radius-sm) !important;
  padding: 8px 16px !important;
  font-weight: 700 !important;
  color: var(--tlt-dark) !important;
}
.woocommerce nav.woocommerce-pagination ul li span.current {
  background: var(--tlt-primary) !important;
  color: #fff !important;
  border-color: var(--tlt-primary) !important;
}
</style>
<?php
});
