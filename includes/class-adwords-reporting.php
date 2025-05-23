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
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Client Secret</th>
                        <td>
                            <input type="password" name="adwords_reporting_client_secret" 
                                   value="<?php echo esc_attr(get_option('adwords_reporting_client_secret')); ?>" 
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Developer Token</th>
                        <td>
                            <input type="text" name="adwords_reporting_developer_token" 
                                   value="<?php echo esc_attr(get_option('adwords_reporting_developer_token')); ?>" 
                                   class="regular-text">
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
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

        // TODO: Implement actual data fetching
        // For now, return dummy data
        $data = array(
            'performance' => array(
                'labels' => array('Jan', 'Feb', 'Mar', 'Apr', 'May'),
                'clicks' => array(100, 120, 90, 150, 200),
                'impressions' => array(1000, 1200, 900, 1500, 2000),
                'cost' => array(500, 600, 450, 750, 1000)
            ),
            'campaigns' => array(
                array(
                    'name' => 'Campaign 1',
                    'status' => 'Active',
                    'budget' => '$100',
                    'clicks' => 100,
                    'impressions' => 1000,
                    'cost' => '$500'
                )
            )
        );

        wp_send_json_success($data);
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