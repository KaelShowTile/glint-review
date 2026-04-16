<?php

$current_dir = __DIR__; 
$wp_root = dirname(dirname(dirname(dirname($current_dir))));

require_once $wp_root . '/wp-load.php';

// Function to sanitize input data
function sanitizeInput($data) 
{
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input data
    $email_id = isset($_POST['email_id']) ? sanitizeInput($_POST['email_id']) : null;
    $product_id = isset($_POST['product_id']) ? sanitizeInput($_POST['product_id']) : null;
    $customer_name = isset($_POST['customer_name']) ? sanitizeInput(wp_unslash($_POST['customer_name'])) : null;
    $customer_email = isset($_POST['customer_email']) ? sanitizeInput(wp_unslash($_POST['customer_email'])) : null;
    
    // Sanitize and safely encode Emojis to HTML entities (&#x...;) so they survive any database charset limitations
    $product_review_raw = isset($_POST['product_review']) ? wp_unslash($_POST['product_review']) : '';
    $product_review = wp_encode_emoji( sanitize_textarea_field( $product_review_raw ) );
    $product_rating = isset($_POST['product_rating']) ? intval($_POST['product_rating']) : null; 

    // Validate required fields
    if (empty($product_id) || empty($customer_name) || empty($customer_email) || empty($product_review) || $product_rating === null) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    // Directory to save uploaded images
    $uploadDir = $wp_root . '/wp-content/uploads/glint-review/';
    if (!is_dir($uploadDir)) 
    {
        mkdir($uploadDir, 0755, true); // Create the directory if it doesn't exist
    }

    // Array to store uploaded image URLs
    $uploadedImageUrls = [];

    // Loop through all uploaded files
    if (!empty($_FILES)) 
    {
        foreach ($_FILES as $key => $file) 
        {
            if ($file['error'] === UPLOAD_ERR_OK) {
                $fileName = basename($file['name']);
                $uploadFile = $uploadDir . $fileName;

                // Move the uploaded file to the desired directory
                if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                    $uploadedImageUrls[] = $fileName; // Store the file path
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error moving uploaded file: ' . $fileName]);
                    exit;
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error uploading file: ' . $file['name']]);
                exit;
            }
        }
    }

    $review_imgs = !empty($uploadedImageUrls) ? implode(',', $uploadedImageUrls) : '';
    $review_date = date('Y-m-d H:i:s');

    global $wpdb;
    $table_name = $wpdb->prefix . 'glint_review'; 
    
    // Force the current database connection to use utf8mb4
    $wpdb->query("SET NAMES utf8mb4");
    
    // Insert data using WordPress $wpdb class (Safely handles utf8mb4 and emojis natively)
    $inserted = $wpdb->insert(
        $table_name,
        array(
            'post_id'        => $product_id,
            'customer_name'  => $customer_name,
            'customer_email' => $customer_email,
            'review_content' => $product_review,
            'review_date'    => $review_date,
            'review_imgs'    => $review_imgs,
            'product_rating' => $product_rating
        ),
        array(
            '%d', // post_id
            '%s', // customer_name
            '%s', // customer_email
            '%s', // review_content
            '%s', // review_date
            '%s', // review_imgs
            '%d'  // product_rating
        )
    );

    if ($inserted === false) {
        echo json_encode(['status' => 'error', 'message' => 'Database insertion failed: ' . $wpdb->last_error]);
        exit;
    }

    if($email_id){
        glint_confirm_review_checked($email_id);
    }
    
    // Return success response
    echo json_encode(['status' => 'success', 'message' => 'Data and images uploaded successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

function glint_confirm_review_checked($email_id){
    global $wpdb;
    $table_name = $wpdb->prefix . 'glint_review_feedback_email';

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
        'check_reviewed' => 1
    ];

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
}


?>
