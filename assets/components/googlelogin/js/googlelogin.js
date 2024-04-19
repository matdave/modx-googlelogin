var GoogleLogin = function (config){
    config = config || {};
    GoogleLogin.superclass.constructor.call(this, config);
};
Ext.extend(GoogleLogin, Ext.Component, {
    config: {},
});
Ext.reg('googlelogin', GoogleLogin);
googlelogin = new GoogleLogin();