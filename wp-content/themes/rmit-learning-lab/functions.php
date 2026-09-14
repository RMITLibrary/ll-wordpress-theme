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
	$bootstrap_version = rmit_learning_lab_asset_version( 'js/bootstrap.bundle.min.js' );

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

    // Enqueue search functionality globally for static exports (except on search page)
	if ( ! is_page( 'search' ) ) {
		$search_home_version = rmit_learning_lab_asset_version( 'js/search-home.js' );

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
require_once $theme_includes_dir . 'seo-blog-public-reset.php';  // Nightly reset of "Discourage search engines" on non-production
require_once $theme_includes_dir . 'analytics-dashboards.php';   // Analytics dashboards functionality

/**
 * Cache-busting version for a local theme asset.
 *
 * Hashes the contents rather than reading filemtime: a deploy rewrites the mtime
 * of every theme file at once, which would change every asset URL on all 716
 * exported pages even when nothing changed.
 */
function rmit_learning_lab_asset_version( $relative_path ) {
	static $versions = array();

	if ( isset( $versions[ $relative_path ] ) ) {
		return $versions[ $relative_path ];
	}

	$candidates = array(
		trailingslashit( get_stylesheet_directory() ) . ltrim( $relative_path, '/' ),
		trailingslashit( get_template_directory() ) . ltrim( $relative_path, '/' ),
		trailingslashit( ABSPATH ) . ltrim( $relative_path, '/' ),
	);

	$versions[ $relative_path ] = null;
	foreach ( $candidates as $path ) {
		if ( file_exists( $path ) ) {
			$versions[ $relative_path ] = substr( md5_file( $path ), 0, 8 );
			break;
		}
	}

	return $versions[ $relative_path ];
}

/**
 * Swap the version on every theme-hosted asset for a content hash, whatever
 * enqueued it. Without this a deploy invalidates all of them at once.
 */
function rmit_learning_lab_hash_asset_src( $src ) {
	foreach ( array( get_stylesheet_directory_uri(), get_template_directory_uri() ) as $base ) {
		if ( 0 !== strpos( $src, $base ) ) {
			continue;
		}

		$relative = strtok( substr( $src, strlen( $base ) ), '?' );
		$version  = rmit_learning_lab_asset_version( $relative );

		if ( null !== $version ) {
			$src = add_query_arg( 'ver', $version, remove_query_arg( 'ver', $src ) );
		}

		break;
	}

	return $src;
}
add_filter( 'script_loader_src', 'rmit_learning_lab_hash_asset_src', 20 );
add_filter( 'style_loader_src', 'rmit_learning_lab_hash_asset_src', 20 );

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

	$iframe_loader_handle = 'rmit-learning-lab-iframe-loader';
	$iframe_loader_path   = 'js/iframe-loader.js';
	$iframe_loader_ver    = rmit_learning_lab_asset_version( $iframe_loader_path );

	$iframe_resizer_host_handle = 'rmit-learning-lab-iframe-resizer-host';
	$iframe_resizer_host_src    = 'https://rmitlibrary.github.io/cdn/libraries/js/iframeResizer.min.js';

	$iframe_resizer_content_handle = 'rmit-learning-lab-iframe-resizer-content';
	$iframe_resizer_content_path   = 'js/iframeResizer.contentWindow.min.js';
	$iframe_resizer_content_ver    = rmit_learning_lab_asset_version( $iframe_resizer_content_path );

	$lti_resize_handle = 'rmit-learning-lab-lti-trigger-resize';
	$lti_resize_src    = 'https://rmitlibrary.github.io/cdn/libraries/js/ltiTriggerResize.js';

	wp_enqueue_script(
		$iframe_resizer_host_handle,
		$iframe_resizer_host_src,
		array(),
		null,
		true
	);

	wp_enqueue_script(
		$iframe_resizer_content_handle,
		$theme_uri . $iframe_resizer_content_path,
		array(),
		$iframe_resizer_content_ver,
		true
	);

	wp_enqueue_script(
		$lti_resize_handle,
		$lti_resize_src,
		array(),
		null,
		true
	);

	wp_register_script(
		$iframe_loader_handle,
		$theme_uri . $iframe_loader_path,
		array(
			$iframe_resizer_host_handle,
			$iframe_resizer_content_handle,
			$lti_resize_handle
		),
		$iframe_loader_ver,
		true
	);

	wp_localize_script(
		$iframe_loader_handle,
		'RMITIframeAssets',
		array(
			'host'    => $iframe_resizer_host_src,
			'content' => $theme_uri . 'js/iframeResizer.contentWindow.min.js',
			'lti'     => $lti_resize_src,
		)
	);

	wp_enqueue_script( $iframe_loader_handle );
}, 200 );

// Search index is now loaded lazily via JavaScript as users interact with search,
// so the previous global prefetch hints are intentionally removed.
