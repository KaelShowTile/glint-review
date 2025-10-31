<?php

function generate_product_review_feed()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'glint_review';

    $upload_dir = wp_upload_dir();
    $upload_path = $upload_dir['basedir'];

    $file_name = 'google-merchat-review-feed.xml';
    $file_path = $upload_path . '/' . $file_name;

    $reviews = $wpdb->get_results("
        SELECT * FROM $table_name 
        WHERE show_review = 1
    ");

    // Initialize XML structure
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><feed></feed>');
    $xml->addAttribute('xmlns:vc', 'http://www.w3.org/2007/XMLSchema-versioning');
    $xml->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
    $xml->addAttribute('xsi:noNamespaceSchemaLocation', 'http://www.google.com/shopping/reviews/schema/product/2.4/product_reviews.xsd');

    // Add version
    $xml->addChild('version', '2.4');

    // Add publisher
    $publisher = $xml->addChild('publisher');
    $publisher->addChild('name', get_bloginfo('name'));
    $publisher->addChild('favicon', 'https://www.cheapestiles.com.au/wp-content/uploads/2025/06/cht-fav-20250630.png'); // for cht

    // Add reviews container
    $reviews_container = $xml->addChild('reviews');

    foreach ($reviews as $review) {
        $review_element = $reviews_container->addChild('review');

        $review_element->addChild('review_id', $review->review_id);

        $reviewer = $review_element->addChild('reviewer');
        $reviewer->addChild('name', htmlspecialchars($review->customer_name));

        $review_element->addChild('review_timestamp', date('c', strtotime($review->review_date)));
        $review_element->addChild('content', htmlspecialchars($review->review_content));

        $review_url = $review_element->addChild('review_url', 'https://www.cheapestiles.com.au/wp-content/plugins/glint-review/submit-review/submit-form.php#product_id=' . htmlspecialchars($review->post_id));
        $review_url->addAttribute('type', 'singleton');

        $ratings = $review_element->addChild('ratings');
        $overall = $ratings->addChild('overall', $review->product_rating);
        $overall->addAttribute('min', '1');
        $overall->addAttribute('max', '5');

        $products = $review_element->addChild('products');
        $product = $products->addChild('product');
        $product->addChild('product_url', get_permalink(htmlspecialchars($review->post_id)));

        /* Add images if needed
        $images = unserialize($review->review_imgs);
        foreach ($images as $image_url) {
            // Note: The sample doesn't include images, so skipping for now
        } */
    }

    // Save XML to file
    if ($xml->asXML($file_path)) {
        error_log("File saved successfully at: " . $file_path);
    } else {
        error_log("Error saving file.");
    }
}
