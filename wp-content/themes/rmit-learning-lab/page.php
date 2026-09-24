<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// First content page, Last content page and No side nav set a flag and load this file,
// so there is one page layout. The flags are set before get_header() so the schema in
// header.php can read them too.
$hide_side_nav = (bool) get_query_var('hide_side_nav');

get_header();
?>

<div class="container" id="page-content">
    <div class="row ">
<?php if ( ! $hide_side_nav ) : ?>
        <!-- START right nav -->
        <div class="col-xl-4 order-last">
            <?php 
            get_sidebar();
            ?>
        </div>
        <!-- END right nav -->
<?php endif; ?>
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
                _e( 'Sorry, no posts matched your criteria.', 'rmit-learning-lab' );
            endif;

            if ( get_query_var('is_last_page') ) {
                get_template_part( 'page-templates/includes/end-of-section' );
            }
            ?>
<?php if ( $hide_side_nav ) : ?>
			<?php get_template_part( 'page-templates/includes/additional-resources', 'page' ); ?>
			<?php get_template_part( 'page-templates/includes/keywords-embed-modal', 'page' ); ?>
<?php else : ?>
			<?php get_template_part( 'page-templates/includes/prev-next-buttons', 'page' ); ?>
			<div id="additional-info">
			<?php get_template_part( 'page-templates/includes/additional-resources', 'page' ); ?>
			<?php get_template_part( 'page-templates/includes/keywords-embed-modal', 'page' ); ?>
            </div>
<?php endif; ?>
        </div>
        <!-- END content --> 
    </div>
</div>

<?php get_footer();
