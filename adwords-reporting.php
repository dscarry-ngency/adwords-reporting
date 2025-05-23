<?php
/*
Plugin Name: Adwords Reporting
Description: A WordPress plugin for Google Ads reporting.
Version: 1.0.0
Author: Your Name
*/

// Enqueue Chart.js
function enqueue_admin_scripts() {
    wp_enqueue_script(
        'chartjs',
        plugins_url('assets/js/chart.min.js', __FILE__),
        [],
        '3.7.0',
        true
    );
}
add_action('admin_enqueue_scripts', 'enqueue_admin_scripts'); 