<?php 

add_action('woocommerce_checkout_order_processed', 'glint_record_order_for_review', 10, 3);

//Get data for new email record
function glint_record_order_for_review($order_id, $posted_data, $order) {
    
    if (!$order_id || glint_order_already_recorded($order_id)) {
        return;
    }
    
    // Get order object if not provided
    if (!$order instanceof WC_Order) {
        $order = wc_get_order($order_id);
    }
    
    // Skip if can't get a valid order object
    if (!$order) {
        return;
    }
    
    // Get customer details
    $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
    $customer_email = $order->get_billing_email();

    // Get the first product from the order (you might want to adjust this logic)
    $items = $order->get_items();
    $first_item = reset($items);
    
    if ($first_item) {
        $product = $first_item->get_product();
        $review_item = $product->get_name();
        $review_item_link = get_permalink($product->get_id());
    } else {
        // If something wrong happen, the order doesn't have a product
        return;
    }
    
    // Get email settings for scheduling
    $settings = get_all_edm_setting();
    $delay_days = isset($settings['delay-days']) ? intval($settings['delay-days']) : 7;
    
    // Calculate first send date
    $order_date = $order->get_date_created();
    $first_send_date = $order_date->modify("+{$delay_days} days")->format('Y-m-d');
    
    // Prepare data for insertion
    $data = [
        'order_id' => $order_id,
        'customer_name' => $customer_name,
        'customer_email' => $customer_email,
        'review_item' => $review_item,
        'review_item_link' => $review_item_link,
        'check_reviewed' => 0,
        'first_send_date' => $first_send_date,
        'next_send_date' => $first_send_date, // Same as first send date initially
        'send_times' => 0
    ];
    
    // Insert into database
    glint_insert_review_email_record($data);
}

function glint_order_already_recorded($order_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'glint_review_feedback_email';
    
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE order_id = %d",
        $order_id
    ));
    
    return $count > 0;
}

//insert new email record
function glint_insert_review_email_record($data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'glint_review_feedback_email';
    
    // Validate required fields
    $required = ['order_id', 'customer_email', 'review_item'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            error_log("Missing required field for review email record: $field");
            error_log("Data received: " . print_r($data, true));
            return false;
        }
    }
    
    // Set default values only for missing optional fields
    $defaults = [
        'customer_name' => '',
        'review_item_link' => '',
        'check_reviewed' => 0,
        'first_send_date' => current_time('mysql'),
        'next_send_date' => current_time('mysql'),
        'send_times' => 0
    ];
    
    // Merge defaults without overwriting existing values
    foreach ($defaults as $key => $value) {
        if (!isset($data[$key])) {
            $data[$key] = $value;
        }
    }
    
    // Define the format for each field
    $format = [
        'order_id' => '%d',
        'customer_name' => '%s',
        'customer_email' => '%s',
        'review_item' => '%s',
        'review_item_link' => '%s',
        'check_reviewed' => '%d',
        'first_send_date' => '%s',
        'next_send_date' => '%s',
        'send_times' => '%d'
    ];
    
    // Insert the record
    $result = $wpdb->insert(
        $table_name,
        $data,
        $format
    );
    
    if ($result === false) {
        error_log("Failed to insert review email record: " . $wpdb->last_error);
        error_log("Attempted to insert: " . print_r($data, true));
        return false;
    }
    
    return $wpdb->insert_id;
}

//update record
function glint_update_email_record_after_sending($email_id, $reviewed = false) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'glint_review_feedback_email';
    
    // Get current record
    $record = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE email_id = %d",
        $email_id
    ));
    
    if (!$record) {
        error_log("Email record not found for ID: $email_id");
        return false;
    }
    
    // Prepare update data
    $update_data = [
        'send_times' => $record->send_times + 1,
        'check_reviewed' => $reviewed ? 1 : 0
    ];
    
    // Only update next_send_date if customer hasn't reviewed yet
    if (!$reviewed) {
        $settings = get_all_edm_setting();
        $sending_period = isset($settings['sending-period']) ? intval($settings['sending-period']) : 3;
        
        $next_send_date = date('Y-m-d', strtotime("+{$sending_period} days"));
        $update_data['next_send_date'] = $next_send_date;
    }
    
    // Update the record
    $result = $wpdb->update(
        $table_name,
        $update_data,
        ['email_id' => $email_id],
        ['%d', '%d', '%s'],
        ['%d']
    );
    
    if ($result === false) {
        error_log("Failed to update email record: " . $wpdb->last_error);
        return false;
    }
    
    return true;
}

