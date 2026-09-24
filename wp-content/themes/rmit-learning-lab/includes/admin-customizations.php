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
 * Put author documentation and its main sections last in the admin sidebar.
 */
add_action('admin_menu', function () {
    $documentation = get_page_by_path('documentation');

    if (!$documentation || 'publish' !== $documentation->post_status) {
        return;
    }

    $documentation_url = get_permalink($documentation);
    $hook = add_menu_page(
        'Documentation',
        'Documentation',
        'edit_pages',
        'rmit-ll-documentation',
        '__return_null',
        'dashicons-book-alt',
        9999
    );

    add_action('load-' . $hook, function () use ($documentation_url) {
        wp_safe_redirect($documentation_url);
        exit;
    });

    $sections = get_pages(array(
        'parent'      => $documentation->ID,
        'post_status' => 'publish',
        'sort_column' => 'menu_order,post_title',
    ));

    foreach ($sections as $section) {
        $section_url = get_permalink($section);
        $section_hook = add_submenu_page(
            'rmit-ll-documentation',
            wp_strip_all_tags($section->post_title),
            wp_strip_all_tags($section->post_title),
            'edit_pages',
            'rmit-ll-documentation-' . $section->ID,
            '__return_null'
        );

        add_action('load-' . $section_hook, function () use ($section_url) {
            wp_safe_redirect($section_url);
            exit;
        });
    }
}, 9999);

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
 * Keep the AIOSEO Settings metabox at the bottom of the editor.
 *
 * It registers in the 'normal' context at 'high' priority, so out of the box it sits
 * above the fields the page is actually about.
 *
 * Two mechanisms decide where a box lands, and both have to be answered:
 *
 * - With no saved box order, registration priority decides. The plugin's own filter
 *   covers that.
 * - Once anyone has dragged a box, WordPress stores meta-box-order_<screen> per user,
 *   and do_meta_boxes() re-adds every id it names into its saved context at priority
 *   'sorted' — at render time, which beats anything done on add_meta_boxes. Four
 *   editors here have aioseo-settings saved first. Rewriting that order is the only
 *   thing it respects.
 *
 * The saved order sends it to 'advanced' rather than the end of 'normal', because
 * WordPress appends boxes the order does not name after the ones it does, and that
 * container always renders after 'normal'.
 */
add_filter('aioseo_post_metabox_priority', function () {
    return 'low';
});

/**
 * Disable AIOSEO Writing Assistant before it can register its metabox or assets.
 */
add_action('after_setup_theme', function () {
    global $wp_filter;

    if (empty($wp_filter['add_meta_boxes']->callbacks)) {
        return;
    }

    foreach ($wp_filter['add_meta_boxes']->callbacks as $priority => $callbacks) {
        foreach ($callbacks as $callback) {
            $function = $callback['function'];

            if (
                is_array($function) &&
                is_object($function[0]) &&
                'AIOSEO\\Plugin\\Common\\Admin\\WritingAssistant' === get_class($function[0])
            ) {
                remove_action('add_meta_boxes', $function, $priority);
            }
        }
    }
});

add_action('current_screen', function ($screen) {
    if ('post' !== $screen->base) {
        return;
    }

    if (function_exists('aioseo') && isset(aioseo()->admin)) {
        remove_action('post_submitbox_misc_actions', array(aioseo()->admin, 'addPublishScore'));
    }

    // Registered per screen id so every post type is covered without listing them.
    add_filter('get_user_option_meta-box-order_' . $screen->id, 'rmit_ll_aioseo_metabox_last');

    if ('page' === $screen->id) {
        add_filter('get_user_option_closedpostboxes_page', 'rmit_ll_metaboxes_closed_by_default');
    }
});

