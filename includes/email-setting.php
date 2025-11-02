<?php 

function glint_wc_product_review_edm_setting_admin() {
    wp_enqueue_script('edm-admin', GLINT_WC_PRODUCT_REVIEW_URL . 'assets/js/edm-admin.js', ['jquery', 'wp-util'], '1.0', true);
    wp_localize_script('edm-admin', 'glintEdmAdmin', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);

    // Get all settings
    $settings = get_all_edm_setting();

    //add css
    wp_enqueue_style( 'glint-edm-admin', GLINT_WC_PRODUCT_REVIEW_URL . 'assets/css/edm-admin.css', [], '1.0' );

    // Display the settings form
    ?>
    <div class="wrap glint-review-settings">
        <h1>EDM Settings</h1>
        <div id="edm-message" class="notice hidden" style="margin: 10px 0; padding: 10px;"></div>
        <form id="glint-edm-setting-form">
            <div class="input-section sender-section">
                <p>Send From:</p>
                <input type="text" name="sender" value="<?php echo esc_attr($settings['sender']); ?>" placeholder="sender@example.com">
            </div>
            <div class="input-section bcc-section">
                <p>BCC:</p>
                <input type="text" name="bcc" value="<?php echo esc_attr($settings['bcc']); ?>" placeholder="bcc@example.com">
            </div>
            <div class="input-section title-section">
                <p>Email Title:</p>
                <input type="text" name="title" value="<?php echo esc_attr($settings['title']); ?>" placeholder="Email Subject">
            </div>

            <div class="input-section google-image-section">
                <p>Logo on the top of Email:</p>
                <input type="text" name="email-header-logo" value="<?php echo esc_attr($settings['email-header-logo']); ?>" placeholder="https://">
            </div>

            <div class="input-section content-before-section">
                <p>Email Content Before Review Button:</p>
                <?php 
                $content_before = isset($settings['content-before']) ? $settings['content-before'] : '';
                wp_editor($content_before, 'content_before_editor', [
                    'textarea_name' => 'content-before',
                    'textarea_rows' => 10,
                    'media_buttons' => false,
                    'teeny' => true
                ]); 
                ?>
            </div>

            <div class="input-section google-review-section">
                <p>Google Review Link:</p>
                <input type="text" name="google-business-url" value="<?php echo esc_attr($settings['google-business-url']); ?>" placeholder="Google MyBusiness Review Link">
            </div>

            <div class="input-section google-image-section">
                <p>Google Review Image:</p>
                <input type="text" name="google-review-image" value="<?php echo esc_attr($settings['google-review-image']); ?>" placeholder="https://">
            </div>

            <div class="input-section content-after-section">
                <p>Email Content After Review Button:</p>
                <?php 
                $content_after = isset($settings['content-after']) ? $settings['content-after'] : '';
                wp_editor($content_after, 'content_after_editor', [
                    'textarea_name' => 'content-after',
                    'textarea_rows' => 10,
                    'media_buttons' => false,
                    'teeny' => true
                ]); 
                ?>
            </div>

            <div class="input-section content-footer-section">
                <p>Email Footer:</p>
                <?php 
                $content_footer = isset($settings['content-footer']) ? $settings['content-footer'] : '';
                wp_editor($content_footer, 'content-footer', [
                    'textarea_name' => 'content-footer',
                    'textarea_rows' => 10,
                    'media_buttons' => false,
                    'teeny' => true
                ]); 
                ?>
            </div>

            <div class="input-section delay-days-section">
                <p>How Many Days Start Sending Email After Purchasing:</p>
                <input type="number" name="delay-days" value="<?php echo esc_attr($settings['delay-days']); ?>" min="1" max="30">
            </div>

            <div class="input-section sending-period-section">
                <p>Days between sending attempts:</p>
                <input type="number" name="sending-period" value="<?php echo esc_attr($settings['sending-period']); ?>" min="1" max="30">
            </div>

            <div class="input-section times-limitation">
                <p>Maximum sending times:</p>
                <input type="number" name="times-limitation" value="<?php echo esc_attr($settings['times-limitation']); ?>" min="1" max="30">
            </div>

            <button type="submit" id="save-edm-settings" class="button-primary">Save Settings</button>

            <div style="margin-top: 30px; padding: 20px; background: #f5f5f5; border: 1px solid #ddd;">
                <h2>Email Preview</h2>
                <p>Preview how your email will look with the current settings:</p>
                <a href="<?php echo admin_url('admin.php?page=cht-wc-edm-setting&glint_preview_email=1'); ?>" 
                class="button button-primary" target="_blank">Preview Email</a>
            </div>
        </form>
    </div>

    <?php 
}

