<?php
/**
 * Template Name: No side nav
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

set_query_var( 'hide_side_nav', true );
require get_stylesheet_directory() . '/page.php';
