<?php

//-------------------------------------------
//    write_redirects_js_file

//    Creates and writes a JavaScript file containing URL mappings for redirects
//    Now fetches redirects from the Redirection plugin's database tables

//    Called from:    Redirection plugin hooks (redirection_redirect_updated, redirection_redirect_deleted)

//    Calls:            None directly

//    Usage:            Automatically called by WordPress when redirects are updated
//-------------------------------------------

// Disable WordPress's canonical redirect feature
// This prevents WordPress from automatically redirecting URLs to their canonical versions.
//remove_filter('template_redirect', 'redirect_canonical'); //removed as it was causing the wordpress to break as we only use slugs in some instances, and wordpress was redirecting for us

// Prevent the Redirection plugin from performing any redirects by returning false for the source URL.
// This effectively disables the plugin's redirect functionality for source URLs.
add_filter('redirection_url_source', '__return_false');

// Prevent the Redirection plugin from performing any redirects by returning false for the target URL.
// This effectively disables the plugin's redirect functionality for target URLs.
add_filter('redirection_url_target', '__return_false');

// Disable logging of 404 errors by the Redirection plugin.
// This prevents the plugin from recording 404 errors in its logs.
add_filter('redirection_log_404', function ($log) {
  return false;
}, 1);


function write_redirects_js_file()
{
  global $wpdb;

  error_log('write_redirects_js_file function called!'); // Added logging

  // Query the Redirection plugin's table
  $table_name = $wpdb->prefix . 'redirection_items';
  $redirects = $wpdb->get_results(
    "SELECT url, action_data, regex FROM $table_name WHERE action_type = 'url' AND status = 'enabled'",
    ARRAY_A
  );

  $url_mappings = [];
  if ($redirects) {
    foreach ($redirects as $redirect) {
      if ($redirect['regex'] == 1) {
        $url_mappings[] = [
          'regex' => true,
          'pattern' => $redirect['url'],
          'newPath' => $redirect['action_data'],
        ];
      } else {
        $url_mappings[] = [
          'oldPath' => $redirect['url'],
          'newPath' => $redirect['action_data'],
        ];
      }
    }
  }


  $dir = get_stylesheet_directory() . '/js/';
  $js_file_path = $dir . 'redirects.js'; // Path to save the JS file

  // Check if directory exists and is writable
  if (!is_dir($dir)) {
    error_log('Directory does not exist: ' . esc_html($dir));
    return;
  }

  if (!is_writable($dir)) {
    error_log('Directory is not writable: ' . esc_html($dir));
    return;
  }

  // Prepare JS content with proper JSON escaping for regex patterns
  $js_content = "const urlMappings = " . json_encode($url_mappings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ";\n";
  $js_content .= "console.log('urlMappings.length: ' + urlMappings.length);\n";
  $js_content .= "console.log('Regex patterns found: ' + urlMappings.filter(m => m.regex).length);\n";

  // Write the JavaScript content to the file
  if (file_put_contents($js_file_path, $js_content) === false) {
    error_log('Failed to write JS file to: ' . esc_html($js_file_path));
  } else {
    error_log('JS file successfully written to: ' . esc_html($js_file_path));
  }
}

// Hook into Redirection plugin's actions
add_action('redirection_redirect_updated', 'write_redirects_js_file');
add_action('redirection_redirect_deleted', 'write_redirects_js_file');
add_action('redirection_redirect_created', 'write_redirects_js_file');

// Additional hooks to catch status changes (enabled/disabled)
add_action('redirection_redirect_enabled', 'write_redirects_js_file');
add_action('redirection_redirect_disabled', 'write_redirects_js_file');

// Fallback - regenerate on any redirect table changes
add_action('redirection_flush_cache', 'write_redirects_js_file');

//-------------------------------------------
//    output_redirect_404_script_and_html

//    Outputs JavaScript for URL redirects and the HTML structure for redirect and 404 messages

//    Called from:    404.php and pages using the redirect-404 page template (currently https://lab.bitma.app/redirect-404/ )

//    Calls: createBreadcrumbs (ensure this function exists in your theme)

//    Usage:            Call output_redirect_script_and_html() in template files to display redirect logic and messages
//-------------------------------------------

function output_redirect_404_script_and_html()
{
  // Output the HTML and CSS
?>

  <script src="<?php echo esc_url(get_stylesheet_directory_uri()); ?>/js/redirects.js?v=<?php echo time(); ?>"></script>


  <style>
    #redirect-container {
      display: none;
    }

    #four-oh-container {
      display: none;
    }
  </style>


  <div class="container" id="page-content">
    <div class="row ">
      <!-- START content -->
      <div class="col-xl-8 order-first">
        <?php echo createBreadcrumbs(get_post()); ?>
        <a id="main-content"></a>

        <!-- START redirect container -->
        <div id="redirect-container">
          <h1 class="margin-top-zero">Archived page</h1>
          <p>This content has been updated or relocated. You will be redirected shortly to the latest version. Please update your links if necessary.</p>
        </div>
        <!-- END redirect container -->

        <!-- START 404 container -->
        <div id="four-oh-container">
          <h1 class="margin-top-zero">Page not found</h1>
          <p class="lead">We're sorry, but the page you're looking for doesn't exist. <br />It might have been moved or deleted.</p>

          <p>Use the search bar below to find what you're looking for or <a href="search/#keywords">browse keywords</a> to find related content.</p>

          <!-- START search -->
          <div class="search-container label-side" style="max-width: 592px;">
            <label for="searchInput">
              <h2 class="h4">
                Search <span class="visually-hidden">this website:</span>
              </h2>
            </label>
            <div class="input-group">
              <input type="search" id="searchInput" class="form-control">
              <button type="submit" id="searchButton" class="btn btn-primary">
                <div class="mag-glass"></div><span class="visually-hidden">Search</span>
              </button>
            </div>
          </div>
          <!-- END search -->
        </div>
        <!-- END 404 container -->
      </div>
      <!-- END content -->
    </div>
  </div>

  <script>
 // References to DOM objects
    const fourOhInfo = document.getElementById('four-oh-container');
    const redirectInfo = document.getElementById('redirect-container');

    // Prefix for the environment; set to '' for live and '/preview' for test
    const pathPrefix = '';

    // Function to extract the path after the domain and remove the prefix if present
    function extractPath(url) {
      const urlObj = new URL(url);
      let path = urlObj.pathname;

      // Remove the path prefix if present
      if (path.startsWith(pathPrefix)) {
        path = path.substring(pathPrefix.length);
      }

      return path; // Do NOT remove leading or trailing slashes
    }

    // Function to normalise a path by trimming leading and trailing slashes
    function normalizePath(path) {
      if (typeof path === 'string') {
        return path.replace(/^\/|\/$/g, '');
      } else {
        console.error('normalizePath was called with a non-string value:', path);
        return ''; // Or handle it in another way, like returning null or undefined
      }
    }

    // Function to replace the old URL path with the new URL path, adding the prefix
    function replaceUrlPath(url, newPath) {
      const urlObj = new URL(url);
      urlObj.pathname = pathPrefix + newPath;
      return urlObj.toString();
    }

    // Function to normalize path by removing index.html and .html
    function normalizeIndexPath(path) {
      console.log('normalizeIndexPath input:', path);
      if (typeof path !== 'string') return '';
      let p = path;
      // Remove trailing '/index.html'
      if (p.endsWith('/index.html')) {
        p = p.slice(0, -10);
      } else if (p.endsWith('index.html')) {
        // Handle 'index.html' without a preceding slash
        p = p.slice(0, -10) || '/';
      } else if (p.endsWith('.html')) {
        // Remove '.html'
        p = p.slice(0, -5);
      }
      // Remove a trailing slash if not root
      if (p.length > 1 && p.endsWith('/')) {
        p = p.slice(0, -1);
      }
      if (!p.startsWith('/')) p = '/' + p;
      console.log('normalizeIndexPath result:', p);
      return p;
    }

    // Database-backed URL validation and redirect guard helpers
    const PAGES_URLS_JSON_URL = '/wp-content/uploads/pages-urls.json'; // preferred (array of paths)
    const PAGES_JSON_URL = '/wp-content/uploads/pages.json'; // fallback (array of objects with link)
    let VALID_PATHS = null; // Set of normalized valid paths

    function standardizePathForDb(path) {
      if (typeof path !== 'string') return '';
      let p = path;
      // Strip environment prefix
      if (p.startsWith(pathPrefix)) p = p.substring(pathPrefix.length);
      p = normalizeIndexPath(p);
      // Ensure canonical no-trailing-slash except root
      if (p.length > 1 && p.endsWith('/')) p = p.slice(0, -1);
      if (!p.startsWith('/')) p = '/' + p;
      return p;
    }

    function extractPathFromLink(link) {
      try {
        const u = new URL(link, window.location.origin);
        return standardizePathForDb(u.pathname);
      } catch (e) {
        return '';
      }
    }

    async function loadValidPaths() {
      if (VALID_PATHS) return VALID_PATHS;
      try {
        // Try the lightweight pages-urls.json first
        let res = await fetch(PAGES_URLS_JSON_URL, { cache: 'force-cache' });
        let data;
        if (res.ok) {
          data = await res.json();
        } else {
          // Fallback to legacy pages.json
          res = await fetch(PAGES_JSON_URL, { cache: 'force-cache' });
          data = await res.json();
        }
        const set = new Set();
        if (Array.isArray(data)) {
          data.forEach(item => {
            // Support both formats: strings (paths) and objects (with link/url/permalink)
            let p = '';
            if (typeof item === 'string') {
              p = standardizePathForDb(item);
            } else {
              const link = item && (item.link || item.url || item.permalink);
              p = extractPathFromLink(link);
            }
            if (p) {
              set.add(p);
              if (p !== '/' && !p.endsWith('/')) set.add(p + '/');
            }
          });
        }
        VALID_PATHS = set;
        console.log('Loaded valid paths count:', set.size);
        return set;
      } catch (e) {
        console.warn('Failed to load pages.json; redirects will be suppressed.', e);
        VALID_PATHS = new Set();
        return VALID_PATHS;
      }
    }

    function isUrlValidInDb(url) {
      try {
        const u = new URL(url, window.location.origin);
        // Allow external URLs (cannot validate client-side)
        if (u.origin !== window.location.origin) return true;
        const p = standardizePathForDb(u.pathname);
        return VALID_PATHS && (VALID_PATHS.has(p) || VALID_PATHS.has(p + '/'));
      } catch (e) {
        return false;
      }
    }

    // Function to perform the redirect after all checks and URL processing
    function doRedirect(redirectUrl, delay = 1000) {
      // Add meta refresh for better SEO
      const metaRefresh = document.createElement('meta');
      metaRefresh.httpEquiv = 'refresh';
      metaRefresh.content = '0;url=' + redirectUrl;
      document.head.appendChild(metaRefresh);

      // Add canonical link for SEO
      const canonicalLink = document.createElement('link');
      canonicalLink.rel = 'canonical';
      canonicalLink.href = redirectUrl;
      document.head.appendChild(canonicalLink);

      // Perform immediate redirect for better UX
      setTimeout(() => {
        console.log('Redirecting to: ' + redirectUrl);
        window.location.replace(redirectUrl);
      }, delay);
    }

    // Ensure URL has a trailing slash if necessary
    function ensureTrailingSlash() {
      const myPath = window.location.pathname;
      if (!myPath.endsWith('/') && !myPath.endsWith('.html')) {
        const newPath = myPath + '/';
        window.location.replace(newPath + window.location.search + window.location.hash);
      }
    }

    // Main execution
    async function main() {
      // Ensure the current URL has a trailing slash if no .html is present
      //ensureTrailingSlash();

      // Get the current URL
      const currentURL = window.location.href;

      // Extract the path from the current URL
      const extractedPath = extractPath(currentURL);

      // Normalize the path by removing index.html
      let normalizedPath = normalizeIndexPath(extractedPath);

      // Debugging log to check the extracted and normalized paths
      console.log('Extracted Path: ' + extractedPath);
      console.log('Extracted Path length: ' + extractedPath.length);
      console.log('Normalized Path: ' + normalizedPath);
      console.log('Normalized Path length: ' + normalizedPath.length);

      // Check for redirect mappings
      {
        // Check if urlMappings is defined before using it.
        if (typeof urlMappings !== 'undefined') {
          // Function to search for a mapping with a given path
          function findMapping(searchPath) {
            console.log('Searching for mapping with path:', searchPath);

            // First, try to find an exact match (non-regex)
            let mapping = urlMappings.find((mapping) => !mapping.regex && normalizePath(mapping.oldPath) === normalizePath(searchPath));

            // If no exact match, then try regex matching
            if (!mapping) {
              mapping = urlMappings.find((mapping) => {
                if (mapping.regex) {
                  try {
                    // Properly escape the regex pattern for JavaScript
                    const regex = new RegExp(mapping.pattern, 'i');
                    console.log('Testing regex pattern:', mapping.pattern, 'against:', searchPath);
                    return regex.test(searchPath);
                  } catch (e) {
                    console.error('Invalid regex pattern:', mapping.pattern, e);
                    return false; // Skip this mapping if the regex is invalid
                  }
                }
                return false;
              });
            }

            return mapping;
          }

          // First, try to find a mapping with the ORIGINAL extracted path (before normalization)
          let mapping = findMapping(extractedPath);
          let matchedPath = extractedPath;

          // If no mapping found, try the normalized path
          if (!mapping) {
            mapping = findMapping(normalizedPath);
            matchedPath = normalizedPath;
          }

          // If no mapping found and path contains '/content/', try replacing it with '/'
          if (!mapping && normalizedPath.includes('/content/')) {
            console.log('No direct match found, trying with /content/ replaced by /');
            const pathWithoutContent = normalizedPath.replace('/content/', '/');
            mapping = findMapping(pathWithoutContent);

            // If we found a match with the content-stripped path, update the search path for redirect processing
            if (mapping) {
              console.log('Found match after replacing /content/ with /');
              // Use the path without content for the redirect processing
              normalizedPath = pathWithoutContent;
              matchedPath = pathWithoutContent;
            }
          }

          // If no mapping found but URL was normalized, try redirecting to normalized URL if it exists in DB
          if (!mapping && normalizedPath !== extractedPath) {
            console.log('No mapping found, considering normalized URL:', normalizedPath);
            const normalizedUrl = window.location.origin + normalizedPath + window.location.search + window.location.hash;
            // Load DB only when needed
            await loadValidPaths();
            if (isUrlValidInDb(normalizedUrl)) {
              // Change page title to reflect change
              document.title = 'Redirecting you to the correct page...';
              // Display redirect information
              redirectInfo.style.display = 'block';
              // Perform the redirect
              doRedirect(normalizedUrl);
              return; // Exit early to prevent immediate 404 display
            } else {
              console.log('Normalized URL not in database, skipping redirect:', normalizedPath);
            }
          }

          // If no mapping found but we have content/ in the original path, try content-stripped URL if it exists in DB
          if (!mapping && extractedPath.includes('/content/')) {
            const pathWithoutContent = extractedPath.replace('/content/', '/');
            if (pathWithoutContent !== extractedPath) {
              console.log('No mapping found, considering content-stripped URL:', pathWithoutContent);
              const contentStrippedUrl = window.location.origin + pathWithoutContent + window.location.search + window.location.hash;
              // Load DB only when needed
              await loadValidPaths();
              if (isUrlValidInDb(contentStrippedUrl)) {
                // Change page title to reflect change
                document.title = 'Redirecting you to the correct page...';
                // Display redirect information
                redirectInfo.style.display = 'block';
                // Perform the redirect
                doRedirect(contentStrippedUrl);
                return; // Exit early to prevent immediate 404 display
              } else {
                console.log('Content-stripped URL not in database, skipping redirect:', pathWithoutContent);
              }
            }
          }

          // If a matching mapping is found, redirect to the new URL
          if (mapping) {
            let newUrl;
            if (mapping.regex) {
              try {
                // Use the same regex pattern for matching and replacement
                const regex = new RegExp(mapping.pattern, 'i');

                // Use the path that was used to find the mapping
                const matches = matchedPath.match(regex);
                console.log('Regex match attempt - Pattern:', mapping.pattern, 'Matched path:', matchedPath, 'Matches:', matches);

                if (matches) {
                  // If there are matches, do the replacement using captured groups
                  newUrl = mapping.newPath.replace(/\$(\d+)/g, (_, groupIndex) => {
                    const matchIndex = parseInt(groupIndex);
                    const replacement = matches[matchIndex] || '';
                    console.log('Replacing $' + groupIndex + ' with:', replacement);
                    return replacement;
                  });
                  console.log('Regex replacement result:', newUrl);
                } else {
                  // If no matches, use the new path as is
                  newUrl = mapping.newPath;
                }

                // Preserve query parameters and hash for regex redirects
                const urlObj = new URL(currentURL);
                const finalUrlObj = new URL(newUrl, urlObj.origin);
                if (urlObj.search) {
                  finalUrlObj.search = urlObj.search;
                }
                if (urlObj.hash) {
                  finalUrlObj.hash = urlObj.hash;
                }
                newUrl = finalUrlObj.toString();
              } catch (e) {
                console.error('Error during regex replacement:', e);
                newUrl = mapping.newPath;
              }
            } else {
              // Non-regex match, check if newPath is external URL or relative path
              if (mapping.newPath.startsWith('http://') || mapping.newPath.startsWith('https://')) {
                // External URL - use as is
                newUrl = mapping.newPath;
              } else {
                // Relative path - use replaceUrlPath
                newUrl = replaceUrlPath(currentURL, mapping.newPath);
              }
            }
            console.log('Match found! Redirecting to: ' + newUrl);

            //Change page title to relect change
            document.title = 'Redirecting you to the new page...';

            // Display redirect information
            redirectInfo.style.display = 'block';

            // Perform the redirect
            doRedirect(newUrl);
          } else {
            // If no mapping is found, display 404 information
            console.log('No match found. Displaying 404 info.');
            fourOhInfo.style.display = 'block';
          }
        } else {
          // urlMappings is not defined, display 404 information
          console.log('urlMappings is not defined. Displaying 404 info.');
          fourOhInfo.style.display = 'block';
        }
      }
    }

    main();
  </script>
  <!-- script to punt search input to /search via query string -->
  <script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/js/search-home.js?v=1.0.1"></script>

<?php
}
