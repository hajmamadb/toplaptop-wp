jQuery(document).ready(function () {
  wpsc_reset_responsive_style();
  jQuery(window).resize(function () {
    wpsc_reset_responsive_style(true);
  });
  wpsc_document_ready();

  // for check there is value in reply box.
  jQuery(window).on("popstate", function () {
    // agent workspace handles back/forward itself (see wpsc_tickets_popstate).
    if (typeof supportcandy.section_callbacks !== "undefined") {
      return;
    }
    if (wpsc_is_description_text()) {
      if (!confirm(supportcandy.translations.warning_message)) {
        return;
      }
    }
  });

  // Change the element from h1 to h2
  if (jQuery('#link-modal-title').length > 0) {
    jQuery('#link-modal-title').replaceWith('<h2 id="link-modal-title">' + jQuery('#link-modal-title').html() + '</h2>');
  }
});

/**
 * Apply responsive styles based on container size
 */
function wpsc_reset_responsive_style(isWindowResize = false) {
  var lastContainerWidth = supportcandy.lastContainerWidth
    ? supportcandy.lastContainerWidth
    : 0;
  var containerWidth = jQuery("#wpsc-container").outerWidth();

  if (isWindowResize && lastContainerWidth === containerWidth) {
    return;
  }

  supportcandy.lastContainerWidth = containerWidth;

  jQuery("#wpsc-container").hide();
  var style = "xs";
  if (containerWidth <= 768) {
    style = "xs";
  } else if (containerWidth > 768 && containerWidth <= 992) {
    style = "sm";
  } else if (containerWidth > 992 && containerWidth <= 1200) {
    style = "md";
  } else {
    style = "lg";
  }
  supportcandy.responsiveStyle = style;
  var styleURL =
    supportcandy.plugin_url +
    "framework/responsive/" +
    style +
    ".css?version=" +
    supportcandy.version;
  var styleEl =
    '<link id="wpsc-responsive-css" rel="stylesheet" type="text/css" href="' +
    styleURL +
    '" />';
  jQuery("#wpsc-responsive-css").remove();
  jQuery("head").append(styleEl);
  wpsc_apply_responsive_styles();
  jQuery("#wpsc-container").show();
}

/**
 * Apply visible elements on screen resize
 */
function wpsc_el_reset_visible() {
  jQuery(
    ".wpsc-visible-xs,.wpsc-visible-sm,.wpsc-visible-md,.wpsc-visible-lg"
  ).hide();
  jQuery(".wpsc-visible-" + supportcandy.responsiveStyle).show();
}

/**
 * Apply hidden elements on screen resize
 */
function wpsc_el_reset_hidden() {
  jQuery(
    ".wpsc-hidden-xs,.wpsc-hidden-sm,.wpsc-hidden-md,.wpsc-hidden-lg"
  ).show();
  jQuery(".wpsc-hidden-" + supportcandy.responsiveStyle).hide();
}

/**
 * Toggle humbargar menu
 */
function wpsc_toggle_humbargar() {
  var flag = supportcandy.humbargar ? true : false;
  if (flag) {
    jQuery(".wpsc-humbargar-menu").hide(
      "slide",
      { direction: "right" },
      200,
      function () {
        jQuery(".wpsc-humbargar-overlay").hide();
      }
    );
    supportcandy.humbargar = false;
  } else {
    jQuery(".wpsc-humbargar-overlay").show();
    jQuery(".wpsc-humbargar-menu").show("slide", { direction: "right" }, 200);
    supportcandy.humbargar = true;
  }
}

/**
 * Individual ticket: the back action only makes sense when a ticket list exists
 * (not on the open-ticket page or a ticket url page), so remove it otherwise.
 */
function wpsc_it_init_back_link() {
  var callbacks = supportcandy.section_callbacks || {};
  var callback = callbacks[supportcandy.current_section];
  if (typeof window[callback] !== "function") {
    jQuery(".wpsc-it-back + .action-devider").remove();
    jQuery(".wpsc-it-back").remove();
  }
}

/**
 * Go back from an individual ticket to its list
 */
function wpsc_it_back_to_list() {
  var callbacks = supportcandy.section_callbacks || {};
  var callback = callbacks[supportcandy.current_section];
  if (typeof window[callback] !== "function") {
    return;
  }
  // prevent the list callback from re-opening the ticket that was opened by url.
  delete supportcandy.current_ticket_id;
  delete supportcandy.current_archived_ticket_id;
  window[callback]();
}

// Header title doubles as a shortcut back to the list while a ticket is open.
jQuery(document).on("click", ".wpsc-humbargar-title", function () {
  if (supportcandy.ticketListIsIndividual && jQuery(".wpsc-it-back").length) {
    wpsc_it_back_to_list();
  }
});

/**
 * Close humbargar menu if open
 */
function wpsc_close_humbargar() {
  var flag = supportcandy.humbargar ? true : false;
  if (flag) {
    jQuery(".wpsc-humbargar-menu").hide();
    jQuery(".wpsc-humbargar-overlay").hide();
    supportcandy.humbargar = false;
  }
}

/**
 * Bulk selector change
 */
function wpsc_bulk_select_change() {
  var checked = jQuery(".wpsc-bulk-selector").is(":checked");
  jQuery(".wpsc-bulk-select").prop("checked", checked);
  wpsc_toggle_bulk_actions_btn();
}

/**
 * Bulk item selector change
 */
function wpsc_bulk_item_select_change() {
  var items = jQuery(".wpsc-bulk-select:checked");
  var checked = items.length === 0 ? false : true;
  jQuery(".wpsc-bulk-selector").prop("checked", checked);
  wpsc_toggle_bulk_actions_btn();
}

/**
 * Enable bulk actions button only when at least one ticket is selected
 */
function wpsc_toggle_bulk_actions_btn() {
  var hasSelection = jQuery(".wpsc-bulk-select:checked").length > 0;
  var btn = jQuery("#wpsc-bulk-actions-btn");
  if (hasSelection) {
    btn.removeAttr("title");
  } else if (!btn.attr("title")) {
    btn.attr("title", btn.data("disabled-title"));
  }
  btn.prop("disabled", !hasSelection);
  if (!hasSelection) {
    jQuery("#wpsc-bulk-actions").hide();
  }
}

/**
 * Show modal with loading screen
 */
function wpsc_show_modal() {
  jQuery(".wpsc-modal .inner-container").hide();
  jQuery(".wpsc-modal .loader").show();
  jQuery(".wpsc-modal").show();
}

/**
 * Close modal
 */
function wpsc_show_modal_inner_container() {
  jQuery(".wpsc-modal .loader").hide();
  jQuery(".wpsc-modal .inner-container").show(
    "slide",
    { direction: "up" },
    200
  );
}

/**
 * Close modal
 */
function wpsc_close_modal() {
  jQuery(".wpsc-modal .inner-container").hide(
    "slide",
    { direction: "up" },
    200,
    function () {
      jQuery(".wpsc-modal").hide();
    }
  );
}

/**
 * OFF toggle button click
 */
function wpsc_toggle_off(el) {
  var off = jQuery(el);
  var on = jQuery(el).next();
  var input = on.next();

  if (input.val() === 0) {
    return;
  }

  input.val(0);
  on.removeClass("active");
  off.addClass("active");
}

/**
 * ON toggle button click
 */
function wpsc_toggle_on(el) {
  var on = jQuery(el);
  var off = jQuery(el).prev();
  var input = on.next();

  if (input.val() === 1) {
    return;
  }

  input.val(1);
  off.removeClass("active");
  on.addClass("active");
}

/**
 * Scroll to top of WPSC component
 */
function wpsc_scroll_top() {
  jQuery("html, body").animate(
    {
      scrollTop: jQuery("#wpsc-container").offset().top - 42,
    },
    500
  );
}

/**
 * Toggle individual widgets
 */
function wpsc_toggle_mob_it_widgets() {
  var status = parseInt(
    jQuery(".wpsc-it-mob-widgets-inner-container").data("status")
  );

  if (!status) {
    jQuery(".wpsc-it-mob-widgets-inner-container").slideDown();
    jQuery(".wpsc-it-mob-widget-trigger-btn .down").hide();
    jQuery(".wpsc-it-mob-widget-trigger-btn .up").show();
    jQuery(".wpsc-it-mob-widgets-inner-container").data("status", 1);
  } else {
    jQuery(".wpsc-it-mob-widgets-inner-container").slideUp();
    jQuery(".wpsc-it-mob-widget-trigger-btn .up").hide();
    jQuery(".wpsc-it-mob-widget-trigger-btn .down").show();
    jQuery(".wpsc-it-mob-widgets-inner-container").data("status", 0);
  }
}

/**
 *  Get refresh current ticket
 */
function wpsc_it_ab_refresh(ticket_id) {
  if (wpsc_is_description_text()) {
    if (confirm(supportcandy.translations.warning_message)) {
      wpsc_clear_saved_draft_reply(ticket_id);
    } else {
      return;
    }
  }

  wpsc_get_individual_ticket(ticket_id);
}

/**
 * Get close current ticket modal UI
 */
function wpsc_it_close_ticket(ticket_id, nonce) {
  if (wpsc_is_description_text()) {
    if (!confirm(supportcandy.translations.warning_message)) return;
  }

  var flag = confirm(supportcandy.translations.confirm);
  if (flag) {
    if (wpsc_is_description_text()) {
      wpsc_clear_saved_draft_reply(ticket_id);
    }
  } else {
    return;
  }

  var data = { action: "wpsc_it_close_ticket", ticket_id, _ajax_nonce: nonce };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    wpsc_run_ajax_background_process();
    wpsc_after_close_ticket(ticket_id);
  });
}

/**
 * Get duplicate ticket
 */
function wpsc_it_get_duplicate_ticket(ticket_id, nonce) {
  wpsc_show_modal();
  var data = {
    action: "wpsc_it_get_duplicate_ticket",
    ticket_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    wpsc_show_modal_inner_container();
  });
}

/**
 * Set duplicate ticket
 */
function wpsc_it_set_duplicate_ticket(el, ticket_id) {
  if (wpsc_is_description_text()) {
    if (confirm(supportcandy.translations.warning_message)) {
      wpsc_clear_saved_draft_reply(ticket_id);
    } else {
      return;
    }
  }

  var form = jQuery("form.duplicate")[0];
  var dataform = new FormData(form);
  jQuery(".wpsc-modal-footer button").attr("disabled", true);
  jQuery(el).text(supportcandy.translations.please_wait);

  jQuery
    .ajax({
      url: supportcandy.ajax_url,
      type: "POST",
      data: dataform,
      processData: false,
      contentType: false,
    })
    .done(function (response) {
      wpsc_close_modal();
      wpsc_get_individual_ticket(response.ticket_id);
    });
}

/**
 * Get delete ticket
 */
function wpsc_it_delete_ticket(ticket_id, nonce) {
  if (wpsc_is_description_text()) {
    if (!confirm(supportcandy.translations.warning_message)) {
      return;
    } else {
      var is_tinymce =
        typeof tinyMCE != "undefined" &&
        tinyMCE.activeEditor &&
        !tinyMCE.activeEditor.isHidden();
      if (is_tinymce && tinymce.get("description")) {
        tinyMCE.get("description").setContent("");
      } else {
        jQuery("#description").val("");
      }
      wpsc_clear_saved_draft_reply(ticket_id);
    }
  }

  var flag = confirm(supportcandy.translations.confirm);
  if (!flag) {
    return;
  }

  var data = { action: "wpsc_it_delete_ticket", ticket_id, _ajax_nonce: nonce };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    wpsc_get_ticket_list();
    wpsc_run_ajax_background_process();
  });
}

/**
 * Restore ticket
 */
function wpsc_it_ticket_restore(ticket_id, nonce) {
  var flag = confirm(supportcandy.translations.confirm);
  if (!flag) {
    return;
  }

  var data = {
    action: "wpsc_it_ticket_restore",
    ticket_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    wpsc_get_individual_ticket(ticket_id);
  });
}

/**
 * Get delete ticket
 */
function wpsc_it_archive_ticket(ticket_id, nonce) {
  if (wpsc_is_description_text()) {
    if (!confirm(supportcandy.translations.warning_message)) {
      return;
    } else {
      var is_tinymce =
        typeof tinyMCE != "undefined" &&
        tinyMCE.activeEditor &&
        !tinyMCE.activeEditor.isHidden();
      if (is_tinymce && tinymce.get("description")) {
        var description = tinyMCE.get("description").setContent("");
      } else {
        var description = jQuery("#description").val("");
      }
      wpsc_clear_saved_draft_reply(ticket_id);
    }
  }

  var flag = confirm(supportcandy.translations.confirm);
  if (!flag) {
    return;
  }

  var data = { action: "wpsc_it_archive_ticket", ticket_id, _ajax_nonce: nonce };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    wpsc_get_ticket_list();
    wpsc_run_ajax_background_process();
  });
}

/**
 * Delete permanently
 */
function wpsc_it_delete_permanently(ticket_id, nonce) {
  var flag = confirm(supportcandy.translations.delete_permanently);
  if (!flag) {
    return;
  }

  var data = {
    action: "wpsc_it_delete_permanently",
    ticket_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    wpsc_get_ticket_list();
  });
}

/**
 * Block/unblock notifications
 */
function wpsc_it_block_unblock_notifications(ticketId, isBlock, nonce) {

	if (!ticketId || typeof isBlock !== 'boolean' || !nonce) {
		console.error('Invalid parameters passed to wpsc_it_block_unblock_notifications');
		return;
	}

	const message = isBlock
		? supportcandy.translations.block_notifications
		: supportcandy.translations.unblock_notifications;

	if (!window.confirm(message)) {
		return;
	}

  var data = {
			action: 'wpsc_it_block_unblock_notifications',
			ticket_id: ticketId,
			is_block: isBlock ? 1 : 0,
			_ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    wpsc_get_individual_ticket(ticketId);
  });
}

/**
 *  Get edit ticket subject modal UI
 */
function wpsc_it_get_edit_subject(ticket_id) {
  wpsc_show_modal();
  var data = {
    action: "wpsc_it_get_edit_subject",
    ticket_id,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    wpsc_show_modal_inner_container();
  });
}

/**
 *  Set edit ticket subject
 */
function wpsc_it_set_edit_subject(el, ticket_id) {
  if (wpsc_is_description_text()) {
    if (!confirm(supportcandy.translations.warning_message)) {
      wpsc_clear_saved_draft_reply(ticket_id);
    } else {
      return;
    }
  }

  var form = jQuery("form.edit-subject")[0];
  var dataform = new FormData(form);
  jQuery(".wpsc-modal-footer button").attr("disabled", true);
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
      wpsc_close_modal();
      wpsc_get_individual_ticket(ticket_id);
    });
}

/**
 * Get change status modal UI
 */
function wpsc_it_get_edit_ticket_status(ticket_id, nonce) {
  wpsc_show_modal();
  var data = {
    action: "wpsc_it_get_edit_ticket_status",
    ticket_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    // Set to modal.
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    // Display modal.
    wpsc_show_modal_inner_container();
  });
}

/**
 * Update ticket status
 *
 * @param {*} el
 */
function wpsc_it_set_edit_ticket_status(el, ticket_id) {
  if (wpsc_is_description_text()) {
    if (confirm(supportcandy.translations.warning_message)) {
      wpsc_clear_saved_draft_reply(ticket_id);
    } else {
      return;
    }
  }

  var form = jQuery("form.frm-edit-ticket-status")[0];
  var dataform = new FormData(form);
  jQuery(".wpsc-modal-footer button").attr("disabled", true);
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
      wpsc_close_modal();
      wpsc_get_individual_ticket(ticket_id);
      wpsc_run_ajax_background_process();
    });
}

/**
 * Get additional recipients modal UI
 */
function wpsc_it_get_add_ar(ticket_id, nonce) {
  wpsc_show_modal();
  var data = {
    action: "wpsc_it_get_add_ar",
    ticket_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    wpsc_show_modal_inner_container();
  });
}

/**
 * Update additional recipients
 *
 * @param {*} el
 */
function wpsc_it_set_add_ar(el, ticket_id) {
  if (wpsc_is_description_text()) {
    if (confirm(supportcandy.translations.warning_message)) {
      wpsc_clear_saved_draft_reply(ticket_id);
    } else {
      return;
    }
  }

  var form = jQuery("form.additional-recipients")[0];
  var dataform = new FormData(form);
  jQuery(".wpsc-modal-footer button").attr("disabled", true);
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
      wpsc_close_modal();
      wpsc_get_individual_ticket(ticket_id);
    });
}

/**
 * Get assigned agents modal UI
 */
function wpsc_it_get_edit_assigned_agents(ticket_id, nonce) {
  wpsc_show_modal();
  var data = {
    action: "wpsc_it_get_edit_assigned_agents",
    ticket_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    wpsc_show_modal_inner_container();
  });
}

/**
 * Update assigned agents
 *
 * @param {*} el
 */
function wpsc_it_set_edit_assigned_agents(el, ticket_id, uniqueId) {
  if (wpsc_is_description_text()) {
    if (confirm(supportcandy.translations.warning_message)) {
      wpsc_clear_saved_draft_reply(ticket_id);
    } else {
      return;
    }
  }

  var form = jQuery("form.change-assignee." + uniqueId)[0];
  var dataform = new FormData(form);
  jQuery(".wpsc-modal-footer button").attr("disabled", true);
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
      wpsc_close_modal();
      if (res.viewPermission) {
        wpsc_get_individual_ticket(ticket_id);
      } else {
        wpsc_get_ticket_list();
      }
      wpsc_run_ajax_background_process();
    });
}

