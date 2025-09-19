<?php
/**
 * Analytics Dashboards Functionality
 *
 * Handles the display and management of analytics dashboards
 * in the WordPress admin area.
 *
 * @package RMIT_Learning_Lab
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add Analytics Dashboards to the dashboard page
 */
add_action('admin_notices', 'rmit_ll_display_analytics_dashboards');

function rmit_ll_display_analytics_dashboards() {
    $screen = get_current_screen();
    if ($screen && $screen->base === 'dashboard') {
        $dashboards = get_option('analytics_dashboards', array());

        if ($dashboards) {
            echo '<h1 style="font-size: 23px; font-weight: 400; margin: 0 0 20px 0; padding: 9px 0 4px 0; line-height: 1.3;">Analytics</h1>';

            foreach ($dashboards as $index => $dashboard) {
                if (!$dashboard['enabled']) continue;

                $dashboard_id = 'analytics-dashboard-' . $index;
                $title = esc_html($dashboard['title']);
                $url = esc_url($dashboard['embed_url']);

                echo '<div id="' . esc_attr($dashboard_id) . '" style="max-width: 100%; margin: 0 20px 20px 0; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">';
                echo '<div style="padding: 12px; border-bottom: 1px solid #eee; background: #fafafa;">';
                echo '<h2 style="margin: 0; font-size: 14px; font-weight: 600;">' . $title . '</h2>';
                echo '</div>';

                echo '<div id="' . esc_attr($dashboard_id) . '-container" style="padding: 0;">';
                echo '<div id="' . esc_attr($dashboard_id) . '-button" style="padding: 40px; text-align: center; background: #f8f9fa; cursor: pointer; border-bottom: 1px solid #eee;">';
                echo '<button type="button" class="button button-primary" data-dashboard-id="' . esc_attr($dashboard_id) . '" data-dashboard-url="' . esc_attr($url) . '">📊 Load ' . $title . '</button>';
                echo '<p style="margin: 10px 0 0 0; color: #666; font-size: 12px;">Click to load dashboard</p>';
                echo '</div>';
                echo '<div id="' . esc_attr($dashboard_id) . '-iframe" style="display: none; position: relative; width: 100%; padding-bottom: 56.25%;">';
                echo '<iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" frameborder="0" allowfullscreen></iframe>';
                echo '</div>';
                echo '</div>';

                echo '</div>';
            }

            // Add JavaScript for dashboard loading
            rmit_ll_analytics_dashboard_scripts();

        } else {
            echo '<h1 style="font-size: 23px; font-weight: 400; margin: 0 0 20px 0; padding: 9px 0 4px 0; line-height: 1.3;">Analytics</h1>';
            echo '<div style="max-width: 100%; margin: 0 20px 20px 0; padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); text-align: center;">';
            echo '<p><strong>No Analytics Dashboards Configured</strong></p>';
            echo '<p>Go to <a href="' . admin_url('admin.php?page=analytics-dashboards') . '">Analytics Dashboards</a> to add your first dashboard.</p>';
            echo '</div>';
        }
    }
}

/**
 * Output dashboard loading scripts
 */
function rmit_ll_analytics_dashboard_scripts() {
    ?>
    <script>
    document.addEventListener('click', function(e) {
        if (e.target && e.target.hasAttribute('data-dashboard-id')) {
            const dashboardId = e.target.getAttribute('data-dashboard-id');
            const url = e.target.getAttribute('data-dashboard-url');
            const button = document.getElementById(dashboardId + "-button");
            const iframe = document.getElementById(dashboardId + "-iframe");
            const iframeElement = iframe.querySelector("iframe");

            button.style.display = "none";
            iframe.style.display = "block";
            iframeElement.src = url;
        }
    });
    </script>
    <?php
}

/**
 * Create Analytics Dashboards Settings Page
 */
add_action('admin_menu', 'rmit_ll_add_analytics_menu');

function rmit_ll_add_analytics_menu() {
    add_options_page(
        'Analytics Dashboards',
        'Analytics Dashboards',
        'manage_options',
        'analytics-dashboards',
        'rmit_ll_analytics_dashboards_page'
    );
}

/**
 * Analytics Dashboards Settings Page Content
 */
