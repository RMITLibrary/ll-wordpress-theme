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

    // Merge user-defined attributes with defaults
    $a = shortcode_atts($default, $atts);

    // Sanitize content and class/id fragments
    $content = wp_kses_post(do_shortcode($content));

    $headingTag = 'h2';

    if($a['heading-tag'] != '')
    {
        // Sanitize the heading level to prevent invalid HTML
        $allowed_headings = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6');
        if (in_array($a['heading-tag'], $allowed_headings)) {
            $headingTag = $a['heading-tag'];
        }
    }

    $heading_id = '';
    if (!empty($a['id'])) {
        $heading_id = ' id="' . esc_attr($a['id']) . '"';
    }

    $classes = array('title-icon');
    if (!empty($a['type'])) {
        $type_classes = preg_split('/\s+/', $a['type'], -1, PREG_SPLIT_NO_EMPTY);
        foreach ($type_classes as $class_token) {
            $classes[] = sanitize_html_class($class_token);
        }
    }
    if (!empty($a['heading-size'])) {
        $size_classes = preg_split('/\s+/', $a['heading-size'], -1, PREG_SPLIT_NO_EMPTY);
        foreach ($size_classes as $class_token) {
            $classes[] = sanitize_html_class($class_token);
        }
    }

    $heading  = '<' . tag_escape($headingTag) . ' class="' . esc_attr(implode(' ', array_filter($classes))) . '"' . $heading_id . '>';
    $heading .= $content . '</' . tag_escape($headingTag) . '>';

    $style = '';

    $base_class = '';
    if (!empty($a['type'])) {
        $type_classes = preg_split('/\s+/', $a['type'], -1, PREG_SPLIT_NO_EMPTY);
        if (!empty($type_classes)) {
            $base_class = sanitize_html_class($type_classes[0]);
        }
    }
    $alt_text   = !empty($a['alt']) ? esc_attr($a['alt']) : '';
    $img_url    = !empty($a['img']) ? esc_url($a['img']) : '';
    $img_dark   = !empty($a['img-dark']) ? esc_url($a['img-dark']) : '';

    if ($base_class && ($alt_text || $img_url)) {
        $style_rules = array();

        if ($alt_text && !$img_url) {
            $style_rules[] = sprintf('.%s::before { content: "" / "%s"; }', $base_class, $alt_text);
        }

        if ($img_url) {
            $content_rule = $alt_text ? sprintf(' content: "" / "%s";', $alt_text) : '';
            $style_rules[] = sprintf('.%s::before { background-image: url("%s");%s }', $base_class, $img_url, $content_rule);

            if ($img_dark) {
                $style_rules[] = sprintf('@media (prefers-color-scheme: dark) { .%s::before { background-image: url("%s"); } }', $base_class, $img_dark);
            }
        }

        if (!empty($style_rules)) {
            $style = '<style>' . implode(' ', $style_rules) . '</style>';
        }
    }

    $output = $style . $heading .  "\n";
    return $output;
}

//add code to list (used in the_content_filter)
add_shortcode_to_list("title-icon");

//add code to wordpress itself
add_shortcode('title-icon', 'title_icon');

?>
