<?php
/**
 * Video Schema Markup
 *
 * Outputs schema.org VideoObject structured data for videos embedded via [ll-video] shortcodes.
 * Each video gets its own separate JSON-LD script tag for modularity.
 *
 * @link https://schema.org/VideoObject
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Only output on singular pages (not home, archives, etc.)
if (!is_singular() || is_front_page() || is_home()) {
  return;
}

global $post;

// Parse video shortcodes from post content
$content = $post->post_content;

// Find all [ll-video] shortcodes
preg_match_all('/\[ll-video([^\]]*)\](.*?)\[\/ll-video\]/s', $content, $video_matches, PREG_SET_ORDER);

// Exit early if no videos found
if (empty($video_matches)) {
  return;
}

// Basic page data for fallbacks
$page_url = get_permalink($post);
$page_title = get_the_title($post);

// Process each video
foreach ($video_matches as $match) {
  $atts_string = $match[1];
  $video_content = $match[2];

  // Extract url attribute
  if (!preg_match('/url=["\']([^"\']+)["\']/', $atts_string, $url_match)) {
    continue; // Skip if no URL
  }

  $video_url = $url_match[1];

  // Extract video ID from YouTube URL
  $video_id = null;
  $parsed_url = parse_url($video_url);

  if (isset($parsed_url['host']) && $parsed_url['host'] === 'youtu.be') {
    // Format: https://youtu.be/VIDEO_ID
    $video_id = ltrim($parsed_url['path'], '/');
  } elseif (isset($parsed_url['path']) && strpos($parsed_url['path'], '/embed/') !== false) {
    // Format: https://www.youtube.com/embed/VIDEO_ID
    $video_id = str_replace('/embed/', '', $parsed_url['path']);
  } elseif (isset($parsed_url['query'])) {
    // Format: https://www.youtube.com/watch?v=VIDEO_ID
    parse_str($parsed_url['query'], $query_params);
    if (isset($query_params['v'])) {
      $video_id = $query_params['v'];
    }
  }

  // Skip if we couldn't extract a valid video ID
  if (!$video_id) {
    continue;
  }

  // Fetch YouTube metadata via oEmbed API (with caching)
  $oembed_data = null;
  $transient_key = 'yt_oembed_' . $video_id;
  $cached_data = get_transient($transient_key);

  if ($cached_data !== false) {
    $oembed_data = $cached_data;
  } else {
    // Fetch from YouTube oEmbed API
    $oembed_url = 'https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v=' . urlencode($video_id) . '&format=json';
    $response = wp_remote_get($oembed_url, ['timeout' => 5]);

    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
      $body = wp_remote_retrieve_body($response);
      $oembed_data = json_decode($body, true);

      // Cache for 7 days
      if ($oembed_data) {
        set_transient($transient_key, $oembed_data, 7 * DAY_IN_SECONDS);
      }
    }
  }

  // Extract caption attribute (user-provided override)
  $caption = null;
  if (preg_match('/caption=["\']([^"\']+)["\']/', $atts_string, $caption_match)) {
    $caption = $caption_match[1];
  }

  // Use YouTube title if no caption provided
  $video_title = $caption;
  if (!$video_title && $oembed_data && isset($oembed_data['title'])) {
    $video_title = $oembed_data['title'];
  }
  if (!$video_title) {
    $video_title = $page_title . ' - Video';
  }

  // Get author/channel name from oEmbed
  $author_name = null;
  if ($oembed_data && isset($oembed_data['author_name'])) {
    $author_name = $oembed_data['author_name'];
  }

  // Extract transcript content if present
  $transcript = null;
  if (preg_match('/\[transcript[^\]]*\](.*?)\[\/transcript\]/s', $video_content, $transcript_match) ||
      preg_match('/\[transcript-accordion[^\]]*\](.*?)\[\/transcript-accordion\]/s', $video_content, $transcript_match) ||
      preg_match('/\[lightweight-accordion[^\]]*\](.*?)\[\/lightweight-accordion\]/s', $video_content, $transcript_match)) {
    $transcript = wp_strip_all_tags($transcript_match[1]);
    $transcript = trim($transcript);
  }

  // Build VideoObject schema
  $video_data = [
    '@context' => 'https://schema.org',
    '@type' => 'VideoObject',
    '@id' => $page_url . '#video-' . $video_id,
    'name' => $video_title,
    'embedUrl' => 'https://www.youtube.com/embed/' . $video_id,
    'uploadDate' => get_the_date('c', $post),
    'thumbnailUrl' => [
      'https://img.youtube.com/vi/' . $video_id . '/maxresdefault.jpg',
      'https://img.youtube.com/vi/' . $video_id . '/hqdefault.jpg'
    ],
    'inLanguage' => get_bloginfo('language'),
  ];

  // Only add description if caption is explicitly provided
  if ($caption) {
    $video_data['description'] = $caption;
  }

  // Add author/creator if available from YouTube
  if ($author_name) {
    $video_data['creator'] = [
      '@type' => 'Organization',
      'name' => $author_name
    ];
  }

  // Add transcript if available (plain text string)
  if ($transcript) {
    $video_data['transcript'] = $transcript;
  }

  // Output separate JSON-LD script for this video
  echo "\n" . '<script type="application/ld+json">';
  echo wp_json_encode($video_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  echo '</script>' . "\n";
}
