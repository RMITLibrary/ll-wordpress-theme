<?php

//-----------------------------
//	video_att
//
//	Creates an image, options for portrait, caption and transcript for the landing page

//	args:		$content - description of the landing page (max 280 characters)
//              $atts - attributes as follows:

//  $atts:      url         Absolute path to video - eg https://www.youtube.com/embed/w_IEpVVdNrE
//              caption     Attribution for the video (optional)  
//              align       center or centre -  to align img to centre (optional)
//              alert       html message for an alert banner (optional)

//				aspect		4-3, 3-2, square - default is 16-9
//              portrait    true - does 9-16. If omitted landscape is default (optional)
//              classes     adds whatever is placed in here into the figure class 
//                          can be useful to adjust margins - margin-top-sm (most definitely optional)

//  shortcode:  [ll-video][/ll-video]

//	usage:			
//  [ll-image img='' alt='Alt tag for the image' caption='Caption here' aspect='portrait' left='true' border='true' size='sm'][/ll-image]
//
//  [ll-video url='']
//      [transcript-accordion]<p>Transcript content.</p>[/transcript-accordion]
//  [/ll-video]

//  Expected output
//<figure class="video">
//	<div class="responsive-video">
//		<iframe src="https://www.youtube.com/embed/video-id" frameborder="0" 
//		allowfullscreen=""></iframe>
//	</div>
//	<figcaption>An example caption for this image.</figcaption>
//	<div class="accordion-item transcript">
//		<!-- lots of additional accordion code goes here -->	
//	</div>
//</figure>

function video_att($atts, $content = null) {
    $default = array(
        'url' => '',
        'left' => '',
        'caption' => '',
		'align' => '',
        'alert' => '',
		'aspect' => '',
		'portrait' => '',
        'classes' => ''   
    );
    $a = shortcode_atts($default, $atts);
    $content = do_shortcode($content);
        
    $output = '<figure class="';

	 //if portrait is true, add a class
    if($a['portrait'] == 'true') { 
        $output .= 'video-portrait ';  
    }
	else if($a['aspect'] == '4-3') {
		$output .= 'video-4-3 ';
	}
	else if($a['aspect'] == '3-2') {
		$output .= 'video-3-2 ';
	}
	else if($a['aspect'] == 'square' || $a['aspect'] == '1-1') {
		$output .= 'video-square ';
	}
	else if($a['aspect'] == '3-2') {
		$output .= 'video ';
	}
	
	//if align = center or centre, add class to align image to the centre
    if ($a['align'] == 'center' || $a['align'] == 'centre') {
        $output .= 'centre'; 
    }
	
	//if there's anything in clesses, add it (don't document this, for web devs only)
    if($a['classes'] != '') { 
        $output .= $a['classes'] . ' '; 
    } 
	
    $output .= '">' . "\n";

    //if there's an alert message, call alert_banner_att to do the mark-up
    if($a['alert'] != '') { 
        $output .= doAlertBanner($a['alert']);   
    }      
    
	if($a['portrait'] == 'true') { 
        $output .= '<div class="video-wrapper">' . "\n";  
    }
    
    //format url to https://www.youtube.com/embed/video-id
    $url = format_youtube_video_url($a['url']);
	
    $output .= '<div class="responsive-video">' . "\n"; 
    $output .= '<iframe src="' . $url . '" frameborder="0" allowfullscreen=""></iframe>' . "\n";
            
    $output .= '</div>' . "\n"; 
	

    //If caption exists
    if($a['caption'] != '') { 
        $output .= '<figcaption>' . $a['caption'] . '</figcaption>' . "\n"; 
    }  
	
	if($a['portrait'] == 'true') { 
		//close wrapper div
        $output .= '</div>' . "\n";  
    }
     
        
    //If $content exists, there's a transcript, add output from [transcript-accordion]
	if($content != null) {
		$output .= $content;
	}   
        
    $output .= '</figure>' . "\n";
            
    return $output; 
}

//-----------------------------
//	format_youtube_video_url($url)
//
//	We want urls in the form -  https://www.youtube.com/embed/video-id
//  By default, share gives -   https://youtu.be/video-id or silar
//  This function extracts video id and reformats to correct url

//	args:		$url - youtube url
//  returns:    url in the form https://www.youtube.com/embed/video-id

function format_youtube_video_url($url) {
    // Parse the URL to get its components
    $parsed_url = parse_url($url);

    // Check if the host is youtu.be
    if (isset($parsed_url['host']) && $parsed_url['host'] === 'youtu.be') {
        // Extract the path and remove the leading slash
        $video_id = ltrim($parsed_url['path'], '/');
        
        $formatted_url = 'https://www.youtube.com/embed/' . $video_id;
        return $formatted_url;
    }
    else
    {
        return $url;
    }
}

//add code to list (used in the_content_filter)
add_shortcode_to_list("ll-video");

//add code to wordpress itself
add_shortcode('ll-video', 'video_att');

?>