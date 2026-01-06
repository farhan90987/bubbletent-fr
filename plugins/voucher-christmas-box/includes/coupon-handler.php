<?php
// coupon-handler.php

// Hook into WooCommerce order completion
add_action('woocommerce_order_status_completed', 'handle_order_completion_for_christmas_box_products', 20, 1);

function handle_order_completion_for_christmas_box_products($order_id) {
    global $printer_email;

    log_to_debug("Function: handle_order_completion_for_christmas_box_products started for Order #$order_id");

    // Load the order
    $order = wc_get_order($order_id);

    // Check if this is the main order (ignore sub-orders)
    if ($order->get_parent_id()) {
        log_to_debug("Ignoring sub-order #$order_id (part of main order #" . $order->get_parent_id() . ")");
        return;
    }

    // Step 1: Check if the order contains a Christmas Box product
    if (!order_contains_christmas_box($order)) {
        log_to_debug("Order #$order_id does not contain a Christmas Box product.");
        return;
    }

    // Step 2: Check if the order contains other valid voucher products (downloadable or PDF coupon)
    if (!order_contains_voucher_products($order)) {
        log_to_debug("Order #$order_id does not contain voucher-eligible products.");
        return;
    }

    // Step 3: Retrieve a voucher code and save it with the order
    $voucher_code = get_available_voucher_code();
    if (empty($voucher_code)) {
        log_to_debug("No available voucher codes found for Order #$order_id.");
        return;
    }

    // Step 4: Attach the voucher code to the order immediately
    log_to_debug("Attaching voucher code $voucher_code to order #$order_id");
    update_post_meta($order_id, '_voucher_code', $voucher_code);

    // Step 5: Schedule the coupon creation to happen 2 minutes later via Action Scheduler
    if (function_exists('as_schedule_single_action')) {
        log_to_debug("Scheduling coupon creation for order #$order_id with voucher code $voucher_code in 2 minutes.");
        as_schedule_single_action(time() + 120, 'create_coupon_for_order', array('order_id' => $order_id, 'voucher_code' => $voucher_code));
    } else {
        log_to_debug("Action Scheduler not available for order #$order_id.");
    }

    // Step 6: Send email to printer with voucher code and order details
    send_email_to_printer($order, $voucher_code, $printer_email);
}

// Function to create a coupon in WooCommerce after a delay
add_action('create_coupon_for_order', 'create_coupon_for_order', 10, 2);
function create_coupon_for_order($order_id, $voucher_code) {
    log_to_debug("Attempting to create coupon for delayed action for order #$order_id with voucher code $voucher_code.");

    // Check if the coupon already exists
    if (!empty(get_post_meta($order_id, '_coupon_created', true))) {
        log_to_debug("Coupon already created for order #$order_id, skipping.");
        return;
    }

    // Load the order
    $order = wc_get_order($order_id);

    // Prepare a description with purchased product names
    $product_names = [];
    $item_subtotal = 0;

    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();
        $product = wc_get_product($product_id);

        // Exclude the Christmas Box product
        if ($product_id === get_christmas_box_product_id()) {
            log_to_debug("Excluding Christmas Box product (ID: $product_id) from subtotal calculation.");
            continue;
        }

        // Add the product's subtotal to the voucher value
        $item_subtotal += $item->get_subtotal();
        $product_names[] = $item->get_name() . ' x ' . $item->get_quantity();
    }

    log_to_debug("Product names for coupon description: " . implode(', ', $product_names));
    log_to_debug("Calculated subtotal for voucher: $item_subtotal");

    // Check if subtotal is valid
    if ($item_subtotal <= 0) {
        log_to_debug("Order #$order_id has a subtotal of 0 (excluding Christmas Box), skipping coupon creation.");
        return;
    }

    // Set coupon expiration date to 3 years from order creation
    $expiry_date = date('Y-m-d', strtotime('+3 years', strtotime($order->get_date_created())));

    // Prepare the description including order ID
    $coupon_description = "Order #$order_id - " . implode(', ', $product_names);

    // Create the coupon using the WC_Coupon class
    $coupon = new WC_Coupon();
    $coupon->set_code($voucher_code);
    $coupon->set_discount_type('fixed_cart');
    $coupon->set_amount($item_subtotal);
    $coupon->set_date_expires($expiry_date);
    $coupon->set_usage_limit(1);
    $coupon->set_description($coupon_description);
    $coupon->save();

    log_to_debug("Coupon created and assigned to order #$order_id with voucher code $voucher_code.");

    // Mark the coupon as created for this order
    update_post_meta($order_id, '_coupon_created', true);
}
