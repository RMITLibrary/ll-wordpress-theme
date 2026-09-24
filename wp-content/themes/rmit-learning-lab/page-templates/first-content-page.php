<?php
/**
 * Template Name: First content page
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

set_query_var( 'is_first_page', true );
require get_stylesheet_directory() . '/page.php';
