<?php
/*
 ____  __  __ ___ _____   _                           _               _          _
|  _ \|  \/  |_ _|_   _| | |    ___  __ _ _ __ _ __   (_)_ __   __ _  | |    __ _| |__
| |_) | |\/| || |  | |   | |   / _ \/ _` | '__| '_ \  | | '_ \ / _` | | |   / _` | '_ \
|  _ <| |  | || |  | |   | |__|  __/ (_| | |  | | | | | | | | | (_| | | |__| (_| | |_) |
|_| \_\_|  |_|___| |_|   |_____\___|\__,_|_|  |_| |_| |_|_| |_|\__, | |_____\__,_|_.__/
                                                               |___/

********************* RMIT LEARNING LAB WORDPRESS THEME *********************
*                                                                           *
* Built on Picostrap5 - Bootstrap 5.3.3 WordPress Starter Theme           *
* Developed by Digital Learning Team for RMIT Learning Lab                *
*                                                                           *
***************************************************************************
*/

// DE-ENQUEUE PARENT THEME BOOTSTRAP JS BUNDLE
add_action( 'wp_print_scripts', function(){
    wp_dequeue_script( 'bootstrap5' );
}, 100 );

// ENQUEUE THE BOOTSTRAP JS BUNDLE (AND EVENTUALLY MORE LIBS) FROM THE CHILD THEME DIRECTORY
add_action( 'wp_enqueue_scripts', function() {
    //enqueue js in footer, defer

    //wp_enqueue_script( 'bootstrap5-childtheme', get_stylesheet_directory_uri() . "/js/bootstrap.bundle.min.js#deferload", array(), null, true );
	//LC replaced the enqueue script above with the line below... to fix a problem with picostrap theme and the latest version of WordPress
	$bootstrap_path    = get_stylesheet_directory() . '/js/bootstrap.bundle.min.js';
	$bootstrap_version = file_exists( $bootstrap_path ) ? filemtime( $bootstrap_path ) : null;

	wp_enqueue_script(
		'bootstrap5-childtheme',
		get_stylesheet_directory_uri() . '/js/bootstrap.bundle.min.js',
		array(),
		$bootstrap_version,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

    //optional: lottie (maybe...)
    //wp_enqueue_script( 'lottie-player', 'https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js#deferload', array(), null, true );

    //optional: rellax
    //wp_enqueue_script( 'rellax', 'https://cdnjs.cloudflare.com/ajax/libs/rellax/1.12.1/rellax.min.js#deferload', array(), null, true );

}, 101);

// ENQUEUE YOUR CUSTOM JS FILES, IF NEEDED
add_action( 'wp_enqueue_scripts', function() {

    // Enqueue search functionality for home page
    if ( is_front_page() ) {
		$search_home_path    = get_stylesheet_directory() . '/js/search-home.js';
		$search_home_version = file_exists( $search_home_path ) ? filemtime( $search_home_path ) : null;

		wp_enqueue_script(
			'search-home',
			get_stylesheet_directory_uri() . '/js/search-home.js',
			array(),
			$search_home_version,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
    }

    //UNCOMMENT next row to include the js/custom.js file globally
    //wp_enqueue_script('custom', get_stylesheet_directory_uri() . '/js/custom.js#deferload', array(/* 'jquery' */), null, true);

    //UNCOMMENT next 3 rows to load the js file only on one page
    //if (is_page('mypageslug')) {
    //    wp_enqueue_script('custom', get_stylesheet_directory_uri() . '/js/custom.js#deferload', array(/* 'jquery' */), null, true);
    //}

}, 102);

// OPTIONAL: ADD MORE NAV MENUS
//register_nav_menus( array( 'third' => __( 'Third Menu', 'picostrap' ), 'fourth' => __( 'Fourth Menu', 'picostrap' ), 'fifth' => __( 'Fifth Menu', 'picostrap' ), ) );
// THEN USE SHORTCODE:  [lc_nav_menu theme_location="third" container_class="" container_id="" menu_class="navbar-nav"]

// CHECK PARENT THEME VERSION as Bootstrap 5.2 requires an updated SCSSphp, so picostrap5 v2 is required
add_action( 'admin_notices', function  () {
    if( (pico_get_parent_theme_version())>=2.1) return;
	$message = __( 'This Child Theme requires at least Picostrap Version 2.1.0  in order to work properly. Please update the parent theme.', 'picostrap' );
	printf( '<div class="%1$s"><h1>%2$s</h1></div>', esc_attr( 'notice notice-error' ), esc_html( $message ) );
} );

// FOR SECURITY: DISABLE APPLICATION PASSWORDS. Remove if needed (unlikely!)
add_filter( 'wp_is_application_passwords_available', '__return_false' );

// THEME FUNCTIONALITY INCLUDES
// Organized into separate files for better maintainability

$theme_includes_dir = get_stylesheet_directory() . '/includes/';

require_once $theme_includes_dir . 'helper-utils.php';           // Common utility functions
require_once $theme_includes_dir . 'admin-customizations.php';   // WordPress admin customizations
require_once $theme_includes_dir . 'breadcrumbs-navigation.php'; // Breadcrumbs and navigation functions
require_once $theme_includes_dir . 'content-filters.php';        // Content filtering and query modifications
require_once $theme_includes_dir . 'custom-taxonomy.php';        // Custom taxonomy registration and ACF filters
require_once $theme_includes_dir . 'custom-shortcodes.php';      // Custom shortcodes manager - auto-discovers shortcode files
require_once $theme_includes_dir . 'json-export.php';            // Exports the site data to json. Required for search to function
require_once $theme_includes_dir . 'redirect.php';               // Redirect and 404 code for both admin and client side
require_once $theme_includes_dir . 'seo-noindex-inheritance.php'; // Noindex inheritance for work in progress pages
require_once $theme_includes_dir . 'analytics-dashboards.php';   // Analytics dashboards functionality

/**
 * Utility for cache-busting local theme assets based on file modification time.
 */
function rmit_learning_lab_asset_version( $relative_path ) {
	$relative_path = ltrim( $relative_path, '/' );
	$theme_path    = trailingslashit( get_stylesheet_directory() ) . $relative_path;
	if ( file_exists( $theme_path ) ) {
		return filemtime( $theme_path );
	}

	$root_path = trailingslashit( ABSPATH ) . $relative_path;
	if ( file_exists( $root_path ) ) {
		return filemtime( $root_path );
	}

	return null;
}

add_action('wp_head', function () {
	$origins = array(
		'https://cdn.jsdelivr.net',
		'https://www.googletagmanager.com',
		'https://www.rmit.edu.au',
		'https://rmitlibrary.github.io',
	);

	foreach ($origins as $origin) {
		echo '<link rel="preconnect" href="' . esc_url($origin) . '" crossorigin>' . "\n";
	}
}, 1);

/**
 * Enqueue print stylesheet with automatic versioning.
 */
add_action( 'wp_enqueue_scripts', function() {
	$print_handle  = 'rmit-learning-lab-print';
	$print_path    = 'print.css';
	$print_version = rmit_learning_lab_asset_version( $print_path );
	wp_enqueue_style(
		$print_handle,
		trailingslashit( get_stylesheet_directory_uri() ) . $print_path,
		array(),
		$print_version,
		'print'
	);
});

/**
 * Replace default stylesheet version with the compiled bundle's mtime.
 */
add_filter( 'style_loader_src', function( $src, $handle ) {
	if ( 'picostrap-styles' === $handle ) {
	$version = rmit_learning_lab_asset_version( 'css-output/bundle.css' );
		if ( null !== $version ) {
			$src = remove_query_arg( 'ver', $src );
			$src = add_query_arg( 'ver', $version, $src );
		}
	}

	return $src;
}, 10, 2 );

/**
 * Register theme-specific scripts with automatic cache-busting.
 */
add_action( 'wp_enqueue_scripts', function() {
	$theme_uri  = trailingslashit( get_stylesheet_directory_uri() );
	$main_handle = 'rmit-learning-lab-main-body';
	$main_path   = 'js/main-body.js';
	$main_ver    = rmit_learning_lab_asset_version( $main_path );
	wp_register_script(
		$main_handle,
		$theme_uri . $main_path,
		array(),
		$main_ver,
		true
	);
	wp_enqueue_script( $main_handle );

	$iframe_resizer_handle = 'rmit-learning-lab-iframe-resizer';
	$iframe_resizer_path   = 'js/iframeResizer.min.js';
	$iframe_resizer_ver    = rmit_learning_lab_asset_version( $iframe_resizer_path );
	wp_register_script(
		$iframe_resizer_handle,
		$theme_uri . $iframe_resizer_path,
		array(),
		$iframe_resizer_ver,
		true
	);
	wp_enqueue_script( $iframe_resizer_handle );
	wp_add_inline_script( $iframe_resizer_handle, 'if (typeof iFrameResize === "function") { iFrameResize({ log: false }); }', 'after' );

	$iframe_resizer_content_handle = 'rmit-learning-lab-iframe-resizer-content';
	$iframe_resizer_content_path   = 'js/iframeResizer.contentWindow.min.js';
	$iframe_resizer_content_ver    = rmit_learning_lab_asset_version( $iframe_resizer_content_path );
	wp_register_script(
		$iframe_resizer_content_handle,
		$theme_uri . $iframe_resizer_content_path,
		array(),
		$iframe_resizer_content_ver,
		true
	);
	wp_enqueue_script( $iframe_resizer_content_handle );

	$lti_trigger_handle = 'rmit-learning-lab-lti-trigger';
	$lti_trigger_path   = 'js/ltiTriggerResize.js';
	$lti_trigger_ver    = rmit_learning_lab_asset_version( $lti_trigger_path );
	wp_register_script(
		$lti_trigger_handle,
		$theme_uri . $lti_trigger_path,
		array(),
		$lti_trigger_ver,
		true
	);
	wp_enqueue_script( $lti_trigger_handle );
}, 200 );

/**
 * Add cache-busted preload/prefetch hints for JSON index files.
 */
add_action( 'wp_head', function() {
	$prefetch_files = array(
		'wp-content/uploads/pages-urls.json',
		'wp-content/uploads/pages.json',
	);

	foreach ( $prefetch_files as $relative_path ) {
		$version = rmit_learning_lab_asset_version( $relative_path );
		$href    = esc_url( home_url( '/' . ltrim( $relative_path, '/' ) ) );
		if ( $version ) {
			$href = add_query_arg( 'ver', $version, $href );
		}
		echo '<link rel="prefetch" href="' . $href . '">' . "\n";
	}
}, 20 );
