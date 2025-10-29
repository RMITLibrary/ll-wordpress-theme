(function() {
    'use strict';

    var dataURL = "../wp-content/uploads/pages.json?v=1.1.3";
    var indexURL = "../wp-content/uploads/pages-index.json?v=1.1.3";
    var fuseScriptURL = "https://cdn.jsdelivr.net/npm/fuse.js";

    var fuseScriptPromise = null;
    var pagesData = null;
    var pagesDataPromise = null;
    var prebuiltIndexData = null;
    var prebuiltIndexPromise = null;
    var parsedFuseIndex = null;

    var urlParamsSearch;
    try {
        urlParamsSearch = new URLSearchParams(window.location.search);
    } catch (error) {
        urlParamsSearch = null;
    }

    var searchString = urlParamsSearch ? urlParamsSearch.get('query') : null;

    var searchInput = document.getElementById('searchInput');
    if (!searchInput) {
        return;
    }

    var searchButton = document.getElementById('searchButton');
    var searchForm = searchInput.closest('form');
    var resultsList = document.getElementById('results');
    var resultsCountDisplay = document.getElementById('results-counter');
    var collapseElement = document.getElementById('results-container');

    function loadFuseScript() {
        if (typeof Fuse === 'function') {
            return Promise.resolve(Fuse);
        }

        if (!fuseScriptPromise) {
            fuseScriptPromise = new Promise(function(resolve, reject) {
                var script = document.createElement('script');
                script.src = fuseScriptURL;
                script.async = true;
                script.onload = function() {
                    if (typeof Fuse === 'function') {
                        resolve(Fuse);
                    } else {
                        fuseScriptPromise = null;
                        reject(new Error('Fuse.js loaded but the global constructor is missing.'));
                    }
                };
                script.onerror = function(event) {
                    fuseScriptPromise = null;
                    reject(new Error('Failed to load Fuse.js')); 
                };
                document.head.appendChild(script);
            });
        }

        return fuseScriptPromise;
    }

    function loadPagesData() {
        if (Array.isArray(pagesData)) {
            return Promise.resolve(pagesData);
        }

        if (!pagesDataPromise) {
            pagesDataPromise = fetch(dataURL, { credentials: 'same-origin' })
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('Search index request failed with status ' + response.status);
                    }
                    return response.json();
                })
                .then(function(data) {
                    pagesData = data;
                    return data;
                })
                .catch(function(error) {
                    pagesDataPromise = null;
                    throw error;
                });
        }

        return pagesDataPromise;
    }

    function ensureResources() {
        return Promise.all([loadFuseScript(), loadPagesData(), loadPrebuiltIndex()]).then(function(resources) {
            var FuseLib = resources[0];
            var indexData = resources[2];

            if (indexData && !parsedFuseIndex) {
                try {
                    parsedFuseIndex = FuseLib.parseIndex(indexData);
                } catch (error) {
                    parsedFuseIndex = null;
                    if (window.console && window.console.warn) {
                        console.warn('Failed to parse Fuse index', error);
                    }
                }
            }

            return resources;
        });
    }

    function primeResources() {
        loadFuseScript().catch(function() {});
        loadPagesData().catch(function() {});
        loadPrebuiltIndex().catch(function() {});
    }

    function loadPrebuiltIndex() {
        if (prebuiltIndexData !== null) {
            return Promise.resolve(prebuiltIndexData);
        }

        if (!prebuiltIndexPromise) {
            prebuiltIndexPromise = fetch(indexURL, { credentials: 'same-origin', cache: 'no-store' })
                .then(function(response) {
                    if (response.status === 404) {
                        return null;
                    }
                    if (!response.ok) {
                        throw new Error('Prebuilt index request failed with status ' + response.status);
                    }
                    return response.json();
                })
                .then(function(indexData) {
                    prebuiltIndexData = indexData;
                    return indexData;
                })
                .catch(function(error) {
                    prebuiltIndexPromise = null;
                    if (window.console && window.console.warn) {
                        console.warn('Unable to load prebuilt Fuse index', error);
                    }
                    return null;
                });
        }

        return prebuiltIndexPromise;
    }

    function setStatusText(message) {
        if (resultsCountDisplay) {
            resultsCountDisplay.textContent = message || '';
        }
    }

    function setLoadingState(isLoading, message) {
        if (searchButton) {
            searchButton.disabled = isLoading;
            searchButton.setAttribute('aria-busy', isLoading ? 'true' : 'false');
        }

        searchInput.setAttribute('aria-busy', isLoading ? 'true' : 'false');

        if (typeof message === 'string') {
            setStatusText(message);
        }
    }

    function handleSearchError(error) {
        setStatusText('Unable to load the search index. Please try again.');
        if (window.console && window.console.error) {
            console.error(error);
        }
    }

    function getFuseOptions() {
        return {
            keys: ['title', 'content', 'keywords'],
            threshold: 0.4,
            distance: 1200,
            location: 0,
            minMatchCharLength: 4
        };
    }

    function performSearch(FuseLib, data, parsedIndex) {
        var query = searchInput.value.trim();
        if (!query) {
            setStatusText('Enter a search term to begin.');
            if (resultsList) {
                resultsList.innerHTML = '';
            }
            return;
        }

        var fuseOptions = getFuseOptions();
        var fuse = parsedIndex ? new FuseLib(data, fuseOptions, parsedIndex) : new FuseLib(data, fuseOptions);
        var results = fuse.search(query);

        if (!resultsList) {
            return;
        }

        resultsList.innerHTML = '';
        var resultCount = 0;

        results.forEach(function(result) {
            var item = result.item;
            if (!shouldIncludeResult(item.keywords, item.link)) {
                return;
            }

            var li = document.createElement('li');
            li.classList.add('result-item');
            li.innerHTML = '<a href="..' + item.link + '"><h3 class="text">' + item.title + '</h3></a>';

            var breadcrumbs = getBreadcrumbs(item.breadcrumbs);
            if (breadcrumbs) {
                li.innerHTML += '<ul class="breadcrumbs">' + breadcrumbs + '</ul>';
            }

            var snippet = getSnippet(cleanJSONContent(item.content), query);
            li.innerHTML += '<p>' + snippet + '</p>';

            resultsList.appendChild(li);

            if (window.MathJax && typeof window.MathJax.typesetPromise === 'function') {
                window.MathJax.typesetPromise([li]).catch(function(mathError) {
                    if (window.console && window.console.warn) {
                        console.warn('MathJax rendering error', mathError);
                    }
                });
            }

            resultCount++;
        });

        updateResultsCount(resultCount);
        handleSearchFocus(resultCount);
    }

    function getBreadcrumbs(arr) {
        if (!Array.isArray(arr)) {
            return '';
        }

        var breadcrumbStr = '';
        for (var i = 0; i < arr.length - 1; i++) {
            breadcrumbStr += '<li>' + arr[i]['title'] + '</li>';
        }
        return breadcrumbStr;
    }

    function getSnippet(content, query) {
        var snippetLength = 270;
        var halfSnippetLength = snippetLength / 2;

        if (!query) {
            return content.substring(0, snippetLength);
        }

        var lowerContent = content.toLowerCase();
        var index = lowerContent.indexOf(query.toLowerCase());

        if (index !== -1) {
            var snippetStart = Math.max(0, index - halfSnippetLength);
            var snippetEnd = Math.min(content.length, index + halfSnippetLength);

            if (snippetEnd - snippetStart < snippetLength) {
                if (snippetStart === 0) {
                    snippetEnd = Math.min(content.length, snippetStart + snippetLength);
                } else {
                    snippetStart = Math.max(0, snippetEnd - snippetLength);
                }
            }

            while (snippetStart > 0 && !/\s/.test(content.charAt(snippetStart - 1))) {
                snippetStart--;
            }
            while (snippetEnd < content.length && !/\s/.test(content.charAt(snippetEnd))) {
                snippetEnd++;
            }

            var openIndex = content.lastIndexOf('\\[', snippetStart);
            var closeIndex = content.indexOf('\\]', snippetEnd);

            if (openIndex !== -1 && (closeIndex === -1 || closeIndex > snippetEnd)) {
                snippetEnd = Math.min(content.length, closeIndex + 2);
            }

            var snippet = content.substring(snippetStart, snippetEnd).trim();
            snippet = snippet.replace(/\\ce/g, '');

            if (snippetStart > 0) {
                snippet = '&hellip;' + snippet;
            }
            if (snippetEnd < content.length) {
                snippet += '&hellip;';
            }

            var escapedQuery = query.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
            var regex = new RegExp('(' + escapedQuery + ')', 'gi');
            snippet = snippet.replace(regex, '<span class="highlight-1">$1</span>');

            return snippet;
        }

        return content.substring(0, snippetLength) + '&hellip;';
    }

    function shouldIncludeResult(keywords, link) {
        var excludeKeywords = ["documentation", "archive", "redirect"];
        var excludePaths = ["/work-in-progress/", "/documentation/"];

        var includeByKeyword = !keywords || !keywords.some(function(keyword) {
            return excludeKeywords.indexOf(keyword.toLowerCase()) !== -1;
        });

        var includeByLink = !excludePaths.some(function(path) {
            return link.indexOf(path) !== -1;
        });

        return includeByKeyword && includeByLink;
    }

    function cleanJSONContent(content) {
        return content
            .replace(/\\\\/g, '\\')
            .replace(/\\r\\n/g, ' ')
            .trim();
    }

    function updateResultsCount(count) {
        if (!resultsCountDisplay) {
            return;
        }

        if (count === 0) {
            resultsCountDisplay.textContent = 'No results found.';
        } else {
            resultsCountDisplay.textContent = count + ' result' + (count > 1 ? 's' : '') + ' found.';
        }
    }

    function handleSearchFocus(count) {
        if (!collapseElement) {
            return;
        }

        if (searchString != null) {
            collapseElement.classList.remove('collapse');
            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, '', window.location.origin + window.location.pathname);
            }
            searchString = null;
        } else if (count > 0 && typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
            var collapseInstance = new bootstrap.Collapse(collapseElement, { toggle: false });
            collapseInstance.show();
            var resultsTitle = document.getElementById('results-title');
            if (resultsTitle) {
                resultsTitle.focus();
            }
        }
    }

    function triggerSearch(fromQuery) {
        var query = searchInput.value.trim();
        if (!query) {
            if (!fromQuery) {
                setStatusText('Enter a search term to begin.');
            }
            return;
        }

        var hasIndex = Array.isArray(pagesData) && (parsedFuseIndex || prebuiltIndexData);
        setLoadingState(true, hasIndex ? 'Searching…' : 'Loading search index…');

        ensureResources()
            .then(function(resources) {
                setLoadingState(false);
                var FuseLib = resources[0];
                var data = resources[1];
                performSearch(FuseLib, data, parsedFuseIndex);
            })
            .catch(function(error) {
                setLoadingState(false);
                handleSearchError(error);
            });
    }

    if (searchForm) {
        searchForm.addEventListener('submit', function(event) {
            event.preventDefault();
            triggerSearch(false);
        });
    }

    searchInput.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            triggerSearch(false);
        }
    });

    if (searchButton) {
        searchButton.addEventListener('click', function(event) {
            event.preventDefault();
            triggerSearch(false);
        });
    }

    searchInput.addEventListener('focus', function() {
        primeResources();
    }, { once: true });

    if (searchString) {
        searchInput.value = searchString;
        primeResources();
        triggerSearch(true);
    } else {
        setStatusText('Enter a search term to begin.');
    }
})();
