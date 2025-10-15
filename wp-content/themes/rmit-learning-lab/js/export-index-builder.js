(function() {
  'use strict';

  var settings = window.RMITExportIndex || null;
  if (!settings || !settings.shouldBuild) {
    return;
  }

  var statusSelector = settings.statusSelector || '#rmit-export-index-status';
  var statusElement = document.querySelector(statusSelector);

  function ensureStatusElement() {
    if (!statusElement) {
      return null;
    }
    statusElement.style.display = '';
    return statusElement;
  }

  function updateStatus(message, type) {
    var container = ensureStatusElement();
    if (!container) {
      return;
    }

    var className = 'notice notice-info';
    if (type === 'success') {
      className = 'notice notice-success';
    } else if (type === 'error') {
      className = 'notice notice-error';
    }

    container.className = className;
    container.innerHTML = '<p>' + message + '</p>';
  }

  function loadFuse() {
    if (typeof window.Fuse === 'function') {
      return Promise.resolve(window.Fuse);
    }

    return new Promise(function(resolve, reject) {
      var script = document.createElement('script');
      script.src = settings.fuseUrl;
      script.async = true;
      script.onload = function() {
        if (typeof window.Fuse === 'function') {
          resolve(window.Fuse);
        } else {
          reject(new Error('Fuse.js failed to initialise.'));
        }
      };
      script.onerror = function(event) {
        reject(new Error('Unable to load Fuse.js.'));
      };
      document.head.appendChild(script);
    });
  }

  function fetchPagesDataset() {
    var requestUrl = settings.pagesJson;
    if (!requestUrl) {
      return Promise.reject(new Error('Dataset URL missing.'));
    }

    return fetch(requestUrl, {
      credentials: 'same-origin',
      cache: 'no-store',
    }).then(function(response) {
      if (!response.ok) {
        throw new Error('Dataset request failed with status ' + response.status + '.');
      }
      return response.json();
    });
  }

  function saveIndex(indexPayload) {
    var formData = new FormData();
    formData.append('action', 'rmit_ll_save_fuse_index');
    formData.append('nonce', settings.nonce);
    formData.append('index', JSON.stringify(indexPayload));

    return fetch(settings.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData,
    }).then(function(response) {
      if (!response.ok) {
        throw new Error('Index save request failed with status ' + response.status + '.');
      }
      return response.json();
    }).then(function(payload) {
      if (!payload || !payload.success) {
        throw new Error(payload && payload.data && payload.data.message ? payload.data.message : 'Unknown error from server.');
      }
      return payload;
    });
  }

  var messages = settings.messages || {};
  updateStatus(messages.starting || 'Building Fuse.js index…', 'info');

  Promise.all([loadFuse(), fetchPagesDataset()])
    .then(function(results) {
      var FuseLib = results[0];
      var dataset = results[1];
      var keys = Array.isArray(settings.keys) && settings.keys.length ? settings.keys : ['title', 'content', 'keywords'];
      var index = FuseLib.createIndex(keys, dataset);
      updateStatus(messages.saving || 'Saving Fuse.js index…', 'info');
      return saveIndex(index.toJSON());
    })
    .then(function() {
      updateStatus(messages.success || 'Fuse.js index updated.', 'success');
    })
    .catch(function(error) {
      console.error('Fuse index build failed:', error);
      updateStatus((messages.failure || 'Unable to generate Fuse.js index.') + ' ' + error.message, 'error');
    });
})();
