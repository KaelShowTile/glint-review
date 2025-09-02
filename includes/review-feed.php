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
        AND review_date >= DATE_SUB(NOW(), INTERVAL 60 DAY)
    ");

    // Initialize XML structure
    $xml = new SimpleXMLElement('<?xml version="1.0"?><feed></feed>');
    $xml->addAttribute('xmlns', 'http://www.w3.org/2005/Atom');
    $xml->addAttribute('xmlns:g', 'http://base.google.com/ns/1.0');

    foreach ($reviews as $review) {
        $entry = $xml->addChild('entry');
        $entry->addChild('g:review_id', $review->review_id);
        $entry->addChild('g:product_id', htmlspecialchars($review->post_id)); 
        $entry->addChild('g:reviewer_name', htmlspecialchars($review->customer_name));
        $entry->addChild('g:content', htmlspecialchars($review->review_content));
        $entry->addChild('g:review_timestamp', date('c', strtotime($review->review_date)));
        $entry->addChild('g:rating', $review->product_rating);

        /* Add images
        $images = unserialize($review->review_imgs);
        foreach ($images as $image_url) {
            $entry->addChild('g:image_url', $image_url);
        } */
    }

    // Save XML to file
    if ($xml->asXML($file_path)) {
        error_log("File saved successfully at: " . $file_path);
    } else {
        error_log("Error saving file.");
    }
}

