<?php
// Grab the whole page list, put them in an array
$pagelist = get_pages(array(
    'sort_column' => 'menu_order,post_title',
    'sort_order' => 'asc'
));


//grab the whole page list, put them in an array
$pages = array();
foreach ($pagelist as $page) {
   $pages[] = $page->ID;
}

//find the current page index in the list
$current = array_search(get_the_ID(), $pages, true);

// Bail out early if we cannot find the current page in the list
if ($current === false) {
    $prevID = null;
    $nextID = null;
} else {
    $prevID = ($current > 0) ? $pages[$current - 1] : null;
    $nextID = ($current < count($pages) - 1) ? $pages[$current + 1] : null;
}

//format the titles, removing anything before a colon
//e.g. Artist statement:Formats becomes Formats in $nextTitle
$prevTitle = $prevID ? formatAfterTheColon(get_the_title($prevID)) : '';
$nextTitle = $nextID ? formatAfterTheColon(get_the_title($nextID)) : '';

//grab relative urls for both prev and next (to support adding query strings in js)

$prevURL = $prevID ? doRelativeURL($prevID) : '';
$nextURL = $nextID ? doRelativeURL($nextID) : '';

$is_first_page = (bool) get_query_var('is_first_page');
$is_last_page = (bool) get_query_var('is_last_page');

$has_prev = !empty($prevID) && !$is_first_page;
$has_next = !empty($nextID) && !$is_last_page;

if (!$has_prev && !$has_next) {
    return;
}

$container_classes = $has_prev ? 'btn-nav-container' : 'btn-nav-container no-prev-button';

function doRelativeURL($myId)
{
    // Get the full permalink URL
    $full_url = get_permalink($myId);

    // Convert the full URL to a relative URL
    $relative_url = wp_make_link_relative($full_url);

    // Output the relative URL
    return $relative_url;
}

?>
<nav class="<?php echo esc_attr($container_classes); ?>" aria-label="Previous and next links">
<?php 
if ($has_prev) { ?>
<h2 class="btn-nav-prev">
    <a href="<?php echo esc_url($prevURL); ?>">
        <span aria-hidden="true"><?php echo esc_html($prevTitle); ?></span>
        <span class="visually-hidden">Previous page: <?php echo esc_html($prevTitle); ?></span>
    </a>
</h2>
    

<?php }
if ($has_next) { 

?>

<h2 class="btn-nav-next">
    <a href="<?php echo esc_url($nextURL); ?>">
        <span aria-hidden="true"><?php echo esc_html($nextTitle); ?></span>
        <span class="visually-hidden">Next page: <?php echo esc_html($nextTitle); ?></span>
    </a>
</h2>
	
<?php } ?>
</nav><!-- .navigation -->
