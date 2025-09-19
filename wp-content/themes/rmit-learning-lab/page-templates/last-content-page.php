<?php
/**
 * Template Name:  Last content page
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

            //add last page panel
            //Write markup and styles then addin SC fields
            $section_title = get_field('end_section_title');

			$left_title = get_field('end_section_lc_title');
			$left_content = get_field('end_section_lc_content');

			$show_right = get_field('end_section_show_rc');
			$right_title = get_field('end_section_rc_title');
			$right_content = get_field('end_section_rc_content');


            if($section_title != "") {
                echo '<div class="end-of-section">' . "\n";

				//START title
				echo '<div class="eos-title">' . "\n";
				echo '<div>' . "\n";
				echo '<p>Well done! You’ve finished:</p>' . "\n";
				echo '<h2>' . $section_title . '</h2>' . "\n";
				echo '</div>' . "\n";
				echo '</div>' . "\n";
				//END title

				//START content
				echo '<div class="eos-content">' . "\n";

				//START left
				echo '<div class="left">' . "\n";
				echo '<p class="eos-left-title">' . $left_title . '</p>' . "\n";

				//START left content
				echo '<div class="eos-left-content">' . "\n";
				echo $left_content . "\n";
				echo '</div>' . "\n";
				//END left content

				echo '</div>' . "\n";
				//END left

				if($show_right == 'true') {
					//START right
					echo '<div class="right">' . "\n";
					echo '<p class="eos-right-title">' . $right_title . '</p>' . "\n";

					//START right content
					echo '<div class="eos-right-content">' . "\n";
					echo $right_content . "\n";
					echo '</div>' . "\n";
					//END right content

					echo '</div>' . "\n";
					//END right
				}

				echo '</div>' . "\n";
				//END content

				echo '</div>' . "\n";
            }
			
            ?>
			<?php 
				$is_last_page = 'true'; 
				set_query_var('is_last_page', $is_last_page);
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
<!--

<div class="end-of-section">
	<div class="eos-title">
		<div>
			<p>Well done! You’ve finished:</p>
			<h2>Essays</h2>
		</div>
	</div>
	<div class="eos-content">
		<div class="left">
			<p class="eos-left-title">Key takeaways:</p>
			<div class="eos-left-content">

			</div>
		</div>
		<div class="right">
			<p class="eos-left-title">Want more? Try these resources:</p>
			<div class="eos-left-content">

			</div>
		</div>
	</div>
</div>
-->

<?php get_footer();

