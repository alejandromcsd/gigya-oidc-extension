var oidcHelper = {

  loadConfig: function() {
    var oidcHTML = document.getElementById("oidcExtension").innerHTML.replace(/^\s+|\s+$/g, '');
    var oidcJSON = JSON.parse(oidcHTML);
    oidcHelper.apiKey = oidcJSON.apiKey;
    oidcHelper.config = oidcJSON.config;
  },

  reloadGigyaOIDC: function() {
    var s = document.createElement("script");
    s.type = "text/javascript";
    s.src = "https://cdns.gigya.com/js/gigya.oidc.js?apiKey=" + oidcHelper.apiKey;
    s.innerText = JSON.stringify(oidcHelper.config);
    s.id = "gigyaOIDC";
    document.head.appendChild(s);
  },

  urlParams: function(query) {
    if (query.indexOf('?') > -1)
        query = query.substring(query.indexOf('?') + 1);
    var queryParts = query.split(/&/);
    var params = {};
    for (var i = 0; i < queryParts.length; ++i) {
        var param = queryParts[i];
        if (param.indexOf('=') === -1)
            continue;
        var paramParts = param.split('=');
        if (paramParts.length !== 2)
            continue;
        params[paramParts[0]] = decodeURIComponent(paramParts[1]);
    }
    return params;
  },

  getUid: function (callback) {
    gigya.socialize.getUserInfo({
      callback: function (response) {
        if (response.errorCode === 0) {
          var uidParams = {
            UID: response['UID'],
            UIDSignature: response['UIDSignature'],
            signatureTimestamp: response['signatureTimestamp']
          }
          callback(uidParams);
        }
      }
    });
  },

  consentCall: function (uidParams) {
    var result = oidcHelper.urlParams(window.location.search);

    var consentParams = {
      context: result["context"],
      clientID: result["client_id"],
      scope: result["scope"],
      UID: uidParams.UID,
      UIDSignature: uidParams.UIDSignature,
      signatureTimestamp: uidParams.signatureTimestamp
    };

    // Optionally, pass consent to server-side here, for server authorization
    var consentUrl = gigya.utils.URL.addParamsToURL("consent.php", consentParams);

    fetch(consentUrl).then(function(response) {
      response.text().then(function(text) {
        if (response.ok) {
          oidcHelper.afterConsent(text);
        }
      })
    });
  },

  afterConsent: function(afterConsentUrl){
    var url = window.location.href;
    var tempArray = url.split("?");
    var baseURL = tempArray[0];

    var returnUrl = baseURL + afterConsentUrl;
    window.history.replaceState('', '', returnUrl);
    oidcHelper.checkForOIDCEvents();
  },

  checkForOIDCEvents: function() {
    var result = oidcHelper.urlParams(window.location.search);
    var param = result["mode"];
    if(!param || oidcHelper.modeParam===param) return;

    // Reload Gigya OIDC
    var elem = document.getElementById("gigyaOIDC");
    if(elem) {
      elem.parentNode.removeChild(elem);
    }
    oidcHelper.reloadGigyaOIDC();
    oidcHelper.modeParam = param;
  },

  getParams: function() {
    var result = oidcHelper.urlParams(window.location.hash || window.location.search);
    return {
        mode: result['mode'],
        context: result['context'],
        clientID: result['client_id'] || result['clientID'],
        scope: result['scope'],
        prompt: result['prompt'],
        display: result['display'],
        message: result['errorMessage'],
        code: result['errorCode'],
        callID: result['callId']
    }
  },

  saveContext: function(done) {
    // This is required, in case user is already logged in
    // /authorize/continue will fail otherwise
    var key = 'gig_oidcContext_' + oidcHelper.apiKey;

    var sessionStorageTimeout = 10 * 60 * 1000; // allow user 10 minutes to finish
    gigya.utils.sessionCache.get(key, sessionStorageTimeout, function (savedContexts) {
        savedContexts = savedContexts || [];
        savedContexts.push(oidcHelper.getParams());

        gigya.utils.sessionCache.set(key, savedContexts);
        done();
    });
  },

  checkForUIChanges: function() {
    var result = oidcHelper.urlParams(window.location.hash);
    var fragment = result["#oidcUI"];
    if(!fragment || oidcHelper.uiFragment===fragment) return;

    switch (fragment) {
      case "login":
        gigya.accounts.addEventHandlers({
          onLogin: function () {
            window.location.hash = "#oidcUI=consent";
          }
        });
        gigya.accounts.showScreenSet({
          screenSet: 'Default-RegistrationLogin',
          containerID: "container",
          sessionExpiration: '14400' // 4 hours
        });

        break;
      case "consent":
        document.getElementById("container").style.display = 'none';

        // OPTIONAL: Capture consent from user
        oidcHelper.uidParams
          ? oidcHelper.consentCall(oidcHelper.uidParams)
          : oidcHelper.getUid(oidcHelper.consentCall);
        break;
    }

    oidcHelper.uiFragment = fragment;
  },

  init: function() {
    oidcHelper.loadConfig();
    window.onhashchange = oidcHelper.checkForUIChanges;

    oidcHelper.saveContext(function() {
      oidcHelper.getUid(function (uidParams) {
          if (uidParams.UID) {
            oidcHelper.uidParams = uidParams;
            window.location.hash = "#oidcUI=consent";
          } else {
            oidcHelper.reloadGigyaOIDC();
          }
      });
    });
  }
};

oidcHelper.init();
