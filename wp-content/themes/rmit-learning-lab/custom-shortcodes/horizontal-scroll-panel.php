<?php

//-----------------------------
//	hscroll_panel_att

//	Outputs a horizontally scrollable panel. For wid content (equations, tables) on small screens.
//
//  shortcode:  [hscroll-panel][/hscroll-panel]

//	usage:		[hscroll-panel]Wide content goes here[/hscroll-panel]

//  Expected output
// <div class="hscroll">
//   Content here
// </div>


function hscroll_panel_att($atts, $content = null) {
    // $default = array(
    //     'id' => 'my-attribution'
    // );
    
    if($content == null) {
        $content = '<strong>[hscroll-panel]</strong> Error, add some content to scroll horizontally!';
    }
    
   // $a = shortcode_atts($default, $atts);
    
    //grab content from within the two shortcode tags
    $content = do_shortcode($content);
	
    $tag = '<div class="hscroll">' . "\n" . $content . "\n" . '</div>';

    return $tag;
}

//add code to list (used in the_content_filter)
add_shortcode_to_list("hscroll");

//add code to wordpress itself
add_shortcode('hscroll', 'hscroll_panel_att');

?>