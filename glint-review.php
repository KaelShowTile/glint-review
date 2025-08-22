<?php
/*
Plugin Name: CHT REVIEW
Description: A plugin for GTO/CHT to recevied reviews
Version: 2.0.0
Author: Kael
*/

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

define('GLINT_WC_PRODUCT_REVIEW_PATH', plugin_dir_path(__FILE__));
define('GLINT_WC_PRODUCT_REVIEW_URL', plugin_dir_url(__FILE__));

require_once plugin_dir_path(__FILE__) . 'includes/review-db.php';
require_once plugin_dir_path(__FILE__) . 'includes/review-list.php';
require_once plugin_dir_path(__FILE__) . 'includes/review-display.php';
require_once plugin_dir_path(__FILE__) . 'includes/email-create.php';
require_once plugin_dir_path(__FILE__) . 'includes/email-list.php';
require_once plugin_dir_path(__FILE__) . 'includes/email-setting.php';
require_once plugin_dir_path(__FILE__) . 'includes/corn-job.php';

//Add css to frontend
function glint_wc_product_review_enqueue_styles() {
    wp_enqueue_style('glint-wc-product-review-frontend', plugins_url('assets/css/review-frontend.css', __FILE__), array(), '1.0', 'all');
}
add_action('wp_enqueue_scripts', 'glint_wc_product_review_enqueue_styles');

// Activation/Deactivation actions
register_activation_hook(__FILE__, function() {
    require_once plugin_dir_path(__FILE__) . 'includes/review-db.php';
    Glint_WC_Product_Review_DB::create_table();
});

register_deactivation_hook(__FILE__, function() {
    require_once plugin_dir_path(__FILE__) . 'includes/review-db.php';
    //Glint_WC_Product_Review_DB::cleanup();
});

register_activation_hook(__FILE__, 'glint_schedule_email_cron');
function glint_schedule_email_cron() {
    if (!wp_next_scheduled('glint_daily_email_check')) {
        wp_schedule_event(time(), 'hourly', 'glint_daily_email_check');
    }
}
 
register_deactivation_hook(__FILE__, 'glint_remove_email_cron');
function glint_remove_email_cron() {
    wp_clear_scheduled_hook('glint_daily_email_check');
}

// Add the cron hook
add_action('glint_daily_email_check', 'glint_check_and_send_review_emails');

// Register admin menu 
function external_product_review_menu() {
    add_menu_page(
        'CHT Reviews',                              // Page title
        'CHT Reviews',                              // Menu title
        'manage_options',                           // Capability
        'cht-wc-product-reviews',                   // Menu slug
        'glint_wc_product_review_list_admin',       // Callback function
        'dashicons-star-filled',                    // Icon URL
        6                                           // Position
    );

    add_submenu_page(
        'cht-wc-product-reviews',                   // Parent slug
        'Reviews',                                  // Page title
        'Reviews',                                  // Menu title
        'manage_options',                           // Capability
        'cht-wc-product-reviews',                   // Menu slug (same as parent makes it first submenu)
        'glint_wc_product_review_list_admin'        // Callback (same as parent)
    );

    add_submenu_page(
        'cht-wc-product-reviews',                   // Parent slug
        'Emails',                                   // Page title
        'Emails',                                   // Menu title
        'manage_options',                           // Capability
        'cht-wc-email-list',                        // Menu slug (same as parent makes it first submenu)
        'glint_wc_product_review_email_list_admin'  // Callback (same as parent)
    );

    add_submenu_page(
        'cht-wc-product-reviews',                   // Parent slug
        'Settings',                                 // Page title
        'Settings',                                 // Menu title
        'manage_options',                           // Capability
        'cht-wc-edm-setting',                       // Menu slug (same as parent makes it first submenu)
        'glint_wc_product_review_edm_setting_admin' // Callback (same as parent)
    );
}
add_action('admin_menu', 'external_product_review_menu');

//testing
//add_action('init', 'glint_manual_trigger_cron_test');
function glint_manual_trigger_cron_test() {
    if (isset($_GET['glint_test_cron']) && current_user_can('manage_options')) {
        // Run the cron function
        glint_check_and_send_review_emails();
        
        // Show results
        echo '<div class="notice notice-success"><p>Cron job manually triggered and completed.</p></div>';
        
        // Log the test
        error_log('GLINT: Cron job manually triggered via URL');
    }
}