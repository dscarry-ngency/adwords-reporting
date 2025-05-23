<?php
/*
Plugin Name: Adwords Reporting
Description: A WordPress plugin for Google Ads reporting.
Version: 1.0.0
Author: Your Name
*/

// Require Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';

use Google\Ads\GoogleAds\Lib\V18\GoogleAdsClient;
use Google\Ads\GoogleAds\Lib\V18\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Util\V18\ResourceNames;
use Google\Ads\GoogleAds\V18\Enums\SummaryRowSettingEnum\SummaryRowSetting;
use Google\Ads\GoogleAds\V18\Services\GoogleAdsRow;
use Google\Ads\GoogleAds\V18\Services\SearchGoogleAdsRequest;
use Google\Ads\GoogleAds\V18\Services\SearchGoogleAdsResponse;
use Google\Ads\GoogleAds\V18\Services\GoogleAdsServiceClient;

// Add this near the top of the file, after the plugin header
function adwords_reporting_log($message) {
    $log_file = plugin_dir_path(__FILE__) . 'debug.log';
    $timestamp = date('[Y-m-d H:i:s]');
    $log_message = $timestamp . ' ' . $message . "\n";
    error_log($log_message, 3, $log_file);
}

// Enqueue Chart.js
function enqueue_admin_scripts($hook) {
    // Only load on our plugin pages
    if (strpos($hook, 'adwords-reporting') === false) {
        return;
    }

    wp_enqueue_script(
        'chartjs',
        plugins_url('assets/js/chart.min.js', __FILE__),
        [],
        '3.7.0',
        true
    );

    // Add our custom script
    wp_enqueue_script(
        'adwords-reporting-script',
        plugins_url('assets/js/adwords-reporting.js', __FILE__),
        ['chartjs'],
        '1.0.0',
        true
    );

    // Pass data to JavaScript
    wp_localize_script('adwords-reporting-script', 'adwordsReporting', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('adwords_reporting_nonce')
    ]);
}
add_action('admin_enqueue_scripts', 'enqueue_admin_scripts');

function adwords_reporting_enqueue_styles() {
    // Base styles
    wp_enqueue_style(
        'adwords-reporting-base',
        plugin_dir_url(__FILE__) . 'assets/css/min.styles.css',
        array(),
        '1.0.0'
    );

    // Component styles
    wp_enqueue_style(
        'adwords-reporting-charts',
        plugin_dir_url(__FILE__) . 'assets/css/components/charts.css',
        array('adwords-reporting-base'),
        '1.0.0'
    );

    wp_enqueue_style(
        'adwords-reporting-forms',
        plugin_dir_url(__FILE__) . 'assets/css/components/forms.css',
        array('adwords-reporting-base'),
        '1.0.0'
    );

    wp_enqueue_style(
        'adwords-reporting-tables',
        plugin_dir_url(__FILE__) . 'assets/css/components/tables.css',
        array('adwords-reporting-base'),
        '1.0.0'
    );
}
add_action('admin_enqueue_scripts', 'adwords_reporting_enqueue_styles');

// Add menu items
function adwords_reporting_admin_menu() {
    add_menu_page(
        'Adwords Reporting', // Page title
        'Adwords', // Menu title
        'manage_options', // Capability required
        'adwords-reporting', // Menu slug
        'adwords_reporting_dashboard_page', // Callback function
        'dashicons-chart-area', // Icon
        30 // Position
    );

    add_submenu_page(
        'adwords-reporting', // Parent slug
        'Dashboard', // Page title
        'Dashboard', // Menu title
        'manage_options', // Capability
        'adwords-reporting', // Menu slug (same as parent for first submenu)
        'adwords_reporting_dashboard_page' // Callback function
    );

    add_submenu_page(
        'adwords-reporting',
        'Campaigns',
        'Campaigns',
        'manage_options',
        'adwords-reporting-campaigns',
        'adwords_reporting_campaigns_page'
    );

    add_submenu_page(
        'adwords-reporting',
        'Settings',
        'Settings',
        'manage_options',
        'adwords-reporting-settings',
        'adwords_reporting_settings_page'
    );
}
add_action('admin_menu', 'adwords_reporting_admin_menu');

// Dashboard page callback
function adwords_reporting_dashboard_page() {
    ?>
    <div class="wrap adwords-reporting">
        <h1>Adwords Dashboard</h1>
        <div class="grid">
            <div class="chart-container">
                <div class="chart-header">
                    <h2 class="chart-title">Performance Overview</h2>
                </div>
                <canvas id="performanceChart"></canvas>
            </div>
            <div class="chart-container">
                <div class="chart-header">
                    <h2 class="chart-title">Campaign Performance</h2>
                </div>
                <canvas id="campaignChart"></canvas>
            </div>
        </div>
    </div>
    <?php
}

