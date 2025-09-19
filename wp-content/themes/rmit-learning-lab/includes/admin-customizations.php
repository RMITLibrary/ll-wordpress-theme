<?php
/**
 * Admin Customizations
 *
 * Handles WordPress admin area modifications including menu items,
 * dashboard widgets, and admin bar customizations.
 *
 * @package RMIT_Learning_Lab
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Remove AIOSEO Redirects from Tools Menu
 * Using admin_menu hook with very late priority to ensure it runs after plugin menus are added
 */
add_action('admin_menu', function() {
    remove_submenu_page('tools.php', 'aioseo-redirects');
}, 9999);

/**
 * Add Redirection Link to Main Menu (without moving original)
 */
add_action('admin_menu', function() {
    add_menu_page(
        'Redirections',                    // Page title
        'Redirections',                    // Menu title
        'manage_options',                  // Capability
        'tools.php?page=redirection.php',  // Menu slug - link to existing page
        '',                                // Function (empty - just a link)
        'dashicons-admin-links',           // Icon
        25                                 // Position (above Tools)
    );
}, 9999);

/**
 * Remove Unused Menu Items
 */
add_action('admin_menu', function() {
    remove_menu_page('edit.php');           // Posts
    remove_menu_page('edit-comments.php');  // Comments
}, 9999);

/**
 * Disable Picostrap SASS Recompile Menu
 */
add_action('admin_bar_menu', function() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_node('ps-recompile-sass-backend');
    $wp_admin_bar->remove_node('ps-recompile-sass');
}, 999);

/**
 * Remove Default Dashboard Widgets - Multiple Approaches for Reliability
 */
add_action('wp_dashboard_setup', function() {
    // Remove WordPress default widgets
    remove_meta_box('dashboard_primary', 'dashboard', 'side');        // WordPress Events and News
    remove_meta_box('dashboard_secondary', 'dashboard', 'side');      // Other WordPress News
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');    // Quick Draft
    remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');  // Recent Drafts
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal'); // Recent Comments
    remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal'); // Incoming Links
    remove_meta_box('dashboard_plugins', 'dashboard', 'normal');      // Plugins
    remove_meta_box('dashboard_activity', 'dashboard', 'normal');     // Activity

    // Remove AIOSEO widgets - try all possible IDs
    remove_meta_box('aioseo-overview', 'dashboard', 'normal');        // AIOSEO Overview
    remove_meta_box('aioseo-seo-news', 'dashboard', 'side');          // AIOSEO SEO News
    remove_meta_box('aioseo-rss-feed', 'dashboard', 'side');          // AIOSEO RSS Feed
    remove_meta_box('aioseo_rss_feed', 'dashboard', 'side');          // Alternative ID
    remove_meta_box('aioseo-news', 'dashboard', 'side');              // Alternative ID

    // Remove any widget with 'aioseo' in the ID
    global $wp_meta_boxes;
    if (isset($wp_meta_boxes['dashboard'])) {
        foreach (['normal', 'side'] as $context) {
            if (isset($wp_meta_boxes['dashboard'][$context])) {
                foreach ($wp_meta_boxes['dashboard'][$context] as $priority => $widgets) {
                    foreach ($widgets as $widget_id => $widget) {
                        if (strpos($widget_id, 'aioseo') !== false) {
                            remove_meta_box($widget_id, 'dashboard', $context);
                        }
                    }
                }
            }
        }
    }
}, 999);

/**
 * Additional Removal Attempt with Different Hook
 */
add_action('admin_init', function() {
    remove_meta_box('aioseo-rss-feed', 'dashboard', 'side');
    remove_meta_box('aioseo_rss_feed', 'dashboard', 'side');
}, 9999);

/**
 * CSS Approach as Fallback for Widget Removal
 */
add_action('admin_head', function() {
    if (get_current_screen()->base === 'dashboard') {
        echo '<style>
            #aioseo-rss-feed,
            #aioseo_rss_feed,
            .postbox[id*="aioseo"][id*="rss"],
            .postbox[id*="aioseo"][id*="feed"] {
                display: none !important;
            }
        </style>';
    }
});