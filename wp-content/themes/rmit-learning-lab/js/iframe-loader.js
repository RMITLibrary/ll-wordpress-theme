(function() {
  'use strict';

  var assets = window.RMITIframeAssets || {};
  var pendingHostScript = null;
  var observer = null;

  function scriptExists(src, attributeSelector) {
    if (!src) {
      return false;
    }

    if (attributeSelector && document.querySelector(attributeSelector)) {
      return true;
    }

    var scripts = document.querySelectorAll('script[src]');
    for (var i = 0; i < scripts.length; i++) {
      if (scripts[i].src === src) {
        return true;
      }
    }

    return false;
  }

  function injectScript(key, src) {
    if (!src) {
      return Promise.resolve(false);
    }

    var attribute = 'data-rmit-iframe-loader';
    var selector = 'script[' + attribute + '="' + key + '"]';

    if (scriptExists(src, selector)) {
      return Promise.resolve(true);
    }

    if (key === 'host' && typeof window.iFrameResize === 'function') {
      return Promise.resolve(true);
    }

    return new Promise(function(resolve, reject) {
      var script = document.createElement('script');
      script.src = src;
      script.async = false;
      script.setAttribute(attribute, key);
      script.onload = function() {
        resolve(true);
      };
      script.onerror = function(event) {
        reject(event);
      };
      document.head.appendChild(script);
    });
  }

  function ensureHostResizer() {
    if (!assets.host) {
      return;
    }

    if (!pendingHostScript) {
      pendingHostScript = injectScript('host', assets.host).catch(function(error) {
        pendingHostScript = null;
        if (window.console && window.console.warn) {
          console.warn('Failed to load iframeResizer host script', error);
        }
      });
    }

    if (!pendingHostScript) {
      return;
    }

    pendingHostScript.then(function() {
      if (typeof window.iFrameResize === 'function') {
        window.iFrameResize({ log: false });
      }
    });
  }

  function shouldResizeIframe(node) {
    if (!node || node.nodeType !== 1 || node.tagName !== 'IFRAME') {
      return false;
    }

    if (node.hasAttribute('data-rmit-ignore-resizer')) {
      return false;
    }

    var src = (node.getAttribute('src') || '').trim();
    if (!src) {
      return false;
    }

    var ignorePattern = /(?:googletagmanager|doubleclick|adservice\.google|adsystem|scorecardresearch)/i;
    if (ignorePattern.test(src)) {
      return false;
    }

    return true;
  }

  function pageHasResizableIframe() {
    var iframes = document.getElementsByTagName('iframe');
    for (var i = 0; i < iframes.length; i++) {
      if (shouldResizeIframe(iframes[i])) {
        return true;
      }
    }
    return false;
  }

  function evaluateForHostResize() {
    if (!pageHasResizableIframe()) {
      return false;
    }

    ensureHostResizer();
    return true;
  }

  function onReady(callback) {
    if (document.readyState === 'loading') {
      var wrapped = function() {
        document.removeEventListener('DOMContentLoaded', wrapped);
        callback();
      };
      document.addEventListener('DOMContentLoaded', wrapped);
    } else {
      callback();
    }
  }

  onReady(function() {
    if (!evaluateForHostResize() && 'MutationObserver' in window) {
      observer = new MutationObserver(function(mutations) {
        for (var i = 0; i < mutations.length; i++) {
          var mutation = mutations[i];
          if (!mutation.addedNodes) {
            continue;
          }
          for (var j = 0; j < mutation.addedNodes.length; j++) {
            var node = mutation.addedNodes[j];
            if (shouldResizeIframe(node)) {
              evaluateForHostResize();
              return;
            }
            if (node.querySelectorAll) {
              var nested = node.querySelectorAll('iframe');
              for (var k = 0; k < nested.length; k++) {
                if (shouldResizeIframe(nested[k])) {
                  evaluateForHostResize();
                  return;
                }
              }
            }
          }
        }
      });
      observer.observe(document.body, { childList: true, subtree: true });
    }
  });

  function loadEmbeddedScripts() {
    var params = null;
    try {
      params = new URLSearchParams(window.location.search);
    } catch (error) {
      params = null;
    }

    var embedParam = params && typeof params.get === 'function' ? params.get('iframe') : null;
    var isEmbedded = window.top !== window.self || embedParam === 'true';

    if (!isEmbedded) {
      return;
    }

    if (assets.content) {
      injectScript('content', assets.content).catch(function(error) {
        if (window.console && window.console.warn) {
          console.warn('Failed to load iframeResizer content script', error);
        }
      });
    }

    if (assets.lti) {
      injectScript('lti', assets.lti).catch(function(error) {
        if (window.console && window.console.warn) {
          console.warn('Failed to load LTI resize helper', error);
        }
      });
    }
  }

  loadEmbeddedScripts();
})();
