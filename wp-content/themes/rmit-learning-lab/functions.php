<?php
/*
        _               _                  _____        _     _ _     _   _   _                         
       (_)             | |                | ____|      | |   (_) |   | | | | | |                        
  _ __  _  ___ ___  ___| |_ _ __ __ _ _ __| |__     ___| |__  _| | __| | | |_| |__   ___ _ __ ___   ___ 
 | '_ \| |/ __/ _ \/ __| __| '__/ _` | '_ \___ \   / __| '_ \| | |/ _` | | __| '_ \ / _ \ '_ ` _ \ / _ \
 | |_) | | (_| (_) \__ \ |_| | | (_| | |_) |__) | | (__| | | | | | (_| | | |_| | | |  __/ | | | | |  __/
 | .__/|_|\___\___/|___/\__|_|  \__,_| .__/____/   \___|_| |_|_|_|\__,_|  \__|_| |_|\___|_| |_| |_|\___|
 | |                                 | |                                                                
 |_|                                 |_|                                                                

                                                       
*************************************** WELCOME TO PICOSTRAP ***************************************

********************* THE BEST WAY TO EXPERIENCE SASS, BOOTSTRAP AND WORDPRESS *********************

    PLEASE WATCH THE VIDEOS FOR BEST RESULTS:
    https://www.youtube.com/playlist?list=PLtyHhWhkgYU8i11wu-5KJDBfA9C-D4Bfl
	Custom functions from Line 200
*/



// DE-ENQUEUE PARENT THEME BOOTSTRAP JS BUNDLE
add_action( 'wp_print_scripts', function(){
    wp_dequeue_script( 'bootstrap5' );
}, 100 );

// ENQUEUE THE BOOTSTRAP JS BUNDLE (AND EVENTUALLY MORE LIBS) FROM THE CHILD THEME DIRECTORY
add_action( 'wp_enqueue_scripts', function() {
    //enqueue js in footer, defer
	
    //wp_enqueue_script( 'bootstrap5-childtheme', get_stylesheet_directory_uri() . "/js/bootstrap.bundle.min.js#deferload", array(), null, true );
	//LC replaced the enqueue script above with the line below... to fix a problem with picostrap theme and the latest version of WordPress
    wp_enqueue_script( 'bootstrap5-childtheme', get_stylesheet_directory_uri() . "/js/bootstrap.bundle.min.js", array(), null, array('strategy' => 'defer', 'in_footer' => true) );

    //optional: lottie (maybe...)
    //wp_enqueue_script( 'lottie-player', 'https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js#deferload', array(), null, true );

    //optional: rellax 
    //wp_enqueue_script( 'rellax', 'https://cdnjs.cloudflare.com/ajax/libs/rellax/1.12.1/rellax.min.js#deferload', array(), null, true );

}, 101);

// REMOVE AIOSEO REDIRECTS FROM TOOLS MENU
// Using admin_menu hook with very late priority to ensure it runs after plugin menus are added
add_action('admin_menu', function() {
    remove_submenu_page('tools.php', 'aioseo-redirects');
}, 9999);

// ADD REDIRECTION LINK TO MAIN MENU (without moving original)
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

// REMOVE UNUSED MENU ITEMS
add_action('admin_menu', function() {
    remove_menu_page('edit.php');           // Posts
    remove_menu_page('edit-comments.php');  // Comments
}, 9999);

// DISABLE PICOSTRAP SASS RECOMPILE MENU
add_action('admin_bar_menu', function() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_node('ps-recompile-sass-backend');
    $wp_admin_bar->remove_node('ps-recompile-sass');
}, 999);

// REMOVE DEFAULT DASHBOARD WIDGETS - MULTIPLE APPROACHES FOR RELIABILITY
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

// ADDITIONAL REMOVAL ATTEMPT WITH DIFFERENT HOOK
add_action('admin_init', function() {
    remove_meta_box('aioseo-rss-feed', 'dashboard', 'side');
    remove_meta_box('aioseo_rss_feed', 'dashboard', 'side');
}, 9999);

