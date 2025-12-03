<?php
/**
 * Learning Resource Schema Markup
 *
 * Outputs schema.org LearningResource structured data for educational content.
 * Complements AIOSEO's existing schema output (WebPage, Organization, BreadcrumbList).
 *
 * @link https://schema.org/LearningResource
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Only output on singular pages (not home, archives, etc.)
if (!is_singular() || is_front_page() || is_home()) {
  return;
}

global $post;

// Basic page data
$url   = get_permalink($post);
$title = get_the_title($post);

// Description: Use AIOSEO if available, fallback to excerpt
$desc = get_post_meta(get_the_ID(), '_aioseo_description', true);
if (empty($desc)) {
  $excerpt = wp_strip_all_tags(get_the_excerpt($post), true);
  $desc = ($excerpt === '...' || $excerpt === '')
    ? 'The Learning Lab provides foundation skills and study support materials for writing, science and more.'
    : $excerpt;
}

// Keywords from ACF taxonomy field
$keywords = [];
$terms = get_field('field_6527440d6f9a2'); // Keywords field
if ($terms) {
  $keywords = wp_list_pluck($terms, 'name');
}

// Parent page relationship
$parent_id   = wp_get_post_parent_id($post);
$parent_page = $parent_id ? get_post($parent_id) : null;

// Previous/Next page relationships (from navigation logic)
$pagelist = get_pages([
  'sort_column' => 'menu_order,post_title',
  'sort_order' => 'asc'
]);
$pages = wp_list_pluck($pagelist, 'ID');
$current = array_search(get_the_ID(), $pages, true);

$prevID = null;
$nextID = null;
if ($current !== false) {
  $prevID = ($current > 0) ? $pages[$current - 1] : null;
  $nextID = ($current < count($pages) - 1) ? $pages[$current + 1] : null;
}

// Respect is_first_page and is_last_page flags (same as prev-next-buttons.php)
$is_first_page = (bool) get_query_var('is_first_page');
$is_last_page = (bool) get_query_var('is_last_page');

$has_prev = !empty($prevID) && !$is_first_page;
$has_next = !empty($nextID) && !$is_last_page;

// Check if current template actually shows navigation buttons
// Landing pages don't include prev-next-buttons.php, so exclude them
$template = get_page_template_slug();
$is_landing = ($template === 'page-templates/landing.php');
// Show navigation on all pages EXCEPT landing pages
$shows_navigation = !$is_landing;

// Build schema data
$data = [
  '@context'           => 'https://schema.org',
  '@type'              => 'LearningResource',
  '@id'                => $url . '#learning-resource',
  'name'               => $title,
  'description'        => $desc,
  'url'                => $url,
  'inLanguage'         => get_bloginfo('language'), // e.g. en-AU
  'learningResourceType' => $is_landing
    ? ['Learning resource overview', 'Online learning resource']
    : ['Online learning resource'],
  'educationalUse'     => ['Self-directed study'],
  'educationalLevel'   => 'Higher education',
  'audience'           => [
    '@type'           => 'EducationalAudience',
    'educationalRole' => 'Student',
  ],
  'keywords'           => $keywords,
  'provider'           => [
    '@type' => 'CollegeOrUniversity',
    '@id'   => 'https://learninglab.rmit.edu.au/#organization',
    'name'  => 'RMIT University Library',
  ],
  'datePublished'      => get_the_date('c', $post),
  'dateModified'       => get_the_modified_date('c', $post),
];

// Add parent page relationship if exists
if ($parent_page) {
  $data['isPartOf'] = [
    '@type' => 'WebPage',
    '@id'   => get_permalink($parent_page) . '#webpage',
    'name'  => get_the_title($parent_page),
  ];
}

// Add child page relationships for landing pages
if ($is_landing) {
  $child_pages = get_pages([
    'parent'      => get_the_ID(),
    'sort_column' => 'menu_order,post_title',
    'sort_order'  => 'asc',
  ]);

  if ($child_pages) {
    $has_part = [];
    foreach ($child_pages as $child) {
      $has_part[] = [
        '@type' => 'LearningResource',
        '@id'   => get_permalink($child) . '#learning-resource',
        'name'  => get_the_title($child),
        'url'   => get_permalink($child),
      ];
    }
    $data['hasPart'] = $has_part;
  }
}

// Only add prev/next if template actually shows navigation AND at least one exists
if ($shows_navigation && ($has_prev || $has_next)) {
  // Add previous page relationship (only if not marked as first page)
  if ($has_prev) {
    $data['previousItem'] = [
      '@type' => 'LearningResource',
      '@id'   => get_permalink($prevID) . '#learning-resource',
      'name'  => get_the_title($prevID),
      'url'   => get_permalink($prevID),
    ];
  }

  // Add next page relationship (only if not marked as last page)
  if ($has_next) {
    $data['nextItem'] = [
      '@type' => 'LearningResource',
      '@id'   => get_permalink($nextID) . '#learning-resource',
      'name'  => get_the_title($nextID),
      'url'   => get_permalink($nextID),
    ];
  }
}

// Output JSON-LD script
echo "\n" . '<script type="application/ld+json">';
echo wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
echo '</script>' . "\n";
