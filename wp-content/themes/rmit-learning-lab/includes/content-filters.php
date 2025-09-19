<?php
/**
 * Content Filters and Query Modifications
 *
 * Functions that modify WordPress content output and queries.
 *
 * @package RMIT_Learning_Lab
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Include custom post types in search results
 *
 * This function modifies the main WordPress query to include an array of
 * post types instead of the default 'post' post type.
 *
 * @param WP_Query $query The main WordPress query
 */
function rmit_ll_include_custom_post_types_in_search_results($query) {
    if ($query->is_main_query() && $query->is_search() && !is_admin()) {
        $query->set('post_type', array('post', 'page'));
    }
}
add_action('pre_get_posts', 'rmit_ll_include_custom_post_types_in_search_results');

/**
 * Prepend slash to relative URLs in content
 *
 * Fixes relative URLs that don't have a leading slash, which can break
 * when site hierarchy is deployed. Only affects relative page links,
 * not special protocols or anchors.
 *
 * @param string $content The post content
 * @return string Modified content with fixed URLs
 */
function prepend_slash_to_relative_urls($content) {
    // Only process if content contains href attributes
    if (strpos($content, 'href=') === false) {
        return $content;
    }

    // More specific regex that excludes common protocols and special links
    // Matches href="something" where something doesn't start with:
    // - / (already absolute path)
    // - # (anchor links)
    // - http:// or https:// (external URLs)
    // - mailto:, tel:, javascript:, data: (special protocols)
    // - // (protocol-relative URLs)
    $pattern = '/href="(?!\/\/|\/|https?:\/\/|#|mailto:|tel:|javascript:|data:)([a-zA-Z0-9][^"]*)"/i';
    $replacement = 'href="/$1"';
    $content = preg_replace($pattern, $replacement, $content);
    return $content;
}
add_filter('the_content', 'prepend_slash_to_relative_urls');

/**
 * Stop WordPress removing tags from content
 * From: https://www.denisbouquet.com/stop-wordpress-removing-tags-without-plugins/
 *
 * @param array $init TinyMCE initialization array
 * @return array Modified initialization array
 */
function tags_tinymce_fix($init) {
    // html elements being stripped
    $init['extended_valid_elements'] = 'div[*],p[*],br[*]';
    // don't remove line breaks
    $init['remove_linebreaks'] = false;
    // convert newline characters to BR
    $init['convert_newlines_to_brs'] = true;
    // don't remove redundant BR
    $init['remove_redundant_brs'] = false;
    // pass back to wordpress
    return $init;
}
add_filter('tiny_mce_before_init', 'tags_tinymce_fix');

/**
 * Remove pagination limit for certain pages
 *
 * This function sets the 'posts_per_page' parameter to -1 to display all posts
 * without pagination for certain conditions.
 *
 * @param WP_Query $query The WP_Query instance (passed by reference)
 */
function custom_remove_pagination_limit($query) {
    // Check if this is the main query and not in the admin dashboard
    if ($query->is_main_query() && !is_admin()) {
        // Check if the current query is for the home page, an archive, or a page post type archive
        if ($query->is_home() || $query->is_archive() || $query->is_post_type_archive('page')) {
            // Set 'posts_per_page' to -1 to retrieve all posts/pages without pagination
            $query->set('posts_per_page', -1);
        }
    }
}
add_action('pre_get_posts', 'custom_remove_pagination_limit');

/**
 * Order archive pages alphabetically by title
 *
 * This function hooks into the 'pre_get_posts' action to adjust the query parameters
 * before WordPress executes the query on archive pages.
 *
 * @param WP_Query $query The WP_Query instance (passed by reference)
 */
function custom_order_archives_by_title($query) {
    // Ensure this runs only on the main query and not in the admin dashboard
    if ($query->is_main_query() && !is_admin() && $query->is_archive()) {
        // Set the query to order posts by title in ascending order
        $query->set('orderby', 'title');
        $query->set('order', 'ASC');
    }
}
add_action('pre_get_posts', 'custom_order_archives_by_title');

/**
 * Modify excerpt to add ellipsis
 *
 * @param string $post_excerpt The post excerpt
 * @return string Modified excerpt
 */
function picostrap_all_excerpts_get_more_link($post_excerpt) {
    if (!is_admin() OR (isset($_POST['action']) && $_POST['action'] == 'lc_process_dynamic_templating_shortcode')) {
        $post_excerpt = $post_excerpt . '...';
    }
    return $post_excerpt;
}

/**
 * Set excerpt length to 50 words
 */
add_filter("excerpt_length", function($in) {
    // Return the desired number of words for the excerpt
    return 50;
    // The '999' sets a high priority to ensure this filter runs last
}, 999);

/**
 * Disable WordPress text formatting for specific content
 * Prevents WordPress trying to replace standard straight quotes with curly ones
 * This was causing issues with code blocks.
 */
remove_filter('the_content', 'wptexturize');
remove_filter('the_title', 'wptexturize');
remove_filter('comment_text', 'wptexturize');