function get_all_edm_setting() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'glint_review_email_setting';
    
    // Default settings
    $default_settings = [
        'sender' => '',
        'bcc' => '',
        'title' => '',
        'email-header-logo' => '',
        'content-before' => '',
        'google-business-url' => '',
        'google-review-image' => '',
        'content-after' => '',
        'content-footer' => '',
        'delay-days' => '14',
        'sending-period' => '7',
        'times-limitation' => '3'
    ];
    
    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        return $default_settings;
    }
    
    // Get all settings from database
    $results = $wpdb->get_results("SELECT setting_name, setting_value FROM $table_name", ARRAY_A);
    
    if (empty($results)) {
        return $default_settings;
    }
    
    $settings = [];
    foreach ($results as $result) {
        $settings[$result['setting_name']] = $result['setting_value'];
    }
    
    // Merge with defaults to ensure all keys exist
    return array_merge($default_settings, $settings);
}

function save_all_edm_setting(){
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized access');
        wp_die();
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'glint_review_email_setting';
    
    // Get the posted data
    $settings = [
        'sender' => sanitize_text_field($_POST['sender']),
        'bcc' => sanitize_text_field($_POST['bcc']),
        'title' => sanitize_text_field($_POST['title']),
        'email-header-logo' => sanitize_text_field($_POST['email-header-logo']),
        'content-before' => wp_kses_post(wp_unslash($_POST['content-before'])),
        'google-business-url' => sanitize_text_field($_POST['google-business-url']),
        'google-review-image' => sanitize_text_field($_POST['google-review-image']),
        'content-after' => wp_kses_post(wp_unslash($_POST['content-after'])),
        'content-footer' => wp_kses_post(wp_unslash($_POST['content-footer'])),
        'delay-days' => intval($_POST['delay-days']),
        'sending-period' => intval($_POST['sending-period']),
        'times-limitation' => intval($_POST['times-limitation'])
    ];
    
    // Validate required fields
    if (empty($settings['sender']) || empty($settings['title'])) {
        wp_send_json_error('Sender and Title are required fields');
        wp_die();
    }
    
    // Validate email format
    if (!empty($settings['sender']) && !is_email($settings['sender'])) {
        wp_send_json_error('Please enter a valid sender email address');
        wp_die();
    }
    
    // Update or insert each setting
    foreach ($settings as $name => $value) {
        // Check if setting already exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE setting_name = %s", 
            $name
        ));
        
        if ($exists) {
            // Update existing setting
            $wpdb->update(
                $table_name,
                ['setting_value' => $value],
                ['setting_name' => $name],
                ['%s'],
                ['%s']
            );
        } else {
            // Insert new setting
            $wpdb->insert(
                $table_name,
                [
                    'setting_name' => $name,
                    'setting_value' => $value
                ],
                ['%s', '%s']
            );
        }
    }
    
    wp_send_json_success('Settings saved successfully');
    wp_die();

}

add_action('wp_ajax_save_edm_settings', 'save_all_edm_setting');


// Add email preview functionality
add_action('admin_init', 'glint_email_preview_handler');
function glint_email_preview_handler() {
    if (isset($_GET['glint_preview_email']) && current_user_can('manage_options')) {
        // Generate a test email
        $email_content = glint_generate_test_email();
        
        // Display the email
        echo $email_content;
        exit;
    }
}

// Generate a test email with sample data
function glint_generate_test_email() {
    // Get email settings
    $settings = get_all_edm_setting();
    
    // Create a mock email record with sample data
    $mock_email_record = (object) [
        'email_id' => 999,
        'order_id' => 12345,
        'customer_name' => 'Customer',
        'customer_email' => 'kaelshowtile@gmail.com',
        'review_item' => 'Sample Tile Product',
        'review_item_link' => get_site_url() . '/product/mineral-quartz-matt-tile-200x200-code02309/',
        'check_reviewed' => 0,
        'first_send_date' => date('Y-m-d'),
        'next_send_date' => date('Y-m-d'),
        'send_times' => 0,
        'times-limitation' => 0
    ];
    
    // Generate the email content
    return glint_generate_email_content($mock_email_record, $settings);
}
