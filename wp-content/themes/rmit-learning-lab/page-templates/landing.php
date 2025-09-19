<?php
/**
 * Template Name:  Landing page
 *

 */
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="container" id="page-content">
    <?php echo createBreadcrumbs($post); ?>
    <a id="main-content"></a>
    <?php 

    if ( have_posts() ) : 
        while ( have_posts() ) : the_post();
            the_content();
        endwhile;
    else :
        _e( 'Sorry, no posts matched your criteria.', 'textdomain' );
    endif;
    ?>
</div>

<?php get_footer();