/**
 * Get raised by modal UI
 */
function wpsc_it_get_edit_raised_by(ticket_id, nonce) {
  wpsc_show_modal();
  var data = {
    action: "wpsc_it_get_edit_raised_by",
    ticket_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    // Set to modal.
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    // Display modal.
    wpsc_show_modal_inner_container();
  });
}

/**
 * Update raised by
 *
 * @param {*} el
 */
function wpsc_it_set_edit_raised_by(el, ticket_id, uniqueId) {
  if (wpsc_is_description_text()) {
    if (confirm(supportcandy.translations.warning_message)) {
      wpsc_clear_saved_draft_reply(ticket_id);
    } else {
      return;
    }
  }

  var type = jQuery(".type." + uniqueId).val();
  if (type == "new") {
    var name = jQuery(".name." + uniqueId)
      .val()
      .trim();
    var email = jQuery(".email." + uniqueId)
      .val()
      .trim();
    if (!name || !email) {
      return;
    }

    if (!validateEmail(email)) {
      alert(supportcandy.translations.raisedByEditWidget.invalidEmail);
      return;
    }
  }

  var form = jQuery("form.change-raised-by." + uniqueId)[0];
  var dataform = new FormData(form);
  jQuery(".wpsc-modal-footer button").attr("disabled", true);
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
      wpsc_close_modal();
      wpsc_get_individual_ticket(ticket_id);
    });
}

/**
 * Get ticket fields modal UI
 */
function wpsc_it_get_edit_ticket_fields(ticket_id, nonce) {
  wpsc_show_modal();
  var data = {
    action: "wpsc_it_get_edit_ticket_fields",
    ticket_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    wpsc_show_modal_inner_container();
  });
}

/**
 * Update ticket fields
 *
 * @param {*} el
 */
function wpsc_it_set_edit_ticket_fields(el, ticket_id) {
  if (wpsc_is_description_text()) {
    if (confirm(supportcandy.translations.warning_message)) {
      wpsc_clear_saved_draft_reply(ticket_id);
    } else {
      return;
    }
  }

  var form = jQuery("form.change-ticket-fields")[0];
  var dataform = new FormData(form);
  jQuery(".wpsc-modal-footer button").attr("disabled", true);
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
      wpsc_close_modal();
      wpsc_get_individual_ticket(ticket_id);
    });
}

/**
 * Get agent only fields modal UI
 */
function wpsc_it_get_edit_agentonly_fields(ticket_id, nonce) {
  wpsc_show_modal();
  var data = {
    action: "wpsc_it_get_edit_agentonly_fields",
    ticket_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    wpsc_show_modal_inner_container();
  });
}

/**
 * Update agent only fields
 *
 * @param {*} el
 */
function wpsc_it_set_edit_agentonly_fields(el, ticket_id) {
  if (wpsc_is_description_text()) {
    if (confirm(supportcandy.translations.warning_message)) {
      wpsc_clear_saved_draft_reply(ticket_id);
    } else {
      return;
    }
  }

  var form = jQuery("form.change-agentonly-fields")[0];
  var dataform = new FormData(form);
  jQuery(".wpsc-modal-footer button").attr("disabled", true);
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
      wpsc_close_modal();
      wpsc_get_individual_ticket(ticket_id);
    });
}

/**
 * Get edit thread
 */
function wpsc_it_get_edit_thread(el, ticket_id, thread_id, nonce) {
  var thread = jQuery(el).closest(".wpsc-thread");
  thread.html(supportcandy.loader_html);

  var data = {
    action: "wpsc_it_get_edit_thread",
    ticket_id,
    thread_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (res) {
    thread.html(res);
  });
}

/**
 * Set edit thread
 */
function wpsc_it_set_edit_thread(el, uniqueId) {
  var form = jQuery("form.edit-thread")[0];
  var dataform = new FormData(form);

  var is_tinymce =
    typeof tinyMCE != "undefined" &&
    tinyMCE.activeEditor &&
    !tinyMCE.activeEditor.isHidden();
  var description =
    is_tinymce && tinymce.get(uniqueId)
      ? tinyMCE.get(uniqueId).getContent()
      : jQuery("#" + uniqueId)
          .val()
          .trim();
  if (!description) {
    return;
  }

  dataform.append("thread_content", description);

  var thread = jQuery(el).closest(".wpsc-thread");
  thread.html(supportcandy.loader_html);

  jQuery
    .ajax({
      url: supportcandy.ajax_url,
      type: "POST",
      data: dataform,
      processData: false,
      contentType: false,
    })
    .done(function (res) {
      if (thread.next().length > 0) {
        thread.next().before(res);
      } else {
        thread.parent().append(res);
      }
      thread.remove();
    });
}

/**
 * Delete thread
 */
function wpsc_it_thread_delete(el, ticket_id, thread_id, nonce) {
  var flag = confirm(supportcandy.translations.confirm);
  if (!flag) {
    return;
  }

  var thread = jQuery(el).closest(".wpsc-thread");
  thread.html(supportcandy.loader_html);

  var data = {
    action: "wpsc_it_thread_delete",
    ticket_id,
    thread_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (res) {
    if (res.trim()) {
      if (thread.next().length > 0) {
        thread.next().before(res);
      } else {
        thread.parent().append(res);
      }
    }
    thread.remove();
  });
}

/**
 * Get thread html
 */
function wpsc_it_get_thread(el, ticket_id, thread_id, _ajax_nonce) {
  var thread = jQuery(el).closest(".wpsc-thread");
  thread.html(supportcandy.loader_html);

  var data = {
    action: "wpsc_it_get_thread",
    ticket_id,
    thread_id,
    _ajax_nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (res) {
    if (thread.next().length > 0) {
      thread.next().before(res);
    } else {
      thread.parent().append(res);
    }
    thread.remove();
  });
}

/**
 * View thread log
 */
function wpsc_it_view_thread_log(ticket_id, thread_id, log_id, _ajax_nonce) {
  wpsc_show_modal();
  var data = {
    action: "wpsc_it_view_thread_log",
    ticket_id,
    thread_id,
    log_id,
    _ajax_nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    wpsc_show_modal_inner_container();
  });
}

/**
 * View thread
 */
function wpsc_it_view_deleted_thread(ticket_id, thread_id, _ajax_nonce) {
  wpsc_show_modal();
  var data = {
    action: "wpsc_it_view_deleted_thread",
    ticket_id,
    thread_id,
    _ajax_nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    wpsc_show_modal_inner_container();
  });
}

/**
 * Restore thread
 */
function wpsc_it_restore_thread(ticket_id, thread_id, _ajax_nonce) {
  var flag = confirm(supportcandy.translations.confirm);
  if (!flag) {
    return;
  }

  wpsc_close_modal();
  var thread = jQuery(".wpsc-thread." + thread_id);
  thread.html(supportcandy.loader_html);

  var data = {
    action: "wpsc_it_restore_thread",
    ticket_id,
    thread_id,
    _ajax_nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (res) {
    if (thread.next().length > 0) {
      thread.next().before(res);
    } else {
      thread.parent().append(res);
    }
    thread.remove();
  });
}

/**
 * Restore thread
 */
function wpsc_it_thread_delete_permanently(ticket_id, thread_id, _ajax_nonce) {
  var flag = confirm(supportcandy.translations.confirm);
  if (!flag) {
    return;
  }

  wpsc_close_modal();
  var thread = jQuery(".wpsc-thread." + thread_id);
  thread.html(supportcandy.loader_html);

  var data = {
    action: "wpsc_it_thread_delete_permanently",
    ticket_id,
    thread_id,
    _ajax_nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (res) {
    thread.remove();
  });
}

/**
 * Get macros modal UI
 */
function wpsc_get_macros() {
  wpsc_show_modal();
  var data = {
    action: "wpsc_get_macros",
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    // Set to modal.
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    // Display modal.
    wpsc_show_modal_inner_container();
    jQuery("input[type=text]").focus();
  });
}

/**
 * Add and condition item
 * @param {*} el
 * @param {*} uniqueId
 */
function wpsc_add_and_condition(el, uniqueId) {
  var container = jQuery(el)
    .closest(".wpsc-form-filter-container")
    .find(".and-container");
  container.append(jQuery(".and-template." + uniqueId).html());
  container = container.find(".and-item:last-child .or-container");
  container.append(jQuery(".or-template." + uniqueId).html());
  container.find("select").selectWoo();
}

/**
 * Add and condition item
 * @param {*} el
 * @param {*} uniqueId
 */
function wpsc_add_or_condition(el, uniqueId) {
  var container = jQuery(el).closest(".and-item").find(".or-container");
  container.append(jQuery(".or-template." + uniqueId).html());
  container.find(":last-child").find("select").selectWoo();
}

/**
 * Remove condition item
 * @param {*} el
 */
function wpsc_remove_condition_item(el) {
  var andItem = jQuery(el).closest(".and-item");
  jQuery(el).closest(".wpsc-form-filter-item").remove();
  if (andItem.find(".or-container").children().length === 0) {
    jQuery(andItem).remove();
  }
}

/**
 * Get JSON string for the condition input of given name
 * @param {*} name
 */
function wpsc_get_condition_json(name) {
  var conditions = [];
  jQuery(".wpsc-form-filter-container." + name + " .and-item").each(
    function () {
      var andCondition = [];
      jQuery(this)
        .find(".wpsc-form-filter-item")
        .each(function () {
          var slug = jQuery(this).find("select.filter").first().val();
          if (!slug) {
            return;
          }
          var operator = jQuery(this).find("select.operator").first().val();
          if (!operator) {
            return;
          }
          var operand_val_1 = jQuery(this).find(".operand_val_1").first().val();
          if (!operand_val_1) {
            return;
          }
          var filter = { slug, operator, operand_val_1 };
          if (operator === "BETWEEN") {
            var operand_val_2 = jQuery(this)
              .find(".operand_val_2")
              .first()
              .val();
            if (!operand_val_2) {
              return;
            }
            filter.operand_val_2 = operand_val_2;
          }
          andCondition.push(filter);
        });
      conditions.push(andCondition);
    }
  );
  return conditions;
}

/**
 * Show live plain-English summary of the conditions under the condition builder
 *
 * @param {*} name
 * @param {*} hook
 * @param {*} nonce
 */
function wpsc_tc_init_summary(name, hook, nonce) {
  var container = jQuery(".wpsc-form-filter-container." + name);
  var wrapper = jQuery(".wpsc-tc-tools." + name);
  var summary = wrapper.find(".wpsc-tc-summary .text");
  var timer = null;

  function refresh() {
    var data = {
      action: "wpsc_tc_get_summary",
      conditions: JSON.stringify(wpsc_get_condition_json(name)),
      hook,
      _ajax_nonce: nonce,
    };
    jQuery.post(supportcandy.ajax_url, data, function (response) {
      var text = response.success ? response.data.summary : "";
      summary.html(text);
      wrapper.toggle(text !== "");
    });
  }

  container.on("change input click", function () {
    clearTimeout(timer);
    timer = setTimeout(refresh, 400);
  });
  refresh();
}

/**
 * Get form filter operators
 */
function wpsc_get_ticket_filter_operators(el, nonce) {
  var content = jQuery(el).closest(".content");
  var slug = jQuery(el).val().trim();

  content.find(".conditional, .wpsc-inline-loader").remove();
  if (!slug) {
    return;
  }

  content.append(supportcandy.inline_loader);
  var data = {
    action: "wpsc_get_ticket_filter_operators",
    slug: slug,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    content.find(".wpsc-inline-loader").remove();
    content.append(response);
  });
}

/**
 * Get ticket filter value input(s)
 */
function wpsc_get_ticket_filter_operands(el, id, nonce) {
  var content = jQuery(el).closest(".content");
  var operator = jQuery(el).val().trim();

  content.find(".conditional.operand, .wpsc-inline-loader").remove();
  if (!operator) {
    return;
  }

  content.append(supportcandy.inline_loader);
  var data = {
    action: "wpsc_get_ticket_filter_operands",
    operator,
    id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    content.find(".wpsc-inline-loader").remove();
    content.append(response);
  });
}

/**
 * Single file attachment upload handler
 */
function wpsc_set_attach_single(el, uniqueId, slug) {
  var fileAttachment = el.files[0];
  wpsc_file_upload(fileAttachment, uniqueId, slug, true);
  jQuery(el).val("");
}

/**
 * Multiple file attachment upload handler
 */
function wpsc_set_attach_multiple(el, uniqueId, slug) {
  jQuery.each(el.files, function (index, fileAttachment) {
    wpsc_file_upload(fileAttachment, uniqueId, slug);
  });
  jQuery(el).val("");
}

/**
 * Trigger description attachments
 */
function wpsc_trigger_desc_attachments(uniqueId) {
  jQuery("input." + uniqueId).trigger("click");
}

/**
 * Remove an attachment
 */
function wpsc_remove_attachment(el) {
  var single = jQuery(el).data("single");
  var uniqueId = jQuery(el).data("uniqueid");
  jQuery(el).closest(".wpsc-editor-attachment").remove();
  if (single) {
    jQuery("input." + uniqueId).show();
  }
}

/**
 * Attachment upload progress update
 */
function wpscAttachmentUploadProgress(e) {
  if (e.lengthComputable) {
    var max = e.total;
    var current = e.loaded;
    var progrss = current / max;
    e.currentTarget.attachment
      .find(".attachment-waiting")
      .circleProgress("value", progrss);
  }
}

/**
 * Change raised by or create ticket as
 */
function wpsc_get_change_create_as(nonce) {
  wpsc_show_modal();
  var data = { action: "wpsc_get_change_create_as", _ajax_nonce: nonce };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    // Set to modal.
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    // Display modal.
    wpsc_show_modal_inner_container();
  });
}

/**
 * Change create as
 */
function wpsc_set_change_create_as(uniqueId, _ajax_nonce) {
  var name = jQuery("input.name." + uniqueId)
    .val()
    .trim();
  var email = jQuery("input.email." + uniqueId)
    .val()
    .trim();
  if (!name || !email) {
    alert(supportcandy.translations.req_fields_missing);
    return;
  }

  if (!validateEmail(email)) {
    alert(supportcandy.translations.invalidEmail);
    return;
  }

  var data = { action: "wpsc_add_new_create_as", name, email, _ajax_nonce };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery("input.name").val(response.name);
    jQuery("input.email").val(response.email);

    // add option and select.
    if (jQuery("select.create-as").val() != response.id) {
      var newOption = new Option(response.label, response.id, false, false);
      jQuery("select.create-as").append(newOption);
      jQuery("select.create-as").val(response.id).trigger("change");
    }

    wpsc_close_modal();
    wpsc_after_change_create_as();
  });
}

/**
 * Check visibility conditions
 */
function wpsc_check_tff_visibility() {
  var conditionalFields = jQuery(".wpsc-tff.conditional");
  if (conditionalFields.length === 0) {
    return;
  }

  const callerId = Math.floor(Math.random() * 100000);
  supportcandy.checkTffVisibityCallers =
    supportcandy.checkTffVisibityCallers || [];
  supportcandy.checkTffVisibityCallers.push(callerId);

  jQuery(".wpsc-ct-loader").html(supportcandy.inline_loader);
  jQuery("select.create-as").attr("disabled", true);

  var form = jQuery("form.wpsc-create-ticket")[0];
  var dataform = new FormData(form);

  var is_tinymce =
    typeof tinyMCE != "undefined" &&
    tinyMCE.activeEditor &&
    !tinyMCE.activeEditor.isHidden();
  if (is_tinymce && tinymce.get("description")) {
    var description = tinyMCE.get("description").getContent();
  } else {
    var description = jQuery("#description").val();
  }

  dataform.append("description", description);
  dataform.append("action", "wpsc_check_tff_visibility");
  jQuery
    .ajax({
      url: supportcandy.ajax_url,
      type: "POST",
      data: dataform,
      processData: false,
      contentType: false,
    })
    .done(function (results) {
      jQuery.each(results, function (key, val) {
        if (parseInt(val) === 0) {
          jQuery(".wpsc-tff." + key).addClass("wpsc-hidden");
          jQuery(".wpsc-tff." + key).removeClass("wpsc-visible");
        } else {
          jQuery(".wpsc-tff." + key).removeClass("wpsc-hidden");
          jQuery(".wpsc-tff." + key).addClass("wpsc-visible");
        }
      });
      jQuery(".wpsc-ct-loader").html("");
      jQuery("select.create-as").removeAttr("disabled");
    })
    .fail(function (xhr, status, error) {
      console.error("AJAX request failed:", status, error);
      jQuery(".wpsc-ct-loader").html("");
      jQuery("select.create-as").removeAttr("disabled");
    })
    .always(function () {
      // remove callerId from supportcandy.checkTffVisibityCallers array
      supportcandy.checkTffVisibityCallers =
        supportcandy.checkTffVisibityCallers.filter(function (id) {
          return id !== callerId;
        });
    });
}

/**
 * Validate input for email address
 */
function validateEmail(email) {
  var re =
    /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(email);
}

/**
 * Show an inline error on an auth screen (sign in, sign up, OTP)
 *
 * @param {Element|jQuery} el - any element inside the auth screen
 * @param {string} message - error text
 * @param {jQuery} fields - optional inputs to flag as invalid; the first one gets focus
 */
