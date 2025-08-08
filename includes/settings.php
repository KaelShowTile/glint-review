<?php

// Add a menu item for the plugin in the admin menu 
function external_product_review_menu() {
    add_menu_page(
        'CHT Reviews',          // Page title
        'CHT Reviews',          // Menu title
        'manage_options',      // Capability
        'external-product-reviews',    // Menu slug
        'external_product_review_list_page', // Callback function
        'dashicons-star-filled', // Icon URL
        6                       // Position
    );
}
add_action('admin_menu', 'external_product_review_menu');

//add css
function external_product_review_admin_style() 
{
        wp_register_style( 'custom_wp_admin_css', plugin_dir_url(dirname(__FILE__)) . 'css/style.css', false, '1.0.0' );
        wp_enqueue_style( 'custom_wp_admin_css' );
}

add_action( 'admin_enqueue_scripts', 'wpdocs_enqueue_custom_admin_style' ); 

//add js
function external_product_review_enqueue_scripts() 
{
    wp_enqueue_style('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css');
    wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'external_product_review_enqueue_scripts');

// Callback function to render the Sending List page
function external_product_review_list_page() 
{
    if (!current_user_can('manage_options')) 
    {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'glint_review';
    $reviews = $wpdb->get_results("SELECT * FROM $table_name");

    if (empty($reviews)) {
        echo '<div class="notice notice-warning"><p>No reviews found.</p></div>';
        return;
    }

    // add CSS
    wp_enqueue_style('custom_wp_admin_css');

    // Display the data in a table
    echo '<div class="wrap">';
    echo '<h1>Reviews</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>
            <tr>
                <th>Review ID</th>
                <th>Post ID</th>
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
        echo '<td>' . esc_html($review->post_id) . '</td>';
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
                $full_url = home_url('/submit-review/' . trim($path)); 
                echo '<a href="'. $full_url . '" target="_blank" rel="nofollow"><img src="' . esc_url($full_url) . '" style="max-width: 60px; height: auto; margin: 5px;" /></a>';
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

    // Add JavaScript for AJAX handling
    echo '<script>
            jQuery(document).ready(function($) {
                // Handle Show Review checkbox change
                $(".show-review-checkbox").on("change", function() {
                    var reviewId = $(this).data("review-id");
                    var isChecked = $(this).is(":checked") ? 1 : 0;

                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: {
                            action: "update_show_review",
                            review_id: reviewId,
                            show_review: isChecked
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Success",
                                    text: "Show Review updated successfully!",
                                    toast: true,
                                    position: "top-end",
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Error",
                                    text: "Failed to update Show Review.",
                                    toast: true,
                                    position: "top-end",
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "AJAX request failed. Please try again.",
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    });
                });

                // Handle Delete button click
                $(".delete-review-button").on("click", function() {
                    var reviewId = $(this).data("review-id");
                    var button = $(this);

                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won\'t be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, delete it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: ajaxurl,
                                type: "POST",
                                data: {
                                    action: "delete_review",
                                    review_id: reviewId
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Remove the row from the table
                                        $("#review-row-" + reviewId).remove();
                                        Swal.fire({
                                            icon: "success",
                                            title: "Deleted!",
                                            text: "The review has been deleted.",
                                            toast: true,
                                            position: "top-end",
                                            showConfirmButton: false,
                                            timer: 3000
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: "error",
                                            title: "Error",
                                            text: "Failed to delete the review.",
                                            toast: true,
                                            position: "top-end",
                                            showConfirmButton: false,
                                            timer: 3000
                                        });
                                    }
                                },
                                error: function() {
                                    Swal.fire({
                                        icon: "error",
                                        title: "Error",
                                        text: "AJAX request failed. Please try again.",
                                        toast: true,
                                        position: "top-end",
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                }
                            });
                        }
                    });
                });
            });
          </script>';
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

    // Define the table name with the prefix
    $table_name = $wpdb->prefix . 'glint_review';

    // Update the show_review value in the database
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

    // Sanitize the input
    $review_id = intval($_POST['review_id']);

    // Define the table name with the prefix
    $table_name = $wpdb->prefix . 'glint_review';

    // Delete the record from the database
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

