

<!-- this style changes the page link style in the sidebar nav 
to indicate the current page and removes bullets from unordered lists. 
The wp_list_pages function allocates the classes automatically. 
This should be moved to a stylesheet once style is determined. -->

<!--<style>
[aria-current="page"] {
  pointer-events: none !important; 
  cursor: default;
  text-decoration: none !important;
  color: #000054;
}
</style>-->

	
<?php
/*
// VARIABLE NAMING PROTOCOLS
//------------------------------------------------
$greatGrandParent = '';		// Landing page / Subject area page e.g. 'Art and design'
$grandParent = '';			// Top-level section page e.g. 'Artist statement'
$parent = '';				// Second-level page - i.e. child of top-level e.g. 'Writing process'
							// Third-level page - i.e. child of second-level e.g. 'Mind mapping'
							// Note: there should not be any further levels than these 4
*/

// Get the lineage of the current page (if any)
// Note: a page may not have a parent, grandparent or great-grandparent depending on it's position in the navigation hierarcy
$parent = get_post_parent($post);
$grandParent = get_post_parent($parent);
$greatGrandParent = get_post_parent($grandParent);

?>
<!-- START SIDEBAR NAV PANEL -->
<nav class="right-nav sticky-nav" aria-label="Section Menu">

<?php
	
// There are 4 possible scenarios to display in the sidebar navigation panel:

if($greatGrandParent->ID && $grandParent->ID) {
	// if the current page has a great-grandparent (i.e. third-level page)
	// show the following headings for great-grandparent and grandparent
	// then recursively output current page siblings (there should be no children at this level)
	echo doNavHeading($greatGrandParent, 'h2');
	echo doNavHeading($grandParent, 'h3');
	outputChildNav($grandParent->ID, $post, $parent);
} 
elseif($grandParent->ID && $parent->ID) {
	// if the current page doesn't have a great-grandparent but has a grandparent (i.e. second-level page)
	// show the following headings for grandparent and parent
	// then recursively output current page siblings and children
	echo doNavHeading($grandParent, 'h2');
	echo doNavHeading($parent, 'h3');
	outputChildNav($parent->ID, $post);
}
elseif ($parent->ID) {
    // if the current page doesn't have a great-grandparent or grandparent (i.e. top-level page)
    // show the heading for parent
    echo doNavHeading($parent, 'h2');
    
	// Retrieve children of the current post
    $children = get_posts(array(
        'post_type' => $post->post_type,
        'post_parent' => $post->ID,
        'numberposts' => -1
    ));
    
    // Check if there are any children
    if (!empty($children)) {
        // There are children, show the heading for current page
		echo doNavHeading($post, 'h3', 'selected');
		
		// Output current page's children
    	outputChildNav($post->ID, null);
		
    } else {
        // There are no children, so there's a shallower structure to this page's section.
		// output the parent's children (including this page which will be selected)
		outputChildNav($parent->ID, $post);
    }
}
/*elseif($parent->ID) {
	// if the current page doesn't have a great-grandparent or grandparent (i.e. top-level page)
	// show the following headings for parent and current page
	// then recursively output current page's children
	echo doNavHeading($parent, 'h2');
	echo doNavHeading($post, 'h3', 'selected');
	outputChildNav($post->ID, null);
	
	echo('If condition 3');
}*/
else
{
	// if the current page doesn't have a great-grandparent, grandparent or parent (i.e. landing / subject area page)
	// show the following headings for current page
	// then recursively output direct children (need to add this bit??)
	echo doNavHeading($post, 'h2', 'selected');
	outputChildNav($post->ID, null);
}
    
?>
</nav>
<!-- END SIDEBAR NAV PANEL -->

<?php

// This function builds the hierarchical navigation for current page siblings and/or children where required, <br>
// depending on the current page level in the hierarchy - i.e. child, parent, grandparent or great-grandparent

function outputChildNav($parent_id, $thePost, $thePostParent = null)
{
	// $parent_id is the ID of the parent or grandparent post
	// Set up the arguments for the query
	$args = array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => -1,           // Get all children
		'post_parent'    => $parent_id,
		'orderby'        => array(
            'menu_order' => 'ASC',
            'title'      => 'ASC'
        ),
        'order'          => 'ASC'  
	);

	// Create a new query
	$child_query = new WP_Query($args);

	// Check if the query returns any posts (pages) - i.e. does the current page have a parent or grandparent page?
	if ($child_query->have_posts()) {
		// if there are children, create an unordered list of pages
		echo '<ul>';
		// Loop through the pages 
		// This will output the current page and its sibling pages in the correct menu order
		while ($child_query->have_posts()) {
			$child_query->the_post();
			// Output the title and link to the page
            
            $post_slug = get_post_field('post_name', get_the_ID());
			
			$post_url = get_permalink(get_the_ID());

			// Retrieve the value of the 'nav-divider' select field
			$nav_divider = get_field('nav-divider');

			// Check if $nav_divider is set and not equal to 'Select'
			if ($nav_divider && $nav_divider !== 'Select a divider label') {

				// Check if the selected value is 'Other'
				if ($nav_divider === 'Other') {
					// Retrieve the value from the 'nav-divider-other' text field
					$nav_divider_other = get_field('nav-divider-other');

					// If 'nav-divider-other' has a value, output it as a list item
					if ($nav_divider_other) {
						echo '<li class="nav-divider">' . esc_html($nav_divider_other) . '</li>';
					}

				} else {
					// For values other than 'Other', output the value of 'nav-divider' as a list item
					echo '<li class="nav-divider">' . esc_html($nav_divider) . '</li>';
				}
			}
            
			if($thePost->ID == get_the_ID()) {
                // If the post ID matches with the current page, output the selected code and then output the child nav if there are children (recursively)
				// This only outputs the children of the current page and not the children of the current page siblings
				// If the title has a colon, implement the formatAfterTheColon() function to shorten the title
                echo '<li><a href="' . $post_url . '" class="selected"  aria-current="page">' . formatAfterTheColon(get_the_title()) . '</a>';
				outputChildNav($thePost->ID, $thePost);
			}
			else {
                // Otherwise, output the link
				// If the title has a colon after the first part, implement the formatAfterTheColon() function (in functions.php) to remove the first part and shorten the title
                echo '<li><a href="' . $post_url . '">' . formatAfterTheColon(get_the_title()) . '</a>';
                // If thePostParent->ID exists, we are in a subpage.
				// If thePostParent->ID matches the loop item, do children pages (recursively)
				// This only outputs the siblings of the current page and not the children of the current page
                if($thePostParent->ID == get_the_ID())
                {
                    outputChildNav($thePostParent->ID, $thePost);
                }
                echo '</li>';
			}  
		}
		echo '</ul>';
	} else {
		// If no children found, do nothing

	}

	// Reset post data
	wp_reset_postdata();
}

// This function builds the correct HTML for H2 and H3 navigation items whether selected or not selected
function doNavHeading($myPost, $tag, $selected = null)
{
	// Create the variable for the output
	$output = '';
    
	// Set the title and slug for the navigation item
	$title = get_the_title($myPost);
	$slug = $myPost->post_name;

	$post_url = get_permalink($myPost->ID);
    
	// If the item ins selected, set the class to "selected"
	if($selected == true) {
		$output .= '<' . $tag . ' class="selected">' . $title . '</' . $tag . '>';
	}
	else {
		$output .= '<' . $tag . '><a href="' . $post_url . '">' . $title . '</a></' . $tag . '>';
	}	
		
	return $output;
}

?>