function rmit_ll_metaboxes_closed_by_default($closed)
{
    $user_id  = get_current_user_id();
    $defaults = array('aioseo-settings', 'revisionsdiv', 'acf-group_668dedab7afe4');
    $migrated = 'rmit_ll_page_metabox_defaults_20260922';

    if (!$user_id) {
        return false === $closed ? $defaults : $closed;
    }

    if (get_user_meta($user_id, $migrated, true)) {
        return $closed;
    }

    $closed = array_values(array_unique(array_merge(is_array($closed) ? $closed : array(), $defaults)));

    update_user_option($user_id, 'closedpostboxes_page', $closed, true);
    update_user_meta($user_id, $migrated, 1);

    return $closed;
}

function rmit_ll_aioseo_metabox_last($order)
{
    if (!is_array($order)) {
        return $order;
    }

    foreach (array('normal', 'side', 'advanced') as $context) {
        if (empty($order[$context])) {
            continue;
        }

        $ids = array_filter(explode(',', $order[$context]));
        $order[$context] = implode(',', array_diff($ids, array('aioseo-settings')));
    }

    $advanced   = array_filter(explode(',', isset($order['advanced']) ? $order['advanced'] : ''));
    $advanced[] = 'aioseo-settings';

    $order['advanced'] = implode(',', $advanced);

    return $order;
}

/**
 * The ACF Keywords field is the single editor for keyword terms.
 */
add_action('add_meta_boxes_page', function () {
    remove_meta_box('tagsdiv-keyword', 'page', 'side');
}, 100);

/**
 * Subject areas: kept, not shown.
 *
 * 547 pages carry subject-area terms but nothing on the site displays them — the only
 * reader fetched the field and discarded it. The terms stay attached so the work isn't
 * lost; the editor field, the Pages > Subjects screen and the missing-terms warning go.
 * ACF leaves a field alone on save when it isn't rendered, so saving a page keeps its
 * terms. To bring it back, delete these two filters.
 */
add_filter('acf/prepare_field/key=field_65275ce3c7e36', '__return_false');

add_filter('register_taxonomy_args', function ($args, $taxonomy) {
    if ('subject-area' === $taxonomy) {
        $args['show_ui']            = false;
        $args['show_in_menu']       = false;
        $args['show_in_nav_menus']  = false;
        $args['show_in_quick_edit'] = false;
        $args['show_admin_column']  = false;
    }
    return $args;
}, 10, 2);

/**
 * Surface publishing details that are otherwise easy to miss in the editor.
 */
add_action('post_submitbox_misc_actions', function ($post) {
    if (!$post || 'page' !== $post->post_type) {
        return;
    }

    $work_in_progress = get_page_by_path('work-in-progress');
    if (
        $work_in_progress &&
        ($post->ID === $work_in_progress->ID || in_array($work_in_progress->ID, get_post_ancestors($post), true))
    ) {
        ?>
        <div class="misc-pub-section rmit-ll-wip-notice">
            <span class="dashicons dashicons-warning" aria-hidden="true"></span>
            <span>
                <strong><?php esc_html_e('Work in progress', 'rmit-learning-lab'); ?></strong>
                <?php esc_html_e('This page sits inside the Work in progress section.', 'rmit-learning-lab'); ?>
            </span>
        </div>
        <?php
    }

    $taxonomy_group_visible = function_exists('acf_get_field_groups') && array_filter(
        acf_get_field_groups(array('post_id' => $post->ID, 'post_type' => 'page')),
        function ($group) {
            return 'group_6527440974679' === $group['key'];
        }
    );

    if ($taxonomy_group_visible) {
        $missing = array();

        if (!has_term('', 'keyword', $post)) {
            $missing[] = __('Keywords', 'rmit-learning-lab');
        }

        if ($missing) {
            ?>
            <div class="misc-pub-section rmit-ll-taxonomy-notice">
                <span class="dashicons dashicons-warning" aria-hidden="true"></span>
                <span>
                    <strong><?php esc_html_e('Taxonomy details missing', 'rmit-learning-lab'); ?></strong>
                    <?php
                    printf(
                        esc_html__('Add %s in the Taxonomy panel.', 'rmit-learning-lab'),
                        esc_html(implode(__(' and ', 'rmit-learning-lab'), $missing))
                    );
                    ?>
                </span>
            </div>
            <?php
        }
    }

    if (!preg_match('/<!--(?!\s*(?:\/?wp:|more\b|nextpage\b))[\s\S]*?-->/', $post->post_content)) {
        return;
    }
    ?>
    <div class="misc-pub-section rmit-ll-comment-notice">
        <span class="dashicons dashicons-editor-code" aria-hidden="true"></span>
        <span>
            <strong><?php esc_html_e('HTML comments in content', 'rmit-learning-lab'); ?></strong>
            <?php esc_html_e('Hidden on the page, but still included in its HTML source. Remove them in the Text editor if they are no longer needed.', 'rmit-learning-lab'); ?>
        </span>
    </div>
    <?php
});

