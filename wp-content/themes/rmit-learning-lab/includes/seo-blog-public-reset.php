<?php

//-----------------------------
// NIGHTLY "DISCOURAGE SEARCH ENGINES" RESET
//
// blog_public gets switched on for one-off crawls and then forgotten. This puts
// it back at 2am site time. Never runs on production: wp_get_environment_type()
// returns 'production' when WP_ENVIRONMENT_TYPE is unset, so an unconfigured
// install is a no-op rather than a self-deindex.
//-----------------------------

add_action('init', function () {
    if (wp_get_environment_type() === 'production') {
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
