<?php
/**
 * Template Name: Home Page
 *
 * Template for displaying the home page.
 * This template is specifically for the page with slug "home".
 *
 * Template Hierarchy:
 * - page-home.php (this file) - matches page with slug "home"
 * - page.php - fallback for any page
 * - index.php - final fallback
 *
 * @package RMIT_Learning_Lab
 * @version 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container" id="page-content">
    <div id="main-content"></div>

    <!-- START home banner -->
    <div class="home-banner">
        <span class="background-image" role="img" aria-label="A desk with a laptop, a house plant, a mug filled with pencils, an open book and a hot cup of coffee. Book shelves are shown in the background. There's a cat on the lower shelf."></span>
        <h1><?php esc_html_e('Learning Lab', 'rmit-learning-lab'); ?></h1>
    </div>
    <!-- END home banner -->

    <!-- START home intro -->
    <div class="home-intro">
        <p class="lead"><?php esc_html_e('The Learning Lab provides foundation skills and study support materials for writing, assessments, referencing, health sciences, physics, chemistry, maths and much more.', 'rmit-learning-lab'); ?></p>
        <p class="small" id="caption-text">
            <?php
            printf(
                __('Image by <a href="%1$s">RMIT</a>, licensed under <a href="%2$s">CC BY-NC 4.0</a>.', 'rmit-learning-lab'),
                esc_url('https://rmit.edu.au/'),
                esc_url('https://creativecommons.org/licenses/by/4.0/')
            );
            ?>
        </p>

        <!-- START search -->
        <div class="search-container label-side">
            <label for="searchInput">
                <h2 class="h4">
                    <?php esc_html_e('Search', 'rmit-learning-lab'); ?> <span class="visually-hidden"><?php esc_html_e('this website:', 'rmit-learning-lab'); ?></span>
                </h2>
            </label>
            <form role="search" method="get" action="/">
                <div class="input-group">
                    <input type="search" id="searchInput" class="form-control" name="s">
                    <button type="submit" id="searchButton" class="btn btn-primary">
                        <div class="mag-glass"></div>
                        <span class="visually-hidden"><?php esc_html_e('Search', 'rmit-learning-lab'); ?></span>
                    </button>
                </div>
            </form>
        </div>
        <!-- END search -->
    </div>
    <!-- END home intro -->

    <!-- START home panels -->
    <?php
    if ( have_posts() ) :
        while ( have_posts() ) : the_post();
            the_content();
        endwhile;
    else :
        echo '<p>' . esc_html__( 'Sorry, no content found.', 'rmit-learning-lab' ) . '</p>';
    endif;
    ?>
    <!-- END home panels -->
</div>

<?php
get_footer();