// Campaigns page callback
function adwords_reporting_campaigns_page() {
    ?>
    <div class="wrap adwords-reporting">
        <h1>Campaigns</h1>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="sortable">Campaign</th>
                        <th class="sortable">Status</th>
                        <th class="sortable">Budget</th>
                        <th class="sortable">Clicks</th>
                        <th class="sortable">Impressions</th>
                        <th class="sortable">Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Campaign data will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

// Initialize Google Ads client
function get_google_ads_client() {
    $client_id = get_option('adwords_reporting_client_id');
    $client_secret = get_option('adwords_reporting_client_secret');
    $developer_token = get_option('adwords_reporting_developer_token');
    $refresh_token = get_option('adwords_reporting_refresh_token');

    if (!$client_id || !$client_secret || !$developer_token || !$refresh_token) {
        return null;
    }

    return (new GoogleAdsClientBuilder())
        ->withDeveloperToken($developer_token)
        ->withClientId($client_id)
        ->withClientSecret($client_secret)
        ->withRefreshToken($refresh_token)
        ->build();
}

// Add AJAX handlers
function adwords_reporting_get_campaign_data() {
    check_ajax_referer('adwords_reporting_nonce', 'nonce');

    $client = get_google_ads_client();
    if (!$client) {
        wp_send_json_error('Missing credentials');
        return;
    }

    try {
        $customer_id = get_option('adwords_reporting_customer_id');
        if (!$customer_id) {
            wp_send_json_error('Missing customer ID');
            return;
        }

        // Get campaign performance data
        $googleAdsServiceClient = $client->getGoogleAdsServiceClient();
        $query = "
            SELECT 
                campaign.name,
                campaign.status,
                campaign.advertising_channel_type,
                metrics.clicks,
                metrics.impressions,
                metrics.cost_micros,
                campaign.campaign_budget
            FROM campaign
            WHERE campaign.status != 'REMOVED'
            ORDER BY campaign.name";

        $response = $googleAdsServiceClient->search(
            $customer_id,
            $query,
            ['pageSize' => 50]
        );

        $campaigns = [];
        $performance_data = [
            'labels' => [],
            'clicks' => [],
            'impressions' => [],
            'cost' => []
        ];

        foreach ($response->iterateAllElements() as $googleAdsRow) {
            $campaign = $googleAdsRow->getCampaign();
            $metrics = $googleAdsRow->getMetrics();
            
            $campaigns[] = [
                'name' => $campaign->getName(),
                'status' => $campaign->getStatus(),
                'budget' => number_format($campaign->getCampaignBudget() / 1000000, 2),
                'clicks' => $metrics->getClicks(),
                'impressions' => $metrics->getImpressions(),
                'cost' => number_format($metrics->getCostMicros() / 1000000, 2)
            ];

            // Add to performance data
            $performance_data['labels'][] = $campaign->getName();
            $performance_data['clicks'][] = $metrics->getClicks();
            $performance_data['impressions'][] = $metrics->getImpressions();
            $performance_data['cost'][] = $metrics->getCostMicros() / 1000000;
        }

        wp_send_json_success([
            'performance' => $performance_data,
            'campaigns' => $campaigns
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Error fetching data: ' . $e->getMessage());
    }
}
add_action('wp_ajax_adwords_reporting_get_campaign_data', 'adwords_reporting_get_campaign_data');

// Add OAuth2 callback handler
function adwords_reporting_oauth_callback() {
    // Debug: Log the current page and request
    adwords_reporting_log('OAuth Callback Debug - Current Page: ' . (isset($_GET['page']) ? $_GET['page'] : 'not set'));
    adwords_reporting_log('OAuth Callback Debug - Full Request: ' . print_r($_REQUEST, true));
    adwords_reporting_log('OAuth Callback Debug - Server Variables: ' . print_r($_SERVER, true));

    // Only process if we're on the settings page
    if (!isset($_GET['page']) || $_GET['page'] !== 'adwords-reporting-settings') {
        adwords_reporting_log('OAuth Callback Debug - Not on settings page, returning');
        return;
    }

    // Debug: Log OAuth parameters
    adwords_reporting_log('OAuth Callback Debug - Code: ' . (isset($_GET['code']) ? 'present' : 'not present'));
    adwords_reporting_log('OAuth Callback Debug - Error: ' . (isset($_GET['error']) ? $_GET['error'] : 'not present'));

    if (!isset($_GET['code'])) {
        // If we're not in the OAuth flow, just return
        if (!isset($_GET['error'])) {
            adwords_reporting_log('OAuth Callback Debug - No code or error parameter, returning');
            return;
        }
        adwords_reporting_log('OAuth Callback Debug - Authorization error: ' . esc_html($_GET['error']));
        wp_die('Authorization error: ' . esc_html($_GET['error']));
    }

    $client_id = get_option('adwords_reporting_client_id');
    $client_secret = get_option('adwords_reporting_client_secret');
    $redirect_uri = admin_url('admin.php?page=adwords-reporting-settings');

    // Debug: Log credentials status
    adwords_reporting_log('OAuth Callback Debug - Client ID: ' . ($client_id ? 'present' : 'missing'));
    adwords_reporting_log('OAuth Callback Debug - Client Secret: ' . ($client_secret ? 'present' : 'missing'));
    adwords_reporting_log('OAuth Callback Debug - Redirect URI: ' . $redirect_uri);

    if (!$client_id || !$client_secret) {
        adwords_reporting_log('OAuth Callback Debug - Missing client credentials');
        wp_die('Missing client credentials. Please configure the plugin settings first.');
    }

    $token_url = 'https://oauth2.googleapis.com/token';
    $data = [
        'code' => $_GET['code'],
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code'
    ];

    // Debug: Log token request data (excluding sensitive info)
    adwords_reporting_log('OAuth Callback Debug - Token Request Data: ' . print_r(array_merge($data, ['client_secret' => 'REDACTED']), true));

    $response = wp_remote_post($token_url, [
        'body' => $data,
        'timeout' => 30
    ]);

    if (is_wp_error($response)) {
        adwords_reporting_log('OAuth Token Error: ' . $response->get_error_message());
        wp_die('Error getting access token: ' . $response->get_error_message());
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    // Debug: Log response status and body
    adwords_reporting_log('OAuth Callback Debug - Response Status: ' . wp_remote_retrieve_response_code($response));
    adwords_reporting_log('OAuth Callback Debug - Response Body: ' . print_r($body, true));

    if (isset($body['error'])) {
        adwords_reporting_log('OAuth Response Error: ' . print_r($body, true));
        wp_die('Error from Google: ' . esc_html($body['error_description'] ?? $body['error']));
    }

    if (isset($body['refresh_token'])) {
        adwords_reporting_log('OAuth Callback Debug - Successfully received refresh token');
        update_option('adwords_reporting_refresh_token', $body['refresh_token']);
        wp_redirect(admin_url('admin.php?page=adwords-reporting-settings&oauth_success=1'));
        exit;
    }

    adwords_reporting_log('OAuth Callback Debug - No refresh token in response');
    wp_die('Error: No refresh token received. Please try again.');
}
add_action('admin_init', 'adwords_reporting_oauth_callback');

// Update settings page to include OAuth button and customer ID
function adwords_reporting_settings_page() {
    ?>
    <div class="wrap adwords-reporting">
        <h1>Adwords Settings</h1>
        <?php if (isset($_GET['oauth_success'])): ?>
            <div class="notice notice-success">
                <p>Successfully authenticated with Google Ads!</p>
            </div>
        <?php endif; ?>
        <div class="form-container">
            <form method="post" action="options.php">
                <?php
                settings_fields('adwords_reporting_options');
                do_settings_sections('adwords_reporting_options');
                ?>
                <div class="form-group">
                    <label class="form-label" for="client_id">Client ID</label>
                    <input type="text" class="form-control" id="client_id" name="adwords_reporting_client_id" value="<?php echo esc_attr(get_option('adwords_reporting_client_id')); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="client_secret">Client Secret</label>
                    <input type="password" class="form-control" id="client_secret" name="adwords_reporting_client_secret" value="<?php echo esc_attr(get_option('adwords_reporting_client_secret')); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="developer_token">Developer Token</label>
                    <input type="text" class="form-control" id="developer_token" name="adwords_reporting_developer_token" value="<?php echo esc_attr(get_option('adwords_reporting_developer_token')); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="customer_id">Customer ID</label>
                    <input type="text" class="form-control" id="customer_id" name="adwords_reporting_customer_id" value="<?php echo esc_attr(get_option('adwords_reporting_customer_id')); ?>">
                    <small class="form-text">Your Google Ads customer ID (without dashes)</small>
                </div>
                <?php submit_button('Save Settings'); ?>
            </form>

            <?php if (get_option('adwords_reporting_client_id') && get_option('adwords_reporting_client_secret')): ?>
                <div class="oauth-section">
                    <h2>Google Ads Authentication</h2>
                    <?php if (!get_option('adwords_reporting_refresh_token')): ?>
                        <?php
                        $auth_url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query([
                            'client_id' => get_option('adwords_reporting_client_id'),
                            'redirect_uri' => admin_url('admin.php?page=adwords-reporting-settings'),
                            'scope' => 'https://www.googleapis.com/auth/adwords',
                            'response_type' => 'code',
                            'access_type' => 'offline',
                            'prompt' => 'consent'
                        ]);
                        ?>
                        <a href="<?php echo esc_url($auth_url); ?>" class="button button-primary">
                            Connect Google Ads Account
                        </a>
                    <?php else: ?>
                        <p class="success-message">✓ Connected to Google Ads</p>
                        <form method="post" action="">
                            <?php wp_nonce_field('adwords_reporting_disconnect', 'adwords_reporting_disconnect_nonce'); ?>
                            <input type="hidden" name="action" value="disconnect">
                            <button type="submit" class="button">Disconnect</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

// Register settings
function adwords_reporting_register_settings() {
    register_setting('adwords_reporting_options', 'adwords_reporting_client_id');
    register_setting('adwords_reporting_options', 'adwords_reporting_client_secret');
    register_setting('adwords_reporting_options', 'adwords_reporting_developer_token');
    register_setting('adwords_reporting_options', 'adwords_reporting_customer_id');
}
add_action('admin_init', 'adwords_reporting_register_settings'); 