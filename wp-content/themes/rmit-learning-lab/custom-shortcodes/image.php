<?php 
//-----------------------------
//	image_att

//	Creates an image, options for portrait, caption and transcript for the landing page

//	args:		$content - description of the landing page (max 280 characters)
//              $atts - attributes as follows:

//  $atts:      url         Absolute path to image
//              alt         Alt tag for the above image
//              caption     Attribution for the image (optional) 

//              caption-id  If multiple images use the same attribution  
//                          this id will point to it via aria-labelledBy

//              align       center or centre -  to align img to centre (optional)
//              size        wide, md, sm - sets size of image (optional)
//              loading     lazy (default), eager or auto (optional)

//              portrait    true - if omitted landscape is default (optional)
//              
//              border      true  - Adds a 1px border (optional)
//              shadow      true  - Adds a dropshadow (optional)
//              rounded     true  - Adds rounded corners (optional)
//              classes     adds whatever is placed in here into the figure class 
//                          can be useful to adjust margins - margin-top-sm (most definitely optional)


//  shortcode:  [ll-image][/ll-image]

//	usage:			
//  [ll-image url='https://path.to/image' alt='Alt tag for the image' caption='Caption here' portrait='true' centre='true' border='true' size='sm'][/ll-image]
//
//  [ll-image url='https://path.to/image' shadow='true' rounded='true' alt='Alt tag for the image']
//      [transcript-accordion]<p>Transcript content.</p>[/transcript-accordion]
//  [/ll-image]

//    Expected output
//    <figure>
//        <img src="my-image.jpg" alt="An example image" />
//        <figcaption>An example caption for this image.</figcaption>
//        <div class="accordion-item transcript"> 
//            <!-- lots of additional accordion code goes here -->	
//        </div>
//    </figure>
//
//    <figure class="portrait">
//        <div class="image-caption-wrapper">
//            <img src="my-image.jpg" alt="An example image" />
//            <figcaption>An example caption for this image.</figcaption>
//        </div>
//        <!-- START accordion item -->
//        <div class="accordion-item transcript">
//            <!-- lots of additional accordion code goes here -->
//        </div>
//        <!-- END accordion item -->
//    </figure>

function image_att ($atts, $content = null) {
    $default = array(
        'alt' => '',
        'url' => '',
        'border' => '',
        'shadow' => '',
        'rounded' => '',
        'align' => '',
        'portrait' => '',
        'size' => '',
        'caption' => '',
		'float' => '',
		'hide-sm' => '',
        'attribution-id' => '',
        'caption-gap' => '',
        'classes' => '',
        'loading' => 'lazy'
    );
    $a = shortcode_atts($default, $atts);
    $content = wp_kses_post(do_shortcode($content));

    $figure_classes = array();

    if ($a['align'] === 'center' || $a['align'] === 'centre') {
        $figure_classes[] = 'centre';
    }

    if ($a['border'] === 'true') {
        $figure_classes[] = 'my-border';
    }

    if ($a['shadow'] === 'true') {
        $figure_classes[] = 'drop-shadow';
    }

    if ($a['rounded'] === 'true') {
        $figure_classes[] = 'round-corners';
    }

    if ($a['float'] === 'true' || $a['float'] === 'right') {
        $figure_classes[] = $a['size'] === 'sm' ? 'float-right-sm' : 'float-right';
    }

    if ($a['hide-sm'] === 'true') {
        $figure_classes[] = 'hide-sm';
    }

    if ($a['portrait'] === 'true') {
        $figure_classes[] = $a['size'] === 'sm' ? 'portrait-small' : 'portrait';
    } elseif ($a['size'] !== '' && $a['float'] === '') {
        $figure_classes[] = $a['size'] === 'wide' ? 'wide' : 'img-width-' . sanitize_html_class($a['size']);
    }

    if (!empty($a['classes'])) {
        $extra_classes = preg_split('/\s+/', $a['classes'], -1, PREG_SPLIT_NO_EMPTY);
        foreach ($extra_classes as $class_token) {
            $figure_classes[] = sanitize_html_class($class_token);
        }
    }

    $figure_attributes = array();
    if (!empty($figure_classes)) {
        $figure_attributes[] = 'class="' . esc_attr(implode(' ', array_filter($figure_classes))) . '"';
    }

    if (!empty($a['attribution-id'])) {
        $figure_attributes[] = 'aria-labelledby="' . esc_attr($a['attribution-id']) . '"';
    }

    $figureTag = '<figure' . (!empty($figure_attributes) ? ' ' . implode(' ', $figure_attributes) : '') . '>';

    //Wrapper div not required for most cases
    $wrapperDiv = '';
    $wrapperDivEnd = '';
    
    //if aspect is portrait, create wrapper div code
    if($a['portrait'] == 'true') { 
        $wrapperDiv = '<div class="image-caption-wrapper">'; 
        $wrapperDivEnd = '</div>';
    }
            
    $figCaptionTag = '';

    if ($a['caption'] !== '') {
        $caption = wp_kses_post(addAttribution($a['caption']));

        if ($a['caption-gap'] !== '') {
            $figCaptionTag .= '<figcaption class="gap-lg">' . $caption . '</figcaption>' . "\n";
        } else {
            $figCaptionTag .= '<figcaption>' . $caption . '</figcaption>' . "\n";
        }
    }
             
            
    //Build <img> tag with alt tag, add border if present
    $loading_mode = strtolower($a['loading']);
    $allowed_loading_modes = array('lazy', 'eager', 'auto');
    if (!in_array($loading_mode, $allowed_loading_modes, true)) {
        $loading_mode = 'lazy';
    }

    $imageTag = '<img src="' . esc_url($a['url']) . '" alt="' . esc_attr($a['alt']) . '" loading="' . esc_attr($loading_mode) . '" decoding="async" />' . "\n";
           
 
    //Start output phase       
    $output = '';
    $output .= $figureTag . "\n";
    $output .= $wrapperDiv . "\n";
    $output .= $imageTag . "\n";
    $output .= $figCaptionTag;
    $output .= $wrapperDivEnd . "\n"; 
    
    //If $content exists, there's a transcript, add output from [transcript-accordion] 
	if($content != null) {
		$output .= $content;
	}        
                  
    $output .= '</figure>' . "\n";
            
    return $output; 
    
    /*$debug = '<pre><code>';
    $debug .= $imageTag;
    
    //$debug .= 'img: ' . $a['img'] . "\n";
    $debug .= 'alt: ' . $a['alt'] . "\n";
    $debug .= 'left: ' . $a['left'] . "\n";
    $debug .= 'border: ' . $a['border'] . "\n";
    $debug .= 'size: ' . $a['size'] . "\n";
    $debug .= '</code></pre>';
    
    
    return $debug;*/
}

function addAttribution($input) {
    // Define the attribution string
    $attribution = ", by <a href='https://rmit.edu.au/'>RMIT</a>, licensed under <a href='https://creativecommons.org/licenses/by/4.0/'>CC BY-NC 4.0</a>";
    
    // Check if the input string contains "|attrib"
    if (strpos($input, '|attrib') !== false) {
        // Remove "|attrib" from the input string
        $input = str_replace('|attrib', '', $input);
        
        // Append the attribution string
        $input .= $attribution;
    }
    
    return $input;
}

//add code to list (used in the_content_filter)
add_shortcode_to_list("ll-image");

//add code to wordpress itself
add_shortcode('ll-image', 'image_att');

?>
