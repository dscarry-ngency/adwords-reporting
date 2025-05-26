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
            'adwords-reporting-settings',
            array($this, 'render_settings_page'),
            'dashicons-admin-generic',
            30
        );
        add_submenu_page(
            'adwords-reporting-settings',
            'Settings',
            'Settings',
            'manage_options',
            'adwords-reporting-settings',
            array($this, 'render_settings_page')
        );
        // Add Testing submenu
        add_submenu_page(
            'adwords-reporting-settings',
            'Testing',
            'Testing',
            'manage_options',
            'adwords-reporting-testing',
            array($this, 'render_testing_page')
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
        </div>
        <?php
    }

    /**
     * Render testing page.
     */
    public function render_testing_page() {
        ?>
        <div class="wrap adwords-reporting">
            <h1>Testing</h1>
            <div class="testing-container">
                <!-- Testing interface will be added here -->
            </div>
        </div>
        <?php
    }
} 