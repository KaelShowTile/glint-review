<?php

// Callback function to render the Sending List page
function glint_wc_product_review_list_admin() 
{
    if (!current_user_can('manage_options')){
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'glint_review';
    $reviews = $wpdb->get_results("SELECT * FROM $table_name");

    if (empty($reviews)) {
        echo '<div class="notice notice-warning"><p>No reviews found.</p></div>';
        return;
    }

    wp_enqueue_style('glint-review-sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css');
    wp_enqueue_script('glint-review-sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array('jquery'), null, true);
    wp_enqueue_script('glint-review-list-function', GLINT_WC_PRODUCT_REVIEW_URL . 'assets/js/review-list.js', array('jquery'), null, true);

    // Display the data in a table
    echo '<div class="wrap">';
    echo '<h1 style="float:left;">Reviews</h1>';
    echo '<button id="generate-review-feed" class="button button-primary button-large" style="float:right;">Update Review Feed</button>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>
            <tr>
                <th>Review ID</th>
                <th>Product Name</th>
                <th>Customer Name</th>
                <th>Customer Email</th>
                <th>Review Content</th>
                <th>Review Date</th>
                <th>Review Images</th>
                <th>Product Rating</th>
                <th>Show Review</th>
                <th>Delete Review</th>
            </tr>
          </thead>';
    echo '<tbody>';

    foreach ($reviews as $review) {
        echo '<tr id="review-row-' . esc_attr($review->review_id) . '">';
        echo '<td>' . esc_html($review->review_id) . '</td>';
        echo '<td>' . get_the_title($review->post_id) . '</td>';
        echo '<td>' . esc_html($review->customer_name) . '</td>';
        echo '<td>' . esc_html($review->customer_email) . '</td>';
        echo '<td>' . esc_html($review->review_content) . '</td>';
        echo '<td>' . esc_html($review->review_date) . '</td>';
        echo '<td>';
        if (!empty($review->review_imgs)) 
        {
            $image_paths = explode(',', $review->review_imgs); // Split URLs by comma
            foreach ($image_paths as $path) 
            {
                $images_url = get_site_url() . '/wp-content/uploads/glint-review/' . trim($path);
                echo '<a href="'. $images_url . '" target="_blank" rel="nofollow"><img src="' . esc_url($images_url) . '" style="max-width: 60px; height: auto; margin: 5px;" /></a>';
            }
        }
        echo '</td>';
        echo '<td>' . esc_html($review->product_rating) . '</td>';
        echo '<td>
                <input type="checkbox" class="show-review-checkbox" 
                       data-review-id="' . esc_attr($review->review_id) . '" 
                       ' . checked($review->show_review, 1, false) . ' />
              </td>';
         echo '<td>
                <button class="delete-review-button button button-danger" 
                        data-review-id="' . esc_attr($review->review_id) . '">
                    Delete
                </button>
              </td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}

function update_show_review() 
{
    global $wpdb;

    // Check if the request is valid
    if (!isset($_POST['review_id']) || !isset($_POST['show_review'])) 
    {
        wp_send_json_error('Invalid request.');
    }

    // Sanitize inputs
    $review_id = intval($_POST['review_id']);
    $show_review = intval($_POST['show_review']);

    $table_name = $wpdb->prefix . 'glint_review';
    $result = $wpdb->update(
        $table_name,
        array('show_review' => $show_review),
        array('review_id' => $review_id),
        array('%d'),
        array('%d')
    );

    // Check if the update was successful
    if ($result !== false) 
    {
        wp_send_json_success();
    } else {
        wp_send_json_error('Failed to update the database.');
    }
}
add_action('wp_ajax_update_show_review', 'update_show_review');

function delete_review() 
{
    global $wpdb;

    // Check if the request is valid
    if (!isset($_POST['review_id'])) {
        wp_send_json_error('Invalid request.');
    }

    $review_id = intval($_POST['review_id']);
    $table_name = $wpdb->prefix . 'glint_review';
    $result = $wpdb->delete(
        $table_name,
        array('review_id' => $review_id),
        array('%d')
    );

    // Check if the deletion was successful
    if ($result !== false) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Failed to delete the review.');
    }
}
add_action('wp_ajax_delete_review', 'delete_review');
