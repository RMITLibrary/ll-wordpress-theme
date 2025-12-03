<?php
// Exit if accessed directly.
defined('ABSPATH') || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?> class="nav-fixed">

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <?php
  wp_head();
  ?>

  <link href="https://rmitlibrary.github.io/cdn/learninglab/illustration/dev-fav-icon.png" rel="icon" type="image/x-icon" />
  <link href="https://rmitlibrary.github.io/cdn/learninglab/illustration/dev-fav-icon.png" rel="shortcut icon" type="image/x-icon" />

  <?php get_template_part('includes/dcterms-meta'); ?>

  <script>
    (function(w, d, s, l, i) {
      w[l] = w[l] || [];
      w[l].push({
        'gtm.start': new Date().getTime(),
        event: 'gtm.js'
      });
      var f = d.getElementsByTagName(s)[0],
        j = d.createElement(s),
        dl = l != 'dataLayer' ? '&l=' + l : '';
      j.async = true;
      j.src =
        'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
      f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer', 'GTM-MPHJK3H');
  </script>

  <meta name="msvalidate.01" content="8E4954E1DFAB7E2F8A92DD0A0BD6ED09">

  <style>
    <?php
    // Fix menu overlap bug in WordPress dev environment.
    if (is_admin_bar_showing()) {
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

  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MPHJK3H"
      height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->

  <?php wp_body_open(); ?>

  <header>
    <a href="#main-content" class="visually-hidden-focusable">Skip to main content</a>
    <div class="top-navigation">
      <div class="container">
        <div class="row">
          <div class="col-auto left-nav">
            <a href="https://www.rmit.edu.au/" class="rmit-logo"><span class="visually-hidden">RMIT University</span></a>
            <div class="h2">
              <?php /*
              <!-- Explicitly turn off one bit of text and turn on the other to deal with JAWS bug - https://github.com/alphagov/govuk-frontend/issues/1643 -->
              */ ?>
              <a href="/">
                <span aria-hidden="true">Learning Lab</span>
                <span class="visually-hidden">Learning Lab homepage</span>
              </a>
            </div>
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
                  aria-controls="context-menu">Click for main menu</button>
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
              if (function_exists('doContextMenuAccordion')) {
                echo doContextMenuAccordion('University essentials', 6823);
                echo doContextMenuAccordion('Writing fundamentals', 6825);
                echo doContextMenuAccordion('Assessments', 6828);
                echo doContextMenuAccordion('Referencing', 2545);
                echo doContextMenuAccordion('Digital skills', 15640);
              }
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
                      <?php /*
                                        <li><a href="/life-science/">Life science</a></li>
                                        */ ?>
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