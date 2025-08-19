<?php 

function glint_create_feedback_email_record($pass_id){
    global $wpdb;

    $order = null;
    $order = wc_get_order($pass_id);
    $order_id = $pass_id;
    $order_date = $order->get_date_created()->format('d-m-Y');
    $customer_name = $order->get_billing_first_name();
    $customer_email = $order->get_billing_email();
    $items = $order->get_items();
    $items_name = "";
    $items_premalink = "";
    if (!empty($items)) 
    {
        $first_item = reset($items);
        $product = $first_item->get_product();

        if ($product){
            $items_name = $product->get_name();
            $items_premalink = $product->get_permalink();
        }
    } 

}

add_action('woocommerce_checkout_order_processed', 'glint_create_feedback_email_record', 10, 1);