function wpsc_auth_show_error(el, message, fields) {
  var container = jQuery(el).closest(".auth-inner-container");
  var box = container.children(".wpsc-auth-error");
  if (!box.length) {
    box = jQuery(
      '<div class="wpsc-auth-error" role="alert" aria-live="assertive">' +
        '<svg class="wpsc-auth-error-icon" viewBox="0 0 20 20" width="18" height="18" aria-hidden="true" focusable="false"><path fill="currentColor" d="M10 1.667a8.333 8.333 0 1 0 0 16.666 8.333 8.333 0 0 0 0-16.666Zm.833 12.5H9.167V12.5h1.666v1.667Zm0-3.334H9.167V5.833h1.666v5Z"/></svg>' +
        '<span class="wpsc-auth-error-text"></span>' +
        "</div>"
    );
    var heading = container.children("h2").first();
    if (heading.length) {
      box.insertAfter(heading);
    } else {
      box.prependTo(container);
    }
  }
  box.find(".wpsc-auth-error-text").text(message);
  box.prop("hidden", false);
  if (fields && fields.length) {
    fields.addClass("wpsc-invalid").attr("aria-invalid", "true");
    fields.first().trigger("focus");
  }
}

/**
 * Hide the inline auth error and reset flagged fields
 */
function wpsc_auth_clear_error(el) {
  var container = jQuery(el).closest(".auth-inner-container");
  container.children(".wpsc-auth-error").prop("hidden", true);
  container
    .find(".wpsc-invalid")
    .removeClass("wpsc-invalid")
    .removeAttr("aria-invalid");
}

/**
 * Extract a readable message from an auth ajax response or failed jqXHR
 */
function wpsc_auth_error_message(response) {
  if (response && response.responseJSON !== undefined) {
    response = response.responseJSON;
  }
  var data = response && response.data;
  // WP_Error objects are serialized as an array of {code, message}.
  if (Array.isArray(data)) {
    data = data[0];
  }
  if (typeof data === "string" && data) {
    return data;
  }
  if (data && data.message) {
    return data.message;
  }
  return supportcandy.translations.something_wrong;
}

/**
 * Toggle the busy state of an auth submit button
 */
function wpsc_auth_button_busy(btn, busy) {
  btn = jQuery(btn);
  if (busy) {
    if (btn.prop("disabled")) {
      return;
    }
    btn
      .data("wpsc-label", btn.text())
      .text(supportcandy.translations.please_wait)
      .prop("disabled", true)
      .attr("aria-busy", "true");
  } else {
    btn
      .text(btn.data("wpsc-label") || btn.text())
      .prop("disabled", false)
      .removeAttr("aria-busy");
  }
}

// Un-flag an auth field as soon as the user edits it.
jQuery(document).on(
  "input change",
  ".auth-inner-container .wpsc-invalid",
  function () {
    jQuery(this).removeClass("wpsc-invalid").removeAttr("aria-invalid");
  }
);

/**
 * Send guest login OTP
 */
function wpsc_authenticate_guest_login(el) {
  var form = jQuery(el).closest("form");
  var dataform = new FormData(form[0]);
  var email = form.find('input[name="email_address"]');
  var email_address = dataform.get("email_address").trim();

  wpsc_auth_clear_error(el);
  if (!email_address) {
    wpsc_auth_show_error(el, supportcandy.translations.req_fields_missing, email);
    return;
  }
  if (!validateEmail(email_address)) {
    wpsc_auth_show_error(el, supportcandy.translations.incorrect_email, email);
    return;
  }

  wpsc_auth_button_busy(el, true);
  jQuery
    .ajax({
      url: supportcandy.ajax_url,
      type: "POST",
      data: dataform,
      processData: false,
      contentType: false,
    })
    .done(function (res) {
      if (typeof res == "object") {
        wpsc_auth_button_busy(el, false);
        wpsc_auth_show_error(el, wpsc_auth_error_message(res), email);
      } else {
        jQuery(el).closest(".auth-inner-container").html(res);
      }
    })
    .fail(function (xhr) {
      wpsc_auth_button_busy(el, false);
      wpsc_auth_show_error(el, wpsc_auth_error_message(xhr), email);
    });
}

/**
 * Confirm guest login OTP
 */
function wpsc_confirm_guest_login(el) {
  wpsc_auth_submit_otp(el, function () {
    window.location.reload();
  });
}

/**
 * Submit an OTP form and show an inline error when the code is rejected
 *
 * @param {Element} el - submit button
 * @param {Function} onSuccess - called with the response when the code is accepted
 */
function wpsc_auth_submit_otp(el, onSuccess) {
  var form = jQuery(el).closest("form");
  var dataform = new FormData(form[0]);
  var otp = form.find('input[name="otp"]');

  wpsc_auth_clear_error(el);
  if (!dataform.get("otp").trim()) {
    wpsc_auth_show_error(el, supportcandy.translations.req_fields_missing, otp);
    return;
  }

  wpsc_auth_button_busy(el, true);
  jQuery
    .ajax({
      url: supportcandy.ajax_url,
      type: "POST",
      data: dataform,
      processData: false,
      contentType: false,
    })
    .done(function (res) {
      if (res && res.isSuccess == 1) {
        onSuccess(res);
        return;
      }
      var message =
        res && res.isSuccess === 0 && !res.data
          ? supportcandy.translations.invalid_otp
          : wpsc_auth_error_message(res);
      wpsc_auth_button_busy(el, false);
      wpsc_auth_show_error(el, message, otp);
      otp.val("");
    })
    .fail(function (xhr) {
      wpsc_auth_button_busy(el, false);
      wpsc_auth_show_error(el, wpsc_auth_error_message(xhr), otp);
    });
}

/**
 * Validate input for URL
 */
function validateURL(url) {
  var re =
    /^(?:(?:https?|ftp):\/\/)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/\S*)?$/;
  return re.test(url);
}

/**
 * Validate input for number
 */
function validateNumber(number) {
  var re = /^[0-9]+$/;
  return re.test(number);
}

/**
 * Change event for filter
 */
function wpsc_tl_filter_change(el, type) {
  const filterSlug = jQuery(el).val()?.trim();
  const isTicketList = type === 'ticket_list';
  supportcandy.prevFilter = supportcandy.ticketList.filters.filterSlug;

  if (filterSlug === 'custom') {
    wpsc_tl_get_custom_filter( type );
    return;
  }

  supportcandy.ticketList.filters = { filterSlug };
  wpsc_tl_keep_quick_filters(supportcandy.ticketList.filters);
  isTicketList ? wpsc_get_tickets() : wpsc_get_archive_tickets();
}

/**
 * Copy currently selected quick filter values to given filters object
 */
function wpsc_tl_keep_quick_filters(filters) {
  jQuery(".wpsc-quick-filter").each(function () {
    const val = jQuery(this).val();
    if (val) {
      filters["quick_" + jQuery(this).data("quick")] = val;
    }
  });
}

/**
 * Sync quick filter dropdowns and clear button with given filters
 */
function wpsc_tl_sync_quick_filters(filters) {
  var active = false;
  jQuery(".wpsc-quick-filter").each(function () {
    var val = filters["quick_" + jQuery(this).data("quick")] || "";
    jQuery(this).val(val);
    wpsc_qf_render(this);
    active = active || !!val;
  });
  jQuery(".wpsc-quick-clear").toggle(active);
}

/**
 * Change event for quick filters
 */
function wpsc_tl_quick_filter_change(el, type) {
  wpsc_qf_render(el);
  supportcandy.ticketList.filters["quick_" + jQuery(el).data("quick")] = jQuery(el).val();
  supportcandy.ticketList.filters.page_no = 1;
  type === 'ticket_list' ? wpsc_get_tickets() : wpsc_get_archive_tickets();
}

/**
 * Quick filter popover: refresh button text, active state and selected option from hidden input
 */
function wpsc_qf_render(input) {
  var qf = jQuery(input).closest(".wpsc-qf");
  var val = String(jQuery(input).val() || "");
  var option = qf.find(".qf-options li").filter(function () {
    return String(jQuery(this).data("value")) === val;
  }).first();
  var label = qf.find(".qf-label").text();
  var text = val !== "" ? (option.length ? option.text() : String(jQuery(input).attr("data-text") || "")) : "";
  qf.find(".qf-options li").removeClass("selected");
  option.addClass("selected");
  qf.find(".qf-value").text(text);
  qf.find(".wpsc-qf-btn")
    .toggleClass("active", val !== "")
    .attr("title", text ? label + ": " + text : label);
}

function wpsc_qf_close() {
  jQuery(".wpsc-qf-panel:visible").hide();
  jQuery(".wpsc-qf-btn").attr("aria-expanded", "false");
}

/**
 * Position popover with fixed coordinates so that parent containers (overflow hidden) can not clip it
 */
function wpsc_qf_position(qf) {
  var btn = qf.find(".wpsc-qf-btn")[0];
  var panel = qf.find(".wpsc-qf-panel");
  var list = panel.find(".qf-options");
  var rect = btn.getBoundingClientRect();
  var vw = window.innerWidth;
  var vh = window.innerHeight;
  var gap = 6;
  var margin = 8;
  var rtl = jQuery("html").attr("dir") === "rtl" || jQuery("body").hasClass("rtl");

  // Height: use the larger of space below or above.
  var searchH = panel.find(".qf-search-wrap").outerHeight() || 0;
  var below = vh - rect.bottom - gap - margin;
  var above = rect.top - gap - margin;
  var openUp = below < 220 && above > below;
  var space = openUp ? above : below;
  list.css("max-height", Math.max(120, Math.min(260, space - searchH - 10)) + "px");

  var panelW = panel.outerWidth();
  var left = rtl ? rect.right - panelW : rect.left;
  left = Math.max(margin, Math.min(left, vw - panelW - margin));
  var panelH = panel.outerHeight();
  var top = openUp ? rect.top - gap - panelH : rect.bottom + gap;
  panel.css({ left: left + "px", top: Math.max(margin, top) + "px" });
}

function wpsc_qf_open(qf) {
  wpsc_qf_close();
    var panel = qf.find(".wpsc-qf-panel");
    if (qf.data("remote")) {
    if (!qf.data("initialHtml")) {
      qf.data("initialHtml", qf.find(".qf-options").html());
      qf.data("anyText", qf.find(".qf-options li[data-any]").text());
    } else {
      qf.find(".qf-options").html(qf.data("initialHtml"));
    }
    qf.data("page", 1);
    qf.data("hasMore", String(qf.attr("data-has-more")) === "1");
    qf.data("loading", false);
    wpsc_qf_render(qf.find(".wpsc-quick-filter"));
  }
  qf.find(".qf-search").val("");
  qf.find(".qf-options li").show();
  qf.find(".qf-empty").hide();
  panel.show();
  qf.find(".wpsc-qf-btn").attr("aria-expanded", "true");
  wpsc_qf_position(qf);
  var selected = qf.find(".qf-options li.selected").first();
  qf.find(".qf-options li").removeClass("hover");
  selected.addClass("hover");
  if (qf.find(".qf-search").length) {
    qf.find(".qf-search").trigger("focus");
  } else {
    qf.find(".wpsc-qf-btn").trigger("focus");
  }
  if (selected.length) {
    var list = qf.find(".qf-options");
    list.scrollTop(selected.position().top + list.scrollTop() - list.height() / 2);
  }
}

function wpsc_qf_choose(qf, li) {
  var input = qf.find(".wpsc-quick-filter");
  input.val(String(li.data("value")));
  input.attr("data-text", li.data("any") ? "" : li.text());
  wpsc_qf_close();
  qf.find(".wpsc-qf-btn").trigger("focus");
  wpsc_tl_quick_filter_change(input[0], qf.data("type"));
}

function wpsc_qf_move(qf, step) {
  var items = qf.find(".qf-options li:visible:not(.qf-loading)");
  if (!items.length) {
    return;
  }
  var index = items.index(items.filter(".hover").first()) + step;
  index = Math.max(0, Math.min(items.length - 1, index));
  qf.find(".qf-options li").removeClass("hover");
  var target = items.eq(index).addClass("hover");
  var list = qf.find(".qf-options");
  var top = target.position().top;
  if (top < 0) {
    list.scrollTop(list.scrollTop() + top);
  } else if (top + target.outerHeight() > list.height()) {
    list.scrollTop(list.scrollTop() + top + target.outerHeight() - list.height());
  }
}

jQuery(document).on("click", ".wpsc-qf-btn", function (e) {
  e.preventDefault();
  var qf = jQuery(this).closest(".wpsc-qf");
  qf.find(".wpsc-qf-panel").is(":visible") ? wpsc_qf_close() : wpsc_qf_open(qf);
});

jQuery(document).on("click", ".wpsc-qf-panel li:not(.qf-loading)", function () {
  wpsc_qf_choose(jQuery(this).closest(".wpsc-qf"), jQuery(this));
});

function wpsc_qf_remote_fetch(qf, page) {
  var term = qf.find(".qf-search").val().trim();
  var list = qf.find(".qf-options");
  qf.data("loading", true);
  if (page > 1) {
    list.append('<li class="qf-loading">' + (supportcandy.translations.please_wait || "...") + "</li>");
  }
  jQuery
    .post(
      supportcandy.ajax_url,
      {
        action: "wpsc_quick_filter_search",
        _ajax_nonce: supportcandy.nonce,
        key: qf.data("key"),
        term: term,
        page: page,
      },
      function (response) {
        list.find(".qf-loading").remove();
        if (qf.find(".qf-search").val().trim() !== term || !response || !Array.isArray(response.items)) {
          return;
        }
        var selected = String(qf.find(".wpsc-quick-filter").val() || "");
        if (page === 1) {
          var anyText = qf.data("anyText") || "";
          list.empty();
          if (!term) {
            jQuery("<li>").attr({ role: "option", "data-any": 1 }).data("value", "").text(anyText).appendTo(list);
          }
        }
        var existing = {};
        list.find("li").each(function () {
          existing[String(jQuery(this).data("value"))] = true;
        });
        response.items.forEach(function (item) {
          if (existing[String(item.id)]) {
            return;
          }
          jQuery("<li>")
            .attr({ role: "option", title: item.name })
            .data("value", String(item.id))
            .toggleClass("selected", String(item.id) === selected)
            .text(item.name)
            .appendTo(list);
        });
        qf.data("page", page);
        qf.data("hasMore", !!response.has_more);
        qf.find(".qf-empty").toggle(list.find("li").length === 0);
        if (page === 1) {
          list.scrollTop(0);
          list.find("li").removeClass("hover").first().addClass("hover");
        }
      }
    )
    .always(function () {
      qf.data("loading", false);
    });
}

document.addEventListener(
  "scroll",
  function (e) {
    var el = e.target;
    if (!el || !el.classList || !el.classList.contains("qf-options")) {
      return;
    }
    var qf = jQuery(el).closest(".wpsc-qf");
    if (!qf.data("remote") || qf.data("loading") || !qf.data("hasMore")) {
      return;
    }
    if (el.scrollTop + el.clientHeight >= el.scrollHeight - 40) {
      wpsc_qf_remote_fetch(qf, (qf.data("page") || 1) + 1);
    }
  },
  true
);

var wpsc_qf_remote_timer = null;

jQuery(document).on("input", ".wpsc-qf .qf-search", function () {
  var qf = jQuery(this).closest(".wpsc-qf");
  if (qf.data("remote")) {
    clearTimeout(wpsc_qf_remote_timer);
    wpsc_qf_remote_timer = setTimeout(function () {
      wpsc_qf_remote_fetch(qf, 1);
    }, 250);
    return;
  }
  var term = jQuery(this).val().toLowerCase().trim();
  var shown = 0;
  qf.find(".qf-options li").removeClass("hover").each(function () {
    var match = !term || jQuery(this).text().toLowerCase().indexOf(term) !== -1;
    jQuery(this).toggle(match);
    shown += match ? 1 : 0;
  });
  qf.find(".qf-options li:visible").first().addClass("hover");
  qf.find(".qf-empty").toggle(shown === 0);
});

jQuery(document).on("keydown", ".wpsc-qf", function (e) {
  var qf = jQuery(this);
  var open = qf.find(".wpsc-qf-panel").is(":visible");
  switch (e.key) {
    case "Escape":
      if (open) {
        e.preventDefault();
        wpsc_qf_close();
        qf.find(".wpsc-qf-btn").trigger("focus");
      }
      break;
    case "ArrowDown":
    case "ArrowUp":
      e.preventDefault();
      open ? wpsc_qf_move(qf, e.key === "ArrowDown" ? 1 : -1) : wpsc_qf_open(qf);
      break;
    case "Enter":
      if (open) {
        e.preventDefault();
        var target = qf.find(".qf-options li.hover:visible").first();
        if (target.length) {
          wpsc_qf_choose(qf, target);
        }
      }
      break;
    case "Tab":
      open && wpsc_qf_close();
      break;
  }
});

// Fixed panel does not follow page scroll/resize; close it.
// Exception: on touch devices focusing the search field opens the on-screen keyboard,
// which scrolls/resizes the viewport. Reposition instead of closing in that case.
function wpsc_qf_search_focused() {
  var a = document.activeElement;
  return !!(a && jQuery(a).is(".qf-search") && jQuery(a).closest(".wpsc-qf-panel").length);
}
function wpsc_qf_reposition_open() {
  jQuery(".wpsc-qf-panel:visible").each(function () {
    wpsc_qf_position(jQuery(this).closest(".wpsc-qf"));
  });
}
window.addEventListener(
  "scroll",
  function (e) {
    if (jQuery(e.target).closest(".wpsc-qf-panel").length) {
      return;
    }
    if (wpsc_qf_search_focused()) {
      wpsc_qf_reposition_open();
      return;
    }
    wpsc_qf_close();
  },
  true
);
var wpsc_qf_width = window.innerWidth;
jQuery(window).on("resize", function () {
  var widthChanged = window.innerWidth !== wpsc_qf_width;
  wpsc_qf_width = window.innerWidth;
  if (!widthChanged && wpsc_qf_search_focused()) {
    wpsc_qf_reposition_open();
    return;
  }
  wpsc_qf_close();
});