// CSS APPROACH AS FALLBACK
add_action('admin_head', function() {
    if (get_current_screen()->base === 'dashboard') {
        echo '<style>
            #aioseo-rss-feed,
            #aioseo_rss_feed,
            .postbox[id*="aioseo"][id*="rss"],
            .postbox[id*="aioseo"][id*="feed"] {
                display: none !important;
            }
        </style>';
    }
});

// ANALYTICS DASHBOARDS FUNCTIONALITY
// Moved to separate file for better organization

// ENQUEUE YOUR CUSTOM JS FILES, IF NEEDED 
add_action( 'wp_enqueue_scripts', function() {	   
    
    //UNCOMMENT next row to include the js/custom.js file globally
    //wp_enqueue_script('custom', get_stylesheet_directory_uri() . '/js/custom.js#deferload', array(/* 'jquery' */), null, true); 

    //UNCOMMENT next 3 rows to load the js file only on one page
    //if (is_page('mypageslug')) {
    //    wp_enqueue_script('custom', get_stylesheet_directory_uri() . '/js/custom.js#deferload', array(/* 'jquery' */), null, true); 
    //}  

}, 102);

// OPTIONAL: ADD MORE NAV MENUS
//register_nav_menus( array( 'third' => __( 'Third Menu', 'picostrap' ), 'fourth' => __( 'Fourth Menu', 'picostrap' ), 'fifth' => __( 'Fifth Menu', 'picostrap' ), ) );
// THEN USE SHORTCODE:  [lc_nav_menu theme_location="third" container_class="" container_id="" menu_class="navbar-nav"]


// CHECK PARENT THEME VERSION as Bootstrap 5.2 requires an updated SCSSphp, so picostrap5 v2 is required
add_action( 'admin_notices', function  () {
    if( (pico_get_parent_theme_version())>=2.1) return; 
	$message = __( 'This Child Theme requires at least Picostrap Version 2.1.0  in order to work properly. Please update the parent theme.', 'picostrap' );
	printf( '<div class="%1$s"><h1>%2$s</h1></div>', esc_attr( 'notice notice-error' ), esc_html( $message ) );
} );

// FOR SECURITY: DISABLE APPLICATION PASSWORDS. Remove if needed (unlikely!)
add_filter( 'wp_is_application_passwords_available', '__return_false' );

// ADD YOUR CUSTOM PHP CODE DOWN BELOW /////////////////////////

/**
 * This function modifies the main WordPress query to include an array of 
 * post types instead of the default 'post' post type.
 *
 * @param object $query The main WordPress query.
 */
function tg_include_custom_post_types_in_search_results( $query ) {
  if ( $query->is_main_query() && $query->is_search() && ! is_admin() ) {
      $query->set( 'post_type', array( 'post', 'page' ) );
  }
}
add_action( 'pre_get_posts', 'tg_include_custom_post_types_in_search_results' );

add_filter('acf/fields/taxonomy/result', 'my_acf_fields_taxonomy_result', 10, 4);
function my_acf_fields_taxonomy_result( $text, $term, $field, $post_id ) {
    $text;
    return $text;
}


