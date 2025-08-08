<?php

function get_product_reviews($product_id) 
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'glint_review';

    // Prepare the SQL query
    if($product_id != 0)
    {
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE post_id = %d ORDER BY review_date DESC",
            $product_id
        );
    }
    else
    {
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY review_date DESC",
        );
    }

    // Fetch the results
    $reviews = $wpdb->get_results($query);

    return $reviews;
}