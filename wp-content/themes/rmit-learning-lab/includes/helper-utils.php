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
    return trailingslashit( get_stylesheet_directory_uri() ) . 'js/fuse/fuse.min.js?v='
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
