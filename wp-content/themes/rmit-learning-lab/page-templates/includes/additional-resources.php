<?php
// Get the value of 'additional_resources' using ACF's get_field function
$additional_resources = get_field('additional_resources');
$additional_resources_title = get_field('additional_resources_title');

// Check if the title is empty and set a default value if needed
if (empty($additional_resources_title)) {
    $additional_resources_title = 'Further resources';
}


if (!empty($additional_resources)) {
    echo '<hr class="margin-top-xl">' . "\n";

    if (!empty($additional_resources_title)) {
        echo '<h2>' . $additional_resources_title . '</h2>' . "\n";
    }

    echo strip_tags_before_echo($additional_resources);
} 
?>