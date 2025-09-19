<?php

//-----------------------------
//	ll_grid_att

//	Outputs a a css grid, avoiding blank p and br tags. 
//

//	args:		$content - the html markup to be put into the grid
//              $atts - attributes as follows:

//  $atts:      columns     3 or 4 (optional, 2 is the default)
//              gap         lg or true (makes gap 40px at larger screen sizes)
//              size        lg, md, sm (640px, 400px, 296px) - sets width of grid (optional, full width ids the default)

//  shortcode:  [ll-grid][/ll-grid]

//	usage:		[ll-grid wrap="true"]Markup goes here[/ll-grid] 

//  Expected output
//<div class="my-grid my-grid-3up grid-width-lg">
//    <div><!-- content --></div>
//    <div><!-- content --></div>
//    <div><!-- content --></div>
//</div>

function ll_grid_att($atts, $content = null) {
    $default = array(
        'columns' => '',
        'gap' => '',
        'size' => '',
        'classes' => ''
    );
    $a = shortcode_atts($default, $atts);

    // Ensure content is processed correctly
    $content = do_shortcode(shortcode_unautop($content));

    $tag = '<div class="my-grid ';

    // Apply optional class for 3 or 4 columns
    if ($a['columns'] == '3') {
        $tag .= ' my-grid-3up ';
    } elseif ($a['columns'] == '4') {
        $tag .= ' my-grid-4up ';
    }

    // Apply optional class for large gap
    if ($a['gap'] == 'lg' || $a['gap'] == 'true') {
        $tag .= ' gap-lg';
    }

    // Apply optional class for width classes. Note only sm, md and lg work normally
    //other sizes can be used for grids within an hscroll
    if ($a['size'] == 'md') {
        $tag .= ' grid-width-md ';
    } elseif ($a['size'] == 'sm') {
        $tag .= ' grid-width-sm ';
    } elseif ($a['size'] == 'xs') {
        $tag .= ' grid-width-xs ';
    } elseif ($a['size'] == 'lg') {
        $tag .= ' grid-width-lg ';
    } elseif ($a['size'] == 'xl') {
        $tag .= ' grid-width-xl ';
    } elseif ($a['size'] == 'xxl') {
        $tag .= ' grid-width-xxl ';
    }

    //if there's anything in clesses, add it (don't document this, for web devs only)
    if($a['classes'] != '') { 
        $tag .= $a['classes']; 
    } 

    // Complete tag
    $tag .= '">';

    // Return the complete output
    return $tag . $content . '</div>';
}


//-----------------------------
//	img_text_att

//	Outputs a a css grid, avoiding blank p and br tags. 
//

//	args:		$content - the html markup to be put into the grid
//              $atts - attributes as follows:

//  $atts:      align-top           true to align all items to the top (middle by default)
//              icon                if true, image sized to 160px square, aligned left
//              url                 Absolute path to image
//              alt                 Alt tag for the above image
//              attribution-id      No room for a caption so link to attribution, use this in conjuntion with [attribution[ shortcode. (ALL THIS NEEDS MORE DOCUMENTATION)]

//  shortcode:  [img-text][/img-text]

//	usage:		[img-text align-top="true" url="https://path.to/image.jpg" alt=""]Content goes here[/img-text] 

//  Expected output
// <div class="img-text align-items-top">
// 	<figure>
// 		<img src="my-image.jpg" alt="An example image" />
// 	</figure>
// 	<div class="content-text">
// 		<h3>Heading</h3>
// 		<p>Text to be placed alongside image.</p>
// 		<p>Text to be placed alongside image.</p>
// 	</div>
// </div>

function img_text_att($atts, $content = null) { 
    $default = array(
        'align-top' => '',
        'icon' => '',
        'url' => '',
        'alt' => '',
        'attribution-id' => '',
        'classes' => ''
    );

    $a = shortcode_atts($default, $atts);

    // Ensure content is processed correctly
    $content = do_shortcode(shortcode_unautop($content));

    $tag = '<div class="img-text ';

    if ($a['icon'] != '') {
        $tag = '<div class="icon-text ';
    }

    if ($a['align-top'] != '') {
        $tag .= 'align-items-top ';
    }

    if($a['classes'] != '') { 
        $tag .= $a['classes']; 
    } 

    // Complete tag
    $tag .= '">';

    //call image_att to create figure and image tags
    $imgOutput = image_att($atts);

    //wrap html content in div
    $content = '<div class="content-text">' . $content . '</div>';

    // Return the complete output
    return $tag . $imgOutput . $content . '</div>';
}

//add code to list (used in the_content_filter)
add_shortcode_to_list("ll-grid");
add_shortcode_to_list("img-text");

//add code to wordpress itself
add_shortcode('ll-grid', 'll_grid_att');
add_shortcode('img-text', 'img_text_att');

?>