<?php
//-----------------------------
//	export_content_to_json

//	Creates a json file of all page data. Intended for use as a dataset for search functionality

//	Called from:	export_json_page()

//	Expected output

function export_content_to_json() {
    // Query WordPress content
    $args = array(
        'post_type' => 'page',
        'post_status' => 'publish', // Only fetch published pages
        'posts_per_page' => -1, // Get all pages
    );

    $query = new WP_Query($args);
    $posts_data = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $permalink = get_permalink();
            $path = wp_parse_url($permalink, PHP_URL_PATH);

            if (rmit_ll_is_work_in_progress_path($path)) {
                continue;
            }

            $relative_link = !empty($path) ? $path : '/';
            $content = get_the_content();
            //$content = strip_tags(strip_shortcodes($content)); // Remove shortcodes and HTML tags

            $content = strip_tags($content); // Remove HTML tags

            // Extract content from specific shortcodes
            $shortcodes = array('ll-accordion', 'transcript', 'transcript-accordion', 'lightweight-accordion', 'hl', 'highlight-text');
            foreach ($shortcodes as $shortcode) {
                $pattern = sprintf('/\[%1$s\](.*?)\[\/%1$s\]/s', preg_quote($shortcode, '/'));
                if (preg_match_all($pattern, get_the_content(), $matches)) {
                    foreach ($matches[1] as $match) {
                        $content .= ' ' . strip_tags($match);
                    }
                }
            }
            
            // Remove remaining shortcodes
            $content = strip_shortcodes($content);

            // Get taxonomy terms using ACF
            $llkeywords = get_field('field_6527440d6f9a2');
            $keywords = [];
            if ($llkeywords) {
                foreach ($llkeywords as $term) {
                    $keywords[] = $term->name;
                }
            }

            $breadcrumbs = get_breadcrumbs(get_the_ID());

            $posts_data[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'content' => $content,
                'excerpt' => strip_tags(strip_shortcodes(get_the_excerpt())), // Remove shortcodes and HTML tags
                'date' => get_the_date(),
                'link' => $relative_link, // Path-only link for search consumers
                'keywords' => $keywords,
                'breadcrumbs' => $breadcrumbs
            );
        }
        wp_reset_postdata();
    }

    // Convert to JSON
    $json_data = json_encode($posts_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    // Save to a file
    $file_path = rmit_ll_get_export_file_path( 'pages.json' );
    if (is_wp_error($file_path)) {
        return $file_path;
    }

    if (false === file_put_contents($file_path, $json_data)) {
        return new WP_Error('json_export_write_failed', sprintf('Unable to write export file: %s', $file_path));
    }

    clearstatcache(true, $file_path);

    return $file_path;
}

//-----------------------------
//	export_page_urls_to_json
//
//	Creates a json file that contains only page URLs (paths) for all published pages
//
//	Called from:	export_json_page()
//
function export_page_urls_to_json() {
    // Query WordPress pages
    $args = array(
        'post_type' => 'page',
        'post_status' => 'publish', // Only fetch published pages
        'posts_per_page' => -1, // Get all pages
    );

    $query = new WP_Query($args);
    $urls = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            // Use path-only to be consistent with pages.json
            $permalink = get_permalink();
            $path = wp_parse_url($permalink, PHP_URL_PATH);
            if (rmit_ll_is_work_in_progress_path($path)) {
                continue;
            }
            $relative_path = !empty($path) ? $path : '/';
            $urls[] = $relative_path;
        }
        wp_reset_postdata();
    }

    // Convert to JSON
    $json_data = json_encode($urls, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    // Save to a file
    $file_path = rmit_ll_get_export_file_path( 'pages-urls.json' );
    if (is_wp_error($file_path)) {
        return $file_path;
    }

    if (false === file_put_contents($file_path, $json_data)) {
        return new WP_Error('json_export_write_failed', sprintf('Unable to write export file: %s', $file_path));
    }

    clearstatcache(true, $file_path);

    return $file_path;
}

function rmit_ll_get_export_file_path($filename) {
    $uploads = wp_get_upload_dir();
    if (!empty($uploads['error'])) {
        return new WP_Error('json_export_no_uploads', $uploads['error']);
    }

    if (empty($uploads['basedir'])) {
        return new WP_Error('json_export_no_uploads', 'Uploads directory is not available.');
    }

    $export_dir = trailingslashit($uploads['basedir']);

    if (!wp_mkdir_p($export_dir)) {
        return new WP_Error('json_export_directory_unwritable', 'Unable to create uploads directory for export.');
    }

    return $export_dir . $filename;
}

