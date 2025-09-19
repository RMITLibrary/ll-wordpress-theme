<?php


function list_slugs() {
    $page_slugs = get_all_page_slugs();
    sort($page_slugs); // Sort the slugs alphabetically

    $output = '<ul style="width: 100%; column-count: 3;">';

    foreach ($page_slugs as $slug) {
        $permalink = home_url($slug); // Construct the full URL for the page
        $output .= '<li><a href="' . esc_url($permalink) . '">' . esc_html($slug) . '</a></li>';
    }

    $output .= '</ul>';
    return $output; // Return the output instead of echoing it
}

function get_all_page_slugs() {
    $args = array(
        'post_type' => 'page', // Retrieves only pages
        'posts_per_page' => -1 // Retrieves all pages
    );

    $all_pages = get_posts($args);
    $slugs = array();

    foreach ($all_pages as $page) {
        $slug = $page->post_name; // This will give you the slug
        $slugs[] = '/' . $slug . '/';
    }

    return $slugs;
}

add_shortcode('all-slugs', 'list_slugs');



//-----------------------------
//	list_redirects_shortcode

//	Outputs a list of posts or pages with their associated redirect slugs. 
//  Utilises Advanced Custom Fields (ACF) to retrieve the 'redirect_slug' field.

//  args:       None

//  shortcode:  [list_redirects]

//	usage:		[list_redirects] 

//  Expected output:
//  <ul>
//      <li>Post/Page Title: redirect_slug_value</li>
//      ...
//  </ul>
//  If no redirects are found, outputs: "No redirects found."
//


function list_redirects() {
    // Start output buffering
    ob_start();

    // Query posts or pages with the 'redirect_slug' field
    $args = array(
        'post_type' => 'page',
        'meta_query' => array(
            array(
                'key' => 'redirect_slug',
                'compare' => 'EXISTS'
            ),
        ),
        'orderby' => 'title',
        'order' => 'ASC',
        'posts_per_page' => -1 // This ensures all pages are returned
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<table class="table-striped">';
        echo '<thead><tr><th></th><th>Page Title</th><th>Slug</th><th>Redirect Slug</th></tr></thead>';
        echo '<tbody>';
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID(); // Ensure the post ID is correctly retrieved here
            $page_slug = get_post_field('post_name', $post_id); // Get the page slug
            $redirect_slug = get_field('redirect_slug'); // Assuming ACF is used for redirect slug

            echo '<tr>';
            if (is_user_logged_in()) {
                echo '<td><a href="' . esc_url(admin_url('post.php?post=' . $post_id . '&action=edit')) . '">Edit</a></td>';
            } else {
                echo '<td>—</td>'; // Placeholder for non-logged-in users
            }
            echo '<td>' . get_the_title() . '</td>';
            echo '<td><a href="' . esc_url(site_url($page_slug)) . '">' . esc_html($page_slug) . '</a></td>';
            echo '<td><a href="' . esc_url(site_url($redirect_slug)) . '">' . esc_html($redirect_slug) . '</a></td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo 'No redirects found.';
    }

    // Reset post data
    wp_reset_postdata();

    // Return the buffered content
    return ob_get_clean();
}

// Register the shortcode
add_shortcode('list_redirects', 'list_redirects');

?>