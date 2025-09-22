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

    // Pre-compute whether each keyword has at least one qualifying post
    $keyword_status_map = array();
    $keyword_ids = wp_list_pluck($keywords, 'term_id');
    $keyword_lookup = array_fill_keys($keyword_ids, true);

    if (!empty($keyword_ids)) {
        // Fetch all pages attached to any of the keywords in one request
        $keyword_posts = get_posts(
            array(
                'post_type'      => 'page',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'tax_query'      => array(
                    array(
                        'taxonomy'         => 'keyword',
                        'field'            => 'term_id',
                        'terms'            => $keyword_ids,
                        'include_children' => false,
                    ),
                ),
            )
        );

        if (!empty($keyword_posts)) {
            $terms_for_posts = wp_get_object_terms(
                $keyword_posts,
                'keyword',
                array(
                    'orderby' => 'term_id',
                    'fields'  => 'all_with_object_id',
                )
            );

            $terms_indexed = array();
            if (!is_wp_error($terms_for_posts)) {
                foreach ($terms_for_posts as $term) {
                    $object_id = (int) $term->object_id;
                    if (!isset($terms_indexed[$object_id])) {
                        $terms_indexed[$object_id] = array();
                    }
                    $terms_indexed[$object_id][] = $term;
                }

                // Determine which keywords have qualifying posts (non-Archive, non work-in-progress URL)
                foreach ($keyword_posts as $post_id) {
                    $post_terms = isset($terms_indexed[$post_id]) ? $terms_indexed[$post_id] : array();

                    if (empty($post_terms)) {
                        continue;
                    }

                    $has_archive_term = false;
                    foreach ($post_terms as $term) {
                        if (strcasecmp($term->name, 'Archive') === 0) {
                            $has_archive_term = true;
                            break;
                        }
                    }

                    if ($has_archive_term) {
                        continue;
                    }

                    $post_url = get_permalink($post_id);
                    if ($post_url && stripos($post_url, 'work-in-progress') !== false) {
                        continue;
                    }

                    foreach ($post_terms as $term) {
                        $term_id = (int) $term->term_id;
                        if (isset($keyword_lookup[$term_id])) {
                            $keyword_status_map[$term_id] = true;
                        }
                    }
                }
            }
        }
    }

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
        $link = is_wp_error($link) ? '' : $link;

        // Output the keyword as a list item with a link, exclude "Documentation" and "Archive"
        if ($keyword->name != "Documentation" && $keyword->name != "Archive") {
            $has_valid_post = !empty($keyword_status_map[$keyword->term_id]);

            if ($has_valid_post && !empty($link)) {
                echo '<li><a href="' . esc_url($link) . '">' . esc_html($keyword->name) . '</a></li>';
            }
        }
    }

    // Close the last section
    echo '</ul></section>';
} else {
    // Output message if no keywords are found or there's an error
    echo '<p>' . esc_html__('No keywords found.', 'rmit-learning-lab') . '</p>';
}
?>
    <!-- <p class="visually-hidden">Data set lives here: <a href="/wp-content/uploads/pages.json" target="_blank" rel="noopener">/wp-content/uploads/pages.json</a></p>-->
    </div>
</div>
<!-- END col-xs-12 -->
</div>

<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/js/search.js?v=1.3.7"></script>
<?php get_footer();
