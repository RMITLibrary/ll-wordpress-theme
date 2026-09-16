<?php

//-----------------------------
// NIGHTLY "DISCOURAGE SEARCH ENGINES" RESET
//
// None of these WordPress installs is the public site — that is the static export
// — so every one of them gets "Discourage search engines" put back on overnight.
// It gets switched off by hand so SiteSucker can capture PRD, then forgotten.
//
// Keyed off the host, not wp_get_environment_type(): nothing sets
// WP_ENVIRONMENT_TYPE here, so that returned 'production' on every environment and
// the event was cleared instead of ever being scheduled. An unlisted host stays a
// no-op rather than a self-deindex.
//
// Capture window matters: SiteSucker must run while blog_public is 1. A capture
// taken after this has fired bakes noindex into every static page.
//-----------------------------

add_action('init', function () {
    $discourage_hosts = array(
        'prdlearninglab.wpenginepowered.com',
        'devlearninglab.wpenginepowered.com',
        'll-wordpress-theme.test',
    );

    if (!in_array(wp_parse_url(home_url(), PHP_URL_HOST), $discourage_hosts, true)) {
        wp_clear_scheduled_hook('ll_restore_blog_public');
        return;
    }

    if (!wp_next_scheduled('ll_restore_blog_public')) {
        $next = new DateTimeImmutable('tomorrow 02:00', wp_timezone());
        wp_schedule_event($next->getTimestamp(), 'daily', 'll_restore_blog_public');
    }
});

add_action('ll_restore_blog_public', function () {
    update_option('blog_public', '0');
});