jQuery(document).on("mousedown", function (e) {
  if (!jQuery(e.target).closest(".wpsc-qf").length) {
    wpsc_qf_close();
  }
});

/**
 * Clear all quick filters
 */
function wpsc_tl_quick_filters_clear(type) {
  jQuery(".wpsc-quick-filter").each(function () {
    delete supportcandy.ticketList.filters["quick_" + jQuery(this).data("quick")];
  });
  supportcandy.ticketList.filters.page_no = 1;
  type === 'ticket_list' ? wpsc_get_tickets() : wpsc_get_archive_tickets();
}

/**
 * Change ticket list page
 */
function wpsc_tl_set_page(page, type) {
  switch (page) {
    case "first":
      if (supportcandy.ticketList.pagination.current_page == 1) {
        return;
      }
      supportcandy.ticketList.filters.page_no = 1;
      break;

    case "prev":
      if (supportcandy.ticketList.pagination.current_page < 2) {
        return;
      }
      supportcandy.ticketList.filters.page_no =
        supportcandy.ticketList.pagination.current_page - 1;
      break;

    case "next":
      if (!supportcandy.ticketList.pagination.has_next_page) {
        return;
      }
      supportcandy.ticketList.filters.page_no =
        supportcandy.ticketList.pagination.current_page + 1;
      break;

    case "last":
      if (
        supportcandy.ticketList.pagination.current_page ==
        supportcandy.ticketList.pagination.total_pages
      ) {
        return;
      }
      supportcandy.ticketList.filters.page_no =
        supportcandy.ticketList.pagination.total_pages;
      break;
  }
  type === 'ticket_list' ? wpsc_get_tickets() : wpsc_get_archive_tickets();
}

/**
 * Reset ticket list filters
 */
function wpsc_tl_reset_filter( type ) {
  var filters = { filterSlug: "" };
  supportcandy.ticketList.filters = filters;
  type === 'ticket_list' ? wpsc_get_tickets() : wpsc_get_archive_tickets();
}

/**
 * Search input keyup
 */
function wpsc_tl_search_keyup(e, el, type) {
  if (e.keyCode !== 13) {
    return;
  }
  var search = jQuery(el).val().trim();
  supportcandy.ticketList.filters.search = search;
  supportcandy.ticketList.filters.page_no = 1;
  type === 'ticket_list' ? wpsc_get_tickets() : wpsc_get_archive_tickets();
}

/**
 * Apply filters
 */
function wpsc_tl_apply_filter_btn_click( type ) {
  supportcandy.ticketList.filters.search = jQuery("input.wpsc-search-input")
    .val()
    .trim();
  supportcandy.ticketList.filters.orderby = jQuery(
    "select.wpsc-input-sort-by"
  ).val();
  supportcandy.ticketList.filters.order = jQuery(
    "select.wpsc-input-sort-order"
  ).val();
  supportcandy.ticketList.filters.page_no = 1;
  type === 'ticket_list' ? wpsc_get_tickets() : wpsc_get_archive_tickets();
}

/**
 * Sort ticket list by clicking on a sortable column header.
 * Toggles ASC/DESC when the same column is clicked again, otherwise
 * defaults to ASC for the newly selected column.
 */
function wpsc_tl_sort_column( slug, type ) {
  var filters = supportcandy.ticketList.filters;
  if ( filters.orderby === slug ) {
    filters.order = filters.order === 'ASC' ? 'DESC' : 'ASC';
  } else {
    filters.orderby = slug;
    filters.order = 'ASC';
  }
  filters.page_no = 1;
  type === 'ticket_list' ? wpsc_get_tickets() : wpsc_get_archive_tickets();
}

/**
 * Get custom filter UI
 */
function wpsc_tl_get_custom_filter( type ) {
  wpsc_show_modal();
  
  const isTicketList = type === 'ticket_list';
  const action = isTicketList ? 'wpsc_get_tl_custom_filter' : 'wpsc_get_atl_custom_filter';

  const data = {
    action,
    _ajax_nonce: supportcandy.nonce,
  };
  if (supportcandy.ticketList.filters.filterSlug === 'custom') {
    data.filters = supportcandy.ticketList.filters;
  }
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery('.wpsc-modal-header').text(response.title);
    jQuery('.wpsc-modal-body').html(response.body);
    jQuery('.wpsc-modal-footer').html(response.footer);
    wpsc_show_modal_inner_container();
  });
}

/**
 * Apply custom filter
 */
function wpsc_tl_apply_custom_filter(el, type) {

  var filters = wpsc_get_condition_json("custom_filters");
  if (
    filters.length === 0 ||
    (filters.length === 1 && filters[0].length === 0)
  ) {
    alert(supportcandy.translations.req_fields_missing);
    return;
  }
  
  const cust_filter = type === "ticket_list" ? "wpsc-tl-custom-filter" : "wpsc-atl-custom-filter";
  filters = {
    filterSlug: "custom",
    "parent-filter": jQuery(
      "." + cust_filter + " select[name=parent-filter]"
    ).val(),
    filters: JSON.stringify(filters),
    orderby: jQuery("." + cust_filter + " select[name=sort-by]").val(),
    order: jQuery("." + cust_filter + " select[name=sort-order]").val(),
    page_no: 1,
  };
  wpsc_tl_keep_quick_filters(filters);
  supportcandy.ticketList.filters = filters;

  wpsc_close_modal();
  type === "ticket_list" ? wpsc_get_tickets() : wpsc_get_archive_tickets();
}

/**
 * Edit ticket list filter
 */
function wpsc_tl_edit_filter( type ) {
  if (supportcandy.ticketList.filters.filterSlug == "custom") {
    wpsc_tl_get_custom_filter( type );
  } else {
    type === "ticket_list" ? wpsc_tl_get_edit_saved_filter() : wpsc_atl_get_edit_saved_filter();
  }
}

/**
 * Add saved filter
 */
function wpsc_tl_add_saved_filter() {
  var filters = wpsc_get_condition_json("custom_filters");
  if (
    filters.length === 0 ||
    (filters.length === 1 && filters[0].length === 0)
  ) {
    alert(supportcandy.translations.req_fields_missing);
    return;
  }

  filters = {
    "parent-filter": jQuery(
      ".wpsc-tl-custom-filter select[name=parent-filter]"
    ).val(),
    filters: JSON.stringify(filters),
    orderby: jQuery(".wpsc-tl-custom-filter select[name=sort-by]").val(),
    order: jQuery(".wpsc-tl-custom-filter select[name=sort-order]").val(),
  };

  supportcandy.tempFilters = filters;
  wpsc_close_modal();
  setTimeout(function () {
    wpsc_tl_get_add_saved_filter();
  }, 500);
}

/**
 * Get add saved filter ui
 */
function wpsc_tl_get_add_saved_filter() {
  wpsc_show_modal();
  var data = { action: "wpsc_tl_get_add_saved_filter" };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    // Set to modal.
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    // Display modal.
    wpsc_show_modal_inner_container();
  });
}

/**
 * Save and apply custom filter
 */
function wpsc_tl_set_add_saved_filter(el, nonce) {
  var label = jQuery("input.wpsc-cf-label").val().trim();
  if (!label) {
    alert(supportcandy.translations.req_fields_missing);
    return;
  }

  jQuery(".wpsc-modal-footer button").attr("disabled", true);
  jQuery(el).text(supportcandy.translations.please_wait);

  var data = {
    action: "wpsc_tl_set_add_saved_filter",
    filters: supportcandy.tempFilters,
    _ajax_nonce: nonce,
  };
  data.filters.label = label;

  jQuery.post(supportcandy.ajax_url, data, function (response) {
    supportcandy.ticketList.filters = { filterSlug: response.slug };
    wpsc_close_modal();
    wpsc_get_ticket_list();
  });
}

/**
 * Get edit saved filter modal
 */
function wpsc_tl_get_edit_saved_filter() {
  wpsc_show_modal();
  var data = {
    action: "wpsc_tl_get_edit_saved_filter",
    filterSlug: supportcandy.ticketList.filters.filterSlug,
    _ajax_nonce: supportcandy.nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    // Set to modal.
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    // Display modal.
    wpsc_show_modal_inner_container();
  });
}

/**
 * Set edit saved filter
 */
function wpsc_tl_set_edit_saved_filter(el) {
  var label = jQuery("input.wpsc-cf-label").val().trim();
  if (!label) {
    alert(supportcandy.translations.req_fields_missing);
    return;
  }

  var filters = wpsc_get_condition_json("custom_filters");
  if (
    filters.length === 0 ||
    (filters.length === 1 && filters[0].length === 0)
  ) {
    alert(supportcandy.translations.req_fields_missing);
    return;
  }

  jQuery(".wpsc-modal-footer button").attr("disabled", true);
  jQuery(el).text(supportcandy.translations.please_wait);

  var form = jQuery(".wpsc-tl-custom-filter")[0];
  var dataform = new FormData(form);
  dataform.append("filters", JSON.stringify(filters));

  jQuery
    .ajax({
      url: supportcandy.ajax_url,
      type: "POST",
      data: dataform,
      processData: false,
      contentType: false,
    })
    .done(function (res) {
      wpsc_close_modal();
      wpsc_get_ticket_list();
    });
}

/**
 * Delete saved filter
 */
function wpsc_tl_delete_saved_filter( type ) {
  var flag = confirm(supportcandy.translations.confirm);
  if (!flag) {
    return;
  }

  var data = {
    action: "wpsc_tl_delete_saved_filter",
    slug: supportcandy.ticketList.filters.filterSlug,
    _ajax_nonce: supportcandy.nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (res) {
    supportcandy.ticketList.filters = { filterSlug: "" };
    wpsc_get_ticket_list();
  });
}

/**
 * Close custom filter modal
 */
function wpsc_tl_close_custom_filter_modal() {
  jQuery("select.wpsc-input-filter").val(supportcandy.prevFilter);
  wpsc_close_modal();
}

/**
 * Get form filter operators
 */
function wpsc_tc_get_operators(el, nonce) {
  var content = jQuery(el).closest(".content");
  var slug = jQuery(el).val().trim();

  content.find(".conditional, .wpsc-inline-loader").remove();
  if (!slug) {
    return;
  }

  content.append(supportcandy.inline_loader);
  var data = {
    action: "wpsc_tc_get_operators",
    slug: slug,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    content.find(".wpsc-inline-loader").remove();
    content.append(response);
  });
}

/**
 * Get email notification condition operands
 */
function wpsc_tc_get_operand(el, slug, nonce) {
  var content = jQuery(el).closest(".content");
  var operator = jQuery(el).val().trim();

  content.find(".conditional.operand, .wpsc-inline-loader").remove();
  if (!operator) {
    return;
  }

  content.append(supportcandy.inline_loader);
  var data = {
    action: "wpsc_tc_get_operand",
    operator,
    slug,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    content.find(".wpsc-inline-loader").remove();
    content.append(response);
  });
}

/**
 * Add image to editor
 *
 * @param {*} editor
 */
function wpsc_add_custom_image_tinymce(editor, nonce) {
  wpsc_show_modal();
  var data = {
    action: "wpsc_add_custom_image_tinymce",
    editor_id: editor.id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    // Set to modal.
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    // Display modal.
    wpsc_show_modal_inner_container();
  });
}

/**
 * Check whether given url has image or not
 *
 * @param {*} url
 */
function isValidImageURL(url) {
  if (typeof url !== "string") {
    return false;
  }
  return !!url.match(/\w+\.(jpg|jpeg|gif|png|tiff|bmp)$/gi);
}

/**
 * Upload image to tinymce editor
 *
 * @param {*} el
 */
function wpsc_insert_editor_img(ed) {
  if (
    jQuery("#wpsc-tinymce-image-url").val().trim() === "" &&
    validateURL(jQuery("#wpsc-tinymce-image-url").val().trim()) &&
    isValidImageURL(jQuery("#wpsc-tinymce-image-url").val().trim())
  ) {
    alert(supportcandy.translations.req_fields_error);
    return;
  }

  if (jQuery("#wpsc-tinymce-image-width").val().trim() === "") {
    alert(supportcandy.translations.req_fields_error);
    return;
  }

  if (jQuery("#wpsc-tinymce-image-height").val().trim() === "") {
    alert(supportcandy.translations.req_fields_error);
    return;
  }

  var is_tinymce =
    typeof tinyMCE != "undefined" &&
    tinyMCE.activeEditor &&
    !tinyMCE.activeEditor.isHidden();
  if (!is_tinymce) {
    return;
  }

  wpsc_close_modal();

  var source = jQuery("#wpsc-tinymce-image-url").val().trim();
  var height = jQuery("#wpsc-tinymce-image-height").val().trim();
  var width = jQuery("#wpsc-tinymce-image-width").val().trim();
  tinymce
    .get(ed)
    .execCommand(
      "mceInsertContent",
      false,
      '<img height="' +
        height +
        '" width="' +
        width +
        '" src="' +
        source +
        '"/>'
    );
}

/**
 * Add image to editor
 *
 * @param {*} editor
 */
function wpsc_edit_custom_image_tinymce(editor, image_obj, nonce) {
  if (!(editor && image_obj)) {
    alert(supportcandy.translations.req_fields_error);
    return;
  }

  wpsc_show_modal();
  var data = {
    action: "wpsc_edit_custom_image_tinymce",
    editor_id: editor.id,
    height: image_obj.height,
    width: image_obj.width,
    src: image_obj.src,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    // Set to modal.
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    // Display modal.
    wpsc_show_modal_inner_container();
  });
}

/**
 * Get create ticket from thread
 */
function wpsc_it_thread_new_ticket(el, ticket_id, thread_id, nonce) {
  wpsc_show_modal();
  var data = {
    action: "wpsc_it_thread_new_ticket",
    ticket_id,
    thread_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    wpsc_show_modal_inner_container();
  });
}

/**
 * Get thread info
 *
 * @param {*} thread_id
 */
function wpsc_it_thread_info(el, ticket_id, thread_id, nonce, type = 'ticket_list') {
  wpsc_show_modal();
  var data = {
    action: type === 'ticket_list' ? "wpsc_it_thread_info" : "wpsc_it_archive_thread_info",
    thread_id,
    ticket_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    wpsc_show_modal_inner_container();
  });
}

/**
 * Set thread new ticket
 */
function wpsc_it_set_thread_new_ticket(el, thread_id, nonce) {
  var form = jQuery("form.new_ticket_from_thread")[0];
  var dataform = new FormData(form);
  jQuery(".wpsc-modal-footer button").attr("disabled", true);
  jQuery(el).text(supportcandy.translations.please_wait);
  jQuery
    .ajax({
      url: supportcandy.ajax_url,
      type: "POST",
      data: dataform,
      processData: false,
      contentType: false,
    })
    .done(function (response) {
      wpsc_close_modal();
      wpsc_get_individual_ticket(response.ticket_id);
    });
}

/**
 * Get customer other tickets
 */
function wpsc_get_rb_other_tickets(el, ticket_id, nonce) {
  wpsc_show_modal();
  var data = {
    action: "wpsc_get_rb_other_tickets",
    ticket_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    wpsc_show_modal_inner_container();
  });
}

/**
 * Get raised by info
 */
function wpsc_get_rb_info(el, ticket_id, nonce) {
  wpsc_show_modal();
  var data = { action: "wpsc_get_rb_info", ticket_id, _ajax_nonce: nonce };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    wpsc_show_modal_inner_container();
  });
}

/**
 * Thread expander.
 */
function wpsc_init_thread_expanders($threadTexts) {
  $threadTexts.filter(".wpsc-collapsed").each(function () {
    if (this.scrollHeight > this.clientHeight + 1) {
      jQuery(this)
        .parent()
        .find(".wpsc-ticket-thread-expander")
        .text(supportcandy.translations.view_more)
        .show();
    } else {
      // Content already fits within the collapsed max-height; no need
      // for an expander link.
      jQuery(this).removeClass("wpsc-collapsed");
    }
  });
}

function wpsc_ticket_thread_expander_toggle(el) {
  var $threadText = jQuery(el).parent().find(".thread-text");
  if ($threadText.hasClass("wpsc-collapsed")) {
    $threadText.removeClass("wpsc-collapsed").addClass("wpsc-expanded");
    jQuery(el).text(supportcandy.translations.view_less);
  } else {
    $threadText.removeClass("wpsc-expanded").addClass("wpsc-collapsed");
    jQuery(el).text(supportcandy.translations.view_more);
  }
}

/**
 * Get change bulk status
 */
function wpsc_bulk_change_status(nonce) {
  var items = jQuery(".wpsc-bulk-select:checked");
  var checked = items.length === 0 ? false : true;
  jQuery(".wpsc-bulk-selector").prop("checked", checked);
  if (items.length != 0) {
    var ticket_ids = jQuery(".wpsc-bulk-select:checked")
      .map(function () {
        return this.value;
      })
      .get();
    wpsc_show_modal();
    var data = {
      action: "wpsc_bulk_change_status",
      ticket_ids,
      _ajax_nonce: nonce,
    };
    jQuery.post(supportcandy.ajax_url, data, function (response) {
      // Set to modal.
      jQuery(".wpsc-modal-header").text(response.title);
      jQuery(".wpsc-modal-body").html(response.body);
      jQuery(".wpsc-modal-footer").html(response.footer);
      // Display modal.
      wpsc_show_modal_inner_container();
    });
  }
}

