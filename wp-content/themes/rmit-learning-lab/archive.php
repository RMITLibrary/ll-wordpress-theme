<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();
?>
<?php

function createKeywordBreadcrumbs($thePost)
{
	$parent = get_post_parent($thePost);
	$grandParent = get_post_parent($parent);
	$greatGrandParent = get_post_parent($grandParent);
	
//	Debug code
//	echo('<p>id: ' . $greatGrandParent->ID . ' greatGrandParent: ' . $greatGrandParent->post_name . '<br/>');
//	echo('id: ' . $grandParent->ID . ' grandParent: '. $grandParent->post_name . '<br/>');
//	echo('id: ' . $parent->ID . ' parent: ' . $parent->post_name . '<br/>');
//	echo("---</p>");
	
	$output = '';

	$output .= '<ul class="breadcrumbs">' . "\n";
	
	//	At one level $greatGrandParent and $parent have the same value. Due to bug?
	//	Check that both $greatGrandParent and $grandParent have a value to solve this.
	if($greatGrandParent->ID && $grandParent->ID) {
		$output .= '<li>' . formatAfterTheColon(get_the_title($greatGrandParent)) . '</li>' . "\n";
	}
	
	if($grandParent->ID && $parent->ID) {
		$output .= '<li>' . formatAfterTheColon(get_the_title($grandParent)) . '</li>' . "\n";
	}
	
	if($parent->ID) {
		$output .= '<li>' . formatAfterTheColon(get_the_title($parent)) . '</li>' . "\n";
	}
	
	$output .= '</ul>'  . "\n";
	return $output;	
}

?>
  
<div class="container" id="page-content">
    <div class="row ">
        <!-- START content -->
        <div class="col-xl-8 order-first">
            <nav aria-label="breadcrumbs">
                <ul class="breadcrumbs">
                <li><a href="/">Home</a></li>
                <li><a href="/search">Search</a></li>
                <!-- UPDATE THIS -->
                </ul>
            </nav>
            <a id="main-content"></a>
            <h1><?php the_archive_title() ?></h1>
            <p class="lead">Select a page to view or select a related keyword to view other linked pages.</p>
            <!--<p class="lead text-muted col-md-8 offset-md-2 archive-description"><?php //echo category_description(); ?></div> -->
            
            <?php if ( have_posts() ) : ?>
                <div>
                    <ul class="list-link-expanded" id="keywords">

                    <?php while ( have_posts() ) : the_post(); 

                        // Retrieve the terms associated with the post
                        $terms = get_field('field_6527440d6f9a2');
                        $has_archive_term = false;


                    ?>
                    <?php
                        
                        if ( $terms ) {
                            foreach ( $terms as $term ) {
                                if ( stripos( $term->name, 'Archive' ) !== false ) {
                                    $has_archive_term = true;
                                    break;
                                }
                            }
                        }

                        // Skip the post if it has a term with the keyword "Archive" or 'work-in-progres' in the URL
                        if ( $has_archive_term || stripos(get_permalink(), 'work-in-progress') !== false ) {
                            continue;
                        }
                    ?>
                        <li class="result-item">
                            <h2 class="h3">
                                <a href="<?php echo esc_url( get_permalink() ); ?>">
                                    <?php echo esc_html( get_the_title() ); ?>
                                </a>
                            </h2>
                            <?php echo createKeywordBreadcrumbs($post); ?>
                            <p><?php echo esc_html( get_the_excerpt() ); ?></p>
                        </li>
                    <?php endwhile; ?>
                    </ul>
                </div>
            <?php else : ?>
                <p><?php _e( 'Sorry, no pages matched your criteria.', 'textdomain' ); ?></p>
            <?php endif; ?>
            
            <nav class="btn-nav-container" aria-label="Previous and next links">
                <h2 class="btn-nav-prev">
                    <a href="#" onclick="goBack(); return false;">
                        Back
                    </a>
                </h2>
                </nav>
            <script>
            function goBack() {
                window.history.back();
            }
            </script>
        </div>
    </div>
    <!-- END content --> 
</div>
 
<?php get_footer();
