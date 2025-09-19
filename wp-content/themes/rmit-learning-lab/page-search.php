<?php
/**
 *
 * Template for displaying a page just with the header and footer area and a "naked" content area in between.
 * Good for landing pages and other types of pages where you want to add a lot of custom markup.
 *
 * @package UnderStrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container" id="page-content">
<!-- FUSE script --> 
<script src="https://cdn.jsdelivr.net/npm/fuse.js"></script>
<div class="col-xl-8">
<nav aria-label="breadcrumbs">
<ul class="breadcrumbs">
<li><a href="/">Home</a></li>
</ul>
</nav>
<a id="main-content"></a>
<!-- START search -->
<div class="search-container">
    <label for="searchInput"><h1 class="margin-top-zero">Search the Learning Lab</h1></label>
    <div class="input-group">
        <input type="search" id="searchInput" class="form-control">
        <button type="submit"  id="searchButton" class="btn btn-primary"><div class="mag-glass"></div><span class="visually-hidden">Search</span></button>
    </div>
</div>
<!-- END search -->
<!-- START debug -->
        <div id="search-debug" class="search-debug">
            <div>            
                <!-- START debug -->
                <label for="threshold">
                    <a href="https://www.fusejs.io/api/options.html#threshold" target="docs">Threshold:</a>
                </label>
                <input type="number" id="threshold" min="0" max="1" step="0.05" value="0.4">
                </div>
                <div>
                    <label for="distance">
                        <a href="https://www.fusejs.io/api/options.html#distance" target="docs">Distance:</a>
                    </label>
                    <input type="number" id="distance" min="0" max="10000" step="50" value="1200">
                </div>
                <div>
                    <label for="location">
                        <a href="https://www.fusejs.io/api/options.html#location" target="docs">Location:</a>
                    </label>
                    <input type="number" id="location" min="0" max="10000" step="50" value="0">
                </div>
                <div>
                    <label for="minMatchCharLength" class="small">
                        <a href="https://www.fusejs.io/api/options.html#minmatchcharlength" target="docs">Min Match Char Length:</a>
                    </label>
                    <input type="number" id="minMatchCharLength" min="1" max="100" step="1" value="4">
                </div>
                <div>
                <label for="useExtendedSearch" class="small">
                    <a href="https://www.fusejs.io/api/options.html#useextendedsearch" target="_blank">Use Extended Search:</a>
                </label>
                <input type="checkbox" id="useExtendedSearch">
            </div>
        </div>
<!-- END debug -->
<!-- START results div -->
<div id="results-container" class="collapse">
    <div>
        <h2 id="results-title" tabindex="0">Search results</h2>
        <p id="results-counter" class="small"></p>
        <ul class="list-link-expanded" id="results"></ul> 
        <p>
            <a href="#searchInput">Search again</a>
        </p> 
    </div>   
</div>
<!-- END results div -->
<hr>
<a name="keywords"></a>
<h2>Browse keywords</h2>
            <p>These pages of similar topics aim to make it quicker and easier to find the content you need. Select any keyword to see all pages linked to that specific term.</p>
</div>
    <!-- END col-xs-8 -->
    <div class="col-xl-12">
        <div class="keyword-listing">
<?php
// Retrieve all terms from the 'keyword' taxonomy
$keywords = get_terms(array(
    'taxonomy' => 'keyword',    // Specify the taxonomy slug
    'orderby' => 'name',        // Order terms by name
    'order' => 'ASC',           // Sort in ascending order
    'hide_empty' => true,       // Exclude terms with no posts/pages
));

// Check if keywords are retrieved successfully
if (!empty($keywords) && !is_wp_error($keywords)) {
    $current_letter = ''; // Initialize variable to track the current starting letter

    // Loop through each keyword
    foreach ($keywords as $keyword) {
        // Get the first letter of the keyword name and convert to uppercase
        $first_letter = strtoupper($keyword->name[0]);

        // Check if the first letter has changed
        if ($first_letter !== $current_letter) {
            // Close previous section if it's not the first letter
            if ($current_letter !== '') {
                echo '</ul></section>';
            }

            // Update the current letter and start a new section
            $current_letter = $first_letter;
            echo '<section><h2>' . esc_html($current_letter) . '</h2><ul>';
        }

        // Get the link for the current keyword
        $link = get_term_link($keyword);

        // Parse the URL to extract the path component
        $parsed_url = wp_parse_url($link);
        $relative_link = isset($parsed_url['path']) ? $parsed_url['path'] : '';

        // Output the keyword as a list item with a link, exclude "Documentation" and "Archive"
        if ($keyword->name != "Documentation" && $keyword->name != "Archive") {
            // Query posts associated with the current keyword
            $query_args = array(
                'post_type' => 'page', // Adjust post type if needed
                'tax_query' => array(
                    array(
                        'taxonomy' => 'keyword',
                        'field'    => 'slug',
                        'terms'    => $keyword->slug,
                    ),
                ),
                'fields' => 'ids', // Only retrieve post IDs for efficiency
            );

            $posts = get_posts($query_args);
            $has_valid_post = false;

            // Check if there's at least one post without the "Archive" term and "work-in-progress" in the URL
            foreach ($posts as $post_id) {
                $post_terms = get_the_terms($post_id, 'keyword');
                $has_archive_term = false;
                
                if ($post_terms) {
                    foreach ($post_terms as $term) {
                        if ($term->name == "Archive") {
                            $has_archive_term = true;
                            break; // Break the loop if the "Archive" term is found
                        }
                    }
                }
                
                // Check if the post URL contains "work-in-progress"
                $post_url = get_permalink($post_id);
                if ($has_archive_term || stripos($post_url, 'work-in-progress') !== false) {
                    continue; // Skip this post if it has the "Archive" term or "work-in-progress" in the URL
                }
                
                $has_valid_post = true;
                break; // Exit loop if a valid post is found
            }

            // Output the keyword link if a valid post is found
            if ($has_valid_post) {
                echo '<li><a href="..' . esc_url($relative_link) . '">' . esc_html($keyword->name) . '</a></li>';
            }
        }
    }

    // Close the last section
    echo '</ul></section>';
} else {
    // Output message if no keywords are found or there's an error
    echo 'No keywords found.';
}
?>
    <!-- <p class="visually-hidden">Data set lives here: <a href="/wp-content/uploads/pages.json" target="_blank" rel="noopener">/wp-content/uploads/pages.json</a></p>-->
    </div>
</div>
<!-- END col-xs-12 -->
</div>

<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/search.js?v=1.3.7"></script>
<?php get_footer();
