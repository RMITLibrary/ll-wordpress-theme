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
// Plain WP-Cron on purpose. WP Engine's Alternate Cron is meant to curl wp-cron.php
// every minute; ll_blog_public_last_reset is how we tell whether it does.
//
// Capture window matters: SiteSucker must run while blog_public is 1. A capture
// taken after this has fired bakes noindex into every static page.
//-----------------------------

// TESTING ONLY. true resets hourly instead of nightly at 2am, so Alternate Cron can
// be checked in one sitting. While it is on the capture window is one hour.
if (!defined('RMIT_LL_BLOG_PUBLIC_TEST_HOURLY')) {
    define('RMIT_LL_BLOG_PUBLIC_TEST_HOURLY', true);
}

function rmit_ll_blog_public_recurrence()
{
    return RMIT_LL_BLOG_PUBLIC_TEST_HOURLY ? 'hourly' : 'daily';
}

function rmit_ll_blog_public_next_run()
{
    return RMIT_LL_BLOG_PUBLIC_TEST_HOURLY
        ? time() + HOUR_IN_SECONDS
        : (new DateTimeImmutable('tomorrow 02:00', wp_timezone()))->getTimestamp();
}

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

    // Changing the interval leaves the old event behind on the wrong recurrence.
    if (wp_next_scheduled('ll_restore_blog_public')
        && wp_get_schedule('ll_restore_blog_public') !== rmit_ll_blog_public_recurrence()) {
        wp_clear_scheduled_hook('ll_restore_blog_public');
    }

    if (!wp_next_scheduled('ll_restore_blog_public')) {
        wp_schedule_event(rmit_ll_blog_public_next_run(), rmit_ll_blog_public_recurrence(), 'll_restore_blog_public');
    }
});

add_action('ll_restore_blog_public', function () {
    update_option('blog_public', '0');

    // Proof the event actually fired, without needing WP Engine log access.
    // Read it with `wp option get ll_blog_public_last_reset`.
    update_option('ll_blog_public_last_reset', gmdate('c'));
});
