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
// The event is scheduled the normal way, but it has never been observed firing on
// PRD, so an overdue event is also run inline on the next request of any kind.
// ll_blog_public_last_reset records which path did it.
//
// Capture window matters: SiteSucker must run while blog_public is 1. A capture
// taken after this has fired bakes noindex into every static page.
//-----------------------------

// TESTING ONLY. Seconds between resets; 0 restores the nightly 2am schedule.
// While this is non-zero the capture window is only this long — do not leave it set.
if (!defined('RMIT_LL_BLOG_PUBLIC_TEST_INTERVAL')) {
    define('RMIT_LL_BLOG_PUBLIC_TEST_INTERVAL', 5 * MINUTE_IN_SECONDS);
}

add_filter('cron_schedules', function ($schedules) {
    if (RMIT_LL_BLOG_PUBLIC_TEST_INTERVAL > 0) {
        $schedules['ll_blog_public_test'] = array(
            'interval' => RMIT_LL_BLOG_PUBLIC_TEST_INTERVAL,
            'display'  => 'Discourage-search-engines reset (testing)',
        );
    }

    return $schedules;
});

function rmit_ll_blog_public_is_discouraged_host()
{
    $discourage_hosts = array(
        'prdlearninglab.wpenginepowered.com',
        'devlearninglab.wpenginepowered.com',
        'll-wordpress-theme.test',
    );

    return in_array(wp_parse_url(home_url(), PHP_URL_HOST), $discourage_hosts, true);
}

function rmit_ll_blog_public_recurrence()
{
    return RMIT_LL_BLOG_PUBLIC_TEST_INTERVAL > 0 ? 'll_blog_public_test' : 'daily';
}

function rmit_ll_blog_public_next_run()
{
    if (RMIT_LL_BLOG_PUBLIC_TEST_INTERVAL > 0) {
        return time() + RMIT_LL_BLOG_PUBLIC_TEST_INTERVAL;
    }

    return (new DateTimeImmutable('tomorrow 02:00', wp_timezone()))->getTimestamp();
}

add_action('init', function () {
    if (!rmit_ll_blog_public_is_discouraged_host()) {
        wp_clear_scheduled_hook('ll_restore_blog_public');
        return;
    }

    $next = wp_next_scheduled('ll_restore_blog_public');

    // Switching between the test interval and the nightly one leaves the old event
    // behind, so drop anything on the wrong recurrence.
    if ($next && wp_get_schedule('ll_restore_blog_public') !== rmit_ll_blog_public_recurrence()) {
        wp_clear_scheduled_hook('ll_restore_blog_public');
        $next = false;
    }

    if (!$next) {
        wp_schedule_event(rmit_ll_blog_public_next_run(), rmit_ll_blog_public_recurrence(), 'll_restore_blog_public');
        return;
    }

    // Overdue means WP-Cron never ran it. Do it here and move the event on.
    if ($next <= time()) {
        wp_unschedule_event($next, 'll_restore_blog_public');
        do_action('ll_restore_blog_public', 'inline');
        wp_schedule_event(rmit_ll_blog_public_next_run(), rmit_ll_blog_public_recurrence(), 'll_restore_blog_public');
    }
});

add_action('ll_restore_blog_public', function ($source = '') {
    // do_action() with no args passes '', not the parameter default.
    $source = $source ?: 'cron';

    update_option('blog_public', '0');

    // Whether WP-Cron ever fires this on PRD is the open question, and WP Engine log
    // access is awkward. Read it with `wp option get ll_blog_public_last_reset`.
    update_option('ll_blog_public_last_reset', gmdate('c') . ' ' . $source);
});
