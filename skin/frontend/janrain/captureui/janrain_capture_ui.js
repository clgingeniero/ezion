jQuery(function(){
  jQuery(".janrain_capture_anchor").colorbox({
    iframe: true,
    width: 700,
    height: 700,
    scrolling: false,
    overlayClose: false,
    current: '',
    next: '',
    previous: ''
  });
});

var CAPTURE = {
  resize: function(jargs) {
    var args = jQuery.parseJSON(jargs);
    jQuery.colorbox.resize({ innerWidth: args.w, innerHeight: args.h });
    if(typeof(janrain_capture_on_resize) == 'function') {
      janrain_capture_on_resize(args);
    }
  },
  completeAuth: function() {
    if (typeof(janrain_capture_on_complete_auth) == 'function') {
      janrain_capture_on_complete_auth();
    } else {
      window.location.href = CAPTURE.completeAuthUrl;
    }
  },
  closeRecoverPassword: function() {
    jQuery.colorbox.close();
    if (typeof(janrain_capture_on_close_recover_password) == 'function') {
      janrain_capture_on_close_recover_password();
    } else {
      window.location.href = CAPTURE.recoverPasswordUrl;
    }
  },
  closeProfile: function() {
    jQuery.colorbox.close();
    if (typeof(janrain_capture_on_close_profile) == 'function') {
      janrain_capture_on_close_profile();
    } else {
      window.location.href = CAPTURE.profileSyncUrl;
    }
  },
  token_expired: function() {
    if (typeof(janrain_capture_on_token_expired) == 'function') {
      janrain_capture_on_token_expired();
    } else {
      window.location.href = CAPTURE.tokenExpiredUrl;
    }
  },
  bp_ready: function() {
    if (typeof(window.Backplane) != 'undefined') {
      var channelId = Backplane.getChannelID();
      if (typeof(channelId) != 'undefined' && typeof(janrain_capture_on_bp_ready) == 'function')
        janrain_capture_on_bp_ready(channelId);
      jQuery('.janrain_capture_signin').each(function(){
        channelId = encodeURIComponent(channelId);
        jQuery(this).attr("href", jQuery(this).attr("href") + "&bp_channel=" + channelId).click(function(){
          Backplane.expectMessages("identity/login");
        });
      });
    }
  },
  logout: function() {
    if (typeof(JANRAIN.SSO.CAPTURE.logout) == 'function' && typeof(CAPTURE.ssoUrl) != 'undefined') {
      JANRAIN.SSO.CAPTURE.logout({
        sso_server: CAPTURE.ssoUrl,
        logout_uri: CAPTURE.logoutUrl
      });
    } else {
      window.location.href = CAPTURE.logoutUrl;
    }
  }
};