/**
 * Load older threads
 */
function wpsc_load_older_threads(el, ticket_id) {
  if (supportcandy.reply_form_position === "top") {
    jQuery(".wpsc-it-thread-section-container").append(
      supportcandy.loader_html
    );
  } else {
    jQuery(".wpsc-it-thread-section-container").prepend(
      supportcandy.loader_html
    );
  }

  var oldBtnContainer = jQuery(el).parent();
  oldBtnContainer.hide();

  var data = {
    action: "wpsc_load_older_threads",
    ticket_id,
    last_thread: supportcandy.threads.last_thread,
  };
  wpsc_add_url_auth_code(data);
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-it-thread-section-container").find(".wpsc-loader").remove();
    // add threads.
    var $container = jQuery(".wpsc-it-thread-section-container");
    var $before = $container.find(".thread-text");
    if (supportcandy.reply_form_position === "top") {
      $container.append(response.threads);
    } else {
      var scrollHeightBody = jQuery("body").prop("scrollHeight");
      var windowScrollTop = jQuery(window).scrollTop();
      $container.prepend(response.threads);
      var scrollDiff = jQuery("body").prop("scrollHeight") - scrollHeightBody;
      jQuery(window).scrollTop(windowScrollTop + scrollDiff);
    }
    // Newly injected threads also need their "view more" state evaluated -
    // the initial page-load script only runs once, so without this the
    // lazily-loaded threads never get truncated/expandable.
    wpsc_init_thread_expanders($container.find(".thread-text").not($before));
    // show btn if there are more threads available.
    if (response.has_next_page) {
      supportcandy.threads.last_thread = response.last_thread;
      oldBtnContainer.show();
    }
  });
}

/**
 * Add ticket url auth code (guest opened ticket by link) to ajax data
 */
function wpsc_add_url_auth_code(data) {
  var search_params = new URL(window.location.href).searchParams;
  var auth_code =
    search_params.get("auth-code") || search_params.get("auth_code");
  // "auth_code" with "ticket_id" triggers the old ticket url redirect, so use "auth-code".
  if (auth_code) {
    data["auth-code"] = auth_code;
  }
  return data;
}

/**
 * Switch thread view (all | conversation) and reload threads from first page
 */
function wpsc_set_thread_view(el, ticket_id, archive) {
  var $btn = jQuery(el);
  if ($btn.hasClass("active")) return;
  var $buttons = jQuery(".wpsc-it-thread-view-btn");
  var $prev = $buttons.filter(".active");
  var mark = function ($active) {
    $buttons.removeClass("active").attr("aria-pressed", "false");
    $active.addClass("active").attr("aria-pressed", "true");
  };
  var $container = jQuery(".wpsc-it-thread-section-container");
  mark($btn);
  $buttons.prop("disabled", true);
  $container.html(supportcandy.loader_html);
  var data = {
    action: archive
      ? "wpsc_load_older_archive_threads"
      : "wpsc_load_older_threads",
    ticket_id,
    first_page: 1,
    thread_view: $btn.data("view"),
    _ajax_nonce: supportcandy.nonce,
  };
  jQuery
    .post(supportcandy.ajax_url, data, function (response) {
      $container.html(response.threads);
      wpsc_init_thread_expanders($container.find(".thread-text"));
      supportcandy.threads.last_thread = response.last_thread;
      jQuery(".wpsc-it-load-older").css(
        "display",
        response.has_next_page ? "flex" : "none"
      );
    })
    .fail(function () {
      // revert so the buttons match the view still saved on server.
      mark($prev);
      $container.empty();
      alert(supportcandy.translations.something_wrong);
    })
    .always(function () {
      $buttons.prop("disabled", false);
    });
}

/**
 * Set change bulk status
 */
function wpsc_set_bulk_change_status(el) {
  var form = jQuery("form.frm-edit-bulk-status")[0];
  var dataform = new FormData(form);
  jQuery(".wpsc-modal-footer button").attr("disabled", true);
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
      wpsc_close_modal();
      wpsc_get_ticket_list();
      wpsc_run_ajax_background_process();
    });
}

/**
 * Get bulk assign agent
 */
function wpsc_bulk_assign_agents(nonce) {
  var items = jQuery(".wpsc-bulk-select:checked");
  var checked = items.length === 0 ? false : true;
  jQuery(".wpsc-bulk-selector").prop("checked", checked);
  if (items.length != 0) {
    var ticket_ids = jQuery(".wpsc-bulk-select:checked")
      .map(function () {
        return this.value;
      })
      .get();
    wpsc_show_modal();
    var data = {
      action: "wpsc_bulk_assign_agents",
      ticket_ids,
      _ajax_nonce: nonce,
    };
    jQuery.post(supportcandy.ajax_url, data, function (response) {
      // Set to modal.
      jQuery(".wpsc-modal-header").text(response.title);
      jQuery(".wpsc-modal-body").html(response.body);
      jQuery(".wpsc-modal-footer").html(response.footer);
      // Display modal.
      wpsc_show_modal_inner_container();
    });
  }
}

/**
 * Set change assign agent
 */
function wpsc_set_bulk_assign_agent(el) {
  var form = jQuery("form.frm-bulk-assign-agent")[0];
  var dataform = new FormData(form);
  jQuery(".wpsc-modal-footer button").attr("disabled", true);
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
      wpsc_close_modal();
      wpsc_get_ticket_list();
      wpsc_run_ajax_background_process();
    });
}

/**
 * Get bulk assign tags
 */
function wpsc_bulk_assign_tags(nonce) {
  var items = jQuery(".wpsc-bulk-select:checked");
  var checked = items.length === 0 ? false : true;
  jQuery(".wpsc-bulk-selector").prop("checked", checked);
  if (items.length != 0) {
    var ticket_ids = jQuery(".wpsc-bulk-select:checked")
      .map(function () {
        return this.value;
      })
      .get();
    wpsc_show_modal();
    var data = {
      action: "wpsc_bulk_assign_tags",
      ticket_ids,
      _ajax_nonce: nonce,
    };
    jQuery.post(supportcandy.ajax_url, data, function (response) {
      // Set to modal.
      jQuery(".wpsc-modal-header").text(response.title);
      jQuery(".wpsc-modal-body").html(response.body);
      jQuery(".wpsc-modal-footer").html(response.footer);
      // Display modal.
      wpsc_show_modal_inner_container();
    });
  }
}

/**
 * Set change assign tags
 */
function wpsc_set_bulk_assign_tag(el) {
  var form = jQuery("form.frm-bulk-assign-tags")[0];
  var dataform = new FormData(form);

  var tags = dataform.getAll("tags[]");
  if (!tags.length) {
    return;
  }

  jQuery(".wpsc-modal-footer button").attr("disabled", true);
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
      wpsc_close_modal();
      wpsc_get_ticket_list();
      wpsc_run_ajax_background_process();
    });
}

/**
 * Get bulk archive ticket
 */
function wpsc_bulk_archive_tickets(nonce) {
  var items = jQuery(".wpsc-bulk-select:checked");
  var checked = items.length === 0 ? false : true;
  jQuery(".wpsc-bulk-selector").prop("checked", checked);
  if (items.length != 0) {
    var ticket_ids = jQuery(".wpsc-bulk-select:checked")
      .map(function () {
        return this.value;
      })
      .get();
    var flag = confirm(supportcandy.translations.confirm);
    if (!flag) {
      return;
    }

    var data = {
      action: "wpsc_bulk_archive_tickets",
      ticket_ids,
      _ajax_nonce: nonce,
    };
    jQuery.post(supportcandy.ajax_url, data, function (response) {
      wpsc_get_ticket_list();
      wpsc_run_ajax_background_process();
    });
  }
}

/**
 * Get bulk permanently delete ticket
 */
function wpsc_bulk_permanently_delete_tickets(nonce) {
  var items = jQuery(".wpsc-bulk-select:checked");
  var checked = items.length === 0 ? false : true;
  jQuery(".wpsc-bulk-selector").prop("checked", checked);
  if (items.length != 0) {
    var ticket_ids = jQuery(".wpsc-bulk-select:checked")
      .map(function () {
        return this.value;
      })
      .get();
    var flag = confirm(supportcandy.translations.delete_permanently);
    if (!flag) {
      return;
    }

    var data = {
      action: "wpsc_bulk_permanently_delete_tickets",
      ticket_ids,
      _ajax_nonce: nonce,
    };
    jQuery.post(supportcandy.ajax_url, data, function (response) {
      wpsc_get_ticket_list();
      wpsc_run_ajax_background_process();
    });
  }
}

/**
 * Set agent working hrs
 */
function wpsc_set_agent_wh_hrs(el) {
  const form = jQuery(".wpsc-frm-agent-wh")[0];
  const dataform = new FormData(form);
  jQuery(el).text(supportcandy.translations.please_wait);
  jQuery(".wpsc-section-container").html(supportcandy.loader_html);
  jQuery
    .ajax({
      url: supportcandy.ajax_url,
      type: "POST",
      data: dataform,
      processData: false,
      contentType: false,
    })
    .done(function (res) {
      window.location.reload();
    });
}

/**
 * Set add agent exception
 */
function wpsc_set_add_agent_wh_exception(el) {
  var title = jQuery("input[name=title]").val();
  var from_date = jQuery("input[name=exception_date]").val();
  if (!title || !from_date) {
    alert(supportcandy.translations.req_fields_missing);
    return;
  }
  const form = jQuery(".wpsc-frm-add-agent-exception")[0];
  const dataform = new FormData(form);
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
      window.location.reload();
    });
}

/**
 * Update agent working hour exception
 */
function wpsc_set_edit_agent_wh_exception(el) {
  var title = jQuery("input[name=title]").val();
  var from_date = jQuery("input[name=exception_date]").val();
  if (!title || !from_date) {
    alert(supportcandy.translations.req_fields_missing);
    return;
  }

  const form = jQuery(".wpsc-frm-edit-agent-exception")[0];
  const dataform = new FormData(form);
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
      window.location.reload();
    });
}

/**
 * Get agent profile holiday actions
 */
function wpsc_get_ap_leaves_actions(dateSelected, nonce) {
  supportcandy.temp.dateSelected = dateSelected;
  wpsc_show_modal();
  var data = {
    action: "wpsc_get_ap_leaves_actions",
    dateSelected,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    // Set to modal.
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    // Display modal.
    wpsc_show_modal_inner_container();
  });
}

/**
 * Set agent profile holiday actions
 */
function wpsc_set_ap_leaves_actions(el) {
  const form = jQuery(".wpsc-frm-ap-holiday-actions")[0];
  const dataform = new FormData(form);
  dataform.append("dateSelected", supportcandy.temp.dateSelected);
  jQuery(el).text(supportcandy.translations.please_wait);
  jQuery
    .ajax({
      url: supportcandy.ajax_url,
      type: "POST",
      data: dataform,
      processData: false,
      contentType: false,
    })
    .done(function (response) {
      jQuery.each(supportcandy.temp.dateSelected, function (index, value) {
        if (response.action == "add" && response.is_recurring == 1) {
          jQuery("td")
            .find("[data-date=" + value + "]")
            .css({ "background-color": "#eb4d4b" });
        } else if (response.action == "add" && response.is_recurring == 0) {
          jQuery("td")
            .find("[data-date=" + value + "]")
            .css({ "background-color": "#f0932b" });
        } else {
          jQuery("td")
            .find("[data-date=" + value + "]")
            .css("background-color", "unset");
        }
      });
      supportcandy.temp.holidayList = response.holidayList;
      wpsc_close_modal();
    });
}

/**
 * Clear dates
 */
function wpsc_clear_date(el) {
  jQuery(el).prev().val("");

  var conditionalFields = jQuery(".wpsc-tff.conditional");
  if (conditionalFields.length !== 0) {
    wpsc_check_tff_visibility();
  }
}

/**
 * Check whether there is text in reply box
 */
function wpsc_is_description_text() {
  var is_tinymce =
    typeof tinyMCE != "undefined" &&
    tinyMCE.activeEditor &&
    !tinyMCE.activeEditor.isHidden();
  if (is_tinymce && tinymce.get("description")) {
    var description = tinyMCE.get("description").getContent();
  } else {
    var description = jQuery("#description").val();
  }

  var flag = false;
  if (description && description.length != 0) {
    flag = true;
  }

  return flag;
}

/**
 * Get Edit customer info
 *
 * @param {Int} ticket_id
 */
function wpsc_get_edit_rb_info(el, ticket_id, nonce) {
  wpsc_show_modal();
  var data = { action: "wpsc_get_edit_rb_info", ticket_id, _ajax_nonce: nonce };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    wpsc_show_modal_inner_container();
  });
}

/**
 * Set edit customer info
 *
 * @param {object} el
 * @param {Int} ticket_id
 */
function wpsc_set_edit_rb_info(el, ticket_id, nonce) {
  var form = jQuery("form.frm-edit-rb-info")[0];
  var dataform = new FormData(form);
  jQuery(".wpsc-modal-footer button").attr("disabled", true);
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
      wpsc_close_modal();
      setTimeout(function () {
        wpsc_get_rb_info(el, ticket_id, nonce);
      }, 500);
    });
}

/**
 * Enable/disable pagination buttons based on current page
 *
 * @param {Object} pagination
 */
function wpsc_update_pagination_buttons(pagination) {
  jQuery(".wpsc-pagination-btn").toggle(pagination.total_pages > 1);
  var isFirst = pagination.current_page <= 1;
  var isLast = pagination.current_page >= pagination.total_pages;
  jQuery(".wpsc-pagination-first, .wpsc-pagination-prev").toggleClass("disabled", isFirst);
  jQuery(".wpsc-pagination-next, .wpsc-pagination-last").toggleClass("disabled", isLast);
}

/**
 * Get tickets based on filter set
 */
function wpsc_get_archive_tickets() {
  jQuery(".wpsc-bulk-selector").prop("checked", false);
  jQuery(".wpsc-ticket-list").html(supportcandy.loader_html);

  var data = {
    action: "wpsc_get_archive_tickets",
    _ajax_nonce: supportcandy.nonce,
    is_frontend : supportcandy.is_frontend
  };
  if (
    typeof supportcandy.ticketList != "undefined" &&
    typeof supportcandy.ticketList.filters != "undefined"
  ) {
    data.filters = supportcandy.ticketList.filters;
  }

  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-filter-actions").html(response.filter_actions);
    jQuery(".wpsc-ticket-bulk-actions").html(response.bulk_actions);
    jQuery(".wpsc-ticket-list").html(response.tickets);
    jQuery(".wpsc-pagination-txt").text(response.pagination_str);
    jQuery("select.wpsc-input-filter").val(response.filters.filterSlug);
    jQuery("select.wpsc-input-sort-by").val(response.filters.orderby);
    jQuery("select.wpsc-input-sort-order").val(response.filters.order);
    jQuery("input.wpsc-search-input").val(response.filters.search);
    wpsc_tl_sync_quick_filters(response.filters);
    wpsc_update_pagination_buttons(response.pagination);
    supportcandy.ticketList = {
      filters: response.filters,
      pagination: response.pagination,
    };
  });
}

/**
 * Get tickets based on filter set
 */
function wpsc_get_tickets() {

  if (supportcandy.ticket_list_loading) {
    return;
  }

  supportcandy.ticket_list_loading = true;

  jQuery(".wpsc-bulk-selector").prop("checked", false);
  jQuery(".wpsc-ticket-list").html(supportcandy.loader_html);

  var data = {
    action: "wpsc_get_tickets",
    _ajax_nonce: supportcandy.nonce,
    is_frontend: supportcandy.is_frontend,
  };

  if (
    typeof supportcandy.ticketList != "undefined" &&
    typeof supportcandy.ticketList.filters != "undefined"
  ) {
    data.filters = supportcandy.ticketList.filters;
  }

  jQuery.post(supportcandy.ajax_url, data, function (response) {

    jQuery(".wpsc-filter-actions").html(response.filter_actions);
    jQuery(".wpsc-ticket-bulk-actions").html(response.bulk_actions);
    jQuery(".wpsc-ticket-list").html(response.tickets);
    jQuery(".wpsc-pagination-txt").text(response.pagination_str);
    jQuery("select.wpsc-input-filter").val(response.filters.filterSlug);
    jQuery("select.wpsc-input-sort-by").val(response.filters.orderby);
    jQuery("select.wpsc-input-sort-order").val(response.filters.order);
    jQuery("input.wpsc-search-input").val(response.filters.search);
    wpsc_tl_sync_quick_filters(response.filters);

    wpsc_update_pagination_buttons(response.pagination);

    supportcandy.ticketList = {
      filters: response.filters,
      pagination: response.pagination,
    };

  }).always(function(){
      supportcandy.ticket_list_loading = false;
  });
}

/**
 * Copy ticket url
 *
 * @param {int} ticket_id
 */
function wpsc_it_copy_url(ticket_id) {
  var temp = jQuery("<input>");
  jQuery("body").append(temp);
  temp.val(jQuery("#wpsc-ticket-url").text()).select();
  document.execCommand("copy");
  temp.remove();
  alert(supportcandy.translations.copy_url);
}

/**
 * Open auto-refresh toggle
 */
