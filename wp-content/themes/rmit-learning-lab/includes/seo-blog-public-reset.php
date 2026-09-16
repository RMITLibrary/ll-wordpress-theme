<?php

//-----------------------------
// NIGHTLY "DISCOURAGE SEARCH ENGINES" RESET
//
// blog_public gets switched on for one-off crawls and then forgotten. This puts
// it back at 2am site time on the non-production hosts listed below.
//
// Keyed off the host, not wp_get_environment_type(): nothing sets
// WP_ENVIRONMENT_TYPE here, so that returned 'production' on DEV as well and the
// event was cleared everywhere instead of ever being scheduled. An unlisted host
// is still a no-op rather than a self-deindex.
//-----------------------------

add_action('init', function () {
    $non_production_hosts = array(
        'devlearninglab.wpenginepowered.com',
        'll-wordpress-theme.test',
    );

    if (!in_array(wp_parse_url(home_url(), PHP_URL_HOST), $non_production_hosts, true)) {
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
