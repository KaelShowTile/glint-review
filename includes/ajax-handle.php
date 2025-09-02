<?php
function generate_product_review_feed_ajax() {
    /* Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'generate_feed_nonce')) {
        wp_send_json_error('Security check failed');
    } */
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized access');
    }
    
    // Execute your function
    try {
        generate_product_review_feed(); // Your existing function
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/google-merchat-review-feed.xml';
        
        if (file_exists($file_path)) {
            wp_send_json_success(array(
                'path' => $upload_dir['baseurl'] . '/google-merchat-review-feed.xml',
                'message' => 'Feed generated successfully'
            ));
        } else {
            wp_send_json_error('File not created');
        }
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
}
// Hook for logged-in users
add_action('wp_ajax_generate_product_review_feed', 'generate_product_review_feed_ajax');