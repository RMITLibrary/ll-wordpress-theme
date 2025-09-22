<?php
// Grab the whole page list, put them in an array
$pagelist = get_pages(array(
    'sort_column' => 'menu_order,post_title',
    'sort_order' => 'asc'
));


//grab the whole page list, put them in an array
$pages = array();
foreach ($pagelist as $page) {
   $pages[] += $page->ID;
}

//find the current page and then get ids for prev and next pages
$current = array_search(get_the_ID(), $pages);
$prevID = $pages[$current-1];
$nextID = $pages[$current+1];

//format the titles, removing anything before a colon
//e.g. Artist statement:Formats becomes Formats in $nextTitle
$prevTitle = formatAfterTheColon(get_the_title($prevID));
$nextTitle = formatAfterTheColon(get_the_title($nextID));

//grab relative urls for both prev and next (to support adding query strings in js)

$prevURL = doRelativeURL($prevID);
$nextURL = doRelativeURL($nextID);

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
<?php
$is_first_page = (bool) get_query_var('is_first_page');
$is_last_page = (bool) get_query_var('is_last_page');
?>
<nav class="btn-nav-container <?php echo $is_first_page ? 'no-prev-button' : ''; ?>" aria-label="Previous and next links">
<?php 
if (!empty($prevID) && !$is_first_page) { ?>
<h2 class="btn-nav-prev">
    <a href="<?php echo $prevURL; ?>">
        <span aria-hidden="true"><?php echo $prevTitle ; ?></span>
        <span class="visually-hidden">Previous page: <?php echo $prevTitle; ?></span>
    </a>
</h2>
    

<?php }
if (!empty($nextID) && !$is_last_page) { 

?>

<h2 class="btn-nav-next">
    <a href="<?php echo $nextURL; ?>">
        <span aria-hidden="true"><?php echo $nextTitle ; ?></span>
        <span class="visually-hidden">Next page: <?php echo $nextTitle ; ?></span>
    </a>
</h2>
	
<?php } ?>
</nav><!-- .navigation -->
