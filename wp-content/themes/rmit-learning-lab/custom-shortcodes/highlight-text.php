<?php

//-----------------------------
//	highlight_text_att

//	Outputs a css grid, a key list and a content area for highlighted text
//

//	args:		$content - the html markup whicvh will be highlighted by [hl]
//              $atts - attributes as follows:

//  $atts:      key     string seperated by pipes (|), then values sepeated by  colons (:)
//              screen-reader         lg or true (makes gap 40px at larger screen sizes)
//              size        lg, md, sm (640px, 400px, 296px) - sets width of grid (optional, full width ids the default)

//  shortcode:  [ll-grid][/ll-grid]

//	usage:		[highlight-text key="1:item one|2:item two|3: item three"]<p>[hl id="1"]Highlight[/hl] Other content here</p>[/highlight-text] 

// Expected output
// <div class="highlight-text">
//     <div class="key">
//         <ul aria-hidden="true">
//             <li class="highlight-1">1 item one</li>
//             <li class="highlight-2">2 item two</li>
//             <li class="highlight-3">3 item three</li>
//         </ul>
//     </div>
//     <div class="content">
//          <p><span class="highlight-1">Highlight<sup aria-hidden="true">1</sup>
//          <span class="visually-hidden">Screen reader users, this is an example of a highlight.</span></span> 
//          Other content here</p>
//     </div>
// </div>

function highlight_text_att($atts, $content = null) {
    $default = array(
        'key' => '',
        'screen-reader' => '',
        'one-column' => '',
        'classes' => ''
    );
 
    $a = shortcode_atts($default, $atts);
    $content = do_shortcode($content);

    $output = '';

    //add in hidden screen readr only text if available
    if ($a['screen-reader'] != '') { 
        $output .= '<p class="visually-hidden">' . $a['screen-reader'] . '</p>';

    }

    $tag = '<div class="highlight-text ';

    //if one column equals 'true', the add class hl-one-column
    if($a['one-column'] == 'true') { 
        $tag .= 'hl-one-column '; 
    } 

    //if there's anything in clesses, add it (don't document this, for web devs only)
    if($a['classes'] != '') { 
        $tag .= $a['classes'] . ' '; 
    } 

    $tag .= '">';


    $key = '';
    //Format key, check if the 'key' attribute is not empty
    if ($a['key'] != '') { 
        // Split the input string into an array using the pipe delimiter
        $items = explode('|', $a['key']);
        
        // Initialize the output string with opening div and ul tags
        $key = '<div class="key"><ul aria-hidden="true">';
        
        // Loop through each item in the array
        foreach ($items as $item) {
            // Split each item into number and text parts, limiting to 2 parts to handle colons in values
            list($number, $text) = explode(':', $item, 2);
            
            // Append each list item to the output string
            $key .= '<li class="highlight-' . $number . '">' . $number . ' ' . $text . '</li>';
        }
        
        // Close the ul and div tags
        $key .= '</ul></div>';
    }

    //add key markup
    $output .= $tag . $key;

    //add content
    $output .= '<div class="content">' . $content . '</div>';

    // Complete highligh-text tag
    $output .= '</div>';

    // Return the complete output
    return $output;
}



//-----------------------------
//	highlight_att

//	Outputs a span with highlight class added, plus superscript and screen reader text
//

//	args:		$content - the html markup to be put into the grid
//              $atts - attributes as follows:

//  $atts:      id     1,2,3,4,5,6 - colour of highlight, superscript
//              screen-reader   text to give screen reader users the context of what is visually highlighted

//  shortcode:  [hl][/hl]

//	usage:		[hl id="1" screen-reader="Screen reader users, this is an example of highlighted content."]Highlighted content[/hl] 

//  Expected output

// <span class="highlight-1">Highlighted content
// <sup aria-hidden="true">1</sup>
// <span class="visually-hidden">Screen reader users, this is an example of highlighted content.</span>
// </span>


function highlight_att($atts, $content = null) {
    $default = array(
        'id' => '',
        'screen-reader' => '',
        'superscript' => ''
    );
    $a = shortcode_atts($default, $atts);
    $content = do_shortcode($content);

    $output = '<span class="highlight-';

    //if no id, apply highlight-1 but with no superscript
    if ($a['id'] == '') {
        $output .= '1';
    }
    else
    {
        $output .= $a['id'] . '">';
    }

    //add the content itself
    $output .= $content;

    //if id is set, add the superscript
    if ($a['id'] != '' && $a['superscript'] != 'false') {
        //apply superscript if id is set
        $output .= '<sup aria-hidden="true">' . $a['id'] . '</sup>';
    }

    //if screen-reader text is set, add it inot visually-hisdden span
    if ($a['screen-reader'] != '') {
        //apply superscript if id is set
        $output .= '<span class="visually-hidden">' . $a['screen-reader'] . '</span>';
    }
    
    //close span
    $output .= '</span>';
    $output = strip_tags_before_echo($output);

    return $output;
}

//add code to list (used in the_content_filter)
add_shortcode_to_list("hl");
add_shortcode_to_list("highlight-text");

//add code to wordpress itself
add_shortcode('hl', 'highlight_att');
add_shortcode('highlight-text', 'highlight_text_att');

?>