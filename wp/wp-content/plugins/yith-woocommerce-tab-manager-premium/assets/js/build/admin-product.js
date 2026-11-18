/******/ (() => { // webpackBootstrap
/******/ 	"use strict";

;// ./assets/js/src/admin-product/product-wc-tabs-metabox.js
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
var YWTM_Product_WC_Metabox = /*#__PURE__*/function () {
  function YWTM_Product_WC_Metabox(element) {
    _classCallCheck(this, YWTM_Product_WC_Metabox);
    this.element = jQuery(element);
    this.element.on('change', 'input[id^="ywtm_override"]', this.showContent.bind(this));
    jQuery(document).find('input[id^="ywtm_override"]').trigger('change');
  }
  return _createClass(YWTM_Product_WC_Metabox, [{
    key: "showContent",
    value: function showContent(event) {
      var field = jQuery(event.target);
      if (field.is(':checked')) {
        this.element.find('.ywtm-product-edit-tab-fields-wrapper').show();
      } else {
        this.element.find('.ywtm-product-edit-tab-fields-wrapper').hide();
      }
    }
  }]);
}();

;// ./assets/js/src/admin-product/index.js



jQuery(document).ready(function ($) {
  if ($(document).find('#woocommerce-product-data').length) {
    var productTabs = $(document).find('.ywtm-product-edit-tab:not(.disable)');
    if (productTabs.length) {
      $.each(productTabs, function (i, productTab) {
        new YWTM_Product_WC_Metabox(productTab);
      });
    }
  }
  var toggleMapWidthField = function toggleMapWidthField(element) {
    var parent = element.closest('.ywtm-tab-panel-content');
    if (element.is(':checked')) {
      parent.find('.form-field.map-width').hide();
    } else {
      parent.find('.form-field.map-width').show();
    }
  };
  var mapFields = $(document).find('.product_data .ywtm-tab-panel-content .map-is-full input[type="checkbox"]');
  if (mapFields.length) {
    $.each(mapFields, function (i, mapField) {
      toggleMapWidthField($(mapField));
    });
  }
  $(document).on('change', '.product_data .ywtm-tab-panel-content .map-is-full input[type="checkbox"]', function (e) {
    toggleMapWidthField($(this));
  });
});
/******/ })()
;
//# sourceMappingURL=admin-product.js.map