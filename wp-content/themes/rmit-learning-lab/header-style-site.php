<?php
// Exit if accessed directly.
defined('ABSPATH') || exit;

?>
<!doctype html>
<html <?php language_attributes(); ?> class="nav-fixed">

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- wp_head -->
	<?php 
        // wp_head() outputs the following: 
        //      1. title attribute
        //
        // Via All in One SEO Plugin
        //      1. Meta tags, generic, OpenGraph and twitter
        //      2. JSON schema
        //
        // A Python script is deployed to change https://lab.bitm.app to
        // https://learninglab.rmit.edu.au/ before upload to prod
        //
        // Via other plug-ins
        // 1. MathJax script and link to jsdeliver.net
        // 2. Styles related to WordPress editor and plugins
        // 3. Site Stylesheets
        //
        // A Python script is deployed to remove some of these styles
        wp_head(); 
    ?>
	<!-- /wp_head -->

    <!-- START Additional meta tags not covered by wp_head -->
    <?php
        // A Python script is deployed to change https://lab.bitm.app to
        // https://learninglab.rmit.edu.au/ in the tags below before upload to prod
    ?>

    <link href="https://rmitlibrary.github.io/cdn/learninglab/illustration/dev-fav-icon-style.png" rel="icon" type="image/x-icon"/>
    <link href="https://rmitlibrary.github.io/cdn/learninglab/illustration/dev-fav-icon-style.png" rel="shortcut icon" type="image/x-icon"/>

    <meta name="author" content="RMIT Library">

    <meta name="geo.position" content="-37.807778, 144.963333">
    <meta name="geo.placename" content="Melbourne">
    <meta name="geo.region" content="AU">

    <meta name="dcterms.title" content="Learning Lab">
    
    <?php 
    if (is_singular()) { // Check if it's a single post or page
        $excerpt = get_the_excerpt();
        if($excerpt == "...") {
            echo '<meta name="dcterms.description" content="The Learning Lab provides foundation skills and study support materials for writing, science and more.">';
        }
        else
        {
            echo '<meta name="dcterms.description" content="' . esc_attr($excerpt) . '">';
        }

        echo "\n";
    }
    ?>
    <meta name="dcterms.type" content="Text">
    <?php echo '<meta name="dcterms.identifier" content="' . esc_url(get_permalink()) . '">'; echo "\n"; ?>
    <meta name="dcterms.format" content="text/html">
    <!-- END Additional meta tags not covered by wp_head -->

    <!-- START print styles -->


    <!-- START Additional scripts for tracking -->

    <!-- Google tag - (gtag.js) --> 
    <script async="" src="https://www.googletagmanager.com/gtag/js?id=G-VLHPB23GYR"></script> 
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-VLHPB23GYR');
    </script>

    <!-- Microsoft Bing - Not sure how useful this is? --> 
    <meta name="msvalidate.01" content="8E4954E1DFAB7E2F8A92DD0A0BD6ED09">

    <!-- END Additional scripts for tracking -->

    <style>
    <?php 
      // Fix menu overlap bug in WordPress dev environment.
      if ( is_admin_bar_showing() ) {
          echo '#wp-admin-bar-root-default { top: -1.5rem !important; }'; 
          echo '#wp-admin-bar-top-secondary { top: -1.5rem !important; }'; 
      }
    ?>
    </style>

    <!-- 
    DARK MODE javascript
    This script is placed in the <head> to ensure the theme is set as early as possible,
    reducing the flash of unstyled content (FOUC). It determines the user's preferred
    theme (either from local storage or system settings) and applies it immediately.
    -->
    <script>
        (function() {
            'use strict';

            const getStoredTheme = () => localStorage.getItem('theme');

            const getPreferredTheme = () => {
            const storedTheme = getStoredTheme();
            return storedTheme ? storedTheme : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            };

            const setTheme = theme => {
            const themeToSet = theme === 'auto' ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : theme;
            document.documentElement.setAttribute('data-bs-theme', themeToSet);
            };

            setTheme(getPreferredTheme());
        })();
    </script>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	
