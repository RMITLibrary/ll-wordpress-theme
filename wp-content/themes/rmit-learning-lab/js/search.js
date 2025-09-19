var dataURL = "../wp-content/uploads/pages.json?v=1.1.3";
var debug = false;

// Check query string for debug and search initiation
const urlParamsSearch = new URLSearchParams(window.location.search);
const debugBool = urlParamsSearch.get('debug');
const searchString = urlParamsSearch.get('query');

// Enable debug mode if flag is present
if (debugBool === 'true') {
    debug = true;
    document.getElementById("search-debug").style.display = "block";
}

fetch(dataURL)
    .then(response => response.json())
    .then(data => {

        function performSearch() {
            var query = document.getElementById('searchInput').value;

            if (debug) {
                threshold = parseFloat(document.getElementById('threshold').value);
                distance = parseInt(document.getElementById('distance').value, 10);
                mySearchLocation = parseInt(document.getElementById('location').value, 10);
                useExtendedSearch = document.getElementById('useExtendedSearch').checked;
                minMatchCharLength = parseInt(document.getElementById('minMatchCharLength').value, 10);
            }

            if (query !== "") {
                const options = {
                    keys: ['title', 'content', 'keywords'],
                    threshold: 0.4,
                    distance: 1200,
                    location: 0,
                    minMatchCharLength: 4,
                    includeScore: true,
                    includeMatches: true,
                };

                const fuse = new Fuse(data, options);
                const results = fuse.search(query);
                const resultsList = document.getElementById('results');
                resultsList.innerHTML = '';

                let resultCount = 0;

                results.forEach(result => {
                    var title = result.item.title;
                    var content = cleanJSONContent(result.item.content);
                    var link = result.item.link;
                    var breadcrumbs = getBreadcrumbs(result.item.breadcrumbs);
                    var snippet = getSnippet(content, query);
                    
                    if (shouldIncludeResult(result.item.keywords, result.item.link)) {
                        var li = document.createElement('li');
                        li.classList.add('result-item');
                        li.innerHTML = `<a href="..${link}"><h3 class="text">${title}</h3></a>`;
                        
                        if (breadcrumbs) {
                            li.innerHTML += `<ul class="breadcrumbs">${breadcrumbs}</ul>`;
                        }
                        
                        li.innerHTML += `<p>${snippet}</p>`;
                        
                        if (debug) {
                            var score = result.score.toFixed(2);
                            var matches = result.matches;
                            li.innerHTML += `<p class="small">Score: ${score} &nbsp;&nbsp;&nbsp;&nbsp;Matches: ${matches}</p>`;
                        }
                
                        resultsList.appendChild(li);
                        MathJax.typesetPromise([li]).catch(err => console.log('MathJax error:', err));
                        resultCount++;
                    }
                });

                updateResultsCount(resultCount);
                handleSearchFocus(resultCount);
            }
        }

        function getBreadcrumbs(arr)
        {
            var breadcrumbStr = "";

            //last breadcrumb matches title, so exclude
            for(var i=0; i < arr.length-1; i++)
            {
                breadcrumbStr += "<li>" +arr[i]["title"] +"</li>";
            }

            return breadcrumbStr;
        }

        function getSnippet(content, query) {
            const snippetLength = 270; // Desired snippet length
            const halfSnippetLength = snippetLength / 2;
        
            if (!query) return content.substring(0, snippetLength);
        
            const index = content.toLowerCase().indexOf(query.toLowerCase());
        
            if (index !== -1) {
                let snippetStart = Math.max(0, index - halfSnippetLength);
                let snippetEnd = Math.min(content.length, index + halfSnippetLength);
        
                // Adjust snippetStart if the remaining content length is less than snippetLength
                if (snippetEnd - snippetStart < snippetLength) {
                    if (snippetStart === 0) {
                        snippetEnd = Math.min(content.length, snippetStart + snippetLength);
                    } else {
                        snippetStart = Math.max(0, snippetEnd - snippetLength);
                    }
                }
        
                // Expand to nearest whole words
                while (snippetStart > 0 && !/\s/.test(content.charAt(snippetStart - 1))) {
                    snippetStart--;
                }
                while (snippetEnd < content.length && !/\s/.test(content.charAt(snippetEnd))) {
                    snippetEnd++;
                }
        
                // Adjust for unmatched MathJax delimiters
                const openIndex = content.lastIndexOf('\\[', snippetStart);
                const closeIndex = content.indexOf('\\]', snippetEnd);
        
                if (openIndex !== -1 && (closeIndex === -1 || closeIndex > snippetEnd)) {
                    snippetEnd = Math.min(content.length, closeIndex + 2);
                }
        
                // Extract and trim the snippet from content
                let snippet = content.substring(snippetStart, snippetEnd).trim();
        
                // Remove \ce from the snippet
                snippet = snippet.replace(/\\ce/g, '');
        
                // Add ellipses if snippet doesn't start or end at content bounds
                if (snippetStart > 0) {
                    snippet = "&hellip;" + snippet;
                }
                if (snippetEnd < content.length) {
                    snippet += "&hellip;";
                }
        
                // Highlight the query within the snippet
                const escapedQuery = query.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                const regex = new RegExp(`(${escapedQuery})`, 'gi');
                snippet = snippet.replace(regex, '<span class="highlight-1">$1</span>');
                
                return snippet;
            }
        
            // If the query is not found, return the first snippetLength characters as a fallback
            return content.substring(0, snippetLength) + "&hellip;";
        }

        function shouldIncludeResult(keywords, link) {
            const excludeKeywords = ["documentation", "archive", "redirect"];
            const excludePaths = ["/work-in-progress/"];
        
            const excludeByKeyword = !keywords || !keywords.some(keyword =>
                excludeKeywords.includes(keyword.toLowerCase())
            );
        
            const excludeByLink = !excludePaths.some(path => link.includes(path));
        
            return excludeByKeyword && excludeByLink;
        }

        function cleanJSONContent(content) {
            return content
                .replace(/\\\\/g, '\\') // Converts double-escaped backslashes to single
                .replace(/\\r\\n/g, ' ') // Replaces newline with space
                .trim();
        }

        function updateResultsCount(count) {
            const resultsCountDisplay = document.getElementById('results-counter');
            if (count === 0) {
                resultsCountDisplay.textContent = 'No results found.';
            } else {
                resultsCountDisplay.textContent = `${count} result${count > 1 ? 's' : ''} found.`;
            }
        }

        function handleSearchFocus(count) {
            var collapseElement = document.getElementById('results-container');

            if (searchString != null) {
                collapseElement.classList.remove('collapse');
                if (window.history.replaceState) {
                    window.history.replaceState(null, '', window.location.origin + window.location.pathname);
                }
            } else if (count > 0) {
                var collapseInstance = new bootstrap.Collapse(collapseElement, { toggle: false });
                collapseInstance.show();
                document.getElementById("results-title").focus();
            }
        }

        if (searchString != null) {
            document.getElementById('searchInput').value = searchString;
            performSearch();
        }

        document.getElementById('searchButton').addEventListener('click', performSearch);
        document.getElementById('searchInput').addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                performSearch();
            }
        });
    })
    .catch(error => console.error('Error fetching JSON:', error));