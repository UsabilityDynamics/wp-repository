!function($, data) {
    var btn = $("#wpr_check_updates_for_repository"), path_field = $("#repository_path"), spinner = $("#wpr_spinner");
    btn.click(function() {
        var access_token = jQuery('[name="a_access_token"]').val(), path = jQuery('[name="a_path"]').val(), nocache = jQuery('[name="a_nocache"]').is(":checked"), organizations = "undefined" != typeof jQuery('[name="a_organizations"]:checked').val() ? jQuery('[name="a_organizations"]:checked').val() : jQuery('[name="a_organizations"]').val();
        return btn.prop("disabled", !0), spinner.show(), $.ajax({
            type: "POST",
            url: data.ajax_url,
            data: {
                access_token: access_token,
                organizations: organizations,
                path: path,
                nocache: nocache
            },
            success: function(r) {
                alert(1 == r.ok ? r.message : r.message);
            }
        }).done(function() {
            btn.prop("disabled", !1), spinner.hide();
        }), !1;
    }), data.is_defined_constant && path_field.hide().after('<p style="font-size:14px;">' + data.current_path + "</p>");
}(jQuery, _ud_wpr_settings);