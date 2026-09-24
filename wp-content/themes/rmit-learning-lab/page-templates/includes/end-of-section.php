<?php
// End-of-section panel shown on Last content page pages, from the Last content page
// field group. Rendered by page.php when the template sets is_last_page.

$section_title = get_field('end_section_title');

$left_title = get_field('end_section_lc_title');
$left_content = get_field('end_section_lc_content');

$show_right = filter_var(get_field('end_section_show_rc'), FILTER_VALIDATE_BOOLEAN);
$right_title = get_field('end_section_rc_title');
$right_content = get_field('end_section_rc_content');


if($section_title != "") {
    echo '<div class="end-of-section">' . "\n";

	//START title
	echo '<div class="eos-title">' . "\n";
	echo '<div>' . "\n";
	echo '<p>Well done! You’ve finished:</p>' . "\n";
echo '<h2>' . esc_html($section_title) . '</h2>' . "\n";
	echo '</div>' . "\n";
	echo '</div>' . "\n";
	//END title

	//START content
	echo '<div class="eos-content">' . "\n";

	//START left
	echo '<div class="left">' . "\n";
echo '<p class="eos-left-title">' . esc_html($left_title) . '</p>' . "\n";

	//START left content
	echo '<div class="eos-left-content">' . "\n";
echo wp_kses_post($left_content) . "\n";
	echo '</div>' . "\n";
	//END left content

	echo '</div>' . "\n";
	//END left

if ($show_right) {
		//START right
		echo '<div class="right">' . "\n";
		echo '<p class="eos-right-title">' . esc_html($right_title) . '</p>' . "\n";

		//START right content
		echo '<div class="eos-right-content">' . "\n";
		echo wp_kses_post($right_content) . "\n";
		echo '</div>' . "\n";
		//END right content

		echo '</div>' . "\n";
		//END right
	}

	echo '</div>' . "\n";
	//END content

	echo '</div>' . "\n";
}
