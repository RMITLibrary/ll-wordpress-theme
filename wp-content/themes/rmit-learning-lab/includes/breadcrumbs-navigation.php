<?php
/**
 * Breadcrumbs and Navigation Functions
 *
 * Functions for creating breadcrumbs and navigation menus.
 *
 * @package RMIT_Learning_Lab
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create Breadcrumbs
 *
 * Output markup for breadcrumbs. Current page is listed in
 * breadcrumbs due to proximity to title
 *
 * @param WP_Post $thePost Page where the breadcrumbs will be shown
 * @return string HTML markup for breadcrumbs
 */
function createBreadcrumbs($thePost) {
    $parent = get_post_parent($thePost);
    $grandParent = $parent ? get_post_parent($parent) : null;
    $greatGrandParent = $grandParent ? get_post_parent($grandParent) : null;

    $output = '';

    $output .= '<nav aria-label="breadcrumbs">' . "\n";
    $output .= '<ul class="breadcrumbs">' . "\n";

    $output .= '<li><a href="/">Home</a></li>' . "\n";

    // Check that objects exist and have ID property before accessing
    if($greatGrandParent && !empty($greatGrandParent->ID) && $grandParent && !empty($grandParent->ID)) {
        $greatGrandParent_link = wp_make_link_relative(get_permalink($greatGrandParent->ID));
        $output .= '<li><a href="' . esc_url($greatGrandParent_link) . '">' . esc_html(formatAfterTheColon(get_the_title($greatGrandParent))) . '</a></li>' . "\n";
    }

    if($grandParent && !empty($grandParent->ID) && $parent && !empty($parent->ID)) {
        $grandParent_link = wp_make_link_relative(get_permalink($grandParent->ID));
        $output .= '<li><a href="' . esc_url($grandParent_link) . '">' . esc_html(formatAfterTheColon(get_the_title($grandParent))) . '</a></li>' . "\n";
    }

    if($parent && !empty($parent->ID)) {
        $parent_link = wp_make_link_relative(get_permalink($parent->ID));
        $output .= '<li><a href="' . esc_url($parent_link) . '">' . esc_html(formatAfterTheColon(get_the_title($parent))) . '</a></li>' . "\n";
    }

    $output .= '</ul>'  . "\n";
    $output .= '</nav>';
    return $output;
}

/**
 * Create Context Menu Accordion
 *
 * Creates an accordion for the context (hamburger) menu based on $pageId argument
 *
 * @param string $title Title to be displayed on the accordion
 * @param int $pageId Id of the page whose children we want to display
 * @return string HTML markup for accordion
 */
function doContextMenuAccordion($title, $pageId) {
    $headId = 'accordion-head-' . $pageId;
    $bodyId = 'accordion-body-' . $pageId;

    $output = '';

    $output .= '<div class="accordion-item">' . "\n";
    $output .= '<h2 class="accordion-header" id="' . $headId .'">' . "\n";
    $output .= '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#' . $bodyId . '" aria-expanded="false" aria-controls="' . $bodyId . '">';
    $output .= $title;
    $output .= '</button>' . "\n";
    $output .= '</h2>' . "\n";
    $output .= '<div id="' . $bodyId . '" class="accordion-collapse collapse" aria-labelledby="' . $headId . '">' . "\n";
    $output .= '<div class="accordion-body"><ul>' . doChildrenList($pageId) . '</ul></div></div></div>';

    return $output;
}
