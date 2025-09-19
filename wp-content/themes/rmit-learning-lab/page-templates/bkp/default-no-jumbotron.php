<?php
/**
 * Template Name: Default Without Jumbotron
 *

 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();
?>
 

<div id="container-content-page" class="container">
    <div class="row">
        <div class="col-md-10 offset-md-1 py-5">

            <h1 class="display-4 fw-bold mb-4"><?php the_title(); ?></h1>
<p><?php if( function_exists( 'aioseo_breadcrumbs' ) ) aioseo_breadcrumbs(); ?></p>
            <?php 

            if ( have_posts() ) : 
                while ( have_posts() ) : the_post();
                    the_content();
                endwhile;
            else :
                _e( 'Sorry, no posts matched your criteria.', 'textdomain' );
            endif;
            ?>
			<p>&nbsp;</p>
			<?php get_template_part( 'page-templates/taxonomy', 'page' ); ?>
        </div>
    </div>
</div>


<?php get_footer();
