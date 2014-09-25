!function($, strings) {
    var btn = $("#wpr_check_updates_for_repository"), spinner = $("#wpr_spinner");
    btn.click(function() {
        return btn.prop("disabled", !0), spinner.show(), $.ajax({
            type: "POST",
            url: strings.ajax_url,
            data: {
                action: "wprepository_check_updates"
            },
            success: function(r) {
                alert(1 == r.ok ? r.message : r.message);
            }
        }).done(function() {
            btn.prop("disabled", !1), spinner.hide();
        }), !1;
    });
}(jQuery, _ud_wpr_settings);