function rmit_ll_analytics_dashboards_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Handle form submission
    if (isset($_POST['submit'])) {
        check_admin_referer('analytics_dashboards_nonce');

        $dashboards = array();
        if (isset($_POST['dashboards']) && is_array($_POST['dashboards'])) {
            foreach ($_POST['dashboards'] as $dashboard) {
                if (!empty($dashboard['title']) && !empty($dashboard['embed_url'])) {
                    $dashboards[] = array(
                        'title' => sanitize_text_field($dashboard['title']),
                        'embed_url' => esc_url_raw($dashboard['embed_url']),
                        'enabled' => isset($dashboard['enabled']) ? 1 : 0
                    );
                }
            }
        }

        update_option('analytics_dashboards', $dashboards);
        echo '<div class="notice notice-success"><p>Analytics dashboards saved!</p></div>';
    }

    $dashboards = get_option('analytics_dashboards', array());
    ?>
    <div class="wrap">
        <h1>Analytics Dashboards</h1>
        <p>Add multiple analytics dashboards to display on the WordPress dashboard.</p>

        <form method="post" action="">
            <?php wp_nonce_field('analytics_dashboards_nonce'); ?>

            <div id="dashboards-container">
                <?php
                if (empty($dashboards)) {
                    $dashboards = array(array('title' => '', 'embed_url' => '', 'enabled' => 1));
                }

                foreach ($dashboards as $index => $dashboard):
                ?>
                <div class="dashboard-item" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin-bottom: 20px;">
                    <h3>Dashboard <?php echo $index + 1; ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Title</th>
                            <td>
                                <input type="text" name="dashboards[<?php echo $index; ?>][title]"
                                       value="<?php echo esc_attr($dashboard['title'] ?? ''); ?>"
                                       placeholder="e.g. Google Search Console Analytics"
                                       class="regular-text" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Embed URL</th>
                            <td>
                                <input type="url" name="dashboards[<?php echo $index; ?>][embed_url]"
                                       value="<?php echo esc_attr($dashboard['embed_url'] ?? ''); ?>"
                                       placeholder="https://lookerstudio.google.com/embed/reporting/..."
                                       class="large-text" />
                                <p class="description">Get this from Looker Studio: Share → Embed report → Copy URL</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Enabled</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="dashboards[<?php echo $index; ?>][enabled]"
                                           value="1" <?php checked($dashboard['enabled'] ?? 1, 1); ?> />
                                    Show this dashboard
                                </label>
                            </td>
                        </tr>
                    </table>
                    <?php if ($index > 0): ?>
                    <button type="button" class="button button-secondary remove-dashboard-btn">Remove Dashboard</button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <button type="button" id="add-dashboard" class="button button-secondary">Add Another Dashboard</button>
            <br><br>
            <?php submit_button('Save Analytics Dashboards'); ?>
        </form>
    </div>

    <script>
    // Event delegation for remove buttons
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-dashboard-btn')) {
            e.target.parentElement.remove();
        }
    });

    document.getElementById('add-dashboard').addEventListener('click', function() {
        const container = document.getElementById('dashboards-container');
        const index = container.children.length;

        const newDashboard = document.createElement('div');
        newDashboard.className = 'dashboard-item';
        newDashboard.style.cssText = 'background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin-bottom: 20px;';

        const dashboardHTML = `
            <h3>Dashboard ${index + 1}</h3>
            <table class="form-table">
                <tr>
                    <th scope="row">Title</th>
                    <td>
                        <input type="text" name="dashboards[${index}][title]" placeholder="e.g. Google Search Console Analytics" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Embed URL</th>
                    <td>
                        <input type="url" name="dashboards[${index}][embed_url]" placeholder="https://lookerstudio.google.com/embed/reporting/..." class="large-text" />
                        <p class="description">Get this from Looker Studio: Share → Embed report → Copy URL</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Enabled</th>
                    <td>
                        <label>
                            <input type="checkbox" name="dashboards[${index}][enabled]" value="1" checked />
                            Show this dashboard
                        </label>
                    </td>
                </tr>
            </table>
            <button type="button" class="button button-secondary remove-dashboard-btn">Remove Dashboard</button>
        `;

        newDashboard.innerHTML = dashboardHTML;
        container.appendChild(newDashboard);
    });
    </script>
    <?php
}