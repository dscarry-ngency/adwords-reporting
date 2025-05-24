<?php
/**
 * Main plugin class
 *
 * @package Adwords_Reporting
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Adwords_Reporting {
    /**
     * Plugin instance.
     *
     * @var Adwords_Reporting
     */
    private static $instance = null;

    /**
     * Get plugin instance.
     *
     * @return Adwords_Reporting
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));

        // Add menu items
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Register settings
        add_action('admin_init', array($this, 'register_settings'));

        // AJAX handlers
        add_action('wp_ajax_adwords_reporting_get_campaign_data', array($this, 'get_campaign_data'));

        // OAuth handlers
        add_action('admin_init', array($this, 'handle_oauth_callback'));
        add_action('admin_init', array($this, 'handle_disconnect'));
    }

    /**
     * Enqueue admin scripts.
     *
     * @param string $hook Current admin page.
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'adwords-reporting') === false) {
            return;
        }

        wp_enqueue_script(
            'chartjs',
            plugins_url('assets/js/chart.min.js', dirname(__FILE__)),
            array(),
            '3.7.0',
            true
        );

        wp_enqueue_script(
            'adwords-reporting-script',
            plugins_url('assets/js/adwords-reporting.js', dirname(__FILE__)),
            array('chartjs'),
            '1.0.0',
            true
        );

        wp_localize_script('adwords-reporting-script', 'adwordsReporting', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('adwords_reporting_nonce')
        ));
    }

    /**
     * Enqueue styles.
     */
    public function enqueue_styles() {
        // Base styles
        wp_enqueue_style(
            'adwords-reporting-base',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/min.styles.css',
            array(),
            '1.0.0'
        );

        // Component styles
        wp_enqueue_style(
            'adwords-reporting-charts',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/components/charts.css',
            array('adwords-reporting-base'),
            '1.0.0'
        );

        wp_enqueue_style(
            'adwords-reporting-forms',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/components/forms.css',
            array('adwords-reporting-base'),
            '1.0.0'
        );

        wp_enqueue_style(
            'adwords-reporting-tables',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/components/tables.css',
            array('adwords-reporting-base'),
            '1.0.0'
        );
    }

    /**
     * Add admin menu items.
     */
    public function add_admin_menu() {
        add_menu_page(
            'Adwords Reporting',
            'Adwords',
            'manage_options',
            'adwords-reporting',
            array($this, 'render_dashboard_page'),
            'dashicons-chart-area',
            30
        );

        add_submenu_page(
            'adwords-reporting',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'adwords-reporting',
            array($this, 'render_dashboard_page')
        );

        add_submenu_page(
            'adwords-reporting',
            'Campaigns',
            'Campaigns',
            'manage_options',
            'adwords-reporting-campaigns',
            array($this, 'render_campaigns_page')
        );

        add_submenu_page(
            'adwords-reporting',
            'Settings',
            'Settings',
            'manage_options',
            'adwords-reporting-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register plugin settings.
     */
    public function register_settings() {
        register_setting('adwords_reporting_settings', 'adwords_reporting_client_id');
        register_setting('adwords_reporting_settings', 'adwords_reporting_client_secret');
        register_setting('adwords_reporting_settings', 'adwords_reporting_developer_token');
        register_setting('adwords_reporting_settings', 'adwords_reporting_refresh_token');
        register_setting('adwords_reporting_settings', 'adwords_reporting_customer_id');
    }

    /**
     * Render dashboard page.
     */
    public function render_dashboard_page() {
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

    /**
     * Render campaigns page.
     */
    public function render_campaigns_page() {
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

    /**
     * Render settings page.
     */
    public function render_settings_page() {
        ?>
        <div class="wrap adwords-reporting">
            <h1>Adwords Settings</h1>
            <?php if (isset($_GET['oauth_success'])): ?>
                <div class="notice notice-success">
                    <p>Successfully authenticated with Google Ads!</p>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['oauth_error'])): ?>
                <div class="notice notice-error">
                    <p>Error authenticating with Google Ads: <?php echo esc_html($_GET['oauth_error']); ?></p>
                </div>
            <?php endif; ?>
            <form method="post" action="options.php">
                <?php
                settings_fields('adwords_reporting_settings');
                do_settings_sections('adwords_reporting_settings');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Client ID</th>
                        <td>
                            <input type="text" name="adwords_reporting_client_id" 
                                   value="<?php echo esc_attr(get_option('adwords_reporting_client_id')); ?>" 
                                   class="regular-text">
                            <p class="description">Your Google OAuth 2.0 Client ID</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Client Secret</th>
                        <td>
                            <input type="password" name="adwords_reporting_client_secret" 
                                   value="<?php echo esc_attr(get_option('adwords_reporting_client_secret')); ?>" 
                                   class="regular-text">
                            <p class="description">Your Google OAuth 2.0 Client Secret</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Developer Token</th>
                        <td>
                            <input type="text" name="adwords_reporting_developer_token" 
                                   value="<?php echo esc_attr(get_option('adwords_reporting_developer_token')); ?>" 
                                   class="regular-text">
                            <p class="description">Your Google Ads Developer Token</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Customer ID</th>
                        <td>
                            <input type="text" name="adwords_reporting_customer_id" 
                                   value="<?php echo esc_attr(get_option('adwords_reporting_customer_id')); ?>" 
                                   class="regular-text"
                                   placeholder="123-456-7890">
                            <p class="description">Your Google Ads customer ID without dashes (e.g., 1234567890)</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Save Settings'); ?>
            </form>

            <div class="oauth-section">
                <h2>Google Ads Authentication</h2>
                <?php
                // Debug raw option value
                $raw_client_id = get_option('adwords_reporting_client_id');
                $client_id = $raw_client_id;
                $redirect_uri = admin_url('admin.php?page=adwords-reporting-settings');
                
                // Debug output with error checking
                echo '<div style="background: #f0f0f0; padding: 15px; margin: 15px 0; border: 1px solid #ccc; border-radius: 4px;">';
                echo '<h3 style="margin-top: 0; color: #23282d;">Debug Information:</h3>';
                echo '<pre style="background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 3px; overflow: auto;">';
                echo "Raw Client ID from get_option(): " . var_export($raw_client_id, true) . "\n";
                echo "Client ID (after processing): " . (empty($client_id) ? 'Not Set' : $client_id) . "\n";
                echo "Redirect URI: " . $redirect_uri . "\n";
                
                // Check if settings were saved
                if (isset($_POST['adwords_reporting_client_id'])) {
                    echo "\nPOST data received:\n";
                    echo "Client ID from POST: " . $_POST['adwords_reporting_client_id'] . "\n";
                }
                
                // Check WordPress options table
                global $wpdb;
                $option_value = $wpdb->get_var($wpdb->prepare(
                    "SELECT option_value FROM $wpdb->options WHERE option_name = %s",
                    'adwords_reporting_client_id'
                ));
                echo "\nDirect database check:\n";
                echo "Client ID from database: " . var_export($option_value, true) . "\n";
                
                $auth_url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query([
                    'client_id' => $client_id,
                    'redirect_uri' => $redirect_uri,
                    'scope' => 'https://www.googleapis.com/auth/adwords',
                    'response_type' => 'code',
                    'access_type' => 'offline',
                    'prompt' => 'consent'
                ]);
                
                echo "\nAuth URL: " . $auth_url . "\n";
                echo '</pre>';
                
                // Show any error messages
                if (isset($_GET['oauth_error'])) {
                    echo '<div style="background: #ffebee; padding: 10px; margin-top: 10px; border: 1px solid #ffcdd2; border-radius: 4px;">';
                    echo '<h4 style="margin-top: 0; color: #c62828;">Error Information:</h4>';
                    echo '<p><strong>Error:</strong> ' . esc_html($_GET['oauth_error']) . '</p>';
                    echo '</div>';
                }
                
                echo '</div>';
                ?>

                <?php if (get_option('adwords_reporting_client_id') && get_option('adwords_reporting_client_secret')): ?>
                    <?php if (!get_option('adwords_reporting_refresh_token')): ?>
                        <a href="<?php echo esc_url($auth_url); ?>" class="button button-primary">
                            Connect Google Ads Account
                        </a>
                    <?php else: ?>
                        <p class="description">✓ Connected to Google Ads</p>
                        <form method="post" action="">
                            <?php wp_nonce_field('adwords_reporting_disconnect', 'adwords_reporting_disconnect_nonce'); ?>
                            <input type="hidden" name="action" value="disconnect">
                            <button type="submit" class="button">Disconnect</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Handle OAuth callback.
     */
    public function handle_oauth_callback() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'adwords-reporting-settings') {
            return;
        }

        if (isset($_GET['code'])) {
            $client_id = get_option('adwords_reporting_client_id');
            $client_secret = get_option('adwords_reporting_client_secret');
            $redirect_uri = admin_url('admin.php?page=adwords-reporting-settings');

            $token_url = 'https://oauth2.googleapis.com/token';
            $response = wp_remote_post($token_url, [
                'body' => [
                    'code' => $_GET['code'],
                    'client_id' => $client_id,
                    'client_secret' => $client_secret,
                    'redirect_uri' => $redirect_uri,
                    'grant_type' => 'authorization_code'
                ]
            ]);

            if (is_wp_error($response)) {
                wp_redirect(add_query_arg('oauth_error', urlencode($response->get_error_message())));
                exit;
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($body['refresh_token'])) {
                update_option('adwords_reporting_refresh_token', $body['refresh_token']);
                wp_redirect(add_query_arg('oauth_success', '1'));
                exit;
            } else {
                wp_redirect(add_query_arg('oauth_error', 'No refresh token received'));
                exit;
            }
        }
    }

    /**
     * Handle disconnect action.
     */
    public function handle_disconnect() {
        if (!isset($_POST['adwords_reporting_disconnect_nonce']) || 
            !wp_verify_nonce($_POST['adwords_reporting_disconnect_nonce'], 'adwords_reporting_disconnect')) {
            return;
        }

        delete_option('adwords_reporting_refresh_token');
        wp_redirect(add_query_arg('oauth_success', '1'));
        exit;
    }

    /**
     * Get campaign data via AJAX.
     */
    public function get_campaign_data() {
        check_ajax_referer('adwords_reporting_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $client = $this->get_google_ads_client();
        if (!$client) {
            wp_send_json_error('Google Ads client not configured');
        }

        try {
            $customer_id = get_option('adwords_reporting_customer_id');
            if (!$customer_id) {
                wp_send_json_error('Missing customer ID');
                return;
            }

            // Get campaign performance data for the last 30 days
            $googleAdsServiceClient = $client->getGoogleAdsServiceClient();
            $query = "
                SELECT 
                    campaign.name,
                    segments.date,
                    metrics.impressions,
                    metrics.clicks
                FROM campaign
                WHERE 
                    campaign.status != 'REMOVED'
                    AND segments.date DURING LAST_30_DAYS
                ORDER BY 
                    campaign.name,
                    segments.date";

            $response = $googleAdsServiceClient->search(
                $customer_id,
                $query,
                ['pageSize' => 1000]
            );

            $campaign_data = [];
            $dates = [];
            $current_campaign = null;
            $impressions = [];
            $clicks = [];

            foreach ($response->iterateAllElements() as $googleAdsRow) {
                $campaign = $googleAdsRow->getCampaign();
                $segments = $googleAdsRow->getSegments();
                $metrics = $googleAdsRow->getMetrics();
                
                $date = $segments->getDate();
                $campaign_name = $campaign->getName();
                
                // Store unique dates
                if (!in_array($date, $dates)) {
                    $dates[] = $date;
                }

                // If we're starting a new campaign
                if ($current_campaign !== $campaign_name) {
                    if ($current_campaign !== null) {
                        // Save the previous campaign's data
                        $campaign_data[] = [
                            'name' => $current_campaign,
                            'impressions' => $impressions,
                            'clicks' => $clicks
                        ];
                    }
                    $current_campaign = $campaign_name;
                    $impressions = array_fill(0, count($dates), 0); // Initialize with zeros
                    $clicks = array_fill(0, count($dates), 0); // Initialize with zeros
                }

                // Add the impression and click data for this date
                $date_index = array_search($date, $dates);
                $impressions[$date_index] = $metrics->getImpressions();
                $clicks[$date_index] = $metrics->getClicks();
            }

            // Add the last campaign's data
            if ($current_campaign !== null) {
                $campaign_data[] = [
                    'name' => $current_campaign,
                    'impressions' => $impressions,
                    'clicks' => $clicks
                ];
            }

            // Sort dates chronologically
            sort($dates);

            wp_send_json_success([
                'dates' => $dates,
                'campaigns' => $campaign_data
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Error fetching data: ' . $e->getMessage());
        }
    }

    /**
     * Get Google Ads client.
     *
     * @return GoogleAdsClient|null
     */
    private function get_google_ads_client() {
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
} 