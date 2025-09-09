<?php

function glint_wc_product_review_email_list_admin(){
    if (!current_user_can('manage_options')){
            return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'glint_review_feedback_email';
    $emails = $wpdb->get_results("SELECT * FROM $table_name");

    if (empty($emails)) {
        echo '<div class="notice notice-warning"><p>No email found.</p></div>';
        return;
    }

    wp_enqueue_style('glint-review-sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css');
    wp_enqueue_script('glint-review-sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array('jquery'), null, true);
    wp_enqueue_script('glint-email-list-function', GLINT_WC_PRODUCT_REVIEW_URL . 'assets/js/email-list.js', array('jquery'), null, true);

    // Display the data in a table
    echo '<div class="wrap">';
    echo '<h1>Emails</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>
            <tr>
                <th style="width: 60px;">Email ID</th>
                <th style="width: 80px;">Order No.</th>
                <th>Product Name</th>
                <th style="width: 180px;">Customer Name</th>
                <th style="width: 240px;">Customer Email</th>
                <th style="width: 140px;">First Sending Date</th>
                <th style="width: 140px;">Next Sending Date</th>
                <th style="width: 80px;">Total Email Sent</th>
                <th style="width: 80px;">Product Reviewed</th>
                <th style="width: 80px;">Delete Record</th>
            </tr>
        </thead>';
    echo '<tbody>';

    foreach ($emails as $email) {

        $order_permalink = get_site_url() . '/wp-admin/admin.php?page=wc-orders&action=edit&id=' . $email->order_id;

        echo '<tr id="email-row-' . esc_attr($email->email_id) . '">';
        echo '<td>' . esc_html($email->email_id) . '</td>';
        echo '<td><a href="' . $order_permalink . '">#' . esc_html($email->order_id) . '</a></td>';
        echo '<td>' . esc_html($email->review_item) . '</td>';
        echo '<td>' . esc_html($email->customer_name) . '</td>';
        echo '<td>' . esc_html($email->customer_email) . '</td>';
        echo '<td>' . esc_html($email->first_send_date) . '</td>';
        echo '<td>' . esc_html($email->next_send_date) . '</td>';  
        echo '<td>' . esc_html($email->send_times) . '</td>';
        if($email->check_reviewed == 1){
            echo '<td>Yes</td>';
        }else{
            echo '<td>No</td>';
        }
        echo '<td>
                <button class="delete-email-button button button-danger" 
                        data-email-id="' . esc_attr($email->email_id) . '">
                    Delete
                </button>
            </td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}

function delete_email() 
{
    global $wpdb;

    // Check if the request is valid
    if (!isset($_POST['email_id'])) {
        wp_send_json_error('Invalid request.');
    }

    $email_id = intval($_POST['email_id']);
    $table_name = $wpdb->prefix . 'glint_review_feedback_email';
    $result = $wpdb->delete(
        $table_name,
        array('email_id' => $email_id),
        array('%d')
    );

    // Check if the deletion was successful
    if ($result !== false) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Failed to delete the email. ');
    }
}
add_action('wp_ajax_delete_email', 'delete_email');