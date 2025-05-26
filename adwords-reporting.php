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

// Require the authentication class
require_once __DIR__ . '/includes/class-google-ads-auth.php';

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
    register_setting('adwords_reporting_settings', 'adwords_reporting_refresh_token');
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
    $client_id = get_option('adwords_reporting_client_id');
    $client_secret = get_option('adwords_reporting_client_secret');
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

// Add settings page
function adwords_reporting_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Check if we have a successful OAuth connection
    $oauth_success = isset($_GET['oauth_success']) && $_GET['oauth_success'] === '1';
    $refresh_token = get_option('adwords_reporting_refresh_token');
    $client_id = get_option('adwords_reporting_client_id');
    $client_secret = get_option('adwords_reporting_client_secret');
    $developer_token = get_option('adwords_reporting_developer_token');
    $customer_id = get_option('adwords_reporting_customer_id');

    // Get connection status
    $connection_status = 'Not Connected';
    $connection_details = [];
    
    if ($refresh_token) {
        $connection_status = 'Connected';
        $connection_details[] = 'Refresh Token: Present';
    } else {
        $connection_details[] = 'Refresh Token: Missing';
    }
    
    if ($client_id) {
        $connection_details[] = 'Client ID: Present';
    } else {
        $connection_details[] = 'Client ID: Missing';
    }
    
    if ($client_secret) {
        $connection_details[] = 'Client Secret: Present';
    } else {
        $connection_details[] = 'Client Secret: Missing';
    }
    
    if ($developer_token) {
        $connection_details[] = 'Developer Token: Present';
    } else {
        $connection_details[] = 'Developer Token: Missing';
    }
    
    if ($customer_id) {
        $connection_details[] = 'Customer ID: Present';
    } else {
        $connection_details[] = 'Customer ID: Missing';
    }

    // Get the auth URL if we have the required credentials
    $auth_url = '';
    if ($client_id && $client_secret) {
        $scopes = [
            'https://www.googleapis.com/auth/adwords'
        ];
        $auth = new Google_Ads_Auth($client_id, $client_secret, admin_url('admin.php?page=adwords-reporting-settings'), $scopes);
        $auth_url = $auth->getAuthUrl();
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <?php if ($oauth_success): ?>
            <div class="notice notice-success is-dismissible">
                <p>Successfully connected to Google Ads!</p>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>Connection Status</h2>
            <p><strong>Status:</strong> <?php echo esc_html($connection_status); ?></p>
            <ul>
                <?php foreach ($connection_details as $detail): ?>
                    <li><?php echo esc_html($detail); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php if ($auth_url): ?>
                <p>
                    <a href="<?php echo esc_url($auth_url); ?>" class="button button-primary">
                        <?php echo $refresh_token ? 'Reconnect to Google Ads' : 'Connect to Google Ads'; ?>
                    </a>
                </p>
            <?php else: ?>
                <p class="description">Please fill in the Client ID and Client Secret below to enable the connect button.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

// Add menu page
function adwords_reporting_add_menu_page() {
    add_menu_page(
        'Adwords Reporting',
        'Adwords',
        'manage_options',
        'adwords-reporting-settings',
        'adwords_reporting_settings_page',
        'dashicons-chart-area',
        30
    );
}
add_action('admin_menu', 'adwords_reporting_add_menu_page'); 