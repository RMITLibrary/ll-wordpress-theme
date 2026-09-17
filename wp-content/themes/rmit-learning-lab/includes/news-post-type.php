<?php

//-----------------------------
// WHAT'S NEW
//
// Announcements were a single hand-edited page. This makes each one a post so
// they can be added without a release, with the archive at /whats-new/.
//
// Deliberately out of the search index: json-export.php stays 'page' only.
//-----------------------------

add_action('init', function () {
    register_post_type('news', array(
        'label'         => __("What's new", 'rmit-learning-lab'),
        'labels'        => array(
            'name'          => __("What's new", 'rmit-learning-lab'),
            'singular_name' => __('Announcement', 'rmit-learning-lab'),
            'add_new_item'  => __('Add announcement', 'rmit-learning-lab'),
            'edit_item'     => __('Edit announcement', 'rmit-learning-lab'),
            'all_items'     => __("What's new", 'rmit-learning-lab'),
        ),
        'public'        => true,
        'menu_icon'     => 'dashicons-megaphone',
        'menu_position' => 21,
        'supports'      => array('title', 'editor', 'excerpt', 'revisions'),
        'has_archive'   => 'whats-new',
        'rewrite'       => array('slug' => 'whats-new', 'with_front' => false),
        'show_in_rest'  => false,
    ));
});

// Newest first, and show the lot — there are a handful of announcements a year,
// so pagination would only add a second URL for the capture to find.
add_action('pre_get_posts', function ($query) {
    if (is_admin() || !$query->is_main_query() || !$query->is_post_type_archive('news')) {
        return;
    }

    $query->set('posts_per_page', -1);
    $query->set('orderby', 'date');
    $query->set('order', 'DESC');
});