add_action('admin_head-post.php', function () {
    ?>
    <style>
        .rmit-ll-wip-notice,
        .rmit-ll-taxonomy-notice,
        .rmit-ll-comment-notice {
            align-items: flex-start;
            display: flex;
            gap: 8px;
            line-height: 1.4;
            padding-bottom: 10px;
            padding-top: 10px;
        }

        .rmit-ll-wip-notice {
            background: #000054;
            color: #fff;
        }

        .rmit-ll-taxonomy-notice,
        .rmit-ll-comment-notice {
            background: #fff3cd;
            color: #664d03;
        }

        .rmit-ll-wip-notice .dashicons,
        .rmit-ll-taxonomy-notice .dashicons,
        .rmit-ll-comment-notice .dashicons {
            flex: 0 0 20px;
            margin-top: 1px;
        }

        .rmit-ll-taxonomy-notice .dashicons,
        .rmit-ll-taxonomy-notice strong,
        .rmit-ll-comment-notice .dashicons,
        .rmit-ll-comment-notice strong {
            color: #664d03;
        }

        .rmit-ll-wip-notice strong,
        .rmit-ll-taxonomy-notice strong,
        .rmit-ll-comment-notice strong {
            display: block;
        }
    </style>
    <?php
});

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

add_action('admin_init', function () {
    foreach (get_post_types() as $post_type) {
        remove_post_type_support($post_type, 'comments');
        remove_post_type_support($post_type, 'trackbacks');
    }
});

add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
});

add_action('wp_before_admin_bar_render', function () {
    global $wp_admin_bar;
    $wp_admin_bar->remove_node('comments');
});

/**
 * Remove the dashboard widgets nobody here uses.
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

    // AIOSEO registers aioseo-seo-setup, aioseo-seo-checklist, aioseo-overview and
    // aioseo-rss-feed, all in the 'normal' context and all conditional, so naming them
    // individually is what went wrong before: the ids were guessed, four were given the
    // 'side' context they never use, and two did not exist. Matching on the prefix
    // removes whichever ones the plugin decided to register today.
    global $wp_meta_boxes;
    if (isset($wp_meta_boxes['dashboard'])) {
        foreach (array('normal', 'side', 'advanced') as $context) {
            if (!isset($wp_meta_boxes['dashboard'][$context])) {
                continue;
            }

            foreach ($wp_meta_boxes['dashboard'][$context] as $widgets) {
                foreach (array_keys($widgets) as $widget_id) {
                    if (strpos($widget_id, 'aioseo') === 0) {
                        remove_meta_box($widget_id, 'dashboard', $context);
                    }
                }
            }
        }
    }
}, 999);

add_action('admin_head', function() {
    if (get_current_screen()->base === 'dashboard') {
        echo '<style>
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

/**
 * Hide picostrap's "Page with Sidebar on the Right" from the Template dropdown.
 *
 * It comes from the parent theme, no page uses it, and it lays a page out differently
 * from every template here, so offering it only invites a page that looks wrong.
 */
add_filter('theme_page_templates', function ($templates) {
    unset($templates['page-templates/page-sidebar-right.php']);
    return $templates;
});
