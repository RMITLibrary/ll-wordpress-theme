<?php

//specific shortcode files included at the bottom of this file.


// Define an array to hold the shortcodes
// this is used by the_content_filter to remove unwanted br and emtpy p tags
$shortcodes =array();

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


// include files with code for specific shortcodes. 
// Shortcodes are added in these files via add_shortcode('name, 'function').

include('accordion.php');	      //handles ll-accordion and transcript
include('nav_panel.php');	      //handles nav-panel shortcode

include('image.php');	          //handles ll-image shortcode
include('video.php');	          //handles ll-video shortcode
include('alert_banner.php');	  //handles alert-banner shortcode

include('landing.php');	          //handles landing-banner, landing-list 
include('code_example.php');	  //handles code example shortcode
include('attribution.php');	       //handles external caption/attribution

include('card.php');               //handles card
include('layout.php');               //handles grid, image-text and icon-text
include('highlight-text.php');      //handles highlight text

include('horizontal-scroll-panel.php');      //handles horizontal scroll panel
include('title_icon.php');      //handles horizontal scroll panel

//include('redirect-listing.php');    // DEPRECATED allows us to list every redirect via a shortcode

function custom_line_break() {
    return '<br />';
}
add_shortcode('br', 'custom_line_break');

?>