function rmit_ll_get_export_file_meta($filename, $label) {
    $path = rmit_ll_get_export_file_path($filename);
    if (is_wp_error($path)) {
        return $path;
    }

    $uploads = wp_get_upload_dir();
    $url = !empty($uploads['baseurl']) ? trailingslashit($uploads['baseurl']) . $filename : '';

    clearstatcache(true, $path);
    $exists = file_exists($path);

    return array(
        'label' => $label,
        'filename' => $filename,
        'path' => $path,
        'url' => $url,
        'exists' => $exists,
        'modified' => $exists ? filemtime($path) : null,
        'size' => $exists ? filesize($path) : null,
    );
}

function rmit_ll_get_timezone_label() {
    $timezone = wp_timezone();
    $timezone_name = $timezone instanceof DateTimeZone ? $timezone->getName() : get_option('timezone_string');
    if (empty($timezone_name)) {
        $timezone_name = 'UTC';
    }

    $timezone_abbr = wp_date('T', current_time('timestamp', true));
    if (!empty($timezone_abbr) && stripos($timezone_name, $timezone_abbr) === false) {
        $timezone_name .= ' (' . $timezone_abbr . ')';
    }

    return $timezone_name;
}

function rmit_ll_store_export_history($key, $path) {
    $history = get_option('rmit_ll_export_history', array());
    $history[$key] = array(
        'timestamp' => time(),
        'path' => $path,
    );
    update_option('rmit_ll_export_history', $history, false);
}

/**
 * Determine whether a relative path belongs to an excluded section.
 *
 * @param string|null $path Path portion of a permalink.
 * @return bool True when the path should be excluded from exports.
 */
function rmit_ll_is_work_in_progress_path($path) {
    if (empty($path) || !is_string($path)) {
        return false;
    }

    return (bool) preg_match('~(?:^|/)(?:work-in-progress|documentation)(?:/|$)~', $path);
}

//-----------------------------
//	get_breadcrumbs
//	Generates a breadcrumb trail for a given post/page

//	Called from:	export_content_to_json() - Custom export function

//	Returns:		Array of breadcrumb items with title and link

//	Usage:			Used to add breadcrumb data to JSON export

function get_breadcrumbs($post_id) {
    $breadcrumbs = array();
    $parent_id = wp_get_post_parent_id($post_id);

    // Traverse up to get all parent pages
    while ($parent_id) {
        $page = get_post($parent_id);
        array_unshift($breadcrumbs, array(
            'title' => get_the_title($page->ID),
            'link' => get_permalink($page->ID)
        ));
        $parent_id = wp_get_post_parent_id($page->ID);
    }

    // Add the current page
    $breadcrumbs[] = array(
        'title' => get_the_title($post_id),
        'link' => get_permalink($post_id)
    );

    return $breadcrumbs;
}

//-----------------------------
//	register_export_page

//	Creates a new admin menu page for exporting content to JSON

//	Called from:	add_action('admin_menu', 'register_export_page');

//	calls:			add_menu_page() - WordPress function

//	usage:			Automatically called by WordPress when admin menu is built

function register_export_page() {
    add_menu_page(
        'Export Content to JSON',
        'Export JSON',
        'manage_options',
        'export-json',
        'export_json_page'
    );
}
add_action('admin_menu', 'register_export_page');

//-----------------------------
//	export_json_page

//	Generates the admin page with a button to export content to JSON

//	Called from:	register_export_page

//	calls:			export_content_to_json() - custom function

//	usage:			Triggered by form submission on the admin page