function wpsc_get_tl_auto_refresh() {
  wpsc_show_modal();

  // Get modal parts from static modals.
  var headerText = jQuery(
    ".wpsc-tl_snippets .auto-refresh .modal-header"
  ).text();
  var bodyHTML = jQuery(".wpsc-tl_snippets .auto-refresh .modal-body").html();
  var footerHTML = jQuery(
    ".wpsc-tl_snippets .auto-refresh .modal-footer"
  ).html();

  // Set them to modal.
  jQuery(".wpsc-modal-header").text(headerText);
  jQuery(".wpsc-modal-body").html(bodyHTML);
  jQuery(".wpsc-modal-footer").html(footerHTML);

  // Set current status.
  var status = supportcandy.tl_auto_refresh ? supportcandy.tl_auto_refresh : 0;
  var el = status
    ? jQuery(".wpsc-modal .toggle-on")
    : jQuery(".wpsc-modal .toggle-off");
  jQuery(".wpsc-modal-body input").val(status);
  status ? wpsc_toggle_on(el) : wpsc_toggle_off(el);

  // Display modal.
  wpsc_show_modal_inner_container();
}

/**
 * Set auto-refresh settings
 */
function wpsc_set_tl_auto_refresh(el) {
  jQuery(".wpsc-modal-footer button").attr("disabled", true);
  jQuery(el).text(supportcandy.translations.please_wait);
  var status = parseInt(jQuery(".wpsc-modal-body input").val());
  wpsc_close_modal();
  if (supportcandy.tl_auto_refresh !== status) {
    supportcandy.tl_auto_refresh = status;
    if (status === 1) {
      wpsc_tl_auto_refresh();
    }
  }
}

/**
 * Auto refresh
 */
function wpsc_tl_auto_refresh() {

  if (supportcandy.tl_auto_refresh_started) {
    return;
  }

  supportcandy.tl_auto_refresh_started = true;

  function run_refresh() {

    if (
      supportcandy.current_section === "ticket-list" &&
      !supportcandy.ticketListIsIndividual &&
      supportcandy.tl_auto_refresh === 1 &&
      !(
        jQuery(".wpsc-bulk-select:checked").length ||
        jQuery(".wpsc-search-input").is(":focus")
      )
    ) {
      wpsc_get_tickets();
    }

    setTimeout(run_refresh, 60000);
  }

  run_refresh();
}

/**
 * User logout
 */
function wpsc_user_logout(el, nonce) {
  jQuery(el).text(supportcandy.translations.please_wait);
  var data = { action: "wpsc_user_logout", _ajax_nonce: nonce };
  jQuery.post(supportcandy.ajax_url, data, function (res) {
    window.location.reload();
  });
}

/**
 * Close model popup on escape key.
 */
jQuery(document).keydown(function (e) {
  if (e.keyCode == 27) {
    wpsc_close_modal();
  }
});

/**
 * Get customer info
 */
function wpsc_view_customer_info(customer_id, _ajax_nonce) {
  wpsc_show_modal();
  var data = { action: "wpsc_view_customer_info", customer_id, _ajax_nonce };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    wpsc_show_modal_inner_container();
  });
}

/**
 * Get edit customer info
 */
function wpsc_get_edit_customer_info(customer_id, _ajax_nonce) {
  wpsc_show_modal();
  var data = {
    action: "wpsc_get_edit_customer_info",
    customer_id,
    _ajax_nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    wpsc_show_modal_inner_container();
  });
}

/**
 * Set edit customer info
 */
function wpsc_set_edit_customer_info(el, id, nonce) {
  var form = jQuery("form.frm-edit-customer-info")[0];
  var dataform = new FormData(form);
  jQuery(".wpsc-modal-footer button").attr("disabled", true);
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
      wpsc_close_modal();
      wpsc_scroll_top();
      wpsc_view_customer_detailed_info(res.customer_id, res.nonce);
    });
}

/**
 * Get customer logs
 */
function wpsc_view_customer_logs(customer_id, _ajax_nonce) {
  wpsc_show_modal();
  var data = { action: "wpsc_view_customer_logs", customer_id, _ajax_nonce };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    wpsc_show_modal_inner_container();
  });
}

/**
 *
 * @param {*} ticket
 */
function wpsc_clear_saved_draft_reply(ticket_id) {
  var draft_replies = JSON.parse(localStorage.getItem("wpsc_auto_draft")) || {};
  var is_tinymce =
    typeof tinyMCE != "undefined" &&
    tinyMCE.activeEditor &&
    !tinyMCE.activeEditor.isHidden();
  if (is_tinymce && tinymce.get("description")) {
    tinyMCE.get("description").setContent("");
  }
  jQuery("#description").val("");
  if (!ticket_id) return;
  delete draft_replies[ticket_id];
  localStorage.setItem("wpsc_auto_draft", JSON.stringify(draft_replies));
}

/**
 * Run background processes.
 */
function wpsc_run_ajax_background_process() {
  var data = { action: "wpsc_run_ajax_background_process" };
  jQuery.post(supportcandy.ajax_url, data, function (response) {});
}

/**
 * Assign ticket to self
 */
function wpsc_self_assign_ticket(ticket_id, nonce) {
  var data = {
    action: "wpsc_self_assign_ticket",
    ticket_id,
    _ajax_nonce: nonce,
  };

  jQuery.post(supportcandy.ajax_url, data, function (response) {
    wpsc_get_individual_ticket(ticket_id);
  });
}

/**
 * Delete wpsc_auto_draft localStorage after 24 hr
 */
function wpsc_delete_auto_draft() {
  var draft_cron_time = localStorage.getItem("wpsc_draft_cron_time");
  if (!draft_cron_time) {
    localStorage.setItem("wpsc_draft_cron_time", new Date().getTime());
  }
  var diff = new Date().getTime() - draft_cron_time;
  if (diff < 3600000) {
    return;
  }
  var draft_replies = JSON.parse(localStorage.getItem("wpsc_auto_draft")) || {};
  Object.keys(draft_replies).forEach((ticket_id) => {
    var timeDifference =
      new Date() - new Date(draft_replies[ticket_id].date_updated);
    if (timeDifference > 86400000) {
      delete draft_replies[ticket_id];
    }
  });
  localStorage.setItem("wpsc_auto_draft", JSON.stringify(draft_replies));
  localStorage.setItem("wpsc_draft_cron_time", new Date().getTime());
}

/**
 * Delete customer
 */
function wpsc_delete_customer(customer_id, nonce) {
  if (!confirm(supportcandy.translations.customer_delete_warn)) {
    return;
  }

  var data = {
    action: "wpsc_delete_customer",
    customer_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    window.location.reload();
  });
}

/**
 * Dashboard - Get filter from and to dates based on duration slug
 */
function wpsc_db_set_filter_duration_dates(duration) {
  let dateStr = "";
  let date = new Date();
  let from_date, to_date;

  switch (duration) {
    case "today":
      dateStr = date.toISOString().split("T")[0];
      date_from_to = {
        from: dateStr,
        to: dateStr,
      };
      break;

    case "yesterday":
      date.setDate(date.getDate() - 1);
      dateStr = date.toISOString().split("T")[0];
      date_from_to = {
        from: dateStr,
        to: dateStr,
      };
      break;

    case "this-week":
      let firstDayOfWeek = date.getDay() === 0 ? 6 : date.getDay() - 1;
      date.setDate(date.getDate() - firstDayOfWeek);
      from_date = date.toISOString().split("T")[0];
      date.setDate(date.getDate() + 6);
      to_date = date.toISOString().split("T")[0];
      date_from_to = {
        from: from_date,
        to: to_date,
      };
      break;

    case "last-week":
      let firstDayOfLastWeek = date.getDay() === 0 ? 7 : date.getDay();
      date.setDate(date.getDate() - firstDayOfLastWeek - 6);
      from_date = date.toISOString().split("T")[0];
      date.setDate(date.getDate() + 6);
      to_date = date.toISOString().split("T")[0];
      date_from_to = {
        from: from_date,
        to: to_date,
      };
      break;

    case "last-7":
      date.setDate(date.getDate() - 1); // Exclude today
      to_date = date.toISOString().split("T")[0];
      date.setDate(date.getDate() - 6); // Last 7 days from yesterday
      from_date = date.toISOString().split("T")[0];
      date_from_to = {
        from: from_date,
        to: to_date,
      };
      break;

    case "last-30-days":
      date.setDate(date.getDate() - 1); // Exclude today
      to_date = date.toISOString().split("T")[0];
      date.setDate(date.getDate() - 29); // Last 30 days from yesterday
      from_date = date.toISOString().split("T")[0];
      date_from_to = {
        from: from_date,
        to: to_date,
      };
      break;

    case "this-month":
      let this_month_starts = new Date(date.getFullYear(), date.getMonth(), 1);
      let this_month_ends = new Date(
        date.getFullYear(),
        date.getMonth() + 1,
        0
      );
      date_from_to = {
        from: `${this_month_starts.getFullYear()}-${(
          this_month_starts.getMonth() + 1
        )
          .toString()
          .padStart(2, "0")}-${this_month_starts
          .getDate()
          .toString()
          .padStart(2, "0")}`,
        to: `${this_month_ends.getFullYear()}-${(this_month_ends.getMonth() + 1)
          .toString()
          .padStart(2, "0")}-${this_month_ends
          .getDate()
          .toString()
          .padStart(2, "0")}`,
      };
      break;

    case "last-month":
      date.setMonth(date.getMonth() - 1);
      let last_month_starts = new Date(date.getFullYear(), date.getMonth(), 1);
      let last_month_ends = new Date(
        date.getFullYear(),
        date.getMonth() + 1,
        0
      );
      date_from_to = {
        from: `${last_month_starts.getFullYear()}-${(
          last_month_starts.getMonth() + 1
        )
          .toString()
          .padStart(2, "0")}-${last_month_starts
          .getDate()
          .toString()
          .padStart(2, "0")}`,
        to: `${last_month_ends.getFullYear()}-${(last_month_ends.getMonth() + 1)
          .toString()
          .padStart(2, "0")}-${last_month_ends
          .getDate()
          .toString()
          .padStart(2, "0")}`,
      };
      break;

    case "this-quarter":
      let this_quarter_month_starts = Math.floor(date.getMonth() / 3) * 3;
      let quarter_start = new Date(
        date.getFullYear(),
        this_quarter_month_starts,
        1
      );
      let quarter_end = new Date(
        date.getFullYear(),
        this_quarter_month_starts + 3,
        0
      );
      date_from_to = {
        from: `${quarter_start.getFullYear()}-${(quarter_start.getMonth() + 1)
          .toString()
          .padStart(2, "0")}-${quarter_start
          .getDate()
          .toString()
          .padStart(2, "0")}`,
        to: `${quarter_end.getFullYear()}-${(quarter_end.getMonth() + 1)
          .toString()
          .padStart(2, "0")}-${quarter_end
          .getDate()
          .toString()
          .padStart(2, "0")}`,
      };
      break;

    case "last-quarter":
      let last_quarter_month_ends = Math.floor(date.getMonth() / 3) * 3 - 1; // End month of last quarter
      let last_quarter_month_starts = last_quarter_month_ends - 2; // Start month of last quarter
      // In Q1 these are negative; Date() rolls them into the previous year.
      let last_quarter_start = new Date(
        date.getFullYear(),
        last_quarter_month_starts,
        1
      );
      let last_quarter_end = new Date(
        date.getFullYear(),
        last_quarter_month_ends + 1,
        0
      );
      date_from_to = {
        from: `${last_quarter_start.getFullYear()}-${(
          last_quarter_start.getMonth() + 1
        )
          .toString()
          .padStart(2, "0")}-${last_quarter_start
          .getDate()
          .toString()
          .padStart(2, "0")}`,
        to: `${last_quarter_end.getFullYear()}-${(
          last_quarter_end.getMonth() + 1
        )
          .toString()
          .padStart(2, "0")}-${last_quarter_end
          .getDate()
          .toString()
          .padStart(2, "0")}`,
      };
      break;

    case "this-year":
      date_from_to = {
        from: `${date.getFullYear()}-01-01`,
        to: `${date.getFullYear()}-12-31`,
      };
      break;

    case "last-year":
      date_from_to = {
        from: `${date.getFullYear() - 1}-01-01`,
        to: `${date.getFullYear() - 1}-12-31`,
      };
      break;
  }
  return date_from_to;
}

/**
 * Get batches for async report loading.
 *
 * fromDate / toDate are calendar days (callers pass new Date('YYYY-MM-DD'), which is UTC midnight), so all
 * date math here is done in UTC. Mixing local-time getters with toISOString() shifted bucket edges by a
 * day in timezones away from UTC. The server converts these site-local strings to UTC before querying.
 */
function wpsc_rp_get_baches(fromDate, toDate) {
  var dayMs = 24 * 60 * 60 * 1000;
  var utcDay = function (d) {
    return new Date(Date.UTC(d.getUTCFullYear(), d.getUTCMonth(), d.getUTCDate()));
  };
  var addDays = function (d, n) {
    return new Date(d.getTime() + n * dayMs);
  };
  var ymd = function (d) {
    return d.toISOString().split("T")[0];
  };
  var minDate = function (a, b) {
    return a < b ? a : b;
  };

  var from = utcDay(fromDate);
  var to = utcDay(toDate);
  if (to < from) {
    var swap = from;
    from = to;
    to = swap;
  }

  var diffDays = Math.round((to - from) / dayMs) + 1;

  var batches = [];
  var batchData = [];
  var push = function (start, end, durationType) {
    batchData.push({
      fromDate: ymd(start) + " 00:00:00",
      toDate: ymd(end) + " 23:59:59",
      durationType: durationType,
    });
  };
  var start, end;

  if (diffDays == 1) {
    // one day.

    for (var i = 0; i <= 23; i++) {
      var hr = String(i).padStart(2, "0");
      batchData.push({
        fromDate: ymd(from) + " " + hr + ":00:00",
        toDate: ymd(from) + " " + hr + ":59:59",
        durationType: "day",
      });
    }
  } else if (diffDays <= 31) {
    // days.

    for (start = from; start <= to; start = addDays(start, 1)) {
      push(start, start, "days");
    }
  } else if (diffDays <= 180) {
    // weeks (Monday - Sunday), first and last clipped to the range.

    for (start = from; start <= to; start = addDays(end, 1)) {
      end = minDate(addDays(start, (7 - start.getUTCDay()) % 7), to);
      push(start, end, "weeks");
    }
  } else if (diffDays <= 720) {
    // months.

    for (start = from; start <= to; start = addDays(end, 1)) {
      end = minDate(new Date(Date.UTC(start.getUTCFullYear(), start.getUTCMonth() + 1, 0)), to);
      push(start, end, "months");
    }
  } else {
    // years.

    for (start = from; start <= to; start = addDays(end, 1)) {
      end = minDate(new Date(Date.UTC(start.getUTCFullYear(), 11, 31)), to);
      push(start, end, "years");
    }
  }

  // divide into batches of 5.
  var batchLength = batchData.length;
  for (var i = 0; i < batchLength; i = i + 5) {
    var batch = [];
    var counter = 0;
    do {
      var index = i + counter;
      if (index == batchLength) {
        break;
      }
      batch.push(batchData[index]);
      counter++;
    } while (counter < 5);
    batches.push(batch);
  }

  return batches;
}

function wpsc_generate_random_color() {
  var minLuminance = 0.7;

  do {
    var color = Math.floor(Math.random() * 16777216); // 0x000000 to 0xFFFFFF
    var red = (color >> 16) & 0xff;
    var green = (color >> 8) & 0xff;
    var blue = color & 0xff;

    // Calculate luminance (brightness).
    var luminance = (0.299 * red + 0.587 * green + 0.114 * blue) / 255;
  } while (luminance < minLuminance);

  return "#" + color.toString(16).toUpperCase();
}

// onclick load respective card ticket list
function wpsc_get_dbc_ticket_list(element, card) {
  var count = parseInt(jQuery(element).find(".wpsc-dbc-count").text(), 10);
  if (count === 0) {
    return;
  }
  var data = {
    action: "wpsc_dash_card_count_filter",
    card: card,
    view: supportcandy.is_frontend,
    _ajax_nonce: supportcandy.nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    if (response.url) {
      window.location = response.url;
    }
  });
}

// onclick load respective card ticket list
function wpsc_get_agent_status_ticket_list(agent_id, status_id, nonce) {
  var data = {
    action: "wpsc_get_agent_status_ticket_list",
    agent_id: agent_id,
    status_id: status_id,
    view: supportcandy.is_frontend,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    if (response.url) {
      window.location = response.url;
    }
  });
}

/**
 * Refresh tags list
 */
function wpsc_it_refresh_tags(ticket_id) {
  jQuery(".wpsc-it-tag-body").html(supportcandy.loader_html);

  var data = { action: "wpsc_it_refresh_tags", ticket_id };
  jQuery.post(supportcandy.ajax_url, data, function (res) {
    jQuery(".wpsc-it-tag-body").html(res);
  });
}

/**
 * View user wordpress profile
 */
function wpsc_wp_user_profile(el, ticket_id, nonce) {
  var data = {
    action: "wpsc_wp_user_profile",
    ticket_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    if (response.url) {
      window.open(response.url, "_blank");
    }
  });
}

/**
 * Restore archived ticket
 */
function wpsc_it_restore_archived(ticket_id, nonce) {

  if (!confirm(supportcandy.translations.confirm)){
    return;
  }

  var data = {
    action: "wpsc_it_restore_archived",
    ticket_id,
    _ajax_nonce: nonce
  };

  jQuery.post(
    supportcandy.ajax_url,
    data,
    function (response) {
      wpsc_get_archive_ticket_list();
    }
  );
}

