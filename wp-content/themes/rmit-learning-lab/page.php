<?php

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
			<div id="additional-info">
			<?php get_template_part( 'page-templates/includes/additional-resources', 'page' ); ?>
			<?php get_template_part( 'page-templates/includes/keywords-embed-modal', 'page' ); ?>
            </div>
        </div>
        <!-- END content --> 
    </div>
</div>

<?php get_footer();
