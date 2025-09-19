<?php

//-----------------------------
//	title_icon
//
//	Creates a heading element with an icon and optional dark mode styling.
//	args:		$content - text within the heading
//              $atts - attributes as follows:
//
//  $atts:      heading-tag    Specifies the heading level (h1-h6), default is 'h2'.
//              heading-size   Adds a classs to adjust physical size (h1-h6).
//              type           CSS class name for distinguishing styles.
//							   aboriginal-flag, torres-strait-flag, quiz-icon are 
//			   				   predefined values, images etc. coded into css
//
//              img            (very much optional) URL of the icon image for the light mode.
//              img-dark       (very much optional) URL of the icon image for the dark mode.
//              alt            (optional) Alternate text for the icon image.
//
//  shortcode:  [title-icon]
//	usage:	
//
//  [title-icon heading-tag='h3' type='aboriginal-flag']Your heading content[/title-icon]
//	Expected output:
//  <h3 class="title-icon aboriginal-flag">Your heading here</h3>
//		
//  [title-icon heading-tag='h3' type='my-type' img='https://path.to/light-icon.svg' img-dark='https://path.to/dark-icon.svg' alt='Quiz Icon']Your Heading Content[/title-icon]
//	Expected output:
//
//  <style>
//      .quiz-icon::before { 
//          background-image: url("https://path.to/light-icon.svg"); 
//          content: '' / 'Quiz Icon'; 
//      } 
//      @media (prefers-color-scheme: dark) { 
//          .quiz-icon::before { 
//              background-image: url("https://path.to/dark-icon.svg"); 
//          } 
//      }
//  </style>
//  <h3 class="title-icon my-type">Your Heading Content</h3>


function title_icon($atts, $content = null) {
	$default = array(
        'heading-tag' => '',
        'heading-size' => '',
        'type' => '',
		'img' => '',
		'img-dark' => '',
        'alt' => '',
        'id' => ''
    );
    
    //merges user-defined attributes with a set of default values ($default)
    $a = shortcode_atts($default, $atts);
    
    //grab content from within the two shortcode tags
    $content = do_shortcode($content);
	
    $headingTag = 'h2';

    if($a['heading-tag'] != '')
    {
        // Sanitize the heading level to prevent invalid HTML
        $allowed_headings = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6');
        if (in_array($a['heading-tag'], $allowed_headings)) {
            $headingTag = $a['heading-tag'];
        }
    }

    $myId ='';
    if($a['id'] != '')
    {
        $myId = ' id="' . $a['id'] . '" ';
    }

    $type = $a['type'];
    $headingSize = $a['heading-size'];

    $heading .= '<' . $headingTag . ' class="title-icon ' . $type . ' ' . $headingSize . '"' . $myId .'>';
	$heading .= $content . '</' . $headingTag . '>';

	$style = '';

    if($a['alt'] != '' && $a['img'] == '') {
        $style = '<style>';
		$style .= '.' . $a['type'] .'::before { ' ;
        $style .= "content: '' / '" . $a['alt'] . "'; ";
		$style .= ' } ';
        $style .=  '</style>';
    }
	elseif($a['img'] != '') {
		
		$style = '<style>';

		$style .= '.' . $a['type'] .'::before { background-image: url("' . $a['img'] . '"); ' ;

		if($a['alt'] != '') {
			$style .= "content: '' / '" . $a['alt'] . "'; ";
		}

		$style .= ' } ';

		if($a['img-dark'] != '') {
			$style .= '@media (prefers-color-scheme: dark) { .' . $a['type'] .'::before { background-image: url("' . $a['img-dark'] . '"); } }' ;
		}

		$style .=  '</style>';
	}
    
    $output = $style . $heading .  "\n";
	return $output;
}

//add code to list (used in the_content_filter)
add_shortcode_to_list("title-icon");

//add code to wordpress itself
add_shortcode('title-icon', 'title_icon');

?>