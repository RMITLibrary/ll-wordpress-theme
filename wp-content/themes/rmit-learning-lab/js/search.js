(function() {
    'use strict';

    // Set by page-search.php from the index file's mtime; 1.1.3 was hardcoded and never
    // moved, so a returning visitor kept whatever 4.6 MB their cache already held.
    var version = window.LL_SEARCH_VERSION || "1";
    var dataURL = "../wp-content/uploads/pages.json?v=" + version;
    var indexURL = "../wp-content/uploads/pages-index.json?v=" + version;
    // Set by page-search.php. Self-hosted so search does not depend on a third-party
    // CDN being reachable; the literal below is only a fallback if the global is missing.
    var fuseScriptURL = window.LL_FUSE_URL || "../wp-content/themes/rmit-learning-lab/js/fuse/fuse.min.js";

    var fuseScriptPromise = null;
    var pagesData = null;
    var pagesDataPromise = null;
    var prebuiltIndexData = null;
    var prebuiltIndexPromise = null;
    var parsedFuseIndex = null;

    var searchString = new URLSearchParams(window.location.search).get('query');

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
                    console.warn('Failed to parse Fuse index', error);
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
                    console.warn('Unable to load prebuilt Fuse index', error);
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
        console.error(error);
    }

    // Weak matches worth keeping sit at or below 0.048; the first junk result across the
    // queries tested came in at 0.111, so the cut goes in the gap between them.
    var SCORE_CUTOFF = 0.1;
    var MAX_RESULTS = 40;

    // Fuse matches the query as one string, so a question scores badly against everything
    // — "how to reference a website" returned nothing. These carry no meaning on their own.
    var STOP_WORDS = ['how', 'the', 'and', 'for', 'with', 'you', 'your', 'are', 'can', 'what',
        'when', 'where', 'why', 'does', 'from', 'that', 'this', 'into', 'about', 'should',
        'would', 'need', 'want', 'use', 'using', 'any', 'there', 'been', 'have', 'its',
        // two-letter words are allowed through below, so the common ones belong here
        'to', 'of', 'in', 'on', 'is', 'it', 'my', 'me', 'we', 'us', 'an', 'as', 'at', 'be',
        'by', 'do', 'if', 'or', 'so', 'up'];

    function getFuseOptions() {
        return {
            // Pages are indexed whole, so a title match and a word buried in 38kB of body
            // text would otherwise count the same.
            keys: [
                { name: 'title', weight: 3 },
                { name: 'keywords', weight: 2 },
                { name: 'content', weight: 1 }
            ],
            threshold: 0.4,
            minMatchCharLength: 4,
            includeScore: true,
            // location/distance made anything past roughly the first 1200 characters of a
            // page score badly, which hid most of the content.
            ignoreLocation: true,
            // Without this an exact match is penalised for appearing in a long page, so
            // "harvard" ranked a short fuzzy match above the Easy Cite page that says it.
            ignoreFieldNorm: true
        };
    }

    // Turning off field-norm scoring leaves a lot of results tied at 0, and Fuse returns
    // ties in index order — so "referencing" put an images page above the Referencing
    // section. Break ties by where the term actually appears.
    function matchRank(item, needle) {
        if (String(item.title || '').toLowerCase().indexOf(needle) !== -1) {
            return 0;
        }
        if (String(item.keywords || '').toLowerCase().indexOf(needle) !== -1) {
            return 1;
        }
        return 2;
    }

    function sortResults(results, query) {
        var needle = query.trim().toLowerCase();

        return results.slice().sort(function(a, b) {
            var byScore = (a.score || 0) - (b.score || 0);
            if (Math.abs(byScore) > 0.0001) {
                return byScore;
            }

            var byWhere = matchRank(a.item, needle) - matchRank(b.item, needle);
            if (byWhere !== 0) {
                return byWhere;
            }

            // Shorter titles are the more general page: "Referencing" over
            // "Referencing an oral presentation".
            return String(a.item.title || '').length - String(b.item.title || '').length;
        });
    }

    function keywordsOf(item) {
        return String(item.keywords || '').toLowerCase();
    }

    // What students type, mapped to what the site calls it. The keyword taxonomy is a
    // controlled vocabulary, so this covers the gap without adding terms to it — and
    // several of these (RMIT Harvard, APA, AGLC4) exist as terms but are on no page.
    var PHRASE_SYNONYMS = {
        'sig figs': 'significant figures',
        'group assignment': 'group work',
        'lit review': 'literature review',
        'reference list': 'referencing',
        'reading list': 'referencing',
        'apa 7': 'apa',
        'apa7': 'apa'
    };

    var WORD_SYNONYMS = {
        harvard: ['referencing', 'cite'],
        apa: ['referencing', 'cite'],
        apa7: ['referencing', 'cite'],
        vancouver: ['referencing', 'cite'],
        aglc: ['referencing', 'legal'],
        aglc4: ['referencing', 'legal'],
        footnote: ['citation'],
        footnotes: ['citation'],
        endnote: ['referencing', 'cite'],
        zotero: ['referencing', 'cite'],
        mendeley: ['referencing', 'cite'],
        powerpoint: ['presentation'],
        slides: ['presentation'],
        slideshow: ['presentation'],
        stats: ['statistics'],
        sigfigs: ['significant', 'figures'],
        ai: ['artificial', 'intelligence'],
        chatgpt: ['artificial', 'intelligence'],
        copilot: ['artificial', 'intelligence']
    };

    function expandQuery(query) {
        var text = query.toLowerCase();

        Object.keys(PHRASE_SYNONYMS).forEach(function(phrase) {
            if (text.indexOf(phrase) !== -1) {
                text = text.split(phrase).join(PHRASE_SYNONYMS[phrase]);
            }
        });

        var words = meaningfulWords(text);
        var expanded = words.slice();

        words.forEach(function(word) {
            (WORD_SYNONYMS[word] || []).forEach(function(alias) {
                if (expanded.indexOf(alias) === -1) {
                    expanded.push(alias);
                }
            });
        });

        return expanded;
    }

    function meaningfulWords(query) {
        return query.toLowerCase().split(/[^a-z0-9]+/).filter(function(word) {
            // Two-character terms are real here — AI and pH are both searched for.
            return word.length > 1 && STOP_WORDS.indexOf(word) === -1;
        });
    }

    // Search each word, then rank by how many of them a page matched. A page matching
    // every word comes first, but a page matching one still appears — "paraphrasing and
    // summarising" should surface Synthesising, then Summarising and Paraphrasing.
    function searchByWord(fuse, words) {
        var found = {};

        words.forEach(function(word) {
            fuse.search(word).forEach(function(result) {
                if (result.score > SCORE_CUTOFF) {
                    return;
                }
                var key = result.item.link;
                if (!found[key]) {
                    found[key] = { item: result.item, scores: [], matched: 0 };
                }
                found[key].scores.push(result.score);
                found[key].matched += 1;
            });
        });

        return Object.keys(found).map(function(key) {
            var hit = found[key];
            var title = String(hit.item.title || '').toLowerCase();
            var total = hit.scores.reduce(function(sum, score) { return sum + score; }, 0);

            return {
                item: hit.item,
                score: total / hit.scores.length,
                matched: hit.matched,
                inTitle: words.filter(function(word) {
                    return title.indexOf(word) !== -1 || keywordsOf(hit.item).indexOf(word) !== -1;
                }).length
            };
        }).sort(function(a, b) {
            return (b.matched - a.matched)
                || (b.inTitle - a.inTitle)
                || (a.score - b.score)
                || (String(a.item.title || '').length - String(b.item.title || '').length);
        });
    }

    function performSearch(FuseLib, data, parsedIndex, fromQuery) {
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
        var words = expandQuery(query);
        var results = words.length > 1
            ? searchByWord(fuse, words)
            : sortResults(fuse.search(query), query);

        if (!resultsList) {
            return;
        }

        resultsList.innerHTML = '';
        var resultCount = 0;

        results = results.slice(0, MAX_RESULTS);

        results.forEach(function(result) {
            var item = result.item;
            if (typeof result.score === 'number' && result.score > SCORE_CUTOFF) {
                return;
            }
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

            var cleanedContent = cleanJSONContent(item.content);
            if (!cleanedContent && item.meta_description) {
                cleanedContent = item.meta_description.trim();
            }
            var snippet = getSnippet(cleanedContent, query);
            li.innerHTML += '<p>' + snippet + '</p>';

            resultsList.appendChild(li);

            typesetWhenReady(li);

            resultCount++;
        });

        updateResultsCount(resultCount);
        handleSearchFocus(resultCount);

        // Update URL with query parameter (no page refresh) - only for manual searches
        if (!fromQuery && window.history && window.history.pushState) {
            var newUrl = window.location.pathname + '?query=' + encodeURIComponent(query);
            window.history.pushState({ query: query }, '', newUrl);
        }
    }

    // Results are built as soon as the index loads, which can be before MathJax has
    // replaced window.MathJax with its real API. The old check silently skipped
    // typesetting in that window, leaving raw LaTeX in the snippet.
    // ponytail: bounded poll — MathJax offers no ready event before its core loads.
    function typesetWhenReady(el) {
        var waited = 0;

        (function attempt() {
            var mathJax = window.MathJax;

            // Deliberately not chained off MathJax.startup.promise: when a runtime
            // asset 404s (as on a static capture missing mathjax/) that promise never
            // settles, and every result silently stays as raw LaTeX.
            if (mathJax && typeof mathJax.typesetPromise === 'function') {
                mathJax.typesetPromise([el]).catch(function(mathError) {
                    console.warn('MathJax rendering error', mathError);
                });
                return;
            }

            if (waited >= 10000) { return; }
            waited += 100;
            setTimeout(attempt, 100);
        })();
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
        if (index === -1) {
            index = 0;
        }

        var snippetStart = Math.max(0, index - halfSnippetLength);
        var snippetEnd = Math.min(content.length, index + halfSnippetLength);

        if (snippetEnd - snippetStart < snippetLength) {
            if (snippetStart === 0) {
                snippetEnd = Math.min(content.length, snippetStart + snippetLength);
            } else {
                snippetStart = Math.max(0, snippetEnd - snippetLength);
            }
        }

        // Expand to word boundaries
        while (snippetStart > 0 && !/\s/.test(content.charAt(snippetStart - 1))) {
            snippetStart--;
        }
        while (snippetEnd < content.length && !/\s/.test(content.charAt(snippetEnd))) {
            snippetEnd++;
        }

        // SMARTER APPROACH: Check if snippetEnd is INSIDE a formula
        // If so, move snippetEnd to BEFORE the formula starts
        var formulaStarts = ['\\(', '\\['];
        for (var i = 0; i < formulaStarts.length; i++) {
            var formulaStart = formulaStarts[i];
            // Find the last formula that starts before snippetEnd
            var lastFormulaStart = content.lastIndexOf(formulaStart, snippetEnd - 1);
            if (lastFormulaStart !== -1 && lastFormulaStart >= snippetStart) {
                // Found a formula start before our cut point - check if it closes after
                var formulaEnd = getCompoundFormulaEnd(content, lastFormulaStart);
                if (formulaEnd > snippetEnd) {
                    // We're cutting mid-formula! Move snippetEnd to before the formula
                    snippetEnd = lastFormulaStart;
                    // Trim back to word boundary
                    while (snippetEnd > snippetStart && !/\s/.test(content.charAt(snippetEnd - 1))) {
                        snippetEnd--;
                    }
                }
            }
        }

        // Check if we're cutting mid-word BEFORE extracting snippet
        // Look at the character right before snippetEnd in the original content
        if (snippetEnd < content.length) {
            var charAtEnd = content.charAt(snippetEnd - 1);
            var charAfterEnd = content.charAt(snippetEnd);
            // If we're in the middle of a word (no whitespace before or after cut point)
            if (!/\s/.test(charAtEnd) && !/\s/.test(charAfterEnd)) {
                // Move snippetEnd back to the last space
                while (snippetEnd > snippetStart && !/\s/.test(content.charAt(snippetEnd - 1))) {
                    snippetEnd--;
                }
            }
        }

        var snippet = content.substring(snippetStart, snippetEnd).trim();
        snippet = stripIncompleteFormulas(snippet);

        if (snippetStart > 0) {
            snippet = '&hellip;' + snippet;
        }
        if (snippetEnd < content.length) {
            snippet += '&hellip;';
        }

        // Highlight the search query
        var escapedQuery = query.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        var regex = new RegExp('(' + escapedQuery + ')', 'gi');
        snippet = snippet.replace(regex, '<span class="highlight-1">$1</span>');

        return snippet;
    }

    function shouldIncludeResult(keywords, link) {
        // Defined once in helper-utils.php and passed in by page-search.php, so the browse
        // list and the results cannot drift apart. The literals are only a fallback.
        var exclusions = window.LL_SEARCH_EXCLUSIONS || {};
        var excludeKeywords = exclusions.keywords || ["documentation", "archive", "redirect"];
        var excludePaths = exclusions.paths || ["/work-in-progress/", "/documentation/"];

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
        .replace(/\r?\n/g, ' ')
        .trim();
}

function getCompoundFormulaEnd(content, startPos) {
    var pos = startPos;

    // If we start with inline or display math delimiters, find their end first
    // These are containers that may have complex content inside
    if (content.substr(pos, 2) === '\\(') {
        var closePos = content.indexOf('\\)', pos + 2);
        if (closePos !== -1) {
            return closePos + 2; // Return end of inline math block
        }
        return content.length; // Unclosed, take to end
    }

    if (content.substr(pos, 2) === '\\[') {
        var closePos = content.indexOf('\\]', pos + 2);
        if (closePos !== -1) {
            return closePos + 2; // Return end of display math block
        }
        return content.length; // Unclosed, take to end
    }

    // For other formulas (\ce{}, _{}, ^{}), look for compound chains
    var foundMore = true;

    while (foundMore) {
        foundMore = false;

        // Try to match chemistry notation \ce{...}
        if (content.substr(pos, 4) === '\\ce{') {
            var closePos = findMatchingBrace(content, pos + 3);
            if (closePos !== -1) {
                pos = closePos + 1;
                foundMore = true;
                continue;
            }
        }

        // Try to match subscript _{...}
        if (content.substr(pos, 2) === '_{') {
            var closePos = findMatchingBrace(content, pos + 1);
            if (closePos !== -1) {
                pos = closePos + 1;
                foundMore = true;
                continue;
            }
        }

        // Try to match superscript ^{...}
        if (content.substr(pos, 2) === '^{') {
            var closePos = findMatchingBrace(content, pos + 1);
            if (closePos !== -1) {
                pos = closePos + 1;
                foundMore = true;
                continue;
            }
        }

        // Handle LaTeX arrow commands (check longest first)
        if (content.substr(pos, 16) === '\\leftrightarrow') {
            pos += 16;
            foundMore = true;
            continue;
        }
        if (content.substr(pos, 11) === '\\rightarrow') {
            pos += 11;
            foundMore = true;
            continue;
        }
        if (content.substr(pos, 10) === '\\leftarrow') {
            pos += 10;
            foundMore = true;
            continue;
        }

        // Handle simple operators and spaces
        var currentChar = content.charAt(pos);
        if (currentChar === ' ' || currentChar === '+' || currentChar === '-' ||
            currentChar === '=' || currentChar === '→' || currentChar === '↔') {
            pos++;
            foundMore = true;
            continue;
        }

        // Check for numeric coefficients (e.g., "2" in "2\ce{H2O}")
        if (/[0-9]/.test(currentChar)) {
            pos++;
            foundMore = true;
            continue;
        }
    }

    return pos;
}

function findMatchingBrace(content, braceStart) {
    var depth = 0;

    for (var i = braceStart; i < content.length; i++) {
        var character = content.charAt(i);

        if (character === '{') {
            depth++;
        } else if (character === '}') {
            depth--;

            if (depth === 0) {
                return i;
            }
        }
    }

    return -1;
}

function stripIncompleteFormulas(snippet) {
    if (!snippet) {
        return snippet;
    }

    var patterns = [
        { open: '\\(', close: '\\)', closeLength: 2 },
        { open: '\\[', close: '\\]', closeLength: 2 },
        { open: '\\ce{', close: '}', requireBraceMatch: true },
        { open: '_{', close: '}', requireBraceMatch: true },
        { open: '^{', close: '}', requireBraceMatch: true }
    ];

    var changed = true;

    while (changed) {
        changed = false;

        outer: for (var i = 0; i < patterns.length; i++) {
            var pattern = patterns[i];
            var searchIndex = 0;

            while (searchIndex < snippet.length) {
                var openIndex = snippet.indexOf(pattern.open, searchIndex);

                if (openIndex === -1) {
                    break;
                }

                var closeIndex = getFormulaCloseIndexWithinSnippet(snippet, openIndex, pattern);

                if (closeIndex === -1) {
                    snippet = snippet.substring(0, openIndex).replace(/\s+$/g, '');
                    changed = true;
                    break outer;
                }

                var nextSearch = pattern.requireBraceMatch ? closeIndex + 1 : closeIndex + pattern.closeLength;
                searchIndex = Math.max(openIndex + 1, nextSearch);
            }
        }
    }

    return snippet;
}

function getFormulaCloseIndexWithinSnippet(snippet, openIndex, pattern) {
    if (pattern.requireBraceMatch) {
        var braceStart = openIndex + pattern.open.length - 1;

        if (braceStart < 0 || braceStart >= snippet.length || snippet.charAt(braceStart) !== '{') {
            return -1;
        }

        return findMatchingBrace(snippet, braceStart);
    }

    return snippet.indexOf(pattern.close, openIndex + pattern.open.length);
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
            // Show results container for URL-based search
            collapseElement.classList.remove('collapse');
            // Mark searchString as processed to prevent re-running this branch
            searchString = null;
        } else if (count > 0) {
            // Show results container for manual search (no animation for smooth UX)
            collapseElement.classList.remove('collapse');
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
                performSearch(FuseLib, data, parsedFuseIndex, fromQuery);
            })
            .catch(function(error) {
                setLoadingState(false);
                handleSearchError(error);
            });
    }

    // Enter in the input and the type="submit" button both fire this natively.
    searchForm.addEventListener('submit', function(event) {
        event.preventDefault();
        triggerSearch(false);
    });

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
