<?php
/**
 * Helper Utilities
 *
 * Common utility functions used throughout the theme.
 *
 * @package RMIT_Learning_Lab
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Normalise a shortcode size value.
 *
 * Content writers reach for words like "med" or "large", so accept those
 * alongside the documented sm/md/lg values. Anything else is left alone.
 *
 * @param string $size Size value from a shortcode attribute.
 * @return string Canonical size value.
 */
function ll_normalise_size($size) {
    $synonyms = array(
        'med'    => 'md',
        'medium' => 'md',
        'small'  => 'sm',
        'large'  => 'lg',
    );

    $size = strtolower(trim((string) $size));

    return isset($synonyms[$size]) ? $synonyms[$size] : $size;
}

/**
 * Format string after the colon
 *
 * Formats string - capitalizes string section after 1st colon.
 * E.g. "Artists statement: writing Process" becomes "Writing process"
 *
 * @param string $string The string to format
 * @return string Formatted string
 */
function formatAfterTheColon($string) {
    // Split the string at colon
    $parts = explode(':', $string, 2); // Limit to 2 parts to handle colons within the string correctly

    if (count($parts) === 2) {
        // Capitalise the first character of the second part
        $parts[1] = ucfirst(trim($parts[1]));
        return $parts[1];
    } else {
        // Handle cases where there might not be a colon
        return $string;
    }
}

/**
 * Get child pages list
 *
 * Creates a list of child pages: links wrapped in list items
 *
 * @param int $pageId Id of the page to get children of
 * @return string HTML list of child pages
 */
function doChildrenList($pageId) {
    return wp_list_pages(
        array(
            'child_of' => $pageId,
            'depth' => 1,
            'title_li' => null,
            'echo' => false
        )
    );
}

/**
 * URL of the vendored Fuse.js.
 *
 * Self-hosted rather than loaded from jsDelivr: search is the one feature that
 * breaks completely without it, and the static export cannot help with a script
 * fetched at runtime from a third party. Pinned in package.json, copied by
 * `npm run fuse:vendor`, checked by the deploy workflow.
 *
 * Both the search page and the index builder read this, so they cannot end up on
 * different versions — an index built by one is read by the other.
 */
function rmit_ll_fuse_url() {
    // Root-relative, not absolute: the static capture rewrites hosts, and an absolute
    // URL sent Fuse requests to the public domain, where the file 404s. Same fault as
    // MathJax's font path in 2.0.54.
    return wp_make_link_relative( trailingslashit( get_stylesheet_directory_uri() ) ) . 'js/fuse/fuse.min.js?v='
        . rmit_learning_lab_asset_version( 'js/fuse/fuse.min.js' );
}

/**
 * Pages and keywords that never belong in search.
 *
 * One definition for both ends: page-search.php filters the keyword list with it and
 * hands it to search.js, which filters the results. It used to live in four places with
 * three different rules — the browse list checked only "Archive" when deciding whether a
 * keyword had any qualifying pages, while the results also dropped "redirect".
 */
function rmit_ll_search_exclusions() {
    return array(
        'keywords' => array( 'documentation', 'archive', 'redirect' ),
        'paths'    => array( '/work-in-progress/', '/documentation/' ),
    );
}

/**
 * True when a term name is one of the excluded keywords.
 */
function rmit_ll_is_excluded_keyword( $name ) {
    $excluded = rmit_ll_search_exclusions()['keywords'];

    return in_array( strtolower( (string) $name ), $excluded, true );
}

/**
 * True when a URL or path sits under an excluded section.
 */
function rmit_ll_is_excluded_path( $url ) {
    foreach ( rmit_ll_search_exclusions()['paths'] as $path ) {
        if ( stripos( (string) $url, $path ) !== false ) {
            return true;
        }
    }

    return false;
}

/**
 * Search synonyms, edited by content editors at Pages > Search synonyms.
 *
 * Stored as plain text, one "what people type = what the site calls it" per line. A
 * multi-word left side replaces that phrase in the search; a single word adds the
 * targets to it. Each comma-separated target is searched whole, phrases included. Until someone saves the screen, the list below is used, so
 * it doubles as the starting content of the box. search.js reads the parsed result
 * from window.LL_SEARCH_SYNONYMS, which page-search.php prints — so on the static site
 * a change reaches search at the next export.
 */
function rmit_ll_default_search_synonyms() {
    return implode("\n", array(
        'sig figs = significant figures',
        'group assignment = group work',
        'lit review = literature review',
        'reference list = referencing',
        'reading list = referencing',
        'harvard = referencing, cite',
        'apa = referencing, cite',
        'vancouver = referencing, cite',
        'ieee = referencing, cite',
        'chicago = referencing, cite',
        'aglc = referencing, legal',
        'aglc4 = referencing, legal',
        'footnote = citation',
        'footnotes = citation',
        'endnote = referencing, cite',
        'zotero = referencing, cite',
        'mendeley = referencing, cite',
        'powerpoint = presentation',
        'slides = presentation',
        'slideshow = presentation',
        'stats = statistics',
        'sigfigs = significant figures',
        'ai = artificial intelligence',
        'chatgpt = artificial intelligence',
        'copilot = artificial intelligence',
    ));
}

function rmit_ll_search_synonyms_text() {
    return get_option('rmit_ll_search_synonyms', rmit_ll_default_search_synonyms());
}

/**
 * The stored text as editor rows: array( array( 'from' => ..., 'to' => ... ), ... ).
 * Lines missing either side come back with the empty side, so the screen can flag them.
 */
function rmit_ll_search_synonym_rows($text) {
    $rows = array();
    foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
        $line = trim($line);
        if ('' === $line || 0 === strpos($line, '#')) {
            continue;
        }
        $parts  = preg_split('/\s*=>?\s*/', $line, 2);
        $rows[] = array(
            'from' => strtolower(trim(preg_replace('/\s+/', ' ', $parts[0] ?? ''))),
            'to'   => implode(', ', array_filter(array_map('trim', explode(',', strtolower($parts[1] ?? ''))))),
        );
    }
    return $rows;
}

/**
 * Parse the text into what search.js needs, plus the rows it could not use.
 */
function rmit_ll_parse_search_synonyms($text) {
    $out = array('phrases' => array(), 'words' => array(), 'rejected' => array());

    foreach (rmit_ll_search_synonym_rows($text) as $row) {
        $from = $row['from'];
        $to   = array_values(array_filter(array_map('trim', explode(',', $row['to']))));
        if ('' === $from || !$to) {
            $out['rejected'][] = $row;
            continue;
        }

        // Targets stay as entered, so a multi-word one is searched as a phrase:
        // "ai = artificial intelligence" must not match every page that says
        // "intelligence". A multi-word left side is taken out of the query and
        // replaced by the targets; a single word keeps itself and adds them.
        if (false !== strpos($from, ' ')) {
            $out['phrases'][$from] = $to;
        } else {
            $out['words'][$from] = $to;
        }
    }

    return $out;
}