/**
 *  Get refresh current ticket
 */
function wpsc_it_archive_ab_refresh(ticket_id) {
  if (wpsc_is_description_text()) {
    if (confirm(supportcandy.translations.confirm)) {
      wpsc_clear_saved_draft_reply(ticket_id);
    } else {
      return;
    }
  }

  wpsc_get_individual_archive_ticket(ticket_id);
}

/**
 * Load older threads
 */
function wpsc_load_older_archive_threads(el, ticket_id) {
  if (supportcandy.reply_form_position === "top") {
    jQuery(".wpsc-it-thread-section-container").append(
      supportcandy.loader_html
    );
  } else {
    jQuery(".wpsc-it-thread-section-container").prepend(
      supportcandy.loader_html
    );
  }

  var oldBtnContainer = jQuery(el).parent();
  oldBtnContainer.hide();

  var data = {
    action: "wpsc_load_older_archive_threads",
    ticket_id,
    last_thread: supportcandy.threads.last_thread,
  };
  wpsc_add_url_auth_code(data);
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    jQuery(".wpsc-it-thread-section-container").find(".wpsc-loader").remove();
    // add threads.
    var $container = jQuery(".wpsc-it-thread-section-container");
    var $before = $container.find(".thread-text");
    if (supportcandy.reply_form_position === "top") {
      $container.append(response.threads);
    } else {
      var scrollHeightBody = jQuery("body").prop("scrollHeight");
      var windowScrollTop = jQuery(window).scrollTop();
      $container.prepend(response.threads);
      var scrollDiff = jQuery("body").prop("scrollHeight") - scrollHeightBody;
      jQuery(window).scrollTop(windowScrollTop + scrollDiff);
    }
    // Newly injected threads also need their "view more" state evaluated -
    // the initial page-load script only runs once, so without this the
    // lazily-loaded threads never get truncated/expandable.
    wpsc_init_thread_expanders($container.find(".thread-text").not($before));
    // show btn if there are more threads available.
    if (response.has_next_page) {
      supportcandy.threads.last_thread = response.last_thread;
      oldBtnContainer.show();
    }
  });
}


/**
 * Delete permanently
 */
function wpsc_iat_delete_permanently(ticket_id, nonce) {
  var flag = confirm(supportcandy.translations.delete_permanently);
  if (!flag) {
    return;
  }

  var data = {
    action: "wpsc_iat_delete_permanently",
    ticket_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    wpsc_get_archive_ticket_list();
  });
}

/**
 * Get bulk delete archive ticket
 */
function wpsc_bulk_delete_archive_tickets(nonce) {
  var items = jQuery(".wpsc-bulk-select:checked");
  var checked = items.length === 0 ? false : true;
  jQuery(".wpsc-bulk-selector").prop("checked", checked);
  if (items.length != 0) {
    var ticket_ids = jQuery(".wpsc-bulk-select:checked")
      .map(function () {
        return this.value;
      })
      .get();
    var flag = confirm(supportcandy.translations.delete_permanently);
    if (!flag) {
      return;
    }

    var data = {
      action: "wpsc_bulk_delete_archive_tickets",
      ticket_ids,
      _ajax_nonce: nonce,
    };
    jQuery.post(supportcandy.ajax_url, data, function (response) {
      wpsc_get_archive_ticket_list();
      wpsc_run_ajax_background_process();
    });
  }
}

/**
 * Restore bulk archive tickets
 */
function wpsc_bulk_restore_archive_tickets(nonce) {
  var items = jQuery(".wpsc-bulk-select:checked");
  var checked = items.length === 0 ? false : true;
  jQuery(".wpsc-bulk-selector").prop("checked", checked);
  if (items.length != 0) {
    var ticket_ids = jQuery(".wpsc-bulk-select:checked")
      .map(function () {
        return this.value;
      })
      .get();
    var flag = confirm(supportcandy.translations.confirm);
    if (!flag) {
      return;
    }
    var data = {
      action: "wpsc_bulk_restore_archive_tickets",
      ticket_ids,
      _ajax_nonce: nonce,
    };
    jQuery.post(supportcandy.ajax_url, data, function (response) {
      wpsc_get_archive_ticket_list();
    });
  }
}

/**
 * Get bulk archive ticket
 */
function wpsc_bulk_delete_tickets(nonce) {
  var items = jQuery(".wpsc-bulk-select:checked");
  var checked = items.length === 0 ? false : true;
  jQuery(".wpsc-bulk-selector").prop("checked", checked);
  if (items.length != 0) {
    var ticket_ids = jQuery(".wpsc-bulk-select:checked")
      .map(function () {
        return this.value;
      })
      .get();
    var flag = confirm(supportcandy.translations.confirm);
    if (!flag) {
      return;
    }

    var data = {
      action: "wpsc_bulk_delete_tickets",
      ticket_ids,
      _ajax_nonce: nonce,
    };
    jQuery.post(supportcandy.ajax_url, data, function (response) {
      wpsc_get_ticket_list();
      wpsc_run_ajax_background_process();
    });
  }
}

/**
 * Restore bulk tickets
 */
function wpsc_bulk_restore_tickets(nonce) {
  var items = jQuery(".wpsc-bulk-select:checked");
  var checked = items.length === 0 ? false : true;
  jQuery(".wpsc-bulk-selector").prop("checked", checked);
  if (items.length != 0) {
    var ticket_ids = jQuery(".wpsc-bulk-select:checked")
      .map(function () {
        return this.value;
      })
      .get();
    var flag = confirm(supportcandy.translations.confirm);
    if (!flag) {
      return;
    }
    var data = {
      action: "wpsc_bulk_restore_tickets",
      ticket_ids,
      _ajax_nonce: nonce,
    };
    jQuery.post(supportcandy.ajax_url, data, function (response) {
      wpsc_get_ticket_list();
    });
  }
}

/**
 * Deletes bulk ticket permanently
 */
function wpsc_bulk_delete_tickets_permanently(nonce) {
  var items = jQuery(".wpsc-bulk-select:checked");
  var checked = items.length === 0 ? false : true;
  jQuery(".wpsc-bulk-selector").prop("checked", checked);
  if (items.length != 0) {
    var ticket_ids = jQuery(".wpsc-bulk-select:checked")
      .map(function () {
        return this.value;
      })
      .get();
    var flag = confirm(supportcandy.translations.confirm);
    if (!flag) {
      return;
    }

    var data = {
      action: "wpsc_bulk_delete_tickets_permanently",
      ticket_ids,
      _ajax_nonce: nonce,
    };
    jQuery.post(supportcandy.ajax_url, data, function (response) {
      wpsc_get_ticket_list();
    });
  }
}

/**
 * Copy working hours of one day to other days
 */
jQuery(document).on("click", "table.wpsc-working-hrs .wpsc-wh-copy", function () {
  const link = jQuery(this);
  const table = link.closest("table.wpsc-working-hrs");
  const existing = jQuery(".wpsc-wh-copy-popover");
  const same = existing.data("src") === link[0];
  existing.remove();
  if (same) return;
  const srcRow = link.closest("tr");
  let html = '<div class="wpsc-wh-copy-popover"><strong>' + table.data("copy-label") + "</strong>";
  table.find("tr").each(function () {
    if (this === srcRow[0]) return;
    const day = jQuery(this).find(".wpsc-wh-copy").data("day");
    html += '<label><input type="checkbox" value="' + day + '"> <span></span></label>';
  });
  html += '<button type="button" class="wpsc-button small primary">' + table.data("apply-label") + "</button></div>";
  const pop = jQuery(html).data("src", link[0]);
  let idx = 0;
  const names = [];
  table.find("tr").each(function () {
    if (this !== srcRow[0]) names.push(jQuery(this).find(".dayName").text().replace(/:\s*$/, ""));
  });
  pop.find("label span").each(function () {
    jQuery(this).text(names[idx++]);
  });
  pop.find("button").on("click", function () {
    const start = srcRow.find(".wpsc-wh-start-time").val();
    const end = srcRow.find(".wpsc-wh-end-time").val();
    pop.find("input:checked").each(function () {
      const row = table.find('.wpsc-wh-copy[data-day="' + jQuery(this).val() + '"]').closest("tr");
      row.find(".wpsc-wh-start-time").val(start).trigger("change");
      if (start !== "off") row.find(".wpsc-wh-end-time").val(end);
    });
    pop.remove();
  });
  link.closest("td").css("position", "relative").append(pop);
});

jQuery(document).on("click", function (e) {
  if (!jQuery(e.target).closest(".wpsc-wh-copy-popover, .wpsc-wh-copy").length) {
    jQuery(".wpsc-wh-copy-popover").remove();
  }
});

/**
 * Set working hours of a day to 24 hours
 */
jQuery(document).on("click", "table.wpsc-working-hrs .wpsc-wh-24h", function () {
  const row = jQuery(this).closest("tr");
  row.find(".wpsc-wh-start-time").val("00:00:00").trigger("change");
  row.find(".wpsc-wh-end-time").val("23:59:59");
});

/**
 * Inline edit popover (individual ticket widgets)
 */
var wpsc_ie = { el: null, trigger: null };

/**
 * Close currently open inline edit popover
 */
function wpsc_ie_close() {
  if (wpsc_ie.el) {
    wpsc_ie.el.remove();
  }
  if (wpsc_ie.trigger) {
    jQuery(wpsc_ie.trigger).attr("aria-expanded", "false").removeClass("open");
  }
  wpsc_ie = { el: null, trigger: null };
  jQuery(document).off(".wpscIe");
  jQuery(window).off(".wpscIe");
}

/**
 * Place popover below the trigger, keeping it inside viewport
 */
function wpsc_ie_position() {
  if (!wpsc_ie.el || !wpsc_ie.trigger) {
    return;
  }
  // widget was re-rendered while popover was open
  if (!document.body.contains(wpsc_ie.trigger)) {
    wpsc_ie_close();
    return;
  }
  var rect = wpsc_ie.trigger.getBoundingClientRect();
  var width = wpsc_ie.el.outerWidth();
  var height = wpsc_ie.el.outerHeight();
  // clientWidth excludes scrollbar, innerWidth does not
  var viewW = document.documentElement.clientWidth;
  var viewH = document.documentElement.clientHeight;
  var left = rect.left;
  if (left + width > viewW - 8) {
    // align with right edge of trigger, then keep inside screen
    left = Math.min(rect.right - width, viewW - width - 8);
  }
  left = Math.max(8, left);
  var top = rect.bottom + 4;
  if (top + height > viewH - 8 && rect.top - height - 4 > 8) {
    top = rect.top - height - 4;
  }
  wpsc_ie.el.css({ left: left + "px", top: top + "px" });
}

/**
 * Open (or toggle) popover for given trigger.
 *
 * @param {Element} trigger
 * @returns {jQuery|null} popover element or null if it was closed by this call.
 */
function wpsc_ie_open(trigger) {
  var same = wpsc_ie.trigger === trigger;
  wpsc_ie_close();
  if (same) {
    return null;
  }

  var pop = jQuery('<div class="wpsc-ie-popover wpsc-ie-panel" role="listbox"></div>');
  jQuery("body").append(pop);
  wpsc_ie = { el: pop, trigger: trigger };
  jQuery(trigger).attr("aria-expanded", "true").addClass("open");

  jQuery(document).on("mousedown.wpscIe", function (e) {
    if (!jQuery(e.target).closest(".wpsc-ie-popover, .wpsc-ie-trigger").length) {
      wpsc_ie_close();
    }
  });
  jQuery(document).on("keydown.wpscIe", function (e) {
    if (e.key === "Escape") {
      var t = wpsc_ie.trigger;
      wpsc_ie_close();
      jQuery(t).trigger("focus");
    }
  });
  // On touch devices the on-screen keyboard (opened by the search field) fires a
  // height-only resize; closing then would snap the popover shut. Only close on width change.
  var ieWidth = window.innerWidth;
  jQuery(window).on("resize.wpscIe", function () {
    if (window.innerWidth !== ieWidth) {
      wpsc_ie_close();
    } else {
      wpsc_ie_position();
    }
  });
  window.addEventListener(
    "scroll",
    function (e) {
      if (wpsc_ie.el && !jQuery(e.target).closest(".wpsc-ie-popover").length) {
        wpsc_ie_position();
      }
    },
    true,
  );
  return pop;
}

/**
 * Keyboard support for triggers (Enter / Space / ArrowDown opens popover)
 */
function wpsc_ie_trigger_keydown(e, el) {
  if (e.target !== el) {
    return;
  }
  if (e.key === "Enter" || e.key === " " || e.key === "ArrowDown") {
    e.preventDefault();
    jQuery(el).trigger("click");
  }
}

/**
 * Arrow key navigation for items inside popover
 */
function wpsc_ie_bind_list_keys(pop) {
  pop.on("keydown.wpscIe", function (e) {
    var items = pop.find(".wpsc-ie-item:visible");
    var idx = items.index(jQuery(document.activeElement));
    if (e.key === "ArrowDown") {
      e.preventDefault();
      items.eq(Math.min(idx + 1, items.length - 1)).trigger("focus");
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      if (idx <= 0) {
        pop.find(".wpsc-ie-search").trigger("focus");
      } else {
        items.eq(idx - 1).trigger("focus");
      }
    } else if (
      (e.key === "Enter" || e.key === " ") &&
      jQuery(document.activeElement).hasClass("wpsc-ie-item")
    ) {
      e.preventDefault();
      jQuery(document.activeElement).trigger("click");
    }
  });
}

/**
 * Run a change through the existing ticket save endpoint and reload ticket.
 * Hooks are executed by the backend exactly like submitting the edit modal.
 */
function wpsc_ie_save(trigger, ticket_id, data, onDone) {
  if (wpsc_is_description_text()) {
    if (confirm(supportcandy.translations.warning_message)) {
      wpsc_clear_saved_draft_reply(ticket_id);
    } else {
      wpsc_ie_close();
      return false;
    }
  }

  jQuery(trigger).addClass("saving");
  wpsc_ie_close();
  jQuery.ajax({
    url: supportcandy.ajax_url,
    type: "POST",
    data: data,
  })
    .done(function (res) {
      if (onDone) {
        onDone(res);
      } else {
        wpsc_get_individual_ticket(ticket_id);
      }
      wpsc_run_ajax_background_process();
    })
    .fail(function () {
      jQuery(trigger).removeClass("saving");
      wpsc_get_individual_ticket(ticket_id);
    });
  return true;
}

/**
 * Open status, category or priority popover
 */
function wpsc_ie_open_select(trigger) {
  var $t = jQuery(trigger);
  var field = $t.data("field");
  var options = $t.data("options") || [];
  var current = parseInt($t.data("current"));
  var widget = $t.closest(".wpsc-itw-ticket-status");
  var ticket_id = widget.data("ticket-id");

  var pop = wpsc_ie_open(trigger);
  if (!pop) {
    return;
  }
  pop.addClass("wpsc-ie-select");

  var i18n = $t.data("i18n") || {};
  var maxRender = 50;

  var search = null;
  if (options.length > 7) {
    search = jQuery(
      '<input type="text" class="wpsc-ie-search" autocomplete="off">',
    ).attr("placeholder", i18n.search);
    pop.append(search);
  }

  var list = jQuery('<div class="wpsc-ie-list"></div>');
  pop.append(list);

  function select(opt) {
    if (opt.id === current) {
      wpsc_ie_close();
      return;
    }
    var data = {
      action: "wpsc_it_set_edit_ticket_status",
      ticket_id: ticket_id,
      _ajax_nonce: widget.data("nonce"),
    };
    data[
      field === "status" ? "status_id" : field === "category" ? "cat_id" : "priority_id"
    ] = opt.id;
    wpsc_ie_save(trigger, ticket_id, data);
  }

  // render matching options - capped so that very large lists stay fast
  function render(term) {
    term = (term || "").toLowerCase();
    list.empty().scrollTop(0);
    var matches = options.filter(function (opt) {
      return !term || opt.name.toLowerCase().indexOf(term) !== -1;
    });
    matches.slice(0, maxRender).forEach(function (opt) {
      var item = jQuery(
        '<div class="wpsc-ie-item" role="option" tabindex="-1"></div>',
      ).attr("aria-selected", opt.id === current ? "true" : "false");
      var label = jQuery('<span class="wpsc-ie-label"></span>')
        .text(opt.name)
        .attr("title", opt.name);
      if (opt.bg_color) {
        label
          .addClass("wpsc-tag")
          .css({ "background-color": opt.bg_color, color: opt.color });
      }
      item.append(label);
      item.append(
        jQuery('<span class="wpsc-ie-check"></span>').html(opt.id === current ? "&#10003;" : ""),
      );
      item.toggleClass("selected", opt.id === current);
      item.on("click", function () {
        select(opt);
      });
      list.append(item);
    });
    if (!matches.length) {
      list.append(jQuery('<div class="wpsc-ie-empty"></div>').text(i18n.noResults));
    } else if (matches.length > maxRender) {
      list.append(jQuery('<div class="wpsc-ie-empty wpsc-ie-more"></div>').text(i18n.more));
    }
    wpsc_ie_position();
  }
  render("");

  if (search) {
    search.on("input", function () {
      render(search.val());
    });
    search.on("keydown", function (e) {
      if (e.key === "ArrowDown") {
        e.preventDefault();
        list.find(".wpsc-ie-item").first().trigger("focus");
      } else if (e.key === "Enter") {
        // pick first match
        e.preventDefault();
        list.find(".wpsc-ie-item").first().trigger("click");
      }
    });
  }
  wpsc_ie_bind_list_keys(pop);
  if (search) {
    search.trigger("focus");
  } else {
    list.find(".wpsc-ie-item.selected, .wpsc-ie-item").first().trigger("focus");
  }
  var sel = list.find(".wpsc-ie-item.selected").get(0);
  if (sel && search) {
    list.scrollTop(sel.offsetTop - list.height() / 2);
  }
}

