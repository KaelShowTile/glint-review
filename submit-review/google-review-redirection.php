<?php

$current_dir = __DIR__; 
$wp_root = dirname(dirname(dirname(dirname($current_dir))));

require_once $wp_root . '/wp-load.php';

$params = [];
if (!empty($_GET)) {
    $params = $_GET;
} 
// If no GET parameters, try to parse from fragment
else if (isset($_SERVER['REQUEST_URI'])) {
    $url_parts = parse_url($_SERVER['REQUEST_URI']);
    if (isset($url_parts['fragment'])) {
        parse_str($url_parts['fragment'], $params);
    }
}

// Extract email_id and google_review_link from parameters
$email_id = isset($params['email_id']) ? intval($params['email_id']) : 0;
$google_review_link = isset($params['google-review-link']) ? urldecode($params['google-review-link']) : '';

// If we still don't have parameters, try to parse the raw URL
if (!$email_id || !$google_review_link) {
    $raw_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    if ($raw_url) {
        $url_parts = parse_url($raw_url);
        if (isset($url_parts['fragment'])) {
            parse_str($url_parts['fragment'], $params);
            $email_id = isset($params['email_id']) ? intval($params['email_id']) : $email_id;
            $google_review_link = isset($params['google-review-link']) ? urldecode($params['google-review-link']) : $google_review_link;
        }
    }
}

// Validate parameters
if (!$email_id || !$google_review_link) {
    // Log the error for debugging
    error_log("GLINT: Invalid parameters in Google review redirection. Email ID: $email_id, Google Link: $google_review_link");
    error_log("GLINT: Received parameters: " . print_r($params, true));
    error_log("GLINT: GET parameters: " . print_r($_GET, true));
    error_log("GLINT: Request URI: " . $_SERVER['REQUEST_URI']);
    
    wp_die('Invalid parameters provided. Please check the link and try again.');
}

// Update the database to mark as reviewed
global $wpdb;
$table_name = $wpdb->prefix . 'glint_review_feedback_email';

$updated = $wpdb->update(
    $table_name,
    ['check_reviewed' => 1],
    ['email_id' => $email_id],
    ['%d'],
    ['%d']
);

if ($updated === false) {
    error_log("GLINT: Failed to update review status for email_id: $email_id");
    // Continue with redirect anyway
}

// Redirect to Google review page
header('Location: ' . esc_url_raw($google_review_link));
exit;
