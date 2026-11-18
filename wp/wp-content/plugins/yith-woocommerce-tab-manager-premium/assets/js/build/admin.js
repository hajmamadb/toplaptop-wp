/******/ (() => { // webpackBootstrap
/******/ 	"use strict";

;// ./assets/js/src/admin/config.js
var $ = jQuery;
var scrollTo = function scrollTo(element) {
  var main = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
  var container = false === main ? $('html,body') : main;
  container.animate({
    scrollTop: element.offset().top - 100
  }, 'slow');
};
;// ./assets/js/src/admin/field-dependencies/field-dependecies.js


function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }

var YWTM_FieldDependencies = /*#__PURE__*/function () {
  function YWTM_FieldDependencies(containerClass, parentClassField) {
    _classCallCheck(this, YWTM_FieldDependencies);
    this.containerClass = containerClass;
    this.parentClass = parentClassField;
    this.target_deps = [];
    this.target_deps_id = [];
  }
  return _createClass(YWTM_FieldDependencies, [{
    key: "init",
    value: function init() {
      this.fields = this._getFields();
      this._initFields();
      this.handleFieldsChange();
    }
  }, {
    key: "_getFields",
    value: function _getFields() {
      return $(this.containerClass).find('[data-ywtm-deps]');
    }
  }, {
    key: "_initFields",
    value: function _initFields() {
      var self = this;
      this.fields.each(function () {
        var t = $(this);
        self.handleField(t);
      });
      $(document).trigger('ywtm-init-fields', [self]);
    }
  }, {
    key: "getTargetDep",
    value: function getTargetDep(dep) {
      return $('#' + dep.id);
    }
  }, {
    key: "getDeps",
    value: function getDeps(field) {
      return field.data('ywtm-deps');
    }
  }, {
    key: "handleField",
    value: function handleField(field) {
      var self = this,
        parent = field.closest(self.parentClass),
        deps = this.getDeps(field),
        show = true;
      $.each(deps, function (i, dep) {
        var target_dep = self.getTargetDep(dep),
          compare = typeof dep.compare === 'undefined' ? '==' : dep.compare,
          property = typeof dep.property === 'undefined' ? false : dep.property,
          current_value;

        // it's a radio button.
        if (target_dep.hasClass('yith-plugin-fw-radio')) {
          current_value = target_dep.find('input[type="radio"]').filter(':checked').val();
        } else if (target_dep.hasClass('yith-plugin-fw-select') || target_dep.hasClass('yith-post-search') || target_dep.hasClass('wc-enhanced-select')) {
          current_value = target_dep.val();
        } else if (target_dep.hasClass('yith-plugin-fw-onoff-container')) {
          current_value = target_dep.find('input[type="checkbox"]').is(':checked') ? 'yes' : 'no';
        } else {
          current_value = target_dep.is(':checked') ? 'yes' : 'no';
        }
        if (self.target_deps_id.indexOf(dep.id) < 0) {
          self.target_deps.push(target_dep);
          self.target_deps_id.push(dep.id);
        }
        if (show) {
          if (property) {
            if (property === 'length') {
              switch (compare) {
                case '==':
                case '===':
                  show = current_value.length == dep.value;
                  break;
                case '>':
                  show = current_value.length > dep.value;
                  break;
                case '<':
                  show = current_value.length < dep.value;
                  break;
                case '>=':
                  show = current_value.length >= dep.value;
                  break;
                case '<=':
                  show = current_value.length <= dep.value;
                  break;
              }
            }
          } else {
            var value = dep.value.split(',');
            switch (compare) {
              case '==':
              case '===':
                show = value.indexOf(current_value) >= 0;
                break;
              case '!=':
              case '!==':
                show = value.indexOf(current_value) < 0;
                break;
            }
          }
        }
      });
      if (show) {
        parent.show();
      } else {
        parent.hide();
      }
    }
  }, {
    key: "handleFieldsChange",
    value: function handleFieldsChange() {
      var self = this;
      $.each(self.target_deps, function (i, field) {
        field.on('change', function () {
          self._initFields();
        });
      });
    }
  }]);
}();

;// ./assets/js/src/admin/metabox/index.js
function metabox_typeof(o) { "@babel/helpers - typeof"; return metabox_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, metabox_typeof(o); }
function metabox_classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function metabox_defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, metabox_toPropertyKey(o.key), o); } }
function metabox_createClass(e, r, t) { return r && metabox_defineProperties(e.prototype, r), t && metabox_defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function metabox_toPropertyKey(t) { var i = metabox_toPrimitive(t, "string"); return "symbol" == metabox_typeof(i) ? i : i + ""; }
function metabox_toPrimitive(t, r) { if ("object" != metabox_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != metabox_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }


var YWTM_Metabox = /*#__PURE__*/function () {
  function YWTM_Metabox() {
    metabox_classCallCheck(this, YWTM_Metabox);
    this.elementDOM = $(document).find('.yith-plugin-ui--ywtm_tab-post_type');
    if (this.elementDOM.length) {
      this.elementDOM.find('form#post').submit(this.checkErrors.bind(this));
      this.elementDOM.find('form#post').find('input,select,checkbox,textarea').on('change', this.setHasChanges.bind(this));
      this.elementDOM.find('#yith-plugin-fw__back-to-wp-list').on('click', this.showWaringMessage.bind(this));
      this.initFloatingSaveButton();
      new YWTM_FieldDependencies('.yith-plugin-ui--ywtm_tab-post_type', '.the-metabox').init();
      this.elementDOM.on('change', '#_ywtm_layout_type', this.changeFieldLabel.bind(this));
      this.hasChanges = false;
      var v = this.elementDOM.find('#_ywtm_layout_type').val();
      this.changeFieldLabel({
        target: {
          id: '#_ywtm_layout_type',
          value: v
        }
      });
      $(document.body).trigger('wc-enhanced-select-init');
    }
  }
  return metabox_createClass(YWTM_Metabox, [{
    key: "initFloatingSaveButton",
    value: function initFloatingSaveButton() {
      if (typeof adminpage !== 'undefined' && ['post-php', 'post-new-php'].indexOf(adminpage) >= 0) {
        var postTypeSaving = {
          dom: {
            actions: $('#ywtm-post-type__actions'),
            save: $('#ywtm-post-type__save'),
            floatSave: $('#ywtm-post-type__float-save')
          },
          init: function init() {
            var self = postTypeSaving;
            if (self.dom.save.length) {
              self.dom.save.on('click', self.onSaveClick);
              self.dom.floatSave.on('click', self.onFloatSaveClick);
              document.addEventListener('scroll', self.handleFloatSaveVisibility, {
                passive: true
              });
              $(window).on('resize', self.handleFloatSaveVisibility);
              self.handleFloatSaveVisibility();
            }
          },
          isInViewport: function isInViewport(el) {
            var rect = el.get(0).getBoundingClientRect(),
              viewport = {
                width: window.innerWidth || document.documentElement.clientWidth,
                height: window.innerHeight || document.documentElement.clientHeight
              };
            return rect.top >= 0 && rect.left >= 0 && rect.top <= viewport.height && rect.left <= viewport.width;
          },
          handleFloatSaveVisibility: function handleFloatSaveVisibility() {
            if (postTypeSaving.isInViewport(postTypeSaving.dom.save)) {
              postTypeSaving.dom.floatSave.removeClass('visible');
            } else {
              postTypeSaving.dom.floatSave.addClass('visible');
            }
          },
          onSaveClick: function onSaveClick(event) {
            $(window).off('beforeunload.edit-post');
            $(event.target).block({
              message: null,
              overlayCSS: {
                background: 'transparent',
                opacity: 0.6
              }
            });
          },
          onFloatSaveClick: function onFloatSaveClick() {
            postTypeSaving.dom.save.trigger('click');
          }
        };
        postTypeSaving.init();
      }
    }
  }, {
    key: "checkErrors",
    value: function checkErrors(event) {
      var required_field = this.elementDOM.find('.yith-plugin-fw--required'),
        required_only_one_field = this.elementDOM.find('.ywtm-only-one-required'),
        title = $(document).find('#_ywtm_tab_title'),
        send = true,
        row = false,
        self = this;
      if ('' === title.val()) {
        send = false;
        row = title;
        title.parent().addClass('ywtm_required_check');
      } else {
        var postTitle = $(document).find('input[name="post_title"]');
        postTitle.val(title.val());
      }
      if (send) {
        required_field.each(function () {
          var current_row = $(this);
          if (current_row.is(':visible')) {
            var select = current_row.find('select');
            if (select.length) {
              var selected = select.find(':selected');
              if (selected.length === 0) {
                send = false;
                if (!row) {
                  row = current_row;
                }
                current_row.addClass('ywtm_required_check');
              }
            } else {
              var text;
              if (current_row.parent().hasClass('ywtm-modal-editor')) {
                text = current_row.find('textarea');
              } else {
                text = current_row.find('input,textarea');
              }
              if (text.length) {
                if (text.val() === '') {
                  send = false;
                  if (!row) {
                    row = current_row;
                  }
                  current_row.addClass('ywtm_required_check');
                }
              }
            }
          }
        });
      }
      if (send) {
        required_only_one_field.each(function () {
          if ($(this).is(':visible')) {
            var allEmpty = true,
              elements = $(this).find('input');
            elements.each(function () {
              var value = $(this).val();
              if ('' !== value) {
                allEmpty = false;
                return false;
              }
            });
            if (allEmpty) {
              $(this).find('.ywtm-video-single-row:first-child').addClass('ywtm_required_check');
              row = $(this);
              send = false;
            }
          }
        });
      }
      if (!send) {
        $('#ywtm-post-type__save').unblock();
        event.preventDefault();
        scrollTo(row);
        var selects = this.elementDOM.find('.yith-plugin-fw--required.ywtm_required_check select');
        selects.on('select2:open', function (e) {
          $(this).parents('.yith-plugin-fw--required').removeClass('ywtm_required_check');
          event.stopImmediatePropagation();
        });
        title.on('change', function () {
          title.parent().removeClass('ywtm_required_check');
        });
      }
    }
  }, {
    key: "setHasChanges",
    value: function setHasChanges(event) {
      var required = $(event.target).closest('.yith-plugin-fw--required');
      if (required.length && required.hasClass('ywtm_required_check')) {
        required.removeClass('ywtm_required_check');
      }
      this.hasChanges = true;
    }
  }, {
    key: "showWaringMessage",
    value: function showWaringMessage(event) {
      if (this.hasChanges) {
        event.preventDefault();
        var url = $(event.target).attr('href');
        yith.ui.confirm({
          title: ywtm_admin_args.message_alert.title,
          message: ywtm_admin_args.message_alert.desc,
          confirmButton: ywtm_admin_args.message_alert.confirmButton,
          closeAfterConfirm: false,
          classes: {
            wrap: 'ywtm-warning-popup'
          },
          onConfirm: function onConfirm() {
            window.location.href = url;
          }
        });
      }
    }
  }, {
    key: "changeFieldLabel",
    value: function changeFieldLabel(event) {
      var value = event.target.value;
      if (typeof ywtm_admin_args.fields_labels['_ywtm_tab_description'][value] !== 'undefined') {
        this.elementDOM.find('#ywtm-modal-editor-_ywtm_tab_additional_content').closest('.the-metabox').find('.description').html(ywtm_admin_args.fields_labels['_ywtm_tab_description'][value]);
      }
    }
  }]);
}();

;// ./assets/js/src/admin/tab-table/index.js
function tab_table_typeof(o) { "@babel/helpers - typeof"; return tab_table_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, tab_table_typeof(o); }
function tab_table_classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function tab_table_defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, tab_table_toPropertyKey(o.key), o); } }
function tab_table_createClass(e, r, t) { return r && tab_table_defineProperties(e.prototype, r), t && tab_table_defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function tab_table_toPropertyKey(t) { var i = tab_table_toPrimitive(t, "string"); return "symbol" == tab_table_typeof(i) ? i : i + ""; }
function tab_table_toPrimitive(t, r) { if ("object" != tab_table_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != tab_table_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }

var YWTM_Tabs_List = /*#__PURE__*/function () {
  function YWTM_Tabs_List() {
    tab_table_classCallCheck(this, YWTM_Tabs_List);
    this.table = $(document).find('.yith-plugin-ui--ywtm_tab-post_type .wp-list-table tbody');
    if (this.table.length) {
      this.table.on('click', 'a.row-title', this.preserveDefaultClick.bind(this));
      this.initSortableRows();
      this.table.find('tr td.enable .ywtm-toggle-enabled input').on('change', this.handleStatusToggle.bind(this));
    }
  }
  return tab_table_createClass(YWTM_Tabs_List, [{
    key: "preserveDefaultClick",
    value: function preserveDefaultClick(event) {
      event.stopPropagation();
    }
  }, {
    key: "initSortableRows",
    value: function initSortableRows() {
      this.table.sortable({
        axis: "y",
        items: 'tr:not(.ywtm-not-sortable)',
        update: function update(event, ui) {
          var tabs_to_sort = $(this).sortable('toArray').map(function (value) {
            return value.replace('post-', '');
          }).toString();
          $.ajax({
            type: 'POST',
            data: {
              action: ywtm_admin_args.actions.sort_tabs,
              security: ywtm_admin_args.nonces.sort_tabs,
              tabs_to_sort: tabs_to_sort
            },
            url: ywtm_admin_args.ajax_url,
            success: function success(response) {
              if (typeof response.error !== 'undefined') {
                alert(response.error);
              }
            }
          });
        }
      });
    }
  }, {
    key: "handleStatusToggle",
    value: function handleStatusToggle(event) {
      var $this = $(event.target),
        enabled = $this.val() === 'yes' ? 'yes' : 'no',
        container = $this.closest('.ywtm-toggle-enabled'),
        tabID = container.data('tab-id');
      $.ajax({
        type: 'POST',
        data: {
          action: ywtm_admin_args.actions.toggle_show_tab,
          security: ywtm_admin_args.nonces.toggle_show_tab,
          tab_id: tabID,
          enabled: enabled
        },
        url: ywtm_admin_args.ajax_url,
        success: function success(response) {
          if (typeof response.error !== 'undefined') {
            alert(response.error);
          }
        }
      });
    }
  }]);
}();

;// ./assets/js/src/admin/custom-fields/modal-editor/popup.js
function popup_typeof(o) { "@babel/helpers - typeof"; return popup_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, popup_typeof(o); }
function popup_classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function popup_defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, popup_toPropertyKey(o.key), o); } }
function popup_createClass(e, r, t) { return r && popup_defineProperties(e.prototype, r), t && popup_defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function popup_toPropertyKey(t) { var i = popup_toPrimitive(t, "string"); return "symbol" == popup_typeof(i) ? i : i + ""; }
function popup_toPrimitive(t, r) { if ("object" != popup_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != popup_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
var YWTM_Modal_Editor_Popup = /*#__PURE__*/function () {
  function YWTM_Modal_Editor_Popup(element) {
    popup_classCallCheck(this, YWTM_Modal_Editor_Popup);
    this.main = element.find('.ywtm-modal-editor-popup');
    this.wrapper = element.find('.ywtm-modal-editor-popup-wrapper');
    this.closeIcon = element.find('.ywtm-modal-editor-popup-icon');
    this.init();
  }
  return popup_createClass(YWTM_Modal_Editor_Popup, [{
    key: "init",
    value: function init() {
      var _this = this;
      this.closeIcon.on('click', function () {
        _this.close();
      });
    }
  }, {
    key: "open",
    value: function open() {
      this.main.addClass('opened');
    }
  }, {
    key: "close",
    value: function close() {
      this.main.removeClass('opened');
    }
  }]);
}();

;// ./assets/js/src/admin/custom-fields/modal-editor/index.js
function modal_editor_typeof(o) { "@babel/helpers - typeof"; return modal_editor_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, modal_editor_typeof(o); }
function modal_editor_classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function modal_editor_defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, modal_editor_toPropertyKey(o.key), o); } }
function modal_editor_createClass(e, r, t) { return r && modal_editor_defineProperties(e.prototype, r), t && modal_editor_defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function modal_editor_toPropertyKey(t) { var i = modal_editor_toPrimitive(t, "string"); return "symbol" == modal_editor_typeof(i) ? i : i + ""; }
function modal_editor_toPrimitive(t, r) { if ("object" != modal_editor_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != modal_editor_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }


var YWTM_ModalEditor = /*#__PURE__*/function () {
  function YWTM_ModalEditor(element) {
    modal_editor_classCallCheck(this, YWTM_ModalEditor);
    this.element = $(element);
    this.ID = this.element.data('id');
    this.preview = this.element.find('.ywtm-edit-content-wrapper');
    this.element.on('click', '.ywtm-edit-content', this.openModalEditor.bind(this));
    this.popup = new YWTM_Modal_Editor_Popup(this.element);
    this.element.on('click', '.ywtm-set-content-btn', this.updatePreview.bind(this));
  }
  return modal_editor_createClass(YWTM_ModalEditor, [{
    key: "openModalEditor",
    value: function openModalEditor(event) {
      event.preventDefault();
      this.popup.open();
    }
  }, {
    key: "updatePreview",
    value: function updatePreview(event) {
      event.preventDefault();
      var self = this,
        val = this.getEditorContent();
      self.preview.html(val);
      self.popup.close();
    }
  }, {
    key: "getEditorContent",
    value: function getEditorContent() {
      var mce_editor = tinymce.get(this.ID);
      var val;
      if (mce_editor) {
        val = wp.editor.getContent(this.ID); // Visual tab is active
      } else {
        val = $('#' + this.ID).val(); // HTML tab is active
      }
      return val;
    }
  }]);
}();

;// ./assets/js/src/admin/custom-fields/icon-list.js
function icon_list_typeof(o) { "@babel/helpers - typeof"; return icon_list_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, icon_list_typeof(o); }
function icon_list_classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function icon_list_defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, icon_list_toPropertyKey(o.key), o); } }
function icon_list_createClass(e, r, t) { return r && icon_list_defineProperties(e.prototype, r), t && icon_list_defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function icon_list_toPropertyKey(t) { var i = icon_list_toPrimitive(t, "string"); return "symbol" == icon_list_typeof(i) ? i : i + ""; }
function icon_list_toPrimitive(t, r) { if ("object" != icon_list_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != icon_list_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }

var YWTM_Icon_List = /*#__PURE__*/function () {
  function YWTM_Icon_List(element) {
    icon_list_classCallCheck(this, YWTM_Icon_List);
    this.element = $(element);
    this.wrapper = this.element.closest('.the-metabox');
    this.element.on('change', '.ywtm_icon_list_type', this.handleChangeType.bind(this));
    var v = this.element.find('.ywtm_icon_list_type').val();
    this.handleChangeType({
      target: {
        value: v
      }
    });
  }
  return icon_list_createClass(YWTM_Icon_List, [{
    key: "handleChangeType",
    value: function handleChangeType(event) {
      var value = event.target.value;
      var iconsWrapper = this.wrapper.find('.yith-plugin-fw-icons-field-wrapper');
      var updloadWrapper = this.wrapper.find('.yith-plugin-fw-media-field-wrapper');
      if ('icon' === value) {
        iconsWrapper.show();
        updloadWrapper.hide();
      } else if ('upload' === value) {
        updloadWrapper.show();
        iconsWrapper.hide();
      } else {
        updloadWrapper.hide();
        iconsWrapper.hide();
      }
    }
  }]);
}();

;// ./assets/js/src/admin/custom-fields/video-rows.js
function video_rows_typeof(o) { "@babel/helpers - typeof"; return video_rows_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, video_rows_typeof(o); }
function video_rows_classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function video_rows_defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, video_rows_toPropertyKey(o.key), o); } }
function video_rows_createClass(e, r, t) { return r && video_rows_defineProperties(e.prototype, r), t && video_rows_defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function video_rows_toPropertyKey(t) { var i = video_rows_toPrimitive(t, "string"); return "symbol" == video_rows_typeof(i) ? i : i + ""; }
function video_rows_toPrimitive(t, r) { if ("object" != video_rows_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != video_rows_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }

var YWTM_Video_Rows = /*#__PURE__*/function () {
  function YWTM_Video_Rows(element) {
    video_rows_classCallCheck(this, YWTM_Video_Rows);
    this.element = $(element);
    this.templateID = 'ywtm-new-video-row-' + this.element.find('.ywtm-videos-field-wrapper').attr('id');
    this.element.on('click', '.ywtm-add-video', this.addNewVideo.bind(this));
    this._initDeleteRow();
  }
  return video_rows_createClass(YWTM_Video_Rows, [{
    key: "addNewVideo",
    value: function addNewVideo(event) {
      event.preventDefault();
      var maxIndex = 0,
        rows = this.element.find('.ywtm-video-single-grid'),
        template;
      rows.each(function () {
        var index = $(this).data('index');
        if (index > maxIndex) {
          maxIndex = index;
        }
      });
      template = wp.template(this.templateID);
      var new_row = $(template({
        index: maxIndex + 1
      }));
      new_row.appendTo(this.element.find('.ywtm-videos-field-list'));
      $(document.body).trigger('wc-enhanced-select-init');
      $(document.body).trigger('yith-framework-enhanced-select-init');
      scrollTo(new_row, this.element.find('.ywtm-video-fields-overflow'));
      this._initDeleteRow();
    }
  }, {
    key: "_initDeleteRow",
    value: function _initDeleteRow() {
      this.element.find('.ywtm-video-delete').on('click', this.handleDeleteRow.bind(this));
    }
  }, {
    key: "handleDeleteRow",
    value: function handleDeleteRow(event) {
      event.preventDefault();
      $(event.target).parent().remove();
    }
  }]);
}();

;// ./assets/js/src/admin/custom-fields/media-gallery.js
function media_gallery_typeof(o) { "@babel/helpers - typeof"; return media_gallery_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, media_gallery_typeof(o); }
function media_gallery_classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function media_gallery_defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, media_gallery_toPropertyKey(o.key), o); } }
function media_gallery_createClass(e, r, t) { return r && media_gallery_defineProperties(e.prototype, r), t && media_gallery_defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function media_gallery_toPropertyKey(t) { var i = media_gallery_toPrimitive(t, "string"); return "symbol" == media_gallery_typeof(i) ? i : i + ""; }
function media_gallery_toPrimitive(t, r) { if ("object" != media_gallery_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != media_gallery_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }

var YWTM_MediaGallery = /*#__PURE__*/function () {
  function YWTM_MediaGallery(element) {
    media_gallery_classCallCheck(this, YWTM_MediaGallery);
    this.gallery = $(element);
    this.isDragging = false;
    this.uploader = {
      instance: null,
      wrapper: null
    };
    this.emptyState = this.gallery.find('.ywtm-media-gallery-empty-state');
    this.gallery.on('click', '.ywtm-media-gallery__preview__upload__link,.ywtm-media-gallery__add_new', this.openMedia.bind(this));
    this.gallery.on('click', '.ywtm-media-gallery__delete', this.deleteImage.bind(this));
    this.gallery.on('dragover', '.ywtm-media-gallery-empty-state', this.onDragOver.bind(this));
    this.gallery.on('dragenter', '.ywtm-media-gallery-empty-state', this.onDragEnter.bind(this));
    this.gallery.on('dragleave', '.ywtm-media-gallery-empty-state', this.onDragLeave.bind(this));
    this.gallery.on('drop', '.ywtm-media-gallery-empty-state', this.onDrop.bind(this));
    this.templateID = 'ywtm-new-media-gallery-image-' + this.gallery.find('.ywtm-media-gallery-field-wrapper').attr('id');
  }
  return media_gallery_createClass(YWTM_MediaGallery, [{
    key: "init",
    value: function init(wrapper) {
      this.uploader.wrapper = wrapper;
      if (!this.uploader.instance) {
        var mediaUploaderStates = [new wp.media.controller.Library({
          library: wp.media.query(),
          multiple: true,
          filterable: 'uploaded'
        })];
        this.uploader.instance = wp.media.frames.downloadable_file = wp.media({
          multiple: true,
          states: mediaUploaderStates,
          library: {
            type: ''
          },
          button: {
            text: ywtm_admin_args.media_gallery.button
          }
        });
        this.uploader.instance.on('select', this.triggerMediaChange.bind(this));
      }
    }
  }, {
    key: "open",
    value: function open(wrapper) {
      this.init(wrapper);
      this.uploader.instance.open();
    }
  }, {
    key: "openMedia",
    value: function openMedia(event) {
      event.preventDefault();
      this.open($(this).closest('.ywtm-media-gallery-field-wrapper'));
    }
  }, {
    key: "triggerMediaChange",
    value: function triggerMediaChange(event) {
      var attachments = this.uploader.instance.state().get('selection').toArray();
      var hidden = this.gallery.find('.ywtm_media_ids');
      var values = hidden.val();
      var self = this;
      $.each(attachments, function (i, attachment) {
        attachment = attachment.toJSON();
        if (attachment && 'image' === attachment.type.toLowerCase()) {
          values += '' !== values ? ',' + attachment.id : attachment.id;
          hidden.val(values);
          self.addImagePreview(attachment);
        }
      });
    }
  }, {
    key: "triggerMediaUploaded",
    value: function triggerMediaUploaded(file) {
      var hidden = this.gallery.find('.ywtm_media_ids');
      var values = hidden.val();
      if (file) {
        values += '' !== values ? ',' + file.id : file.id;
        hidden.val(values);
        this.addImagePreview(file);
      }
    }
  }, {
    key: "addImagePreview",
    value: function addImagePreview(attachment) {
      var maxIndex = 0,
        rows = this.gallery.find('.ywtm-media-gallery__image_preview'),
        template;
      rows.each(function () {
        var index = $(this).data('index');
        if (index > maxIndex) {
          maxIndex = index;
        }
      });
      template = wp.template(this.templateID);
      var new_row = $(template({
        index: maxIndex + 1,
        imageID: attachment.id,
        imageURL: attachment.url,
        imageALT: attachment.alt
      }));
      this.toggleEmptyState(false);
      new_row.appendTo(this.gallery.find('.ywtm-media-gallery__media_list'));
    }
  }, {
    key: "toggleEmptyState",
    value: function toggleEmptyState(show) {
      if (show) {
        this.gallery.find('.ywtm-media-gallery-empty-state').removeClass('hide');
        this.gallery.find('.ywtm-media-gallery__add_new').addClass('hide');
      } else {
        this.gallery.find('.ywtm-media-gallery-empty-state').addClass('hide');
        this.gallery.find('.ywtm-media-gallery__add_new').removeClass('hide');
      }
    }
  }, {
    key: "deleteImage",
    value: function deleteImage(event) {
      var element = $(event.target).parent();
      var imageToDelete = element.data('image_id');
      var hidden = this.gallery.find('.ywtm_media_ids');
      var values = hidden.val().split(',');
      values = values.filter(function (val) {
        return val != imageToDelete;
      }).join(',');
      hidden.val(values);
      element.remove();
      if (values === '') {
        this.toggleEmptyState(true);
      }
    }
  }, {
    key: "onDragOver",
    value: function onDragOver(event) {
      event.preventDefault();
    }
  }, {
    key: "onDragEnter",
    value: function onDragEnter(event) {
      event.preventDefault();
      if (!event.dataTransfer) {
        event.dataTransfer = event.originalEvent.dataTransfer;
      }
      event.dataTransfer.dropEffect = "copy";
      this.emptyState.addClass('yith-plugin-fw--is-dragging');
    }
  }, {
    key: "onDragLeave",
    value: function onDragLeave(event) {
      event.preventDefault();
      this.emptyState.removeClass('yith-plugin-fw--is-dragging');
    }
  }, {
    key: "onDrop",
    value: function onDrop(event) {
      event.preventDefault();
      this.emptyState.removeClass('yith-plugin-fw--is-dragging');
      this.emptyState.addClass('yith-plugin-fw--is-loading');
      if (!event.dataTransfer) {
        event.dataTransfer = event.originalEvent.dataTransfer;
      }
      var files = event.dataTransfer.files;
      var self = this,
        wrapper = this.gallery.parent(),
        errorNotices = wrapper.find('.yith-plugin-fw__notice'),
        onFinish = function onFinish() {
          self.emptyState.removeClass('yith-plugin-fw--is-loading');
        };
      errorNotices.remove();
      if (files.length > 1) {
        files = files[0];
      }
      if (!files.length) {
        this.addErrorNotice(wrapper, yith_framework_fw_fields.i18n.noFileError);
        onFinish();
      } else if (files[0].type.toLowerCase().indexOf('image') === -1) {
        this.addErrorNotice(wrapper, ywtm_admin_args.media_gallery.noImageError);
        onFinish();
      } else if ('mediaUtils' in wp && 'uploadMedia' in wp.mediaUtils) {
        wp.mediaUtils.uploadMedia({
          filesList: files,
          onFileChange: function onFileChange(data) {
            // Check for the ID, since uploadMedia triggers onFileChange at the start with a temporary blob URL to allow previewing it before uploading.
            if (data && data.length && data[0] && 'id' in data[0]) {
              self.triggerMediaUploaded(data[0]);
              onFinish();
            }
          },
          onError: function onError(data) {
            console.error(data);
            onFinish();
            self.addErrorNotice(wrapper, data.message);
          }
        });
      } else {
        this.addErrorNotice(wrapper, yith_framework_fw_fields.i18n.cannotDropError);
        onFinish();
      }
    }
  }, {
    key: "addErrorNotice",
    value: function addErrorNotice(wrapper, message) {
      var errorNotice = $('<div>');
      errorNotice.addClass(['yith-plugin-fw__notice', 'yith-plugin-fw__notice--error', 'yith-plugin-fw-animate__appear-from-top', 'yith-plugin-fw--inline']);
      errorNotice.html(message);
      errorNotice.append($('<span class="yith-plugin-fw__notice__dismiss"></span>'));
      wrapper.append(errorNotice);
    }
  }]);
}();

;// ./assets/js/src/admin/custom-fields/faqs.js
function faqs_typeof(o) { "@babel/helpers - typeof"; return faqs_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, faqs_typeof(o); }
function faqs_classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function faqs_defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, faqs_toPropertyKey(o.key), o); } }
function faqs_createClass(e, r, t) { return r && faqs_defineProperties(e.prototype, r), t && faqs_defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function faqs_toPropertyKey(t) { var i = faqs_toPrimitive(t, "string"); return "symbol" == faqs_typeof(i) ? i : i + ""; }
function faqs_toPrimitive(t, r) { if ("object" != faqs_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != faqs_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }

var YWTM_Faqs = /*#__PURE__*/function () {
  function YWTM_Faqs(element) {
    faqs_classCallCheck(this, YWTM_Faqs);
    this.element = $(element);
    this.templateID = 'ywtm-new-faq-row-' + this.element.attr('id');
    this.element.on('click', '.ywtm-add-faq', this.addFaq.bind(this));
    this.element.on('keyup', 'textarea[id$="question"]', this.updateToggleTitle.bind(this));
    this.element.on('click', '.ywtm-faq-delete', this.deleteFaq.bind(this));
    this.addRequiredClass();
    this.openEmptyState();
  }
  return faqs_createClass(YWTM_Faqs, [{
    key: "addRequiredClass",
    value: function addRequiredClass() {
      this.element.find('.yith-toggle-content-row').addClass('yith-plugin-fw--required');
    }
  }, {
    key: "openEmptyState",
    value: function openEmptyState() {
      this.element.find('.ywtm-custom-field-toggle.force-open').find('.yith-toggle-title').trigger('click');
    }
  }, {
    key: "addFaq",
    value: function addFaq(event) {
      event.preventDefault();
      var maxIndex = 0,
        rows = this.element.find('.ywtm-custom-field-toggle'),
        template;
      rows.each(function () {
        var index = $(this).data('index');
        if (index > maxIndex) {
          maxIndex = index;
        }
      });
      template = wp.template(this.templateID);
      var new_row = $(template({
        index: maxIndex + 1
      }));
      new_row.appendTo(this.element.find('.ywtm-faqs-field-list'));
      $(document).trigger('yith_fields_init');
      $(document.body).trigger('wc-enhanced-select-init');
      $(document.body).trigger('yith-framework-enhanced-select-init');
      scrollTo(new_row);
      new_row.find('.yith-toggle-title').trigger('click');
      this.addRequiredClass();
    }
  }, {
    key: "deleteFaq",
    value: function deleteFaq(event) {
      event.preventDefault();
      $(event.target).parent().remove();
    }
  }, {
    key: "updateToggleTitle",
    value: function updateToggleTitle(event) {
      var closestTitle = $(event.target).closest('.yith-toggle-content').parent().find('span.title');
      closestTitle.html(event.target.value);
    }
  }]);
}();

;// ./assets/js/src/admin/custom-fields/download.js
function download_typeof(o) { "@babel/helpers - typeof"; return download_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, download_typeof(o); }
function download_classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function download_defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, download_toPropertyKey(o.key), o); } }
function download_createClass(e, r, t) { return r && download_defineProperties(e.prototype, r), t && download_defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function download_toPropertyKey(t) { var i = download_toPrimitive(t, "string"); return "symbol" == download_typeof(i) ? i : i + ""; }
function download_toPrimitive(t, r) { if ("object" != download_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != download_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }

var YWTM_Download = /*#__PURE__*/function () {
  function YWTM_Download(element) {
    download_classCallCheck(this, YWTM_Download);
    this.element = $(element);
    this.templateID = 'ywtm-new-download-row-' + this.element.attr('id');
    this.element.on('click', '.ywtm-add-download', this.addDownload.bind(this));
    this.element.on('keyup', 'input[id$="name"]', this.onFieldNameChange.bind(this));
    this.element.on('click', '.ywtm-download-delete', this.deleteDownload.bind(this));
    this.addRequiredClass();
    this.openEmptyState();
  }
  return download_createClass(YWTM_Download, [{
    key: "addRequiredClass",
    value: function addRequiredClass() {
      this.element.find('.yith-toggle-content-row.text:first-child').addClass('yith-plugin-fw--required');
      this.element.find('.yith-toggle-content-row.media').addClass('yith-plugin-fw--required');
    }
  }, {
    key: "openEmptyState",
    value: function openEmptyState() {
      this.element.find('.ywtm-custom-field-toggle.force-open').find('.yith-toggle-title').trigger('click');
    }
  }, {
    key: "addDownload",
    value: function addDownload(event) {
      event.preventDefault();
      var maxIndex = 0,
        rows = this.element.find('.ywtm-custom-field-toggle'),
        template;
      rows.each(function () {
        var index = $(this).data('index');
        if (index > maxIndex) {
          maxIndex = index;
        }
      });
      template = wp.template(this.templateID);
      var new_row = $(template({
        index: maxIndex + 1
      }));
      new_row.appendTo(this.element.find('.ywtm-download-field-list'));
      this.updateToggleTitle(new_row);
      $(document).trigger('yith_fields_init');
      $(document.body).trigger('wc-enhanced-select-init');
      $(document.body).trigger('yith-framework-enhanced-select-init');
      scrollTo(new_row);
      new_row.find('.yith-toggle-title').trigger('click');
      this.addRequiredClass();
    }
  }, {
    key: "deleteDownload",
    value: function deleteDownload(event) {
      event.preventDefault();
      $(event.target).parent().remove();
      this.updateIndex();
    }
  }, {
    key: "onFieldNameChange",
    value: function onFieldNameChange(event) {
      var file = $(event.target).closest('.yith-toggle-content').parent().parent().parent().parent();
      this.updateToggleTitle(file);
    }
  }, {
    key: "updateToggleTitle",
    value: function updateToggleTitle(file) {
      var closestTitle = file.find('span.title'),
        index = file.data('index'),
        prefix = file.data('prefix'),
        title = file.find('input[id$="name"]').val();
      var fieldTitle = prefix.replace('%s', parseInt(index + 1));
      if (title !== '') {
        fieldTitle += ' - ';
      }
      fieldTitle += title;
      closestTitle.html(fieldTitle);
    }
  }, {
    key: "updateIndex",
    value: function updateIndex() {
      var files = this.element.find('.ywtm-custom-field-toggle').toArray(),
        self = this;
      $.each(files, function (i, file) {
        $(file).data('index', i);
        self.updateToggleTitle($(file));
      });
    }
  }]);
}();

;// ./assets/js/src/admin/custom-fields/index.js
function custom_fields_typeof(o) { "@babel/helpers - typeof"; return custom_fields_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, custom_fields_typeof(o); }
function custom_fields_defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, custom_fields_toPropertyKey(o.key), o); } }
function custom_fields_createClass(e, r, t) { return r && custom_fields_defineProperties(e.prototype, r), t && custom_fields_defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function custom_fields_toPropertyKey(t) { var i = custom_fields_toPrimitive(t, "string"); return "symbol" == custom_fields_typeof(i) ? i : i + ""; }
function custom_fields_toPrimitive(t, r) { if ("object" != custom_fields_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != custom_fields_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function custom_fields_classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }







var YWTM_CustomFields = /*#__PURE__*/custom_fields_createClass(function YWTM_CustomFields() {
  custom_fields_classCallCheck(this, YWTM_CustomFields);
  var modalEditors = $(document).find('.ywtm-modal-editor-field');
  var iconLists = $(document).find('.yith-plugin-fw-ywtm-icon-list-field-wrapper');
  var videoFields = $(document).find('.yith-plugin-fw-ywtm-video-field-wrapper');
  var mediaGalleryFields = $(document).find('.yith-plugin-fw-ywtm-media-gallery-field-wrapper');
  var faqFields = $(document).find('.ywtm-faqs-field-wrapper');
  var downloadFields = $(document).find('.ywtm-download-field-wrapper');
  if (modalEditors.length) {
    $.each(modalEditors, function (i, editor) {
      new YWTM_ModalEditor(editor);
    });
  }
  if (iconLists.length) {
    $.each(iconLists, function (i, iconList) {
      new YWTM_Icon_List(iconList);
    });
  }
  if (videoFields.length) {
    $.each(videoFields, function (i, videoField) {
      new YWTM_Video_Rows(videoField);
    });
  }
  if (mediaGalleryFields.length) {
    $.each(mediaGalleryFields, function (i, mediaGalleryField) {
      new YWTM_MediaGallery(mediaGalleryField);
    });
  }
  if (faqFields.length) {
    $.each(faqFields, function (i, faqField) {
      new YWTM_Faqs(faqField);
    });
  }
  if (downloadFields.length) {
    $.each(downloadFields, function (i, downloadField) {
      new YWTM_Download(downloadField);
    });
  }
});

;// ./assets/js/src/admin/index.js





jQuery(document).ready(function ($) {
  if ($(document).find('.yith-plugin-ui--ywtm_tab-post_type').length) {
    new YWTM_Tabs_List();
    new YWTM_Metabox();
  }
  new YWTM_CustomFields();
});
/******/ })()
;
//# sourceMappingURL=admin.js.map