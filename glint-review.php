<?php
/*
Plugin Name: CHT REVIEW
Description: A plugin for GTO/CHT to recevied reviews
Version: 1.1
Author: Kael
*/

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/main.php';

//add css
function glint_review_enqueue_styles() {
    // Define the URL of the CSS file
    $css_url = plugins_url('css/style.css', __FILE__);

    // Enqueue the CSS file
    wp_enqueue_style('glint-review-style', $css_url, array(), '1.0', 'all');
}
add_action('wp_enqueue_scripts', 'glint_review_enqueue_styles');
