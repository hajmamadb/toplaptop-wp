/*
 * AI chatbot setting js
 */
function wpsc_ai_chatbot_settings(is_humbargar = false) {
  if (is_humbargar) {
    wpsc_toggle_humbargar();
  }

  jQuery(".wpsc-setting-nav, .wpsc-humbargar-menu-item").removeClass("active");
  jQuery(
    ".wpsc-setting-nav.ai-chatbot-setting, .wpsc-humbargar-menu-item.ai-chatbot-setting",
  ).addClass("active");
  jQuery(".wpsc-humbargar-title").html(
    supportcandy.humbargar_titles.ai_chatbot_setting,
  );

  if (supportcandy.current_section !== "ai-chatbot-setting") {
    supportcandy.current_section = "ai-chatbot-setting";
    supportcandy.current_tab = "general";
  }

  window.history.replaceState(
    {},
    null,
    "admin.php?page=wpsc-settings&section=" + supportcandy.current_section,
  );
  jQuery(".wpsc-setting-body").html(supportcandy.loader_html);

  wpsc_scroll_top();
  var data = {
    action: "wpsc_ai_chatbot_settings",
    tab: supportcandy.current_tab,
  };

  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-setting-body").html(response);
    wpsc_reset_responsive_style();
    jQuery(
      ".wpsc-setting-tab-container button." + supportcandy.current_tab,
    ).trigger("click");
  });
}

/**
 * Load general setting tab ui
 */
function wpsc_get_acb_general_setting() {
  supportcandy.current_tab = "general";
  jQuery(".wpsc-setting-tab-container button").removeClass("active");
  jQuery(
    ".wpsc-setting-tab-container button." + supportcandy.current_tab,
  ).addClass("active");

  window.history.replaceState(
    {},
    null,
    "admin.php?page=wpsc-settings&section=" +
      supportcandy.current_section +
      "&tab=" +
      supportcandy.current_tab,
  );
  jQuery(".wpsc-setting-section-body").html(supportcandy.loader_html);

  wpsc_scroll_top();

  const data = { action: "wpsc_get_acb_general_setting" };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-setting-section-body").html(response);
    wpsc_reset_responsive_style();
    wpsc_acb_toggle_dependent_settings();
    jQuery("#wpsc-acb-service-status").on(
      "change",
      wpsc_acb_toggle_dependent_settings,
    );
    jQuery("#wpsc-acb-sessions-popup-delay-status").on(
      "change",
      wpsc_acb_toggle_dependent_settings,
    );
  });
}

/**
 * Show/hide the AI chatbot general settings that only make sense while their
 * governing status select is set to "Enable" - settings tied to AI Chatbot
 * Status (session retention, footer branding, sessions per page) and
 * settings tied to Popup Delay Status (Popup Delay, Popup Display Limit).
 */
function wpsc_acb_toggle_dependent_settings() {
  jQuery(".wpsc-acb-status-dependent").toggle(
    jQuery("#wpsc-acb-service-status").val() === "1",
  );
  jQuery(".wpsc-acb-popup-delay-dependent").toggle(
    jQuery("#wpsc-acb-sessions-popup-delay-status").val() === "1",
  );
}

/**
 * Save setting
 */
function wpsc_set_acb_settings(el) {
  var form = jQuery(".wpsc-frm-acb-settings")[0];
  var dataform = new FormData(form);

  jQuery(el).text(supportcandy.translations.please_wait);
  jQuery
    .ajax({
      url: supportcandy.ajax_url,
      type: "POST",
      data: dataform,
      processData: false,
      contentType: false,
    })
    .done(function (res) {
      wpsc_get_acb_general_setting();
    });
}

/*
 * Reset settings
 */
function wpsc_reset_acb_settings(el, nonce) {
  var data = {
    action: "wpsc_reset_acb_settings",
    _ajax_nonce: nonce,
  };

  jQuery(el).text(supportcandy.translations.please_wait);
  jQuery
    .ajax({
      url: supportcandy.ajax_url,
      type: "POST",
      data: data,
    })
    .done(function (res) {
      window.location.reload();
    });
}

/**
 * Get chatbot appearance settings
 */
function wpsc_get_acb_appearance_setting() {
  supportcandy.current_tab = "acb-appearance";
  jQuery(".wpsc-setting-tab-container button").removeClass("active");
  jQuery(
    ".wpsc-setting-tab-container button." + supportcandy.current_tab
  ).addClass("active");

  window.history.replaceState(
    {},
    null,
    "admin.php?page=wpsc-settings&section=" +
      supportcandy.current_section +
      "&tab=" +
      supportcandy.current_tab
  );
  jQuery(".wpsc-setting-section-body").html(supportcandy.loader_html);

  wpsc_scroll_top();
  var data = { action: "wpsc_get_acb_appearance_setting" };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-setting-section-body").html(response);
    wpsc_reset_responsive_style();
  });
}

/**
 * Set chatbot appearance settings
 */
function wpsc_set_acb_appearance_setting(el) {
  var form = jQuery(".wpsc-frm-acb-appearance-general")[0];
  var dataform = new FormData(form);
  jQuery(el).text(supportcandy.translations.please_wait);
  jQuery
    .ajax({
      url: supportcandy.ajax_url,
      type: "POST",
      data: dataform,
      processData: false,
      contentType: false,
    })
    .done(function (res) {
      wpsc_get_acb_appearance_setting();
    });
}

