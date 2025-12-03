<?php
/**
 * Dublin Core Terms Metadata
 *
 * Outputs DCMI metadata elements for the current page.
 * https://www.dublincore.org/specifications/dublin-core/dcmi-terms/
 */

// Exit if accessed directly
defined('ABSPATH') || exit;
?>

<meta name="author" content="RMIT Library">
<meta name="geo.position" content="-37.807778, 144.963333">
<meta name="geo.placename" content="Melbourne">
<meta name="geo.region" content="AU">

<?php
// Use AIOSEO title if available, otherwise use WordPress document title
$page_title = get_post_meta(get_the_ID(), '_aioseo_title', true);
if (empty($page_title)) {
  $page_title = wp_get_document_title();
}
echo '<meta name="dcterms.title" content="' . esc_attr($page_title) . '">' . "\n";
?>

<meta name="dcterms.publisher" content="RMIT University Library">
<meta name="dcterms.language" content="<?php echo esc_attr(get_bloginfo('language')); ?>">

<?php
if (is_singular()) {
  // Try AIOSEO meta description from post meta
  $aioseodesc = get_post_meta(get_the_ID(), '_aioseo_description', true);

  // Fallback to excerpt if AIOSEO doesn't return anything
  if (!empty($aioseodesc)) {
    $desc = $aioseodesc;
  } else {
    $excerpt = wp_strip_all_tags(get_the_excerpt(), true);
    $desc = ($excerpt === '...' || $excerpt === '')
      ? 'The Learning Lab provides foundation skills and study support materials for writing, science and more.'
      : $excerpt;
  }

  echo '<meta name="dcterms.description" content="' . esc_attr($desc) . '">' . "\n";

  // Optional: dcterms.subject from ACF keywords field (multiple fields per DCMI best practice)
  $terms = get_field('field_6527440d6f9a2');
  if ($terms) {
    foreach ($terms as $term) {
      echo '<meta name="dcterms.subject" content="' . esc_attr($term->name) . '">' . "\n";
    }
  }
}
?>

<meta name="dcterms.type" content="Text">
<?php echo '<meta name="dcterms.identifier" content="' . esc_url(get_permalink()) . '">';
echo "\n"; ?>
<meta name="dcterms.format" content="text/html">

<?php
// Date metadata for learning objects
if (is_singular()) {
  // Created date (post published date in ISO 8601 format)
  $created_date = get_the_date('Y-m-d');
  echo '<meta name="dcterms.created" content="' . esc_attr($created_date) . '">' . "\n";

  // Modified date (post last modified date in ISO 8601 format)
  $modified_date = get_the_modified_date('Y-m-d');
  echo '<meta name="dcterms.modified" content="' . esc_attr($modified_date) . '">' . "\n";
}
?>

<meta name="dcterms.rights" content="© RMIT University">
<meta name="dcterms.license" content="https://creativecommons.org/licenses/by-nc/4.0/">
