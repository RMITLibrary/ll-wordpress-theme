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
