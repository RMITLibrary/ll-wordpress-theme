<?php

// SEO noindex inheritance functionality loaded

//-----------------------------
// NOINDEX INHERITANCE FOR WORK IN PROGRESS PAGES
//
// Automatically sets noindex for child pages when parent pages have noindex enabled
// This ensures "work in progress" content doesn't appear in search engines
//
// Usage: When a parent page (like "Work in Progress") has noindex enabled in AIOSEO,
//        all child pages will automatically inherit that noindex setting.
//        When pages are moved out of the noindex parent, they become indexable again.
//-----------------------------

/**
 * Dynamically applies noindex to pages based on ancestor noindex settings
 *
 * Instead of trying to read AIOSEO's internal storage, we'll check if ancestor pages
 * would generate noindex robots meta and inherit that setting.
 *
 * @param array $robots The robots meta array from AIOSEO
 * @return array Modified robots meta array
 */
function dynamically_noindex_pages($robots) {
    // Only run on pages, not posts or other content types
    if (!is_page()) {
        return $robots;
    }

    global $post;

    // Safety check - ensure we have a valid post object
    if (!$post || !isset($post->ID)) {
        return $robots;
    }

    // If current page already has noindex, return as-is
    if (!empty($robots['noindex']) && $robots['noindex'] === 'noindex') {
        return $robots;
    }

    // Check ancestor pages for noindex inheritance
    $ancestors = get_post_ancestors($post->ID);

    if (!empty($ancestors)) {
        foreach ($ancestors as $ancestor_id) {
            // Check if this ancestor should have noindex
            $ancestor_robots = check_ancestor_robots_meta($ancestor_id);

            if (!empty($ancestor_robots['noindex']) && $ancestor_robots['noindex'] === 'noindex') {
                // Inherit all robots directives from the ancestor, not just noindex
                foreach ($ancestor_robots as $directive => $value) {
                    if (!empty($value) && $value !== '') {
                        $robots[$directive] = $value;
                    }
                }
                break; // Exit early once we find a noindex ancestor
            }
        }
    }
    return $robots;
}

/**
 * Check what robots meta an ancestor page would generate
 * Simplified approach: check if ancestor is "Work in progress" page or child of it
 */
function check_ancestor_robots_meta($page_id) {
    $ancestor_robots = array(
        'noindex' => '',
        'nofollow' => '',
        'noarchive' => '',
        'nosnippet' => '',
        'noimageindex' => '',
        'noodp' => '',
        'notranslate' => '',
        'max-snippet' => '',
        'max-image-preview' => '',
        'max-video-preview' => '',
    );

    // Get the page title and slug to identify "Work in progress" pages
    $page_title = get_the_title($page_id);
    $page_slug = get_post_field('post_name', $page_id);

    // Only match the exact "Work in progress" page by slug or exact title
    if (
        $page_slug === 'work-in-progress' || $page_slug === 'documentation' ||
        $page_title === 'Work in progress'
    ) {
        // Set all robots directives that should be inherited for work in progress content
        $ancestor_robots['noindex'] = 'noindex';
        $ancestor_robots['nofollow'] = 'nofollow';
        $ancestor_robots['noarchive'] = 'noarchive';
        $ancestor_robots['nosnippet'] = 'nosnippet';
        $ancestor_robots['noimageindex'] = 'noimageindex';
    }

    return $ancestor_robots;
}

// Hook into All in One SEO's robots meta filter
if (function_exists('aioseo') || class_exists('All_in_One_SEO_Pack')) {
    // Try various AIOSEO filter hooks for different versions
    add_filter('aioseop_robots_meta', 'dynamically_noindex_pages', 10, 1);
    add_filter('aioseo_robots_meta', 'dynamically_noindex_pages', 10, 1);
    add_filter('aioseo_robots', 'dynamically_noindex_pages', 10, 1);
}

// Also try WordPress core robots filter as backup
add_filter('wp_robots', 'dynamically_noindex_pages_wp_core', 10, 1);

/**
 * WordPress core robots filter backup
 */
function dynamically_noindex_pages_wp_core($robots) {
    return dynamically_noindex_pages($robots);
}

//-----------------------------
// SITEMAP EXCLUSION FOR WORK IN PROGRESS PAGES
//
// Automatically excludes "work in progress" pages and their children from AIOSEO sitemaps
//-----------------------------

/**
 * Check if a page should be excluded from sitemap based on work-in-progress hierarchy
 */
function is_work_in_progress_page($page_id) {
    // Check if this page itself is "Work in progress"
    $page_title = get_the_title($page_id);
    $page_slug = get_post_field('post_name', $page_id);

    // Only match the exact "Work in progress" page by slug or exact title
    if (
        $page_slug === 'work-in-progress' ||
        $page_title === 'Work in progress'
    ) {
        return true;
    }

    // Check if any ancestor is the exact "Work in progress" page
    $ancestors = get_post_ancestors($page_id);
    if (!empty($ancestors)) {
        foreach ($ancestors as $ancestor_id) {
            $ancestor_title = get_the_title($ancestor_id);
            $ancestor_slug = get_post_field('post_name', $ancestor_id);

            if (
                $ancestor_slug === 'work-in-progress' ||
                $ancestor_title === 'Work in progress'
            ) {
                return true;
            }
        }
    }

    return false;
}


// Hook into WordPress core sitemap filters as backup
add_filter('wp_sitemaps_posts_exclude_post', 'exclude_work_in_progress_from_wp_sitemap', 10, 2);

/**
 * Exclude work-in-progress pages from WordPress core sitemap
 */
function exclude_work_in_progress_from_wp_sitemap($excluded, $post) {
    if (is_work_in_progress_page($post->ID)) {
        return true;
    }
    return $excluded;
}

// Hook into AIOSEO sitemap filters using the correct filter from documentation
if (function_exists('aioseo') || class_exists('All_in_One_SEO_Pack')) {
    // Use the correct AIOSEO filter: aioseo_sitemap_exclude_posts
    add_filter('aioseo_sitemap_exclude_posts', 'exclude_work_in_progress_posts_from_sitemap', 10, 2);
}

/**
 * Exclude work-in-progress posts from AIOSEO sitemap
 * Uses the correct AIOSEO filter: aioseo_sitemap_exclude_posts
 */
function exclude_work_in_progress_posts_from_sitemap($ids, $type) {
    // Get all work-in-progress pages to exclude
    $work_in_progress_page = get_page_by_path('work-in-progress');
    if ($work_in_progress_page) {
        // Add the main work-in-progress page
        $ids[] = $work_in_progress_page->ID;

        // Get all pages to check which ones are descendants
        $all_pages = get_posts(array(
            'post_type' => 'page',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));

        // Check each page to see if it's a descendant of work-in-progress
        foreach ($all_pages as $page_id) {
            if ($page_id !== $work_in_progress_page->ID && is_work_in_progress_page($page_id)) {
                $ids[] = $page_id;
            }
        }
    }

    return $ids;
}