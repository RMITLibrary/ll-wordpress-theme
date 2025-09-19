<?php

//-----------------------------
//	nav_panel_att

//	Creates a linked blockquote with options for category, extra-info and icon

//	args:		$content - text in the blockquote (optional)
//              $atts - attributes as follows:

//  $atts:      title       Title of the blockquote
//              link        URL to link to (rmith.eduau is the default)
//              category    Small text shown above title (optional)
//              extra-info  Small text shown below content (optional)
//              icon        Absolute path to icon (optional - svg preferred)

//  shortcode:  [nav-panel]

//	usage:			
//  [nav-panel category='Category' link='/cohesion' title='This is the title' extra-info='Extra information' icon='https://path.to/icon.svg']This is the blockquote content.[/nav-panel]

//	Expected output
//  <blockquote class="complex">
//	<a href="mylink">
//		<div class="content">
//			<p class="category">Category</p>
//			<h3>This is a title </h3>
//			<p>This is the blockquote content.</p>
//			<small>Extra information</small>
//		</div>
//		<div class="icon-wrap">
//			<img src="my-icon.png" alt="" />
//		</div>
//	</a>
//  </blockquote>

function nav_panel_att($atts, $content = null) {
	$default = array(
        'link' => 'https://www.rmit.edu.au',
        'category' => '',
		'title' => 'My blockquote nav',
		'extra-info' => '',
		'icon' => '',
        'classes' => ''
    );
    
    //merges user-defined attributes with a set of default values ($default)
    $a = shortcode_atts($default, $atts);
    
    //grab content from within the two shortcode tags
    $content = do_shortcode($content);
	
	$output = '';
    
    $output .= '<blockquote class="complex ';
    
    //if there's anything in clesses, add it (don't document this, for web devs only)
    if($a['classes'] != '') { 
        $output .= $a['classes'] . ' '; 
    } 
    
    $output .= '">' . "\n";
    
    $output .= '<a href="' . $a['link'] .'">' . "\n";
    $output .= '<div class="content">' . "\n";
	
    //If $category exists, add it to the output
	if($a['category'] != '') {
		$output .= '<p class="category">'. $a['category'] . '</p>' . "\n";
	}
	
    //Title has to exist
    $output .= '<h3>' . $a['title'] . '</h3>' . "\n";
    
    //If $content exists, add it to the output
	if($content != null) {
		$output .= '<p>' . $content . '</p>' . "\n";
	}
	
     //If extra-info exists, add it to the output
	if($a['extra-info'] != '') {
		$output .= '<small>'. $a['extra-info'] . '</small>' . "\n";
	}
	
    $output .= '</div>';
	
     //If icon exists, add it to the output
	if($a['icon'] != '') {
		$output .= '<div class="icon-wrap">';
		$output .= '<img src="'. $a['icon'] . '" alt="" />';
		$output .= '</div>' . "\n";
	}
	
    $output .= '</a></blockquote>';
	return $output;
}

//add code to list (used in the_content_filter)
add_shortcode_to_list("nav-panel");
add_shortcode_to_list("blockquote-nav");

//add code to wordpress itself
add_shortcode('nav-panel', 'nav_panel_att');
    
//Look to phase out this old name
add_shortcode('blockquote-nav', 'nav_panel_att');

?>