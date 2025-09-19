<?php
/**
 * Template Name: Empty page template
 *
 * Template for displaying a page just with the header and footer area and a "naked" content area in between.
 * Good for landing pages and other types of pages where you want to add a lot of custom markup.
 *
 * @package UnderStrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container" id="page-content">

    <?php if ( have_posts() ) : 
        while ( have_posts() ) : the_post();
            the_content();
        endwhile;
    else :
        _e( 'Sorry, no posts matched your criteria.', 'textdomain' );
    endif;
    ?>
</div>

<?php get_footer();
