<?php
/**
 * Custom Taxonomy Registration
 *
 * Registers custom taxonomies for the RMIT Learning Lab theme.
 *
 * @package RMIT_Learning_Lab
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ACF Taxonomy Field Result Filter
 *
 * @param string $text The text to display
 * @param WP_Term $term The term object
 * @param array $field The ACF field settings
 * @param int $post_id The post ID
 * @return string The filtered text
 */
add_filter('acf/fields/taxonomy/result', 'rmit_ll_acf_fields_taxonomy_result', 10, 4);
function rmit_ll_acf_fields_taxonomy_result($text, $term, $field, $post_id) {
    return $text;
}

/**
 * Register Keywords Custom Taxonomy
 */
if (!function_exists('rmit_ll_register_keywords_taxonomy')) {
    function rmit_ll_register_keywords_taxonomy() {
        $labels = array(
            'name'                       => _x('Keywords', 'Taxonomy General Name', 'rmit-learning-lab'),
            'singular_name'              => _x('Keyword', 'Taxonomy Singular Name', 'rmit-learning-lab'),
            'menu_name'                  => __('Keywords', 'rmit-learning-lab'),
            'all_items'                  => __('All Keywords', 'rmit-learning-lab'),
            'parent_item'                => __('Parent Keyword', 'rmit-learning-lab'),
            'parent_item_colon'          => __('Parent Keyword:', 'rmit-learning-lab'),
            'new_item_name'              => __('New Keyword Name', 'rmit-learning-lab'),
            'add_new_item'               => __('Add New Keyword', 'rmit-learning-lab'),
            'edit_item'                  => __('Edit Keyword', 'rmit-learning-lab'),
            'update_item'                => __('Update Keyword', 'rmit-learning-lab'),
            'view_item'                  => __('View Keyword', 'rmit-learning-lab'),
            'separate_items_with_commas' => __('Separate keywords with commas', 'rmit-learning-lab'),
            'add_or_remove_items'        => __('Add or remove keywords', 'rmit-learning-lab'),
            'choose_from_most_used'      => __('Choose from the most used', 'rmit-learning-lab'),
            'popular_items'              => __('Popular Keywords', 'rmit-learning-lab'),
            'search_items'               => __('Search Keywords', 'rmit-learning-lab'),
            'not_found'                  => __('Not Found', 'rmit-learning-lab'),
            'no_terms'                   => __('No keywords', 'rmit-learning-lab'),
            'items_list'                 => __('Keywords list', 'rmit-learning-lab'),
            'items_list_navigation'      => __('Keywords list navigation', 'rmit-learning-lab'),
        );

        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => false,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
        );

        register_taxonomy('keywords', array('page'), $args);
    }
    add_action('init', 'rmit_ll_register_keywords_taxonomy', 0);
}