<?php

//-----------------------------
//	transcript_accordion_att

//	Creates a transcript accordion

//	args:		$content - html content in the accordion 
//              $atts - attributes as follows:

//  $atts:      title  Title of the accordion (optional - defaults to "Transcript")
//				size	If set to "wide", transcript is set to 100% width (optional)
//              id      Allows an id to be directly assigned to the button. 
//                      required to get "skip to text only content" to work 
//              classes     adds whatever is placed in here into the figure class 
//                          can be useful to adjust margins - margin-top-sm (most definitely optional)

//	calls:		doAccordion - with arg "transcript"

//  shortcode:  [transcript]

//	usage:			
//  [transcript]<p>Transcript content (wrap in p tags recommended).</p>[/transcript]

//  Expected output
//<div class="accordion-item transcript">
//	<p class="accordion-header" id="head-transcript-rand-num">
//	<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#body-transcript-rand-num" aria-expanded="false" aria-controls="body-transcript-9636">Transcript</button>
//	</p>
//	<div id="body-transcript-rand-num" class="accordion-collapse collapse " aria-labelledby="head-transcript-rand-num">
//		<div class="accordion-body">
//			<p>Transcript content (wrap in p tags recommended).</p>
//		</div>
//	</div>
//</div>

function transcript_accordion_att($atts, $content = null) {
	return doAccordion("transcript", $atts, $content);
}


//-----------------------------
//	bootstrap_accordion_att

//	Creates an accordion, suitable to hoyse large amounts of content

//	args:		$content - html content in the accordion 
//              $atts - attributes as follows:

//  $atts:      title   Title of the accordion (optional - defaults to "Transcript")
//              open    Set to true to open accordion by default.
//              id      Set an id for the button, allows accordion to be targeted by a skip content link

//	calls:		doAccordion - with arg "regular"

//  shortcode:  [ll-accordion]

//	usage:			
//  [bs-accordion title="My accordion"]<p>Accordion content (wrap in p tags recommended).</p>[/bs-accordion]

//  Wrap multiple accordions in a div: 
//  <div class="accordion" id="accordion-example">
//  [bs-accordion title="My accordion 1"]<p>Transcript content</p>[/bs-accordion]
//  [bs-accordion title="My accordion 2"]<p>Transcript content</p>[/bs-accordion]
//  </div>

//  Expected output:
//<div class="accordion-item transcript">
//    <p class="accordion-header" id="Transcript-head">
//      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#Transcript-body" aria-expanded="false" aria-controls="Transcript-body">
//        Transcript
//      </button>
//    </p>
//    <div id="Transcript-body" class="accordion-collapse collapse" aria-labelledby="Transcript-head">
//      <div class="accordion-body"><p>Transcript content</p></div>
//    </div>
//</div>

function bootstrap_accordion_att($atts, $content = null) {
	return doAccordion("regular", $atts, $content);
}



//-----------------------------
//	doAccordion

//	Outputs accordion code

//	Called from:	bootstrap_accordion_att
//                  transcript_accordion_att

//	args:			$type - Can be "transcript" or "regular"
//                  $atts - attribute object from shortcode functions
//					$content - html content in the accordion

//	calls:			generate_id

//	usage:			return doAccordion("transcript", $atts, $content);

