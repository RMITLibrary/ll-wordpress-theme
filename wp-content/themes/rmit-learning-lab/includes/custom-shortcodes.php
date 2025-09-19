<?php
/**
 * Custom Shortcodes Manager
 *
 * Manages all custom shortcodes for the RMIT Learning Lab theme.
 * Auto-discovers and includes shortcode files from the custom-shortcodes directory.
 *
 * @package RMIT_Learning_Lab
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define an array to hold the shortcodes
// this is used by the_content_filter to remove unwanted br and empty p tags
$shortcodes = array();

// Function to add a shortcode to the array
function add_shortcode_to_list($shortcode) {
    global $shortcodes;
    // Initialize $shortcodes as an array if it's not set
    if (!is_array($shortcodes)) {
        $shortcodes = array();
    }
    if (!in_array($shortcode, $shortcodes)) {
        $shortcodes[] = $shortcode;
    }
}

// Function to get the list of shortcodes
function get_shortcodes_list() {
    global $shortcodes;
    return $shortcodes;
}

//-----------------------------
//	the_content_filter

//	Prevents empty <p> or <br> tags being added in between shortcodes
function the_content_filter($content) {
    // Get the list of shortcodes
    $shortcodes = get_shortcodes_list();

    // Add in shortcodes to this list
    $block = join("|", $shortcodes);
    $rep = preg_replace("/(<p>)?\[($block)(\s[^\]]+)?\](<\/p>|<br \/>?)?/", "[$2$3]", $content);
    $rep = preg_replace("/(<p>)?\[\/($block)](<\/p>|<br \/>?)?/", "[/$2]", $rep);
    return $rep;
}

add_filter("the_content", "the_content_filter");





//-----------------------------
//	strip_tags_before_echo

//When the above isn't working, use this function right before echoing 
//content to definitely get rid of <br> and <p></p> (but not <br />)

//called by: Additional_resources page-template
//Maybe this should reside in functions.php ????

function strip_tags_before_echo($content) {
    // Strip out <br> tags
    $content = preg_replace('/<br\s*\/?>/', '', $content);
    
    // Strip out <p></p> tags
    $content = preg_replace('/<p[^>]*>[\s|&nbsp;]*<\/p>/', '', $content);
    
    // Return the stripped content
    return $content;
}


// Auto-include all shortcode files
// Automatically discovers and includes all PHP files in the custom-shortcodes directory
// except _main.php and any files starting with underscore

$shortcode_dir = get_stylesheet_directory() . '/custom-shortcodes';
$shortcode_files = glob($shortcode_dir . '/*.php');

foreach ($shortcode_files as $file) {
    $filename = basename($file);

    // Skip this main file and any files starting with underscore (private/utility files)
    if ($filename === '_main.php' || strpos($filename, '_') === 0) {
        continue;
    }

    // Skip deprecated/excluded files
    $excluded_files = array(
        'redirect-listing.php', // DEPRECATED - consider renaming to _redirect-listing.php
        // Add more files to exclude here as needed
        // 'example-old-shortcode.php',
    );

    if (in_array($filename, $excluded_files)) {
        continue; // Skip excluded files
    }

    include_once($file);
}

function custom_line_break() {
    return '<br />';
}
add_shortcode('br', 'custom_line_break');

?>