<?php
/*
Plugin Name: Adwords Reporting
Description: A WordPress plugin for Google Ads reporting.
Version: 1.0.0
Author: Your Name
*/

// Require Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Require the main plugin class
require_once __DIR__ . '/includes/class-adwords-reporting.php';

// Initialize the plugin
function adwords_reporting_init() {
    return Adwords_Reporting::get_instance();
}

// Start the plugin
adwords_reporting_init();

// Add this near the top of the file, after the plugin header
function adwords_reporting_log($message) {
    $log_file = plugin_dir_path(__FILE__) . 'debug.log';
    $timestamp = date('[Y-m-d H:i:s]');
    $log_message = $timestamp . ' ' . $message . "\n";
    error_log($log_message, 3, $log_file);
}

// Test logging immediately
adwords_reporting_log('=== PLUGIN LOADED ===');
adwords_reporting_log('PHP Version: ' . PHP_VERSION);
adwords_reporting_log('Plugin Directory: ' . plugin_dir_path(__FILE__));

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
        ['chartjs', 'jquery'],
        '1.0.0',
        true
    );

    // Pass data to JavaScript
    wp_localize_script('adwords-reporting-script', 'adwordsReporting', [
        'ajaxurl' => admin_url('admin-ajax.php'),
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

// Initialize Google Ads client
function get_google_ads_client() {
    adwords_reporting_log('get_google_ads_client: called');
    $client_id = get_option('adwords_reporting_client_id');
    adwords_reporting_log('get_google_ads_client: client_id = ' . ($client_id ? 'present' : 'missing'));
    $client_secret = get_option('adwords_reporting_client_secret');
    adwords_reporting_log('get_google_ads_client: client_secret = ' . ($client_secret ? 'present' : 'missing'));
    $developer_token = get_option('adwords_reporting_developer_token');
    adwords_reporting_log('get_google_ads_client: developer_token = ' . ($developer_token ? 'present' : 'missing'));
    $refresh_token = get_option('adwords_reporting_refresh_token');
    adwords_reporting_log('get_google_ads_client: refresh_token = ' . ($refresh_token ? 'present' : 'missing'));

    if (!$client_id || !$client_secret || !$developer_token || !$refresh_token) {
        adwords_reporting_log('get_google_ads_client: missing one or more credentials');
        return null;
    }

    try {
        adwords_reporting_log('get_google_ads_client: building client');
        adwords_reporting_log('get_google_ads_client: using V19 namespace');
        
        // Create OAuth2 credentials
        $oAuth2Credential = (new \Google\Auth\OAuth2([
            'clientId' => $client_id,
            'clientSecret' => $client_secret,
            'refreshToken' => $refresh_token
        ]));
        adwords_reporting_log('get_google_ads_client: OAuth2 credentials created');

        // Build the client
        $client = (new \Google\Ads\GoogleAds\Lib\V19\GoogleAdsClientBuilder())
            ->withDeveloperToken($developer_token)
            ->withOAuth2Credential($oAuth2Credential)
            ->build();
        
        adwords_reporting_log('get_google_ads_client: client built successfully');
        return $client;
    } catch (\Exception $e) {
        adwords_reporting_log('get_google_ads_client: Exception - ' . $e->getMessage());
        adwords_reporting_log('get_google_ads_client: Stack trace - ' . $e->getTraceAsString());
        return null;
    }
}

// Add AJAX handlers
function adwords_reporting_get_campaign_data() {
    try {
        adwords_reporting_log('=== START OF AJAX HANDLER ===');
        adwords_reporting_log('PHP Version: ' . PHP_VERSION);
        adwords_reporting_log('Memory Limit: ' . ini_get('memory_limit'));
        adwords_reporting_log('Max Execution Time: ' . ini_get('max_execution_time'));
        adwords_reporting_log('AJAX request received for campaign data');
        adwords_reporting_log('POST data: ' . print_r($_POST, true));
        adwords_reporting_log('REQUEST data: ' . print_r($_REQUEST, true));
        adwords_reporting_log('SERVER data: ' . print_r($_SERVER, true));
        
        if (!check_ajax_referer('adwords_reporting_nonce', 'nonce', false)) {
            adwords_reporting_log('Nonce verification failed');
            adwords_reporting_log('Expected nonce: ' . wp_create_nonce('adwords_reporting_nonce'));
            adwords_reporting_log('Received nonce: ' . (isset($_POST['nonce']) ? $_POST['nonce'] : 'not set'));
            wp_send_json_error('Security check failed');
            return;
        }
        adwords_reporting_log('Nonce verified successfully');

        $client = get_google_ads_client();
        if (!$client) {
            $missing_credentials = [];
            if (!get_option('adwords_reporting_client_id')) $missing_credentials[] = 'Client ID';
            if (!get_option('adwords_reporting_client_secret')) $missing_credentials[] = 'Client Secret';
            if (!get_option('adwords_reporting_developer_token')) $missing_credentials[] = 'Developer Token';
            if (!get_option('adwords_reporting_refresh_token')) $missing_credentials[] = 'Refresh Token';
            
            adwords_reporting_log('Missing credentials: ' . implode(', ', $missing_credentials));
            wp_send_json_error('Missing required credentials: ' . implode(', ', $missing_credentials));
            return;
        }
        adwords_reporting_log('Google Ads client created successfully');

        $customerId = get_option('adwords_reporting_customer_id');
        if (!$customerId) {
            adwords_reporting_log('Missing customer ID');
            wp_send_json_error('Missing customer ID');
            return;
        }
        adwords_reporting_log('Using customer ID: ' . $customerId);

        // Create the GoogleAdsService client
        $googleAdsServiceClient = $client->getGoogleAdsServiceClient();
        adwords_reporting_log('Google Ads service client created');

        // Create the query
        $query = "
            SELECT
                campaign.id,
                campaign.name,
                campaign.status,
                campaign.advertising_channel_type,
                campaign.start_date,
                campaign.end_date,
                campaign.budget_amount_micros,
                metrics.clicks,
                metrics.impressions,
                metrics.cost_micros,
                segments.date
            FROM campaign
            WHERE campaign.status != 'REMOVED'
            AND segments.date DURING LAST_30_DAYS
            ORDER BY campaign.name";

        adwords_reporting_log('Executing query: ' . $query);
        
        // Execute the query
        $response = $googleAdsServiceClient->search(
            $customerId,
            $query,
            ['pageSize' => 1000]
        );
        adwords_reporting_log('Query executed successfully');

        // Process the results
        $campaigns = [];
        $dates = [];
        $campaignData = [];

        foreach ($response->iterateAllElements() as $googleAdsRow) {
            try {
                $campaignId = $googleAdsRow->getCampaign()->getId();
                $date = $googleAdsRow->getSegments()->getDate();
                
                if (!in_array($date, $dates)) {
                    $dates[] = $date;
                }

                if (!isset($campaignData[$campaignId])) {
                    $campaignData[$campaignId] = [
                        'id' => $campaignId,
                        'name' => $googleAdsRow->getCampaign()->getName(),
                        'status' => $googleAdsRow->getCampaign()->getStatus(),
                        'budget' => $googleAdsRow->getCampaign()->getBudgetAmountMicros() / 1000000,
                        'clicks' => [],
                        'impressions' => [],
                        'cost' => 0
                    ];
                }

                $campaignData[$campaignId]['clicks'][] = $googleAdsRow->getMetrics()->getClicks();
                $campaignData[$campaignId]['impressions'][] = $googleAdsRow->getMetrics()->getImpressions();
                $campaignData[$campaignId]['cost'] += $googleAdsRow->getMetrics()->getCostMicros() / 1000000;
            } catch (Exception $e) {
                adwords_reporting_log('Error processing campaign row: ' . $e->getMessage());
                adwords_reporting_log('Stack trace: ' . $e->getTraceAsString());
                continue;
            }
        }
        adwords_reporting_log('Processed ' . count($campaignData) . ' campaigns');

        // Sort dates chronologically
        sort($dates);

        // Format the response
        $response = [
            'success' => true,
            'dates' => $dates,
            'campaigns' => array_values($campaignData)
        ];

        adwords_reporting_log('Sending response: ' . print_r($response, true));
        wp_send_json($response);
        adwords_reporting_log('Success response sent');

    } catch (Exception $e) {
        adwords_reporting_log('Error in adwords_reporting_get_campaign_data: ' . $e->getMessage());
        adwords_reporting_log('Stack trace: ' . $e->getTraceAsString());
        wp_send_json_error('Error fetching data: ' . $e->getMessage());
    }
}
add_action('wp_ajax_adwords_reporting_get_campaign_data', 'adwords_reporting_get_campaign_data');

// Hardcoded credentials
$HARDCODED_CLIENT_ID = '862420647301-vku1g8mna97j6i3rhcm6gq80ia7qcaq4.apps.googleusercontent.com';
$HARDCODED_CLIENT_SECRET = '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';

// Update settings page to include OAuth button and customer ID
function adwords_reporting_settings_page() {
    adwords_reporting_log('Settings page accessed');
    global $HARDCODED_CLIENT_ID, $HARDCODED_CLIENT_SECRET;
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
                settings_fields('adwords_reporting_settings');
                do_settings_sections('adwords_reporting_settings');
                ?>
                <div class="form-group">
                    <label class="form-label" for="client_id">Client ID</label>
                    <input type="text" class="form-control" id="client_id" name="adwords_reporting_client_id" value="<?php echo esc_attr($HARDCODED_CLIENT_ID); ?>" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label" for="client_secret">Client Secret</label>
                    <input type="password" class="form-control" id="client_secret" name="adwords_reporting_client_secret" value="<?php echo esc_attr($HARDCODED_CLIENT_SECRET); ?>" readonly>
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

            <?php if ($HARDCODED_CLIENT_ID && $HARDCODED_CLIENT_SECRET): ?>
                <div class="oauth-section">
                    <h2>Google Ads Authentication</h2>
                    <?php if (!get_option('adwords_reporting_refresh_token')): ?>
                        <?php
                        $client_id = $HARDCODED_CLIENT_ID;
                        $redirect_uri = admin_url('admin.php?page=adwords-reporting-settings');
                        $auth_url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query([
                            'client_id' => $client_id,
                            'redirect_uri' => $redirect_uri,
                            'scope' => 'https://www.googleapis.com/auth/adwords',
                            'response_type' => 'code',
                            'access_type' => 'offline',
                            'prompt' => 'consent'
                        ]);
                        adwords_reporting_log('Full Auth URL: ' . $auth_url);
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
    // Add validation callback for client ID
    add_filter('pre_update_option_adwords_reporting_client_id', function($value) {
        // Debug output
        adwords_reporting_log('Raw client ID value: ' . print_r($value, true));
        
        // If the value looks like a JSON string, try to extract the client_id
        if (strpos($value, '{') === 0 && strpos($value, '}') !== false) {
            $json = json_decode($value, true);
            adwords_reporting_log('Decoded JSON: ' . print_r($json, true));
            if ($json && isset($json['client_id'])) {
                adwords_reporting_log('Extracted client_id: ' . $json['client_id']);
                return $json['client_id'];
            }
        }
        
        // If it's not JSON or doesn't have client_id, return as is
        return $value;
    });

    register_setting('adwords_reporting_settings', 'adwords_reporting_client_id');
    register_setting('adwords_reporting_settings', 'adwords_reporting_client_secret');
    register_setting('adwords_reporting_settings', 'adwords_reporting_developer_token');
    register_setting('adwords_reporting_settings', 'adwords_reporting_customer_id');
}
add_action('admin_init', 'adwords_reporting_register_settings');

// Add OAuth2 callback handler
function adwords_reporting_oauth_callback() {
    adwords_reporting_log('OAuth Callback Debug - Current Page: ' . (isset($_GET['page']) ? $_GET['page'] : 'not set'));
    adwords_reporting_log('OAuth Callback Debug - Full Request: ' . print_r($_REQUEST, true));
    adwords_reporting_log('OAuth Callback Debug - Server Variables: ' . print_r($_SERVER, true));
    if (!isset($_GET['page']) || $_GET['page'] !== 'adwords-reporting-settings') {
        adwords_reporting_log('OAuth Callback Debug - Not on settings page, returning');
        return;
    }
    adwords_reporting_log('OAuth Callback Debug - Code: ' . (isset($_GET['code']) ? 'present' : 'not present'));
    adwords_reporting_log('OAuth Callback Debug - Error: ' . (isset($_GET['error']) ? $_GET['error'] : 'not present'));
    if (!isset($_GET['code'])) {
        if (!isset($_GET['error'])) {
            adwords_reporting_log('OAuth Callback Debug - No code or error parameter, returning');
            return;
        }
        adwords_reporting_log('OAuth Callback Debug - Authorization error: ' . esc_html($_GET['error']));
        wp_die('Authorization error: ' . esc_html($_GET['error']));
    }
    global $HARDCODED_CLIENT_ID, $HARDCODED_CLIENT_SECRET;
    $client_id = $HARDCODED_CLIENT_ID;
    $client_secret = $HARDCODED_CLIENT_SECRET;
    $redirect_uri = admin_url('admin.php?page=adwords-reporting-settings');
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