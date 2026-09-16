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
    return doMenuAccordionSection($pageId, $title, doChildrenList($pageId));
}

/**
 * Create one accordion section for the context (hamburger) menu
 *
 * @param string $idSuffix Unique fragment for the head and body ids
 * @param string $title Title shown on the accordion button
 * @param string $listHtml List items for the accordion body
 * @return string HTML markup for accordion
 */
function doMenuAccordionSection($idSuffix, $title, $listHtml) {
    $headId = 'accordion-head-' . $idSuffix;
    $bodyId = 'accordion-body-' . $idSuffix;

    $output = '';

    $output .= '<div class="accordion-item">' . "\n";
    $output .= '<h2 class="accordion-header" id="' . esc_attr($headId) .'">' . "\n";
    $output .= '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#' . esc_attr($bodyId) . '" aria-expanded="false" aria-controls="' . esc_attr($bodyId) . '">';
    $output .= esc_html($title);
    $output .= '</button>' . "\n";
    $output .= '</h2>' . "\n";
    $output .= '<div id="' . esc_attr($bodyId) . '" class="accordion-collapse collapse" aria-labelledby="' . esc_attr($headId) . '">' . "\n";
    $output .= '<div class="accordion-body"><ul>' . $listHtml . '</ul></div></div></div>';

    return $output;
}

/**
 * Build the context menu from the "Main menu" location
 *
 * Each top-level menu item becomes an accordion. Its body lists the item's own
 * menu children when it has them, otherwise the child pages of the page it points
 * at, which is how the sections behaved when this list was hardcoded.
 *
 * @return string HTML markup, or '' when no menu is assigned so the header can fall back
 */
function doMainMenuAccordions() {
    $locations = get_nav_menu_locations();

    if (empty($locations['main-menu'])) {
        return '';
    }

    $items = wp_get_nav_menu_items($locations['main-menu']);

    if (empty($items)) {
        return '';
    }

    $byParent = array();
    foreach ($items as $item) {
        $byParent[(int) $item->menu_item_parent][] = $item;
    }

    if (empty($byParent[0])) {
        return '';
    }

    $output = '';
    foreach ($byParent[0] as $section) {
        $children = isset($byParent[$section->ID]) ? $byParent[$section->ID] : array();

        if ($children) {
            $listHtml = '';
            foreach ($children as $child) {
                // Keep internal links relative, as the breadcrumbs and page lists do.
                $url = wp_make_link_relative($child->url);
                $listHtml .= '<li><a href="' . esc_url($url) . '">' . esc_html($child->title) . '</a></li>';
            }
        } else {
            $listHtml = doChildrenList((int) $section->object_id);
        }

        $output .= doMenuAccordionSection($section->ID, $section->title, $listHtml);
    }

    return $output;
}
