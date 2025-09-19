<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

//this function resides in includes/redirect.php
output_redirect_404_script_and_html();

get_footer();

