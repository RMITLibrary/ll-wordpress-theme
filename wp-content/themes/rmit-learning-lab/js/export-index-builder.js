(function() {
  'use strict';

  var settings = window.RMITExportIndex || null;
  if (!settings || !settings.shouldBuild) {
    return;
  }

  var statusSelector = settings.statusSelector || '#rmit-export-index-status';
  var statusElement = document.querySelector(statusSelector);
  var labels = settings.labels || {};

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

  function updateFuseRow(meta) {
    if (!meta || typeof meta !== 'object') {
      return;
    }

    var updatedCell = document.querySelector('[data-fuse-index="updated"]');
    if (updatedCell && meta.formatted) {
      updatedCell.textContent = meta.formatted;

      if (meta.timezone_label || meta.relative) {
        var info = [];
        if (meta.timezone_label) {
          info.push(meta.timezone_label);
        }
        if (meta.relative) {
          info.push(meta.relative + ' ago');
        }

        if (info.length) {
          var description = document.createElement('span');
          description.className = 'description';
          description.textContent = info.join(' • ');
          updatedCell.appendChild(document.createTextNode(' '));
          updatedCell.appendChild(description);
        }
      }
    } else if (updatedCell && labels.notGenerated) {
      updatedCell.textContent = labels.notGenerated;
    }

    var sizeCell = document.querySelector('[data-fuse-index="size"]');
    if (sizeCell) {
      sizeCell.textContent = meta.size_label || '—';
    }

    var actionsCell = document.querySelector('[data-fuse-index="actions"]');
    if (actionsCell) {
      while (actionsCell.firstChild) {
        actionsCell.removeChild(actionsCell.firstChild);
      }

      if (meta.url) {
        var link = document.createElement('a');
        link.className = 'button';
        link.href = meta.url;
        link.target = '_blank';
        link.rel = 'noopener';
        link.textContent = labels.viewJson || 'View JSON';
        actionsCell.appendChild(link);
      } else {
        var message = document.createElement('span');
        message.className = 'description';
        message.textContent = labels.noFile || 'No file available';
        actionsCell.appendChild(message);
      }
    }
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
    .then(function(payload) {
      updateStatus(messages.success || 'Fuse.js index updated.', 'success');
      if (payload && payload.data && payload.data.meta) {
        updateFuseRow(payload.data.meta);
      }
    })
    .catch(function(error) {
      console.error('Fuse index build failed:', error);
      updateStatus((messages.failure || 'Unable to generate Fuse.js index.') + ' ' + error.message, 'error');
    });
})();