/**
 * Reset chatbot appearance settings
 */
function wpsc_reset_acb_appearance_setting(el, nonce) {
  jQuery(el).text(supportcandy.translations.please_wait);
  var data = { action: "wpsc_reset_acb_appearance_setting", _ajax_nonce: nonce };
  jQuery.post(supportcandy.ajax_url, data, function (res) {
    wpsc_get_acb_appearance_setting();
  });
}


/**
 * Get session detailed info
 *
 * @param {int} session_id
 */
function wpsc_view_session_detailed_info(session_id, nonce) {
	var url = new URL(window.location.href);
	url.searchParams.set("session_id", session_id);
	window.history.replaceState({}, null, url.toString());

  // Sessions screen keeps the list visible and loads the conversation beside it.
  var split = jQuery(".wpsc-acb-split");
  var target = split.length
    ? jQuery(".wpsc-acb-split-detail")
    : jQuery(".wpsc-setting-section-body");
  if (split.length) {
    // Remember the server-rendered (translated) empty state so closing can restore it.
    if (split.data("emptyHtml") === undefined && !target.find(".wpsc-acb-chat-container").length) {
      split.data("emptyHtml", target.html());
    }
    split.addClass("wpsc-acb-split-open").attr("data-session-id", session_id);
    wpsc_acb_mark_active_session();
  }

  target.html(supportcandy.loader_html);
  var data = {
    action: "wpsc_view_session_detailed_info",
    session_id,
    _ajax_nonce: nonce,
  };
  jQuery
    .post(supportcandy.ajax_url, data, function (res) {
      if (split.length && String(split.attr("data-session-id")) !== String(session_id)) {
        return; // A newer session was selected meanwhile.
      }
      target.html(res);
      var chat = target.find(".wpsc-acb-chat-container");
      if (split.length && chat.length) {
        chat.scrollTop(chat[0].scrollHeight);
      }
    })
    .fail(function (xhr) {
      if (!split.length) {
        return;
      }
      var msg = xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data : "";
      target.html(
        '<div class="wpsc-acb-split-empty"><p></p>' +
          '<a href="#" class="button button-secondary wpsc-acb-split-close">&larr;</a></div>',
      );
      target.find("p").text(msg);
      target.find(".wpsc-acb-split-close").on("click", function (e) {
        e.preventDefault();
        wpsc_close_session_detailed_info();
      });
    });
}

/**
 * Close the conversation pane (mobile "back" and error state)
 */
function wpsc_close_session_detailed_info() {
  var split = jQuery(".wpsc-acb-split");
  split.removeClass("wpsc-acb-split-open").removeAttr("data-session-id");
  split.find(".wpsc-acb-split-detail").html(
    split.data("emptyHtml") ||
      '<div class="wpsc-acb-split-empty"><span class="wpsc-acb-split-empty-icon" aria-hidden="true">💬</span></div>',
  );
  wpsc_acb_mark_active_session();
  var url = new URL(window.location.href);
  url.searchParams.delete("session_id");
  window.history.replaceState({}, null, url.toString());
}

/**
 * Highlight the session row that is open in the conversation pane
 */
function wpsc_acb_mark_active_session() {
  var id = jQuery(".wpsc-acb-split").attr("data-session-id");
  jQuery("#wpsc-session-list tbody tr").each(function () {
    var row = jQuery(this);
    var active = !!id && jQuery.trim(row.children("td").first().text()) === String(id);
    row.toggleClass("wpsc-acb-session-active", active);
    if (active) {
      row.attr("aria-current", "true");
    } else {
      row.removeAttr("aria-current");
    }
  });
}

// DataTable redraws (paging, sorting, filters) replace the rows: re-apply highlight and keyboard access.
jQuery(document).on("draw.dt", ".wpsc_session_info_list", function () {
  jQuery(this).find("tbody tr").each(function () {
    if (jQuery(this).find(".wpsc-link").length) {
      jQuery(this).attr({ tabindex: "0", role: "button" });
    }
  });
  wpsc_acb_mark_active_session();
});
jQuery(document).on("keydown", "#wpsc-session-list tbody tr", function (e) {
  if ((e.key === "Enter" || e.key === " ") && e.target === this) {
    e.preventDefault();
    jQuery(this).find(".wpsc-link").first().trigger("click");
  }
});

// Close icon in the conversation header.
jQuery(document).on("click", ".wpsc-acb-split .wpsc-acb-close", function (e) {
  e.preventDefault();
  wpsc_close_session_detailed_info();
});

// Smooth open/close for the Filters and More details dropdowns.
jQuery(document).on("click", ".wpsc-acb-filters > summary, .wpsc-acb-more > summary", function (e) {
  // Let links inside the summary (e.g. the ticket id) open instead of toggling.
  if (jQuery(e.target).closest("a", this).length) {
    e.stopPropagation();
    return;
  }
  e.preventDefault();
  var details = jQuery(this).parent();
  var content = jQuery(this).siblings();
  if (content.is(":animated")) {
    return;
  }
  if (details.prop("open")) {
    content.slideUp(200, function () {
      details.removeAttr("open");
      content.css("display", "");
    });
  } else {
    content.hide();
    details.attr("open", "");
    content.slideDown(200);
  }
});
