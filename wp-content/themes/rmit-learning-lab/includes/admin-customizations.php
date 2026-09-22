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
 * "Can I capture right now?" dashboard widget.
 *
 * Two things silently ruin a SiteSucker capture and neither is visible on the
 * dashboard otherwise:
 *
 * - blog_public must be 1 while PRD is captured. ll_restore_blog_public puts it
 *   back to 0 on a schedule, and the theme emits the robots meta per page, so a
 *   capture on the wrong side of that bakes "noindex, nofollow" into every static
 *   page. clean_static_pages.py rewrites robots.txt, never the meta tag.
 * - a dataset older than pages.json means the export ran but one file did not.
 *   That is how the Fuse index sat four days stale while search served raw LaTeX.
 */
add_action('wp_dashboard_setup', function () {
    wp_add_dashboard_widget(
        'rmit_ll_capture_readiness',
        'Static capture readiness',
        'rmit_ll_render_capture_readiness'
    );
});

function rmit_ll_render_capture_readiness() {
    $indexable = '1' === (string) get_option('blog_public');
    $next      = wp_next_scheduled('ll_restore_blog_public');
    $last      = get_option('ll_blog_public_last_reset');
    $format    = get_option('date_format') . ' ' . get_option('time_format');
    $now       = time();

    printf(
        '<p style="margin-top:0;font-size:14px;"><strong style="color:%s;">%s</strong><br><span class="description">%s</span></p>',
        $indexable ? '#007017' : '#b32d2e',
        $indexable ? 'Ready to capture' : 'Not ready to capture',
        $indexable
            ? 'Search engines are allowed, so a capture taken now is publishable.'
            : 'Discourage search engines is on. Every page captured now would carry noindex, nofollow.'
    );

    echo '<p class="description">';
    if ($next) {
        printf(
            'Reset to noindex %s (%s), on the %s schedule.',
            esc_html(wp_date($format, $next)),
            esc_html(human_time_diff($now, $next)) . ' from now',
            esc_html((string) wp_get_schedule('ll_restore_blog_public'))
        );
    } else {
        echo 'No reset is scheduled on this host.';
    }
    if ($last) {
        printf(' Last fired %s.', esc_html(human_time_diff(strtotime($last), $now)) . ' ago');
    }
    echo '</p>';

    if (defined('RMIT_LL_BLOG_PUBLIC_TEST_HOURLY') && RMIT_LL_BLOG_PUBLIC_TEST_HOURLY) {
        echo '<p style="color:#b32d2e;"><strong>Hourly test mode is on</strong> '
            . '<span class="description">— RMIT_LL_BLOG_PUBLIC_TEST_HOURLY in seo-blog-public-reset.php. '
            . 'The capture window is one hour, not a night.</span></p>';
    }

    $tasks    = rmit_ll_export_tasks();
    $statuses = array();

    foreach ($tasks as $key => $task) {
        $meta = 'theme' === $task['location']
            ? rmit_ll_get_theme_export_file_meta($task['path'], $task['label'])
            : rmit_ll_get_export_file_meta($task['filename'], $task['label']);

        $modified = (!is_wp_error($meta) && !empty($meta['modified'])) ? (int) $meta['modified'] : 0;
        $statuses[$key] = array('label' => $task['label'], 'modified' => $modified);
    }

    // Everything else is derived from pages.json, so that is the anchor rather than
    // whichever file happens to be newest: redirects.js is rewritten on admin page
    // loads, and comparing against it marked every real export stale.
    $anchor = isset($statuses['pages']['modified']) ? $statuses['pages']['modified'] : 0;

    echo '<h4 style="margin-bottom:4px;">Export datasets</h4><ul style="margin:0;">';
    foreach ($statuses as $status) {
        if (!$status['modified']) {
            printf(
                '<li>%s — <span style="color:#b32d2e;">never generated</span></li>',
                esc_html($status['label'])
            );
            continue;
        }

        // A minute of slack: the files are written one after another in one request.
        $behind = ($anchor - $status['modified']) > MINUTE_IN_SECONDS;
        printf(
            '<li>%s — %s ago%s</li>',
            esc_html($status['label']),
            esc_html(human_time_diff($status['modified'], $now)),
            $behind ? ' <strong style="color:#b32d2e;">(older than the content dataset — re-run the export)</strong>' : ''
        );
    }
    echo '</ul>';

    printf(
        '<p style="margin-bottom:0;"><a href="%s">Run an export</a></p>',
        esc_url(admin_url('admin.php?page=export-json'))
    );
}

/**
 * Close comments everywhere.
 *
 * The public site is the static export, which has no PHP to accept a comment, so
 * nothing submitted here could ever appear. Leaving the UI in place only invites
 * moderating a queue that cannot exist. Existing comments stay in the database;
 * these filters just stop new ones and hide the entry points.
 */
add_filter('comments_open', '__return_false', 20);
add_filter('pings_open', '__return_false', 20);

add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
});

add_action('wp_before_admin_bar_render', function () {
    global $wp_admin_bar;
    $wp_admin_bar->remove_node('comments');
});

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
            /* At a Glance hardcodes its comment counts, with no filter to drop them. */
            #dashboard_right_now li.comment-count,
            #dashboard_right_now li.comment-mod-count {
                display: none !important;
            }
        </style>';
    }
});
/**
 * Unregister the parent theme's widget areas.
 *
 * The child theme never calls dynamic_sidebar() — sidebar.php builds the page
 * navigation by hand — so every area picostrap registers renders nowhere. Leaving
 * them makes Appearance > Widgets look like a place where changing something has
 * an effect, which is how five stock widgets ended up parked in Main Sidebar.
 *
 * Priority 11 so it runs after picostrap_widgets_init, which is on the default 10.
 * Widgets assigned to an unregistered area are kept by WordPress and reappear if
 * the area ever comes back; nothing is deleted here.
 */
add_action('widgets_init', function () {
    $areas = array(
        'right-sidebar',
        'left-sidebar',
        'hero',
        'herocanvas',
        'statichero',
        'main-sidebar',
        'footerfull',
        'wc-sidebar',
    );

    foreach ($areas as $area) {
        unregister_sidebar($area);
    }
}, 11);