/**
 * Open assignee editor from the edit icon in widget header
 */
function wpsc_ie_open_assignee_from_header(el) {
  var wrap = jQuery(el).closest(".wpsc-it-widget").find(".wpsc-ie-assignee-wrap");
  if (wrap.length && !wrap.hasClass("editing")) {
    wpsc_ie_open_assignee({ target: el }, wrap.find(".wpsc-ie-assignee")[0]);
  }
}

/**
 * Open assignee popover
 */
function wpsc_ie_open_assignee(e, trigger) {
  // let existing "Assign to me" (self assign) link work as before
  if (jQuery(e.target).closest("a").length) {
    return;
  }

  // editor is rendered inside the widget (no floating element)
  wpsc_ie_close();
  var wrap = jQuery(trigger).closest(".wpsc-ie-assignee-wrap");
  var cfg = wrap.data("config");
  var pop = wrap.find(".wpsc-ie-assignee-editor").off(".wpscIe").empty();
  wrap.addClass("editing");

  function closeEditor() {
    clearTimeout(timer);
    jQuery(document).off("mousedown.wpscIeEdit");
    pop.empty();
    wrap.removeClass("editing");
    jQuery(trigger).trigger("focus");
  }
  // click outside closes the editor when nothing was changed
  jQuery(document)
    .off("mousedown.wpscIeEdit")
    .on("mousedown.wpscIeEdit", function (ev) {
      if (!jQuery(ev.target).closest(wrap).length && !changed()) {
        closeEditor();
      }
    });
  pop.on("keydown.wpscIe", function (ev) {
    if (ev.key === "Escape") {
      closeEditor();
    }
  });

  // selected agents (id => name)
  var selected = {};
  var initial = [];
  cfg.assigned.forEach(function (a) {
    selected[a.id] = a.name;
    initial.push(a.id);
  });
  var results = [];
  var reqCounter = 0;
  var timer = null;

  var colors = ["#6366f1", "#0ea5e9", "#14b8a6", "#f59e0b", "#ef4444", "#8b5cf6", "#ec4899", "#64748b"];
  function avatar(id, name) {
    var initials = name
      .split(/\s+/)
      .filter(Boolean)
      .slice(0, 2)
      .map(function (w) {
        return w.charAt(0);
      })
      .join("");
    return jQuery('<span class="wpsc-ie-avatar"></span>')
      .text(initials)
      .css("background", colors[id % colors.length]);
  }

  var search = jQuery(
    '<input type="text" class="wpsc-ie-search" autocomplete="off">',
  ).attr("placeholder", cfg.i18n.search);
  pop.append(search);

  // pinned "Assign to me" row depends on self assign setting
  var meBtn = jQuery(
    '<div class="wpsc-ie-item wpsc-ie-me" role="option" tabindex="-1"></div>',
  );
  if (cfg.selfAssign) {
    pop.append(meBtn);
  }
  var pinnedMe = cfg.selfAssign ? cfg.me.id : null;
  var list = jQuery('<div class="wpsc-ie-list"></div>');
  pop.append(list);

  // sort options
  var sortVal = "workload";
  var opts = jQuery('<div class="wpsc-ie-sort"></div>');
  opts.append(jQuery("<span></span>").text(cfg.i18n.sortBy));
  var sortBtns = jQuery('<div class="wpsc-ie-sort-btns"></div>');
  [
    ["workload", cfg.i18n.workload],
    ["name", cfg.i18n.name],
  ].forEach(function (o) {
    sortBtns.append(
      jQuery('<button type="button"></button>')
        .text(o[1])
        .attr("data-sort", o[0])
        .toggleClass("active", o[0] === sortVal)
        .on("click", function () {
          sortVal = o[0];
          sortBtns.children().removeClass("active");
          jQuery(this).addClass("active");
          load();
        }),
    );
  });
  opts.append(sortBtns);
  pop.append(opts);

  var footer = jQuery('<div class="wpsc-ie-footer"></div>');
  var done = jQuery('<button type="button" class="wpsc-button small primary"></button>').text(cfg.i18n.done);
  var cancel = jQuery('<button type="button" class="wpsc-button small secondary"></button>').text(cfg.i18n.cancel);
  footer.append(cancel, done);
  pop.append(footer);

  function ids() {
    return Object.keys(selected).map(Number);
  }
  function changed() {
    return ids().sort().join(",") !== initial.slice().sort().join(",");
  }
  function toggle(id, name) {
    if (selected[id] !== undefined) {
      delete selected[id];
    } else {
      selected[id] = name;
    }
    render();
  }
  function row(id, name) {
    var isSel = selected[id] !== undefined;
    var item = jQuery(
      '<div class="wpsc-ie-item" role="option" tabindex="-1"></div>',
    )
      .attr("aria-selected", isSel ? "true" : "false")
      .toggleClass("selected", isSel)
      .data("id", id);
    item.append(avatar(id, name));
    item.append(jQuery('<span class="wpsc-ie-label"></span>').text(name));
    item.append(
      jQuery('<span class="wpsc-ie-check"></span>').html(isSel ? "&#10003;" : ""),
    );
    item.on("click", function () {
      toggle(id, name);
    });
    return item;
  }
  function render() {
    var focusId = jQuery(document.activeElement).data("id");

    var meSel = selected[cfg.me.id] !== undefined;
    meBtn
      .empty()
      .toggleClass("selected", meSel)
      .attr("aria-selected", meSel ? "true" : "false")
      .data("id", cfg.me.id)
      .append(avatar(cfg.me.id, cfg.me.name))
      .append(
        jQuery('<span class="wpsc-ie-label"></span>').text(
          meSel ? cfg.i18n.isMe : cfg.i18n.me,
        ),
      )
      .append(
        jQuery('<span class="wpsc-ie-check"></span>').html(meSel ? "&#10003;" : ""),
      );

    list.empty();
    // selected agents first so that they can be removed easily
    Object.keys(selected).forEach(function (id) {
      if (Number(id) !== pinnedMe) {
        list.append(row(Number(id), selected[id]));
      }
    });
    var groups = [];
    results.forEach(function (a) {
      if (selected[a.id] === undefined && a.id !== pinnedMe) {
        if (a.is_group) {
          groups.push(a);
        } else {
          list.append(row(a.id, a.title));
        }
      }
    });
    // agent groups are listed below the agents
    if (groups.length) {
      list.append(
        jQuery('<div class="wpsc-ie-group-title"></div>').text(cfg.i18n.groups),
      );
      groups.forEach(function (a) {
        list.append(row(a.id, a.title));
      });
    }
    if (!list.children().length) {
      list.append(
        jQuery('<div class="wpsc-ie-empty"></div>').text(cfg.i18n.none),
      );
    }
    done.prop("disabled", !changed());
    if (focusId !== undefined) {
      pop
        .find(".wpsc-ie-item")
        .filter(function () {
          return jQuery(this).data("id") === focusId;
        })
        .first()
        .trigger("focus");
    }
    wpsc_ie_position();
  }

  function load() {
    var current = ++reqCounter;
    list.addClass("loading");
    jQuery
      .get(supportcandy.ajax_url, {
        action: "wpsc_agent_autocomplete_itw_assign",
        _ajax_nonce: cfg.searchNonce,
        q: search.val(),
        sort_by: sortVal,
        isMultiple: 1,
        ticket_id: cfg.ticketId,
      })
      .done(function (data) {
        if (current !== reqCounter) {
          return;
        }
        results = Array.isArray(data) ? data : [];
        list.removeClass("loading");
        render();
      })
      .fail(function () {
        if (current === reqCounter) {
          list.removeClass("loading");
        }
      });
  }

  meBtn.on("click", function () {
    toggle(cfg.me.id, cfg.me.name);
  });
  search.on("input", function () {
    clearTimeout(timer);
    timer = setTimeout(load, 250);
  });
  search.on("keydown", function (e) {
    if (e.key === "ArrowDown") {
      e.preventDefault();
      (cfg.selfAssign ? meBtn : list.find(".wpsc-ie-item").first()).trigger("focus");
    }
  });
  cancel.on("click", closeEditor);
  done.on("click", function () {
    if (!changed()) {
      return;
    }
    var data = {
      action: "wpsc_it_set_edit_assigned_agents",
      ticket_id: cfg.ticketId,
      _ajax_nonce: cfg.saveNonce,
      assignee: ids(),
    };
    pop.addClass("saving");
    var started = wpsc_ie_save(trigger, cfg.ticketId, data, function (res) {
      if (res && res.viewPermission) {
        wpsc_get_individual_ticket(cfg.ticketId);
      } else {
        wpsc_get_ticket_list();
      }
    });
    if (!started) {
      pop.removeClass("saving");
    }
  });

  wpsc_ie_bind_list_keys(pop);
  render();
  search.trigger("focus");
  load();
}

/**
 * Assign current agent to the ticket (keeps agents that are already assigned)
 */
function wpsc_ie_assign_me(el) {
  var wrap = jQuery(el).closest(".wpsc-ie-assignee-wrap");
  var trigger = wrap.find(".wpsc-ie-trigger");
  var cfg = wrap.data("config");
  var ids = cfg.assigned.map(function (a) {
    return a.id;
  });
  if (ids.indexOf(cfg.me.id) === -1) {
    ids.push(cfg.me.id);
  }
  wpsc_ie_save(
    trigger[0],
    cfg.ticketId,
    {
      action: "wpsc_it_set_edit_assigned_agents",
      ticket_id: cfg.ticketId,
      _ajax_nonce: cfg.saveNonce,
      assignee: ids,
    },
    function (res) {
      if (res && res.viewPermission) {
        wpsc_get_individual_ticket(cfg.ticketId);
      } else {
        wpsc_get_ticket_list();
      }
    },
  );
}

/**
 * Ticket tags - inline editor (rendered inside the widget, nothing floats)
 */
function wpsc_tags_edit_from_header(el) {
  var wrap = jQuery(el).closest(".wpsc-it-widget").find(".wpsc-tags-wrap");
  if (wrap.length && !wrap.hasClass("editing")) {
    wpsc_tags_edit(wrap.find(".wpsc-tags-view")[0]);
  }
}

function wpsc_tags_edit(view) {
  var wrap = jQuery(view).closest(".wpsc-tags-wrap");
  var cfg = wrap.data("config");
  var editor = wrap.find(".wpsc-tags-editor").off(".wpscTags").empty();
  wrap.addClass("editing");

  // selected tags - {id, name, color, bg_color}; id is null for tags to be created
  var selected = cfg.tags.map(function (t) {
    return jQuery.extend({}, t);
  });
  var initialKey = selected
    .map(function (t) {
      return t.id;
    })
    .sort()
    .join(",");
  var results = [];
  var reqCounter = 0;
  var timer = null;

  var input = jQuery(
    '<input type="text" class="wpsc-ie-search wpsc-tags-input" autocomplete="off">',
  ).attr("placeholder", cfg.i18n.placeholder);
  var list = jQuery('<div class="wpsc-ie-list wpsc-tags-suggest"></div>');
  var footer = jQuery('<div class="wpsc-ie-footer"></div>');
  var cancel = jQuery(
    '<button type="button" class="wpsc-button small secondary"></button>',
  ).text(cfg.i18n.cancel);
  var submit = jQuery(
    '<button type="button" class="wpsc-button small primary"></button>',
  ).text(cfg.i18n.submit);
  footer.append(cancel, submit);
  editor.append(input, list, footer);

  function indexOfName(name) {
    for (var i = 0; i < selected.length; i++) {
      if (selected[i].name.toLowerCase() === name.toLowerCase()) {
        return i;
      }
    }
    return -1;
  }
  function changed() {
    // newly created tags always count as a change
    if (
      selected.some(function (t) {
        return t.id === null;
      })
    ) {
      return true;
    }
    return (
      selected
        .map(function (t) {
          return t.id;
        })
        .sort()
        .join(",") !== initialKey
    );
  }
  function toggle(tag) {
    var idx = indexOfName(tag.name);
    if (idx === -1) {
      selected.push(tag);
    } else {
      selected.splice(idx, 1);
    }
    render();
  }
  function addTyped() {
    var name = jQuery.trim(input.val());
    if (!name) {
      return;
    }
    if (indexOfName(name) === -1) {
      // reuse existing tag when the typed name matches one of the suggestions
      var match = results.filter(function (r) {
        return r.title.toLowerCase() === name.toLowerCase();
      })[0];
      toggle(
        match
          ? { id: match.id, name: match.title, color: match.color, bg_color: match.bg_color }
          : { id: null, name: name, color: cfg.newColors.color, bg_color: cfg.newColors.bg_color },
      );
    }
    input.val("");
    results = [];
    load();
  }
  function row(tag, isSel, onClick) {
    return jQuery('<div class="wpsc-ie-item" role="option" tabindex="-1"></div>')
      .attr("aria-selected", isSel ? "true" : "false")
      .toggleClass("selected", isSel)
      .append(
        jQuery('<span class="wpsc-ie-label wpsc-tag"></span>')
          .text(tag.name)
          .css({ "background-color": tag.bg_color, color: tag.color }),
      )
      .append(jQuery('<span class="wpsc-ie-check"></span>').html(isSel ? "&#10003;" : ""))
      .on("click", onClick);
  }

  function render() {
    list.empty();
    var term = jQuery.trim(input.val());

    // create new tag
    var exact = results.some(function (r) {
      return r.title.toLowerCase() === term.toLowerCase();
    });
    if (term && !exact && indexOfName(term) === -1) {
      list.append(
        jQuery('<div class="wpsc-ie-item wpsc-tags-create" role="option" tabindex="-1"></div>')
          .append(
            jQuery('<span class="wpsc-ie-label"></span>').text(
              cfg.i18n.create.replace("%s", term),
            ),
          )
          .on("click", function () {
            addTyped();
            input.trigger("focus");
          }),
      );
    }
    // selected tags first so that they can be removed easily
    selected.forEach(function (tag) {
      if (!term || tag.name.toLowerCase().indexOf(term.toLowerCase()) !== -1) {
        list.append(
          row(tag, true, function () {
            toggle(tag);
          }),
        );
      }
    });
    results.forEach(function (r) {
      if (indexOfName(r.title) !== -1) {
        return;
      }
      var tag = { id: r.id, name: r.title, color: r.color, bg_color: r.bg_color };
      list.append(
        row(tag, false, function () {
          toggle(tag);
        }),
      );
    });
    if (!list.children().length) {
      list.append(
        jQuery('<div class="wpsc-ie-empty"></div>').text(cfg.i18n.noResults),
      );
    }
    submit.prop("disabled", !changed());
  }

  function load() {
    var current = ++reqCounter;
    list.addClass("loading");
    jQuery
      .get(supportcandy.ajax_url, {
        action: "wpsc_tag_autocomplete",
        _ajax_nonce: cfg.searchNonce,
        q: input.val(),
        per_page: 10,
        tags: selected
          .map(function (t) {
            return t.id;
          })
          .filter(Boolean),
      })
      .done(function (data) {
        if (current !== reqCounter) {
          return;
        }
        results = Array.isArray(data) ? data : [];
        list.removeClass("loading");
        render();
      })
      .fail(function () {
        if (current === reqCounter) {
          list.removeClass("loading");
        }
      });
  }

  function closeEditor() {
    clearTimeout(timer);
    reqCounter++;
    jQuery(document).off("mousedown.wpscTagsEdit");
    editor.empty();
    wrap.removeClass("editing");
    wrap.find(".wpsc-tags-view").trigger("focus");
  }

  function save() {
    if (!changed()) {
      return;
    }
    editor.addClass("saving");
    jQuery
      .post(supportcandy.ajax_url, {
        action: "wpsc_it_set_edit_ticket_tags",
        ticket_id: cfg.ticketId,
        _ajax_nonce: cfg.saveNonce,
        tags: selected
          .filter(function (t) {
            return t.id !== null;
          })
          .map(function (t) {
            return t.id;
          }),
        new_tags: selected
          .filter(function (t) {
            return t.id === null;
          })
          .map(function (t) {
            return t.name;
          }),
      })
      .always(function () {
        wpsc_it_refresh_tags(cfg.ticketId);
      });
  }

  input.on("input", function () {
    render();
    clearTimeout(timer);
    timer = setTimeout(load, 200);
  });
  input.on("keydown", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      if (jQuery.trim(input.val())) {
        addTyped();
      } else {
        save();
      }
    } else if (e.key === "ArrowDown") {
      e.preventDefault();
      list.find(".wpsc-ie-item").first().trigger("focus");
    }
  });
  editor.on("keydown.wpscTags", function (e) {
    if (e.key === "Escape") {
      closeEditor();
    }
  });
  wpsc_ie_bind_list_keys(editor);
  cancel.on("click", closeEditor);
  submit.on("click", save);
  // click outside closes the editor when nothing was changed
  jQuery(document)
    .off("mousedown.wpscTagsEdit")
    .on("mousedown.wpscTagsEdit", function (ev) {
      if (!jQuery(ev.target).closest(wrap).length && !changed()) {
        closeEditor();
      }
    });

  render();
  input.trigger("focus");
  load();
}
