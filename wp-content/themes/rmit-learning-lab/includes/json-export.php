<?php
//-----------------------------
//	export_content_to_json

//	Creates a json file of all page data. Intended for use as a dataset for search functionality

//	Called from:	export_json_page()

//	Expected output

function export_content_to_json() {
    // Query WordPress content
    $args = array(
        'post_type' => 'page',
        'post_status' => 'publish', // Only fetch published pages
        'posts_per_page' => -1, // Get all pages
    );

    $query = new WP_Query($args);
    $posts_data = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $content = get_the_content();
            //$content = strip_tags(strip_shortcodes($content)); // Remove shortcodes and HTML tags

            $content = strip_tags($content); // Remove HTML tags

            // Extract content from specific shortcodes
            $shortcodes = array('ll-accordion', 'transcript', 'transcript-accordion', 'lightweight-accordion', 'hl', 'highlight-text');
            foreach ($shortcodes as $shortcode) {
                $pattern = sprintf('/\[%1$s\](.*?)\[\/%1$s\]/s', preg_quote($shortcode, '/'));
                if (preg_match_all($pattern, get_the_content(), $matches)) {
                    foreach ($matches[1] as $match) {
                        $content .= ' ' . strip_tags($match);
                    }
                }
            }
            
            // Remove remaining shortcodes
            $content = strip_shortcodes($content);

            // Get taxonomy terms using ACF
            $llkeywords = get_field('field_6527440d6f9a2');
            $keywords = [];
            if ($llkeywords) {
                foreach ($llkeywords as $term) {
                    $keywords[] = $term->name;
                }
            }

            $breadcrumbs = get_breadcrumbs(get_the_ID());

            $posts_data[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'content' => $content,
                'excerpt' => strip_tags(strip_shortcodes(get_the_excerpt())), // Remove shortcodes and HTML tags
                'date' => get_the_date(),
                'link' => wp_parse_url(get_permalink(), PHP_URL_PATH), // Extract the path from the URL
                'keywords' => $keywords,
                'breadcrumbs' => $breadcrumbs
            );
        }
        wp_reset_postdata();
    }

    // Convert to JSON
    $json_data = json_encode($posts_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    // Save to a file
    $file = fopen(ABSPATH . '/wp-content/uploads/pages.json', 'w');
    fwrite($file, $json_data);
    fclose($file);
}

//-----------------------------
//	export_page_urls_to_json
//
//	Creates a json file that contains only page URLs (paths) for all published pages
//
//	Called from:	export_json_page()
//
function export_page_urls_to_json() {
    // Query WordPress pages
    $args = array(
        'post_type' => 'page',
        'post_status' => 'publish', // Only fetch published pages
        'posts_per_page' => -1, // Get all pages
    );

    $query = new WP_Query($args);
    $urls = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            // Use path-only to be consistent with pages.json
            $path = wp_parse_url(get_permalink(), PHP_URL_PATH);
            // Exclude any URLs containing '/work-in-progress/'
            if (strpos($path, '/work-in-progress/') !== false) {
                continue;
            }
            $urls[] = $path;
        }
        wp_reset_postdata();
    }

    // Convert to JSON
    $json_data = json_encode($urls, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    // Save to a file
    $file = fopen(ABSPATH . '/wp-content/uploads/pages-urls.json', 'w');
    fwrite($file, $json_data);
    fclose($file);
}

//-----------------------------
//	get_breadcrumbs
//	Generates a breadcrumb trail for a given post/page

//	Called from:	export_content_to_json() - Custom export function

//	Returns:		Array of breadcrumb items with title and link

//	Usage:			Used to add breadcrumb data to JSON export

function get_breadcrumbs($post_id) {
    $breadcrumbs = array();
    $parent_id = wp_get_post_parent_id($post_id);

    // Traverse up to get all parent pages
    while ($parent_id) {
        $page = get_post($parent_id);
        array_unshift($breadcrumbs, array(
            'title' => get_the_title($page->ID),
            'link' => get_permalink($page->ID)
        ));
        $parent_id = wp_get_post_parent_id($page->ID);
    }

    // Add the current page
    $breadcrumbs[] = array(
        'title' => get_the_title($post_id),
        'link' => get_permalink($post_id)
    );

    return $breadcrumbs;
}

//-----------------------------
//	register_export_page

//	Creates a new admin menu page for exporting content to JSON

//	Called from:	add_action('admin_menu', 'register_export_page');

//	calls:			add_menu_page() - WordPress function

//	usage:			Automatically called by WordPress when admin menu is built

function register_export_page() {
    add_menu_page(
        'Export Content to JSON',
        'Export JSON',
        'manage_options',
        'export-json',
        'export_json_page'
    );
}
add_action('admin_menu', 'register_export_page');

//-----------------------------
//	export_json_page

//	Generates the admin page with a button to export content to JSON

//	Called from:	register_export_page

//	calls:			export_content_to_json() - custom function

//	usage:			Triggered by form submission on the admin page

function export_json_page() {
    if (isset($_POST['export_json'])) {
        export_content_to_json();
        export_page_urls_to_json();
        echo '<div class="updated"><p>Content and Page URLs exported to JSON successfully!</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Export Content to JSON</h1>
        <form method="post">
            <input type="submit" name="export_json" class="button-primary" value="Export Now">
        </form>
    </div>
    <?php
}
?>