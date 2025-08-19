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

function glint_show_product_review($product_id)
{
    $product_name = get_the_title($product_id);
    $product_link = get_permalink($product_id);
    $reviews = get_product_reviews($product_id);
    $total_review = 0;
    $total_score = 0;

    $output .= '<div class = "glint-product-review-container" id="submit-a-review">';

    $output .= '<div class="review-score-headline mobile-only">';
    $output .= '<p class = "rating-title">Review & Rating of</p>';
    $output .= '<h5>' . $product_name . '</h5>';
    $output .= '</div>';

    $output .= '<div class="glint-product-review-list">';
    if (!empty($reviews)){
        foreach ($reviews as $review){
            if(esc_html($review->show_review == 1)){
                $rating_score = $review->product_rating;
                $full_stars = floor($rating_score);
                $half_star = ($rating_score - $full_stars) >= 0.5 ? 1 : 0; 
                $empty_stars = 5 - $full_stars - $half_star;
                $total_review ++;
                $total_score = $total_score + $rating_score;

                $output .= '<li class="glint-product-review-item">';
                $output .= '<h5 class="reviewr-name">' . esc_html($review->customer_name) . '</h5>';
                $output .= '<p class="review-date">' . date('F j, Y', strtotime($review->review_date)) . '</p>';
                $output .= '<div class="rating-stars-container">';

                //full stars
                for ($i = 1; $i <= $full_stars; $i++){
                     $output .= '<span class="rating-stars filled">&starf;</span>';
                }
                //half star
                if ($half_star){
                    $output .= '<span class="rating-stars half">&starf;</span>';
                }
                //empty star
                for ($i = 1; $i <= $empty_stars; $i++){
                    $output .= '<span class="rating-stars">&starf;</span>';
                }

                $output .= '</div>';

                if(!empty($review->review_imgs)){
                    $images = explode(',', $review->review_imgs);
                    $output .= '<div class="review-images">';
                    foreach ($images as $image) {
                        $output .= '<a href="' . GLINT_WC_PRODUCT_REVIEW_URL . 'submit-review/' . $image . '" lightbox-added><img src="/submit-review/' . $image . '" alt="Review Image" "></a>';
                    }
                    $output .= '</div>';
                }

                $output .= '<p class= "review-content">' . esc_html($review->review_content) . '</p>';

                $output .= '</li>';
            }
        }
    }else{
        $output .= '<p class="no-review">No Review Found.</p>';
    }
    $output .= '</div>';

    $output .= '<div class="review-score-container">';

    $output .= '<div class="review-score-headline desktop-only">';
    $output .= '<p class = "rating-title">Review & Rating of</p>';
    $output .= '<h5>' . $product_name . '</h5>';
    $output .= '</div>';

    $output .= '<div class="rating-stars-container desktop-only">';

    $average_score;
    if($total_review > 0)
    {
        $average_score = $total_score / $total_review;
    }
    else
    {
        $average_score = 0 ;
    }
    $average_full_stars = floor($average_score);
    $average_half_star = ($average_score - $average_full_stars) >= 0.5 ? 1 : 0; 
    $average_empty_stars = 5 - $average_full_stars - $average_half_star;

    for ($i = 1; $i <= $average_full_stars; $i++){
        $output .= '<span class="rating-stars filled">&starf;</span>';
    }

    if ($average_half_star){
        $output .= '<span class="rating-stars half">&starf;</span>';
    }

    for ($i = 1; $i <= $average_empty_stars; $i++){
        $output .= '<span class="rating-stars">&starf;</span>';
    }

    $output .= '</div>';

    $output .= '<a href="' . GLINT_WC_PRODUCT_REVIEW_URL . 'submit-review/submit-form.php#product_id=' . $product_id . '&product_name=' . $product_name . '&product_link=' . $product_link . '" id="submit-review-btn" target="_blank" rel="nofollow">Post Your Review</a>';

    $output .= '</div>';

    $output .= '</div>';

    return $output;

}