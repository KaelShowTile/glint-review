<?php

$current_dir = __DIR__; 
$wp_root = dirname(dirname(dirname(dirname($current_dir))));

require_once $wp_root . '/wp-load.php';

$params = [];
if (isset($_SERVER['REQUEST_URI'])) {
    $url_parts = parse_url($_SERVER['REQUEST_URI']);
    if (isset($url_parts['fragment'])) {
        parse_str($url_parts['fragment'], $params);
    }
}

// Extract email_id and google_review_link from parameters
$email_id = isset($params['email_id']) ? intval($params['email_id']) : 0;
$google_review_link = isset($params['google-review-link']) ? urldecode($params['google-review-link']) : '';

// Validate parameters
if (!$email_id || !$google_review_link) {
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
    error_log("Failed to update review status for email_id: $email_id");
    // Continue with redirect anyway
}

// Redirect to Google review page
header('Location: ' . esc_url_raw($google_review_link));
exit;