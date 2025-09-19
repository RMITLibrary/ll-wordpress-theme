<?php

//-----------------------------
//	ll_code_example_att

//	Outputs a code example. 
//  Need to escape html and shortcodes [ - &lbrack; and ] - &rbrack; at editor level
//

//  args:       wrap="true" - sets the code block to wrap rather than scroll. //                            This is recommended for shortcode examples where 
//                            line breaks can break the code 

//  shortcode:  [ll-code][/ll-code]

//	usage:		[ll-code wrap="true"]Code example goes here[/ll-code] 

//  Expected output - fill in later
//<div class="highlight wrap-code">
//    <pre>
//        <code>[ll-video url="https://path.to/video-embed"][transcript]&lt;p&gt;Transcript content here.&lt;/p&gt;[/transcript][/ll-video]</code>
//    </pre>
//</div>


function ll_code_example_att($atts, $content = null) {
    $default = array(
        'wrap' => ''
    );
    $a = shortcode_atts($default, $atts);
	
    $tag = '<div class="highlight"><pre><code>';
    
    if($a['wrap'] != '') {
		$tag = '<div class="highlight wrap-code"><pre><code>';
	}
    
    // Remove <br> tags from the content
    $content = str_replace(array('<br>', '<br />'), '', $content);
	
	//Replace random curly quotes with straight ones (oh, wordpress)
	//$content = str_replace(array('”', '″', '“'), '&quot;', $content);
    
    return $tag . $content . '</code></pre></div>';
}

//add code to list (used in the_content_filter)
add_shortcode_to_list("ll-code");

//add code to wordpress itself
add_shortcode('ll-code', 'll_code_example_att');

?>