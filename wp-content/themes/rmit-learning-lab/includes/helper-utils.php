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