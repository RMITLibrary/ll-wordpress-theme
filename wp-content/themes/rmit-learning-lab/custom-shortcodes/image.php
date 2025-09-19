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
        'classes' => ''
    );
    $a = shortcode_atts($default, $atts);
    $content = do_shortcode($content);
            
    //START Build figure tag
    $figureTag = '<figure class="';
            
    //if align = center or centre, add class to align image to the centre
    if ($a['align'] == 'center' || $a['align'] == 'centre') {
        $figureTag .= 'centre '; 
    }
      
    //check for border
    if($a['border'] == 'true') { 
        $figureTag .= 'my-border '; 
    } 

    //check for shadow
    if($a['shadow'] == 'true') { 
        $figureTag .= 'drop-shadow '; 
    } 

    //check for rounded corners
    if($a['rounded'] == 'true') { 
        $figureTag .= 'round-corners '; 
    } 
    
	//check for floated
    if($a['float'] == 'true' || $a['float'] == "right") {
		if($a['size'] == 'sm')
        {
			$figureTag .= 'float-right-sm ';  
        }
        else
        {
            $figureTag .= 'float-right ';  
        }   
    } 
	
	//check for hide-sm
    if($a['hide-sm'] == 'true') {
		$figureTag .= 'hide-sm ';  
    } 
            
    //if portrait is, add a class
    if($a['portrait'] == 'true') { 
        
        if($a['size'] == 'sm')
        {
            $figureTag .= 'portrait-small '; 
        }
        else
        {
            $figureTag .= 'portrait '; 
        } 
    }
    //add size attribute if required and portrait or float not specified       
    else if($a['size'] != '' && $a['float'] == '') { 

        if($a['size'] == 'wide')
        {
            $figureTag .= 'wide '; 
        }
        else
        {
            $figureTag .= 'img-width-' . $a['size'] . ' '; 
        } 
    }
    
    
    //if there's anything in clesses, add it (don't document this, for web devs only)
    if($a['classes'] != '') { 
        $figureTag .= $a['classes'] . ' '; 
    } 
    
    //If caption-id has a value, add it as an aria-labelledby
    if($a['attribution-id'] != '') { 
        $figureTag .= '" aria-labelledby="' . $a['attribution-id']; 
    } 
    
    $figureTag .= '">';
    //END Build figure tag 
            
    //Wrapper div not required for most cases
    $wrapperDiv = '';
    $wrapperDivEnd = '';
    
    //if aspect is portrait, create wrapper div code
    if($a['portrait'] == 'true') { 
        $wrapperDiv = '<div class="image-caption-wrapper">'; 
        $wrapperDivEnd = '</div>';
    }
            
    $figCaptionTag = '';
    
    if($a['caption'] != '') { 
        //check to see if we want to add the default attribution
        $caption = addAttribution($a['caption']);

        //add in classs to incresae gap if attribute is set
        if($a['caption-gap'] != '') {
            $figCaptionTag .= '<figcaption class="gap-lg">' . $caption . '</figcaption>' . "\n"; 
        }
        else
        {
            $figCaptionTag .= '<figcaption>' . $caption . '</figcaption>' . "\n"; 
        }
    }       
             
            
    //Build <img> tag with alt tag, add border if present
    $imageTag = '<img src="' . $a['url'] . '" alt="' . $a['alt'] . '" />' . "\n";
           
 
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