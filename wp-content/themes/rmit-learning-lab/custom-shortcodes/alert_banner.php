<?php

//-----------------------------
//	alert_banner_att

//	Creates an alert banner in the bootstrap style

//	args:		$atts:

//  alert:      The message, html can be included

// called from:  video_att

//  shortcode:  [alert-banner]

//	usage:			
//  [alert-banner mess='<strong>Warning!</strong> Message goes here' /]

//  Expected output
//<div class="alert alert-danger alert-dismissible">
//    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
//    <strong>Warning!</strong> Message here.
//</div>
    
function alert_banner_att($atts) {
    
    return doAlertBanner($atts['alert']);
}
    
//-----------------------------
//	alert_banner_att

//	Creates an alert banner in the bootstrap style

//	args:		$atts:

//  alert:      The message, html can be included

// called from: video_att
//              aler_banner_att

function doAlertBanner($content)
{ 
    $output = '<div class="alert alert-danger alert-dismissible">'  . "\n";
    $output .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' . "\n";
    
    //strips out <script> tags etc.
    $output .= wp_kses_post($content);
    $output .= '</div>';

    return $output;    
}

//add code to list (used in the_content_filter)
add_shortcode_to_list("alert-banner");

//add code to wordpress itself
add_shortcode('alert-banner', 'alert_banner_att');

?>