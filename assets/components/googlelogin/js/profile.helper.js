Ext.onReady(function() {
    var div = Ext.get('modx-panel-profile-update');
    if (googlelogin.config.loginUrl) {
        Ext.DomHelper.insertAfter(div, '<div class="x-panel container">'
            + `<a class="x-btn primary-button x-btn-text" href=${googlelogin.config.loginUrl} >${_('googlelogin.connect_google')}</a>`
            + '</div>');
    }
    if (googlelogin.config.glogId) {
        Ext.DomHelper.insertAfter(div, '<div class="x-panel container">'
            + `<a class="x-btn primary-button x-btn-text" href=${googlelogin.config.disconnectUrl} >${_('googlelogin.disconnect_google')}</a>`
            + '</div>');
    }
});
