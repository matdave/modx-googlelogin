Ext.onReady(function() {
    var div = Ext.get('modx-panel-holder');
    Ext.DomHelper.insertBefore(div, '<div class="container modx_error">' +
        '<div class="error_container">' +
        _('googlelogin.disable_regular_login_warning', {link: "${MODx.config.manager_url}?a=security/profile"}) +
        '</div>' +
    '</div>');
});