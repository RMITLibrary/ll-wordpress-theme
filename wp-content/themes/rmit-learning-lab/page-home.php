<?php
/**
 *
 * Template for displaying the home page. - This is only applied to the page with slug "home"
 *
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container" id="page-content">
<div id="main-content"></div>
    <!-- START home banner -->
    <div class="home-banner">
        <span class="background-image" role="img" aria-label="A desk with a laptop, a house plant, a mug filled with pencils, an open book and a hot cup of coffee. Book shelves are shown in the background. There’s a cat on the lower shelf."></span>
        <h1>Learning Lab</h1>
    </div>
    <!-- END home banner -->
        <!-- START home intro -->
    <div class="home-intro">
        <p class="lead">The Learning Lab provides foundation skills and study support materials for writing, assessments, referencing, health sciences, physics, chemistry, maths and much more.</p>
        <p class="small" id="caption-text">Image by <a href="https://rmit.edu.au/">RMIT</a>, licensed under <a href="https://creativecommons.org/licenses/by/4.0/">CC BY-NC 4.0</a>.</p>
        
        <!-- START search -->
        <div class="search-container label-side">
            <label for="search">
            <h2 class="h4">
                Search <span class="visually-hidden">this website:</span>
            </h2>
            </label>
            <div class="input-group">
                <input type="search" id="searchInput" class="form-control">
                <button type="submit"  id="searchButton" class="btn btn-primary"><div class="mag-glass"></div><span class="visually-hidden">Search</span></button>
            </div>
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
        _e( 'Sorry, no posts matched your criteria.', 'textdomain' );
    endif;
    ?>
    <!--<div class="home-panel-container">
        <a href="/university-essentials/" class="home-panel">
            <img src="https://rmitlibrary.github.io/cdn/learninglab/illustration/landing/uni-essentials.png" alt="" />
            <h2 class="link-large">University essentials</h2>
            <p>New to uni? University essentials has you covered. Find out more about topics as diverse as group work, critical thinking and even artificial intelligence.</p>
        </a>
        
        <a href="/writing-fundamentals/" class="home-panel">
            <img src="https://rmitlibrary.github.io/cdn/learninglab/illustration/landing/writing.png" alt="" />
            <h2 class="link-large">Writing fundamentals</h2>
            <p>Essential resources for writing skills needed in tertiary study. From paragraph structure to academic style, your writing needs are covered.</p>
        </a>
        <a href="/assessments/" class="home-panel">
            <img src="https://rmitlibrary.github.io/cdn/learninglab/illustration/landing/assessments.png" alt="" />
            <h2 class="link-large">Assessments</h2>
            <p>All the resources to help you get started with your assessments. Get assistance structuring essays, presentations, reports and more.</p>
        </a>
        <a href="/referencing/" class="home-panel">
            <img src="https://rmitlibrary.github.io/cdn/learninglab/illustration/landing/referencing.png" alt="" />
            <h2 class="link-large">Referencing</h2>
            <p>Find out how to correctly use different referencing styles in academic writing to avoid plagiarism and get better marks. </p>
        </a>
        <a href="/subject-areas/" class="home-panel">
            <img src="https://rmitlibrary.github.io/cdn/learninglab/illustration/landing/subject-support.png" alt="" />
            <h2 class="link-large">Subject support</h2>
            <p>Specific resources for specific subjects. Whether it's maths or art, science or nursing, subject support can help.</p>
        </a>
    </div> -->
    <!-- END home panels -->
</div>
<!-- script to punt search input to /search via query string -->
<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/search-home.js?v=1.0.0"></script>
<?php get_footer();
