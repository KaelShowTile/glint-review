<?php

function glint_wc_product_review_email_list_admin(){
    if (!current_user_can('manage_options')){
            return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'glint_review_feedback_email';
    $emails = $wpdb->get_results("SELECT * FROM $table_name");

    if (empty($reviews)) {
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
                <th>Email ID</th>
                <th>Order ID</th>
                <th>Product Name</th>
                <th>Customer Name</th>
                <th>Customer Email</th>
                <th>First Sending Date</th>
                <th>Next Sending Date</th>
                <th>Total Email Sent</th>
                <th>Product Reviewed</th>
                <th>Delete Record</th>
            </tr>
        </thead>';
    echo '<tbody>';

    foreach ($reviews as $review) {
        echo '<tr id="review-row-' . esc_attr($review->email_id) . '">';
        echo '<td>' . esc_html($review->email_id) . '</td>';
        echo '<td>' . esc_html($review->order_id) . '</td>';
        echo '<td>' . esc_html($review->review_item) . '</td>';
        echo '<td>' . esc_html($review->customer_name) . '</td>';
        echo '<td>' . esc_html($review->customer_email) . '</td>';
        echo '<td>' . esc_html($review->first_send_date) . '</td>';
        echo '<td>' . esc_html($review->next_send_date) . '</td>';  
        echo '<td>' . esc_html($review->send_times) . '</td>';
        if($review->check_reviewed == 1){
            echo '<td>Yes</td>';
        }else{
            echo '<td>No</td>';
        }
        echo '<td>
                <button class="delete-review-button button button-danger" 
                        data-review-id="' . esc_attr($review->email_id) . '">
                    Delete
                </button>
            </td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}