function export_json_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $export_tasks = array(
        'pages' => array(
            'label' => 'Content dataset',
            'description' => 'Full page content used by Fuse search.',
            'filename' => 'pages.json',
            'callback' => 'export_content_to_json',
        ),
        'urls' => array(
            'label' => 'URL index',
            'description' => 'Path-only list used to guard redirects and search UX.',
            'filename' => 'pages-urls.json',
            'callback' => 'export_page_urls_to_json',
        ),
    );

    $timezone_label = rmit_ll_get_timezone_label();

    $notices = array(
        'errors' => array(),
        'success' => array(),
    );
    $success_exports = array();

    if (isset($_POST['export_json'])) {
        check_admin_referer('export_json_action', 'export_json_nonce');

        foreach ($export_tasks as $key => $task) {
            $result = call_user_func($task['callback']);
            if (is_wp_error($result)) {
                $notices['errors'][] = sprintf('%s export failed: %s', $task['label'], $result->get_error_message());
            } else {
                $success_exports[$key] = $result;
                rmit_ll_store_export_history($key, $result);
            }
        }
    }

    $export_history = get_option('rmit_ll_export_history', array());

    $file_statuses = array();
    foreach ($export_tasks as $key => $task) {
        $meta = rmit_ll_get_export_file_meta($task['filename'], $task['label']);
        $file_statuses[$key] = $meta;

        if (is_wp_error($meta)) {
            $message = sprintf('%s unavailable: %s', $task['label'], $meta->get_error_message());
            if (!in_array($message, $notices['errors'], true)) {
                $notices['errors'][] = $message;
            }
        }
    }

    $fuse_index_meta = rmit_ll_get_export_file_meta('pages-index.json', 'Fuse.js index');
    $fuse_index_recorded_time = isset($export_history['fuse_index']['timestamp']) ? (int) $export_history['fuse_index']['timestamp'] : null;
    if (!$fuse_index_recorded_time && !is_wp_error($fuse_index_meta) && !empty($fuse_index_meta['modified'])) {
        $fuse_index_recorded_time = (int) $fuse_index_meta['modified'];
    }

    $current_gmt = time();

    $should_build_index = isset($success_exports['pages']);

    $export_builder_handle = 'rmit-learning-lab-export-index';
    $export_builder_path   = 'js/export-index-builder.js';
    $export_builder_ver    = rmit_learning_lab_asset_version($export_builder_path);

    wp_enqueue_script(
        $export_builder_handle,
        get_stylesheet_directory_uri() . '/' . $export_builder_path,
        array(),
        $export_builder_ver,
        true
    );

    $pages_meta = isset($file_statuses['pages']) ? $file_statuses['pages'] : null;
    $pages_version = time();
    if ($pages_meta && !is_wp_error($pages_meta) && !empty($pages_meta['modified'])) {
        $pages_version = (int) $pages_meta['modified'];
    }

    $localized_data = array(
        'shouldBuild'    => $should_build_index,
        'ajaxUrl'        => admin_url('admin-ajax.php'),
        'nonce'          => wp_create_nonce('rmit_ll_save_fuse_index'),
        'pagesJson'      => trailingslashit(content_url()) . 'uploads/pages.json' . ( $pages_version ? '?ver=' . $pages_version : '' ),
        'statusSelector' => '#rmit-export-index-status',
        'keys'           => array('title', 'content', 'keywords'),
        'fuseUrl'        => 'https://cdn.jsdelivr.net/npm/fuse.js',
        'messages'       => array(
            'starting' => __('Building Fuse.js index…', 'rmit-learning-lab'),
            'saving'   => __('Saving Fuse.js index…', 'rmit-learning-lab'),
            'success'  => __('Fuse.js index updated.', 'rmit-learning-lab'),
            'failure'  => __('Unable to generate Fuse.js index.', 'rmit-learning-lab'),
        ),
        'labels'         => array(
            'viewJson'     => __('View JSON', 'rmit-learning-lab'),
            'noFile'       => __('No file available', 'rmit-learning-lab'),
            'notGenerated' => __('Not generated yet', 'rmit-learning-lab'),
        ),
    );

    if (!is_wp_error($fuse_index_meta) && !empty($fuse_index_meta['modified'])) {
        $localized_data['indexLastModified'] = (int) $fuse_index_meta['modified'];
    }

    wp_localize_script($export_builder_handle, 'RMITExportIndex', $localized_data);

    if (!empty($success_exports)) {
        $success_items = array();
        foreach ($success_exports as $key => $path) {
            $meta = $file_statuses[$key];
            $recorded_time = isset($export_history[$key]['timestamp']) ? (int) $export_history[$key]['timestamp'] : null;
            if (!$recorded_time && !is_wp_error($meta) && !empty($meta['modified'])) {
                $recorded_time = (int) $meta['modified'];
            }

            if (is_wp_error($meta)) {
                $success_items[] = array(
                    'label' => $export_tasks[$key]['label'],
                    'details' => 'File generated, but metadata is currently unavailable.',
                    'url' => '',
                );
                continue;
            }

            $details = array();
            if (!empty($meta['exists'])) {
                if (!empty($recorded_time)) {
                    $formatted_time = wp_date(get_option('date_format') . ' ' . get_option('time_format'), $recorded_time);
                    $details[] = sprintf('updated %s %s (%s ago)', $formatted_time, $timezone_label, human_time_diff($recorded_time, $current_gmt));
                }
                if (isset($meta['size'])) {
                    $details[] = sprintf('size %s', size_format($meta['size']));
                }
            }

            $success_items[] = array(
                'label' => $meta['label'],
                'details' => !empty($details) ? implode(', ', $details) : 'Export completed.',
                'url' => $meta['url'],
            );
        }

        if (!empty($success_items)) {
            echo '<div class="notice notice-success"><p>Exports completed:</p><ul>';
            foreach ($success_items as $item) {
                echo '<li>' . esc_html($item['label']) . ' — ' . esc_html($item['details']);
                if (!empty($item['url'])) {
                    echo ' <a href="' . esc_url($item['url']) . '" target="_blank" rel="noopener">View file</a>';
                }
                echo '</li>';
            }
            echo '</ul></div>';
        }
    }

    if (!empty($notices['errors'])) {
        echo '<div class="notice notice-error"><p>Export issues detected:</p><ul>';
        foreach ($notices['errors'] as $message) {
            echo '<li>' . esc_html($message) . '</li>';
        }
        echo '</ul></div>';
    }

    echo '<div id="rmit-export-index-status" class="notice notice-info" style="display:none;"></div>';

    ?>
    <div class="wrap">
        <h1>Export Content to JSON</h1>
        <p>Use this tool to refresh the search datasets stored in the uploads directory.</p>
        <form method="post" style="margin-bottom: 24px;">
            <?php wp_nonce_field('export_json_action', 'export_json_nonce'); ?>
            <input type="submit" name="export_json" class="button button-primary" value="Run Export">
        </form>

        <h2 class="title">Dataset status</h2>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th scope="col">Dataset</th>
                    <th scope="col">File</th>
                    <th scope="col">Last updated</th>
                    <th scope="col">Size</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($export_tasks as $key => $task):
                $meta = $file_statuses[$key];
                $recorded_time = isset($export_history[$key]['timestamp']) ? (int) $export_history[$key]['timestamp'] : null;
                if (!$recorded_time && !is_wp_error($meta) && !empty($meta['modified'])) {
                    $recorded_time = (int) $meta['modified'];
                }
                ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html($task['label']); ?></strong><br>
                        <span class="description"><?php echo esc_html($task['description']); ?></span>
                    </td>
                    <td><?php echo esc_html($task['filename']); ?></td>
                    <td>
                        <?php
                        if (is_wp_error($meta)) {
                            echo '<span class="error">' . esc_html($meta->get_error_message()) . '</span>';
                        } elseif (!empty($meta['exists']) && !empty($recorded_time)) {
                            $formatted_time = wp_date(get_option('date_format') . ' ' . get_option('time_format'), $recorded_time);
                            $relative = human_time_diff($recorded_time, $current_gmt);
                            printf('%s <span class="description">%s • %s ago</span>', esc_html($formatted_time), esc_html($timezone_label), esc_html($relative));
                        } else {
                            echo '<span class="description">Not generated yet</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if (is_wp_error($meta)) {
                            echo '—';
                        } elseif (!empty($meta['exists']) && isset($meta['size'])) {
                            echo esc_html(size_format($meta['size']));
                        } else {
                            echo '—';
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if (!is_wp_error($meta) && !empty($meta['exists']) && !empty($meta['url'])) {
                            echo '<a class="button" href="' . esc_url($meta['url']) . '" target="_blank" rel="noopener">View JSON</a>';
                        } else {
                            echo '<span class="description">No file available</span>';
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td>
                    <strong><?php esc_html_e('Fuse.js index', 'rmit-learning-lab'); ?></strong><br>
                    <span class="description"><?php esc_html_e('Precomputed search index consumed by the static site.', 'rmit-learning-lab'); ?></span>
                </td>
                <td>pages-index.json</td>
                <td data-fuse-index="updated">
                    <?php
                    if (is_wp_error($fuse_index_meta)) {
                        echo '<span class="error">' . esc_html($fuse_index_meta->get_error_message()) . '</span>';
                    } elseif (!empty($fuse_index_meta['exists']) && !empty($fuse_index_recorded_time)) {
                        $formatted_time = wp_date(get_option('date_format') . ' ' . get_option('time_format'), $fuse_index_recorded_time);
                        $relative = human_time_diff($fuse_index_recorded_time, $current_gmt);
                        printf('%s <span class="description">%s • %s ago</span>', esc_html($formatted_time), esc_html($timezone_label), esc_html($relative));
                    } else {
                        echo '<span class="description">' . esc_html__('Not generated yet', 'rmit-learning-lab') . '</span>';
                    }
                    ?>
                </td>
                <td data-fuse-index="size">
                    <?php
                    if (is_wp_error($fuse_index_meta)) {
                        echo '—';
                    } elseif (!empty($fuse_index_meta['exists']) && isset($fuse_index_meta['size'])) {
                        echo esc_html(size_format($fuse_index_meta['size']));
                    } else {
                        echo '—';
                    }
                    ?>
                </td>
                <td data-fuse-index="actions">
                    <?php
                    if (!is_wp_error($fuse_index_meta) && !empty($fuse_index_meta['exists']) && !empty($fuse_index_meta['url'])) {
                        echo '<a class="button" href="' . esc_url($fuse_index_meta['url']) . '" target="_blank" rel="noopener">' . esc_html__('View JSON', 'rmit-learning-lab') . '</a>';
                    } else {
                        echo '<span class="description">' . esc_html__('No file available', 'rmit-learning-lab') . '</span>';
                    }
                    ?>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <?php
}

add_action('wp_ajax_rmit_ll_save_fuse_index', 'rmit_ll_save_fuse_index');

function rmit_ll_save_fuse_index() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'rmit-learning-lab')), 403);
    }

    check_ajax_referer('rmit_ll_save_fuse_index', 'nonce');

    $raw_payload = isset($_POST['index']) ? wp_unslash($_POST['index']) : '';
    if ('' === $raw_payload) {
        wp_send_json_error(array('message' => __('Index payload missing.', 'rmit-learning-lab')));
    }

    $decoded = json_decode($raw_payload, true);
    if (null === $decoded || !is_array($decoded)) {
        wp_send_json_error(array('message' => __('Invalid index payload.', 'rmit-learning-lab')));
    }

    $file_path = rmit_ll_get_export_file_path('pages-index.json');
    if (is_wp_error($file_path)) {
        wp_send_json_error(array('message' => $file_path->get_error_message()));
    }

    $encoded = wp_json_encode($decoded, JSON_UNESCAPED_SLASHES);
    if (false === $encoded) {
        wp_send_json_error(array('message' => __('Failed to encode index payload.', 'rmit-learning-lab')));
    }

    if (false === file_put_contents($file_path, $encoded)) {
        wp_send_json_error(array('message' => __('Unable to write index file to uploads directory.', 'rmit-learning-lab')));
    }

    clearstatcache(true, $file_path);

    rmit_ll_store_export_history('fuse_index', $file_path);

    $response = array(
        'message' => __('Fuse index saved.', 'rmit-learning-lab'),
        'path'    => $file_path,
    );

    $meta = rmit_ll_get_export_file_meta('pages-index.json', 'Fuse.js index');
    if (!is_wp_error($meta) && !empty($meta['exists'])) {
        $current_gmt  = time();
        $timestamp    = !empty($meta['modified']) ? (int) $meta['modified'] : $current_gmt;
        $size_bytes   = isset($meta['size']) ? (int) $meta['size'] : null;
        $size_label   = null;

        if (!is_null($size_bytes)) {
            $size_label = size_format($size_bytes);
        }

        $response['meta'] = array(
            'timestamp'      => $timestamp,
            'formatted'      => wp_date(get_option('date_format') . ' ' . get_option('time_format'), $timestamp),
            'relative'       => human_time_diff($timestamp, $current_gmt),
            'timezone_label' => rmit_ll_get_timezone_label(),
            'size'           => $size_bytes,
            'size_label'     => $size_label,
            'url'            => !empty($meta['url']) ? esc_url_raw($meta['url']) : '',
        );
    }

    wp_send_json_success($response);
}
?>
