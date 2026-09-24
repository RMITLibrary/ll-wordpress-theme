<?php
/**
 * Template Name: Last content page
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

set_query_var( 'is_last_page', true );
require get_stylesheet_directory() . '/page.php';
