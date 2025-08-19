<?php 

function glint_wc_product_review_edm_setting_admin(){
    
    wp_enqueue_script('edm-admin', GLINT_WC_PRODUCT_REVIEW_URL . 'assets/js/edm-admin.js', ['jquery', 'wp-util'], '1.0', true );
    wp_localize_script('edm-admin', 'glintEdmAdmin', ['nonce' => wp_create_nonce('glint_edm_nonce') ]);

    $settings = get_all_edm_setting();


    ?>

    <div class="wrap glint-review-settings">
        <h1>EDM Settings</h1>
        <form id="glint-edm-setting-form">
            <div class="input-section sender-section">
                <h2>Send From:</h2>
                <input type="text" name="" value="<?php echo esc_attr($settings['sender']); ?>" >
            </div>
            <div class="input-section bcc-section">
                <h2>BCC:</h2>
                <input type="text" name="" value="<?php echo esc_attr($settings['bcc']); ?>" >
            </div>
            <div class="input-section title-section">
                <h2>BCC:</h2>
                <input type="text" name="" value="<?php echo esc_attr($settings['title']); ?>" >
            </div>
            <div class="input-content content-before-section">
                <h2>Email Content Before Review Button:</h2>
                <textarea name="" rows="10"><?php echo esc_attr($settings['content-before']); ?></textarea>
            </div>

            <div class="input-content ontent-after-section">
                <h2>Email Content After Review Button:</h2>
                <textarea name="" rows="10"><?php echo esc_attr($settings['content-after']); ?></textarea>
            </div>

            <button type="submit" id="save-methods" class="button-primary">Save Settings</button>
        </form>
    </div>

    <?php

}

function get_all_edm_setting(){

}

function save_all_edm_setting(){
    check_ajax_referer('glint_edm_nonce', 'security');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }


}