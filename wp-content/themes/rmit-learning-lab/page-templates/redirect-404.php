<?php
/**
 * Template Name: Redirect / 404 Helper
 * Description: Reuses the standard 404 markup so static crawlers (e.g. SiteSucker) can capture it.
 */

defined('ABSPATH') || exit;

get_header();

// Displays the same markup used on the native 404 template, including redirect messaging.
if (function_exists('output_redirect_404_script_and_html')) {
    output_redirect_404_script_and_html(array(
        'ignored_paths'            => array('/redirect-404/'),
        'disable_dataset_fallback' => true,
    ));
} else {
    echo '<main class="container"><h1>' . esc_html__('Page not found', 'rmit-learning-lab') . '</h1></main>';
}

get_footer();
