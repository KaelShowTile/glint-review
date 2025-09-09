<?php
//Daliy check function
function glint_check_and_send_review_emails() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'glint_review_feedback_email';

    // Get email settings
    $settings = get_all_edm_setting();
    $maximum_sending_times = isset($settings['times-limitation']) ? intval($settings['times-limitation']) : 3;
    
    // Get emails that are due to be sent (next_send_date is today or earlier)
    // and where the customer hasn't reviewed yet
    $today = current_time('Y-m-d');
    $due_emails = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name 
         WHERE next_send_date <= %s 
         AND send_times <= %s
         AND check_reviewed = 0
         ORDER BY next_send_date ASC",
        $today, 
        $maximum_sending_times
    ));
    
    if (empty($due_emails)) {
        return;
    }
    
    foreach ($due_emails as $email_record) {
        // Generate and send the email
        $sent = glint_send_review_email($email_record, $settings);

        if ($sent) {
            // Update the record
            glint_update_email_record_after_sending($email_record->email_id, false);
        }
    }
}

// Send email
function glint_send_review_email($email_record, $settings) {

    // Generate the email content
    $email_content = glint_generate_email_content($email_record, $settings);
    
    // Get email headers
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: Cheapestiles <' . $settings['sender'] . '>',
    ];
    
    if (!empty($settings['bcc'])) {
        $headers[] = 'Bcc: ' . $settings['bcc'];
    }
    
    // Send the email
    $sent = wp_mail(
        $email_record->customer_email,
        $settings['title'],
        $email_content,
        $headers
    );
    
    return $sent;
}

// generate email
function glint_generate_email_content($email_record, $settings) {
    // Get Google business URL from settings
    $google_business_url = isset($settings['google-business-url']) ? $settings['google-business-url'] : '';
    
    // Generate review links
    $website_review_link = glint_generate_website_review_link($email_record);
    $google_review_link = glint_generate_google_review_link($email_record, $google_business_url);
    $user_name = $email_record-> customer_name;
    
    // Get content parts from settings
    $content_before = isset($settings['content-before']) ? $settings['content-before'] : '';
    $content_after = isset($settings['content-after']) ? $settings['content-after'] : '';
    $content_footer = isset($settings['content-footer']) ? $settings['content-footer'] : '';
    $google_img_url = isset($settings['google-review-image']) ? $settings['google-review-image'] : '';
    $email_logo_url = isset($settings['email-header-logo']) ? $settings['email-header-logo'] : '';
    
    // Build the email HTML
    $email_html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>' . esc_html($settings['title']) . '</title>
    </head>
    <body style="font-family: Verdana, sans-serif; color: #333; width: 80%; max-width: 700px; margin: 0 auto; padding: 20px;">
        <div style="background-color: #fff; padding: 30px; border-radius: 10px;">
            
            <div style="text-align: center;"><img style="max-width:200px;" src="' . esc_url($email_logo_url) . '"></div>
            <h1 style="text-align: center; font-size: 28px; margin-bottom: 40px; letter-spacing: -0.5px;">We want to make sure you have the best tile shopping experience!</h1>
            
            <p style="margin-bottom:20px;">Hi ' . esc_html($user_name) . ',</p>
            <!-- Content before button -->
            <div style="margin-bottom: 30px; font-size: 14px; letter-spacing: -0.2px; line-height: 1.5;">
                ' . wpautop(stripslashes($content_before)) . '
            </div>
            
            <!-- Buttons section -->
            <div style="display: block; width:100%; text-align: center; margin-bottom: 50px;">
                <a href="' . esc_url($google_review_link) . '"><img src="' . esc_url($google_img_url) . '" alt="Google Reviews" style="width: 100%; height: auto;"></a>
            </div>
            
            <div style="display: block; width:100%; text-align: center; margin: 30px 0 80px;">
                <a href="' . esc_url($google_review_link) . '" style="margin:5px auto 10px; padding: 15px 25px; border-radius: 10px; background: #294165; color: #fff; font-weight: 600; font-size: 16px; letter-spacing: 1px; text-decoration: none;">Google Review</a>
            </div>

            <p style="font-size: 14px; letter-spacing: -0.2px;">No Google Account? Leave a <a href="' . esc_url($website_review_link) . '" style=" color: #294165;"><b>Website Review</b></a> (no login needed)</p>
            
            <!-- Content after button -->
            <div style="margin-bottom: 30px; font-size: 14px; letter-spacing: -0.2px; line-height: 1.5;">
                ' . wpautop(stripslashes($content_after)) . '
            </div>
            
            <!-- Footer -->
            <div style="border-top: 1px solid #ddd; padding-top: 20px; font-size: 12px; color: #777;">
                ' . wpautop(stripslashes($content_footer)) . '
            </div>
        </div>
    </body>
    </html>';
    
    return $email_html;
}

//generate website review url
function glint_generate_website_review_link($email_record) {
    // Extract product ID from the product link if not stored directly
    $product_id = url_to_postid($email_record->review_item_link);
    
    $params = [
        'email_id' => $email_record->email_id,
        'product_id' => $product_id,
        'product_name' => urlencode($email_record->review_item),
        'product_link' => urlencode($email_record->review_item_link),
        'customer_name' => urlencode($email_record->customer_name),
        'customer_email' => urlencode($email_record->customer_email)
    ];
    
    $query_string = http_build_query($params);
    
    return site_url('/wp-content/plugins/glint-review/submit-review/submit-form.php') . '#' . $query_string;
}

//generate Google review url
function glint_generate_google_review_link($email_record, $google_business_url) {
    $params = [
        'email_id' => $email_record->email_id,
        'google-review-link' => urlencode($google_business_url)
    ];
    
    $query_string = http_build_query($params);
    
    return site_url('/wp-content/plugins/glint-review/submit-review/google-review-redirection.php') . '?' . $query_string;
}