function doAccordion($type, $atts, $content = null) {
    //If title attribute is omitted, default to "Transcript"
    $default = array(
        'title' => 'Transcript',
		'size' => '',
        'open' => '',
        'id' => '',
        'heading-tag' => '',
        'classes' => ''
    );
    
    //merges user-defined attributes with a set of default values ($default)
    $a = shortcode_atts($default, $atts);
    
    //grab content from within the two shortcode tags
    $content = do_shortcode($content);
    
    //generate a unique id for head and body sections of the accordion
    $headId = generate_id($a['title'], "head");
    $bodyId = generate_id($a['title'], "body");
    
    //default state is h2
    $labelTag = 'h2';
    $extraClass = '';
    
    //these vars control whether accordion is open or not. It's closed by default
    $buttonState = 'collapsed';
    $ariaExpanded = 'false';
    $bodyState  = '';
    $id = '';
    
    //if we have a attribute of open=true, set variable to make this happen
    if($a['open'] == 'true')
    {
        $buttonState = '';
        $ariaExpanded = 'true';
        $bodyState = 'show';
    }
    
    //if we have an id add it.
    if($a['id'] != '')
    {
        $id = 'id="' . $a['id'] .'"';    
    }
    
    //if type is transcript, adjust some of the tags to style differently
    if ($type == 'transcript') {
        $labelTag = 'p';
        $extraClass = 'transcript ';
		
		//this additional class stratches the transcript accordion to 100% of container width
		if($a['size'] == 'wide' || $a['size'] == 'full-width')
		{
			$extraClass .= ' transcript-full-width ';
		}
    }
    
    //if heading tag has a value, update $labelTag, otherwise it will retain h2 as default
    if($a['heading-tag'] != '')
    {
        // Sanitize the heading level to prevent invalid HTML
        $allowed_headings = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6');
        if (in_array($a['heading-tag'], $allowed_headings)) {
            $labelTag = $a['heading-tag'];
        }
    }
	
	//if there's anything in clesses, add it (don't document this, for web devs only)
    if($a['classes'] != '') { 
        $extraClass .= $a['classes']; 
    } 
    
    //output the html markup
    $output = '';
    
    $output .= '<div class="accordion-item ' . $extraClass . '">' . "\n";
    $output .= '<' . $labelTag .' class="accordion-header" id="' . $headId .'">' . "\n";
    $output .= '<button class="accordion-button ' . $buttonState . '" type="button" data-bs-toggle="collapse" data-bs-target="#' . $bodyId . '" aria-expanded="'. $ariaExpanded . '" aria-controls="' . $bodyId . '" ' . $id . '>';    
    $output .= $a['title'];
    $output .= '</button>' . "\n";
    $output .= '</' . $labelTag . '>' . "\n";
    $output .= '<div id="' . $bodyId . '" class="accordion-collapse collapse ' . $bodyState . '" aria-labelledby="' . $headId . '">' . "\n";
    $output .= '<div class="accordion-body">' . $content . '</div></div></div>';

    return $output;
}



//-----------------------------
//	generate_id

//	Generate unique id

//	Called from:	doAccordion
//                  Note: these ids will change every time the page is loaded

//	args:			$string  the title of the accordion
//                  $prefix either "head" or "body"

//	usage:			$headId = generate_id($a['title'], "head");

//	Expected output
//	"head-myTitle-4035"

function generate_id($string, $prefix) {
    //Make string lower case
    $lowercaseString = strtolower($string);
    
    //Replace spaces with hypens
    $hyphenatedString = str_replace(' ', '-', $lowercaseString);
    
    //add a random number on the end to ensure uniqueness (important for multiple transcript accordions)
    $randomNumber = rand(1000, 9999);
    return $prefix . '-' . $hyphenatedString . '-' . $randomNumber;
}

//add code to list (used in the_content_filter)
add_shortcode_to_list("ll-accordion");
add_shortcode_to_list("transcript");

//add code to wordpress itself
add_shortcode('ll-accordion', 'bootstrap_accordion_att');
add_shortcode('transcript', 'transcript_accordion_att');

//Look to phase out these older names

//add code to list (used in the_content_filter)
add_shortcode_to_list("transcript-accordion");
add_shortcode_to_list("bs-accordion");

add_shortcode('transcript-accordion', 'transcript_accordion_att'); 
add_shortcode('bs-accordion', 'bootstrap_accordion_att');

// Point lightweight-accordion to transcript_accordion_att temporarily. 
// Look to find and replace "lightweight-accordion" with "transcript" over time.

add_shortcode_to_list("lightweight-accordion");

add_shortcode('lightweight-accordion', 'transcript_accordion_att'); 

?>