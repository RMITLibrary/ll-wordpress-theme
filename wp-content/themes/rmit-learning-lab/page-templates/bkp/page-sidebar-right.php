<?php
/**
 * Template Name:  Content page
 *

 */
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="container" id="page-content">
    <div class="row ">
        <!-- START right nav -->
        <div class="col-xl-4 order-last">
            <?php 
            get_sidebar();
            ?>
        </div>
        <!-- END right nav -->
        <!-- START content -->
        <div class="col-xl-8 order-first">
            <?php echo createBreadcrumbs($post); ?>
			<?php //if( function_exists( 'aioseo_breadcrumbs' ) ) aioseo_breadcrumbs(); ?>
            <a id="main-content"></a>
            <h1 class="margin-top-zero"><?php the_title(); ?></h1>
            <?php 

            if ( have_posts() ) : 
                while ( have_posts() ) : the_post();
                    the_content();
                endwhile;
            else :
                _e( 'Sorry, no posts matched your criteria.', 'textdomain' );
            endif;
            ?>
			<?php get_template_part( 'page-templates/includes/prev-next-buttons', 'page' ); ?>
			<?php get_template_part( 'page-templates/includes/additional-resources', 'page' ); ?>
			<?php get_template_part( 'page-templates/includes/taxonomy', 'page' ); ?>
        </div>
        <!-- END content --> 
    </div>
</div>

<?php get_footer();

