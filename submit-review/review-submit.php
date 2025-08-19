<?php

$current_dir = __DIR__; 
$wp_root = dirname(dirname(dirname(dirname($current_dir))));

require_once $wp_root . '/wp-load.php';
//require_once $wp_root . '/config.php'; 

// Function to sanitize input data
function sanitizeInput($data) 
{
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input data
    $product_id = isset($_POST['product_id']) ? sanitizeInput($_POST['product_id']) : null;
    $customer_name = isset($_POST['customer_name']) ? sanitizeInput($_POST['customer_name']) : null;
    $customer_email = isset($_POST['customer_email']) ? sanitizeInput($_POST['customer_email']) : null;
    $product_review = isset($_POST['product_review']) ? sanitizeInput($_POST['product_review']) : null;
    $product_rating = isset($_POST['product_rating']) ? intval($_POST['product_rating']) : null; 

    // Validate required fields
    if (empty($product_id) || empty($customer_name) || empty($customer_email) || empty($product_review) || $product_rating === null) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    // Directory to save uploaded images
    $uploadDir = 'img/';
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
                    $uploadedImageUrls[] = $uploadFile; // Store the file path
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
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($mysqli->connect_error) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $mysqli->connect_error]);
        exit;
    }

    // Prepare the SQL query to insert data into the byuz_gto_review table
    $table_name = $wpdb->prefix . 'glint_review'; 
    $query = "INSERT INTO $table_name (post_id, customer_name, customer_email, review_content, review_date, review_imgs, product_rating) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);

    if ($stmt === false) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare SQL statement: ' . $mysqli->error]);
        exit;
    }

    // Bind parameters to the SQL query
    $stmt->bind_param(
        'isssssi', // Data types: i (integer), s (string)
        $product_id,
        $customer_name,
        $customer_email,
        $product_review,
        $review_date,
        $review_imgs,
        $product_rating
    );

    // Execute the query
    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Error executing SQL statement: ' . $stmt->error]);
        exit;
    }

    // Close the statement and connection
    $stmt->close();
    $mysqli->close();

    // Return success response
    echo json_encode(['status' => 'success', 'message' => 'Data and images uploaded successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>