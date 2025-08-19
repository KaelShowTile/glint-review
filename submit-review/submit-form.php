<?php

function get_current_url($path = '') {
    static $base;
    if (!$base) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $base = "{$protocol}://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    }
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function get_parent_directory_url($url) {
    // Add scheme if missing (required for parse_url)
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }


    $parsed = parse_url($url);
    $path = isset($parsed['path']) ? $parsed['path'] : '';
    $segments = explode('/', trim($path, '/'));
    
    if (!empty($segments)) {
        array_pop($segments);
    }
    
    // Rebuild path
    $new_path = !empty($segments) ? '/' . implode('/', $segments) . '/' : '/';
    
    // Reconstruct URL
    $scheme = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : 'http://';
    $host = isset($parsed['host']) ? $parsed['host'] : '';
    $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
    
    return $scheme . $host . $port . $new_path;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo get_parent_directory_url(get_current_url()); ?>assets/css/review-form.css" type="text/css" media="all">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Rate This Product</title>
</head>
<body>
    
    <div class="container">

        <h1>Submit Your Reviews</h1>

        <div class="product-info-container">
            <a id="product-link" target="_blank" >Please Rate: <h2 id="product-title"></h2></a>
        </div>

        <form id="uploadForm">

            <div class="star-rating">
                <input type="radio" name="rating" value="1"><i></i>
                <input type="radio" name="rating" value="2"><i></i>
                <input type="radio" name="rating" value="3"><i></i>
                <input type="radio" name="rating" value="4"><i></i>
                <input type="radio" name="rating" value="5"><i></i>
            </div>
            <div class="mt-3">
                <span id="rating-value" class="h4">5</span> out of 5 stars
            </div>

            <input type="hidden" id="product_id" name="product_id" value="">

            <div data-coreui-toggle="rating" data-coreui-value="3"></div>

            <p>Your Comment</p>
            <textarea id="product_review" name="product_review" value="" rows="6" required></textarea>
            
            <p for="images">Upload images (up to 9):</p>
            <input type="file" id="images" name="images" accept="image/*" multiple>

            <div class="thumbnail-container" id="thumbnailContainer"></div>

            <p>Your Name</p>
            <input type="input" id="customer_name" name="customer_name" value=""required>

            <p>Your Email</p>
            <input type="input" id="customer_email" name="customer_email" value="" required>

            <input type="submit" value="Upload">
        </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/browser-image-compression/dist/browser-image-compression.min.js"></script>
    <script src="<?php echo get_parent_directory_url(get_current_url()); ?>assets/js/submit-review.js"></script>
</body>
</html>