if ( ! function_exists( 'custom_taxonomy' ) ) {

// Register Custom Taxonomy
function custom_taxonomy() {

	$labels = array(
		'name'                       => _x( 'Keywords', 'Taxonomy General Name', 'text_domain' ),
		'singular_name'              => _x( 'Keyword', 'Taxonomy Singular Name', 'text_domain' ),
		'menu_name'                  => __( 'Taxonomy', 'text_domain' ),
		'all_items'                  => __( 'All Items', 'text_domain' ),
		'parent_item'                => __( 'Parent Item', 'text_domain' ),
		'parent_item_colon'          => __( 'Parent Item:', 'text_domain' ),
		'new_item_name'              => __( 'New Item Name', 'text_domain' ),
		'add_new_item'               => __( 'Add New Item', 'text_domain' ),
		'edit_item'                  => __( 'Edit Item', 'text_domain' ),
		'update_item'                => __( 'Update Item', 'text_domain' ),
		'view_item'                  => __( 'View Item', 'text_domain' ),
		'separate_items_with_commas' => __( 'Separate items with commas', 'text_domain' ),
		'add_or_remove_items'        => __( 'Add or remove items', 'text_domain' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
		'popular_items'              => __( 'Popular Items', 'text_domain' ),
		'search_items'               => __( 'Search Items', 'text_domain' ),
		'not_found'                  => __( 'Not Found', 'text_domain' ),
		'no_terms'                   => __( 'No items', 'text_domain' ),
		'items_list'                 => __( 'Items list', 'text_domain' ),
		'items_list_navigation'      => __( 'Items list navigation', 'text_domain' ),
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
	register_taxonomy( 'taxonomy', array( 'page' ), $args );

}
add_action( 'init', 'custom_taxonomy', 0 );

}
//-----------------------------
// STOP WORDPRESS REMOVING TAGS
// from https://www.denisbouquet.com/stop-wordpress-removing-tags-without-plugins/

function tags_tinymce_fix( $init )
{
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
//-----------------------------



//-----------------------------------------------------------------------------------
// CUSTOM FUNCTIONS BELOW HERE
//
//
//-----------------------------------------------------------------------------------

//-----------------------------
//	createBreadcrumbs

//	Output markup for breadcrumbs. Current page is listed in 
//	breadcrumbs due to proximity to title

//	Called from:	page-templates/landing.php
//					page-templates/page-sidebar-right.php

//	args:			$thePost - page where the breadcrumbs will be shown

//	calls:			formatAfterTheColon

//	usage:			echo createBreadcrumbs($post);

//	Expected output
//	<nav aria-label="breadcrumbs">
//		<ul class="breadcrumbs">
//		<li><a href="/">Home</a></li>
//		<li><a href="/greatGrandParent">Great grandparent/a></li>
//		<li><a href="/grandParent">Grandparent</a></li>
//		<li><a href="/parent">Parent</a></li>
//		</ul>
//	</nav>

function createBreadcrumbs($thePost)
{
	$parent = get_post_parent($thePost);
	$grandParent = $parent ? get_post_parent($parent) : null;
	$greatGrandParent = $grandParent ? get_post_parent($grandParent) : null;

	$output = '';

	$output .= '<nav aria-label="breadcrumbs">' . "\n";
	$output .= '<ul class="breadcrumbs">' . "\n";

	$output .= '<li><a href="/">Home</a></li>' . "\n";

	// Check that objects exist and have ID property before accessing
	if($greatGrandParent && !empty($greatGrandParent->ID) && $grandParent && !empty($grandParent->ID)) {
		$output .= '<li><a href="/' . esc_attr($greatGrandParent->post_name) . '">' . esc_html(formatAfterTheColon(get_the_title($greatGrandParent))) . '</a></li>' . "\n";
	}

	if($grandParent && !empty($grandParent->ID) && $parent && !empty($parent->ID)) {
		$output .= '<li><a href="/' . esc_attr($grandParent->post_name) . '">' . esc_html(formatAfterTheColon(get_the_title($grandParent))) . '</a></li>' . "\n";
	}

	if($parent && !empty($parent->ID)) {
		$output .= '<li><a href="/' . esc_attr($parent->post_name) . '">' . esc_html(formatAfterTheColon(get_the_title($parent))) . '</a></li>' . "\n";
	}

	$output .= '</ul>'  . "\n";
	$output .= '</nav>';
	return $output;
}



//-----------------------------
//	formatAfterTheColon

//	Formats string - capitialises string section after 1st colon.
//	E.g. "Artists statement: writing Process" becomes "Writing process"

//	Called from:	createBreadcrumbs
//					outputChildNav

//	args:			$string - the string to format

//	usage:			echo formatAfterTheColon("Throw away: keep this");

//	Expected output
//	"Keep this"

function formatAfterTheColon($string)
{
    // Split the string at colon
    $parts = explode(':', $string, 2); // Limit to 2 parts to handle colons within the string correctly

    if (count($parts) === 2) {
        // Capitalise the first character of the second part
        $parts[1] = ucfirst(trim($parts[1]));
        return $parts[1];
    } else {
        // Handle cases where there might not be a colon
        return $string;
    }
}



//-----------------------------
//	doContextMenuAccordion

//	Creates an accordion for the context (hamburger) menu based on $pageId argument

//	Called from:	page_templates/header.php

//	args:			$title - Title to be displayed on the accordion
//					$pageId - Id of the page whose children we want to display

//	calls:			doChildrenList

//	usage:			echo doContextMenuAccordion('Assessments', 4266);

function doContextMenuAccordion($title, $pageId)
{
	$headId = 'accordion-head-' . $pageId;
	$bodyId = 'accordion-body-' . $pageId;

	$output = '';

	$output .= '<div class="accordion-item">' . "\n";
	$output .= '<h2 class="accordion-header" id="' . $headId .'">' . "\n";
	$output .= '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#' . $bodyId . '" aria-expanded="false" aria-controls="' . $bodyId . '">';    
	$output .= $title;
	$output .= '</button>' . "\n";
	$output .= '</h2>' . "\n";
	$output .= '<div id="' . $bodyId . '" class="accordion-collapse collapse" aria-labelledby="' . $headId . '">' . "\n";
	$output .= '<div class="accordion-body"><ul>' . doChildrenList($pageId) . '</ul></div></div></div>';

	return $output;
}



//-----------------------------
//	doChildrenList

//	Creates a list of child pages: links wrapped in list items

//	Called from:	doContextMenuAccordion
//					custom-shortcodes/_main.php

//	args:			$pageId - Id of the page we to get children of

//	calls:			wp_list_pages() - wordpress function

//	usage:			echo doChildrenList($pageId);

//	Expected output
//	<li class="page_item page-item-3107 page_item_has_children"><a href="link">Page title</a></li>
//	Extra classes not required, an artefact of using wp_list_pages()

function doChildrenList($pageId)
{
	return wp_list_pages(
		array(
			'child_of' => $pageId,
			'depth' => 1,
			'title_li' => null,
			'echo' => false
		)
	);
}

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


// Prevents Worpress trying to rplace standrad striaght quotes with curly ones
// This was causing issues with code blocks. Worth exploring if there's a way
// to target code blocks only.
remove_filter('the_content', 'wptexturize');
remove_filter('the_title', 'wptexturize');
remove_filter('comment_text', 'wptexturize');




// Modify the main WordPress query to remove the pagination limit.

// This function sets the 'posts_per_page' parameter to -1 to display all posts
// without pagination for certain conditions.

 // @param WP_Query $query The WP_Query instance (passed by reference).

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

// Hook the function to 'pre_get_posts' to modify the query before it is executed
add_action('pre_get_posts', 'custom_remove_pagination_limit');


// Modify the main query for archive pages to order posts alphabetically by title.

// This function hooks into the 'pre_get_posts' action to adjust the query parameters
// before WordPress executes the query on archive pages.

// @param WP_Query $query The WP_Query instance (passed by reference).

function custom_order_archives_by_title($query) {
    // Ensure this runs only on the main query and not in the admin dashboard
    if ($query->is_main_query() && !is_admin() && $query->is_archive()) {
        // Set the query to order posts by title in ascending order
        $query->set('orderby', 'title');
        $query->set('order', 'ASC');

        // Optionally, uncomment the next line to display all posts without pagination
        // $query->set('posts_per_page', -1);
    }
}

// Hook the function to 'pre_get_posts' to modify the query before it is executed
add_action('pre_get_posts', 'custom_order_archives_by_title');





function picostrap_all_excerpts_get_more_link( $post_excerpt ) {
    if ( ! is_admin() OR ( isset($_POST['action']) && $_POST['action'] == 'lc_process_dynamic_templating_shortcode') ) {
        $post_excerpt = $post_excerpt . '...';
    }
    return $post_excerpt;
}

// Filter to change the excerpt length
add_filter("excerpt_length", function($in){
    // Return the desired number of words for the excerpt
    return 50;
    // The '999' sets a high priority to ensure this filter runs last
}, 999);




//-----------------------------

include('includes/json-export.php');        // exports the site date to json. Required for search to function
include('includes/redirect.php');           // redirect and 404 code for both admin and client side
include('includes/seo-noindex-inheritance.php'); // noindex inheritance for work in progress pages
include('includes/analytics-dashboards.php'); // Analytics dashboards functionality
include('custom-shortcodes/_main.php');     // All shortcode code is included and added below