<header>
<a href="#main-content" class="visually-hidden-focusable">Skip to main content</a>
<div class="top-navigation" style="background-color: #005435">
    <div class="container">
        <div class="row">
            <div class="col-auto left-nav">
                <a href="https://www.rmit.edu.au/" class="rmit-logo"><span class="visually-hidden">RMIT University</span></a>
                <!--<div class="rmit-logo"><span class="visually-hidden">RMIT University logo</span></div>-->
				<h2>
					<!-- Explicitly turn off one bit of text and turn on the other to deal with JAWS bug - https://github.com/alphagov/govuk-frontend/issues/1643 -->
					<a href="/home">
						<span aria-hidden="true">Learning Lab</span>
						<span class="visually-hidden">Learning Lab homepage</span>
					</a>
				</h2>
            </div>
            <div class="col">       
                <ul>
                    <li class="hide-sm">
                        <a href="https://www.rmit.edu.au/library">
							<span aria-hidden="true">Library</span>
							<span class="visually-hidden">Library homepage</span>
						</a>
                    </li>
                    <li class="search">
                        <a href="/search/" id="search2">
                            <div class="search-label">Search</div>
                            <div class="mag-glass"></div>
                        </a>
                    </li>
                    <li class="menu">
                        <button id="menu-button" 
                        class="btn btn-primary collapsed" type="button" data-bs-toggle="collapse" 
                        data-bs-target="#context-menu" data-bs-display="static" aria-expanded="false" 
                        aria-controls="context-menu" style="background-color: #005435">Click for main menu</button>
                    </li>
                </ul> 
            </div>   
        </div>
    </div>
</div>
<nav id="context-menu" class="collapse" aria-label="Main Menu">
    <div class="container nav-container not-wordpress">
        <div class="row">
            <!-- START menu -->
            <div class="col-xl-8">
				<div class="accordion accordion-white" id="context-menu-accordion">	
						<?php 
                            //Identify the page ids of each landing page, doContextMenuAccordion 
                            //will generate the accordion code and list of child pages.
							              echo doContextMenuAccordion('University essentials', 6823);
							              echo doContextMenuAccordion('Writing fundamentals', 6825); 
                            echo doContextMenuAccordion('Assessments', 6828);
                            echo doContextMenuAccordion('Referencing', 2545);
                                        echo doContextMenuAccordion('Digital skills', 13243);
                        ?>
						<!-- START Subject support
                        special case. Effectively each of the child pages here is a section page. For the nav, however, we want toshow these under the banner of subject support. -->
						<div class="accordion-item">
							<h2 class="accordion-header" id="accordion-head-subject-support">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-body-subject-support" aria-expanded="false" aria-controls="accordion-body-subject-support">
								Subject support
							  	</button>
							</h2>
							<div id="accordion-body-subject-support" class="accordion-collapse collapse" aria-labelledby="accordion-head-subject-support" style="">
								<div class="accordion-body">
									<ul>
										<li><a href="/art-and-design/">Art and design</a></li>
										<li><a href="/chemistry/">Chemistry</a></li>
										<li><a href="/law/">Law</a></li>
										<li><a href="/life-science/">Life science</a></li>
										<li><a href="/maths-statistics/">Mathematics and statistics</a></li>
										<li><a href="/nursing/">Nursing</a></li>
										<li><a href="/physics/">Physics</a></li>
									</ul>
								</div>
							  </div>
						</div>
						<!-- END subject support - special case -->
                </div>	
            </div>
            <!-- END menu -->

            <!-- Start theme switcher -->
            <form class="theme-switch hamburger-menu">
                <fieldset>
                    <legend>Theme</legend>
                    <div class="bg">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="themeOptions" data-bs-theme-value="auto" aria-label="System">
                            <span class="form-check-label">System</span>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="themeOptions" data-bs-theme-value="light" aria-label="Light">
                            <span class="form-check-label">Light</span>
                        </div> 
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="themeOptions" data-bs-theme-value="dark" aria-label="Dark">
                            <span class="form-check-label">Dark</span>
                        </div>
                    </div>
                </fieldset>
            </form>
            <!-- End theme switcher -->
        </div>
    </div>
</nav>
</header>

	<main id='theme-main'>
