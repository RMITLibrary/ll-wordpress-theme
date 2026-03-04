<?php

//-----------------------------
//	nowrap

//	Wraps content in a span with white-space: nowrap
//
//	shortcode:  [nowrap][/nowrap]
//	usage:		[nowrap]text that should not wrap[/nowrap]
//
//	Expected output:
//	<span class="nowrap">text that should not wrap</span>

function nowrap_shortcode($atts, $content = null) {
    $content = do_shortcode($content);
    return '<span class="nowrap">' . $content . '</span>';
}

//-----------------------------
//	break-word

//	Wraps content in a span with overflow-wrap: break-word
//	Use for long words or URLs that should only break when necessary
//
//	shortcode:  [break-word][/break-word]
//	usage:		[break-word]https://very-long-url-that-might-overflow.example.com[/break-word]
//
//	Expected output:
//	<span class="break-word">https://very-long-url...</span>

function break_word_shortcode($atts, $content = null) {
    $content = do_shortcode($content);
    return '<span class="break-word">' . $content . '</span>';
}

//-----------------------------
//	break-all

//	Wraps content in a span with word-break: break-all
//	Use for raw unlinked URLs or strings that must break at any character
//
//	shortcode:  [break-all][/break-all]
//	usage:		[break-all]https://very-long-url-that-must-break-anywhere.example.com[/break-all]
//
//	Expected output:
//	<span class="break-all">https://very-long-url...</span>

function break_all_shortcode($atts, $content = null) {
    $content = do_shortcode($content);
    return '<span class="break-all">' . $content . '</span>';
}

add_shortcode_to_list('nowrap');
add_shortcode_to_list('break-word');
add_shortcode_to_list('break-all');

add_shortcode('nowrap', 'nowrap_shortcode');
add_shortcode('break-word', 'break_word_shortcode');
add_shortcode('break-all', 'break_all_shortcode');

?>
